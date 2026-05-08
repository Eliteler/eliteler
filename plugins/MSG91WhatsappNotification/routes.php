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

use Illuminate\Support\Facades\Route;
use Plugins\MSG91WhatsappNotification\Controllers\MSG91WhatsappNotificationController;

// Settings Update
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('admin/plugin/msg91_whatsapp_notification/settings', [MSG91WhatsappNotificationController::class, 'msg91WhatsappNotificationSettings'])->name('admin.plugin.msg91_whatsapp_notification.settings');
    Route::post('admin/plugin/msg91_whatsapp_notification/settings/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappNotificationSettingsUpdate'])->name('admin.msg91_whatsapp_notification_settings.update');

    //New Register Template Update
    Route::post('admin/plugin/msg91_whatsapp/template/user_register/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappTemplateUserRegisterUpdate'])->name('admin.msg91_whatsapp_template_user_register.update');

    //Plan Purchase Template Update
    Route::post('admin/plugin/msg91_whatsapp/template/plan_purchase/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappTemplatePlanPurchaseUpdate'])->name('admin.msg91_whatsapp_template_plan_purchase.update');

    //Plan Renewal Template Update
    Route::post('admin/plugin/msg91_whatsapp/template/plan_renewal/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappTemplatePlanRenewalUpdate'])->name('admin.msg91_whatsapp_template_plan_renewal.update');

    //User Plan Expiry Remainder Template Update
    Route::post('admin/plugin/msg91_whatsapp/template/user_plan_expiry_remainder/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappTemplateUserPlanExpiryRemainderUpdate'])->name('admin.msg91_whatsapp_template_user_plan_expiry_remainder.update');

    //User Expired Template Update
    Route::post('admin/plugin/msg91_whatsapp/template/user_plan_expired_notification/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappTemplateUserExpiredUpdate'])->name('admin.msg91_whatsapp_template_user_plan_expired_notification.update');

    // Appointment Template Update
    Route::post('admin/plugin/msg91_whatsapp/template/appointment/update', [MSG91WhatsappNotificationController::class, 'msg91WhatsappTemplateAppointmentUpdate'])->name('admin.msg91_whatsapp_template_appointment.update');
});
