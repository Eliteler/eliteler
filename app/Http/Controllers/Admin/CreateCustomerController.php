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

use App\Plan;
use App\User;
use App\Setting;
use Carbon\Carbon;
use App\Transaction;
use App\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CreateCustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createCustomer()
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Available Plans
        $plans = Plan::where('status', 1)->get();

        return view('admin.pages.customers.create', compact('plans', 'settings', 'config'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveCustomer(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'plan_id' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Check email
        if (User::where('email', $request->email)->exists()) {
            return back()->with('failed', trans('This email already exists.'));
        }

        // Create Base User
        $userId = uniqid();
        $user = new User();
        $user->user_id = $userId;
        $user->role_id = 2;
        $user->name = ucfirst($request->full_name);
        $user->email = $request->email;
        $user->email_verified_at = now();
        $user->password = Hash::make($request->password);
        $user->status = $request->status;

        $planDetails = null; // Initialize outside the block

        // With Plan
        if ($request->plan_id != 0) {
            $planDetails = Plan::where('plan_id', $request->plan_id)->first();
            if (!$planDetails) {
                return redirect()->route('admin.create.customer')->with('failed', trans('This plan does not exist.'));
            }

            // Plan validity
            $planValidity = Carbon::now()->addDays((int) $planDetails->validity);
            $user->plan_id = $request->plan_id;
            $user->term = $planDetails->validity;
            $user->plan_validity = $planValidity;
            $user->plan_details = json_encode($planDetails);
            $user->plan_activation_date = now();
        }

        $user->save();

        // Update AI Credit only if a plan was assigned
        if ($planDetails) {
            $user->plan_details = $planDetails; // Use the already-loaded $user instance
            $user->save();
        }

        // Welcome Email
        if ($request->welcome_email == 1) {
            $emailTemplate = EmailTemplate::where('email_template_id', '584922675208')->first();

            $message = [
                'emailSubject' => $emailTemplate->email_template_subject,
                'emailContent' => $emailTemplate->email_template_content,
                'registeredName' => ucfirst($request->full_name),
                'registeredEmail' => $request->email
            ];

            try {
                Mail::to($request->email)->bcc(env('MAIL_FROM_ADDRESS'))->send(new \App\Mail\AppointmentMail($message));
            } catch (\Exception $e) {
            }
        }

        // Reset Password
        if ($request->reset_password == 1) {
            $token = Str::random(64);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => Carbon::now()]
            );

            $actionLink = url(route('password.reset', ['token' => $token, 'email' => $request->email], false));

            $emailTemplate = EmailTemplate::where('email_template_id', '584922675212')->first();

            $details = [
                'emailSubject' => $emailTemplate->email_template_subject,
                'emailContent' => $emailTemplate->email_template_content,
                'appname' => config('app.name'),
                'actionlink' => $actionLink
            ];

            try {
                Mail::to($user->email)->send(new \App\Mail\AppointmentMail($details));
            } catch (\Exception $e) {
                return back()->with('email', $e->getMessage());
            }
        }

        // Save offline transaction if plan exists
        if ($request->plan_id != 0) {
            $config = DB::table('config')->get();

            $appliedTax = ($planDetails->plan_price * $config[25]->config_value) / 100;
            $amount = $planDetails->plan_price + $appliedTax;

            $invoiceDetails = [
                'from_billing_name' => $config[16]->config_value,
                'from_billing_address' => $config[19]->config_value,
                'from_billing_city' => $config[20]->config_value,
                'from_billing_state' => $config[21]->config_value,
                'from_billing_zipcode' => $config[22]->config_value,
                'from_billing_country' => $config[23]->config_value,
                'from_vat_number' => $config[26]->config_value,
                'from_billing_phone' => $config[18]->config_value,
                'from_billing_email' => $config[17]->config_value,
                'to_billing_name' => $request->full_name,
                'to_billing_email' => $request->email,
                'subtotal' => $planDetails->plan_price,
                'tax_name' => $config[24]->config_value,
                'tax_type' => $config[14]->config_value,
                'tax_value' => $config[25]->config_value,
                'tax_amount' => $appliedTax,
                'invoice_amount' => $amount
            ];

            $invoiceNumber = Transaction::where('invoice_prefix', $config[15]->config_value)->count() + 1;

            $transaction = new Transaction();
            $transaction->gobiz_transaction_id = uniqid();
            $transaction->transaction_id = "";
            $transaction->transaction_date = now();
            $transaction->user_id = $user->id;
            $transaction->plan_id = $planDetails->plan_id;
            $transaction->desciption = $planDetails->plan_name . " Plan";
            $transaction->payment_gateway_name = "Offline";
            $transaction->transaction_amount = $amount;
            $transaction->transaction_currency = $config[1]->config_value;
            $transaction->invoice_details = json_encode($invoiceDetails);
            $transaction->invoice_prefix = $config[15]->config_value;
            $transaction->invoice_number = $invoiceNumber;
            $transaction->payment_status = "SUCCESS";
            $transaction->save();
        }

        // Message
        $message = trans('auth.customer_created', [
            'email' => $request->email,
            'password' => $request->password
        ]);

        return redirect()->route('admin.create.customer')->with('success', $message);
    }
}
