@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography & Tabler Design --}}
@section('css')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
    
    {{-- Service Booking CSS --}}
    <link rel="stylesheet" href="{{ asset('css/service-booking.css') }}" />
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb">
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
                                {{ __('Booking Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Booking Settings') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Toggle booking features, establish operational times, select available days, and configure customer notifications.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
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

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar Navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'booking',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.booking', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body p-3 p-lg-3">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">
                                            {{ __('Service Booking Configuration') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Configure direct customer bookings, working days schedule, and email alerts.') }}
                                        </p>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Section 1: Service Booking Toggle Switch Card -->
                                        <div class="col-12">
                                            <div class="settings-switch-card">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 text-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="32"
                                                                height="32" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-time">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                                                                <path d="M14 18a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                                                <path d="M15 3v4" />
                                                                <path d="M7 3v4" />
                                                                <path d="M3 11h16" />
                                                                <path d="M18 16.496v1.504l1 1" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="fw-bold text-dark mb-1">
                                                                {{ __('Service Booking') }}</h4>
                                                            <p class="text-muted small mb-0">
                                                                {{ __('Allow clients to book service appointments directly from your digital card.') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch m-0 ms-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            onchange="displayServiceBooking()" id="service_booking"
                                                            {{ isset($serviceBooking) && $serviceBooking->service_booking == 1 ? 'checked' : '' }}>
                                                        <input type="hidden" name="service_booking"
                                                            id="service_booking_hidden"
                                                            value="{{ isset($serviceBooking) && $serviceBooking->service_booking == 1 ? '1' : '0' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Collapsible Service Booking Settings Form -->
                                        <div class="col-12 {{ isset($serviceBooking) && $serviceBooking->service_booking == 1 ? '' : 'd-none' }}"
                                            id="serviceBooking">
                                            <div class="row g-3">
                                                {{-- Section 2: Operating Schedule & Times --}}
                                                <div class="col-12 col-lg-6">
                                                    <div class="booking-section-card h-100">
                                                        <div class="mb-3">
                                                            <h4 class="fw-bold text-dark mb-1">
                                                                {{ __('Schedule & Operating Hours') }}</h4>
                                                            <p class="text-muted small mb-0">
                                                                {{ __('Define available working days and operating time window.') }}
                                                            </p>
                                                        </div>

                                                        {{-- Available days select option --}}
                                                        <div class="mb-3">
                                                            <label class="form-label required"
                                                                for="service_booking_available_days">
                                                                {{ __('Available Days') }}
                                                            </label>
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

                                                                $selectedMap = array_fill_keys($daysOfWeek, 0);

                                                                if (isset($serviceBooking)) {
                                                                    $value =
                                                                        $serviceBooking->service_booking_available_days;

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
                                                                id="service_booking_available_days" class="form-select"
                                                                multiple placeholder="{{ __('Select Days') }}">
                                                                @foreach ($daysOfWeek as $day)
                                                                    <option value="{{ $day }}"
                                                                        {{ !empty($selectedMap[$day]) && $selectedMap[$day] == 1 ? 'selected' : '' }}>
                                                                        {{ __(ucfirst($day)) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="row g-2">
                                                            {{-- Start time input --}}
                                                            <div class="col-12 col-md-6 col-lg-6 mb-3">
                                                                <label class="form-label required"
                                                                    for="service_booking_start_time">
                                                                    {{ __('Start Time') }}
                                                                </label>
                                                                <div class="input-icon">
                                                                    <span class="input-icon-addon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="icon" width="20" height="20"
                                                                            viewBox="0 0 24 24" stroke-width="2"
                                                                            stroke="currentColor" fill="none"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                            <path d="M12 7v5l3 3" />
                                                                        </svg>
                                                                    </span>
                                                                    <input type="text" class="form-control timepicker"
                                                                        name="service_booking_start_time"
                                                                        id="service_booking_start_time"
                                                                        value="{{ isset($serviceBooking) && $serviceBooking->service_booking_start_time ? \Carbon\Carbon::parse($serviceBooking->service_booking_start_time)->format('H:i') : '' }}"
                                                                        placeholder="{{ __('Choose start time') }}">
                                                                </div>
                                                            </div>

                                                            {{-- End time input --}}
                                                            <div class="col-12 col-md-6 col-lg-6 mb-3">
                                                                <label class="form-label required"
                                                                    for="service_booking_end_time">
                                                                    {{ __('End Time') }}
                                                                </label>
                                                                <div class="input-icon">
                                                                    <span class="input-icon-addon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="icon" width="20" height="20"
                                                                            viewBox="0 0 24 24" stroke-width="2"
                                                                            stroke="currentColor" fill="none"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                            <path d="M12 7v5l3 3" />
                                                                        </svg>
                                                                    </span>
                                                                    <input type="text" class="form-control timepicker"
                                                                        name="service_booking_end_time"
                                                                        id="service_booking_end_time"
                                                                        value="{{ isset($serviceBooking) && $serviceBooking->service_booking_end_time ? \Carbon\Carbon::parse($serviceBooking->service_booking_end_time)->format('H:i') : '' }}"
                                                                        placeholder="{{ __('Choose end time') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Section 3: Notification Email Configuration --}}
                                                <div class="col-12 col-lg-6">
                                                    <div class="booking-section-card h-100">
                                                        <div class="mb-3">
                                                            <h4 class="fw-bold text-dark mb-1">
                                                                {{ __('Booking Email Configuration') }}</h4>
                                                            <p class="text-muted small mb-0">
                                                                {{ __('Specify destination email for instant booking notifications.') }}
                                                            </p>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label required"
                                                                for="service_booking_receive_email">
                                                                {{ __('Email address to receive booking notifications') }}
                                                            </label>
                                                            <div class="input-icon">
                                                                <span class="input-icon-addon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                        width="20" height="20" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                                        <path d="M3 7l9 6l9 -6" />
                                                                    </svg>
                                                                </span>
                                                                <input type="email" class="form-control"
                                                                    name="service_booking_receive_email"
                                                                    id="service_booking_receive_email"
                                                                    value="{{ isset($serviceBooking) && $serviceBooking->service_booking_receive_email ? $serviceBooking->service_booking_receive_email : '' }}"
                                                                    placeholder="{{ __('Enter email address') }}">
                                                            </div>
                                                            <small class="text-muted mt-1 d-block">
                                                                {{ __('Instant alerts will be sent to this email upon customer reservation.') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-between py-3">
                                    <a href="{{ route('user.cards') }}"
                                        class="btn btn-danger text-decoration-none fw-semibold">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
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
    {{-- Tom Select JS --}}
    <script src="{{ asset('js/tom-select.base.min.js') }}"></script>
    {{-- Flatpickr JS --}}
    <script src="{{ asset('js/flatpickr.min.js') }}"></script>
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Flatpickr Timepicker
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });

            // Initialize TomSelect for Days
            const daysSelect = document.getElementById('service_booking_available_days');
            if (daysSelect) {
                new TomSelect(daysSelect, {
                    placeholder: "{{ __('Select Days') }}",
                    copyClassesToDropdown: false,
                    dropdownParent: 'body',
                    controlInput: '<input>',
                    maxOptions: null,
                    plugins: ['remove_button'],
                });
            }

            displayServiceBooking();
        });

        function displayServiceBooking() {
            const serviceBookingContainer = document.getElementById('serviceBooking');
            const serviceBookingCheckbox = document.getElementById('service_booking');
            const hiddenStatusInput = document.getElementById('service_booking_hidden');

            const isChecked = serviceBookingCheckbox.checked;
            hiddenStatusInput.value = isChecked ? '1' : '0';

            const startTimeInput = document.getElementById('service_booking_start_time');
            const endTimeInput = document.getElementById('service_booking_end_time');
            const emailInput = document.getElementById('service_booking_receive_email');

            if (isChecked) {
                serviceBookingContainer.classList.remove('d-none');
                if (startTimeInput) startTimeInput.required = true;
                if (endTimeInput) endTimeInput.required = true;
                if (emailInput) emailInput.required = true;
            } else {
                serviceBookingContainer.classList.add('d-none');
                if (startTimeInput) startTimeInput.required = false;
                if (endTimeInput) endTimeInput.required = false;
                if (emailInput) emailInput.required = false;
            }
        }
    </script>
@endpush
