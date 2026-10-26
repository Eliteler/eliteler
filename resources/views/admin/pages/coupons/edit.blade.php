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
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs -->
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
                                <a href="#">{{ __('Update Coupon') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __($couponDetails->coupon_code) }}</span>
                            {{ __('Details') }}
                        </h2>
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
                        <form action="{{ route('admin.update.coupon', $couponDetails->coupon_id) }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Coupon Details') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Coupon Usage --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Coupon Usage') }}</div>
                                                    <select class="form-control used_for" id="used_for" name="used_for"
                                                        required>
                                                        <option value="plan"
                                                            {{ $couponDetails->used_for == 'plan' ? 'selected' : '' }}>
                                                            {{ __('Plan') }}</option>
                                                        <option value="nfc"
                                                            {{ $couponDetails->used_for == 'nfc' ? 'selected' : '' }}>
                                                            {{ __('NFC Card') }}</option>
                                                        <option value="ai_credits"
                                                            {{ $couponDetails->used_for == 'ai_credits' ? 'selected' : '' }}>
                                                            {{ __('AI Credits') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Dynamic Multi-Select Target Item Dropdown --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required" id="target_label">
                                                        {{ __('Select Plans') }}</div>
                                                    <select class="form-select" name="plan_id[]" id="target_item" multiple
                                                        required>
                                                        <!-- Options dynamically loaded via TomSelect -->
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Coupon Code --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Coupon Code') }}</div>
                                                    <input type="text" class="form-control text-uppercase" name="code"
                                                        value="{{ old('code') ?? $couponDetails->coupon_code }}"
                                                        autocomplete="code" autofocus required>
                                                </div>
                                            </div>

                                            {{-- Coupon description --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Description') }}</div>
                                                    <input type="text" class="form-control" name="description"
                                                        value="{{ old('description') ?? $couponDetails->coupon_desc }}"
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
                                                        <option value="fixed"
                                                            {{ $couponDetails->coupon_type == 'fixed' ? 'selected' : '' }}>
                                                            {{ __('Fixed') }}</option>
                                                        <option value="percentage"
                                                            {{ $couponDetails->coupon_type == 'percentage' ? 'selected' : '' }}>
                                                            {{ __('Percentage') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Coupon discount --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Discount') }}</div>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control discount" name="discount"
                                                            id="discount"
                                                            value="{{ old('discount') ?? $couponDetails->coupon_amount }}"
                                                            min="0" step="0.001"
                                                            max="{{ $couponDetails->coupon_type == 'fixed' ? '' : '100' }}"
                                                            placeholder="{{ __('Discount') }}" autocomplete="discount"
                                                            autofocus required>
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
                                                        value="{{ old('validity') ?? \Carbon\Carbon::parse($couponDetails->coupon_expired_on)->format('Y-m-d') }}"
                                                        placeholder="{{ __('Validity') }}" autocomplete="validity"
                                                        autofocus required>
                                                </div>
                                            </div>

                                            {{-- Coupon user limit --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('User Limit') }}</div>
                                                    <input type="number" class="form-control" name="user_limit"
                                                        value="{{ old('user_limit') ?? $couponDetails->coupon_user_usage_limit }}"
                                                        min="1" step="1"
                                                        placeholder="{{ __('User Limit') }}" autocomplete="user_limit"
                                                        autofocus required>
                                                </div>
                                            </div>

                                            {{-- Coupon total limit --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Total Limit') }}</div>
                                                    <input type="number" class="form-control" name="total_limit"
                                                        value="{{ old('total_limit') ?? $couponDetails->coupon_total_usage_limit }}"
                                                        min="1" step="1"
                                                        placeholder="{{ __('Total Limit') }}" autocomplete="total_limit"
                                                        autofocus required>
                                                </div>
                                            </div>
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
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Data arrays passed from controller
        const plansList = @json($plans ?? []);
        const nfcCardsList = @json($nfcCards ?? []);
        const aiCreditsList = @json($aiCredits ?? []);

        // Array of saved item IDs from controller
        const savedPlanIds = @json($savedPlanIds ?? ['all']);

        // Static selector initialization
        var elementSelectors = ['.type'];

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
        function populateTargetDropdown(usageType, selectedValues = ['all']) {
            var labelBox = document.getElementById('target_label');
            var optionsArray = [];

            targetTomSelect.clear();
            targetTomSelect.clearOptions();

            if (usageType === 'plan') {
                labelBox.textContent = "{{ __('Select Plans') }}";
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
                labelBox.textContent = "{{ __('Select NFC Cards') }}";
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
                labelBox.textContent = "{{ __('Select AI Credits Plans') }}";
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

            targetTomSelect.addOptions(optionsArray);
            // Pre-select saved multiple values
            targetTomSelect.setValue(selectedValues);
            targetTomSelect.refreshOptions(false);
        }

        // On usage dropdown change
        var usedForTom = new TomSelect('#used_for', {
            copyClassesToDropdown: false,
            dropdownClass: 'dropdown-menu ts-dropdown',
            optionClass: 'dropdown-item',
            controlInput: '<input>',
            onChange: function(val) {
                // Default to 'all' on manual change
                populateTargetDropdown(val, ['all']);
            }
        });

        // Initialize on page load with pre-saved values
        document.addEventListener('DOMContentLoaded', function() {
            var initialUsage = document.getElementById('used_for').value || 'plan';
            populateTargetDropdown(initialUsage, savedPlanIds);
        });
    </script>
    <script>
        // Get coupon type
        function couponType(type) {
            "use strict";
            var discount = type.value;
            switch (discount) {
                case 'fixed':
                    $("#discount-addon").html(`{{ $config[1]->config_value }}`);
                    $("#discount").attr("max", "");
                    break;
                case 'percentage':
                    $("#discount-addon").html("%");
                    $("#discount").attr("max", 100);
                    break;
            }
        }
    </script>
@endsection
