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

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmailTempleteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Appointment (Pending)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675196',
            'email_template_name' => 'Appointment (Pending)',
            'email_template_subject' => 'Your Appointment is Pending',
            'email_template_content' => <<<'HTML'
                    <div class="header">
                        <h4>Appointment Pendings</h4>
                        <p>Your appointment request is pending and will be confirmed shortly.</p>
                    </div>
                    HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Appointment (Confirmed)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675197',
            'email_template_name' => 'Appointment (Confirmed)',
            'email_template_subject' => 'Your Appointment has been Confirmed',
            'email_template_content' => <<<'HTML'
            <div class="header">
            <h1>Appointment Confirmed</h1>
            <p>Your appointment has been successfully scheduled!</p>
            </div>
            <br />
            <div class="content">
            <p>Hi,</p>
            <p>We are pleased to confirm your appointmentDate with :hyperlink. Please find the details below:</p>
            <p><strong>Date:</strong> :appointmentdate</p>
            <p><strong>Time:</strong> :appointmenttime</p>
            <br />
            <p>If you have any questions, need to reschedule, or need further assistance, please don\'t hesitate to contact :hyperlink.</p>
            <p>Thank you for choosing our service!</p>
            <br />
            <p><strong>You can add this appointment to your Google Calendar by clicking the link below:</strong></p>
            <a class="button" href=":googlecalendarurl" target="_blank" rel="noopener">Add to Google Calendar</a>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Appointment (Canceled)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675198',
            'email_template_name' => 'Appointment (Canceled)',
            'email_template_subject' => 'Your Appointment has been Canceled',
            'email_template_content' => <<<'HTML'
            <div class="header">
                <h1>Appointment Canceled</h1>
                <p>Your appointment has been successfully canceled.</p>
            </div>
            <br>

            <div class="content">
                <p>Hi,</p>
                <p>We are sorry to inform you that your appointment has been canceled.</p>
                <br>
                <p>If you have any questions or need further assistance, please don\'t hesitate to contact :hyperlink.</p>
                <p>Thank you for choosing our service!</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Appointment (Rescheduled)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675199',
            'email_template_name' => 'Appointment (Rescheduled)',
            'email_template_subject' => 'Your Appointment has been Rescheduled',
            'email_template_content' => <<<'HTML'
            <div class="header">
                <h1>Appointment Rescheduled</h1>
                <p>Your appointment has been successfully rescheduled.</p>
            </div>
            <br>

            <div class="content">
                <p>Hi,</p>
                <p>We are sorry to inform you that your appointment has been rescheduled. Please find the new appointment details below:</p>
                <p><strong>Date:</strong> :appointmentdate</p>
                <p><strong>Time:</strong> :appointmenttime</p>
                <br>
                <p>If you have any questions, need to reschedule, or need further assistance, please don\'t hesitate to contact :hyperlink.</p>
                <p>Thank you for choosing our service!</p>

                <p><strong>You can add this appointment to your Google Calendar by clicking the link below:</strong></p>
                <a class="button" href=":googlecalendarurl" target="_blank" rel="noopener">Add to Google Calendar</a>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Appointment (Completed)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675200',
            'email_template_name' => 'Appointment (Completed)',
            'email_template_subject' => 'Your Appointment has been Completed',
            'email_template_content' => <<<'HTML'
            <div class="header">
                <h1>Appointment Completed</h1>
                <p>Your appointment has been successfully completed. Thank you for using our services!</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Appointment (Received)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675201',
            'email_template_name' => 'Appointment (Received vCard Owner)',
            'email_template_subject' => 'New Appointment Request',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">New Appointment Request</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    You have received a new appointment request from
                    <span class="highlight">:customername</span>.
                    Here are the details:
                </p>

                <!-- Appointment Details -->
                <div class="appointment-details">
                    <p>Appointment Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Date:</span> :appointmentdate</li>
                        <li class="list-item"><span>Time:</span> :appointmenttime</li>
                    </ul>
                </div>

                <!-- View Appointment Button -->
                <a href=":appointmentpageurl" class="button">
                    View Appointment
                </a>

                <p class="text">Thank you for using our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Custom Domain Processed
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675202',
            'email_template_name' => 'Custom Domain Processed',
            'email_template_subject' => 'Your domain request has been successfully processed',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">Custom Domain Processed</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    Your domain request has been successfully processed.
                </p>
                <p class="text">
                    Here are the details of the processed domain:
                </p>

                <!-- Domain Details -->
                <div class="domain-details">
                    <p>Domain Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Previous Domain:</span> <a
                                href="https://:previousdomain"
                                target="_blank">:previousdomain</a></li>
                        <li class="list-item"><span>Current Domain:</span> <a
                                href="https://:currentdomain"
                                target="_blank">:currentdomain</a></li>
                    </ul>
                </div>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Custom Domain Approval
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675203',
            'email_template_name' => 'Custom Domain Approval',
            'email_template_subject' => 'Your domain request has been approved',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">Custom Domain Approval</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    Your domain request has been successfully approved.
                </p>
                <p class="text">
                    Here are the details of your approved domain:
                </p>

                <!-- Domain Details -->
                <div class="domain-details">
                    <p>Domain Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Previous Domain:</span> <a
                                href="https://:previousdomain"
                                target="_blank">:previousdomain</a></li>
                        <li class="list-item"><span>Current Domain:</span> <a
                                href="https://:currentdomain"
                                target="_blank">:currentdomain</a></li>
                    </ul>
                </div>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Custom Domain Rejection
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675204',
            'email_template_name' => 'Custom Domain Rejection',
            'email_template_subject' => 'Your domain request has been rejected',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">Custom Domain Rejection</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    Your domain request has been rejected.
                </p>
                <p class="text">
                    Here are the details of the rejected domain:
                </p>

                <!-- Domain Details -->
                <div class="domain-details">
                    <p>Domain Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Previous Domain:</span> <a
                                href="https://:previousdomain"
                                target="_blank">:previousdomain</a></li>
                        <li class="list-item"><span>Current Domain:</span> <a
                                href="https://:currentdomain"
                                target="_blank">:currentdomain</a></li>
                    </ul>
                </div>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // vCard Enquiry email
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675205',
            'email_template_name' => 'Service / Product Enquiry',
            'email_template_subject' => 'New Service / Product Enquiry',
            'email_template_content' => <<<HTML
            <div class="email-content">
                <p class="text">Hello :vcardname,</p>

                <p>You have received a new inquiry. Its details are as follows:</p>
                <p class="text">Name: :receivername</p>
                <p class="text">Email: :receiveremail</p>
                <p class="text">Phone: :receiverphone</p>
                <p class="text">Message: :receivermessage</p>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Plan Expired Email Template
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675206',
            'email_template_name' => 'Plan Expired',
            'email_template_subject' => 'Your plan has been expired',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">Plan Expired</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    Your plan has been expired.
                </p>
                <p class="text">
                    Here are the details of the expired plan:
                </p>

                <div class="plan-details">
                    <p>Plan Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Plan Name:</span> :planname</li>
                        <li class="list-item"><span>Expiry Date:</span> :expirydate</li>
                    </ul>
                </div>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Your plan is about to expire soon.
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675207',
            'email_template_name' => 'Your plan is about to expire soon',
            'email_template_subject' => 'Your plan is about to expire soon',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">Your plan is about to expire soon.</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    Your plan is about to expire soon.
                </p>
                <p class="text">
                    Here are the details of the expired plan:
                </p>

                <div class="plan-details">
                    <p>Plan Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Plan Name:</span> :planname</li>
                        <li class="list-item"><span>Expiry Date:</span> :expirydate</li>
                    </ul>
                </div>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Welcome email template
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675208',
            'email_template_name' => 'Welcome',
            'email_template_subject' => 'Welcome to :appname',
            'email_template_content' => <<<'HTML'
            <div class="container">
                <h2 class="title">Welcome to :appname</h2>
                <p class="text">Hello,</p>
                <p class="text">
                    Welcome to :appname.
                </p>
                <p class="text">
                    Here are the details of your account:
                </p>

                <div class="account-details">
                    <p>Account Details:</p>
                    <ul class="list-none">
                        <li class="list-item"><span>Name:</span> :registeredname</li>
                        <li class="list-item"><span>Email:</span> :registeredemail</li>
                    </ul>
                </div>

                <p class="text">Thank you for choosing our service!</p>
                <p class="text">Best regards,</p>
                <p class="highlight">:appname</p>
            </div>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // NFC Card Order Confirmation (Customer)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675209',
            'email_template_name' => 'NFC Card Order Confirmation (Customer)',
            'email_template_subject' => 'Order Confirmation',
            'email_template_content' => <<<'HTML'
            <table role="presentation" class="email-container" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="center">
                        <table role="presentation" class="email-content" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td class="email-header">
                                    <h1>Order Confirmation</h1>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-body">
                                    <p>Thank you for your order! Your NFC card is being processed.</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-details">
                                    <p>Order Details:</p>
                                    <ul>
                                        <li><strong>Order ID:</strong> #:orderid</li>
                                        <li><strong>Card Design:</strong> :cardname</li>
                                        <li><strong>Quantity:</strong> :quantity</li>
                                        <li><strong>Total Price:</strong> :totalprice</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <a href=":orderpageurl" class="view-order-button">View Order</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-footer">
                                    <p>If you have any questions, contact us at <a href="mailto:\:supportemail">:supportemail</a></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // NFC Card Order Delivery Status
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675210',
            'email_template_name' => 'NFC Card Order Delivery Status',
            'email_template_subject' => 'Update on your order status',
            'email_template_content' => <<<'HTML'
            <table role="presentation" class="email-container" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="center">
                        <table role="presentation" class="email-content" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td class="email-header">
                                    <h1>Order Delivery Status</h1>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-body">
                                    <p>Your NFC card order has been :deliverystatus!</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-details">
                                    <p>Delivery Details:</p>
                                    <ul>
                                        <li><strong>Order ID:</strong> #:orderid</li>
                                        <li><strong>Shipping Carrier:</strong> :courierpartner</li>
                                        <li><strong>Tracking Number:</strong> :trackingnumber</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <a href=":orderpageurl" class="track-button">Track Order</a>
                                </td>                            
                            </tr>
                            <tr>
                                <td class="email-footer">
                                    <p>If you have any questions, contact us at <a href="mailto:\:supportemail">:supportemail</a></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // New NFC Card Order (Website Owner)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675211',
            'email_template_name' => 'New NFC Card Order (Website Owner)',
            'email_template_subject' => 'New NFC Card Order',
            'email_template_content' => <<<'HTML'
            <table role="presentation" class="email-container" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="center">
                        <table role="presentation" class="email-content" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td class="email-header">
                                    <h1>New NFC Card Order</h1>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-body">
                                    <p>A new NFC card order has been placed on your website.</p>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-details">
                                    <p>Order Details:</p>
                                    <ul>
                                        <li><strong>Order ID:</strong> #:orderid</li>
                                        <li><strong>Customer Name:</strong> :customername</li>
                                        <li><strong>Email:</strong> :customeremail</li>
                                        <li><strong>Card Design:</strong> :cardname</li>
                                        <li><strong>Quantity:</strong> :quantity</li>
                                        <li><strong>Total Price:</strong> :totalprice</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <a href=":orderpageurl" class="view-order-button">View Order</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="email-footer">
                                    <p>If you have any questions, contact support at <a href="mailto:\:supportemail">:supportemail</a></p>
                                </td>
                            </tr>                            
                        </table>
                    </td>
                </tr>
            </table>
            HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Forget Password
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675212',
            'email_template_name' => 'Forget Password',
            'email_template_subject' => 'Reset Password',
            'email_template_content' => <<<HTML
