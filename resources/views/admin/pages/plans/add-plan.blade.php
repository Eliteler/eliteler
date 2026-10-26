@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
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
                                <a href="{{ route('admin.plans') }}" class="text-secondary text-decoration-none">
                                    {{ __('Plans') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('New Plan') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Add New Plan') }}
                        </h2>
                        <!-- Helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Create a subscription plan by setting its pricing, features, limits, and access options.') }}
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
                    <div class="col-12">
                        <form action="{{ route('admin.save.plan') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <h2 class="page-title mb-2 fs-3">{{ __('Basic Details') }}</h2>
                                            </div>

                                            {{-- Recommended Plan (Form Switch) --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label mb-2">{{ __('Recommended Plan') }}</label>
                                                    <label class="form-check form-switch mt-2">
                                                        <input type="hidden" name="recommended" value="off">
                                                        <input class="form-check-input" type="checkbox" name="recommended"
                                                            id="recommended" value="on">
                                                        <span class="form-check-label">{{ __('Yes') }}</span>
                                                    </label>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('Highlight this plan as the recommended option.') }}</small>
                                                </div>
                                            </div>

                                            {{-- Private Plan (Form Switch) --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label mb-2">{{ __('Private Plan') }}</label>
                                                    <label class="form-check form-switch mt-2">
                                                        <input type="hidden" name="is_private" value="off">
                                                        <input class="form-check-input" type="checkbox" name="is_private"
                                                            id="is_private" value="on">
                                                        <span class="form-check-label">{{ __('Yes') }}</span>
                                                    </label>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('This plan is not publicly available. Only the admin can assign it to specific customers.') }}</small>
                                                </div>
                                            </div>

                                            {{-- Plan Type (Select) --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan Type') }}</label>
                                                    <select class="form-select plan_type" name="plan_type"
                                                        onchange="checkPlanType(this)" required>
                                                        <option value="" disabled>{{ __('Choose a plan type') }}
                                                        </option>
                                                        <option value="BOTH" selected>
                                                            {{ __('Digital Business Card + Store') }}</option>
                                                        <option value="VCARD">{{ __('Digital Business Card') }}</option>
                                                        <option value="STORE">{{ __('Store') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Plan Name (Input) --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan Name') }}</label>
                                                    <input type="text" class="form-control" name="plan_name"
                                                        placeholder="{{ __('Enter plan name') }}" required>
                                                </div>
                                            </div>

                                            {{-- Description (Input) --}}
                                            <div class="col-8">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Description') }}</label>
                                                    <input type="text" class="form-control" name="plan_description"
                                                        placeholder="{{ __('Enter a short description') }}" required>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-4">
                                                <h2 class="page-title mb-2 fs-3">{{ __('Pricing') }}</h2>
                                            </div>

                                            {{-- Price --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Price') }}</label>
                                                    <input type="number" class="form-control" id="plan_price"
                                                        name="plan_price" min="0" step="0.01"
                                                        placeholder="{{ __('Price') }}" oninput="handlePricingLogic()"
                                                        required>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('Use 0 to make this a free plan.') }}</small>
                                                </div>
                                            </div>

                                            {{-- Plan Term --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan Term') }}</label>
                                                    <select class="form-select plan_term" id="plan_term" name="plan_term"
                                                        onchange="handlePricingLogic()" required>
                                                        <option value="monthly" selected>{{ __('Monthly') }}</option>
                                                        <option value="yearly">{{ __('Yearly') }}</option>
                                                        <option value="custom">{{ __('Custom') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Validity (Days) --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label for="validity"
                                                        class="form-label required">{{ __('Validity (Days)') }}</label>
                                                    <input type="number" class="form-control" id="validity"
                                                        name="validity" min="0" max="9999"
                                                        placeholder="{{ __('Validity') }}" required>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('Use 9999 for forever validity.') }}</small>
                                                </div>
                                            </div>

                                            {{-- Trial period --}}
                                            <div class="col-md-4 col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Trial period') }}</label>
                                                    <input type="number" class="form-control" name="trial_period"
                                                        min="0" max="9999"
                                                        placeholder="{{ __('Enter trial period in days') }}"
                                                        value="{{ old('trial_period', 0) }}" required>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('Enter 0 to disable the free trial.') }}</small>
                                                </div>
                                            </div>

                                            {{-- vCard Features --}}
                                            <div class="row g-3" id="vcard-features">
                                                <div class="col-12 mt-4">
                                                    <h2 class="page-title mb-2 fs-3">{{ __('Business Card Features') }}
                                                    </h2>
                                                </div>

                                                {{-- No. of vCards --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Business Cards') }}</label>
                                                        <input type="number" class="form-control" name="no_of_vcards"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of business cards') }}"
                                                            value="{{ old('no_of_vcards') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Services --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Services') }}</label>
                                                        <input type="number" class="form-control" name="no_of_services"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of services') }}"
                                                            value="{{ old('no_of_services') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Products --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Products') }}</label>
                                                        <input type="number" class="form-control"
                                                            name="no_of_vcard_products" min="0" max="999"
                                                            placeholder="{{ __('Enter no. of products') }}"
                                                            value="{{ old('no_of_vcard_products') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Links --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Links') }}</label>
                                                        <input type="number" class="form-control" name="no_of_links"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of links') }}"
                                                            value="{{ old('no_of_links') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Payment Listed --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Payment Listed') }}</label>
                                                        <input type="number" class="form-control" name="no_of_payments"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of payments') }}"
                                                            value="{{ old('no_of_payments') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Testimonials --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Testimonials') }}</label>
                                                        <input type="number" class="form-control" name="no_testimonials"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of testimonials') }}"
                                                            value="{{ old('no_testimonials') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Galleries --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Galleries') }}</label>
                                                        <input type="number" class="form-control" name="no_of_galleries"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of galleries') }}"
                                                            value="{{ old('no_of_galleries') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of Enquiries --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Enquiries') }}</label>
                                                        <input type="number" class="form-control" name="no_of_enquires"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of enquiries') }}"
                                                            value="{{ old('no_of_enquires') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of AI Credits --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label required">
                                                            {{ __('AI Credits') }}
                                                            <span id="popover-element"
                                                                class="form-help bg-primary text-white ms-1"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                data-bs-html="true"
                                                                data-bs-content="<p>{{ __('1 AI Credit allows the customer to generate 1 Digital Business Card using AI. AI credits never expire and can be used at any time.') }}</p>"><small
                                                                    style="font-family: monospace;">i</small></span>
                                                        </label>
                                                        <input type="number" class="form-control" name="ai_credits"
                                                            min="-1"
                                                            placeholder="{{ __('Enter no. of AI credit') }}"
                                                            value="0" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use -1 for unlimited, or 0 if this plan should not include AI credits.') }}</small>
                                                    </div>
                                                </div>

                                                {{-- No. of trial AI vCards --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label required">
                                                            {{ __('AI Credits for Trial Period') }}
                                                            <span id="popover-element-trial"
                                                                class="form-help bg-primary text-white ms-1"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                data-bs-html="true"
                                                                data-bs-content="<p>{{ __('Number of AI credits provided to users during their trial period. These credits are available only while the trial is active. Any unused AI credits will automatically expire when the trial ends.') }}</p>"><small
                                                                    style="font-family: monospace;">i</small></span>
                                                        </label>
                                                        <input type="number" class="form-control"
                                                            name="trial_ai_credits" min="-1"
                                                            placeholder="{{ __('Enter no. of AI credit') }}"
                                                            value="0" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 0 if you do not want to provide AI credits with this plan.') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-8 col-lg-3"></div>

                                                {{-- Business Hours (Form Switch) --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label mb-2">{{ __('Business Hours') }}</label>
                                                        <label class="form-check form-switch mt-2">
                                                            <input type="hidden" name="business_hours" value="off">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="business_hours" value="on" checked>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Appointment / Appointments (Form Switch) --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label mb-2">{{ __('Appointments') }}</label>
                                                        <label class="form-check form-switch mt-2">
                                                            <input type="hidden" name="appointment" value="off">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="appointment" value="on" checked>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Service Booking (Form Switch) --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label mb-2">{{ __('Service Booking') }}</label>
                                                        <label class="form-check form-switch mt-2">
                                                            <input type="hidden" name="service_booking" value="off">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="service_booking" value="on" checked>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Contact Form (Form Switch) --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label class="form-label mb-2">{{ __('Contact Form') }}</label>
                                                        <label class="form-check form-switch mt-2">
                                                            <input type="hidden" name="contact_form" value="off">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="contact_form" value="on" checked>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Password Protected / Password Protection (Form Switch) --}}
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label mb-2">{{ __('Password Protection') }}</label>
                                                        <label class="form-check form-switch mt-2">
                                                            <input type="hidden" name="password_protected"
                                                                value="off">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="password_protected" value="on" checked>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Check Google Wallet --}}
                                                @if (is_dir(base_path('plugins/GoogleWallet')))
                                                    <div class="col-md-4 col-lg-3">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label mb-2">{{ __('Google Wallet') }}</label>
                                                            <label class="form-check form-switch mt-2">
                                                                <input type="hidden" name="google_wallet"
                                                                    value="off">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="google_wallet" value="on" checked>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 col-lg-3">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label required">{{ __('No. of Wallets') }}</label>
                                                            <input type="number" class="form-control"
                                                                name="no_of_google_wallets" min="0" max="999"
                                                                placeholder="{{ __('Enter no. of wallets') }}"
                                                                value="{{ old('no_of_google_wallets') }}" required>
                                                            <small
                                                                class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Store Features --}}
                                            <div class="row g-3" id="store-features">
                                                <div class="col-12 mt-4">
                                                    <h2 class="page-title mb-2 fs-3">{{ __('Store Features') }}</h2>
                                                </div>
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Stores') }}</label>
                                                        <input type="number" class="form-control" name="no_of_stores"
                                                            min="0" max="999"
                                                            placeholder="{{ __('Enter no. of stores') }}"
                                                            value="{{ old('no_of_stores') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Categories') }}</label>
                                                        <input type="number" class="form-control"
                                                            name="no_of_categories" min="0" max="999"
                                                            placeholder="{{ __('Enter no. of categories') }}"
                                                            value="{{ old('no_of_categories') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label required">{{ __('No. of Products') }}</label>
                                                        <input type="number" class="form-control"
                                                            name="no_of_store_products" min="0" max="999"
                                                            placeholder="{{ __('Enter no. of products') }}"
                                                            value="{{ old('no_of_store_products') }}" required>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12 mt-4">
                                                    <h2 class="page-title mb-2 fs-3">{{ __('Others') }}</h2>
                                                </div>

                                                {{-- Storage --}}
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label class="form-label required">{{ __('Storage') }}</label>
                                                        <div class="input-group mb-2">
                                                            <input type="number" class="form-control" name="storage"
                                                                min="0" max="999"
                                                                placeholder="{{ __('Enter storage') }}"
                                                                value="{{ old('storage') }}" required>
                                                            <span class="input-group-text">{{ __('MB per user') }}</span>
                                                        </div>
                                                        <small
                                                            class="text-muted d-block mt-1">{{ __('Use 999 for unlimited access to this feature.') }}</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-4">
                                                <h2 class="page-title mb-2 fs-3">{{ __('Additional features') }}</h2>
                                            </div>

                                            {{-- Custom domain --}}
                                            <div class="col-md-3 col-lg-3">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="custom_domain" value="off">
                                                    <input class="form-check-input" type="checkbox" id="custom_domain"
                                                        name="custom_domain" value="on">
                                                    <span class="form-check-label">{{ __('Custom Domain') }}</span>
                                                </label>
                                            </div>

                                            {{-- NFC Card --}}
                                            <div class="col-md-3 col-lg-3 d-none">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="nfc_card" value="off">
                                                    <input class="form-check-input" type="checkbox" id="nfc_card"
                                                        name="nfc_card" value="on" checked>
                                                    <span class="form-check-label">{{ __('NFC Card') }}</span>
                                                </label>
                                            </div>

                                            {{-- PWA --}}
                                            <div class="col-md-3 col-lg-3">
                                                <div class="d-flex align-items-center mt-2">
                                                    <div class="form-check form-switch mb-0">
                                                        <input type="hidden" name="pwa" value="off">
                                                        <input class="form-check-input" type="checkbox" id="pwa"
                                                            name="pwa" value="on">
                                                    </div>

                                                    <label for="pwa" class="form-check-label ms-2 mb-0">
                                                        {{ __('PWA') }}
                                                    </label>

                                                    <span class="form-help bg-primary text-white ms-2"
                                                        data-bs-toggle="popover" data-bs-placement="top"
                                                        data-bs-html="true"
                                                        data-bs-content="<p>{{ __('PWA stands for Progressive Web Application. PWAs are web applications that are built using common web technologies like HTML, CSS, JavaScript, and WebAssembly. They are designed to work on any platform with a standards-compliant browser, including desktop and mobile devices.') }}</p>">
                                                        <small style="font-family: monospace;">i</small>
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Advanced Settings --}}
                                            <div class="col-md-3 col-lg-3">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="advanced_settings" value="off">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="advanced_settings" name="advanced_settings" value="on">
                                                    <span class="form-check-label">{{ __('Advanced Settings') }}</span>
                                                </label>
                                            </div>

                                            {{-- Personalized Link --}}
                                            <div class="col-md-3 col-lg-3">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="personalized_link" value="off">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="personalized_link" name="personalized_link" value="on">
                                                    <span class="form-check-label">{{ __('Personalized Link') }}</span>
                                                </label>
                                            </div>

                                            {{-- Hide Branding --}}
                                            <div class="col-md-3 col-lg-3 mt-3">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="hide_branding" value="off">
                                                    <input class="form-check-input" type="checkbox" id="hide_branding"
                                                        name="hide_branding" value="on">
                                                    <span class="form-check-label">{{ __('Hide Branding') }}</span>
                                                </label>
                                            </div>

                                            {{-- Free Setup --}}
                                            <div class="col-md-3 col-lg-3 mt-3">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="free_setup" value="off">
                                                    <input class="form-check-input" type="checkbox" id="free_setup"
                                                        name="free_setup" value="on">
                                                    <span class="form-check-label">{{ __('Free Setup') }}</span>
                                                </label>
                                            </div>

                                            {{-- Free Support --}}
                                            <div class="col-md-3 col-lg-3 mt-3">
                                                <label class="form-check form-switch mt-2">
                                                    <input type="hidden" name="free_support" value="off">
                                                    <input class="form-check-input" type="checkbox" id="free_support"
                                                        name="free_support" value="on">
                                                    <span class="form-check-label">{{ __('Free Support') }}</span>
                                                </label>
                                            </div>

                                            {{-- Paddle Plan Price ID --}}
                                            @if (is_dir(base_path('plugins/PaddleRecurring')))
                                                <div class="col-12 mt-4">
                                                    <h2 class="page-title mb-2 fs-3">{{ __('Paddle Product Details') }}
                                                    </h2>
                                                </div>
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Price ID') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="paddle_plan_price_id"
                                                            placeholder="{{ __('Enter price ID') }}">
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Dodo Payments Plan Price ID --}}
                                            @if (is_dir(base_path('plugins/DodoPaymentsRecurring')))
                                                <div class="col-12 mt-4">
                                                    <h2 class="page-title mb-2 fs-3">
                                                        {{ __('Dodo Payments Product Details') }}</h2>
                                                </div>
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Price ID') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="dodo_plan_price_id"
                                                            placeholder="{{ __('Enter price ID') }}">
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Mobile App Plan Details --}}
                                            @if (is_dir(base_path('plugins/MobileAppAPI')))
                                                <h2 class="page-title my-3">
                                                    {{ __('In-App Purchase Details (Mobile App)') }}
                                                </h2>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Purchase Type') }}</label>
                                                        <select onchange="toggleProductID(this.value)"
                                                            class="form-select in_app_purchase_type"
                                                            name="in_app_purchase_type">
                                                            <option value="one_time">{{ __('One Time') }}</option>
                                                            <option value="recurring">{{ __('Recurring') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div id="in_app_product_id" class="col-md-3 col-sm-6 d-none">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Product ID') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="in_app_product_id"
                                                            placeholder="{{ __('Product ID') }}">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Plan ID') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="in_app_purchase_id" placeholder="{{ __('Plan ID') }}">
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Paypal Payment Plan ID --}}
                                            @if (is_dir(base_path('plugins/PaypalRecurring')))
                                                <div class="col-12 mt-4">
                                                    <h2 class="page-title mb-2 fs-3">{{ __('Paypal Payment Plan ID') }}
                                                    </h2>
                                                </div>

                                                {{-- Plan ID --}}
                                                <div class="col-md-4 col-lg-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Plan ID') }}</label>
                                                        <input type="text" class="form-control" name="paypal_plan_id"
                                                            placeholder="{{ __('Enter plan ID') }}">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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

        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Synchronize Plan Price and Term with Validity
        function handlePricingLogic() {
            "use strict";
            const priceInput = $("input[name='plan_price']").val();
            const price = parseFloat(priceInput);
            const planTerm = $("#plan_term").val();
            const validity = $("#validity");

            // If price is explicitly 0, set validity to 9999 (Forever) and lock it
            if (!isNaN(price) && price === 0) {
                validity.val(9999).prop("readonly", true);
            } else {
                // Otherwise respect the selected term
                if (planTerm === "monthly") {
                    validity.val(30).prop("readonly", true);
                } else if (planTerm === "yearly") {
                    validity.val(365).prop("readonly", true);
                } else if (planTerm === "custom") {
                    if (validity.val() == 9999) {
                        validity.val("");
                    }
                    validity.prop("readonly", false);
                }
            }
        }

        // Initialize on DOM load
        $(document).ready(function() {
            handlePricingLogic();
        });

        document.addEventListener('DOMContentLoaded', () => {
            const popoverElement = document.getElementById('popover-element');
            if (popoverElement) {
                const popover = new bootstrap.Popover(popoverElement, {
                    trigger: 'manual'
                });
                popoverElement.addEventListener('click', (e) => {
                    e.stopPropagation();
                    popover.toggle();
                });
                document.addEventListener('click', (e) => {
                    if (!popoverElement.contains(e.target)) {
                        popover.hide();
                    }
                });
            }

            const popoverElementTrial = document.getElementById('popover-element-trial');
            if (popoverElementTrial) {
                const popoverTrial = new bootstrap.Popover(popoverElementTrial, {
                    trigger: 'manual'
                });
                popoverElementTrial.addEventListener('click', (e) => {
                    e.stopPropagation();
                    popoverTrial.toggle();
                });
                document.addEventListener('click', (e) => {
                    if (!popoverElementTrial.contains(e.target)) {
                        popoverTrial.hide();
                    }
                });
            }
        });
    </script>
    <script>
        function checkPlanType(type) {
            "use strict";
            var planType = type.value;
            switch (planType) {
                case 'VCARD':
                    $("input[name='no_of_stores']").removeAttr('required');
                    $("input[name='no_of_categories']").removeAttr('required');
                    $("input[name='no_of_store_products']").removeAttr('required');

                    $("#store-features").attr("class", "row d-none");

                    $("input[name='no_of_vcards']").attr('required', 'required');
                    $("input[name='no_of_services']").attr('required', 'required');
                    $("input[name='no_of_vcard_products']").attr('required', 'required');
                    $("input[name='no_of_links']").attr('required', 'required');
                    $("input[name='no_of_payments']").attr('required', 'required');
                    $("input[name='no_of_galleries']").attr('required', 'required');
                    $("input[name='business_hours']").attr('required', 'required');
                    $("input[name='no_testimonials']").attr('required');
                    $("input[name='contact_form']").attr('required', 'required');
                    $("input[name='no_of_enquires']").attr('required', 'required');
                    $("input[name='google_wallet']").attr('required', 'required');
                    $("input[name='no_of_google_wallets']").attr('required', 'required');

                    $("#vcard-features").attr("class", "row");
                    break;
                case 'STORE':
                    $("input[name='no_of_vcards']").removeAttr('required');
                    $("input[name='no_of_services']").removeAttr('required');
                    $("input[name='no_of_vcard_products']").removeAttr('required');
                    $("input[name='no_of_links']").removeAttr('required');
                    $("input[name='no_of_payments']").removeAttr('required');
                    $("input[name='no_of_galleries']").removeAttr('required');
                    $("input[name='business_hours']").removeAttr('required');
                    $("input[name='contact_form']").removeAttr('required');
                    $("input[name='no_testimonials']").removeAttr('required');
                    $("input[name='no_of_enquires']").removeAttr('required');
                    $("input[name='google_wallet']").removeAttr('required');
                    $("input[name='no_of_google_wallets']").removeAttr('required');

                    $("#vcard-features").attr("class", "row d-none");

                    $("input[name='no_of_stores']").attr('required', 'required');
                    $("input[name='no_of_categories']").attr('required', 'required');
                    $("input[name='no_of_store_products']").attr('required', 'required');

                    $("#store-features").attr("class", "row");
                    break;
                default:
                    $("input[name='no_of_vcards']").attr('required', 'required');
                    $("input[name='no_of_services']").attr('required', 'required');
                    $("input[name='no_of_vcard_products']").attr('required', 'required');
                    $("input[name='no_of_links']").attr('required', 'required');
                    $("input[name='no_of_payments']").attr('required', 'required');
                    $("input[name='no_of_galleries']").attr('required', 'required');
                    $("input[name='business_hours']").attr('required', 'required');
                    $("input[name='contact_form']").attr('required', 'required');
                    $("input[name='no_testimonials']").attr('required');
                    $("input[name='no_of_enquires']").attr('required', 'required');
                    $("input[name='google_wallet']").attr('required', 'required');
                    $("input[name='no_of_google_wallets']").attr('required', 'required');

                    $("input[name='no_of_stores']").attr('required', 'required');
                    $("input[name='no_of_categories']").attr('required', 'required');
                    $("input[name='no_of_store_products']").attr('required', 'required');

                    $("#vcard-features").attr("class", "row");
                    $("#store-features").attr("class", "row");
            }
        }
    </script>

    <script>
        var elementSelectors = ['.plan_type', '.plan_term', '.in_app_purchase_type'];

        function initializeTomSelect(el) {
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
        }

        elementSelectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            elements.forEach(function(el) {
                initializeTomSelect(el);
            });
        });

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        elementSelectors.forEach(function(selector) {
                            if (node.matches(selector)) {
                                initializeTomSelect(node);
                            }
                            var childElements = node.querySelectorAll(selector);
                            childElements.forEach(function(childEl) {
                                initializeTomSelect(childEl);
                            });
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        function toggleProductID(value) {
            "use strict";
            if (value == "recurring") {
                $("#in_app_product_id").removeClass("d-none");
            } else {
                $("#in_app_product_id").addClass("d-none");
            }
        }
    </script>
@endsection
