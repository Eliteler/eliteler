@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'Email Service (SMTP)'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('Email Service (SMTP)') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your outgoing mail server parameters, authentication drivers, encryption keys, and email validation settings.') }}
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
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
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
                    <div class="col-12 col-md-6">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form id="smtpForm" action="{{ route('admin.smtp_settings.update') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 border-bottom pb-2 mb-2">
                                        <h3 class="card-title fw-bold m-0">{{ __('SMTP Credentials') }}</h3>
                                    </div>

                                    {{-- Sender Name --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Sender Name') }}</label>
                                            <input type="text" class="form-control" name="mail_sender"
                                                value="{{ $email_configuration['name'] }}" maxlength="50"
                                                placeholder="{{ __('e.g., Support Team') }}" required>
                                        </div>
                                    </div>

                                    {{-- Sender Email Address --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Sender Email Address') }}</label>
                                            <input type="email" class="form-control" name="mail_address"
                                                value="{{ $email_configuration['address'] }}"
                                                placeholder="{{ __('e.g., support@yourdomain.com') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailer Driver --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Mailer Driver') }}</label>
                                            <input type="text" class="form-control" name="mail_driver"
                                                value="{{ $email_configuration['driver'] }}"
                                                placeholder="{{ __('e.g., smtp') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailer Host --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Mailer Host') }}</label>
                                            <input type="text" class="form-control" name="mail_host"
                                                value="{{ $email_configuration['host'] }}"
                                                placeholder="{{ __('e.g., smtp.mailgun.org') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailer Port --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Mailer Port') }}</label>
                                            <input type="number" class="form-control" name="mail_port" maxlength="4"
                                                value="{{ $email_configuration['port'] }}"
                                                placeholder="{{ __('e.g., 587') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailer Encryption --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required"
                                                for="mail_encryption">{{ __('Mailer Encryption') }}</label>
                                            <select name="mail_encryption" id="mail_encryption" class="form-select"
                                                required>
                                                <option value="" disabled>{{ __('Choose encryption') }}</option>
                                                <option value="tls"
                                                    {{ $email_configuration['encryption'] == 'tls' ? 'selected' : '' }}>
                                                    {{ __('TLS/STARTTLS') }}
                                                </option>
                                                <option value="ssl"
                                                    {{ $email_configuration['encryption'] == 'ssl' ? 'selected' : '' }}>
                                                    {{ __('SSL') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Mailer Username --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Mailer Username') }}</label>
                                            <input type="text" class="form-control" name="mail_username"
                                                value="{{ $email_configuration['username'] }}"
                                                placeholder="{{ __('Enter SMTP username') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailer Password --}}
                                    <div class="col-12 col-md-6">
                                        <label class="form-label required">{{ __('Mailer Password') }}</label>

                                        <div class="input-group input-group-flat">
                                            <input type="password" class="form-control" name="mail_password"
                                                id="mail_password" value="{{ $email_configuration['password'] }}"
                                                maxlength="100" placeholder="{{ __('Enter SMTP password') }}" required>

                                            <span class="input-group-text cursor-pointer" role="button"
                                                onclick="togglePassword('mail_password')">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-eye" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <circle cx="12" cy="12" r="2" />
                                                    <path
                                                        d="M22 12c-2.667 4 -6 6 -10 6s-7.333 -2 -10 -6c2.667 -4 6 -6 10 -6s7.333 2 10 6" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Test Mail Button --}}
                                    <div class="col-12 col-md-6 d-flex align-items-end">
                                        <div class="mb-3 w-100">
                                            <button type="button"
                                                class="btn btn-outline-primary btn-md w-100 d-inline-flex align-items-center justify-content-center"
                                                onclick="submitTestEmail()">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-send me-2" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M10 14l11 -11" />
                                                    <path
                                                        d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                                </svg>
                                                {{ __('Send Test Email') }}
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Customer Email Verification System --}}
                                    <div class="col-12 mt-3 pt-3 border-top">
                                        <h3 class="card-title fw-bold m-0 mb-3">
                                            {{ __('Customer Email Verification System') }}</h3>
                                        <div class="row">
                                            <div class="col-12 col-md-6 col-lg-5">
                                                <div class="mb-3">
                                                    <label class="form-label required"
                                                        for="disable_user_email_verification">
                                                        {{ __('Require customer email verification?') }}
                                                    </label>
                                                    <select name="disable_user_email_verification"
                                                        id="disable_user_email_verification" class="form-select" required>
                                                        <option value="" disabled>{{ __('Choose an option') }}
                                                        </option>
                                                        <option value="1"
                                                            {{ $config[43]->config_value == '1' ? 'selected' : '' }}>
                                                            {{ __('Yes') }}
                                                        </option>
                                                        <option value="0"
                                                            {{ $config[43]->config_value == '0' ? 'selected' : '' }}>
                                                            {{ __('No') }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    {{ __('Activating this parameter triggers automated token emails to verify customer addresses immediately upon initial dashboard registration.') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <!-- Device Floppy Icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                        <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                        <path d="M14 4l0 4h-6l0 -4" />
                                    </svg>
                                    {{ __('Save') }}
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
    <!-- Script asset loaded inside correct scripts section -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <script type="text/javascript">
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {
            var elementSelectors = ['mail_encryption', 'disable_user_email_verification'];

            function initializeTomSelectWithRequired(el) {
                new TomSelect(el, {
                    copyClassesToDropdown: false,
                    dropdownClass: 'dropdown-menu ts-dropdown',
                    optionClass: 'dropdown-item',
                    controlInput: '<input>',
                    placeholder: "{{ __('Choose an option') }}",
                    maxOptions: null,
                });
            }

            elementSelectors.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    initializeTomSelectWithRequired(el);
                }
            });
        });

        // Handle test connection execution route update safely
        function submitTestEmail() {
            "use strict";
            let form = document.getElementById('smtpForm');
            form.action = "{{ route('admin.plugin.test.email') }}";
            form.submit();
        }

        // Toggle password visibility
        function togglePassword(id) {
            "use strict";

            const input = document.getElementById(id);
            const toggle = input.parentElement.querySelector(".input-group-text");

            if (input.type === "password") {
                input.type = "text";
                toggle.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off"
                width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 3l18 18"/>
                <path d="M10.584 10.587a2 2 0 0 0 2.828 2.828"/>
                <path d="M9.363 5.365a9.466 9.466 0 0 1 2.637 -.365c4 0 7.333 2 10 6a13.16 13.16 0 0 1 -1.67 2.388"/>
                <path d="M6.53 6.53a13.16 13.16 0 0 0 -4.53 5.47c2.667 4 6 6 10 6a9.74 9.74 0 0 0 5.47 -1.53"/>
            </svg>`;
            } else {
                input.type = "password";
                toggle.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye"
                width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" fill="none" stroke-linecap="round"
                stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <circle cx="12" cy="12" r="2"/>
                <path d="M22 12c-2.667 4 -6 6 -10 6s-7.333 -2 -10 -6c2.667 -4 6 -6 10 -6s7.333 2 10 6"/>
            </svg>`;
            }
        }
    </script>
@endsection
