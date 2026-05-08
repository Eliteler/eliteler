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

namespace Plugins\MSG91WhatsappNotification\Observers;

use App\BookedAppointment;
use App\BusinessCard;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MSG91WhatsappAppointmentNotificationObserver
{
    public function created($model)
    {
        // msg91 notification settings
        $msg91_notification_details = DB::table('msg91_whatsapp_notification_settings')->first();

        // check msg91 credentials are not empty
        if (! $msg91_notification_details || ! $msg91_notification_details->auth_key || ! $msg91_notification_details->sender_id || ! $msg91_notification_details->admin_number) {
            return;
        }

        try {
            // check if appointment is created
            if (!($model instanceof BookedAppointment)) {
                return;
            }

            // template details
            $templates = DB::table('msg91_whatsapp_notification_templates')
                ->whereIn('template_name', [
                    'New Appointment Notification User',
                    'New Appointment Notification Customer'
                ])->get()->keyBy('template_name');

            $templateUser = $templates['New Appointment Notification User'] ?? null;
            $templateCustomer = $templates['New Appointment Notification Customer'] ?? null;

            // check if templates are enabled
            $userEnabled = ($templateUser->is_enabled ?? 0) == 1;
            $customerEnabled = ($templateCustomer->is_enabled ?? 0) == 1;

            // check if templates are enabled
            if (!$userEnabled && !$customerEnabled) {
                return;
            }

            $businessCard = BusinessCard::where('card_id', $model->card_id)->first();
            if (!$businessCard) return;

            $vcardUser = User::where('user_id', $businessCard->user_id)->first();
            if (!$vcardUser) return;

            $authKey  = $msg91_notification_details->auth_key;
            $senderId = $msg91_notification_details->sender_id;
            $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

            $dataMap = [
                'app_name' => config('app.name'),
                'name' => $model->name,
                'email' => $model->email,
                'phone' => null,
                'appointment_date' => formatDateOnlyForUser($model->booking_date),
                'appointment_time' => $model->booking_time,
                'notes' => $model->notes ?? '-',
            ];

            $sendMessage = function ($phone, $template) use ($dataMap, $authKey, $senderId, $url) {
                if (empty($phone) || empty($template)) return;

                $variables = json_decode($template->variables ?? '[]', true);
                $components = [];

                foreach ($variables as $i => $var) {
                    $value = $dataMap[$var] ?? '';
                    if ($var === 'phone') $value = $phone;

                    $components["body_" . ($i + 1)] = [
                        "type" => "text",
                        "value" => $value,
                    ];
                }

                $payload = [
                    "integrated_number" => $senderId,
                    "content_type" => "template",
                    "payload" => [
                        "messaging_product" => "whatsapp",
                        "type" => "template",
                        "template" => [
                            "name" => $template->template_id,
                            "language" => [
                                "code" => "en",
                                "policy" => "deterministic",
                            ],
                            "namespace" => $template->namespace,
                            "to_and_components" => [[
                                "to" => [$phone],
                                "components" => $components,
                            ]]
                        ]
                    ]
                ];

                Http::withHeaders([
                    'authkey' => $authKey,
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);
            };

            if (($templateUser->is_enabled ?? 0) == 1) {
                $userPhone = $vcardUser->whatsapp_number ?? $vcardUser->billing_phone;
                $sendMessage($userPhone, $templateUser);
            }

            if (($templateCustomer->is_enabled ?? 0) == 1) {
                $sendMessage($model->phone, $templateCustomer);
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

        try {
            if ($model instanceof BookedAppointment) {
                // Appointment Reschedule Notifications
                if ($model->wasChanged('booking_date') || $model->wasChanged('booking_time')) {
                    // template details
                    $templates = DB::table('msg91_whatsapp_notification_templates')
                        ->whereIn('template_name', [
                            'Appointment Rescheduled Notification User',
                            'Appointment Rescheduled Notification Customer'
                        ])->get()->keyBy('template_name');

                    $templateUser = $templates['Appointment Rescheduled Notification User'] ?? null;
                    $templateCustomer = $templates['Appointment Rescheduled Notification Customer'] ?? null;

                    // check if templates are enabled
                    $userEnabled = ($templateUser->is_enabled ?? 0) == 1;
                    $customerEnabled = ($templateCustomer->is_enabled ?? 0) == 1;

                    // check if templates are enabled
                    if (!$userEnabled && !$customerEnabled) {
                        return;
                    }

                    $businessCard = BusinessCard::where('card_id', $model->card_id)->first();
                    if (!$businessCard) return;

                    $vcardUser = User::where('user_id', $businessCard->user_id)->first();
                    if (!$vcardUser) return;

                    $authKey  = $msg91_notification_details->auth_key;
                    $senderId = $msg91_notification_details->sender_id;
                    $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";

                    $dataMap = [
                        'app_name' => config('app.name'),
                        'name' => $model->name,
                        'email' => $model->email,
                        'phone' => null,
                        'appointment_date' => formatDateOnlyForUser($model->getOriginal('booking_date')),
                        'appointment_time' => $model->getOriginal('booking_time'),
                        'appointment_rescheduled_date' => formatDateOnlyForUser($model->booking_date),
                        'appointment_rescheduled_time' => $model->booking_time,
                        'notes' => $model->notes ?? '-'
                    ];

                    $sendMessage = function ($phone, $template) use ($dataMap, $authKey, $senderId, $url) {
                        if (empty($phone) || empty($template)) return;

                        $variables = json_decode($template->variables ?? '[]', true);
                        $components = [];

                        foreach ($variables as $i => $var) {
                            $value = $dataMap[$var] ?? '';
                            if ($var === 'phone') $value = $phone;

                            $components["body_" . ($i + 1)] = [
                                "type" => "text",
                                "value" => $value,
                            ];
                        }

                        $payload = [
                            "integrated_number" => $senderId,
                            "content_type" => "template",
                            "payload" => [
                                "messaging_product" => "whatsapp",
                                "type" => "template",
                                "template" => [
                                    "name" => $template->template_id,
                                    "language" => [
                                        "code" => "en",
                                        "policy" => "deterministic",
                                    ],
                                    "namespace" => $template->namespace,
                                    "to_and_components" => [[
                                        "to" => [$phone],
                                        "components" => $components,
                                    ]]
                                ]
                            ]
                        ];

                        Http::withHeaders([
                            'authkey' => $authKey,
                            'Content-Type' => 'application/json',
                        ])->post($url, $payload);
                    };

                    if (($templateUser->is_enabled ?? 0) == 1) {
                        $userPhone = $vcardUser->whatsapp_number ?? $vcardUser->billing_phone;
                        $sendMessage($userPhone, $templateUser);
                    }

                    if (($templateCustomer->is_enabled ?? 0) == 1) {
                        $sendMessage($model->phone, $templateCustomer);
                    }
                    // avoid duplicate message when reschedule, return after sending message
                    return;
                }

                // Appointment Notifications (Confirmed, Completed, Cancelled)
                if ($model->wasChanged('booking_status')) {
                    $authKey  = $msg91_notification_details->auth_key;
                    $senderId = $msg91_notification_details->sender_id;
                    $url = "https://control.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/";
                    

                    $statusTemplates = [
                        -1 => [
                            'user' => 'Appointment Cancelled Notification User',
                            'customer' => 'Appointment Cancelled Notification Customer',
                        ],
                        1 => [
                            'user' => 'Appointment Confirmed Notification User',
                            'customer' => 'Appointment Confirmed Notification Customer',
                        ],
                        2 => [
                            'user' => 'Appointment Completed Notification User',
                            'customer' => 'Appointment Completed Notification Customer',
                        ],
                    ];

                    if (!isset($statusTemplates[$model->booking_status])) {
                        return;
                    }

                    $templateNames = $statusTemplates[$model->booking_status];

                    $templates = DB::table('msg91_whatsapp_notification_templates')
                        ->whereIn('template_name', array_values($templateNames))
                        ->get()
                        ->keyBy('template_name');

                    $templateUser = $templates[$templateNames['user']] ?? null;
                    $templateCustomer = $templates[$templateNames['customer']] ?? null;

                    if (!$templateUser && !$templateCustomer) return;

                    if (
                        ($templateUser->is_enabled ?? 0) != 1 &&
                        ($templateCustomer->is_enabled ?? 0) != 1
                    ) {
                        return;
                    }

                    $businessCard = BusinessCard::where('card_id', $model->card_id)->first();
                    if (!$businessCard) return;

                    $vcardUser = User::where('user_id', $businessCard->user_id)->first();
                    if (!$vcardUser) return;

                    $dataMap = [
                        'app_name' => config('app.name'),
                        'name' => $model->name,
                        'email' => $model->email,
                        'phone' => null,
                        'appointment_date' => formatDateOnlyForUser($model->getOriginal('booking_date')),
                        'appointment_time' => $model->getOriginal('booking_time'),
                        'notes' => $model->notes ?? '-'
                    ];

                    $sendMessage = function ($phone, $template) use ($dataMap, $authKey, $senderId, $url) {
                        if (empty($phone) || empty($template)) return;
                        if (empty($template->template_id) || empty($template->namespace)) return;

                        $variables = json_decode($template->variables ?? '[]', true);
                        $components = [];

                        foreach ($variables as $i => $var) {
                            $value = $dataMap[$var] ?? '';
                            if ($var === 'phone') $value = $phone;

                            $components["body_" . ($i + 1)] = [
                                "type" => "text",
                                "value" => $value,
                            ];
                        }

                        $payload = [
                            "integrated_number" => $senderId,
                            "content_type" => "template",
                            "payload" => [
                                "messaging_product" => "whatsapp",
                                "type" => "template",
                                "template" => [
                                    "name" => $template->template_id,
                                    "language" => [
                                        "code" => "en",
                                        "policy" => "deterministic",
                                    ],
                                    "namespace" => $template->namespace,
                                    "to_and_components" => [[
                                        "to" => [$phone],
                                        "components" => $components,
                                    ]]
                                ]
                            ]
                        ];

                        Http::withHeaders([
                            'authkey' => $authKey,
                            'Content-Type' => 'application/json',
                        ])->post($url, $payload);
                    };

                    if (($templateUser->is_enabled ?? 0) == 1) {
                        $userPhone = $vcardUser->whatsapp_number ?? $vcardUser->billing_phone;
                        $sendMessage($userPhone, $templateUser);
                    }

                    if (($templateCustomer->is_enabled ?? 0) == 1) {
                        $sendMessage($model->phone, $templateCustomer);
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
}
