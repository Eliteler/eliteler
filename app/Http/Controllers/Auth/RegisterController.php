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
use App\Setting;
use App\Referral;
use Carbon\Carbon;
use App\Transaction;
use App\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Validator for registration request
     */
    protected function validator(array $data)
    {
        $rules = [
            'name'           => ['required', 'string', 'min:2', 'max:100'],
            'email'          => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'mobile_number'  => ['nullable', 'string', 'max:15', 'regex:/^\+?[0-9]{7,15}$/'],
            'password'       => ['required', 'string', 'min:6', 'confirmed'],
            'terms'          => ['required'],
            'referral_code'  => ['nullable', 'string', 'max:50', 'exists:users,user_id'],
        ];

        if (env('RECAPTCHA_ENABLE') == 'on') {
            $rules['g-recaptcha-response'] = ['recaptcha', 'required'];
        }

        return Validator::make($data, $rules); 
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        $config = GoBizCommonService::config();
        $settings = GoBizCommonService::settings();

        $registration_page = $config->where('config_key', 'registration_page')->first();
        $registration_page_enabled = $registration_page && $registration_page->config_value == '0';

        if ($registration_page_enabled) {
            $google_configuration = [
                'GOOGLE_ENABLE'        => env('GOOGLE_ENABLE', ''),
                'GOOGLE_CLIENT_ID'     => env('GOOGLE_CLIENT_ID', ''),
                'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET', ''),
                'GOOGLE_REDIRECT'      => env('GOOGLE_REDIRECT', ''),
            ];

            $recaptcha_configuration = [
                'RECAPTCHA_ENABLE'     => env('RECAPTCHA_ENABLE', ''),
                'RECAPTCHA_SITE_KEY'   => env('RECAPTCHA_SITE_KEY', ''),
                'RECAPTCHA_SECRET_KEY' => env('RECAPTCHA_SECRET_KEY', ''),
            ];

            $settings['google_configuration'] = $google_configuration;
            $settings['recaptcha_configuration'] = $recaptcha_configuration;

            if (isset($_REQUEST['ref'])) {
                Cookie::queue('referral_code', $_REQUEST['ref'], 10);
            }

            return view('auth.register', compact('config', 'settings'));
        }

        return redirect()->route('home-locale')
            ->with('failed', __('Customer registration is currently closed. Please try again later.'));
    }

    /**
     * Create user after registration
     */
    protected function create(array $data)
    {
        $config = DB::table('config')->pluck('config_value', 'config_key');

        $rawEmail = $data['email'];
        $deletedEmail = $rawEmail . 'deleted';

        // First check the deleted version in DB
        $deletedUser = User::where('email', $deletedEmail)->first();

        if ($deletedUser) {
            throw ValidationException::withMessages([
                'email' => __(
                    'deleted_account_message'
                ) . ' <a href="' . route('contact') . '"><strong>' . __('contact support') . '</strong></a>.',
            ]);
        }

        // Now clean the email for normal check
        $email = $rawEmail;

        if (str_contains($email, 'deleted')) {
            $email = str_replace('deleted', '', $email);
            $email = trim($email);
        }

        $existingUser = User::where('email', $email)
            ->whereNotIn('status', [-1, 2])
            ->first();

        if ($existingUser) {
            return $existingUser;
        }

        $userId = uniqid();

        // Referral System
        if ($config['referral_system'] ?? false) {
            if (!empty($data['referral_code'])) {
                $referralCode = User::where('user_id', $data['referral_code'])->first();

                if ($referralCode) {
                    $referralType  = $config['referral_type'] ?? 0;
                    $referralValue = $config['referral_value'] ?? 0;
                    $referralAmount = $referralType == '0' ? 0 : $referralValue;

                    $referralData = [
                        'referral_type'   => $referralType,
                        'referral_value'  => $referralValue,
                        'referral_amount' => $referralAmount,
                    ];

                    // Save referral
                    $referral = new Referral();
                    $referral->user_id = $userId;
                    $referral->referred_user_id = $referralCode->user_id;
                    $referral->is_registered = 1;
                    $referral->referral_scheme = json_encode($referralData);
                    $referral->status = 1;
                    $referral->save();
                }
            }
        }

        // Create user data
        $userData = [
            'user_id'          => $userId,
            'lang'         => config('app.locale') ?? 'en',
            'name'             => $data['name'],
            'email'            => $data['email'],
            'mobile_number'    => $data['mobile_number'],
            'email_verified_at' => data_get($config, 'disable_user_email_verification') == '1' ? null : now(),
            'auth_type'        => 'Email',
            'password'         => Hash::make($data['password']),
        ];

        // Create new user
        $user = User::create($userData);

        // Activate plan during registeration
        $activate_plan_during_registeration = $config['activate_plan_during_registeration'] ?? '0';

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
                $transaction->user_id = $user->id;
                $transaction->plan_id = $free_plan->plan_id;
                $transaction->desciption = $free_plan->plan_name . " Plan";
                $transaction->payment_gateway_name = "FREE";
                $transaction->transaction_amount = $free_plan->plan_price;
                $transaction->transaction_currency = $config['currency'];
                $transaction->invoice_details = json_encode([]);
                $transaction->payment_status = "SUCCESS";
                $transaction->save();

                // set plan validity
                $plan_validity = Carbon::now();
                $plan_validity->addDays((int) $free_plan->validity);

                $user = User::where('user_id', $userId)->first();
                $user->plan_id = $free_plan->plan_id;
                $user->term = $free_plan->validity;
                $user->plan_validity = $plan_validity;
                $user->plan_activation_date = now();
                $user->plan_details = $free_plan;
                $user->save();
            }
        }

        // Remove referral cookie
        Cookie::queue(Cookie::forget('referral_code'));

        // If user email verification is disabled, set the email as verified
        if (($config['disable_user_email_verification'] ?? '0') == '1') {
            // Remove email verification requirement
        } else {
            // Send Welcome Email
            $emailTemplate = EmailTemplate::where('email_template_id', '584922675208')->first();

            if ($emailTemplate && $emailTemplate->is_enabled == 1) {
                try {
                    $message = [
                        'status'         => '',
                        'emailSubject'   => $emailTemplate->email_template_subject,
                        'emailContent'   => $emailTemplate->email_template_content,
                        'registeredName' => $data['name'],
                        'registeredEmail' => $data['email'],
                    ];

                    // Send welcome email
                    Mail::to($data['email'])
                        ->bcc(env('MAIL_FROM_ADDRESS'))
                        ->send(new \App\Mail\AppointmentMail($message));
                } catch (\Exception $e) {
                    Log::error('Registration email failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $user;
    }

    protected function redirectTo()
    {
        return '/user/dashboard';
    }
}
