@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Scoped CSS Overrides --}}
@section('css')
    <style>
        .ts-control {
            line-height: 1.7 !important;
        }

        .reduce-control {
            line-height: 1.7 !important;
        }

        .list-group-item {
            padding: 0.2rem 0rem !important;
        }

        .code {
            background: #333;
            color: #fff;
            font-family: monospace;
        }

        /* Form help */
        .form-help {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.08);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            user-select: none;
        }

        /* Share Content */
        #share_content {
            resize: none;
            min-height: 50px;
            height: 100%;
            font-size: 12px;
        }

        .row.align-items-stretch>[class*="col-"] {
            display: flex;
        }

        .row.align-items-stretch>[class*="col-"]>.card {
            width: 100%;
        }
    </style>
@endsection

@php
    use Illuminate\Support\Facades\Auth;
    use App\Transaction;
    use Carbon\Carbon;

    // Fetch current user's details
$user = Auth::user();
$allowedPermissions = json_decode($user->permissions, true);

// Ensure `$allowedPermissions` is an array
if (!is_array($allowedPermissions)) {
    $allowedPermissions = [];
}

// Add or update missing permissions
$defaultPermissions = [
    'currencies' => 1,
    'coupons' => 1,
    'custom_domain' => 1,
    'marketing' => 1,
    'demo_mode' => 1,
    'backup' => 1,
    'nfc_card_design' => 1,
    'nfc_card_orders' => 1,
    'nfc_card_order_transactions' => 1,
    'nfc_card_key_generations' => 1,
    'email_templates' => 1,
    'plugins' => 1,
    'referral_system' => 1,
    'in_app_purchases' => 1,
    'vcard_store' => 1,
    'business_card_intros' => 1,
    'ai_credits' => 1,
    ];

    // Merge default permissions with the current ones (current values take precedence)
    $allowedPermissions = array_merge($defaultPermissions, $allowedPermissions);

    // Update user details if permissions were changed
    if ($allowedPermissions !== json_decode($user->permissions, true)) {
        $user->permissions = json_encode($allowedPermissions);
        $user->updated_at = Carbon::now(); // Update timestamp explicitly
        $user->save(); // Save changes to the database
    }

    // Fetch updated permissions
    $allowedPermissions = json_decode($user->permissions, true);
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumb Navigation -->
                    <nav aria-label="breadcrumb" class="mb-1 ms-0">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('General Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('General Settings') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure system preferences, currencies, localized timezones, and system branding parameters.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Form Card wrapper -->
                <div class="card border-0 shadow-sm">
                    <form action="{{ route('admin.change.general.settings') }}" method="post" enctype="multipart/form-data"
                        class="card border-0">
                        @csrf
                        {{-- Card Body --}}
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Website Status --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label mb-2">{{ __('Website Status') }}
                                            <span class="form-help bg-primary text-white ms-1" data-bs-toggle="popover"
                                                data-bs-placement="top" data-bs-html="true"
                                                data-bs-content="<p>{{ __('Turn this on to show your website\'s home page to visitors. Turn it off to send them directly to the login page.') }}</p>">?</span>
                                        </label>
                                        <label class="form-check form-switch mt-2">
                                            <input type="hidden" name="show_website" value="no">
                                            <input class="form-check-input" type="checkbox" name="show_website"
                                                id="show_website" value="yes"
                                                {{ $config[38]->config_value == 'yes' ? 'checked' : '' }}>
                                        </label>
                                        <small
                                            class="text-muted d-block mt-1">{{ __('Toggle public frontend visibility.') }}</small>
                                    </div>
                                </div>

                                {{-- User Registration --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label mb-2">{{ __('User Registration') }}</label>
                                        <label class="form-check form-switch mt-2">
                                            <input type="hidden" name="registration_page" value="1">
                                            <input class="form-check-input" type="checkbox" name="registration_page"
                                                id="registration_page" value="0"
                                                {{ $config[63]->config_value == '0' ? 'checked' : '' }}>
                                        </label>
                                        <small
                                            class="text-muted d-block mt-1">{{ __('Allow guest visitors to register accounts.') }}</small>
                                    </div>
                                </div>

                                {{-- Timezone --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required" for="timezone">{{ __('Timezone') }}</label>
                                        <select name="timezone" id="timezone" class="form-select" required>
                                            @foreach (timezone_identifiers_list() as $timezone)
                                                <option value="{{ $timezone }}"
                                                    {{ $config[2]->config_value == $timezone ? 'selected' : '' }}>
                                                    {{ $timezone }}</option>
                                            @endforeach
                                        </select>
                                        <small
                                            class="text-muted d-block mt-1">{{ __('Choose the default localized timezone.') }}</small>
                                    </div>
                                </div>

                                {{-- Currency --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required" for="currency">{{ __('Currency') }}</label>
                                        <select name="currency" id="currency" class="form-select" required>
                                            @foreach ($currencies as $currency)
                                                <option value="{{ $currency->iso_code }}"
                                                    {{ $config[1]->config_value == $currency->iso_code ? 'selected' : '' }}>
                                                    {{ $currency->name }} ({{ $currency->symbol }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Currency format type --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required"
                                            for="currency_format">{{ __('Currency Format') }}</label>
                                        <select name="currency_format" id="currency_format" class="form-select" required>
                                            <option value="1,234,567.89"
                                                {{ $config[55]->config_value == '1,234,567.89' ? 'selected' : '' }}>
                                                {{ __('1,234,567.89') }}</option>
                                            <option value="12,34,567.89"
                                                {{ $config[55]->config_value == '12,34,567.89' ? 'selected' : '' }}>
                                                {{ __('12,34,567.89') }}</option>
                                            <option value="1.234.567,89"
                                                {{ $config[55]->config_value == '1.234.567,89' ? 'selected' : '' }}>
                                                {{ __('1.234.567,89') }}</option>
                                            <option value="1 234 567,89"
                                                {{ $config[55]->config_value == '1 234 567,89' ? 'selected' : '' }}>
                                                {{ __('1 234 567,89') }}</option>
                                            <option value="1'234'567.89"
                                                {{ $config[55]->config_value == "1'234'567.89" ? 'selected' : '' }}>
                                                {{ __("1'234'567.89") }}</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Currency Decimals Places --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required"
                                            for="currency_decimals_place">{{ __('Decimal Places') }}
                                            <span class="form-help bg-primary text-white ms-1" data-bs-toggle="popover"
                                                data-bs-placement="top" data-bs-html="true"
                                                data-bs-content="<p>{{ __('Choose the number of decimal places to display. Set to 0 to hide decimals.') }}</p>">?</span>
                                        </label>
                                        <input type="number" class="form-control reduce-control"
                                            name="currency_decimals_place" id="currency_decimals_place"
                                            value="{{ $config[56]->config_value }}"
                                            placeholder="{{ __('Decimal Places') }}" min="0" step="1"
                                            max="3" required
                                            oninput="this.value = this.value.replace(/^0+(?=\d)/, '').replace(/[^0-9]/g, ''); if(this.value > 3) this.value = 3;">
                                        <small
                                            class="text-muted">{{ __('Set this to 0 if you do not want decimal values.') }}</small>
                                    </div>
                                </div>

                                {{-- Date Time Format --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3"> 
                                        <label class="form-label required"
                                            for="date_time_format">{{ __('Date Time Format') }}</label>
                                        <select name="date_time_format" id="date_time_format" class="form-select"
                                            required>
                                            @php
                                                $availableDateTimeFormats = getDateTimeFormats();
                                            @endphp
                                            @foreach ($availableDateTimeFormats as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ $config[75]->config_value == $key ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Default Language --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required"
                                            for="default_language">{{ __('Default Language') }}</label>
                                        <select name="default_language" id="default_language" class="form-select"
                                            required>
                                            @php
                                                $availableLanguages = config('app.languages');
                                            @endphp

                                            @foreach ($availableLanguages as $code => $name)
                                                <option value="{{ $code }}"
                                                    {{ $config[98]->config_value == $code ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Image Upload Limit --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="image_limit">{{ __('Maximum Upload Size') }}
                                        </label>
                                        <input type="number" class="form-control reduce-control" name="image_limit"
                                            value="{{ $settings->image_limit['SIZE_LIMIT'] }}"
                                            placeholder="{{ __('Size') }}" min="1024">
                                        <small
                                            class="text-muted">{{ __('Set the largest file size limit users can upload.') }}</small>
                                    </div>
                                </div>

                                {{-- Free Plan on Sign Up --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label mb-2">{{ __('Free Plan on Sign Up') }}</label>
                                        <label class="form-check form-switch mt-2">
                                            <input type="hidden" name="activate_plan_during_registeration"
                                                value="0">
                                            <input class="form-check-input" type="checkbox"
                                                name="activate_plan_during_registeration"
                                                id="activate_plan_during_registeration" value="1"
                                                {{ $config[94]->config_value == '1' ? 'checked' : '' }}>
                                        </label>
                                        <small
                                            class="text-muted">{{ __('Assign the free plan automatically to new users after registration. An active free plan must exist.') }}</small>
                                    </div>
                                </div>

                                {{-- Languages select field --}}
                                <div class="col-12 col-md-12 col-lg-12">
                                    <div class="mb-3">
                                        <label class="form-label required" for="languages">{{ __('Languages') }}</label>
                                        <select name="languages[]" id="languages" class="form-select" required multiple>
                                            @php
                                                $availableLanguages = config('app.availableLanguages');
                                            @endphp

                                            @foreach ($availableLanguages as $code => $name)
                                                <option value="{{ $code }}"
                                                    @if (in_array($code, $selectedLanguages ?? [])) selected @endif>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Website Settings Section Heading --}}
                                <div class="col-12 border-bottom pb-2">
                                    <h2>{{ __('Website Branding') }}</h2>
                                </div>

                                {{-- Dark Logo --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="site_logo">{{ __('Dark Logo') }}</label>
                                        <input type="file" class="form-control" name="site_logo" id="site_logo"
                                            accept=".png,.jpg,.jpeg,.gif,.svg" />
                                        <small
                                            class="text-muted">{{ __('Used when dark mode is enabled. Recommended dimensions: 200 x 90 px.') }}</small>
                                    </div>
                                </div>

                                {{-- Light Logo --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="site_logo_light">{{ __('Light Logo') }}</label>
                                        <input type="file" class="form-control" name="site_logo_light"
                                            id="site_logo_light" accept=".png,.jpg,.jpeg,.gif,.svg" />
                                        <small
                                            class="text-muted">{{ __('Used when light mode is enabled. Recommended dimensions: 200 x 90 px.') }}</small>
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="favi_icon">{{ __('Favicon') }}</label>
                                        <input type="file" class="form-control" name="favi_icon" id="favi_icon"
                                            accept=".png,.jpg,.jpeg,.gif,.svg" />
                                        <small
                                            class="text-muted">{{ __('Displayed in browser tabs and bookmarks. Recommended dimensions: 200 x 200 px.') }}</small>
                                    </div>
                                </div>

                                {{-- App Name --}}
                                <div class="col-12 col-md-3 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="app_name">{{ __('Website Name') }}</label>
                                        <input type="text" class="form-control" name="app_name" id="app_name"
                                            value="{{ config('app.name') }}" maxlength="50"
                                            placeholder="{{ __('Enter your website name') }}">
                                    </div>
                                </div>

                                {{-- Social Sharing Settings --}}
                                <div class="col-12 mt-4">
                                    <h3 class="title-header mb-1">{{ __('Social Sharing Settings') }}</h3>
                                    <p class="text-muted small mb-3">
                                        {{ __('Configure the default message template for sharing digital business cards or stores across social media platforms.') }}
                                    </p>

                                    <div class="row g-3 align-items-stretch">

                                        {{-- Share Workspace --}}
                                        <div class="col-12 col-lg-6 d-flex">
                                            <div
                                                class="card border border-light-subtle shadow-none bg-light-subtle w-100 h-100">
                                                <div class="card-body d-flex flex-column">
                                                    <label for="share_content"
                                                        class="form-label required fw-medium text-secondary small mb-2">
                                                        {{ __('Default Share Message Template') }}
                                                    </label>

                                                    <textarea class="form-control font-monospace flex-grow-1" id="share_content" name="share_content" rows="3"
                                                        cols="50" required>{{ $config[30]->config_value }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Available Placeholders --}}
                                        <div class="col-12 col-lg-6 d-flex">
                                            <div
                                                class="card border border-light-subtle shadow-none bg-light-subtle w-100 h-100">
                                                <div class="card-body">
                                                    <p class="text-muted small mb-3">
                                                        {{ __('Copy and paste these placeholders into your template. They will resolve dynamically.') }}
                                                    </p>

                                                    <div class="list-group list-group-flush">

                                                        <div
                                                            class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 bg-transparent">
                                                            <span
                                                                class="badge bg-primary-subtle text-primary fw-bold font-monospace">
                                                                { business_name }
                                                            </span>
                                                            <span class="text-secondary small">
                                                                {{ __('Inserts Business Name') }}
                                                            </span>
                                                        </div>

                                                        <div
                                                            class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 bg-transparent">
                                                            <span
                                                                class="badge bg-primary-subtle text-primary fw-bold font-monospace">
                                                                { business_url }
                                                            </span>
                                                            <span class="text-secondary small">
                                                                {{ __('Inserts Business URL') }}
                                                            </span>
                                                        </div>

                                                        <div
                                                            class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 bg-transparent">
                                                            <span
                                                                class="badge bg-primary-subtle text-primary fw-bold font-monospace">
                                                                { appName }
                                                            </span>
                                                            <span class="text-secondary small">
                                                                {{ __('Inserts System App Name') }}
                                                            </span>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Simplified Card Footer with a standard device-floppy save button (No Cancel Button) -->
                        <div class="card-footer text-end bg-transparent border-top-0 pt-0">
                            <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-device-floppy me-2" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS pushed cleanly inside scripts stack --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>

    <script>
        function validatePort(input) {
            "use strict";

            const maxLength = 5;
            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
        }
    </script>
    <script>
        // Dark Mode detection
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

            // Correct mobile settings (limited fields allowed)
            mobile: {
                plugins: 'paste',
                toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });
    </script>
    <script>
        // Array of element IDs (Excluding converted checkbox switches to prevent TomSelect errors)
        var elementSelectors = ['timezone', 'languages', 'date_time_format', 'default_language', 'currency',
            'currency_format', 'term'
        ];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(id) {
            var el = document.getElementById(id);
            if (!el) return;

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
            initializeTomSelectWithRequired(id);
        });
    </script>
@endsection
