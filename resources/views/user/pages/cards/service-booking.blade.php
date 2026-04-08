@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
@endsection

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
                        <h2 class="page-title">
                            {{ __('Service Booking') }}
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

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-12 d-flex flex-column">
                            <form action="{{ route('user.save.service.booking', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-4">
                                        <!-- Service Booking Toggle -->
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Service Booking: On / Off') }}</label>
                                                <label class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        onchange="displayServiceBooking()" name="service_booking"
                                                        id="service_booking">
                                                </label>
                                                {{-- Hidden value --}}
                                                <input type="hidden" name="service_booking" value="1">
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
                                                    @endphp

                                                    <select name="service_booking_available_days[]"
                                                        id="service_booking_available_days" class="form-select" multiple>
                                                        @foreach ($daysOfWeek as $day)
                                                            <option value="{{ $day }}">
                                                                {{ __(ucfirst($day)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Start time --}}
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Start time') }}</label>
                                                    <input type="time" class="form-control timepicker"
                                                        name="service_booking_start_time" id="service_booking_start_time"
                                                        value="{{ old('service_booking_start_time') ?? '08:00' }}"
                                                        placeholder="{{ __('Start time') }}" required>
                                                </div>

                                                {{-- End time --}}
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('End time') }}</label>
                                                    <input type="time" class="form-control timepicker"
                                                        name="service_booking_end_time" id="service_booking_end_time"
                                                        value="{{ old('service_booking_end_time') ?? "17:00" }}"
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
                                                        value="{{ old('service_booking_receive_email') }}"
                                                        placeholder="{{ __('Email Address') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.customization', Request::segment(3)) }}" class="btn btn-outline-primary ms-2">{{ __('Skip') }}</a>
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
            // Array of element IDs
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

            // Function to display/hide Service Booking section and manage "required"
            function displayServiceBooking() {
                "use strict";

                const serviceBooking = document.getElementById('serviceBooking');
                const serviceBookingCheckbox = document.getElementById('service_booking');

                const inputs = document.querySelectorAll(
                    'input[name="service_booking_amount"], ' +
                    'input[name="service_booking_start_time"], ' +
                    'input[name="service_booking_end_time"], ' +
                    'select[name="service_booking_available_days[]"]'
                );

                if (serviceBookingCheckbox.checked) {
                    serviceBooking.classList.add('d-none');
                    inputs.forEach(input => input.required = false);

                    // Set value to service booking off
                    $('input[name="service_booking"]').val(0);
                } else {
                    serviceBooking.classList.remove('d-none');
                    inputs.forEach(input => input.required = true);

                    // Set value to service booking on
                    $('input[name="service_booking"]').val(1);
                }
            }
        </script>
    @endpush
@endsection
