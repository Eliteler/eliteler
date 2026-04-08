@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const isServiceBookingActive =
                {{ isset($serviceBooking) && $serviceBooking->service_booking == 1 ? 'true' : 'false' }};

            if (isServiceBookingActive) {
                // Show section
                $("#serviceBooking").removeClass("d-none");

                // Checked checkbox
                $("#service_booking").prop("checked", false);

                // Set value to service booking
                $('input[name="service_booking"]').val(1);

                // Set value to service details
                @php
                    $selectedDays = [];

                    if (isset($serviceBooking)) {
                        $days = $serviceBooking->service_booking_available_days;

                        if (is_array($days)) {
                            $selectedDays = array_keys(array_filter($days));
                        } elseif (is_string($days)) {
                            $decoded = json_decode($days, true);
                            if (is_array($decoded)) {
                                $selectedDays = array_keys(array_filter($decoded));
                            }
                        }
                    }
                @endphp

                $('#service_booking_available_days').val(@json($selectedDays));
                $('#service_booking_min_hours').val("{{ $serviceBooking->service_booking_min_hours ?? '' }}");
                $('#service_booking_before_booking_allowed').val(
                    "{{ $serviceBooking->service_booking_before_booking_allowed ?? '' }}");
                $('#service_booking_start_time').val("{{ $serviceBooking->service_booking_start_time ?? '' }}");
                $('#service_booking_end_time').val("{{ $serviceBooking->service_booking_end_time ?? '' }}");
                $('#service_booking_receive_email').val(
                    "{{ $serviceBooking->service_booking_receive_email ?? '' }}");
            } else {
                // Unchecked checkbox
                $("#service_booking").prop("checked", true);

                // Hide section
                $("#serviceBooking").addClass("d-none");
            }
        });
    </script>
@endsection

