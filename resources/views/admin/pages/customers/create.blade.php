@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }

        /* Custom styles scoped strictly to the password input container */
        .custom-password-group {
            border: 1px solid #DCE1E7 !important;
            /* Matches Tabler's default border color */
            border-radius: 8px !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            display: flex;
            align-items: center;
        }

        /* Dark mode overrides */
        [data-bs-theme="dark"] .custom-password-group {
            border: 1px solid #25384F !important;
        }

        /* Remove default individual borders and shadows inside this group */
        .custom-password-group .form-control {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding-right: 0 !important;
        }

        .custom-password-group .input-group-text {
            border: none !important;
            background: transparent !important;
            padding-left: 0.5rem !important;
            padding-right: 0.75rem !important;
            display: flex;
            align-items: center;
        }

        /* Eye icon button styling */
        .custom-password-group .btn-icon-eye {
            color: #626976;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .custom-password-group .btn-icon-eye:hover {
            color: #206bc4;
            /* Tabler hover blue */
        }

        /* Replicate Tabler's focus glow outline when input is selected */
        .custom-password-group:focus-within {
            border-color: #90b1f5 !important;
            box-shadow: 0 0 0 0.25rem rgba(32, 107, 196, 0.25) !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.customers', ['status' => 'all']) }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Customers') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.customers', ['status' => 'all']) }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Manage Customers') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create Customer') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Add a new customer to your platform.') }}
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
                    <div class="col-sm-6 col-lg-12">
                        <form action="{{ route('admin.save.customer') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row g-3">
                                            {{-- Full Name --}}
                                            <div class="col-md-12 col-lg-4">
                                                <div>
                                                    <label class="form-label required">{{ __('Full Name') }}</label>
                                                    <input type="text" class="form-control" name="full_name"
                                                        value="{{ old('full_name') }}"
                                                        placeholder="{{ __('e.g., John Doe') }}" required>
                                                </div>
                                            </div>
                                            {{-- Email --}}
                                            <div class="col-md-12 col-lg-4">
                                                <div>
                                                    <label class="form-label required">{{ __('Email') }} </label>
                                                    <input type="email" class="form-control" name="email"
                                                        value="{{ old('email') }}"
                                                        placeholder="{{ __('e.g., john@example.com') }}" required>
                                                </div>
                                            </div>

                                            {{-- Password --}}
                                            @php
                                                $randomPassword = Str::random(12);
                                            @endphp
                                            <div class="col-md-12 col-xl-6">
                                                <div>
                                                    <label class="form-label required">{{ __('Password') }} </label>
                                                    <div class="input-group input-group-flat custom-password-group">
                                                        <input type="text" class="form-control" name="password"
                                                            value="{{ $randomPassword }}" id="password"
                                                            placeholder="{{ __('Password') }}" autocomplete="new-password"
                                                            required>
                                                        <span class="input-group-text">
                                                            <a href="javascript:void(0)" class="btn-icon-eye"
                                                                onclick="showPassword('password'); return false;"
                                                                title="{{ __('Hide password') }}" data-bs-toggle="tooltip"
                                                                data-bs-placement="top">
                                                                <!-- Clean 'Eye Off' Icon (Password Hidden) -->
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-eye-off"
                                                                    width="24" height="24" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M10.584 10.587a2 2 0 0 0 2.829 2.829" />
                                                                    <path
                                                                        d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" />
                                                                    <path d="M3 3l18 18" />
                                                                </svg>
                                                            </a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Plan --}}
                                            <div class="col-md-12 col-lg-4">
                                                <div>
                                                    <label class="form-label required">{{ __('Plan') }}</label>
                                                    <select class="form-select" name="plan_id" id="plan_id" required>
                                                        <option value="">{{ __('Choose a plan') }}</option>
                                                        <option value="0" selected>{{ __('No Plan') }}</option>
                                                        @foreach ($plans as $plan)
                                                            <option value="{{ $plan->plan_id }}">{{ $plan->plan_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Status (Hidden field retained for backend context) --}}
                                            <div class="col-12 d-none">
                                                <select class="form-select" name="status" id="status" required>
                                                    <option value="1" selected>{{ __('Active') }}</option>
                                                    <option value="0">{{ __('Inactive') }}</option>
                                                </select>
                                            </div>

                                            {{-- Separator for Notification Preferences --}}
                                            <div class="col-12">
                                                <h3 class="card-title mt-2 mb-2">{{ __('Notification Preferences') }}</h3>
                                            </div>

                                            {{-- Checkbox Send Welcome Email --}}
                                            <div class="col-md-12">
                                                <div>
                                                    <label class="form-label">{{ __('Welcome Email') }}</label>
                                                    <label class="form-check form-switch mt-2">
                                                        <input type="hidden" name="welcome_email" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="welcome_email" id="welcome_email" value="1"
                                                            checked>
                                                        <span
                                                            class="form-check-label">{{ __('Send welcome email to customer') }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            {{-- Checkbox Send Password Reset Email --}}
                                            <div class="col-md-12">
                                                <div>
                                                    <label class="form-label">{{ __('Reset Password Email') }}</label>
                                                    <label class="form-check form-switch mt-2">
                                                        <input type="hidden" name="reset_password" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="reset_password" id="reset_password" value="1">
                                                        <span
                                                            class="form-check-label">{{ __('Send password reset email setup link') }}</span>
                                                    </label>
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
                                    {{ __('Create Customer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        // Array of element IDs to initialize with TomSelect
        var elementSelectors = ['plan_id'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
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

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                initializeTomSelectWithRequired(el);
            }
        });

        // Show/Hide password toggle function
        function showPassword(id) {
            "use strict";

            var passwordInput = document.getElementById(id);
            var toggleLink = document.querySelector(".btn-icon-eye");

            if (passwordInput && toggleLink) {

                // Password is currently visible
                if (passwordInput.type === "text") {
                    passwordInput.type = "password";

                    // Eye Icon (Show Password)
                    toggleLink.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
            </svg>`;

                    toggleLink.setAttribute("title", "{{ __('Show password') }}");
                    toggleLink.setAttribute("data-bs-original-title", "{{ __('Show password') }}");

                } else {
                    passwordInput.type = "text";

                    // Eye Off Icon (Hide Password)
                    toggleLink.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M10.584 10.587a2 2 0 0 0 2.829 2.829" />
                <path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" />
                <path d="M3 3l18 18" />
            </svg>`;

                    toggleLink.setAttribute("title", "{{ __('Hide password') }}");
                    toggleLink.setAttribute("data-bs-original-title", "{{ __('Hide password') }}");
                }

                // Refresh Bootstrap tooltip
                var tooltip = bootstrap.Tooltip.getInstance(toggleLink);
                if (tooltip) {
                    tooltip.dispose();
                }
                new bootstrap.Tooltip(toggleLink);
            }
        }
    </script>
@endsection
