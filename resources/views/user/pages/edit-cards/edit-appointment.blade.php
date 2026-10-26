@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Appointment CSS --}}
    <link rel="stylesheet" href="{{ asset('css/appointment.css') }}" />
    
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
                                {{ __('Appointment Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Appointment Settings') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Configure operating hours, block time intervals, set weekday schedules, and enable appointments for business cards.') }}
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
                                        'link' => 'appointment',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.appointment', Request::segment(3)) }}" method="post"
                                id="myForm">
                                @csrf
                                <div class="card-body p-3 p-lg-3">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">{{ __('Set Appointment Timings') }}
                                        </h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Configure slot durations, specify daily available time windows, and set notification preferences.') }}
                                        </p>
                                    </div>

                                    {{-- Section 1: Slot Duration --}}
                                    <div class="appointment-section-card">
                                        <div class="row align-items-center">
                                            <div class="col-12 col-md-6 col-lg-5">
                                                <label class="form-label"
                                                    for="slot_duration">{{ __('Slot Duration') }}</label>
                                                <div class="input-group">
                                                    <input type="number" name="slot_duration" id="slot_duration"
                                                        class="form-control"
                                                        placeholder="{{ __('Enter slot duration in minutes. Example: 30') }}"
                                                        min="10" step="5" max="60"
                                                        value="{{ $appointmentSlots->first()->slot_duration ?? 30 }}">
                                                    <span class="input-group-text">
                                                        {{ __('minutes') }}
                                                    </span>
                                                </div>
                                                <small
                                                    class="text-muted mt-1 d-block">{{ __('Max duration is 60 minutes.') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 2: Days of the Week Schedule --}}
                                    <div class="appointment-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Days of the Week Schedule') }}</h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Select available time slots for each day. Leave empty for unavailable days.') }}
                                            </p>
                                        </div>

                                        <div class="row g-3">
                                            @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                <div class="col-12 col-lg-3 mb-3">
                                                    <div class="px-3 py-3 border rounded-3 bg-light-subtle">
                                                        <label
                                                            class="form-label fw-bold text-dark mb-2">{{ __($day) }}</label>
                                                        <select name="time_slots[{{ $day }}][]"
                                                            data-day="{{ strtolower($day) }}"
                                                            class="form-select time-select"
                                                            placeholder="{{ __('Choose time slots') }}" multiple>
                                                        </select>
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Price --}}
                                            <div class="mb-3 mt-5 d-none">
                                                <h3 class="card-title">{{ __('Price') }}</h3>
                                            </div>
                                            <div class="mb-3 d-none">
                                                <label class="form-label required"
                                                    for="price">{{ __('Fee') }}</label>
                                                <div class="input-group mb-2">
                                                    <input type="number" name="price" id="price" class="form-control"
                                                        placeholder="{{ __('Enter price') }}" value="0" min="0"
                                                        step=".001" required>
                                                    <span class="input-group-text">
                                                        {{ __('per slot') }}
                                                    </span>
                                                </div>
                                                <small class="text-muted">{{ __('Set the price 0 for free') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 3: Notification Email --}}
                                    <div class="appointment-section-card mb-0">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Appointment Email Configuration') }}
                                            </h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Specify where you would like to receive booking confirmation and alert emails.') }}
                                            </p>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label" id="appointment_receive_email_label"
                                                    for="appointment_receive_email">{{ __('Want to receive booking emails?') }}</label>
                                                <input type="email" class="form-control"
                                                    name="appointment_receive_email" id="appointment_receive_email"
                                                    value="{{ $business_card->appointment_receive_email }}"
                                                    placeholder="{{ __('Enter email address') }}">
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
    <!-- Tomselect JS -->
    <script src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            const slotDurationInput = document.getElementById('slot_duration');
            const timeSelects = document.querySelectorAll('.time-select');
            const emailInput = document.getElementById('appointment_receive_email');
            const emailLabel = document.getElementById('appointment_receive_email_label');

            const timeSlotsByDay = {!! $time_slots !!};

            // Function to show first 1 tags and +N more badge with Tooltip
            const transRemainingSlots = "{{ __('Remaining Slots:') }}";
            const transMore = "{{ __('more') }}";

            function updateSlotDisplay(instance) {
                const control = instance.control;
                const items = control.querySelectorAll('.item');

                const existingBadge = control.querySelector('.slot-more-badge');
                if (existingBadge) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        const tip = bootstrap.Tooltip.getInstance(existingBadge);
                        if (tip) tip.dispose();
                    }
                    existingBadge.remove();
                }

                const values = instance.getValue();
                const total = Array.isArray(values) ? values.length : (values ? [values].length : 0);

                if (total > 1) {
                    items.forEach((item, index) => {
                        item.style.display = (index < 1) ? 'inline-flex' : 'none';
                    });

                    const remainingValues = values.slice(1);
                    const tooltipText = remainingValues.join('<br>');

                    const moreBadge = document.createElement('span');
                    moreBadge.className = 'slot-more-badge';
                    moreBadge.setAttribute('data-bs-toggle', 'tooltip');
                    moreBadge.setAttribute('data-bs-html', 'true');
                    moreBadge.setAttribute('data-bs-placement', 'top');
                    // Translated title
                    moreBadge.setAttribute('title', `<strong>${transRemainingSlots}</strong><br>${tooltipText}`);
                    // Translated "+N more" text
                    moreBadge.textContent = `+${remainingValues.length} ${transMore}`;

                    if (items[1]) {
                        items[1].after(moreBadge);
                        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                            new bootstrap.Tooltip(moreBadge);
                        }
                    }
                } else {
                    items.forEach(item => item.style.display = 'inline-flex');
                }
            }

            // Initialize TomSelect instances
            const tomSelectInstances = Array.from(timeSelects).map(select => {
                const ts = new TomSelect(select, {
                    copyClassesToDropdown: false,
                    dropdownParent: 'body',
                    controlInput: '<input>',
                    maxOptions: null,
                    plugins: ['remove_button'],
                });

                // Attach events to immediately update tags without any lag
                ts.on('item_add', function() {
                    updateSlotDisplay(ts);
                    updateEmailRequirement();
                });

                ts.on('item_remove', function() {
                    updateSlotDisplay(ts);
                    updateEmailRequirement();
                });

                ts.on('clear', function() {
                    updateSlotDisplay(ts);
                    updateEmailRequirement();
                });

                return ts;
            });

            function updateEmailRequirement() {
                if (!emailInput || !emailLabel) return;

                const hasSelectedSlots = tomSelectInstances.some(instance => {
                    const value = instance.getValue();
                    return Array.isArray(value) ? value.length > 0 : !!value;
                });

                if (hasSelectedSlots) {
                    emailLabel.classList.add('required');
                    emailInput.setAttribute('required', 'required');
                } else {
                    emailLabel.classList.remove('required');
                    emailInput.removeAttribute('required');
                }
            }

            function generateTimeSlots(duration) {
                if (isNaN(duration) || duration <= 0) return;

                tomSelectInstances.forEach(tomSelectInstance => {
                    tomSelectInstance.clearOptions();

                    for (let i = 0; i < 24 * 60; i += duration) {
                        const start = String(Math.floor(i / 60)).padStart(2, '0') + ':' + String(i % 60)
                            .padStart(2, '0');
                        const endMinutes = i + duration;

                        if (endMinutes > 24 * 60) break;

                        const end = String(Math.floor(endMinutes / 60)).padStart(2, '0') + ':' + String(
                            endMinutes % 60).padStart(2, '0');

                        tomSelectInstance.addOption({
                            value: `${start} - ${end}`,
                            text: `${start} - ${end}`,
                        });
                    }

                    tomSelectInstance.refreshOptions(false);
                });
            }

            // Generate slots initially
            generateTimeSlots(Math.min(parseInt(slotDurationInput.value), 60));

            slotDurationInput.addEventListener('change', function() {
                let duration = parseInt(this.value);
                duration = Math.min(duration, 60);
                tomSelectInstances.forEach(tomSelectInstance => {
                    tomSelectInstance.clear();
                });
                generateTimeSlots(duration);
                updateEmailRequirement();
            });

            // Set initial selected values for each day
            tomSelectInstances.forEach(select => {
                const day = select.input.dataset.day;

                if (day && timeSlotsByDay[day]) {
                    try {
                        const valuesToSelect = JSON.parse(timeSlotsByDay[day]);
                        select.setValue(valuesToSelect);
                        updateSlotDisplay(select);
                    } catch (e) {
                        // Safe exception bypass
                    }
                }
            });

            updateEmailRequirement();
        });
    </script>
@endpush
