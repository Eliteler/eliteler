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

use App\Plan;
use App\User;
use Carbon\Carbon;
use App\Transaction;
use App\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function authenticated(Request $request)
    {
        $config = GoBizCommonService::config()->pluck('config_value', 'config_key');
        $existingUser = Auth::user();

        if (
            data_get($config, 'disable_user_email_verification') == '1'
            && $existingUser
            && (int) $existingUser->role_id === 2
            && is_null($existingUser->email_verified_at)
        ) {
            Auth::logout();

            return redirect('/login')
                ->with('error', trans('Your email address is not verified.'));
        }

        // Custom redirect support
        if ($request->redirect && $existingUser->role_id == 2) {
            return redirect()->to($request->redirect);
        }

        // Role-based redirect
        if ($existingUser->role_id != 2) {
            return redirect('/admin/dashboard');
        }

        // check modern_dashboard_settings table is available
        if (Schema::hasTable('modern_dashboard_settings')) {
            // check modern_dashboard_enabled
            $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;

            // if enabled redirect to react dashboard
            if (is_dir(base_path('plugins/ModernDashboard')) && $is_modern_dashboard_enabled == 1) {
                return redirect()->route('user.dashboard.overview');
            } else {
                // if disabled redirect to default dashboard
                return redirect()->route('user.dashboard');
            }
        } else {
            // if table not available redirect to default dashboard
            return redirect()->route('user.dashboard');
        }
    }

    public function showLoginForm()
    {
        $config = GoBizCommonService::config();
        $settings = GoBizCommonService::settings();

        $google_configuration = [
            'GOOGLE_ENABLE' => env('GOOGLE_ENABLE', ''),
            'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID', ''),
            'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET', ''),
            'GOOGLE_REDIRECT' => env('GOOGLE_REDIRECT', '')
        ];

        $recaptcha_configuration = [
            'RECAPTCHA_ENABLE' => env('RECAPTCHA_ENABLE', ''),
            'RECAPTCHA_SITE_KEY' => env('RECAPTCHA_SITE_KEY', ''),
            'RECAPTCHA_SECRET_KEY' => env('RECAPTCHA_SECRET_KEY', '')
        ];

        $settings['google_configuration'] = $google_configuration;
        $settings['recaptcha_configuration'] = $recaptcha_configuration;

        return view('auth.login', compact('config', 'settings'));
    }

    public function redirectToProvider(Request $request)
    {
        // check platform
        $platform = $request->query('platform', 'web');
        session(['platform' => $platform]);

        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback()
    {
        // Queries
        $config = GoBizCommonService::config();

        $platform = session('platform', 'web');

        try {
            // Using stateless() to avoid session issues if needed
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            if ($platform == 'app') {
                return redirect()->away("gobiz://auth/login");
            } else {
                return redirect('/login')->with('error', trans('Google login failed.'));
            }
        }

        $rawEmail = $googleUser->getEmail();
        $deletedEmail = $rawEmail . 'deleted';

        // First check the deleted version in DB
        $deletedUser = User::where('email', $deletedEmail)->first();

        if ($deletedUser) {
            return redirect('/login')->with('deleted_account_message', trans('deleted_account_message') . ' <a href="' . route('contact') . '"><strong>' . trans('contact support') . '</strong></a>.');
        }

        // Now clean the email for normal check
        $email = $rawEmail;

        if (str_contains($email, 'deleted')) {
            $email = str_replace('deleted', '', $email);
            $email = trim($email);
        }

        // Check if user exists in the database
        $existingUser = User::where('email', $email)->where('status', 1)->first();

        if ($existingUser) {
            // check if user is active
            if ($existingUser->status == 1) {
                Auth::login($existingUser, true);

                // check platform
                if ($platform == 'app') {
                    $token = Str::random(60);

                    // Old tokens
                    $tokens = json_decode($existingUser->auth_token, true);
                    // Check if tokens is array
                    if (!is_array($tokens)) {
                        $tokens = [];
                    }

                    // Append new token
                    $tokens[] = $token;

                    // Save updated tokens
                    $existingUser->auth_token = json_encode(array_values($tokens));
                    $existingUser->device_token = $request->device_token ?? null;
                    $existingUser->save();

                    // return response
                    return redirect()->away("gobiz://auth/callback?token={$token}&email={$existingUser->email}");
                } else {
                    // check modern_dashboard_enabled
                    if (Schema::hasTable('modern_dashboard_settings')) {
                        $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
                        if (is_dir(base_path('plugins/ModernDashboard')) && $is_modern_dashboard_enabled == 1) {
                            return redirect()->route('user.dashboard.overview');
                        } else {
                            return redirect()->route('user.dashboard');
                        }
                    } else {
                        return redirect()->route('user.dashboard');
                    }
                }
            } else {
                if ($platform == 'app') {
                    return redirect()->away("gobiz://auth/login");
                } else {
                    return redirect('/login')->with('error', trans('Your account is inactive.'));
                }
            }
        } else {
            // Create user data
            $userData = [
                'name' => $googleUser->getName(),
                'lang' => config('app.locale') ?? 'en',
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'user_id' => $googleUser->getId(),
                'profile_image' => $googleUser->getAvatar(),
                'password' => bcrypt($googleUser->getId()),
                'auth_type' => 'Google',
                'role_id' => 2,
                'status' => 1,
            ];

            // Create new user
            $newUser = User::create($userData);

            // Activate plan during registeration
            $activate_plan_during_registeration = $config[94]->config_value ?? '0';

            // if activate plan during registeration is enabled
            if ($activate_plan_during_registeration == '1') {
                // check free plan available in db
                $free_plan = Plan::where('plan_price', 0)->where('status', 1)->first();

                // if free plan available
                if ($free_plan) {
                    // create one transaction for free plan
                    $transaction = new Transaction();
                    $transaction->gobiz_transaction_id = uniqid();
                    $transaction->transaction_date = now();
                    $transaction->transaction_id = uniqid();
                    $transaction->user_id = $newUser->id;
                    $transaction->plan_id = $free_plan->plan_id;
                    $transaction->desciption = $free_plan->plan_name . " Plan";
                    $transaction->payment_gateway_name = "FREE";
                    $transaction->transaction_amount = $free_plan->plan_price;
                    $transaction->transaction_currency = $config[1]->config_value;
                    $transaction->invoice_details = json_encode([]);
                    $transaction->payment_status = "SUCCESS";
                    $transaction->save();

                    // set plan validity
                    $plan_validity = Carbon::now();
                    $plan_validity->addDays((int) $free_plan->validity);

                    // update user
                    $user = User::where('user_id', $googleUser->getId())->first();

                    $user->plan_id = $free_plan->plan_id;
                    $user->term = $free_plan->validity;
                    $user->plan_validity = $plan_validity;
                    $user->plan_activation_date = now();
                    $user->plan_details = $free_plan;
                    $user->save();
                }
            }

            // Get appointment pending email template content
            $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675208')->first();

            $message = [
                'status' => "",
                'emailSubject' => $emailTemplateDetails->email_template_subject,
                'emailContent' => $emailTemplateDetails->email_template_content,
                'registeredName' => $googleUser->getName(),
                'registeredEmail' => $googleUser->getEmail(),
            ];

            // Booking mail sent to customer
            if ($emailTemplateDetails->is_enabled == 1) {
                try {
                    // Welcome email
                    Mail::to($googleUser->getEmail())->bcc(env('MAIL_FROM_ADDRESS'))->send(new \App\Mail\AppointmentMail($message));

                    // Check email verification system is enabled
                    if ($config[43]->config_value == "1") {
                        // Send email verification
                        $newUser->newEmail($googleUser->getEmail());
                    }
                } catch (\Exception $e) {
                }
            }

            Auth::login($newUser, true);

            // check platform
            if ($platform == 'app') {
                $token = Str::random(60);

                // Old tokens
                $tokens = json_decode($newUser->auth_token, true);
                // Check if tokens is array
                if (!is_array($tokens)) {
                    $tokens = [];
                }

                // Append new token
                $tokens[] = $token;

                // Save updated tokens
                $newUser->auth_token = json_encode(array_values($tokens));
                $newUser->save();

                // return response
                return redirect()->away("gobiz://auth/callback?token={$token}&email={$newUser->email}");
            } else {
                // check modern_dashboard_enabled
                if (Schema::hasTable('modern_dashboard_settings')) {
                    $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
                    if (is_dir(base_path('plugins/ModernDashboard')) && $is_modern_dashboard_enabled == 1) {
                        return redirect()->route('user.dashboard.overview');
                    } else {
                        return redirect()->route('user.dashboard');
                    }
                } else {
                    return redirect()->route('user.dashboard');
                }
            }
        }
    }
}
