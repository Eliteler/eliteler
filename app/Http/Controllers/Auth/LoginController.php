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
use App\NfcCardKey;
use App\Services\GoBizCommonService;
use App\Services\Security\DisposableEmailService;
use App\Services\Security\ReservedEmailService;
use App\Support\Impersonation;
use App\Support\Zapier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        logout as protected endSession;
    }

    /** Logging out while signed in as a customer/staff member goes back to the admin account. */
    public function logout(Request $request)
    {
        if (Impersonation::active()) {
            return Impersonation::redirect($request, Impersonation::leave($request));
        }

        return $this->endSession($request);
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo;

    /**
     * Create a new controller instance.
     * Exclude OAuth routes from the guest middleware to prevent in-app browser redirect traps.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'redirectToProvider', 'handleProviderCallback']);
    }

    /**
     * Validate the user login request including reCAPTCHA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string|max:100',
        ];

        // Check if reCAPTCHA is active in environment config
        $recaptchaEnabled = in_array(env('RECAPTCHA_ENABLE'), ['1', 'on', true, 'true'], true);

        if ($recaptchaEnabled) {
            $rules['g-recaptcha-response'] = 'required';
        }

        // Validate basic form input
        $request->validate($rules, [
            'g-recaptcha-response.required' => __('Please check the reCAPTCHA box.'),
        ]);

        // Verify with Google reCAPTCHA server
        if ($recaptchaEnabled) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]);

            // Halt login if verification response is unsuccessful
            if (!$response->json('success')) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => [__('reCAPTCHA verification failed. Please try again.')],
                ]);
            }
        }
    }

    /**
     * Attempt to log the user into the application.
     * Protects Google accounts from being accessed via public user_id passwords.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function attemptLogin(Request $request)
    {
        $user = User::where($this->username(), $request->input($this->username()))->first();

        // Refuse password login if account was created with Google and password equals public user_id
        if ($user && $user->auth_type === 'Google' && Hash::check($user->user_id, $user->password)) {
            throw ValidationException::withMessages([
                $this->username() => [__('This account was registered using Google. Please log in using Google or reset your password.')],
            ]);
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    public function authenticated(Request $request)
    {
        $config = GoBizCommonService::config()->pluck('config_value', 'config_key');
        $existingUser = Auth::user();

        // Staff deactivated in Administrators & staff can't sign in
        if ($existingUser && in_array((int) $existingUser->role_id, [3, 4], true) && (int) $existingUser->status !== 1) {
            Auth::logout();

            return redirect('/login')
                ->with('error', trans('Your admin account has been deactivated.'));
        }

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

        // NFC Card Key
        $nfc_ref = request()->get('nfc_ref');

        if ($nfc_ref) {
            $nfcKey = NfcCardKey::where('unqiue_key', $nfc_ref)->where('status', 1)->where('key_status', 'active')->first();

            if (empty($nfcKey)) {
                throw ValidationException::withMessages([
                    'email' => __('Invalid NFC Card Key Reference'),
                ]);
            }
            session()->put('nfc_ref', $nfc_ref);
        }

        if (Schema::hasTable('modern_dashboard_settings')) {
            $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;

            if (plugin_active('ModernDashboard') && $is_modern_dashboard_enabled == 1) {
                return redirect()->route('user.dashboard.overview');
            } else {
                return redirect()->route('user.dashboard');
            }
        } else {
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
        $platform = $request->query('platform', 'web');
        session(['platform' => $platform]);

        // If request originates from mobile app, clear any stale web session in the webview
        if ($platform == 'app' && Auth::check()) {
            Auth::logout();
        }

        // Encode platform in the state parameter to ensure it survives cross-site redirects
        $state = base64_encode(json_encode(['platform' => $platform]));

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account', 'state' => $state])
            ->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        $config = GoBizCommonService::config();

        // Retrieve platform from session or state parameter
        $platform = session('platform');
        if (!$platform && $request->has('state')) {
            $stateData = json_decode(base64_decode($request->get('state')), true);
            $platform = $stateData['platform'] ?? 'web';
        }
        $platform = $platform ?: 'web';

        try {
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

        // Check if account was deleted
        $deletedUser = User::where('email', $deletedEmail)->first();
        if ($deletedUser) {
            return redirect('/login')->with('deleted_account_message', trans('deleted_account_message') . ' <a href="' . route('contact') . '"><strong>' . trans('contact support') . '</strong></a>.');
        }

        $email = $rawEmail;
        if (str_contains($email, 'deleted')) {
            $email = str_replace('deleted', '', $email);
            $email = trim($email);
        }

        // Check if user exists in database
        $existingUser = User::where('email', $email)->where('status', 1)->first();

        if ($existingUser) {
            if ($existingUser->status == 1) {
                // Auto-healing: If user still has password == user_id, overwrite it with secure random password
                if (Hash::check($existingUser->user_id, $existingUser->password)) {
                    $existingUser->password = Hash::make(Str::random(32));
                    $existingUser->save();
                }

                // Handle mobile app platform login
                if ($platform == 'app') {
                    // Do NOT log into web session so webview stays clean
                    $token = Str::random(60);

                    $tokens = json_decode($existingUser->auth_token, true);
                    if (!is_array($tokens)) {
                        $tokens = [];
                    }

                    $tokens[] = $token;
                    $existingUser->auth_token = json_encode(array_values($tokens));
                    $existingUser->save();

                    // Redirect back to mobile app via deep link
                    return redirect()->away("gobiz://auth/callback?token={$token}&email={$existingUser->email}");
                } else {
                    // Authenticate web session only for web platform
                    Auth::login($existingUser, true);

                    if (Schema::hasTable('modern_dashboard_settings')) {
                        $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
                        if (plugin_active('ModernDashboard') && $is_modern_dashboard_enabled == 1) {
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
            // Prevent registration of reserved or privileged email addresses via OAuth
            $reservedCheck = ReservedEmailService::checkEmail($email);
            if (! $reservedCheck['allowed']) {
                if ($platform == 'app') {
                    return redirect()->away("gobiz://auth/login");
                }
                return redirect('/login')->with('error', $reservedCheck['message'] ?? trans('This email prefix is reserved. Please use a different email address.'));
            }

            // Prevent registration of disposable or bot email domains via OAuth
            $disposableCheck = DisposableEmailService::checkEmail($email);
            if (! $disposableCheck['allowed']) {
                if ($platform == 'app') {
                    return redirect()->away("gobiz://auth/login");
                }
                return redirect('/login')->with('error', $disposableCheck['message'] ?? trans('Disposable or temporary email addresses are not permitted. Please use a valid email.'));
            }

            // Generate standard secure unique user_id
            $newUserId = uniqid();

            // Create user data with secure random password
            $userData = [
                'name' => $googleUser->getName(),
                'lang' => config('app.locale') ?? 'en',
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'user_id' => $newUserId,
                'profile_image' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(32)),
                'auth_type' => 'Google',
                'role_id' => 2,
                'status' => 1,
            ];

            // Create new user
            $newUser = User::create($userData);

            // Zapier notification
            Zapier::newUser($newUser, $platform == 'app' ? 'Customer (Google, Mobile App)' : 'Customer (Google)');

            // Free plan activation during registration
            $activate_plan_during_registeration = $config[94]->config_value ?? '0';

            if ($activate_plan_during_registeration == '1') {
                $free_plan = Plan::where('plan_price', 0)->where('status', 1)->first();

                if ($free_plan) {
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

                    $plan_validity = Carbon::now();
                    $plan_validity->addDays((int) $free_plan->validity);

                    $newUser->plan_id = $free_plan->plan_id;
                    $newUser->term = $free_plan->validity;
                    $newUser->plan_validity = $plan_validity;
                    $newUser->plan_activation_date = now();
                    $newUser->plan_details = $free_plan;
                    $newUser->save();
                }
            }

            // Welcome email handling
            $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675208')->first();
            $message = [
                'status' => "",
                'emailSubject' => $emailTemplateDetails->email_template_subject,
                'emailContent' => $emailTemplateDetails->email_template_content,
                'registeredName' => $googleUser->getName(),
                'registeredEmail' => $googleUser->getEmail(),
            ];

            if ($emailTemplateDetails->is_enabled == 1) {
                try {
                    Mail::to($googleUser->getEmail())->bcc(config('mail.from.address'))->send(new \App\Mail\AppointmentMail($message));
                } catch (\Throwable $e) {
                    report($e);
                }

                if ($config->firstWhere('config_key', 'disable_user_email_verification')?->config_value == "1") {
                    try {
                        $newUser->newEmail($googleUser->getEmail());
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }

            // Handle mobile app platform login for new users
            if ($platform == 'app') {
                $token = Str::random(60);

                $tokens = json_decode($newUser->auth_token, true);
                if (!is_array($tokens)) {
                    $tokens = [];
                }

                $tokens[] = $token;
                $newUser->auth_token = json_encode(array_values($tokens));
                $newUser->save();

                // Redirect back to mobile app via deep link
                return redirect()->away("gobiz://auth/callback?token={$token}&email={$newUser->email}");
            } else {
                // Authenticate web session only for web platform
                Auth::login($newUser, true);

                if (Schema::hasTable('modern_dashboard_settings')) {
                    $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
                    if (plugin_active('ModernDashboard') && $is_modern_dashboard_enabled == 1) {
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
