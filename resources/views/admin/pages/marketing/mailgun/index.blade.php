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
                            {{ __('Mailgun Configuration') }}
                        </h2>
                    </div>
                    <span class="mt-3">{{ __('How to configure Mailgun SMTP from the Mailgun documentation?') }} {!! __('<a href="https://docs.nativecode.in/vcard-saas-gobiz-digital-business-card-script-in-laravel/where-can-I-find-my-API-keys-and-SMTP-credentials" target="_blank">Click here</a>') !!}</span>
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
                        <form action="{{ route('admin.marketing.mailgun.update') }}" method="post" class="card">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Configuration') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    {{-- Mailgun Region --}}
                                    <div class="col-md-4 col-xl-6">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Mailgun Region') }}</div>
                                            <select class="form-control" id="mailgun_region" name="mailgun_region">
                                                <option value="us" {{ $config[95]->config_value == 'us' ? 'selected' : '' }}>{{ __('US') }}</option>
                                                <option value="eu" {{ $config[95]->config_value == 'eu' ? 'selected' : '' }}>{{ __('EU') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Mailgun API Key --}}
                                    <div class="col-md-4 col-xl-6">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Mailgun API Key') }}</div>
                                            <input type="text" class="form-control" id="mailgun_api_key" name="mailgun_api_key" value="{{ $config[58]->config_value }}" placeholder="{{ __('Mailgun API Key') }}" required>
                                        </div>
                                    </div>

                                    {{-- Mailgun From Email --}}
                                    <div class="col-md-4 col-xl-6">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Mailgun From Email') }}</div>
                                            <input type="text" class="form-control" id="mailgun_from_email" name="mailgun_from_email" value="{{ $config[59]->config_value }}" placeholder="{{ __('Mailgun From Email') }}" required>
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
        <!-- Footer -->
        @include('admin.includes.footer')
    </div>

    {{-- Custom JS --}}
    @section('scripts')
        <script>
            // Array of element IDs
            var elementSelectors = ['mailgun_region'];

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
        </script>   
    @endsection
@endsection