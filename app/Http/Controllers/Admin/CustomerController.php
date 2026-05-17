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

use App\BusinessCard;
use App\BusinessField;
use App\BusinessHour;
use App\CardAppointmentTime;
use App\Currency;
use App\EmailTemplate;
use App\Gallery;
use App\Http\Controllers\Controller;
use App\InformationPop;
use App\Newsletter;
use App\NfcCardOrder;
use App\NfcCardOrderTransaction;
use App\Payment;
use App\Plan;
use App\Referral;
use App\Service;
use App\ServiceBooking;
use App\Setting;
use App\StoreBusinessHour;
use App\StoreCategory;
use App\StoreProduct;
use App\Testimonial;
use App\Transaction;
use App\User;
use App\VcardProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
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
     * Show the application dashboard. 
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // All Customers
    public function customers(Request $request, $status)
    {
        // Preload config
        $config = DB::table('config')->get();

        // Allowed statuses
        $allowedStatuses = ['all', 'verified', 'unverified', 'banned', 'deleted'];

        // Validate status
        if (!in_array($status, $allowedStatuses, true)) {
            return redirect()
                ->route('admin.customers', ['status' => 'all'])
                ->with('failed', __('Invalid status!'));
        }

        if ($request->ajax()) {

            $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();
            $symbol = $currencies[$config[1]->config_value] ?? '';

            $status = $status;

            $query = User::where('role_id', 2)->orderBy('id', 'desc');

            match ($status) {
                'verified' => $query
                    ->whereNotNull('email_verified_at')
                    ->where('status', 1),

                'unverified' => $query
                    ->whereNull('email_verified_at')
                    ->where('status', 1),

                'banned' => $query
                    ->where('status', 2),

                'deleted' => $query
                    ->whereNotNull('email_verified_at')
                    ->where('status', -1),

                default => $query,
            };

            return DataTables::of($query->get([
                'id',
                'user_id',
                'name',
                'email',
                'plan_details',
                'created_at',
                'status'
            ]))
                ->addIndexColumn()

                ->addColumn('name', function ($row) {
                    return '<a href="' . route('admin.view.customer', $row->user_id) . '">' . $row->name . '</a>';
                })

                ->addColumn('email', function ($row) {
                    return '<a href="mailto:' . $row->email . '">' . $row->email . '</a>';
                })

                ->addColumn('plan', function ($row) {
                    $plan = json_decode($row->plan_details, true);

                    if (!$plan) {
                        return __('No Plan');
                    }

                    $price = $plan['plan_price'] == 0
                        ? __('Free')
                        : formatCurrency($plan['plan_price']);

                    return '<strong>' . __($plan['plan_name']) . ' <span>(' . $price . ')</span></strong>';
                })

                ->addColumn('created_at', fn($row) => formatDateForUser($row->created_at))

                ->addColumn('status', function ($row) {
                    return match ((int) $row->status) {
                        0 => '<span class="badge bg-warning text-white">' . __('Deactive') . '</span>',
                        1 => '<span class="badge bg-success text-white">' . __('Active') . '</span>',
                        2 => '<span class="badge bg-danger text-white">' . __('Ban') . '</span>',
                        -1 => '<span class="badge bg-secondary text-white">' . __('Delete') . '</span>',
                        default => '',
                    };
                })

                ->addColumn('action', function ($row) {

                    $editUrl = route('admin.edit.customer', $row->user_id);
                    $changePlanUrl = route('admin.change.customer.plan', $row->user_id);

                    $activateDeactivate = $row->status == 0 ? __('Activate') : __('Deactivate');
                    $activateDeactivateFunction = $row->status == 0 ? 'activateUser' : 'deactivateUser';

                    $status = request('status', 'all');

                    $html = '
        <a class="btn-action" href="#"
           data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
            <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            </svg>
        </a>

        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>
            <a class="dropdown-item" href="' . $changePlanUrl . '">' . __('Change Plan') . '</a>
    ';

                    // Check status is "all"
                    if ($status === 'all') {
                        $html .= '
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'1\'); return false;">' . __('Verified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'0\'); return false;">' . __('Unverified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'2\'); return false;">' . __('Banned') . '</a>
            <a class="dropdown-item" href="#" onclick="deleteUser(\'' . $row->user_id . '\'); return false;">' . __('Delete') . '</a>
        ';
                    }

                    // Check status is "verified"
                    if ($status === 'verified') {
                        $html .= '
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'0\'); return false;">' . __('Unverified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'2\'); return false;">' . __('Banned') . '</a>
            <a class="dropdown-item" href="#" onclick="deleteUser(\'' . $row->user_id . '\'); return false;">' . __('Delete') . '</a>
        ';
                    }

                    // Check status is "unverified"
                    if ($status === 'unverified') {
                        $html .= '
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'1\'); return false;">' . __('Verified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'2\'); return false;">' . __('Banned') . '</a>
            <a class="dropdown-item" href="#" onclick="deleteUser(\'' . $row->user_id . '\'); return false;">' . __('Delete') . '</a>
        ';
                    }

                    // Check status is "banned"
                    if ($status === 'banned') {
                        $html .= '
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'1\'); return false;">' . __('Verified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'0\'); return false;">' . __('Unverified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'1\'); return false;">' . __('Unbanned') . '</a>
            <a class="dropdown-item" href="#" onclick="deleteUser(\'' . $row->user_id . '\'); return false;">' . __('Delete') . '</a>
        ';
                    }

                    // Check status is "deleted"
                    if ($status === 'deleted') {
                        $html .= '
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'1\'); return false;">' . __('Verified') . '</a>
            <a class="dropdown-item" href="#" onclick="customerVerified(\'' . $row->user_id . '\', \'0\'); return false;">' . __('Unverified') . '</a>
            <a class="dropdown-item" href="#" onclick="undeleteUser(\'' . $row->user_id . '\'); return false;">' . __('Undelete') . '</a>
            <a class="dropdown-item" href="#" onclick="deleteUser(\'' . $row->user_id . '\'); return false;">' . __('Delete') . '</a>
            ';
                    }

                    $html .= '
            <a class="dropdown-item" href="#" onclick="' . $activateDeactivateFunction . '(\'' . $row->user_id . '\'); return false;">
                ' . $activateDeactivate . '
            </a>
        ';

                    $html .= '</div>';

                    return $html;
                })


                ->rawColumns(['name', 'email', 'plan', 'status', 'action'])
                ->make(true);
        }

        // Page load
        $settings = Setting::where('status', 1)->first();
        return view('admin.pages.customers.index', compact('settings', 'config'));
    }

    // View Customer
    public function viewCustomer(Request $request, $id)
    {
        // Get user details
        $user_details = User::where('user_id', $id)->first();

        if ($user_details == null) {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Not Found!'));
        } else {
            // Get all cards of the customer
            $user_cards = BusinessCard::where('user_id', $user_details->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->where('status', '1')->orderBy('id', 'desc')->get();

            // Get all stores of the customer
            $user_stores = BusinessCard::where('user_id', $user_details->user_id)->where('card_type', 'store')->where('card_status', 'activated')->where('status', '1')->orderBy('id', 'desc')->get();

            // Get all nfc card orders of the customer
            $orders = NfcCardOrder::where('user_id', $user_details->id)->orderBy('id', 'desc')->get();

            // Get all transactions of the customer
            $transactions = Transaction::where('user_id', $user_details->id)->orderBy('id', 'desc')->get();

            // Get all nfc card order transactions of the customer
            $nfc_transactions = NfcCardOrderTransaction::join('nfc_card_orders', 'nfc_card_order_transactions.nfc_card_order_id', '=', 'nfc_card_orders.nfc_card_order_id')
                ->where('nfc_card_orders.user_id', $user_details->id)
                ->orderBy('nfc_card_order_transactions.id', 'desc')
                ->get();

            // Get all ai credits transactions of the customer
            $ai_credits_transactions = DB::table('ai_credits_transactions')
                ->join('ai_credits_plans', 'ai_credits_transactions.ai_credits_plan_id', '=', 'ai_credits_plans.ai_credits_plan_id')
                ->where('ai_credits_transactions.user_id', $user_details->id)
                ->orderBy('ai_credits_transactions.id', 'desc')
                ->get(['ai_credits_transactions.*', 'ai_credits_plans.plan_name']);

            // Queries
            $settings = Setting::where('status', 1)->first();
            $config = DB::table('config')->get();
            $currencies = Currency::where('status', 1)->pluck('symbol', 'iso_code')->toArray();

            // Currency
            $symbol = $currencies[$config[1]->config_value] ?? '';

            // Get all available customers
            $customers = User::where('role_id', 2)->where('status', 1)->get();

            return view('admin.pages.customers.view', compact('user_details', 'ai_credits_transactions', 'user_cards', 'user_stores', 'orders', 'transactions', 'nfc_transactions', 'customers', 'settings', 'config', 'symbol'));
        }
    }

    // Edit Customer
    public function editCustomer(Request $request, $id)
    {
        $user_details = User::where('user_id', $id)->first();
        $settings = Setting::where('status', 1)->first();
        if ($user_details == null) {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Not Found!'));
        } else {
            return view('admin.pages.customers.edit', compact('user_details', 'settings'));
        }
    }

    // Update Customer
    public function updateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'full_name' => 'required',
            'email' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Queries
        $emailExists = User::where('email', $request->email)->count();
        $user_details = User::where('user_id', $request->user_id)->first();

        if ($emailExists != 1 || $request->email == $user_details->email) {
            if ($request->password == null) {
                User::where('user_id', $request->user_id)->update([
                    'name' => $request->full_name,
                    'email' => $request->email,
                    'status' => $request->status
                ]);
            } else {
                User::where('user_id', $request->user_id)->update([
                    'name' => $request->full_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'status' => $request->status
                ]);
            }

            // Set Welcome Email
            if ($request->welcome_email == 1) {
                // Get Welcome email template
                $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675208')->first();

                $message = [
                    'status' => "",
                    'emailSubject' => $emailTemplateDetails->email_template_subject,
                    'emailContent' => $emailTemplateDetails->email_template_content,
                    'registeredName' => ucfirst($request->full_name),
                    'registeredEmail' => $request->email,
                ];

                try {
                    // Welcome email
                    Mail::to($request->email)->bcc(env('MAIL_FROM_ADDRESS'))->send(new \App\Mail\AppointmentMail($message));
                } catch (\Exception $e) {
                }
            }

            // Set Reset Password
            if ($request->reset_password == 1) {
                $token = Str::random(64);

                // Store token in password_resets table
                DB::table('password_resets')->updateOrInsert(
                    ['email' => $request->email],
                    [
                        'token' => Hash::make($token),
                        'created_at' => Carbon::now(),
                    ]
                );

                $actionLink = url(route('password.reset', ['token' => $token, 'email' => $request->email], false));

                $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675212')->first();

                $details = [
                    'emailSubject' => $emailTemplateDetails->email_template_subject,
                    'emailContent' => $emailTemplateDetails->email_template_content,
                    'appname' => config('app.name'),
                    'actionlink' => $actionLink,
                ];

                try {
                    Mail::to($request->email)->send(new \App\Mail\AppointmentMail($details));
                } catch (\Exception $e) {
                    return back()->with('email', $e->getMessage());
                }
            }

            return redirect()->route('admin.customers', ['status' => 'all'])->with('success', trans('Updated!'));
        } else {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('This email address already registered. Try to another email address.'));
        }
    }

    // Change Customer plan
    public function ChangeCustomerPlan(Request $request, $id)
    {
        // Queries
        $user_details = User::where('user_id', $id)->first();

        if ($user_details) {
            // Queries
            $plans = Plan::where('status', 1)->get();
            $settings = Setting::where('status', 1)->first();
            $config = DB::table('config')->get();

            if ($plans == null) {
                return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Not Found!'));
            } else {
                return view('admin.pages.customers.change-plan', compact('user_details', 'plans', 'settings', 'config'));
            }
        } else {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Not Found!'));
        }
    }

    // Upgrade Customer plan
    public function UpdateCustomerPlan(Request $request)
    {
        $config = DB::table('config')->get();

        $user_details = User::where('user_id', $request->user_id)->first();

        $plan_data = Plan::where('plan_id', $request->plan_id)->first();
        $term_days = (int) $plan_data->validity;

        $amountToBePaid = ((float)($plan_data->plan_price) * (float)($config[25]->config_value) / 100) + (float)($plan_data->plan_price);

        if ($config[80]->config_value == '1') {
            // Referral amount details
            $referralCalculation = [];
            $referralCalculation['referral_type'] = $config[81]->config_value;
            $referralCalculation['referral_value'] = $config[82]->config_value;

            // Check referral_type is percentage or amount
            if ($config[81]->config_value == '0') {
                // Plan amount
                $base_amount = (float) $amountToBePaid;

                $referralCalculation['referral_amount'] = ($base_amount * $referralCalculation['referral_value']) / 100;
            } else {
                $referralCalculation['referral_amount'] = $referralCalculation['referral_value'];
            }

            // Check user_id is already subscribed or not
            if (Referral::where('user_id', $user_details->user_id)->where('is_subscribed', 0)->first()) {
                // Update referral details
                Referral::where('user_id', $user_details->user_id)->update([
                    'is_subscribed' => 1,
                    'referral_scheme' => json_encode($referralCalculation),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($user_details->plan_validity == "") {

            $plan_validity = Carbon::now();
            $plan_validity->addDays($term_days);

            $invoice_count = Transaction::where("invoice_prefix", $config[15]->config_value)->count();
            $invoice_number = $invoice_count + 1;

            $gobiz_transaction_id = uniqid();

            $invoice_details = [];

            $invoice_details['from_billing_name'] = $config[16]->config_value;
            $invoice_details['from_billing_address'] = $config[19]->config_value;
            $invoice_details['from_billing_city'] = $config[20]->config_value;
            $invoice_details['from_billing_state'] = $config[21]->config_value;
            $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
            $invoice_details['from_billing_country'] = $config[23]->config_value;
            $invoice_details['from_vat_number'] = $config[26]->config_value;
            $invoice_details['from_billing_phone'] = $config[18]->config_value;
            $invoice_details['from_billing_email'] = $config[17]->config_value;
            $invoice_details['to_billing_name'] = $user_details->billing_name;
            $invoice_details['to_billing_address'] = $user_details->billing_address;
            $invoice_details['to_billing_city'] = $user_details->billing_city;
            $invoice_details['to_billing_state'] = $user_details->billing_state;
            $invoice_details['to_billing_zipcode'] = $user_details->billing_zipcode;
            $invoice_details['to_billing_country'] = $user_details->billing_country;
            $invoice_details['to_billing_phone'] = $user_details->billing_phone;
            $invoice_details['to_billing_email'] = $user_details->billing_email;
            $invoice_details['to_vat_number'] = $user_details->vat_number;
            $invoice_details['tax_name'] = $config[24]->config_value;
            $invoice_details['tax_type'] = $config[14]->config_value;
            $invoice_details['tax_value'] = $config[25]->config_value;
            $invoice_details['invoice_amount'] = $amountToBePaid;
            $invoice_details['subtotal'] = $plan_data->plan_price;
            $invoice_details['tax_amount'] = (float)($plan_data->plan_price) * (float)($config[25]->config_value) / 100;

            // If order is created from stripe
            $transaction = new Transaction();
            $transaction->gobiz_transaction_id = $gobiz_transaction_id;
            $transaction->transaction_date = now();
            $transaction->transaction_id = "";
            $transaction->user_id = $user_details->id;
            $transaction->plan_id = $plan_data->plan_id;
            $transaction->desciption = $plan_data->plan_name . " Plan";
            $transaction->payment_gateway_name = "Offline";
            $transaction->transaction_amount = $amountToBePaid;
            $transaction->invoice_prefix = $config[15]->config_value;
            $transaction->invoice_number = $invoice_number;
            $transaction->transaction_currency = $config[1]->config_value;
            $transaction->invoice_details = json_encode($invoice_details);
            $transaction->payment_status = "SUCCESS";
            $transaction->save();

            User::where('id', $user_details->id)->update([
                'plan_id' => $request->plan_id,
                'term' => $term_days,
                'plan_validity' => $plan_validity,
                'plan_activation_date' => now(),
                'plan_details' => $plan_data
            ]);

            $details = [
                'from_billing_name' => $config[16]->config_value,
                'from_billing_email' => $config[17]->config_value,
                'from_billing_address' => $config[19]->config_value,
                'from_billing_city' => $config[20]->config_value,
                'from_billing_state' => $config[21]->config_value,
                'from_billing_country' => $config[23]->config_value,
                'from_billing_zipcode' => $config[22]->config_value,
                'gobiz_transaction_id' => $gobiz_transaction_id,
                'to_billing_name' => $user_details->billing_name,
                'invoice_currency' => $config[1]->config_value,
                'invoice_amount' => $plan_data->plan_price,
                'invoice_id' => $config[15]->config_value . $invoice_number,
                'invoice_date' => Carbon::now(),
                'description' => $plan_data->plan_name . ' plan Upgrade',
                'email_heading' => $config[27]->config_value,
                'email_footer' => $config[28]->config_value,
            ];

            try {
                Mail::to($user_details->email)->send(new \App\Mail\SendEmailInvoice($details));
            } catch (\Exception $e) {
            }

            return redirect()->route('admin.change.customer.plan', $request->user_id)->with('success', trans('Changed!'));
        } else {
            $message = "";
            if ($user_details->plan_id == $request->plan_id) {


                // Check if plan validity is expired or not.
                $plan_validity = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $user_details->plan_validity);
                $current_date = Carbon::now();
                $remaining_days = $current_date->diffInDays($plan_validity, false);

                if ($remaining_days > 0) {
                    $plan_validity = Carbon::parse($user_details->plan_validity);
                    $plan_validity->addDays($term_days);
                    $message = trans('Changed!');
                } else {
                    $plan_validity = Carbon::now();
                    $plan_validity->addDays($term_days);
                    $message = trans('Changed!');
                }
            } else {

                // Making all cards inactive, For Plan change
                BusinessCard::where('user_id', $user_details->user_id)->update([
                    'card_status' => 'inactive',
                ]);

                $plan_validity = Carbon::now();
                $plan_validity->addDays($term_days);
                $message = trans("Changed!");
            }

            $invoice_count = Transaction::where("invoice_prefix", $config[15]->config_value)->count();
            $invoice_number = $invoice_count + 1;

            $gobiz_transaction_id = uniqid();

            $invoice_details = [];

            $invoice_details['from_billing_name'] = $config[16]->config_value;
            $invoice_details['from_billing_address'] = $config[19]->config_value;
            $invoice_details['from_billing_city'] = $config[20]->config_value;
            $invoice_details['from_billing_state'] = $config[21]->config_value;
            $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
            $invoice_details['from_billing_country'] = $config[23]->config_value;
            $invoice_details['from_vat_number'] = $config[26]->config_value;
            $invoice_details['from_billing_phone'] = $config[18]->config_value;
            $invoice_details['from_billing_email'] = $config[17]->config_value;
            $invoice_details['to_billing_name'] = $user_details->billing_name;
            $invoice_details['to_billing_address'] = $user_details->billing_address;
            $invoice_details['to_billing_city'] = $user_details->billing_city;
            $invoice_details['to_billing_state'] = $user_details->billing_state;
            $invoice_details['to_billing_zipcode'] = $user_details->billing_zipcode;
            $invoice_details['to_billing_country'] = $user_details->billing_country;
            $invoice_details['to_billing_phone'] = $user_details->billing_phone;
            $invoice_details['to_billing_email'] = $user_details->billing_email;
            $invoice_details['to_vat_number'] = $user_details->vat_number;
            $invoice_details['tax_name'] = $config[24]->config_value;
            $invoice_details['tax_type'] = $config[14]->config_value;
            $invoice_details['tax_value'] = $config[25]->config_value;
            $invoice_details['invoice_amount'] = $amountToBePaid;
            $invoice_details['subtotal'] = $plan_data->plan_price;
            $invoice_details['tax_amount'] = (float)($plan_data->plan_price) * (float)($config[25]->config_value) / 100;

            // If order is created from stripe
            $transaction = new Transaction();
            $transaction->gobiz_transaction_id = $gobiz_transaction_id;
            $transaction->transaction_date = now();
            $transaction->transaction_id = "";
            $transaction->user_id = $user_details->id;
            $transaction->plan_id = $plan_data->plan_id;
            $transaction->desciption = $plan_data->plan_name . " Plan";
            $transaction->payment_gateway_name = "Offline";
            $transaction->transaction_amount = $amountToBePaid;
            $transaction->invoice_prefix = $config[15]->config_value;
            $transaction->invoice_number = $invoice_number;
            $transaction->transaction_currency = $config[1]->config_value;
            $transaction->invoice_details = json_encode($invoice_details);
            $transaction->payment_status = "SUCCESS";
            $transaction->save();

            // update customer
            $user_details->plan_id = $request->plan_id;
            $user_details->term = $term_days;
            $user_details->plan_validity = $plan_validity;
            $user_details->plan_activation_date = now();
            $user_details->plan_details = $plan_data;
            $user_details->save();

            $details = [
                'from_billing_name' => $config[16]->config_value,
                'from_billing_email' => $config[17]->config_value,
                'from_billing_address' => $config[19]->config_value,
                'from_billing_city' => $config[20]->config_value,
                'from_billing_state' => $config[21]->config_value,
                'from_billing_country' => $config[23]->config_value,
                'from_billing_zipcode' => $config[22]->config_value,
                'gobiz_transaction_id' => $gobiz_transaction_id,
                'to_billing_name' => $user_details->billing_name,
                'invoice_currency' => $config[1]->config_value,
                'invoice_amount' => $plan_data->plan_price,
                'invoice_id' => $config[15]->config_value . $invoice_number,
                'invoice_date' => Carbon::now(),
                'description' => $plan_data->plan_name . ' plan Upgrade',
                'email_heading' => $config[27]->config_value,
                'email_footer' => $config[28]->config_value,
            ];

            try {
                Mail::to($user_details->email)->send(new \App\Mail\SendEmailInvoice($details));
            } catch (\Exception $e) {
            }

            return redirect()->route('admin.change.customer.plan', $request->user_id)->with('success', trans($message));
        }
    }

    // Update status
    public function updateStatus(Request $request)
    {
        $user_details = User::where('user_id', $request->query('id'))->first();
        if ($user_details->status == 0) {
            // Activate business card
            BusinessCard::where('user_id', $user_details->user_id)->update(['status' => 1]);

            $status = 1;
        } else {
            // Deactivate business card
            BusinessCard::where('user_id', $user_details->user_id)->update(['status' => 0]);

            $status = 0;
        }
        User::where('user_id', $request->query('id'))->update(['status' => $status]);

        return redirect()->route('admin.customers', ['status' => 'all'])->with('success', trans('Updated!'));
    }

    // Delete Customer
    public function deleteCustomer(Request $request)
    {
        // Queries
        $allcards = BusinessCard::where('user_id', $request->query('id'))->get();
        $user = User::where('user_id', $request->query('id'))->first();
        for ($i = 0; $i < count($allcards); $i++) {
            if ($allcards != null) {
                BusinessField::where('card_id', $allcards[$i]->card_id)->delete();
                BusinessHour::where('card_id', $allcards[$i]->card_id)->delete();
                Gallery::where('card_id', $allcards[$i]->card_id)->delete();
                Payment::where('card_id', $allcards[$i]->card_id)->delete();
                Service::where('card_id', $allcards[$i]->card_id)->delete();
                StoreProduct::where('card_id', $allcards[$i]->card_id)->delete();
            }
        }

        $transactions = Transaction::where('user_id', $request->query('id'))->first();
        $businessCards = BusinessCard::where('user_id', $request->query('id'))->first();

        if ($transactions != null) {
            $transactions->delete();
        }

        if ($businessCards != null) {
            $businessCards->delete();
        }

        User::where('user_id', $request->query('id'))->delete();

        return redirect()->back()->with('success', trans('Removed!'));
    }

    // Login As Customer
    public function authAs(Request $request, $id)
    {
        // Queries
        $user_details = User::where('user_id', $id)->where('status', 1)->first();

        if (isset($user_details)) {
            // Login user
            Auth::loginUsingId($user_details->id);

            return redirect()->route('user.dashboard');
        } else {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Unable to find user account!'));
        }
    }

    // Assign card to another customer
    public function assignCard(Request $request)
    {
        // Parameters
        $cardId = $request->card_id;
        $customerId = $request->customer_id;
        $type = $request->type;

        // Check Active plan details in user
        $user_details = User::where('user_id', $customerId)->first();
        $plan_details = json_decode($user_details->plan_details);

        // No of cards to created
        $cards = BusinessCard::where('user_id', $customerId)->where('card_type', 'vcard')->where('card_status', 'activated')->count();

        // Check unlimited cardss
        if ($plan_details->no_of_vcards == 999) {
            $no_cards = 999999;
        } else {
            $no_cards = $plan_details->no_of_vcards;
        }

        // Chech vcard creation limit
        if ($cards < $no_cards) {
            // Queries
            $businessCard = BusinessCard::where('card_id', $cardId)->first();

            // Check business card
            if ($businessCard == null) {
                return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Unable to assign card. The card doesn\'t exist.'));
            }

            // Generate card ID
            $generateCardId = uniqid();

            // Create duplicate
            $duplicateCard = new BusinessCard();
            $duplicateCard->card_id = $generateCardId;
            $duplicateCard->user_id = $customerId;
            $duplicateCard->type = $businessCard->type;
            $duplicateCard->theme_id = $businessCard->theme_id;
            $duplicateCard->card_lang = $businessCard->card_lang;
            $duplicateCard->cover_type = $businessCard->cover_type;
            $duplicateCard->cover = $businessCard->cover;
            $duplicateCard->profile = $businessCard->profile;
            $duplicateCard->card_url = $businessCard->card_url . '-' . Str::random(5);
            $duplicateCard->custom_domain = $businessCard->custom_domain;
            $duplicateCard->card_type = $businessCard->card_type;
            $duplicateCard->title = $businessCard->title . ' (Duplicate)';
            $duplicateCard->sub_title = $businessCard->sub_title;
            $duplicateCard->description = $businessCard->description;
            $duplicateCard->enquiry_email = $businessCard->enquiry_email;
            $duplicateCard->appointment_receive_email = $businessCard->appointment_receive_email;
            $duplicateCard->is_newsletter_pop_active = $businessCard->is_newsletter_pop_active;
            $duplicateCard->is_info_pop_active = $businessCard->is_info_pop_active;
            $duplicateCard->custom_css = $businessCard->custom_css;
            $duplicateCard->custom_js = $businessCard->custom_js;
            $duplicateCard->password = $businessCard->password;
            $duplicateCard->expiry_time = $businessCard->expiry_time;
            $duplicateCard->card_status = 'activated';
            $duplicateCard->status = 1;
            $duplicateCard->save();

            // Check type
            if ($type == 'vcard') {
                // Duplicate social links
                $socialLinks = BusinessField::where('card_id', $cardId)->get();
                foreach ($socialLinks as $socialLink) {
                    try {
                        // Save social link
                        $duplicateSocialLink = new BusinessField();
                        $duplicateSocialLink->card_id = $generateCardId;
                        $duplicateSocialLink->type = $socialLink->type;
                        $duplicateSocialLink->icon = $socialLink->icon;
                        $duplicateSocialLink->label = $socialLink->label;
                        $duplicateSocialLink->content = $socialLink->content;
                        $duplicateSocialLink->position = $socialLink->position;
                        $duplicateSocialLink->status = 1;
                        $duplicateSocialLink->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate payment links
                $paymentLinks = Payment::where('card_id', $cardId)->get();
                foreach ($paymentLinks as $paymentLink) {
                    try {
                        // Save payment link
                        $duplicatePaymentLink = new Payment();
                        $duplicatePaymentLink->card_id = $generateCardId;
                        $duplicatePaymentLink->type = $paymentLink->type;
                        $duplicatePaymentLink->icon = $paymentLink->icon;
                        $duplicatePaymentLink->label = $paymentLink->label;
                        $duplicatePaymentLink->content = $paymentLink->content;
                        $duplicatePaymentLink->position = $paymentLink->position;
                        $duplicatePaymentLink->status = 1;
                        $duplicatePaymentLink->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate services
                $services = Service::where('card_id', $cardId)->get();
                foreach ($services as $service) {
                    try {
                        // Save service
                        $duplicateService = new Service();
                        $duplicateService->card_id = $generateCardId;
                        $duplicateService->service_name = $service->service_name;
                        $duplicateService->service_image = $service->service_image;
                        $duplicateService->service_description = $service->service_description;
                        $duplicateService->enable_enquiry = $service->enable_enquiry;
                        $duplicateService->status = 1;
                        $duplicateService->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate products
                $products = VcardProduct::where('card_id', $cardId)->get();
                foreach ($products as $product) {
                    try {
                        // Save product
                        $duplicateProduct = new VcardProduct();
                        $duplicateProduct->card_id = $generateCardId;
                        $duplicateProduct->product_id = uniqid();
                        $duplicateProduct->badge = $product->badge;
                        $duplicateProduct->currency = $product->currency;
                        $duplicateProduct->product_image = $product->product_image;
                        $duplicateProduct->product_name = $product->product_name;
                        $duplicateProduct->product_description = $product->product_description;
                        $duplicateProduct->regular_price = $product->regular_price;
                        $duplicateProduct->sales_price = $product->sales_price;
                        $duplicateProduct->product_status = $product->product_status;
                        $duplicateProduct->status = 1;
                        $duplicateProduct->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate galleries
                $galleries = Gallery::where('card_id', $cardId)->get();
                foreach ($galleries as $gallery) {
                    try {
                        $duplicateGallery = new Gallery();
                        $duplicateGallery->card_id = $generateCardId;
                        $duplicateGallery->caption = $gallery->caption;
                        $duplicateGallery->gallery_image = $gallery->gallery_image;
                        $duplicateGallery->status = 1;
                        $duplicateGallery->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate testimonials
                $testimonials = Testimonial::where('card_id', $cardId)->get();
                foreach ($testimonials as $testimonial) {
                    try {
                        // Save testimonial
                        $duplicateTestimonial = new Testimonial();
                        $duplicateTestimonial->card_id = $generateCardId;
                        $duplicateTestimonial->reviewer_image = $testimonial->reviewer_image;
                        $duplicateTestimonial->reviewer_name = $testimonial->reviewer_name;
                        $duplicateTestimonial->reviewer_subtext = $testimonial->reviewer_subtext;
                        $duplicateTestimonial->review = $testimonial->review;
                        $duplicateTestimonial->status = 1;
                        $duplicateTestimonial->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate newsletter
                $newsletter = Newsletter::where('card_id', $cardId)->first();
                if ($newsletter) {
                    try {
                        // Save newsletter
                        $duplicateNewsletter = new Newsletter();
                        $duplicateNewsletter->newsletter_id = $newsletter->newsletter_id;
                        $duplicateNewsletter->card_id = $generateCardId;
                        $duplicateNewsletter->email = $newsletter->email;
                        $duplicateNewsletter->status = 1;
                        $duplicateNewsletter->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate  information_pops
                $popups = InformationPop::where('card_id', $cardId)->first();
                if ($popups) {
                    try {
                        // Save information_pop
                        $duplicateInformationPop = new InformationPop();
                        $duplicateInformationPop->information_pop_id = $popups->information_pop_id;
                        $duplicateInformationPop->card_id = $generateCardId;
                        $duplicateInformationPop->confetti_effect = $popups->confetti_effect;
                        $duplicateInformationPop->info_pop_image = $popups->info_pop_image;
                        $duplicateInformationPop->info_pop_title = $popups->info_pop_title;
                        $duplicateInformationPop->info_pop_desc = $popups->info_pop_desc;
                        $duplicateInformationPop->info_pop_button_text = $popups->info_pop_button_text;
                        $duplicateInformationPop->info_pop_button_url = $popups->info_pop_button_url;
                        $duplicateInformationPop->status = 1;
                        $duplicateInformationPop->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate business hours
                $businessHours = BusinessHour::where('card_id', $cardId)->get();
                foreach ($businessHours as $businessHour) {
                    try {
                        // Save business hour
                        $duplicateBusinessHour = new BusinessHour();
                        $duplicateBusinessHour->card_id = $generateCardId;
                        $duplicateBusinessHour->monday = $businessHour->monday;
                        $duplicateBusinessHour->tuesday = $businessHour->tuesday;
                        $duplicateBusinessHour->wednesday = $businessHour->wednesday;
                        $duplicateBusinessHour->thursday = $businessHour->thursday;
                        $duplicateBusinessHour->friday = $businessHour->friday;
                        $duplicateBusinessHour->saturday = $businessHour->saturday;
                        $duplicateBusinessHour->sunday = $businessHour->sunday;
                        $duplicateBusinessHour->is_always_open = $businessHour->is_always_open;
                        $duplicateBusinessHour->is_display = $businessHour->is_display;
                        $duplicateBusinessHour->status = 1;
                        $duplicateBusinessHour->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate card appointments
                $appointments = CardAppointmentTime::where('card_id', $cardId)->get();
                foreach ($appointments as $appointment) {
                    try {
                        // Save appointment
                        $duplicateAppointment = new CardAppointmentTime();
                        $duplicateAppointment->card_appointment_time_id = uniqid();
                        $duplicateAppointment->card_id = $generateCardId;
                        $duplicateAppointment->day = $appointment->day;
                        $duplicateAppointment->slot_duration = $appointment->slot_duration;
                        $duplicateAppointment->time_slots = $appointment->time_slots;
                        $duplicateAppointment->price = $appointment->price;
                        $duplicateAppointment->status = 1;
                        $duplicateAppointment->save();
                    } catch (\Exception $e) {
                    }
                }

                // Service booking
                $serviceBookings = ServiceBooking::where('vcard_id', $cardId)->get();
                foreach ($serviceBookings as $serviceBooking) {
                    try {
                        // Save service booking
                        $duplicateServiceBooking = new ServiceBooking();
                        $duplicateServiceBooking->service_booking_id = uniqid();
                        $duplicateServiceBooking->user_id = $customerId;
                        $duplicateServiceBooking->vcard_id = $generateCardId;
                        $duplicateServiceBooking->service_booking = $serviceBooking->service_booking;
                        $duplicateServiceBooking->service_booking_available_days = $serviceBooking->service_booking_available_days;
                        $duplicateServiceBooking->service_booking_amount = $serviceBooking->service_booking_amount;
                        $duplicateServiceBooking->service_booking_start_time = $serviceBooking->service_booking_start_time;
                        $duplicateServiceBooking->service_booking_end_time = $serviceBooking->service_booking_end_time;
                        $duplicateServiceBooking->service_booking_receive_email = $serviceBooking->service_booking_receive_email;
                        $duplicateServiceBooking->save();
                    } catch (\Exception $e) {
                    }
                }
            } else {
                // Product store category
                $categories = StoreCategory::where('store_id', $cardId)->get();
                $categoryIdMap = []; // Store original -> new category_id

                foreach ($categories as $category) {
                    try {
                        // Save store category
                        $newCategoryId = uniqid(); // Generate new ID once
                        $duplicateCategory = new StoreCategory();
                        $duplicateCategory->user_id = $customerId ?? '';
                        $duplicateCategory->store_id = $generateCardId;
                        $duplicateCategory->category_id = $newCategoryId;
                        $duplicateCategory->thumbnail = $category->thumbnail ?? '';
                        $duplicateCategory->category_name = $category->category_name ?? '';
                        $duplicateCategory->status = 1;
                        $duplicateCategory->save();

                        // Store mapping for use in products
                        $categoryIdMap[$category->category_id] = $newCategoryId;
                    } catch (\Exception $e) {
                        // Optionally log the error
                    }
                }

                // Save products
                $products = StoreProduct::where('card_id', $cardId)->get();

                foreach ($products as $product) {
                    try {
                        $duplicateStoreProduct = new StoreProduct();
                        $duplicateStoreProduct->card_id = $generateCardId;
                        $duplicateStoreProduct->product_id = uniqid();

                        // Replace old category_id with new one using the map
                        $duplicateStoreProduct->category_id = $categoryIdMap[$product->category_id] ?? null;

                        $duplicateStoreProduct->badge = $product->badge;
                        $duplicateStoreProduct->product_image = $product->product_image;
                        $duplicateStoreProduct->product_name = $product->product_name;
                        $duplicateStoreProduct->product_description = $product->product_description;
                        $duplicateStoreProduct->regular_price = $product->regular_price;
                        $duplicateStoreProduct->sales_price = $product->sales_price;
                        $duplicateStoreProduct->product_status = $product->product_status;
                        $duplicateStoreProduct->status = 1;
                        $duplicateStoreProduct->save();
                    } catch (\Exception $e) {
                        // Optionally log the error
                    }
                }

                // Store Business Hours
                $businessHours = StoreBusinessHour::where('store_id', $cardId)->get();
                foreach ($businessHours as $businessHour) {
                    try {
                        // Save store business hour
                        $duplicateBusinessHour = new StoreBusinessHour();
                        $duplicateBusinessHour->user_id = $customerId;
                        $duplicateBusinessHour->store_id = $generateCardId;
                        $duplicateBusinessHour->business_hours_id = uniqid();
                        $duplicateBusinessHour->business_hours = $businessHour->business_hours;
                        $duplicateBusinessHour->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate newsletter
                $newsletter = Newsletter::where('card_id', $cardId)->first();
                if ($newsletter) {
                    try {
                        // Save newsletter
                        $duplicateNewsletter = new Newsletter();
                        $duplicateNewsletter->newsletter_id = $newsletter->newsletter_id;
                        $duplicateNewsletter->card_id = $generateCardId;
                        $duplicateNewsletter->email = $newsletter->email;
                        $duplicateNewsletter->status = 1;
                        $duplicateNewsletter->save();
                    } catch (\Exception $e) {
                    }
                }

                // Duplicate  information_pops
                $popups = InformationPop::where('card_id', $cardId)->get();
                foreach ($popups as $popup) {
                    try {
                        // Save information_pop
                        $duplicateInformationPop = new InformationPop();
                        $duplicateInformationPop->information_pop_id = $popup->information_pop_id;
                        $duplicateInformationPop->card_id = $generateCardId;
                        $duplicateInformationPop->confetti_effect = $popup->confetti_effect;
                        $duplicateInformationPop->info_pop_image = $popup->info_pop_image;
                        $duplicateInformationPop->info_pop_title = $popup->info_pop_title;
                        $duplicateInformationPop->info_pop_desc = $popup->info_pop_desc;
                        $duplicateInformationPop->info_pop_button_text = $popup->info_pop_button_text;
                        $duplicateInformationPop->info_pop_button_url = $popup->info_pop_button_url;
                        $duplicateInformationPop->status = 1;
                        $duplicateInformationPop->save();
                    } catch (\Exception $e) {
                    }
                }
            }

            // Redirect
            if ($type == 'vcard') {
                return redirect()->route('admin.customers', ['status' => 'all'])->with('success', trans('Duplicated!'));
            } else {
                return redirect()->route('admin.customers', ['status' => 'all'])->with('success', trans('Duplicated!'));
            }
        } else {
            return redirect()->route('admin.customers', ['status' => 'all'])->with('failed', trans('Unable to assign card. The maximum limit has been exceeded.'));
        }
    }

    // Customer verified / unverified / banned
    public function customerVerified(Request $request)
    {
        $userId = $request->query('id');
        $status = $request->query('status');

        $user = User::where('user_id', $userId)->first();

        if (!$user) {
            return back()->with('failed', trans('Not Found!'));
        }

        switch ((int) $status) {
            case 0:
                // Activate business card
                BusinessCard::where('user_id', $userId)->update(['status' => 1]);

                $user->email_verified_at = null;
                $user->status = 1; // optional reset to active
                break;
            case 1:
                // Activate business card
                BusinessCard::where('user_id', $userId)->update(['status' => 1]);

                $user->email_verified_at = Carbon::now();
                $user->status = 1;
                break;
            case 2:
                // Deactivate business card
                BusinessCard::where('user_id', $userId)->update(['status' => 0]);

                $user->status = 2; // banned
                break;
            default:
                // Activate business card
                BusinessCard::where('user_id', $userId)->update(['status' => 1]);

                $user->email_verified_at = Carbon::now();
                $user->status = 1;
                break;
        }

        $user->save();

        return back()->with('success', trans('Updated!'));
    }

    // Undelete Customer
    public function undeleteCustomer(Request $request)
    {
        // Queries
        $user = User::where('user_id', $request->query('id'))->first();

        if (!$user) {
            return back()->with('failed', trans('Not Found!'));
        }

        // Check if email is exists
        if (User::where('email', str_replace('deleted', '', $user->email))->count() > 0) {
            return back()->with('failed', trans('Unable to undelete user. Email already exists.'));
        }

        // Update email
        $user->email = str_replace('deleted', '', $user->email);

        // Soft delete (set status = 1)
        $user->status = 1;
        $user->save();

        return back()->with('success', trans('Customer undeleted!'));
    }
}
