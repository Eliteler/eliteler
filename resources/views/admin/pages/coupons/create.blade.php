@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }

        .discount {
            border-radius: 8px 0 0 8px !important;
        }
    </style>
    <script>
        // Auto generate coupon code
        document.addEventListener('DOMContentLoaded', function() {
            autoGenerateCouponCode();
        });
    </script>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.coupons') }}" class="text-secondary text-decoration-none">
                                    {{ __('Coupons') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create Coupon') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create Coupon') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Set up coupon codes to offer fixed or percentage discounts to your customers.') }}
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
                        <form action="{{ route('admin.store.coupon') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Coupon Details') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Used for --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Coupon Usage') }}</div>
                                                    <select class="form-control used_for" id="used_for" name="used_for"
                                                        required>
                                                        <option value="plan"
                                                            {{ old('used_for') == 'plan' ? 'selected' : '' }}>
                                                            {{ __('Plan') }}</option>
                                                        <option value="nfc"
                                                            {{ old('used_for') == 'nfc' ? 'selected' : '' }}>
                                                            {{ __('NFC Card') }}</option>
                                                        <option value="ai_credits"
                                                            {{ old('used_for') == 'ai_credits' ? 'selected' : '' }}>
                                                            {{ __('AI Credits') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Dynamic Plan / Card / AI Dropdown --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required" id="target_label">
                                                        {{ __('Select Plan') }}</div>
                                                    <select class="form-select" name="plan_id[]" id="target_item" multiple
                                                        required>
                                                        <!-- Options dynamically loaded -->
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Coupon Code --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Coupon Code') }}</label>

                                                    <div class="input-group">
                                                        <input type="text" class="form-control text-uppercase"
                                                            name="code" id="coupon_code" value="{{ old('code') }}"
                                                            autocomplete="code" required>
                                                        <button type="button" class="btn btn-primary"
                                                            onclick="autoGenerateCouponCode()">
                                                            {{ __('Generate') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Coupon description --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Description') }}</div>
                                                    <input type="text" class="form-control" name="description"
                                                        value="{{ old('description') }}"
                                                        placeholder="{{ __('Description') }}" autocomplete="description"
                                                        autofocus>
                                                </div>
                                            </div>

                                            {{-- Coupon type --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Type') }}</div>
                                                    <select class="form-select type" name="type"
                                                        onchange="couponType(this)" required>
                                                        <option value="fixed">{{ __('Fixed') }}</option>
                                                        <option value="percentage">{{ __('Percentage') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Coupon discount --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Discount') }}</div>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control discount" name="discount"
                                                            id="discount" value="{{ old('discount') }}" min="0"
                                                            step="0.001" placeholder="{{ __('Discount') }}"
                                                            autocomplete="discount" autofocus required>
                                                        <span class="input-group-text fw-bold"
                                                            id="discount-addon">{{ $config[1]->config_value }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Coupon validity --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Validity') }}</div>
                                                    <input type="date" class="form-control" name="validity"
                                                        value="{{ old('validity') ?? date('Y-m-d') }}"
                                                        placeholder="{{ __('Validity') }}" autocomplete="validity"
                                                        autofocus required>
                                                </div>
                                            </div>

                                            {{-- Coupon user limit --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('User Limit') }}</div>
                                                    <input type="number" class="form-control" name="user_limit"
                                                        value="{{ old('user_limit') ?? 1 }}" min="1"
                                                        step="1" placeholder="{{ __('User Limit') }}"
                                                        autocomplete="user_limit" autofocus required>
                                                </div>
                                            </div>

                                            {{-- Coupon total limit --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Total Limit') }}</div>
                                                    <input type="number" class="form-control" name="total_limit"
                                                        value="{{ old('total_limit') ?? 1 }}" min="1"
                                                        step="1" placeholder="{{ __('Total Limit') }}"
                                                        autocomplete="total_limit" autofocus required>
                                                </div>
                                            </div>

                                            {{-- Keywords/SEO context separator --}}
                                            <div class="col-12 mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
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
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script>
        // Convert slug
        $('#category_name').on("change keyup paste click", function() {
            "use strict";
            var Text = $(this).val();
            Text = Text.toLowerCase();
            Text = Text.replace(/[^a-zA-Z0-9]+/g, '-');
            $('#category_slug').val(Text);
        });

        // Initialize Tooltips to ensure help icons trigger properly on hover
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Data arrays passed from controller
        const plansList = @json($plans ?? []);
        const nfcCardsList = @json($nfcCards ?? []);
        const aiCreditsList = @json($aiCredits ?? []);

        // Array of element selectors for other static selects
        var elementSelectors = ['.type'];

        // Function to initialize TomSelect on an element
        function initializeTomSelect(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });
        }

        // Initialize static selects
        elementSelectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            elements.forEach(function(el) {
                initializeTomSelect(el);
            });
        });

        // Tracking previous selections and recursion lock
        var previousTargetValues = [];
        var isTargetUpdating = false;

        // Initialize Target Item TomSelect
        var targetTomSelect = new TomSelect('#target_item', {
            plugins: ['remove_button'],
            copyClassesToDropdown: false,
            dropdownClass: 'dropdown-menu ts-dropdown',
            optionClass: 'dropdown-item',
            controlInput: '<input>',
            maxOptions: null,
            render: {
                item: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
                option: function(data, escape) {
                    return '<div>' + escape(data.text) + '</div>';
                },
            },
            onChange: function(values) {
                // Prevent infinite loop during programmatical value updates
                if (isTargetUpdating) return;

                if (!values || values.length === 0) {
                    previousTargetValues = [];
                    return;
                }

                // Mutually exclusive logic between 'all' and specific items
                if (values.includes('all')) {
                    // Case 1: User just clicked 'all' while other items were selected
                    if (!previousTargetValues.includes('all')) {
                        isTargetUpdating = true;
                        this.setValue(['all']);
                        previousTargetValues = ['all'];
                        isTargetUpdating = false;
                        return;
                    }
                    // Case 2: 'all' was already selected, but user clicked a specific item
                    else if (values.length > 1) {
                        var specificItems = values.filter(function(v) {
                            return v !== 'all';
                        });
                        isTargetUpdating = true;
                        this.setValue(specificItems);
                        previousTargetValues = specificItems;
                        isTargetUpdating = false;
                        return;
                    }
                }

                previousTargetValues = values.slice();
            }
        });

        // Function to populate TomSelect options dynamically
        function populateTargetDropdown(usageType) {
            var labelBox = document.getElementById('target_label');
            var optionsArray = [];

            // Clear existing TomSelect options
            targetTomSelect.clear();
            targetTomSelect.clearOptions();

            if (usageType === 'plan') {
                labelBox.textContent = "{{ __('Select Plan') }}";
                optionsArray.push({
                    value: 'all',
                    text: "{{ __('All Plans') }}"
                });
                plansList.forEach(function(item) {
                    optionsArray.push({
                        value: item.plan_id,
                        text: item.plan_name + ' (' + item.formatted_price + ')'
                    });
                });
            } else if (usageType === 'nfc') {
                labelBox.textContent = "{{ __('Select NFC Card') }}";
                optionsArray.push({
                    value: 'all',
                    text: "{{ __('All NFC Cards') }}"
                });
                nfcCardsList.forEach(function(item) {
                    optionsArray.push({
                        value: item.nfc_card_id,
                        text: item.nfc_card_name + ' (' + item.formatted_price + ')'
                    });
                });
            } else if (usageType === 'ai_credits') {
                labelBox.textContent = "{{ __('Select AI Credits Plan') }}";
                optionsArray.push({
                    value: 'all',
                    text: "{{ __('All AI Credit Plans') }}"
                });
                aiCreditsList.forEach(function(item) {
                    optionsArray.push({
                        value: item.ai_credits_plan_id,
                        text: item.plan_name + ' (' + item.formatted_price + ')'
                    });
                });
            }

            // Add new options to TomSelect and set default to 'all'
            targetTomSelect.addOptions(optionsArray);
            targetTomSelect.setValue('all');
            targetTomSelect.refreshOptions(false);
        }

        // Initialize TomSelect on used_for with onChange listener
        var usedForTom = new TomSelect('#used_for', {
            copyClassesToDropdown: false,
            dropdownClass: 'dropdown-menu ts-dropdown',
            optionClass: 'dropdown-item',
            controlInput: '<input>',
            onChange: function(val) {
                // Trigger option reload when usage changes
                populateTargetDropdown(val);
            }
        });

        // Initial load on page ready
        document.addEventListener('DOMContentLoaded', function() {
            var initialUsage = document.getElementById('used_for').value || 'plan';
            populateTargetDropdown(initialUsage);
        });
    </script>
    <script>
        // Get coupon type
        function couponType(type) {
            "use strict";
            var discount = type.value;
            switch (discount) {
                case 'fixed':
                    // Remove discount inputs "required" attributes
                    $("#discount-addon").html(`{{ $config[1]->config_value }}`);
                    // Remove max value for discount
                    $("#discount").attr("max", "");
                    break;
                case 'percentage':
                    // Add discount inputs "required" attributes
                    $("#discount-addon").html("%");
                    // Set max value for discount
                    $("#discount").attr("max", 100);
                    break;
            }
        }

        // Auto generate coupon code
        function autoGenerateCouponCode() {
            "use strict";

            // Get coupon code
            var couponCode = document.getElementById("coupon_code");

            // Generate coupon code
            couponCode.value = generateCouponCode();
        }

        // Generate coupon code
        function generateCouponCode() {
            "use strict";

            // Generate coupon code
            var code = Math.random().toString(36).slice(2, 13).toUpperCase();

            // Check if the coupon code already exists
            var couponCodeExists = false;
            var couponCode = document.getElementById("coupon_code");
            couponCode.value = code;

            // Get coupon code from the database
            $.ajax({
                url: "{{ route('admin.coupons.code.exists') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    code: code
                },
                success: function(response) {
                    if (response.success) {
                        couponCodeExists = true;
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });

            // Check if the coupon code already exists
            if (couponCodeExists) {
                // Generate new coupon code
                return generateCouponCode();
            } else {
                // Return the generated coupon code
                return code;
            }
        }
    </script>
@endsection
