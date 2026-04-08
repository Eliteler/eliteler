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

use App\Gateway;
use App\Setting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
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

    // All Payment Methods
    public function paymentMethods(Request $request)
    {
        if ($request->ajax()) {
            $payment_methods = Gateway::where('status', '!=', "-1")->orderBy('created_at', 'desc')->get();

            return DataTables::of($payment_methods)
                ->addIndexColumn()
                ->addColumn('payment_gateway_logo', function ($payment_method) {
                    return '<span class="avatar me-2" style="background-image: url(' . asset($payment_method->payment_gateway_logo) . ')"></span>';
                })
                ->addColumn('payment_gateway_name', function ($payment_method) {
                    return __($payment_method->payment_gateway_name);
                })
                ->addColumn('is_status', function ($payment_method) {
                    if ($payment_method->is_status == 'disabled') {
                        return '<span class="badge badge-outline text-red">' . __('Not Installed Yet') . '</span>';
                    } else {
                        return '<span class="badge badge-outline text-green">' . __('Installed') . '</span>';
                    }
                })
                ->addColumn('status', function ($payment_method) {
                    if ($payment_method->status == 0) {
                        return '<span class="badge bg-red text-white">' . __('Deactive') . '</span>';
                    } else {
                        return '<span class="badge bg-green text-white">' . __('Active') . '</span>';
                    }
                })
                ->addColumn('action', function ($payment_method) {
                    // Edit 
                    $editUrl = route('admin.edit.payment.method', $payment_method->payment_gateway_id);
                    $actionBtn = '<a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>';

                    $excludedGateways = ["742822329180", "300523098676", "29342927119", "665719068230"];

                    $pluginConfigs = [
                        'Esewa' => [
                            'id'    => '742822329180',
                            'route' => 'admin.plugin.esewa',
                        ],
                        'Coinbase' => [
                            'id'    => '300523098676',
                            'route' => 'admin.plugin.coinbase',
                        ],
                        'Moneroo' => [
                            'id'    => '29342927119',
                            'route' => 'admin.plugin.moneroo',
                        ],
                        'Fonepay' => [
                            'id'    => '665719068230',
                            'route' => 'admin.plugin.fonepay',
                        ],
                    ];

                    // Default configure (non-plugin gateways)
                    if (!in_array($payment_method->payment_gateway_id, $excludedGateways)) {
                        $actionBtn .= '<a class="dropdown-item" href="' .
                            route('admin.configure.payment', $payment_method->payment_gateway_id) .
                            '">' . __('Configure') . '</a>';
                    }

                    // Plugin-based configure
                    foreach ($pluginConfigs as $plugin => $config) {
                        if (
                            $payment_method->payment_gateway_id === $config['id'] &&
                            is_dir(base_path('plugins/' . $plugin))
                        ) {
                            $actionBtn .= '<a class="dropdown-item" href="' .
                                route($config['route']) .
                                '">' . __('Configure') . '</a>';
                        }
                    }

                    // Activate / Deactivate
                    if ($payment_method->status == 0) {
                        $actionBtn .= '<a class="dropdown-item" href="#" onclick="getPaymentMethod(`' . $payment_method->payment_gateway_id . '`, `activated`); return false;">' . __('Activate') . '</a>';
                    } else {
                        $actionBtn .= '<a class="dropdown-item" href="#" onclick="getPaymentMethod(`' . $payment_method->payment_gateway_id . '`, `deactivated`); return false;">' . __('Deactivate') . '</a>';
                    }

                    // Delete
                    $actionBtn .= '<a class="dropdown-item" href="#" onclick="getPaymentMethod(`' . $payment_method->payment_gateway_id . '`, `deleted`); return false;">' . __('Delete') . '</a>';

                    return '<a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
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
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="nav-item dropdown">
                                    ' . $actionBtn . '
                                </div>
                            </div>';
                })
                ->rawColumns(['payment_gateway_logo', 'is_status', 'status', 'action'])
                ->make(true);
        }

        // Queries
        $settings = Setting::where('status', 1)->first();

        return view('admin.pages.payment-methods.payment-methods', compact('settings'));
    }

    // Add Payment Method
    public function addPaymentMethod()
    {
        $settings = Setting::where('status', 1)->first();
        return view('admin.pages.payment-methods.add-payment-method', compact('settings'));
    }

    // Save Payment Method
    public function savePaymentMethod(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'payment_gateway_logo' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'payment_gateway_name' => 'required|string|max:255',
            'client_id'            => 'required|string',
            'secret_key'           => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Store logo image
        $logoFile = $request->file('payment_gateway_logo');
        $logoExtension = $logoFile->getClientOriginalExtension();
        $logoFilename = 'IMG-' . time() . '.' . $logoExtension;
        $logoPath = 'img/payment-method/' . $logoFilename;

        // Save the image to storage/app/public/img/payment-method/
        Storage::disk('public')->put($logoPath, file_get_contents($logoFile));

        // Save gateway to database
        $paymentMethod = new Gateway();
        $paymentMethod->payment_gateway_id = uniqid();
        $paymentMethod->payment_gateway_logo = 'storage/' . $logoPath; // Public access path
        $paymentMethod->payment_gateway_name = $request->payment_gateway_name;
        $paymentMethod->client_id = $request->client_id;
        $paymentMethod->secret_key = $request->secret_key;
        $paymentMethod->save();

        return redirect()->route('admin.add.payment.method')->with('success', trans('Created!'));
    }

    // Edit Payment Method
    public function editPaymentMethod(Request $request, $id)
    {
        $gateway_id = $request->id;
        if ($gateway_id == null) {
            return view('errors.404');
        } else {
            $gateway_details = Gateway::where('payment_gateway_id', $gateway_id)->first();
            $settings = Setting::where('status', 1)->first();
            return view('admin.pages.payment-methods.edit-payment-gateway', compact('gateway_details', 'settings'));
        }
    }

    // Update Payment Method
    public function updatePaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_gateway_id' => 'required',
            'payment_gateway_name' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        if ($request->hasFile('payment_gateway_image')) {
            // Validate image
            $validator = Validator::make($request->all(), [
                'payment_gateway_image' => 'required|mimes:jpeg,png,jpg,gif,svg,webp|max:' . env('SIZE_LIMIT'),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->first())->withInput();
            }

            // Generate a clean filename
            $imageFile = $request->file('payment_gateway_image');
            $extension = $imageFile->getClientOriginalExtension();
            $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $cleanName = Str::slug($originalName);
            $fileName = 'IMG-' . $cleanName . '-' . time() . '.' . $extension;

            // Upload to storage/app/public/img/payment-method/
            $imagePath = 'img/payment-method/' . $fileName;
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));

            // Update DB with new image path and name
            Gateway::where('payment_gateway_id', $request->payment_gateway_id)->update([
                'payment_gateway_logo' => 'storage/' . $imagePath,
                'display_name' => $request->payment_gateway_name,
            ]);
        }

        Gateway::where('payment_gateway_id', $request->payment_gateway_id)->update([
            'display_name' => $request->payment_gateway_name
        ]);

        return redirect()->route('admin.edit.payment.method', $request->payment_gateway_id)->with('success', trans('Updated!'));
    }

    // Update Payment Method
    public function deletePaymentMethod(Request $request)
    {
        // Queries
        $payment_gateway_details = Gateway::where('payment_gateway_id', $request->query('id'))->first();

        // Check gateways exist
        if (!$payment_gateway_details) {
            return redirect()->route('admin.payment.methods')->with('failed', __('Not Found!'));
        }

        if ($request->query('status') == 'deleted') {
            $status = -1;
        } else if ($request->query('status') == 'deactivated') {
            $status = 0;
        } else {
            $status = 1;
        }

        // Update payment method
        Gateway::where('payment_gateway_id', $request->query('id'))->update([
            'status' => $status
        ]);

        return redirect()->route('admin.payment.methods', $request->query('id'))->with('success', trans('Updated!'));
    }
}
