<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace Plugins\MSG91WhatsappNotification\Observers;

use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MSG91WhatsappNotificationObserver
{
    public function created($model)
    {
        // msg91 notification settings
        $msg91_notification_details = DB::table('msg91_whatsapp_notification_settings')->first();

        // check msg91 credentials are not empty
        if (! $msg91_notification_details || ! $msg91_notification_details->auth_key || ! $msg91_notification_details->sender_id || ! $msg91_notification_details->admin_number) {
            return;
        }

        // notification template
        $notification_template = DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'New User Registration Admin')->first();

        // check if user registration is enabled
        try {
            if ($model instanceof User && $notification_template->is_enabled == 1) {
                $auth_key  = $msg91_notification_details->auth_key;
                $sender_id = $msg91_notification_details->sender_id;

                $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

                $variables = json_decode($notification_template->variables, true);

                $name = $model->name;
                $email = $model->email;
                $app_name = config('app.name');

                $components = [];

                foreach ($variables as $index => $variable) {
                    // body_1, body_2, ...
                    $key = "body_" . ($index + 1);

                    // get value dynamically
                    switch ($variable) {
                        case 'app_name':
                            $value = $app_name;
                            break;
                        case 'name':
                            $value = $name;
                            break;
                        case 'email':
                            $value = $email;
                            break;
                        default:
                            $value = '';
                            break;
                    }

                    $components[$key] = [
                        "type"  => "text",
                        "value" => $value,
                    ];
                }

                $payload = [
                    "integrated_number" => $sender_id,
                    "content_type"      => "template",
                    "payload"           => [
                        "messaging_product" => "whatsapp",
                        "type"              => "template",
                        "template"          => [
                            "name"      => $notification_template->template_id,
                            "language"  => [
                                "code"   => "en",
                                "policy" => "deterministic",
                            ],
                            "namespace" => $notification_template->namespace,
                            "to_and_components" => [
                                [
                                    "to"        => [$msg91_notification_details->admin_number],
                                    "components" => $components,
                                ]
                            ]
                        ]
                    ]
                ];

                Http::withHeaders([
                    'authkey'      => $auth_key,
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);
            }
        } catch (\Exception $e) {
        }
    }

    public function updated($model)
    {
        // msg91 notification settings
        $msg91_notification_details = DB::table('msg91_whatsapp_notification_settings')->first();

        // check msg91 credentials are not empty
        if (! $msg91_notification_details || ! $msg91_notification_details->auth_key || ! $msg91_notification_details->sender_id || ! $msg91_notification_details->admin_number) {
            return;
        }

        // check if plan details is changed
        try {
            if ($model instanceof User && $model->isDirty('plan_details')) {
                // get old plan details
                $oldPlan = json_decode($model->getOriginal('plan_details'), true);
                // get new plan details
                $newPlan = json_decode($model->plan_details, true);
               
                $total_price = Transaction::orderBy('id', 'desc')->where('user_id', $model->id)->where('plan_id', $newPlan['plan_id'])->first()->transaction_amount;

                if ($oldPlan == null) { // new plan purchase
                    $notification_template_admin_purchase = DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Purchase Admin')->first();
                    $notification_template_user_purchase  = DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Purchase User')->first();
                    // Add Name
                    $app_name = config('app.name');
                    // Get Currency
                    $currency = DB::table('config')->where('config_key', 'currency')->first()->config_value;
                    // Get Expiry Date
                    $expiry_date = Carbon::parse($model->plan_validity)->format('d/m/Y');

                    // Admin Notification
                    if ($notification_template_admin_purchase->is_enabled == 1) {
                        $auth_key  = $msg91_notification_details->auth_key;
                        $sender_id = $msg91_notification_details->sender_id;

                        $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

                        $name = $model->name;
                        $email = $model->email;
                        $app_name = config('app.name');

                        $components = [];

                        $variables = json_decode($notification_template_admin_purchase->variables, true);

                        foreach ($variables as $index => $variable) {
                            // body_1, body_2, ...
                            $key = "body_" . ($index + 1);

                            // get value dynamically
                            switch ($variable) {
                                case 'app_name':
                                    $value = $app_name;
                                    break;
                                case 'name':
                                    $value = $name;
                                    break;
                                case 'email':
                                    $value = $email;
                                    break;
                                case 'plan_name':
                                    $value = $newPlan['plan_name'];
                                    break;
                                case 'currency':
                                    $value = $currency;
                                    break;
                                case 'plan_price':
                                    $value = (string) $total_price;
                                    break;
                                case 'plan_validity':
                                    $value = (string) $newPlan['validity'];
                                    break;
                                case 'plan_expiry_date':
                                    $value = $expiry_date;
                                    break;
                                default:
                                    $value = '';
                                    break;
                            }

                            $components[$key] = [
                                "type"  => "text",
                                "value" => $value,
                            ];
                        }

                        $payload = [
                            "integrated_number" => $sender_id,
                            "content_type"      => "template",
                            "payload"           => [
                                "messaging_product" => "whatsapp",
                                "type"              => "template",
                                "template"          => [
                                    "name"      => $notification_template_admin_purchase->template_id,
                                    "language"  => [
                                        "code"   => "en",
                                        "policy" => "deterministic",
                                    ],
                                    "namespace" => $notification_template_admin_purchase->namespace,
                                    "to_and_components" => [
                                        [
                                            "to"        => [$msg91_notification_details->admin_number],
                                            "components" => $components,
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        Http::withHeaders([
                            'authkey'      => $auth_key,
                            'Content-Type' => 'application/json',
                        ])->post($url, $payload);
                    }

                    // User Notification
                    if ($notification_template_user_purchase->is_enabled == 1) {
                        $auth_key  = $msg91_notification_details->auth_key;
                        $sender_id = $msg91_notification_details->sender_id;

                        $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

                        $name = $model->name;
                        $email = $model->email;
                        $app_name = config('app.name');

                        $variables = json_decode($notification_template_user_purchase->variables, true);

                        $components = [];

                        foreach ($variables as $index => $variable) {
                            // body_1, body_2, ...
                            $key = "body_" . ($index + 1);

                            // get value dynamically
                            switch ($variable) {
                                case 'app_name':
                                    $value = $app_name;
                                    break;
                                case 'name':
                                    $value = $name;
                                    break;
                                case 'email':
                                    $value = $email;
                                    break;
                                case 'plan_name':
                                    $value = $newPlan['plan_name'];
                                    break;
                                case 'currency':
                                    $value = $currency;
                                    break;
                                case 'plan_price':
                                    $value = (string) $total_price;
                                    break;
                                case 'plan_validity':
                                    $value = (string) $newPlan['validity'];
                                    break;
                                case 'plan_expiry_date':
                                    $value = $expiry_date;
                                    break;
                                default:
                                    $value = '';
                                    break;
                            }

                            $components[$key] = [
                                "type"  => "text",
                                "value" => $value,
                            ];
                        }

                        $payload = [
                            "integrated_number" => $sender_id,
                            "content_type"      => "template",
                            "payload"           => [
                                "messaging_product" => "whatsapp",
                                "type"              => "template",
                                "template"          => [
                                    "name"      => $notification_template_user_purchase->template_id,
                                    "language"  => [
                                        "code"   => "en",
                                        "policy" => "deterministic",
                                    ],
                                    "namespace" => $notification_template_user_purchase->namespace,
                                    "to_and_components" => [
                                        [
                                            "to"        => [$model->billing_phone],
                                            "components" => $components,
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        Http::withHeaders([
                            'authkey'      => $auth_key,
                            'Content-Type' => 'application/json',
                        ])->post($url, $payload);
                    }
                } elseif ($oldPlan != null) { // plan renewal
                    $notification_template_admin_renewal = DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Renewal Admin')->first();
                    $notification_template_user_renewal  = DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Renewal User')->first();

                    // Add Name
                    $name = $model->name;
                    $email = $model->email;
                    $app_name = config('app.name');

                    // Get Currency
                    $currency = DB::table('config')->where('config_key', 'currency')->first()->config_value;
                    // Get Expiry Date
                    $expiry_date = Carbon::parse($model->plan_validity)->format('d/m/Y');

                    // Admin Notification
                    if ($notification_template_admin_renewal->is_enabled == 1) {
                        $auth_key  = $msg91_notification_details->auth_key;
                        $sender_id = $msg91_notification_details->sender_id;

                        $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

                        $components = [];

                        $variables = json_decode($notification_template_admin_renewal->variables, true);

                        foreach ($variables as $index => $variable) {
                            // body_1, body_2, ...
                            $key = "body_" . ($index + 1);

                            // get value dynamically
                            switch ($variable) {
                                case 'app_name':
                                    $value = $app_name;
                                    break;
                                case 'name':
                                    $value = $name;
                                    break;
                                case 'email':
                                    $value = $email;
                                    break;
                                case 'plan_name':
                                    $value = $newPlan['plan_name'];
                                    break;
                                case 'currency':
                                    $value = $currency;
                                    break;
                                case 'plan_price':
                                    $value = (string) $total_price;
                                    break;
                                case 'plan_validity':
                                    $value = (string) $newPlan['validity'];
                                    break;
                                case 'plan_expiry_date':
                                    $value = $expiry_date;
                                    break;
                                default:
                                    $value = '';
                                    break;
                            }

                            $components[$key] = [
                                "type"  => "text",
                                "value" => $value,
                            ];
                        }

                        $payload = [
                            "integrated_number" => $sender_id,
                            "content_type"      => "template",
                            "payload"           => [
                                "messaging_product" => "whatsapp",
                                "type"              => "template",
                                "template"          => [
                                    "name"      => $notification_template_admin_renewal->template_id,
                                    "language"  => [
                                        "code"   => "en",
                                        "policy" => "deterministic",
                                    ],
                                    "namespace" => $notification_template_admin_renewal->namespace,
                                    "to_and_components" => [
                                        [
                                            "to"        => [$msg91_notification_details->admin_number],
                                            "components" => $components,
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        Http::withHeaders([
                            'authkey'      => $auth_key,
                            'Content-Type' => 'application/json',
                        ])->post($url, $payload);
                    }

                    // User Notification
                    if ($notification_template_user_renewal->is_enabled == 1) {
                        $auth_key  = $msg91_notification_details->auth_key;
                        $sender_id = $msg91_notification_details->sender_id;

                        $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

                        $components = [];

                        $variables = json_decode($notification_template_user_renewal->variables, true);

                        foreach ($variables as $index => $variable) {
                            // body_1, body_2, ...
                            $key = "body_" . ($index + 1);

                            // get value dynamically
                            switch ($variable) {
                                case 'app_name':
                                    $value = $app_name;
                                    break;
                                case 'name':
                                    $value = $name;
                                    break;
                                case 'email':
                                    $value = $email;
                                    break;
                                case 'plan_name':
                                    $value = $newPlan['plan_name'];
                                    break;
                                case 'currency':
                                    $value = $currency;
                                    break;
                                case 'plan_price':
                                    $value = (string) $total_price;
                                    break;
                                case 'plan_validity':
                                    $value = (string) $newPlan['validity'];
                                    break;
                                case 'plan_expiry_date':
                                    $value = $expiry_date;
                                    break;
                                default:
                                    $value = '';
                                    break;
                            }

                            $components[$key] = [
                                "type"  => "text",
                                "value" => $value,
                            ];
                        }

                        $payload = [
                            "integrated_number" => $sender_id,
                            "content_type"      => "template",
                            "payload"           => [
                                "messaging_product" => "whatsapp",
                                "type"              => "template",
                                "template"          => [
                                    "name"      => $notification_template_user_renewal->template_id,
                                    "language"  => [
                                        "code"   => "en",
                                        "policy" => "deterministic",
                                    ],
                                    "namespace" => $notification_template_user_renewal->namespace,
                                    "to_and_components" => [
                                        [
                                            "to"        => [$model->billing_phone],
                                            "components" => $components,
                                        ]
                                    ]
                                ]
                            ]
                        ];

                        Http::withHeaders([
                            'authkey'      => $auth_key,
                            'Content-Type' => 'application/json',
                        ])->post($url, $payload);
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
}
