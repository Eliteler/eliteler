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

namespace App\Http\Controllers\Auth;

use App\User;
use App\Setting;
use Carbon\Carbon;
use App\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    public function showLinkRequestForm()
    {
        $config = DB::table('config')->get();
        $settings = Setting::first();

        $google_configuration = [
            'GOOGLE_ENABLE' => env('GOOGLE_ENABLE', ''),
            'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID', ''),
            'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET', ''),
            'GOOGLE_REDIRECT' => env('GOOGLE_REDIRECT', '')
        ];

        $settings['google_configuration'] = $google_configuration;

        return view('auth.passwords.email', compact('config', 'settings'));
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->whereNotIn('status', [-1, 2])->first();

        if (!$user) {
            return back()->withErrors(['email' => trans('We can\'t find a user with that email address.')]);
        }

        $token = Str::random(64);

        // Store token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $actionLink = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

        $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675212')->first();

        $details = [
            'emailSubject' => $emailTemplateDetails->email_template_subject,
            'emailContent' => $emailTemplateDetails->email_template_content,
            'appname' => config('app.name'),
            'actionlink' => $actionLink,
        ];

        try {
            Mail::to($user->email)->send(new \App\Mail\AppointmentMail($details));
        } catch (\Exception $e) {
            return back()->with('email', $e->getMessage());
        }

        return back()->with('status', trans('We have emailed your password reset link!'));
    }
}
