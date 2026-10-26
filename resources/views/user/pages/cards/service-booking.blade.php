@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry) --}}
@section('css')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Service Booking Configuration') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Service Booking Configuration') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Manage operation schedules, define booking availability windows, and customize automatic email notifications.') }}
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
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Resolved nesting elements) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.service.booking', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-4">
                                        <!-- Service Booking Toggle -->
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="service_booking">{{ __('Service Booking: On / Off') }}</label>
                                                <label class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        onchange="displayServiceBooking()" name="service_booking"
                                                        id="service_booking">
                                                </label>
                                                {{-- Hidden backup input --}}
                                                <input type="hidden" name="service_booking" value="1">
                                            </div>
                                        </div>

                                        <div class="row g-4 mt-2 d-none" id="serviceBooking">
                                            {{-- Basic Configuration --}}
                                            <div class="col-md-6">
                                                <h4 class="card-title mb-3">{{ __('Basic Configuration') }}</h4>

                                                {{-- Available days --}}
                                                <div class="mb-3">
                                                    <label class="form-label required"
                                                        for="service_booking_available_days">{{ __('Available days (Sunday, Monday, etc.)') }}</label>
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
                                                    <label class="form-label required"
                                                        for="service_booking_start_time">{{ __('Start time') }}</label>
                                                    <input type="time" class="form-control timepicker"
                                                        name="service_booking_start_time" id="service_booking_start_time"
                                                        value="{{ old('service_booking_start_time') ?? '08:00' }}"
                                                        placeholder="{{ __('Choose start time') }}" required>
                                                </div>

                                                {{-- End time --}}
                                                <div class="mb-3">
                                                    <label class="form-label required"
                                                        for="service_booking_end_time">{{ __('End time') }}</label>
                                                    <input type="time" class="form-control timepicker"
                                                        name="service_booking_end_time" id="service_booking_end_time"
                                                        value="{{ old('service_booking_end_time') ?? '17:00' }}"
                                                        placeholder="{{ __('Choose end time') }}" required>
                                                </div>
                                            </div>

                                            <!-- Service Booking Email Configuration -->
                                            <div class="col-md-6">
                                                <h4 class="card-title mb-3">{{ __('Service Booking Email Configuration') }}
                                                </h4>

                                                <div class="mb-3">
                                                    <label class="form-label required" for="service_booking_receive_email">
                                                        {{ __('Email address to receive booking notifications') }}
                                                    </label>
                                                    <input type="email" class="form-control"
                                                        name="service_booking_receive_email"
                                                        id="service_booking_receive_email"
                                                        value="{{ old('service_booking_receive_email') }}"
                                                        placeholder="{{ __('Enter email address') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer containing save trigger styled alone -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    {{-- Tom Select --}}
    <script src="{{ asset('js/tom-select.base.min.js') }}"></script>
    {{-- Flatpickr --}}
    <script src="{{ asset('js/flatpickr.min.js') }}"></script>
    <script>
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    </script>
    <script>
        const elementSelectors = ['service_booking_available_days'];

        function initializeTomSelect(el) {
            new TomSelect(el, {
                placeholder: '{{ __('Select Days') }}',
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        return data.customProperties ?
                            `<div><span class="dropdown-item-indicator">${data.customProperties}</span>${escape(data.text)}</div>` :
                            `<div>${escape(data.text)}</div>`;
                    },
                    option: function(data, escape) {
                        return data.customProperties ?
                            `<div><span class="dropdown-item-indicator">${data.customProperties}</span>${escape(data.text)}</div>` :
                            `<div>${escape(data.text)}</div>`;
                    },
                },
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            elementSelectors.forEach(function(id) {
                const el = document.getElementById(id);
                if (el) {
                    el.removeAttribute('required');
                    initializeTomSelect(el);
                }
            });

            displayServiceBooking();
        });

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
                $('input[name="service_booking"]').val(0);
            } else {
                serviceBooking.classList.remove('d-none');
                inputs.forEach(input => input.required = true);
                $('input[name="service_booking"]').val(1);
            }
        }
    </script>
@endpush