<table class="wrapper" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td class="body" style="background-color: #edf2f7; border-bottom: 1px solid #edf2f7; border-top: 1px solid #edf2f7; margin: 0; padding: 0; width: 100%;" width="100%">
    <table class="inner-body" style="background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025), 2px 4px 0 rgba(0, 0, 150, 0.015); margin: 0 auto; padding: 0; width: 570px;" role="presentation" width="570" cellspacing="0" cellpadding="0" align="center">
    <tbody>
    <tr>
    <td class="content-cell" style="max-width: 100vw; padding: 32px;">
    <h1 style="color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;">Hello :customername!</h1>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">You are receiving this email because we received a password reset request for your account.</p>
    <table class="action" style="margin: 30px auto; padding: 0; text-align: center; width: 100%;" role="presentation" width="100%" cellspacing="0" cellpadding="0" align="center">
    <tbody>
    <tr>
    <td align="center">
    <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td align="center">
    <table role="presentation" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td><a class="button button-primary" style="border-radius: 4px; color: #fff; display: inline-block; text-decoration: none; background-color: #2d3748; border: 8px solid #2d3748; padding: 8px 18px;" href=":actionlink" target="_blank" rel="noopener">Reset Password</a></td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">This password reset link will expire in 60 minutes.</p>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">If you did not request a password reset, no further action is required.</p>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">Regards,<br>GoBiz</p>
    <table class="subcopy" style="border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px;" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td>
    <p style="line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px;">If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: :actionlink</p>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
