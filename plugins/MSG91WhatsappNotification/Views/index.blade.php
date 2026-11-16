@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@php
    $new_user_registration_admin = $msg91_whatsapp_notification_templates['0'];
    $plan_purchase_admin = $msg91_whatsapp_notification_templates['1'];
    $plan_purchase_user = $msg91_whatsapp_notification_templates['2'];
    $plan_renewal_admin = $msg91_whatsapp_notification_templates['3'];
    $plan_renewal_user = $msg91_whatsapp_notification_templates['4'];
    $user_plan_expiry_remainder = $msg91_whatsapp_notification_templates['5'];
    $user_plan_expired_notification = $msg91_whatsapp_notification_templates['6'];
    $new_appointment_user = $msg91_whatsapp_notification_templates['7'];
    $new_appointment_customer = $msg91_whatsapp_notification_templates['8'];
    $appointment_confirmed_user = $msg91_whatsapp_notification_templates['9'];
    $appointment_confirmed_customer = $msg91_whatsapp_notification_templates['10'];
    $appointment_cancelled_user = $msg91_whatsapp_notification_templates['11'];
    $appointment_cancelled_customer = $msg91_whatsapp_notification_templates['12'];
    $appointment_rescheduled_user = $msg91_whatsapp_notification_templates['13'];
    $appointment_rescheduled_customer = $msg91_whatsapp_notification_templates['14'];
    $appointment_completed_user = $msg91_whatsapp_notification_templates['15'];
    $appointment_completed_customer = $msg91_whatsapp_notification_templates['16'];
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
                            {{ __('MSG91 Whatsapp Notification Settings') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('admin.plugins') }}" class="btn btn-primary text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l14 0" />
                                    <path d="M5 12l6 6" />
                                    <path d="M5 12l6 -6" />
                                </svg>
                                {{ __('Back') }}
                            </a>
                        </div>
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

                <div class="row row-deck row-cards">
                    <div class="col-sm-12 col-lg-12">
                        <form action="{{ route('admin.msg91_whatsapp_notification_settings.update') }}" method="post"
                            class="card">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('MSG91 Whatsapp Notification Credentials') }}</h4>
                            </div>
                            <div class="card-body">
                                {{-- MSG91 Settings --}}
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Auth Key --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Auth Key') }}</div>
                                                    <input type="text" class="form-control" name="auth_key"
                                                        value="{{ isset($msg91_whatsapp_notification_settings) ? $msg91_whatsapp_notification_settings->auth_key ?? '' : '' }}"
                                                        placeholder="{{ __('Auth Key') }}" autofocus required>
                                                </div>
                                            </div>
                                            {{-- Sender ID --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Integrated Number') }}</div>
                                                    <input type="text" class="form-control" name="sender_id"
                                                        value="{{ isset($msg91_whatsapp_notification_settings) ? $msg91_whatsapp_notification_settings->sender_id ?? '' : '' }}"
                                                        placeholder="{{ __('Sender ID') }}" required>
                                                </div>
                                            </div>
                                            {{-- Admin Number --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <div class="form-label required">
                                                        {{ __('Admin Whatsapp Number (with country code)') }}</div>
                                                    <input type="number" class="form-control" name="admin_number"
                                                        value="{{ isset($msg91_whatsapp_notification_settings) ? $msg91_whatsapp_notification_settings->admin_number ?? '' : '' }}"
                                                        placeholder="{{ __('Whatsapp Number') }}" required>
                                                </div>
                                            </div>
                                            <span>{{ __('If you did not get these credentials, create a') }}
                                                <a href="https://msg91.com/in"
                                                    target="_blank">{{ __('new MSG91 Whatsapp Notification Credentials.') }}</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Templates --}}
                    <div class="col-sm-12 col-lg-12 card">
                        {{-- Card Header --}}
                        <div class="card-header">
                            <h4 class="page-title">{{ __('MSG91 Whatsapp Notification Templates And Controls') }}</h4>
                        </div>
                        {{-- Card Body --}}
                        <div class="card-body">
                            <div class="accordion" id="accordion-example">
                                {{--  User Registration --}}
                                @include('msg91::includes.user-registration')
                                {{--  Plan Purchase --}}
                                @include('msg91::includes.plan-purchase')
                                {{--  Plan Renewal --}}
                                @include('msg91::includes.plan-renewal')
                                {{--  Plan Expiry Remainder --}}
                                @include('msg91::includes.plan-expiry')
                                {{--  Plan Expired Notification --}}
                                @include('msg91::includes.plan-expired')
                                {{--  Appointment Notification --}}
                                @include('msg91::includes.appointment')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@section('scripts')
    <!-- Custom JS -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element selectors
        var elementSelectors = ['.new_user_registration_admin', '.new_user_registration_user', '.plan_purchase_admin',
            '.plan_renewal_admin', '.plan_renewal_user', '.plan_purchase_user', '.user_remainder', '.variables',
            '.tom-select'
        ];

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

        // Initialize TomSelect on existing elements
        elementSelectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            elements.forEach(function(el) {
                initializeTomSelect(el);
            });
        });

        // Observe the document for dynamically added elements
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Ensure it's an element node
                        elementSelectors.forEach(function(selector) {
                            if (node.matches(selector)) {
                                initializeTomSelect(node);
                            }
                            // Also check if new nodes have children that match
                            var childElements = node.querySelectorAll(selector);
                            childElements.forEach(function(childEl) {
                                initializeTomSelect(childEl);
                            });
                        });
                    }
                });
            });
        });

        // Configure the observer
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // form 1
        document.getElementById("myForm1").addEventListener("submit", function(e) {
            let selectedValues = [];

            // Loop rows
            document.querySelectorAll("#variablesTable1 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues.push(select.value);
                }
            });

            // Put into hidden input
            document.getElementById("variablesInput1").value = JSON.stringify(selectedValues);
        });

        // form 2
        document.getElementById("myForm2").addEventListener("submit", function(e) {
            let selectedValues1 = [];
            let selectedValues2 = [];

            // Loop rows Admin
            document.querySelectorAll("#variablesTable2 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues1.push(select.value);
                }
            });

            // Loop rows User
            document.querySelectorAll("#variablesTable3 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues2.push(select.value);
                }
            });

            // Put into hidden input
            document.getElementById("variablesInput2").value = JSON.stringify(selectedValues1);
            document.getElementById("variablesInput3").value = JSON.stringify(selectedValues2);
        });

        // form 3
        document.getElementById("myForm3").addEventListener("submit", function(e) {
            let selectedValues1 = [];
            let selectedValues2 = [];

            // Loop rows Admin
            document.querySelectorAll("#variablesTable4 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues1.push(select.value);
                }
            });

            // Loop rows User
            document.querySelectorAll("#variablesTable5 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues2.push(select.value);
                }
            });

            // Put into hidden input
            document.getElementById("variablesInput4").value = JSON.stringify(selectedValues1);
            document.getElementById("variablesInput5").value = JSON.stringify(selectedValues2);
        });

        // form 4
        document.getElementById("myForm4").addEventListener("submit", function(e) {
            let selectedValues = [];

            // Loop rows
            document.querySelectorAll("#variablesTable6 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues.push(select.value);
                }
            });

            // Put into hidden input
            document.getElementById("variablesInput6").value = JSON.stringify(selectedValues);
        });

        // form 5
        document.getElementById("myForm5").addEventListener("submit", function(e) {
            let selectedValues = [];

            // Loop rows
            document.querySelectorAll("#variablesTable7 tr").forEach((row, index) => {
                if (index === 0) return; // skip header

                let checkbox = row.querySelector(".variable-check");
                let select = row.querySelector(".variable-select");

                if (checkbox && checkbox.checked && select) {
                    selectedValues.push(select.value);
                }
            });

            // Put into hidden input
            document.getElementById("variablesInput7").value = JSON.stringify(selectedValues);
        });

        // Form 6
        document.getElementById("myForm6").addEventListener("submit", function() {

            document.querySelectorAll("#myForm6 table[id^='variablesTable_']").forEach(table => {

                let key = table.id.replace("variablesTable_", "");
                let values = [];

                table.querySelectorAll("tr").forEach((row, index) => {
                    if (index === 0) return;

                    let checkbox = row.querySelector(".variable-check");
                    let select = row.querySelector(".variable-select");

                    if (checkbox && checkbox.checked && select) {
                        values.push(select.value);
                    }
                });

                document.getElementById("variablesInput_" + key).value = JSON.stringify(values);
            });

        });
    </script>
@endsection
@endsection
