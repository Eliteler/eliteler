<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plan;
use App\Services\Admin\CustomerPlanService;
use App\Services\Admin\CustomerService;
use App\Support\Admin\AdminPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CreateCustomerController extends Controller
{
    public function __construct(
        private CustomerService $customers,
        private CustomerPlanService $plans,
    ) {
        $this->middleware('auth');
    }

    // New customer form
    public function createCustomer()
    {
        return Inertia::render('customers/form', [
            'customer' => null,
            'plans'    => Plan::where('status', 1)->orderBy('plan_price')->get(['plan_id', 'plan_name', 'plan_price', 'validity'])
                ->map(fn (Plan $plan) => [
                    'id'       => $plan->plan_id,
                    'name'     => $plan->plan_name,
                    'price'    => AdminPermissions::price((float) $plan->plan_price),
                    'validity' => (int) $plan->validity,
                ]),
        ]);
    }

    // Save new customer
    public function saveCustomer(Request $request)
    {
        $data = $request->validate([
            'full_name'      => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email',
            'password'       => 'required|string|min:6',
            'plan_id'        => ['nullable', Rule::exists('plans', 'plan_id')->where('status', 1)],
            'status'         => 'required|in:0,1',
            'welcome_email'  => 'boolean',
            'reset_password' => 'boolean',
        ], [
            'email.unique' => __('This email already exists.'),
        ]);

        $plan = ! empty($data['plan_id']) ? Plan::where('plan_id', $data['plan_id'])->first() : null;

        $user = DB::transaction(function () use ($data, $plan) {
            $user = $this->customers->create($data);

            if ($plan) {
                $this->plans->applyToNewCustomer($user, $plan);
        }

        $user->save();

            if ($plan) {
                $this->plans->recordNewCustomerPayment($user, $plan);
        }

            return $user;
        });

        if ($request->boolean('welcome_email')) {
            $this->customers->sendWelcomeEmail($user);
        }

        $resetSent = ! $request->boolean('reset_password') || $this->customers->sendPasswordResetEmail($user);

        if (function_exists('zapier_dispatch')) {
            zapier_dispatch('new_user', [
                'registered_at'   => now()->toDateTimeString(),
                'user_id'         => $user->user_id,
                'name'            => $user->name,
                'email'           => $user->email,
                'mobile_number'   => '',
                'address'         => '',
                'city'            => '',
                'state'           => '',
                'country'         => '',
                'zipcode'         => '',
                'user_tax_number' => '',
                'registered_by'   => 'Admin',
            ]);
        }

        return redirect()->route('admin.view.customer', $user->user_id)->with(
            $resetSent ? 'success' : 'failed',
            $resetSent
                ? __('Customer created.')
                : __('Customer created, but the password reset email could not be sent.')
            );
    }
}