@section('content')
    <div class="page-wrapper">
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

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Business Card') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'booking',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <form action="{{ route('user.update.booking', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title mb-4">{{ __('Service Booking') }}</h3>

                                    <div class="row g-4">
                                        <!-- Service Booking Toggle -->
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Service Booking: On / Off') }}</label>
                                                <label class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        onchange="displayServiceBooking()" name="service_booking"
                                                        id="service_booking">
                                                    {{-- Hidden value --}}
                                                    <input type="hidden" name="service_booking"
                                                        value="{{ isset($serviceBooking) && $serviceBooking->service_booking == 1 ? '1' : '0' }}">
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row mt-2 d-none" id="serviceBooking">
                                            {{-- Basic Configuration --}}
                                            <div class="col-md-6">
                                                <h4>{{ __('Basic Configuration') }}</h4>

                                                {{-- Available days (Sunday, Monday, etc.) --}}
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Available days (Sunday, Monday, etc.)') }}</div>
                                                    @php
                                                        $daysOfWeek = [
                                                            'sunday',
                                                            'monday',
                                                            'tuesday',
                                                            'wednesday',
                                                            'thursday',
                                                            'friday',
                                                            'saturday',
                                                        ];

                                                        // Default to all days unselected
                                                        $selectedMap = array_fill_keys($daysOfWeek, 0);

                                                        if (isset($serviceBooking)) {
                                                            $value = $serviceBooking->service_booking_available_days;

                                                            if (is_array($value)) {
                                                                $selectedMap = $value;
                                                            } elseif (is_string($value)) {
                                                                $decoded = json_decode($value, true);
                                                                if (is_array($decoded)) {
                                                                    $selectedMap = $decoded;
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    <select name="service_booking_available_days[]"
                                                        id="service_booking_available_days" class="form-select" multiple>
                                                        @foreach ($daysOfWeek as $day)
                                                            <option value="{{ $day }}"
                                                                {{ !empty($selectedMap[$day]) && $selectedMap[$day] == 1 ? 'selected' : '' }}>
                                                                {{ __(ucfirst($day)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Start time --}}
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Start time') }}</label>
                                                    <input type="text" class="form-control timepicker"
                                                        name="service_booking_start_time" id="service_booking_start_time"
                                                        value="{{ isset($serviceBooking) && $serviceBooking->service_booking_start_time ? \Carbon\Carbon::parse($serviceBooking->service_booking_start_time)->format('H:i') : '' }}"
                                                        placeholder="{{ __('Start time') }}" required>
                                                </div>

                                                {{-- End time --}}
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('End time') }}</label>
                                                    <input type="text" class="form-control timepicker"
                                                        name="service_booking_end_time" id="service_booking_end_time"
                                                        value="{{ isset($serviceBooking) && $serviceBooking->service_booking_end_time ? \Carbon\Carbon::parse($serviceBooking->service_booking_end_time)->format('H:i') : '' }}"
                                                        placeholder="{{ __('End time') }}" required>
                                                </div>
                                            </div>

                                            <!-- Service Booking Email Configuration -->
                                            <div class="col-md-6">
                                                <h4>{{ __('Service Booking Email Configuration') }}</h4>

                                                <div class="mb-3">
                                                    <label
                                                        class="form-label required">{{ __('Email address to receive booking notifications') }}</label>
                                                    <input type="email" class="form-control"
                                                        name="service_booking_receive_email"
                                                        id="service_booking_receive_email"
                                                        value="{{ isset($serviceBooking) && $serviceBooking->service_booking_receive_email ? $serviceBooking->service_booking_receive_email : '' }}"
                                                        placeholder="{{ __('Email Address') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.cards') }}"
                                            class="btn btn-outline-primary ms-2">{{ __('Cancel') }}</a>
                                        {{-- Next link --}}
                                        @php
                                            $route = route('user.cards');

                                            // Check business hours is "ENABLED"
                                            if (
                                                ($plan_details->password_protected == 1 ||
                                                    $plan_details->advanced_settings == 1) &&
                                                $business_card->type != 'custom'
                                            ) {
                                                $route = route('user.edit.advanced.setting', Request::segment(3));
                                            } elseif (
                                                $plan_details->password_protected == 1 &&
                                                $business_card->type == 'custom'
                                            ) {
                                                $route = route('user.edit.customization', Request::segment(3));
                                            }
                                        @endphp

                                        <a href="{{ $route }}" class="btn btn-outline-primary ms-2">
                                            {{ __('Skip') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('user.includes.footer')
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        {{-- Tom Select --}}
        <script src="{{ asset('js/tom-select.base.min.js') }}"></script>
        {{-- Flatpickr --}}
        <script src="{{ asset('js/flatpickr.min.js') }}"></script>
        <script>
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",   // 24h format without seconds
                time_24hr: true      // force 24-hour clock (remove AM/PM)
            });
        </script>
        <script>
            const elementSelectors = ['service_booking_available_days'];

            // Function to initialize TomSelect (no native required used)
            function initializeTomSelect(el) {
                new TomSelect(el, {
                    placeholder: '{{ __('Select Days') }}',
                    copyClassesToDropdown: false,
                    dropdownClass: 'dropdown-menu ts-dropdown',
                    optionClass: 'dropdown-item',
                    controlInput: '<input>',
                    maxOptions: null,
                    render: {
                        item: function (data, escape) {
                            return data.customProperties
                                ? `<div><span class="dropdown-item-indicator">${data.customProperties}</span>${escape(data.text)}</div>`
                                : `<div>${escape(data.text)}</div>`;
                        },
                        option: function (data, escape) {
                            return data.customProperties
                                ? `<div><span class="dropdown-item-indicator">${data.customProperties}</span>${escape(data.text)}</div>`
                                : `<div>${escape(data.text)}</div>`;
                        },
                    },
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                elementSelectors.forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el) {
                        // Remove required from hidden <select>
                        el.removeAttribute('required');
                        initializeTomSelect(el);
                    }
                });

                // Initial checkbox state
                displayServiceBooking();
            });
        </script>
        <script>
            // Function to display Service Booking
            function displayServiceBooking() {
                "use strict";

                const serviceBooking = document.getElementById('serviceBooking');
                const serviceBookingCheckbox = document.getElementById('service_booking');

                if (serviceBookingCheckbox.checked) {
                    serviceBooking.classList.add('d-none');

                    // Remove required attribute from all name="service_booking_available_days", name="service_booking_min_hours", name="service_booking_before_booking_allowed", name="service_booking_start_time", name="service_booking_end_time" inputs
                    const inputs = document.querySelectorAll(
                        'input[name="service_booking_available_days[]"], input[name="service_booking_amount"], input[name="service_booking_start_time"], input[name="service_booking_end_time"]'
                        );
                    inputs.forEach(input => {
                        input.required = false;
                    });

                    // Set value to service booking
                    $('input[name="service_booking"]').val(0);
                } else {
                    serviceBooking.classList.remove('d-none');

                    // Add required attribute from all name="service_booking_available_days", name="service_booking_min_hours", name="service_booking_before_booking_allowed", name="service_booking_start_time", name="service_booking_end_time" inputs
                    const inputs = document.querySelectorAll(
                        'input[name="service_booking_available_days[]"], input[name="service_booking_amount"], input[name="service_booking_start_time"], input[name="service_booking_end_time"]'
                        );
                    inputs.forEach(input => {
                        input.required = true;
                    });

                    // Set value to service booking
                    $('input[name="service_booking"]').val(1);
                }
            }
        </script>
    @endpush
@endsection
