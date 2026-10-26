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
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Services\Admin\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Payment gateways: one page with an on/off switch per gateway and a single
 * dialog for its name, logo and credentials. Plugin gateways keep their own
 * settings pages. gateways.status: 1 active, 0 inactive, -1 deleted.
 */
class PaymentMethodController extends Controller
{
    public function __construct(private PaymentGatewayService $gateways)
    {
        $this->middleware('auth');
    }

    public function paymentMethods(Request $request)
    {
        $config = $this->gateways->config();

        $gateways = Gateway::where('status', '!=', -1)->orderBy('created_at')->get()->map(function ($gateway) use ($config) {
            $id     = $gateway->payment_gateway_id;
            $plugin = $gateway->plugin_id ? 'admin.plugin.' . strtolower($gateway->plugin_id) : null;

            return [
                'id'          => $id,
                'name'        => $gateway->payment_gateway_name,
                'displayName' => $gateway->display_name ?: $gateway->payment_gateway_name,
                'logo'        => $gateway->payment_gateway_logo ? asset($gateway->payment_gateway_logo) : null,
                'active'      => (int) $gateway->status === 1,
                'plugin'      => (bool) $gateway->plugin_id,
                'settingsUrl' => $plugin && Route::has($plugin) ? route($plugin) : null,
                'fields'      => $this->gateways->form($id, $config),
                'links'       => $this->gateways->links($id),
                'configured'  => $gateway->plugin_id ? null : $this->gateways->configured($id, $config),
                'testable'    => $this->gateways->hasFields($id) && $id !== PaymentGatewayService::BANK_TRANSFER,
            ];
        });

        return Inertia::render('payment-methods/index', [
            'gateways'    => $gateways,
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
            'open'        => $request->query('edit'),
        ]);
    }

    // Old sub-pages now open the dialog on the list page
    public function addPaymentMethod()
    {
        return redirect()->route('admin.payment.methods');
    }

    public function editPaymentMethod($id)
    {
        return redirect()->route('admin.payment.methods', ['edit' => $id]);
    }

    public function updatePaymentMethod(Request $request, $id)
    {
        $gateway = Gateway::where('payment_gateway_id', $id)->where('status', '!=', -1)->firstOrFail();

        $request->validate([
            'display_name' => 'required|string|max:191',
            'logo'         => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:' . env('SIZE_LIMIT', 2048),
        ] + $this->gateways->rules($id), [], [
            'display_name' => __('name'),
            'logo'         => __('logo'),
        ] + $this->gateways->attributes($id));

        $data = ['display_name' => $request->input('display_name')];

        if ($request->hasFile('logo')) {
            StorageHelper::deleteFile($gateway->payment_gateway_logo);
            $data['payment_gateway_logo'] = StorageHelper::uploadImage($request->file('logo'), 'img/payment-method', Str::slug($gateway->payment_gateway_name) . '-' . time());
        }

        Gateway::where('payment_gateway_id', $id)->update($data);
        $this->gateways->save($id, $request->all());

        return back()->with('success', __(':name settings saved.', ['name' => $data['display_name']]));
    }

    public function statusPaymentMethod(Request $request)
    {
        $data = $request->validate([
            'id'     => 'required|string',
            'status' => 'required|in:activated,deactivated,deleted',
        ]);

        $gateway = Gateway::where('payment_gateway_id', $data['id'])->where('status', '!=', -1)->firstOrFail();
        $name    = $gateway->display_name ?: $gateway->payment_gateway_name;

        Gateway::where('payment_gateway_id', $data['id'])->update([
            'status' => ['activated' => 1, 'deactivated' => 0, 'deleted' => -1][$data['status']],
        ]);

        return back()->with('success', [
            'activated'   => __(':name is now available at checkout.', ['name' => $name]),
            'deactivated' => __(':name is hidden from checkout.', ['name' => $name]),
            'deleted'     => __(':name deleted.', ['name' => $name]),
        ][$data['status']]);
    }
}
