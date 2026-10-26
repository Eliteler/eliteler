@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        [data-bs-theme="dark"] .update-message {
            color: #fff !important;
        }

        .form-control+.form-hint {
            margin-top: 0.3rem !important;
        }

        @media (min-width: 767px) {
            .w-md-50 {
                width: 50% !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Software Updates') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Software Updates') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Verify your Envato license and install the latest platform version upgrades.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">

                {{-- Alert Container --}}
                <div id="alert-container"></div>

                <div class="row row-cards">
                    <!-- Left: Update Check -->
                    <div class="col-lg-8">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form action="{{ route('admin.check.update') }}" method="post"
                            class="card card-sm border-0 shadow-sm">
                            @csrf

                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <span class="avatar bg-blue-lt me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h3 class="mb-0">{{ __('Software Updates') }}</h3>
                                        <div class="text-muted small">
                                            {{ __('Verify license and install the latest version') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    {{-- Purchase Code --}}
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label required">
                                            {{ __('Envato Purchase Code') }}
                                        </label>

                                        <input type="text" name="purchase_code" class="form-control"
                                            placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX" value="{{ $purchase_code }}"
                                            required>

                                        <small class="form-hint">
                                            <a href="https://help.market.envato.com/hc/en-us/articles/202822600"
                                                class="text-muted fs-5" rel="noopener noreferrer" target="_blank">
                                                {{ __('Where is my purchase code?') }}
                                            </a>
                                        </small>
                                    </div>

                                    {{-- Email address --}}
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label required">
                                            {{ __('Email address') }}
                                        </label>

                                        <input type="email" name="email" class="form-control"
                                            placeholder="your@email.com" value="{{ $email }}" required>

                                        <small class="form-hint fs-5">
                                            {{ __('Required for important updates') }}
                                        </small>
                                    </div>

                                    <div class="col-12 d-flex">
                                        {{-- Feature Request --}}
                                        <a href="https://gobiz.feednote.io?ref={{ urlencode(config('app.url')) }}&size=source"
                                            target="_blank" class="btn " title="{{ __('Eliteler Future Request') }}">
                                            {{ __('Feature Request') }}
                                        </a>

                                        <button type="submit" class="btn btn-primary px-5 ms-auto">
                                            {{ __('Check for update') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- Support status --}}
                        @if (!empty($response['support_status_message']))
                            <div class="mt-3">
                                {!! __($response['support_status_message']) !!}
                            </div>
                        @endif

                        {{-- Update result --}}
                        @if (isset($response) && $response['message'])
                            <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                            <div class="card mt-4 border-success border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="text-muted small">{{ __('Latest Version') }}</div>
                                            <div class="h1 mb-0">{{ $response['version'] }}</div>
                                        </div>
                                        <span class="badge bg-success text-white fs-6">
                                            {{ __('Stable') }}
                                        </span>
                                    </div>

                                    <p class="h4 mb-3">{{ $response['message'] }}</p>

                                    @if ($response['update'])
                                        <div class="font-weight-bold pb-3">
                                            {!! $response['notes'] !!}
                                        </div>

                                        <input type="hidden" name="app_version" id="app_version"
                                            value="{{ $response['version'] }}">
                                        <button type="submit" class="btn btn-success" id="updateCode">
                                            {{ __('Install Update') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Info & Support -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-3">
                            @if (!isset($response))
                                <!-- Piracy warning (Upgraded to borderless with shadowed styles) -->
                                <div class="card-body text-center">
                                    <a href="https://nativecode.in/products/gobiz-saas?ref={{ urlencode(config('app.url')) }}"
                                        rel="noopener noreferrer" target="_blank">
                                        <img src="{{ asset('img/piracy.png') }}" class="img-fluid rounded mb-3"
                                            alt="Piracy Warning">
                                    </a>
                                    <!-- Support renewal (Upgraded to borderless with shadowed styles) -->
                                    <a href="https://store.nativecode.in/checkout/buy/0f1f87da-5adc-443d-947f-17db72d9f3a2?ref={{ urlencode(config('app.url')) }}"
                                        target="_blank">
                                        <img src="{{ asset('img/in-extended-license.png') }}"
                                            class="img-fluid rounded mb-3" alt="Support">
                                    </a>

                                    @if (isset($response) && $response['license'] === 'Regular License')
                                        <!-- Regular License upgrade (Upgraded to borderless with shadowed styles) -->
                                        <div class="text-center">
                                            <img src="{{ asset('img/upgrade-to-extended-license.png') }}"
                                                class="img-fluid rounded mb-3" alt="Upgrade License">

                                            <a href="https://nativecode.in/products/gobiz-saas?ref={{ urlencode(config('app.url')) }}"
                                                rel="noopener noreferrer" target="_blank" class="btn btn-sm btn-success">
                                                {{ __('Upgrade to Extended License') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    <!-- Email not fillable modal -->
    <div class="modal modal-bg fade" id="emailNotFillableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3 class="text-uppercase mb-3">{{ __('Email address required') }}</h3>
                    <p>{{ __('Please fill the email address.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    {{-- Pass Laravel session messages to JS --}}
    <script>
        window.Laravel = {
            @if (Session::has('success'))
                success: @json(Session::get('success')),
            @endif

            @if (Session::has('failed'))
                failed: @json(Session::get('failed')),
            @endif
        };
    </script>

    {{-- JS --}}
    <script>
        'use strict';

        $(document).ready(function() {

            // Function to render Bootstrap alert
            function showAlert(type, message) {
                if (!message) return;

                var alertDiv = `
            <div class="alert alert-important alert-${type} alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>${message}</div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        `;

                // Append to alert container
                $('#alert-container').prepend(alertDiv);
            }

            // Show Laravel session messages on page load
            if (window.Laravel?.success) {
                showAlert('success', window.Laravel.success);
            }

            if (window.Laravel?.failed) {
                showAlert('danger', window.Laravel.failed);
            }

            // Handle update/install button click
            $('#updateCode').on('click', function() {
                var $btn = $(this);
                var appVersion = $('#app_version').val().trim();
                var email = $('input[name="email"]').val().trim();
                var purchaseCode = $('input[name="purchase_code"]').val().trim();

                // Validate email and required fields
                if (!email || !appVersion) {
                    var modalEl = document.getElementById('emailNotFillableModal');
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                    modal.show();

                    setTimeout(function() {
                        modal.hide();
                    }, 3000);

                    return; // Stop execution here
                }

                // Disable button and show updating text
                $btn.html('{{ __('Updating...') }}').prop('disabled', true);

                // Show loader
                showLoader();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('admin.update.code') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        app_version: appVersion,
                        email: email,
                    },
                    success: function(data) {
                        $btn.prop('disabled', false).html('{{ __('Install') }}');
                        hideLoader();

                        if (data.success) {
                            showAlert('success', data.message ||
                                '{{ __('Installed successfully!') }}');
                            setTimeout(function() {
                                window.location.href = '{{ route('admin.check') }}';
                            }, 1500);
                        } else {
                            showAlert('danger', data.message || '{{ __('Update failed!') }}');
                        }
                    },
                    error: function(xhr) {
                        hideLoader();
                        $btn.prop('disabled', false).html('{{ __('Install') }}');

                        var message = (xhr.responseJSON && xhr.responseJSON.message) ?
                            xhr.responseJSON.message :
                            '{{ __('Something went wrong. Please try again.') }}';

                        showAlert('danger', message);
                    }
                });
            });
        });

        // Show the page loader
        function showLoader() {
            "use strict";

            $('body').append(`
                <div class="nativecode-loader" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255,255,255,0.7);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }

        // Hide all page loaders
        function hideLoader() {
            "use strict";

            $('.nativecode-loader').hide(); // hides all loaders with this class
        }
    </script>
@endsection