</table>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Verify Email Address
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675213',
            'email_template_name' => 'Verify Email Address',
            'email_template_subject' => 'Verify Email Address',
            'email_template_content' => <<<HTML
<table class="wrapper" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; background-color: #edf2f7; margin: 0; padding: 0; width: 100%;" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td class="body" style="background-color: #edf2f7; border-bottom: 1px solid #edf2f7; border-top: 1px solid #edf2f7; margin: 0; padding: 0; width: 100%;" width="100%">
    <table class="inner-body" style="background-color: #ffffff; border-color: #e8e5ef; border-radius: 2px; border-width: 1px; box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025), 2px 4px 0 rgba(0, 0, 150, 0.015); margin: 0 auto; padding: 0; width: 570px;" role="presentation" width="570" cellspacing="0" cellpadding="0" align="center">
    <tbody>
    <tr>
    <td class="content-cell" style="max-width: 100vw; padding: 32px;">
    <h1 style="color: #3d4852; font-size: 18px; font-weight: bold; margin-top: 0; text-align: left;">Verify Email Address</h1>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">Please click the button below to verify your email address.</p>                                            
    <table class="action" style="margin: 30px auto; padding: 0; text-align: center; width: 100%;" role="presentation" width="100%" cellspacing="0" cellpadding="0" align="center">
    <tbody>
    <tr>
    <td align="center">
    <table role="presentation" border="0" width="100%" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td align="center">
    <table role="presentation" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
    <td><a class="button button-primary" style="border-radius: 4px; color: #fff; display: inline-block; text-decoration: none; background-color: #2d3748; border: 8px solid #2d3748; padding: 8px 18px;" href=":actionlink" target="_blank" rel="noopener">Verify Email Address</a></td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">If you did not create an account, no further action is required.</p>
    <p style="font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">Thanks,<br>:appname</p>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
