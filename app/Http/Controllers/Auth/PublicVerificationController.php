<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Carbon\Carbon;
use App\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;

class PublicVerificationController extends Controller
{
    // Show public verification page
    public function show(Request $request) 
    {
        $email = $request->query('email');
        $web_template = getConfigData('web_template');
 
        return view($web_template . '::Website.pages.auth.verify-public', ['email' => $email]);
    }

    // Resend verification email
    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', trans('Email not found.'));
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', trans('Email already verified.'));
        }

        // Generate signed verification link
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify.public',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Get Welcome email template
        $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675213')->first();

        if (!$emailTemplateDetails) {
            return back()->with('error', trans('Unable to send verification email. Please try again later.'));
        }

        $message = [
            'status' => "",
            'emailSubject' => $emailTemplateDetails->email_template_subject,
            'emailContent' => $emailTemplateDetails->email_template_content,
            'actionlink' => $verificationUrl,
        ];

        // Send email (using built-in Mailable or custom one)
        try {
            Mail::to($user->email)->send(new \App\Mail\AppointmentMail($message));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', trans('Unable to send verification email. Please try again later.'));
        }

        return back()->with('status', trans('Verification link sent to your email!'));
    }

    // Verify email without login
    public function verify($id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('/login')->with('status', trans('Your email is already verified.'));
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        // Welcome email is held back until the address is verified
        $user->sendWelcomeEmail();

        return redirect('/login')->with('status', trans('Your email has been verified successfully!'));
    }
}
