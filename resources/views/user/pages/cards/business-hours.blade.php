@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry) --}}
@section('css')
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header (Wraps the inner container-fluid) -->
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
                                {{ __('Business Hours') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Business Hours') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Define your operating hours, specify closed intervals, and configure availability periods.') }}
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
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.business.hours', Request::segment(3)) }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-xl-12">
                                            <h2 class="page-title mb-3">
                                                {{ __('Always Open') }}
                                            </h2>

                                            <div class="row mb-4">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <div class="form-label">{{ __('24 Hours') }}</div>
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" id="always-open" type="checkbox"
                                                                name="always_open">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <div class="form-label">{{ __('Hide Business Hours') }}</div>
                                                        <label class="form-check form-switch">
                                                            <input id="display-hrs" class="form-check-input" type="checkbox"
                                                                onchange="displayBusiness()" name="is_display">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Business Hours Configurations -->
                                            <div id="business-hrs">
                                                <div class="row g-3">
                                                    <!-- Monday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Monday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="MondayBusiness()" name="monday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="monday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="monday_open"
                                                                    placeholder="{{ __('Opening Time') }}" value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="monday_closing"
                                                                    placeholder="{{ __('Closing Time') }}" value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tuesday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Tuesday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="TuesdayBusiness()" name="tuesday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="tuesday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="tuesday_open"
                                                                    placeholder="{{ __('Opening Time') }}" value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="tuesday_closing"
                                                                    placeholder="{{ __('Closing Time') }}"
                                                                    value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Wednesday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Wednesday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="WednesdayBusiness()"
                                                                        name="wednesday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="wednesday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="wednesday_open"
                                                                    placeholder="{{ __('Opening Time') }}"
                                                                    value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="wednesday_closing"
                                                                    placeholder="{{ __('Closing Time') }}"
                                                                    value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Thursday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Thursday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="ThursdayBusiness()"
                                                                        name="thursday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="thursday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="thursday_open"
                                                                    placeholder="{{ __('Opening Time') }}"
                                                                    value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="thursday_closing"
                                                                    placeholder="{{ __('Closing Time') }}"
                                                                    value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Friday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Friday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="FridayBusiness()" name="friday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="friday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="friday_open"
                                                                    placeholder="{{ __('Opening Time') }}"
                                                                    value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="friday_closing"
                                                                    placeholder="{{ __('Closing Time') }}"
                                                                    value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Saturday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Saturday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="SaturdayBusiness()"
                                                                        name="saturday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="saturday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="saturday_open"
                                                                    placeholder="{{ __('Opening Time') }}"
                                                                    value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="saturday_closing"
                                                                    placeholder="{{ __('Closing Time') }}"
                                                                    value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sunday -->
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <h2 class="page-title my-2">{{ __('Sunday') }}</h2>
                                                            <div class="mb-3">
                                                                <div class="form-label">{{ __('Closed') }}</div>
                                                                <label class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        onchange="SundayBusiness()" name="sunday_closed">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div id="sunday-business">
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="sunday_open"
                                                                    placeholder="{{ __('Opening Time') }}"
                                                                    value="09:00">
                                                            </div>
                                                            <div class="mb-3">
                                                                <input type="time" class="form-control"
                                                                    name="sunday_closing"
                                                                    placeholder="{{ __('Closing Time') }}"
                                                                    value="18:30">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right-Aligned Card Footer Containing Standalone Save Button -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
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
    <script>
        "use strict";

        function displayBusiness() {
            var disp = $('input[name="is_display"]:checked').length;
            $("#business-hrs").toggle(disp === 0);
        }

        function toggleBusiness(day, elementID) {
            var closed = $(`input[name="${day}_closed"]:checked`).length;
            $(`#${elementID}`).toggle(closed === 0);
        }

        $(document).ready(function() {
            $('input[name="is_display"]').on('change', displayBusiness);

            $('input[name$="_closed"]').on('change', function() {
                var day = $(this).attr('name').split('_')[0];
                toggleBusiness(day, `${day}-business`);
            });

            displayBusiness();
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(function(day) {
                toggleBusiness(day, `${day}-business`);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const alwaysOpenCheckbox = document.getElementById('always-open');
            const displayHrsCheckbox = document.getElementById('display-hrs');

            alwaysOpenCheckbox.addEventListener('change', function() {
                if (alwaysOpenCheckbox.checked) {
                    displayHrsCheckbox.checked = false;
                    var businessHours = document.getElementById('business-hrs');
                    businessHours.style.display = 'none';
                } else {
                    var businessHours = document.getElementById('business-hrs');
                    businessHours.style.display = 'flex';
                }
            });

            displayHrsCheckbox.addEventListener('change', function() {
                if (displayHrsCheckbox.checked) {
                    alwaysOpenCheckbox.checked = false;
                }
            });
        });

        function toggleDisplay(inputName, elementId) {
            "use strict";
            var isChecked = $('input[name="' + inputName + '"]:checked').length;
            if (isChecked == 0) {
                $(elementId).show();
            } else {
                $(elementId).hide();
            }
        }
    </script>
@endpush
