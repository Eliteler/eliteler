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

namespace App\Http\Controllers\Admin;

use App\EmailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mews\Purifier\Facades\Purifier;

/**
 * System email templates (email_templates). The 25 rows ship with the app
 * and are only edited here, never created or deleted. Content is HTML
 * cleaned with Purifier; ":placeholders" are filled in when the email is sent.
 */
class EmailTemplateController extends Controller
{
    /** Placeholder => sample value used in the live preview. */
    private const SHORTCODES = [
        'Customer' => [
            ':customername' => 'Asha Rao', ':customeremail' => 'asha@example.com', ':receivername' => 'Asha Rao',
            ':receiveremail' => 'asha@example.com', ':receiverphone' => '+1 555 0100', ':receivermessage' => 'Looking forward to it!',
            ':registeredname' => 'Asha Rao', ':registeredemail' => 'asha@example.com',
        ],
        'Appointments' => [
            ':status' => 'Confirmed', ':appointmentdate' => '12 Oct 2026', ':appointmenttime' => '10:30 AM', ':appointmentpageurl' => '#',
            ':googlecalendarurl' => '#', ':checkindate' => '12 Oct 2026', ':checkintime' => '2:00 PM', ':checkoutdate' => '14 Oct 2026',
            ':checkouttime' => '11:00 AM', ':servicebookingpageurl' => '#',
        ],
        'Orders' => [
            ':orderid' => 'OD-10482', ':cardname' => 'Matte black NFC card', ':cardprice' => '$29.00', ':paymentstatus' => 'Paid',
            ':deliverystatus' => 'Shipped', ':quantity' => '2', ':trackingnumber' => '1Z999AA10123456784', ':courierpartner' => 'UPS',
            ':orderpageurl' => '#', ':totalprice' => '$58.00',
        ],
        'Plans' => [':planname' => 'Business', ':plancode' => 'BIZ-12', ':planprice' => '$99.00', ':expirydate' => '31 Dec 2026'],
        'System' => [
            ':hyperlink' => '#', ':vcardname' => 'Asha Rao Design', ':appname' => null, ':previousdomain' => 'old.example.com',
            ':currentdomain' => 'card.example.com', ':otpnumber' => '482913', ':supportemail' => 'support@example.com',
            ':supportphone' => '+1 555 0199', ':actionlink' => '#',
        ],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function emailTemplatesIndex($id)
    {
        $template = EmailTemplate::where('email_template_id', $id)->first();

        if (! $template) {
            $first = EmailTemplate::orderBy('id')->value('email_template_id');

            return $first ? redirect()->route('admin.email.templates.index', $first) : redirect()->route('admin.dashboard')->with('failed', __('No email templates found.'));
        }

        return Inertia::render('settings/email-templates', [
            'templates'  => EmailTemplate::orderBy('id')->get(['email_template_id', 'email_template_name', 'is_enabled'])->map(fn ($row) => [
                'id'      => $row->email_template_id,
                'name'    => __($row->email_template_name),
                'enabled' => (bool) $row->is_enabled,
            ]),
            'template'   => [
                'id'      => $template->email_template_id,
                'name'    => __($template->email_template_name),
                'subject' => (string) $template->email_template_subject,
                'content' => (string) $template->email_template_content,
                'enabled' => (bool) $template->is_enabled,
            ],
            'shortcodes' => collect(self::SHORTCODES)->map(fn ($codes, $group) => [
                'group' => __($group),
                'codes' => collect($codes)->map(fn ($sample, $code) => ['code' => $code, 'sample' => $sample ?? config('app.name')])->values(),
            ])->values(),
            'previewCss' => $this->emailCss(),
        ]);
    }

    public function updateEmailTemplateContent(Request $request)
    {
        $template = EmailTemplate::where('email_template_id', (string) $request->input('email_template_id'))->firstOrFail();

        $data = $request->validate([
            'email_template_subject' => 'required|string|max:255',
            'email_template_content' => 'required|string|max:65000',
            'is_enabled'             => 'required|boolean',
        ], [], [
            'email_template_subject' => __('subject'),
            'email_template_content' => __('message'),
        ]);

        $template->email_template_subject = $data['email_template_subject'];
        $template->email_template_content = Purifier::clean($data['email_template_content']);
        $template->is_enabled             = $request->boolean('is_enabled');
        $template->save();

        return back()->with('success', __(':name saved.', ['name' => __($template->email_template_name)]));
    }

    /** Styles of the email layout, so the preview matches what customers get. */
    private function emailCss(): string
    {
        $layout = @file_get_contents(resource_path('views/emails/appointment.blade.php')) ?: '';

        return preg_match('#<style[^>]*>(.*?)</style>#s', $layout, $match) ? trim($match[1]) : '';
    }
}
