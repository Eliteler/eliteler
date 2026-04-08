@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
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
                            {{ __('Create Customer') }}
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

                <div class="row row-deck row-cards">
                    <div class="col-sm-12 col-lg-12">
                        <form action="{{ route('admin.save.customer') }}" method="post" class="card">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Customer Details') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            {{-- Full Name --}}
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Full Name') }}</label>
                                                    <input type="text" class="form-control" name="full_name"
                                                        value="{{ old('full_name') }}" placeholder="{{ __('Full Name') }}"
                                                        required>
                                                </div>
                                            </div>
                                            {{-- Email --}}
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Email') }} </label>
                                                    <input type="email" class="form-control" name="email"
                                                        value="{{ old('email') }}" placeholder="{{ __('Email') }}"
                                                        required>
                                                </div>
                                            </div>

                                            {{-- Password --}}
                                            @php
                                                $randomPassword = Str::random(12);
                                            @endphp
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Password') }} </label>
                                                    <div class="row g-2">
                                                        <div class="col">
                                                            <input type="password" class="form-control" name="password"
                                                                value="{{ $randomPassword }}" id="password"
                                                                placeholder="{{ __('Password') }}" required>
                                                        </div>
                                                        <div class="col-auto">
                                                            <a href="#"
                                                                onclick="showPassword('password'); return false;"
                                                                class="btn btn-2 btn-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-password">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M12 10v4" />
                                                                    <path d="M10 13l4 -2" />
                                                                    <path d="M10 11l4 2" />
                                                                    <path d="M5 10v4" />
                                                                    <path d="M3 13l4 -2" />
                                                                    <path d="M3 11l4 2" />
                                                                    <path d="M19 10v4" />
                                                                    <path d="M17 13l4 -2" />
                                                                    <path d="M17 11l4 2" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Status --}}
                                            <div class="col-md-6 col-xl-6 d-none">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Status') }}</label>
                                                    <select class="form-select" name="status" id="status" required>
                                                        <option value="1">{{ __('Active') }}</option>
                                                        <option value="0">{{ __('Inactive') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Plan --}}
                                            <h2 class="page-title my-3">
                                                {{ __('Plan') }}
                                            </h2>
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan') }}</label>
                                                    <select class="form-select" name="plan_id" id="plan_id" required>
                                                        <option value="">{{ __('Choose a plan') }}</option>
                                                        <option value="0">{{ __('No Plan') }}</option>
                                                        @foreach ($plans as $plan)
                                                            <option value="{{ $plan->plan_id }}">{{ $plan->plan_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Email --}}
                                            <h2 class="page-title my-3">
                                                {{ __('Emails') }}
                                            </h2>

                                            {{-- Checkbox Welcome Email --}}
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Welcome Email') }}</label>
                                                    <select class="form-select" name="welcome_email" id="welcome_email"
                                                        required>
                                                        <option value="1" selected>{{ __('Yes') }}</option>
                                                        <option value="0">{{ __('No') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Checkbox Reset Password --}}
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label
                                                        class="form-label required">{{ __('Reset Password Email') }}</label>
                                                    <select class="form-select" name="reset_password" id="reset_password"
                                                        required>
                                                        <option value="1">{{ __('Yes') }}</option>
                                                        <option value="0" selected>{{ __('No') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript">
        // Array of element IDs and values
        var elementSelectors = ['plan_id', 'status', 'welcome_email', 'reset_password'];

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
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });

        // Show password
        function showPassword(id) {
            "use strict";
            var password = document.getElementById(id);
            if (password.type === "password") {
                password.type = "text";

                // Change the icon
                var eyeIcon = document.querySelector('.btn-icon');
                eyeIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
            </svg>`;
            } else {
                password.type = "password";

                // Change the icon
                var eyeIcon = document.querySelector('.btn-icon');
                eyeIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye-closed">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M21 9c-2.4 2.667 -5.4 4 -9 4c-3.6 0 -6.6 -1.333 -9 -4" />
                <path d="M3 15l2.5 -3.8" />
                <path d="M21 14.976l-2.492 -3.776" />
                <path d="M9 17l.5 -4" />
                <path d="M15 17l-.5 -4" />
            </svg>`;
            }
        }
    </script>
@endsection
@endsection
