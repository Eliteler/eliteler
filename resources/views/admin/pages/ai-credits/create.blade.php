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
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.ai.credits.plans') }}" class="text-secondary text-decoration-none">
                                    {{ __('AI Credit Plans') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create Plan') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create AI Credit Plan') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Create a new AI credit plans with custom limits and pricing.') }}
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
                        <form action="{{ route('admin.save.ai.credits.plan') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 col-xl-12">
                                        <div class="row">
                                            {{-- Plan Name --}}
                                            <div class="col-md-12 col-xl-4">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan Name') }}</label>
                                                    <input type="text" class="form-control" name="plan_name"
                                                        value="{{ old('plan_name') }}"
                                                        placeholder="{{ __('Enter plan name') }}" minlength="3"
                                                        maxlength="255" required>
                                                </div>
                                            </div>
                                            {{-- Plan Description --}}
                                            <div class="col-md-12 col-xl-4">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan Description') }}</label>
                                                    <textarea class="form-control" name="plan_description" placeholder="{{ __('Enter a short description') }}"
                                                        minlength="10" maxlength="255" required>{{ old('plan_description') }}</textarea>
                                                </div>
                                            </div>
                                            {{-- Plan Price --}}
                                            <div class="col-md-12 col-xl-4">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Plan Price') }}</label>
                                                    <input type="number" class="form-control" name="plan_price"
                                                        value="{{ old('plan_price') }}"
                                                        placeholder="{{ __('Enter plan price') }}" min="0"
                                                        step="0.01" required>
                                                </div>
                                            </div>
                                            {{-- No of AI Credits --}}
                                            <div class="col-md-12 col-xl-4">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('No of AI Credit') }}</label>
                                                    <input type="number" class="form-control" name="no_of_ai_credits"
                                                        value="{{ old('no_of_ai_credits') }}"
                                                        placeholder="{{ __('Enter no. of AI Credit') }}" min="0"
                                                        step="1" required>
                                                </div>
                                            </div>

                                            {{-- Mobile App Plan Details --}}
                                            @if (is_dir(base_path('plugins/MobileAppAPI')))
                                                <h2 class="page-title my-3">
                                                    {{ __('In-App Purchase Details (Mobile App)') }}
                                                </h2>

                                                {{-- Product ID --}}
                                                <div class="col-md-12 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Product ID') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="in_app_product_id"
                                                            placeholder="{{ __('Product ID') }}" />
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center">
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
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element IDs
        var elementSelectors = ['plan_price'];

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
