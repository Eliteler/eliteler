@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Business Hours CSS --}}
    <link rel="stylesheet" href="{{ asset('css/business-hours.css') }}" />
    
    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />
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
                                {{ __('Business Hours') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Business Hours') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Define your operating hours, specify closed intervals, and configure availability periods.') }}
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
                                        'link' => 'hours',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.business.hours', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body p-3 p-lg-3">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">{{ __('Business Hours') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Configure your weekly working schedule, 24-hour open status, or holiday hours.') }}
                                        </p>
                                    </div>

                                    {{-- General Visibility Switches --}}
                                    <div class="row g-4 mb-4">
                                        {{-- 24 Hours Option --}}
                                        <div class="col-12 col-xl-6">
                                            <div class="settings-switch-card h-100">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h4 class="fw-bold text-dark m-0">
                                                            {{ __('Always Open / 24 Hours') }}</h4>
                                                        <p class="text-muted small mb-0 mt-1">
                                                            {{ __('Keep your business card marked as open 24/7 without time restrictions.') }}
                                                        </p>
                                                    </div>
                                                    <label class="form-check form-switch m-0 ms-3">
                                                        <input id="always-open" class="form-check-input" type="checkbox"
                                                            name="always_open"
                                                            {{ $business_hrs['alwaysOpen'] == 'Opening' ? 'checked' : '' }}>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Hide Business Hours Option --}}
                                        <div class="col-12 col-xl-6">
                                            <div class="settings-switch-card h-100">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h4 class="fw-bold text-dark m-0">{{ __('Hide Business Hours') }}
                                                        </h4>
                                                        <p class="text-muted small mb-0 mt-1">
                                                            {{ __('Hide the business hours section entirely from your business card.') }}
                                                        </p>
                                                    </div>
                                                    <label class="form-check form-switch m-0 ms-3">
                                                        <input id="display-hrs" class="form-check-input" type="checkbox"
                                                            onchange="toggleDisplay('is_display', '#business-hrs')"
                                                            name="is_display"
                                                            {{ $business_hrs['isDisplay'] == 0 ? 'checked' : '' }}>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Weekly Schedule Section --}}
                                    <div id="business-hrs" class="row g-3">
                                        <div class="col-12 border-top pt-3 mb-2">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Weekly Operating Schedule') }}</h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Set opening and closing timings for each day of the week or mark specific days as closed.') }}
                                            </p>
                                        </div>

                                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <div class="col-12 col-md-6 col-xl-3">
                                                <div class="day-hours-card h-100">
                                                    {{-- Day Header & Closed Toggle --}}
                                                    <div
                                                        class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                        <h4 class="fw-bold text-dark m-0">{{ __(ucfirst($day)) }}</h4>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span
                                                                class="text-muted small fw-medium">{{ __('Closed') }}</span>
                                                            <label class="form-check form-switch m-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    onload="toggleDisplay('{{ $day }}_closed', '#{{ $day }}-business')"
                                                                    onchange="toggleDisplay('{{ $day }}_closed', '#{{ $day }}-business')"
                                                                    name="{{ $day }}_closed"
                                                                    {{ $business_hrs[$day . '_status'] == 'Closed' ? 'checked' : '' }}>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    {{-- Time Inputs (Hidden when closed) --}}
                                                    <div class="gap-3" id="{{ $day }}-business">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label small text-muted mb-1">{{ __('Opening Time') }}</label>
                                                            <input type="time" class="form-control"
                                                                name="{{ $day }}_open"
                                                                placeholder="{{ __('Choose opening time') }}"
                                                                value="{{ $business_hrs[$day . '_open'] }}">
                                                        </div>
                                                        <div class="mb-0">
                                                            <label
                                                                class="form-label small text-muted mb-1">{{ __('Closing Time') }}</label>
                                                            <input type="time" class="form-control"
                                                                name="{{ $day }}_closing"
                                                                placeholder="{{ __('Choose closing time') }}"
                                                                value="{{ $business_hrs[$day . '_close'] }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            const businessHours = document.getElementById('business-hrs');
            const alwaysOpenCheckbox = document.getElementById('always-open');
            const displayHrsCheckbox = document.getElementById('display-hrs');

            // Initialize toggles for each day on page load
            days.forEach(function(day) {
                toggleDisplay(day + '_closed', '#' + day + '-business');
            });

            // Check if business hours should be hidden initially
            @if ($business_hrs['isDisplay'] === 0 || $business_hrs['alwaysOpen'] === 'Opening')
                if (businessHours) {
                    businessHours.style.display = 'none';
                }
            @endif

            // Handle "Always Open" checkbox changes
            if (alwaysOpenCheckbox) {
                alwaysOpenCheckbox.addEventListener('change', function() {
                    if (alwaysOpenCheckbox.checked) {
                        displayHrsCheckbox.checked = false;
                        if (businessHours) {
                            businessHours.style.display = 'none';
                        }
                    } else {
                        if (businessHours) {
                            businessHours.style.display = 'flex';
                        }
                    }
                });
            }

            // Handle "Display Hours" checkbox changes
            if (displayHrsCheckbox) {
                displayHrsCheckbox.addEventListener('change', function() {
                    if (displayHrsCheckbox.checked) {
                        alwaysOpenCheckbox.checked = false;
                    }
                });
            }
        });

        /**
         * Toggles the display of an element based on the state of an input checkbox.
         *
         * @param {string} inputName - The name of the input checkbox.
         * @param {string} elementId - The ID of the element to toggle.
         */
        function toggleDisplay(inputName, elementId) {
            "use strict";
            const isChecked = document.querySelector(`input[name="${inputName}"]:checked`) !== null;
            const element = document.querySelector(elementId);

            if (element) {
                element.style.display = isChecked ? 'none' : 'flex';
            }
        }
    </script>
@endpush
