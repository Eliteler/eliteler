<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Console\Commands;

use App\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PlanExpiryCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expiry:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Reminder intervals (fetch from settings or define statically)
        $reminderDays = \App\Http\Controllers\Admin\CronJobController::parseDays(
            DB::table('config')->where('config_key', 'cronjob_dates_in_array')->value('config_value')
        );

        $currentDate = Carbon::now();

        // Get appointment pending email template content
        $expiredEmailTemplateDetails = EmailTemplate::where('email_template_id', '584922675206')->first();
        $expiryEmailTemplateDetails  = EmailTemplate::where('email_template_id', '584922675207')->first();

        foreach ($reminderDays as $daysBefore) {
            // Calculate target expiry range
            $expiryDate = $currentDate->copy()->addDays($daysBefore);

            // Get users whose plans expire on the target date
            $users = DB::table('users')
                ->where('status', 1) 
                ->whereDate('plan_validity', $expiryDate)
                ->get();

            if ($users->isEmpty()) {
                continue;
            }

            foreach ($users as $user) {
                $expiredPlanDetails = [
                    'status'          => "",
                    'emailSubject'    => $expiredEmailTemplateDetails->email_template_subject,
                    'emailContent'    => $expiredEmailTemplateDetails->email_template_content,
                    'registeredName'  => $user->name,
                    'registeredEmail' => $user->email,
                    'expiryDate'      => Carbon::parse($user->plan_validity)->format('Y-m-d'),
                    'planCode'        => json_decode($user->plan_details)->plan_id,
                    'planName'        => json_decode($user->plan_details)->plan_name,
                    'planPrice'       => json_decode($user->plan_details)->plan_price,
                ];

                $expiryPlanDetails = [
                    'status'          => "",
                    'emailSubject'    => $expiryEmailTemplateDetails->email_template_subject,
                    'emailContent'    => $expiryEmailTemplateDetails->email_template_content,
                    'registeredName'  => $user->name,
                    'registeredEmail' => $user->email,
                    'expiryDate'      => Carbon::parse($user->plan_validity)->format('Y-m-d'),
                    'planCode'        => json_decode($user->plan_details)->plan_id,
                    'planName'        => json_decode($user->plan_details)->plan_name,
                    'planPrice'       => json_decode($user->plan_details)->plan_price,
                ];

                try {
                    if ($daysBefore <= 0) {
                        Mail::to($user->email)->send(new \App\Mail\AppointmentMail($expiredPlanDetails));
                        $this->sendAllChannels($user, $expiredPlanDetails, 'User Plan Expired Notification');
                    } else {
                        Mail::to($user->email)->send(new \App\Mail\AppointmentMail($expiryPlanDetails));
                        $this->sendAllChannels($user, $expiryPlanDetails, 'User Plan Expiry Remainder');
                    }
                } catch (\Exception $e) {
                    // $this->error("Failed to send email to {$user->email}: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Fire all four notification channels (Twilio WhatsApp, MSG91 WhatsApp,
     * Twilio SMS, MSG91 SMS) for a user, if each channel's plugin is
     * installed, configured, and its template is enabled.
     */
    private function sendAllChannels($user, array $planDetails, string $templateName)
    {
        if (empty($user->billing_phone) || empty($user->billing_country_code)) {
            return;
        }

        $this->sendTwilioWhatsapp($user, $planDetails, $templateName);
        $this->sendMsg91Whatsapp($user, $planDetails, $templateName);
        $this->sendTwilioSms($user, $planDetails, $templateName);
        $this->sendMsg91Sms($user, $planDetails, $templateName);
    }

    /**
     * Send a Twilio WhatsApp notification, if the plugin is active and the
     * template is enabled.
     */
    private function sendTwilioWhatsapp($user, array $planDetails, string $templateName)
    {
        if (! DB::table('twilio_whatsapp_notification_settings')->exists() || ! plugin_active('TwilioWhatsappNotification')) {
            return;
        }

        $notification_template = DB::table('twilio_whatsapp_notification_templates')->where('template_name', $templateName)->first();

        if (! $notification_template || $notification_template->is_enabled != 1) {
            return;
        }

        $twilio_notification_details = DB::table('twilio_whatsapp_notification_settings')->first();
        $phone_code                  = ltrim($user->billing_country_code, '+');

        $sid   = $twilio_notification_details->account_sid;
        $token = $twilio_notification_details->auth_token;

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $data = [
            'From'             => 'whatsapp:+' . $twilio_notification_details->from_number,
            'To'               => 'whatsapp:+' . $phone_code . $user->billing_phone,
            'ContentSid'       => $notification_template->template_sid,
            'ContentVariables' => json_encode($this->contentVariables($user, $planDetails)),
        ];

        Http::withBasicAuth($sid, $token)->asForm()->post($url, $data);
    }

    /**
     * Send a Twilio SMS notification, if the plugin is active and the
     * template is enabled.
     */
    private function sendTwilioSms($user, array $planDetails, string $templateName)
    {
        if (! DB::table('twilio_sms_notification_settings')->exists() || ! plugin_active('TwilioSmsNotification')) {
            return;
        }

        $notification_template = DB::table('twilio_sms_notification_templates')->where('template_name', $templateName)->first();

        if (! $notification_template || $notification_template->is_enabled != 1) {
            return;
        }

        $twilio_notification_details = DB::table('twilio_sms_notification_settings')->first();
        $phone_code                  = ltrim($user->billing_country_code, '+');

        $sid   = $twilio_notification_details->account_sid;
        $token = $twilio_notification_details->auth_token;

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

        $data = [
            'From'             => '+' . $twilio_notification_details->from_number,
            'To'               => '+' . $phone_code . $user->billing_phone,
            'ContentSid'       => $notification_template->template_sid,
            'ContentVariables' => json_encode($this->contentVariables($user, $planDetails)),
        ];

        Http::withBasicAuth($sid, $token)->asForm()->post($url, $data);
    }

    /**
     * Send an MSG91 WhatsApp notification, if the plugin is active and the
     * template is enabled.
     */
    private function sendMsg91Whatsapp($user, array $planDetails, string $templateName)
    {
        if (! DB::table('msg91_whatsapp_notification_settings')->exists() || ! plugin_active('MSG91WhatsappNotification')) {
            return;
        }

        $notification_template = DB::table('msg91_whatsapp_notification_templates')->where('template_name', $templateName)->first();

        if (! $notification_template || $notification_template->is_enabled != 1) {
            return;
        }

        $msg91_notification_details = DB::table('msg91_whatsapp_notification_settings')->first();

        $auth_key  = $msg91_notification_details->auth_key;
        $sender_id = $msg91_notification_details->sender_id;

        $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

        $components = $this->buildComponents($notification_template->variables, $this->contentVariables($user, $planDetails));

        $payload = [
            "integrated_number" => $sender_id,
            "content_type"      => "template",
            "payload"           => [
                "messaging_product" => "whatsapp",
                "type"              => "template",
                "template"          => [
                    "name"              => $notification_template->template_id,
                    "language"          => [
                        "code"   => "en",
                        "policy" => "deterministic",
                    ],
                    "namespace"         => $notification_template->namespace,
                    "to_and_components" => [
                        [
                            "to"         => [$user->billing_country_code . $user->billing_phone],
                            "components" => $components,
                        ],
                    ],
                ],
            ],
        ];

        Http::withHeaders([
            'authkey'      => $auth_key,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
    }

    /**
     * Send an MSG91 SMS notification, if the plugin is active and the
     * template is enabled.
     */
    private function sendMsg91Sms($user, array $planDetails, string $templateName)
    {
        if (! DB::table('msg91_sms_notification_settings')->exists() || ! plugin_active('MSG91SmsNotification')) {
            return;
        }

        $notification_template = DB::table('msg91_sms_notification_templates')->where('template_name', $templateName)->first();

        if (! $notification_template || $notification_template->is_enabled != 1) {
            return;
        }

        $msg91_notification_details = DB::table('msg91_sms_notification_settings')->first();
        $phone_code                 = ltrim($user->billing_country_code, '+');

        $auth_key  = $msg91_notification_details->auth_key;
        $sender_id = $msg91_notification_details->sender_id;

        $url = "https://control.msg91.com/api/v5/flow/";

        $payload = [
            "flow_id" => $notification_template->template_id,
            "sender"  => $sender_id,
            "mobiles" => $phone_code . $user->billing_phone,
            "vars"    => $this->contentVariables($user, $planDetails),
        ];

        Http::withHeaders([
            'authkey'      => $auth_key,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
    }

    /**
     * Common set of template variables shared across all four channels.
     */
    private function contentVariables($user, array $planDetails)
    {
        return [
            "app_name"    => config('app.name'),
            "name"        => $user->name,
            "email"       => $user->email,
            "plan_name"   => $planDetails['planName'],
            "expiry_date" => $planDetails['expiryDate'],
        ];
    }

    /**
     * Build the MSG91 WhatsApp "components" (body_1, body_2, ...) block from
     * a template's declared variable list and a map of available values.
     */
    private function buildComponents($templateVariablesJson, array $valueMap)
    {
        $variables  = json_decode($templateVariablesJson, true) ?: [];
        $components = [];

        foreach ($variables as $index => $variable) {
            // body_1, body_2, ...
            $key = "body_" . ($index + 1);

            $components[$key] = [
                "type"  => "text",
                "value" => $valueMap[$variable] ?? '',
            ];
        }

        return $components;
    }
}
