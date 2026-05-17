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

        .code {
            background: #333;
            color: #fff;
            font-family: monospace;
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

// Ensure `$allowedPermissions` is an array (to handle cases where permissions are null or malformed)
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
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title mb-2">
                            {{ __('Settings') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
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

                {{-- Success --}}
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

                {{-- Settings --}}
                <div class="card">
                    <form action="{{ route('admin.change.general.settings') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        {{-- Card Body --}}
                        <div class="card-body">
                            <div class="row">
                                {{-- General --}}
                                <h2 class="border-bottom pb-2 mb-3">{{ __('General Configuration Settings') }}</h2>
                                {{-- Website? --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required" for="show_website">{{ __('Website?') }}</label>
                                        <select name="show_website" id="show_website" class="form-select" required>
                                            <option value="yes"
                                                {{ $config[38]->config_value == 'yes' ? 'selected' : '' }}>
                                                {{ __('Enabled') }}</option>
                                            <option value="no"
                                                {{ $config[38]->config_value == 'no' ? 'selected' : '' }}>
                                                {{ __('Disabled') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('Turn on or off your website.') }}</small>
                                    </div>
                                </div>

                                {{-- Customer registration --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required"
                                            for="registration_page">{{ __('Customer registration?') }}</label>
                                        <select name="registration_page" id="registration_page" class="form-select"
                                            required>
                                            <option value="0"
                                                {{ $config[63]->config_value == '0' ? 'selected' : '' }}>
                                                {{ __('Enabled') }}</option>
                                            <option value="1"
                                                {{ $config[63]->config_value == '1' ? 'selected' : '' }}>
                                                {{ __('Disabled') }}</option>
                                        </select>
                                        <small class="text-muted">{{ __('Turn on or off registration page.') }}</small>
                                    </div>
                                </div>

                                {{-- Timezone --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required" for="timezone">{{ __('Timezone') }}</label>
                                        <select name="timezone" id="timezone" class="form-select" required>
                                            @foreach (timezone_identifiers_list() as $timezone)
                                                <option value="{{ $timezone }}"
                                                    {{ $config[2]->config_value == $timezone ? 'selected' : '' }}>
                                                    {{ $timezone }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Currency --}}
                                <div class="col-12 col-lg-3">
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
                                <div class="col-12 col-lg-3">
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
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required"
                                            for="currency_decimals_place">{{ __('Decimals Places') }}</label>
                                        <input type="number" class="form-control reduce-control"
                                            name="currency_decimals_place" id="currency_decimals_place"
                                            value="{{ $config[56]->config_value }}"
                                            placeholder="{{ __('Decimals Places') }}" min="0" step="1"
                                            max="3" required
                                            oninput="this.value = this.value.replace(/^0+(?=\d)/, '').replace(/[^0-9]/g, ''); if(this.value > 3) this.value = 3;">
                                        <small
                                            class="text-muted">{{ __('If you don\'t need decimal vale, set 0') }}</small>
                                    </div>
                                </div>

                                {{-- Date Time Format --}}
                                <div class="col-12 col-lg-3">
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
                                <div class="col-12 col-lg-3">
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
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="image_limit">{{ __('Size in Kilobytes') }}
                                        </label>
                                        <input type="number" class="form-control reduce-control" name="image_limit"
                                            value="{{ $settings->image_limit['SIZE_LIMIT'] }}"
                                            placeholder="{{ __('Size') }}" min="1024">
                                        <small
                                            class="text-muted">{{ __('For example, if you want to limit the size to 5MB, set 5120') }}</small>
                                    </div>
                                </div>

                                {{-- Activate free plan during registeration --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required"
                                            for="registration_page">{{ __('Activate free plan during registeration?') }}</label>
                                        <select name="activate_plan_during_registeration"
                                            id="activate_plan_during_registeration" class="form-select" required>
                                            <option value="1"
                                                {{ $config[94]->config_value == '1' ? 'selected' : '' }}>
                                                {{ __('Enabled') }}</option>
                                            <option value="0"
                                                {{ $config[94]->config_value == '0' ? 'selected' : '' }}>
                                                {{ __('Disabled') }}</option>
                                        </select>
                                        <small
                                            class="text-muted">{{ __('A free plan must exist if this option is enabled.') }}</small>
                                    </div>
                                </div>

                                {{-- Languages --}}
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label required" for="language">{{ __('Languages') }}</label>
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

                                {{-- Website Settings --}}
                                <h2 class="border-bottom pb-2 my-3">{{ __('Website Settings') }}</h2>
                                {{-- Logo ({{ __('Dark') }}) --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <div class="form-label">{{ __('Logo') }} ({{ __('Dark') }})</div>
                                        <input type="file" class="form-control" name="site_logo"
                                            placeholder="{{ __('Logo') }}" accept=".png,.jpg,.jpeg,.gif,.svg" />
                                        <small class="text-muted">{{ __('Recommended size : 200 x 90') }}</small>
                                    </div>
                                </div>

                                {{-- Logo ({{ __('Light') }}) --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <div class="form-label">{{ __('Logo') }} ({{ __('Light') }})</div>
                                        <input type="file" class="form-control" name="site_logo_light"
                                            placeholder="{{ __('Logo') }}" accept=".png,.jpg,.jpeg,.gif,.svg" />
                                        <small class="text-muted">{{ __('Recommended size : 200 x 90') }}</small>
                                    </div>
                                </div>

                                {{-- Favicon --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <div class="form-label">{{ __('Favicon') }}</div>
                                        <input type="file" class="form-control" name="favi_icon"
                                            placeholder="{{ __('Favicon') }}" accept=".png,.jpg,.jpeg,.gif,.svg" />
                                        <small class="text-muted">
                                            {{ __('Recommended size : 200 x 200') }}</small>
                                    </div>
                                </div>

                                {{-- App Name --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('App Name') }}</label>
                                        <input type="text" class="form-control" name="app_name"
                                            value="{{ config('app.name') }}" maxlength="50"
                                            placeholder="{{ __('App Name') }}">
                                    </div>
                                </div>

                                {{-- Site Name --}}
                                <div class="col-12 col-lg-3">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Site Name') }}</label>
                                        <input type="text" class="form-control" name="site_name"
                                            value="{{ $settings->site_name }}" placeholder="{{ __('Site Name') }}"
                                            required>
                                    </div>
                                </div>

                                {{-- Share Content Settings --}}
                                <h2 class="border-bottom pb-2 my-4">{{ __('Share Content Settings') }}</h2>

                                <!-- Share Content Input -->
                                <div class="col-lg-6">
                                    <div class="card shadow-none border mb-3">
                                        <div class="card-header bg-light rounded-top-4">
                                            <h5 class="card-title mb-0 text-dark">{{ __('Share Content') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <label for="share_content" class="form-label fw-semibold">
                                                {{ __('Content to Share') }} <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control" name="share_content" id="share_content" rows="5"
                                                placeholder="{{ __('Enter content to share') }}" required>{{ $config[30]->config_value }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Short Codes Section -->
                                <div class="col-lg-6">
                                    <div class="card shadow-none border mb-3">
                                        <div class="card-header bg-light rounded-top-4">
                                            <h5 class="card-title mb-0 text-dark">{{ __('Available Short Codes') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-2">
                                                {{ __('Use the following short codes in your content:') }}</p>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <strong>{ business_name }</strong> - {{ __('Business Name') }}
                                                </li>
                                                <li class="list-group-item">
                                                    <strong>{ business_url }</strong> -
                                                    {{ __('Business URL or Address') }}
                                                </li>
                                                <li class="list-group-item">
                                                    <strong>{ appName }</strong> - {{ __('App Name') }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Card Footer --}}
                        <div class="card-footer d-flex justify-content-end">
                            {{-- Update button --}}
                            <button type="submit" class="btn btn-primary btn-md ms-auto">
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

    {{-- Custom JS --}}
@section('scripts')
    <script>
        function validatePort(input) {
            "use strict";

            const maxLength = 5; // Set your desired max length
            if (input.value.length > maxLength) {
                input.value = input.value.slice(0, maxLength);
            }
        }
    </script>
    <script>
        tinymce.init({
            selector: 'textarea#bank_transfer',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | pagebreak | link',
            toolbar_sticky: true,
            height: 200,
            menubar: false,
            statusbar: false,
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
        // Array of element IDs
        var elementSelectors = ['show_website', 'registration_page', 'timezone', 'languages',
            'date_time_format', 'default_language', 'currency', 'currency_format', 'term',
            'activate_plan_during_registeration'
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
@endsection
