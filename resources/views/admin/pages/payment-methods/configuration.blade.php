@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .ts-control {
            line-height: 1.7 !important;
        }

        .reduce-control {
            line-height: 1.7 !important;
        }

        .list-group-item {
            padding: 0.9rem 0rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.payment.methods') }}" class="text-secondary text-decoration-none">
                                    {{ __('Payment Gateways') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Configuration') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __($gateway_details->payment_gateway_name) }} {{ __('Configuration') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Configure API keys, credentials, environments, sandbox setups, and parameters for this gateway.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-md-6 col-sm-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form id="payment-config-form"
                            action="{{ route('admin.update.payment.configuration', $gateway_details->payment_gateway_id) }}"
                            method="post" class="card border-0 shadow-sm">
                            @csrf

                            <!-- Realtime Ajax Test Alert Container -->
                            <div id="test-connection-alert" class="px-3 pt-3" style="display: none;"></div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            {{-- Paypal --}}
                                            @if ($gateway_details->payment_gateway_id == '60964401751ab')
                                                {{-- Mode --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Mode') }}</label>
                                                        <select type="text" class="form-select"
                                                            placeholder="{{ __('Select a payment mode') }}"
                                                            id="paypal_mode" name="paypal_mode" required>
                                                            <option value="sandbox"
                                                                {{ $config[3]->config_value == 'sandbox' ? 'selected' : '' }}>
                                                                {{ __('Sandbox') }}</option>
                                                            <option value="live"
                                                                {{ $config[3]->config_value == 'live' ? 'selected' : '' }}>
                                                                {{ __('Live') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Client Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Client Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paypal_client_key" value="{{ $config[4]->config_value }}"
                                                            placeholder="{{ __('Client Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Secret --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label" required>{{ __('Secret') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paypal_secret" value="{{ $config[5]->config_value }}"
                                                            placeholder="{{ __('Secret') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Razorpay --}}
                                            @if ($gateway_details->payment_gateway_id == '60964410731d9')
                                                {{-- Client Key --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Client Key') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="razorpay_client_key"
                                                            value="{{ $config[6]->config_value }}"
                                                            placeholder="{{ __('Client Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Secret --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Secret') }}</label>
                                                        <input type="text" class="form-control" name="razorpay_secret"
                                                            value="{{ $config[7]->config_value }}"
                                                            placeholder="{{ __('Secret') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- PhonePe --}}
                                            @if ($gateway_details->payment_gateway_id == '19065566166715')
                                                {{-- Client ID --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Client ID') }}</label>
                                                        <input type="text" class="form-control" name="clientId"
                                                            value="{{ $config[77]->config_value }}"
                                                            placeholder="{{ __('Client ID') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Client Version --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Client Version') }}</label>
                                                        <input type="text" class="form-control" name="clientVersion"
                                                            value="{{ $config[78]->config_value }}"
                                                            placeholder="{{ __('Client Version') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Client Secret --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Client Secret') }}</label>
                                                        <input type="text" class="form-control" name="clientSecret"
                                                            value="{{ $config[79]->config_value }}"
                                                            placeholder="{{ __('Client Secret') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Stripe --}}
                                            @if ($gateway_details->payment_gateway_id == '60964410732t9')
                                                {{-- Publishable Key --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Publishable Key') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="stripe_publishable_key"
                                                            value="{{ $config[9]->config_value }}"
                                                            placeholder="{{ __('Publishable Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Secret --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Secret') }}</label>
                                                        <input type="text" class="form-control" name="stripe_secret"
                                                            value="{{ $config[10]->config_value }}"
                                                            placeholder="{{ __('Secret') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Paystack --}}
                                            @if ($gateway_details->payment_gateway_id == '60964410736592')
                                                {{-- Publishable Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Public Key') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="paystack_public_key"
                                                            value="{{ $config[33]->config_value }}"
                                                            placeholder="{{ __('Public Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Secret --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Secret Key') }}</label>
                                                        <input type="text" class="form-control" name="paystack_secret"
                                                            value="{{ $config[34]->config_value }}"
                                                            placeholder="{{ __('Secret Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Merchant Email --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Merchant Email') }}</label>
                                                        <input type="text" class="form-control" name="merchant_email"
                                                            value="{{ $config[36]->config_value }}"
                                                            placeholder="{{ __('Merchant Email') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Mollie --}}
                                            @if ($gateway_details->payment_gateway_id == '6096441071589632')
                                                {{-- Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Key') }}</label>
                                                        <input type="text" class="form-control" name="mollie_key"
                                                            value="{{ $config[37]->config_value }}"
                                                            placeholder="{{ __('Key') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Mercadopago --}}
                                            @if ($gateway_details->payment_gateway_id == '776111730465')
                                                {{-- Public Key --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Public Key') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="mercado_pago_public_key"
                                                            value="{{ $config[47]->config_value }}"
                                                            placeholder="{{ __('Public Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Access Token --}}
                                                <div class="col-md-12 col-xl-6">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Access Token') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="mercado_pago_access_token"
                                                            value="{{ $config[48]->config_value }}"
                                                            placeholder="{{ __('Access Token') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Toyyibpay --}}
                                            @if ($gateway_details->payment_gateway_id == '767510608137')
                                                {{-- Mode --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Mode') }}</label>
                                                        <select type="text" class="form-select"
                                                            placeholder="{{ __('Select a payment mode') }}"
                                                            id="toyyibpay_mode" name="toyyibpay_mode" required>
                                                            <option value="sandbox"
                                                                {{ $config[54]->config_value == 'sandbox' ? 'selected' : '' }}>
                                                                {{ __('Sandbox') }}</option>
                                                            <option value="live"
                                                                {{ $config[54]->config_value == 'live' ? 'selected' : '' }}>
                                                                {{ __('Live') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                {{-- Public Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('API Key') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="toyyibpay_api_key"
                                                            value="{{ $config[49]->config_value }}"
                                                            placeholder="{{ __('API Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Category Code --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Category Code') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="toyyibpay_category_code"
                                                            value="{{ $config[50]->config_value }}"
                                                            placeholder="{{ __('Category Code') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Flutterwave --}}
                                            @if ($gateway_details->payment_gateway_id == '754201940107')
                                                {{-- Public Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Public Key') }}</label>
                                                        <input type="text" class="form-control" name="flw_public_key"
                                                            value="{{ $config[51]->config_value }}"
                                                            placeholder="{{ __('Public Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Secret Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Secret Key') }}</label>
                                                        <input type="text" class="form-control" name="flw_secret_key"
                                                            value="{{ $config[52]->config_value }}"
                                                            placeholder="{{ __('Secret Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Encryption Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Encryption Key') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="flw_encryption_key"
                                                            value="{{ $config[53]->config_value }}"
                                                            placeholder="{{ __('Encryption Key') }}" required>
                                                    </div>
                                                </div>

                                                {{-- Hash Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Hash Key') }}</label>
                                                        <input type="text" class="form-control" name="flw_hash"
                                                            value="{{ $config[96]->config_value }}"
                                                            placeholder="{{ __('Hash Key') }}" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Paddle --}}
                                            @if ($gateway_details->payment_gateway_id == '5992737427969')
                                                {{-- Paddle Environment --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Paddle Environment') }}</label>
                                                        <select name="paddle_environment" id="paddle_environment"
                                                            class="form-select" required>
                                                            <option value="true"
                                                                {{ $config[64]->config_value == 'true' ? 'selected' : '' }}>
                                                                {{ __('Sandbox') }}</option>
                                                            <option value="false"
                                                                {{ $config[64]->config_value == 'false' ? 'selected' : '' }}>
                                                                {{ __('Production') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Paddle Seller ID --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Paddle Seller ID') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paddle_seller_id"
                                                            value="{{ $config[65]->config_value }}"
                                                            placeholder="{{ __('Paddle Seller ID') }}" autocomplete="off"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- Paddle API Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Paddle API Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paddle_api_key" value="{{ $config[66]->config_value }}"
                                                            placeholder="{{ __('Paddle API Key') }}" autocomplete="off"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- Paddle Client Side Token --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Paddle Client Side Token') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paddle_client_side_token"
                                                            value="{{ $config[67]->config_value }}"
                                                            placeholder="{{ __('Paddle Client Side Token') }}"
                                                            autocomplete="off" required>
                                                    </div>
                                                </div>

                                                {{-- Paddle callback url (disabled) --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Paddle Callback Url') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paddle_callback_url"
                                                            value="{{ route('paddle.payment.status') }}"
                                                            placeholder="{{ __('Paddle Callback Url') }}"
                                                            autocomplete="off" disabled>
                                                    </div>
                                                </div>

                                                {{-- Paddle Webhook Url (disabled) --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Paddle Webhook Url') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paddle_webhook_url"
                                                            value="{{ route('paddle.payment.webhook') }}"
                                                            placeholder="{{ __('Paddle Webhook Url') }}"
                                                            autocomplete="off" disabled>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- PayTR --}}
                                            @if ($gateway_details->payment_gateway_id == '5992737427970')
                                                {{-- PayTR Environment --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Mode') }}</label>
                                                        <select name="paytr_mode" id="paytr_mode" class="form-select"
                                                            required>
                                                            <option value="1"
                                                                {{ $config[71]->config_value == '1' ? 'selected' : '' }}>
                                                                {{ __('Sandbox') }}</option>
                                                            <option value="0"
                                                                {{ $config[71]->config_value == '0' ? 'selected' : '' }}>
                                                                {{ __('Production') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- PayTR Merchant ID --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Merchant ID') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paytr_merchant_id"
                                                            value="{{ $config[68]->config_value }}"
                                                            placeholder="{{ __('Merchant ID') }}" autocomplete="off"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- PayTR Merchant Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Merchant Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paytr_merchant_key"
                                                            value="{{ $config[69]->config_value }}"
                                                            placeholder="{{ __('Merchant Key') }}" autocomplete="off"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- PayTR merchant Salt Key --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Merchant Salt Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paytr_merchant_salt_key"
                                                            value="{{ $config[70]->config_value }}"
                                                            placeholder="{{ __('Merchant Salt Key') }}"
                                                            autocomplete="off" required>
                                                    </div>
                                                </div>

                                                {{-- PayTR Callback URL --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Callback URL') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paytr_callback_url"
                                                            value="{{ route('paytr.payment.status') }}"
                                                            placeholder="{{ __('Callback URL') }}" autocomplete="off"
                                                            disabled>
                                                    </div>
                                                </div>

                                                {{-- PayTR Failure URL --}}
                                                <div class="col-md-12 col-md-12 ">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Failure URL') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="paytr_failure_url"
                                                            value="{{ route('paytr.payment.failure') }}"
                                                            placeholder="{{ __('Failure URL') }}" autocomplete="off"
                                                            disabled>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Xendit --}}
                                            @if ($gateway_details->payment_gateway_id == '278523098674')
                                                {{-- Xendit Secret Key --}}
                                                <div class="col-xl-6">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Xendit Secret Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="xendit_secret_key"
                                                            value="{{ $config[72]->config_value }}"
                                                            placeholder="{{ __('Xendit Secret Key') }}"
                                                            autocomplete="off" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Cashfree Settings --}}
                                            @if ($gateway_details->payment_gateway_id == '278523098675')
                                                {{-- Cashfree Settings --}}
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Cashfree Mode') }}</label>
                                                        <select class="form-control" name="cashfree_mode"
                                                            id="cashfree_mode" required>
                                                            <option value="live"
                                                                @if ($config[84]->config_value == 'live') selected @endif>
                                                                {{ __('Live') }}
                                                            </option>
                                                            <option value="test"
                                                                @if ($config[84]->config_value == 'test') selected @endif>
                                                                {{ __('Test') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Cashfree API ID --}}
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Cashfree App ID') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="cashfree_app_id"
                                                            value="{{ $config[85]->config_value }}"
                                                            placeholder="{{ __('Cashfree App ID') }}" autocomplete="off"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- Cashfree Secret Key --}}
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Cashfree Secret Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="cashfree_secret_key"
                                                            value="{{ $config[86]->config_value }}"
                                                            placeholder="{{ __('Cashfree Secret Key') }}"
                                                            autocomplete="off" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Iyzipay Settings --}}
                                            @if ($gateway_details->payment_gateway_id == '278523098676')
                                                {{-- Iyzipay Settings --}}
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Iyzipay Mode') }}</label>
                                                        <select class="form-control" name="iyzipay_mode"
                                                            id="iyzipay_mode">
                                                            <option value="sandbox"
                                                                {{ $config[89]->config_value == 'sandbox' ? 'selected' : '' }}>
                                                                {{ __('Sandbox') }}</option>
                                                            <option value="production"
                                                                {{ $config[89]->config_value == 'production' ? 'selected' : '' }}>
                                                                {{ __('Production') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Iyzipay API Key -->
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Iyzipay API Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="iyzipay_api_key"
                                                            value="{{ $config[90]->config_value }}"
                                                            placeholder="{{ __('Iyzipay API Key') }}" autocomplete="off"
                                                            required>
                                                    </div>
                                                </div>

                                                {{-- Iyzipay Secret Key --}}
                                                <div class="col-md-12 ">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Iyzipay Secret Key') }}</label>
                                                        <input type="text" class="form-control reduce-control"
                                                            name="iyzipay_secret_key"
                                                            value="{{ $config[91]->config_value }}"
                                                            placeholder="{{ __('Iyzipay Secret Key') }}"
                                                            autocomplete="off" required>
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Offline (Bank Transfer) Settings --}}
                                            @if ($gateway_details->payment_gateway_id == '659644107y2g5')
                                                {{-- Offline (Bank Transfer) Settings --}}
                                                <div class="col-xl-12">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('Offline (Bank Transfer) Details') }}</label>
                                                        <textarea class="form-control" name="bank_transfer" id="bank_transfer" rows="3"
                                                            placeholder="{{ __('Offline (Bank Transfer) Details') }}" required>{{ $config[31]->config_value }}</textarea>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Simplified right-aligned card footer with no Cancel button -->
                            <div class="card-footer text-end">
                                <!-- Test Connection button code ... -->
                                @if ($gateway_details->payment_gateway_id != '659644107y2g5')
                                    <button type="button" id="btn-test-connection" class="btn btn-dark btn-md me-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-plug-connected me-1">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M7 12l5 5l-1.5 1.5a3.536 3.536 0 1 1 -5 -5l1.5 -1.5" />
                                            <path d="M17 12l-5 -5l1.5 -1.5a3.536 3.536 0 1 1 5 5l-1.5 1.5" />
                                            <path d="M3 21l2.5 -2.5" />
                                            <path d="M18.5 5.5l2.5 -2.5" />
                                            <path d="M10 11l-2 2" />
                                            <path d="M13 14l-2 2" />
                                        </svg>
                                        <span id="btn-test-text">{{ __('Test Connection') }}</span>
                                    </button>
                                @endif

                                <!-- Save button code ... -->
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                        <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                        <path d="M14 4l0 4h-6l0 -4" />
                                    </svg>
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const testBtn = document.getElementById('btn-test-connection');
            const form = document.getElementById('payment-config-form');
            const alertBox = document.getElementById('test-connection-alert');
            const btnText = document.getElementById('btn-test-text');

            if (testBtn) {
                testBtn.addEventListener('click', function() {
                    // Read all current form inputs
                    const formData = new FormData(form);

                    // Set loading button state
                    testBtn.disabled = true;
                    btnText.innerHTML = 'Testing...';
                    alertBox.style.display = 'none';

                    fetch("{{ route('admin.test.payment.connection', $gateway_details->payment_gateway_id) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            testBtn.disabled = false;
                            btnText.innerHTML = '{{ __('Test Connection') }}';

                            // Display success or failure alert dynamically
                            alertBox.style.display = 'block';
                            if (data.success) {
                                alertBox.innerHTML = `
                        <div class="alert alert-important alert-success alert-dismissible mb-0" role="alert">
                            <div class="d-flex">
                                <div><strong>Success!</strong> ${data.message}</div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>`;
                            } else {
                                alertBox.innerHTML = `
                        <div class="alert alert-important alert-danger alert-dismissible mb-0" role="alert">
                            <div class="d-flex">
                                <div><strong>Failed!</strong> ${data.message}</div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>`;
                            }
                        })
                        .catch(error => {
                            // Handle system / network errors
                            testBtn.disabled = false;
                            btnText.innerHTML = '{{ __('Test Connection') }}';
                            alertBox.style.display = 'block';
                            alertBox.innerHTML = `
                    <div class="alert alert-important alert-danger alert-dismissible mb-0" role="alert">
                        <div><strong>Error:</strong> Failed to reach server. Check network connection.</div>
                    </div>`;
                        });
                });
            }
        });

        // Dark Mode
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        tinymce.init({
            selector: 'textarea#bank_transfer',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'code | undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | pagebreak | link',
            toolbar_sticky: true,
            height: 200,
            menubar: false,
            statusbar: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'dark'
            ] : [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'default'
            ],
            setup: function(editor) {
                editor.on('init', function() {
                    if (document.body.dataset.bsTheme === 'dark') {
                        editor.getBody().setAttribute('data-bs-theme', 'dark');
                    }
                });
            },
            content_style: isDark ? `
            body {
                    background-color: #151f2c !important;
                    font-family: "Figtree", sans-serif !important;
                }

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    color: rgba(34,47,62,.5) !important;
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            ` : `body {
                    font-family: "Figtree", sans-serif !important;
                }

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',

            // Correct mobile settings
            mobile: {
                plugins: 'paste',
                toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });

        // Array of element IDs
        var elementSelectors = ['paypal_mode', 'toyyibpay_mode', 'paddle_environment', 'paytr_mode', 'cashfree_mode',
            'iyzipay_mode'
        ];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });
    </script>
@endsection
