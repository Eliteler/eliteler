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

namespace App\Http\Controllers\User;

use App\User;
use App\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    // Verified email
    public function verifyEmailVerification()
    {
        // Update
        User::where('id', auth()->user()->id)->update([
            'email_verified_at' => now(),
        ]);

        // Send Welcome Email
        $emailTemplate = EmailTemplate::where('email_template_id', '584922675208')->first();

        if ($emailTemplate && $emailTemplate->is_enabled == 1) {
            try {
                $message = [
                    'status'         => '',
                    'emailSubject'   => $emailTemplate->email_template_subject,
                    'emailContent'   => $emailTemplate->email_template_content,
                    'registeredName' => auth()->user()->name,
                    'registeredEmail' => auth()->user()->email,
                ];

                // Send welcome email
                Mail::to(auth()->user()->email)
                    ->bcc(env('MAIL_FROM_ADDRESS'))
                    ->send(new \App\Mail\AppointmentMail($message));
            } catch (\Exception $e) {
                Log::error('Email Sending Error : ' . $e->getMessage());
            }
        }

        // Page redirect
        return redirect()->route('user.dashboard');
    }

    // Resend Email Verification
    public function resendEmailVerification()
    {
        // Queries
        $user = User::where('id', Auth::user()->id)->where('status', 1)->first();
        // Send Email
        try {
            try {
                // Retrieve config by key instead of index for safer access
                $config = DB::table('config')->pluck('config_value', 'config_key');

                if (($config['disable_user_email_verification'] ?? '0') == '1') {
                    try {
                        // Get Welcome email template
                        $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675213')->first();

                        // Generate signed verification link
                        $verificationUrl = URL::temporarySignedRoute(
                            'verification.verify.public',
                            now()->addMinutes(60),
                            [
                                'id' => $user->id,
                                'hash' => sha1($user->email),
                            ]
                        );

                        $message = [
                            'status' => "",
                            'emailSubject' => $emailTemplateDetails->email_template_subject,
                            'emailContent' => $emailTemplateDetails->email_template_content,
                            'actionlink' => $verificationUrl,
                        ];

                        // Send email (using built-in Mailable or custom one)
                        Mail::to($user->email)->send(new \App\Mail\AppointmentMail($message));

                        // $this->newEmail($this->getEmailForVerification());
                    } catch (\Exception $e) {
                        Log::error('Email verification notification failed', [
                            'user_id' => $this->id ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Email verification notification failed', [
                    'user_id' => $this->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('failed', trans('Email service not available.'));
        }

        // Page redirect
        return redirect()->back()->with('success', trans('Mail Sent.'));
    }
}
