@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry for page-specific style overrides) --}}
@section('css')
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
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
                        <h2 class="page-title">
                            {{ __('Appointment Settings') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Establish dynamic block times, assign custom slot values, and manage automatic notification filters.') }}
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
                        <!-- Borderless Shadowed Card Wrapper (Resolved double-card structure) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.appointment', Request::segment(3)) }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <h4 class="card-title mb-3">{{ __('Set Appointment Timings') }}</h4>

                                    {{-- Slot duration --}}
                                    <div class="mb-4">
                                        <label class="form-label" for="slot_duration">{{ __('Slot duration') }}</label>
                                        <div class="input-group mb-2">
                                            <input type="number" name="slot_duration" id="slot_duration"
                                                class="form-control"
                                                placeholder="{{ __('Enter slot duration in minutes. Example: 30') }}"
                                                min="10" step="5" max="60" value="30">
                                            <span class="input-group-text">
                                                {{ __('minutes') }}
                                            </span>
                                        </div>
                                        <small class="text-muted">{{ __('Max duration is 60 minutes.') }}</small>
                                    </div>

                                    {{-- Days of the week --}}
                                    <div class="mb-3 mt-4">
                                        <h3 class="card-title mb-2">{{ __('Days of the week') }}</h3>
                                    </div>
                                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                        <div class="mb-3">
                                            <label class="form-label">{{ __($day) }}</label>
                                            <div class="input-group">
                                                <select name="time_slots[{{ $day }}][]"
                                                    class="form-select time-select" multiple>
                                                    <option value="" disabled selected>
                                                        {{ __('Choose your time slots (multiple)') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Price --}}
                                    <div class="mb-3 mt-5">
                                        <h3 class="card-title mb-2">{{ __('Price') }}</h3>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label required" for="price">{{ __('Fee') }}</label>
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

                                    {{-- Appointment Form Email Configuration --}}
                                    <div class="mb-3 mt-5">
                                        <h4 class="card-title mb-2">{{ __('Appointment Email Configuration') }}</h4>
                                    </div>

                                    <div class="mb-1">
                                        <label class="form-label required"
                                            for="appointment_receive_email">{{ __('Want to receive email') }}</label>
                                        <input type="email" class="form-control" name="appointment_receive_email"
                                            id="appointment_receive_email" placeholder="{{ __('Enter email address') }}">
                                    </div>
                                </div>

                                <!-- Standardized Right-Aligned Footer containing single submit trigger -->
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
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <!-- Tomselect JS -->
    <script src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slotDurationInput = document.getElementById('slot_duration');
            const timeSelects = document.querySelectorAll('.time-select');

            const tomSelectInstances = Array.from(timeSelects).map(select => {
                return new TomSelect(select, {
                    copyClassesToDropdown: false,
                    dropdownParent: 'body',
                    controlInput: '<input>',
                    maxOptions: null,
                });
            });

            function generateTimeSlots(duration) {
                if (isNaN(duration) || duration <= 0) {
                    return;
                }

                tomSelectInstances.forEach(tomSelectInstance => {
                    tomSelectInstance.clearOptions();

                    for (let i = 0; i < 24 * 60; i += duration) {
                        const start = String(Math.floor(i / 60)).padStart(2, '0') + ':' + String(i % 60)
                            .padStart(2, '0');
                        const endMinutes = i + duration;

                        if (endMinutes > 24 * 60) {
                            break;
                        }

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

            generateTimeSlots(Math.min(parseInt(slotDurationInput.value), 60));

            slotDurationInput.addEventListener('change', function() {
                let duration = parseInt(this.value);
                duration = Math.min(duration, 60);
                generateTimeSlots(duration);
            });
        });
    </script>
@endpush
