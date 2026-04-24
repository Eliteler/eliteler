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

namespace Plugins\MSG91WhatsappNotification\Controllers;

use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MSG91WhatsappNotificationController extends Controller
{
    public function msg91WhatsappNotificationSettings(Request $request)
    {
        // check database and create table if not exists
        if (! DB::table('information_schema.tables')
            ->where('table_schema', config('database.connections.mysql.database'))
            ->where('table_name', 'msg91_whatsapp_notification_settings')
            ->exists()) {

            // Create table
            DB::statement("CREATE TABLE `msg91_whatsapp_notification_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `auth_key` VARCHAR(255) NULL,
            `sender_id` VARCHAR(255) NULL,
            `admin_number` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
        }

        // Check if table msg91_whatsapp_notification_templates exists
        if (! DB::table('information_schema.tables')
            ->where('table_schema', config('database.connections.mysql.database'))
            ->where('table_name', 'msg91_whatsapp_notification_templates')
            ->exists()) {

            // Create table
            DB::statement("CREATE TABLE `msg91_whatsapp_notification_templates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `template_name` VARCHAR(255) NULL,
            `template_id` VARCHAR(255) NULL,
            `namespace` VARCHAR(255) NULL,
            `variables` JSON NULL,
            `is_enabled` BOOLEAN DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");

            // insert default template
            DB::table('msg91_whatsapp_notification_templates')->insert([
                [
                    'id'            => 1,
                    'template_name' => 'New User Registration Admin',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 2,
                    'template_name' => 'Plan Purchase Admin',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 3,
                    'template_name' => 'Plan Purchase User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 4,
                    'template_name' => 'Plan Renewal Admin',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 5,
                    'template_name' => 'Plan Renewal User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 6,
                    'template_name' => 'User Plan Expiry Remainder',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 7,
                    'template_name' => 'User Plan Expired Notification',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 8,
                    'template_name' => 'New Appointment Notification User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 9,
                    'template_name' => 'New Appointment Notification Customer',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 10,
                    'template_name' => 'Appointment Confirmed Notification User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 11,
                    'template_name' => 'Appointment Confirmed Notification Customer',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 12,
                    'template_name' => 'Appointment Cancelled Notification User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 13,
                    'template_name' => 'Appointment Cancelled Notification Customer',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 14,
                    'template_name' => 'Appointment Rescheduled Notification User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 15,
                    'template_name' => 'Appointment Rescheduled Notification Customer',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 16,
                    'template_name' => 'Appointment Completed Notification User',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ],
                [
                    'id'            => 17,
                    'template_name' => 'Appointment Completed Notification Customer',
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ]
            ]);
        }

        // check the namespace column available or not
        if (! DB::table('information_schema.columns')
            ->where('table_schema', config('database.connections.mysql.database'))
            ->where('table_name', 'msg91_whatsapp_notification_templates')
            ->where('column_name', 'namespace')
            ->exists()) {
            DB::statement("ALTER TABLE `msg91_whatsapp_notification_templates` ADD `namespace` VARCHAR(255) NULL AFTER template_id");
        }

        // check the variables column available or not
        if (! DB::table('information_schema.columns')
            ->where('table_schema', config('database.connections.mysql.database'))
            ->where('table_name', 'msg91_whatsapp_notification_templates')
            ->where('column_name', 'variables')
            ->exists()) {
            DB::statement("ALTER TABLE `msg91_whatsapp_notification_templates` ADD `variables` JSON NULL AFTER namespace");
        }

        $msg91_whatsapp_notification_settings  = DB::table('msg91_whatsapp_notification_settings')->first();

        $newTemplates = [
            'New Appointment Notification User',
            'New Appointment Notification Customer',
            'Appointment Confirmed Notification User',
            'Appointment Confirmed Notification Customer',
            'Appointment Cancelled Notification User',
            'Appointment Cancelled Notification Customer',
            'Appointment Rescheduled Notification User',
            'Appointment Rescheduled Notification Customer',
            'Appointment Completed Notification User',
            'Appointment Completed Notification Customer',
        ];

        $existingTemplates = DB::table('msg91_whatsapp_notification_templates')
            ->pluck('template_name')
            ->toArray();

        $missingTemplates = array_diff($newTemplates, $existingTemplates);

        if (!empty($missingTemplates)) {
            $insertData = [];

            foreach ($missingTemplates as $name) {
                $insertData[] = [
                    'template_name' => $name,
                    'template_id'  => '',
                    'namespace'    => '',
                    'variables'    => json_encode([]),
                    'is_enabled'    => 0,
                ];
            }

            DB::table('msg91_whatsapp_notification_templates')->insert($insertData);
        }

        $msg91_whatsapp_notification_templates = DB::table('msg91_whatsapp_notification_templates')->get()->toArray();
        $settings                               = Setting::where('id', 1)->first();

        return view()->file(base_path('plugins/MSG91WhatsappNotification/Views/index.blade.php'), compact('msg91_whatsapp_notification_settings', 'msg91_whatsapp_notification_templates', 'settings'));
    }

    public function msg91WhatsappNotificationSettingsUpdate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'auth_key'  => 'required',
            'sender_id'   => 'required',
            'admin_number'   => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('failed', __('Validation failed.'));
        }

        // Update or insert
        DB::table('msg91_whatsapp_notification_settings')->updateOrInsert(
            ['id' => 1],
            [
                'auth_key'  => $request->auth_key,
                'sender_id'   => $request->sender_id,
                'admin_number' => $request->admin_number,
                'updated_at'   => now(),
            ]
        );

        return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('success', __('MSG91 Whatsapp Notification Settings updated successfully.'));
    }

    public function msg91WhatsappTemplateUserRegisterUpdate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'new_user_registration_admin_template_id' => 'required',
            'new_user_registration_admin'              => 'required',
            'new_user_registration_admin_template_namespace' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('failed', __('Validation failed.'));
        }
        $template_id = $request->new_user_registration_admin_template_id;
        $is_enabled   = $request->new_user_registration_admin;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'New User Registration Admin')->update([
            'template_id' => $template_id,
            'namespace'   => $request->new_user_registration_admin_template_namespace,
            'variables'   => $request->variables,
            'is_enabled'   => $is_enabled,
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('success', __('MSG91 Whatsapp Template User Register updated successfully.'));
    }

    public function msg91WhatsappTemplatePlanPurchaseUpdate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'plan_purchase_admin' => 'required',
            'plan_purchase_user'  => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('failed', __('Validation failed.'));
        }
        // For Admin
        $admin_template_id = $request->plan_purchase_admin_template_id;
        $admin_is_enabled   = $request->plan_purchase_admin;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Purchase Admin')->update([
            'template_id' => $admin_template_id,
            'namespace'   => $request->plan_purchase_admin_template_namespace,
            'variables'   => $request->variablesAdmin,
            'is_enabled'   => $admin_is_enabled,
            'updated_at'   => now(),
        ]);

        // For User
        $user_template_id = $request->plan_purchase_user_template_id;
        $user_is_enabled   = $request->plan_purchase_user;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Purchase User')->update([
            'template_id' => $user_template_id,
            'namespace'   => $request->plan_purchase_user_template_namespace,
            'variables'   => $request->variablesUser,
            'is_enabled'   => $user_is_enabled,
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('success', __('MSG91 Whatsapp Template Plan Purchase updated successfully.'));
    }

    public function msg91WhatsappTemplatePlanRenewalUpdate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'plan_renewal_admin' => 'required',
            'plan_renewal_user'  => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('failed', __('Validation failed.'));
        }
        // For Admin
        $admin_template_id = $request->plan_renewal_admin_template_id;
        $admin_is_enabled   = $request->plan_renewal_admin;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Renewal Admin')->update([
            'template_id' => $admin_template_id,
            'namespace'   => $request->plan_renewal_admin_template_namespace,
            'variables'   => $request->variablesAdmin,
            'is_enabled'   => $admin_is_enabled,
            'updated_at'   => now(),
        ]);

        // For User
        $user_template_id = $request->plan_renewal_user_template_id;
        $user_is_enabled   = $request->plan_renewal_user;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'Plan Renewal User')->update([
            'template_id' => $user_template_id,
            'namespace'   => $request->plan_renewal_user_template_namespace,
            'variables'   => $request->variablesUser,
            'is_enabled'   => $user_is_enabled,
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('success', __('MSG91 Whatsapp Template Plan Renewal updated successfully.'));
    }

    public function msg91WhatsappTemplateUserPlanExpiryRemainderUpdate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'user_plan_expiry_remainder' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('failed', __('Validation failed.'));
        }

        $template_id = $request->user_plan_expiry_remainder_template_id;
        $is_enabled   = $request->user_plan_expiry_remainder;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'User Plan Expiry Remainder')->update([
            'template_id' => $template_id,
            'namespace'   => $request->user_plan_expiry_remainder_template_namespace,
            'variables'   => $request->variables,
            'is_enabled'   => $is_enabled,
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('success', __('MSG91 Whatsapp Template User Plan Expiry Remainder updated successfully.'));
    }

    public function msg91WhatsappTemplateUserExpiredUpdate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'user_plan_expired_notification'              => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('failed', __('Validation failed.'));
        }
        $template_id = $request->user_plan_expired_notification_template_id;
        $is_enabled   = $request->user_plan_expired_notification;

        // Update or insert
        DB::table('msg91_whatsapp_notification_templates')->where('template_name', 'User Plan Expired Notification')->update([
            'template_id' => $template_id,
            'namespace'   => $request->user_plan_expired_notification_template_namespace,
            'variables'   => $request->variables,
            'is_enabled'   => $is_enabled,
            'updated_at'   => now(),
        ]);

        return redirect()->route('admin.plugin.msg91_whatsapp_notification.settings')->with('success', __('MSG91 Whatsapp Template User Plan Expired Notification updated successfully.'));
    }

    public function msg91WhatsappTemplateAppointmentUpdate(Request $request)
    {
        $sections = [
            'new_appointment' => [
                'user' => 'New Appointment Notification User',
                'customer' => 'New Appointment Notification Customer',
            ],
            'appointment_confirmed' => [
                'user' => 'Appointment Confirmed Notification User',
                'customer' => 'Appointment Confirmed Notification Customer',
            ],
            'appointment_cancelled' => [
                'user' => 'Appointment Cancelled Notification User',
                'customer' => 'Appointment Cancelled Notification Customer',
            ],
            'appointment_rescheduled' => [
                'user' => 'Appointment Rescheduled Notification User',
                'customer' => 'Appointment Rescheduled Notification Customer',
            ],
            'appointment_completed' => [
                'user' => 'Appointment Completed Notification User',
                'customer' => 'Appointment Completed Notification Customer',
            ],
        ];

        foreach ($sections as $key => $types) {
            foreach ($types as $type => $templateName) {
                $isEnabled = $request->input("{$key}_{$type}_is_enabled", 0);
                $templateId = $request->input("{$key}_{$type}_template_id");
                $namespace = $request->input("{$key}_{$type}_template_namespace");
                $variables = $request->input("{$key}_{$type}_variables", '[]');

                // validation
                if ($isEnabled == 1 && (empty($templateId) || empty($namespace))) {
                    return redirect()
                        ->route('admin.plugin.msg91_whatsapp_notification.settings')
                        ->with('failed', __("Template ID and Namespace are required when notification is enabled."));
                }

                DB::table('msg91_whatsapp_notification_templates')
                    ->where('template_name', $templateName)
                    ->update([
                        'template_id' => $templateId,
                        'namespace'   => $namespace,
                        'variables'   => $variables,
                        'is_enabled'  => $isEnabled,
                        'updated_at'  => now(),
                    ]);
            }
        }

        return redirect()
            ->route('admin.plugin.msg91_whatsapp_notification.settings')
            ->with('success', __('Appointment notification templates updated successfully.'));
    }
}