</table>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // New Custom Domain Request
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675214',
            'email_template_name' => 'New Custom Domain Request',
            'email_template_subject' => 'You have received a new custom domain request',
            'email_template_content' => <<<HTML
<div class="container" style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; color: #333;">
    <p class="text" style="font-size: 16px;">Hello,</p>
    <p class="text" style="font-size: 16px;">You have received a new custom domain request from a customer.</p>
    <p class="text" style="font-size: 16px;">Please review and verify the request by visiting the verification link below.</p>
    <div class="verify-section" style="margin: 20px 0;">
      <a href=":actionlink" target="_blank" 
        style="display: inline-block; padding: 10px 20px; background-color: #2c7be5; color: #fff; text-decoration: none; border-radius: 5px;">
         Verify Domain Request
      </a>
    </div>
    <p class="text" style="font-size: 16px;">If you did not expect this request, please ignore this email.</p>
    <p class="text" style="font-size: 16px;">Best regards,</p>
    <p class="highlight" style="font-weight: bold; font-size: 16px;">:appname</p>
</div>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Service Booking (Pending)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675215',
            'email_template_name' => 'Service Booking (Pending)',
            'email_template_subject' => 'Your Service Booking is Pending',
            'email_template_content' => <<<'HTML'
           <div class=header><h1>Service Booking Pending</h1><p>Your service booking request is pending and will be confirmed shortly.</div>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Service Booking (Confirmed)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675216',
            'email_template_name' => 'Service Booking (Confirmed)',
            'email_template_subject' => 'Your Service Booking has been Confirmed',
            'email_template_content' => <<<'HTML'
            <div class=header><h1>Service Booking Confirmed</h1><p>Your service booking has been successfully confirmed!</div><br><div class=content><p>Hi,<p>We are pleased to confirm your booking Date with :hyperlink. Please find the details below:<p><strong>CheckIn Date:</strong> :checkindate<p><strong>CheckIn Time:</strong> :checkintime</p><br><p><strong>CheckOut Date:</strong> :checkoutdate<p><strong>CheckOut Time:</strong> :checkouttime</p><br><p>If you have any questions or need further assistance, please does not hesitate to contact :hyperlink.<p>Thank you for choosing our service!</p><br><p><strong>You can add this appointment to your Google Calendar by clicking the link below:</strong></p><a class=button href=:googlecalendarurl rel=noopener target=_blank>Add to Google Calendar</a></div>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Service Booking (Canceled)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675217',
            'email_template_name' => 'Service Booking (Canceled)',
            'email_template_subject' => 'Your Service Booking has been Canceled',
            'email_template_content' => <<<'HTML'
            <div class=header><h1>Service Booking Canceled</h1><p>Your service booking has been canceled.</div><br><div class=content><p>Hi,<p>We are sorry to inform you that your service booking has been canceled.</p><br><p>If you have any questions or need further assistance, please does not hesitate to contact :hyperlink.<p>Thank you for choosing our service!</div>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Service Booking (Completed)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675218',
            'email_template_name' => 'Service Booking (Completed)',
            'email_template_subject' => 'Your Service Booking has been Completed',
            'email_template_content' => <<<'HTML'
            <div class=header><h1>Service Booking Completed</h1><p>Your service booking has been successfully completed. Thank you for using our services!<p> <p><strong>Thanks,</strong><p>:appname</div>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);

        // Service Booking (Received vCard Owner)
        DB::table('email_templates')->insert([
            'email_template_id' => '584922675219',
            'email_template_name' => 'Service Booking (Received vCard Owner)',
            'email_template_subject' => 'New Service Booking Request',
            'email_template_content' => <<<'HTML'
            <div class=container><h2 class=title>New Service Booking Request</h2><p><p class=text>Hello,<p class=text>You have received a new service booking request from <span class=highlight>:customername</span>. Here are the details:<div class=appointment-details><p>Service Booking Details:<ul class=list-none><li class=list-item>CheckIn Date: :checkindate<li class=list-item>CheckIn Time: :checkintime</ul><ul class=list-none><li class=list-item>CheckOut Date: :checkoutdate<li class=list-item>CheckOut Time: :checkouttime</ul></div><a class=button href=%3Aservicebookingpageurl>View Booking</a><p class=text>Thank you for using our service!<p class=text><p class=text>Best regards,<p class=highlight>:appname</div>
HTML,
            'is_enabled' => 1,
            'status' => 1,
        ]);
    }
}
