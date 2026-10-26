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
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.currencies') }}" class="text-secondary text-decoration-none">
                                    {{ __('Currencies') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __($currency_details->name) }}</span>
                            {{ __('Details') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Manage currency information and standard formatting symbols.') }}
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
                    <div class="col-md-6 col-sm-12">
                        <form action="{{ route('admin.update.currency') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="id"
                                                placeholder="{{ __('Currency ID') }}" value="{{ $currency_details->id }}"
                                                readonly>
                                            {{-- Priority --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Priority') }}</label>
                                                    <input type="number" class="form-control" name="priority"
                                                        placeholder="{{ __('Enter priority. E.g., 100') }}"
                                                        value="{{ $currency_details->priority }}">
                                                </div>
                                            </div>
                                            {{-- Name --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Name') }}</label>
                                                    <input type="text" class="form-control" name="name"
                                                        placeholder="{{ __('Enter currency name') }}"
                                                        value="{{ $currency_details->name }}" required>
                                                </div>
                                            </div>
                                            {{-- ISO Code --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('ISO Code') }}</label>
                                                    <input type="text" class="form-control" name="iso_code"
                                                        placeholder="{{ __('Enter ISO code') }}"
                                                        value="{{ $currency_details->iso_code }}" required>
                                                </div>
                                            </div>
                                            {{-- Symbol --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Symbol') }}</label>
                                                    <input type="text" class="form-control" name="symbol"
                                                        placeholder="{{ __('Enter symbol') }}"
                                                        value="{{ $currency_details->symbol }}" required>
                                                </div>
                                            </div>
                                            {{-- Subunit --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Subunit') }}</label>
                                                    <input type="text" class="form-control" name="subunit"
                                                        placeholder="{{ __('Enter subunit. E.g., Centime') }}"
                                                        value="{{ $currency_details->subunit }}">
                                                </div>
                                            </div>
                                            {{-- Subunit to unit --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Subunit to unit') }}</label>
                                                    <input type="text" class="form-control" name="subunit_to_unit"
                                                        placeholder="{{ __('Enter subunit to unit, E.g., 100') }}"
                                                        value="{{ $currency_details->subunit_to_unit }}">
                                                </div>
                                            </div>
                                            {{-- Symbol First --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Symbol First') }}</label>
                                                    <select name="symbol_first" id="symbol_first" class="form-select"
                                                        required>
                                                        <option value="false"
                                                            {{ $currency_details->symbol_first == 'false' ? 'selected' : '' }}>
                                                            {{ __('No') }}</option>
                                                        <option value="true"
                                                            {{ $currency_details->symbol_first == 'true' ? 'selected' : '' }}>
                                                            {{ __('Yes') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            {{-- HTML entity --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('HTML entity') }}</label>
                                                    <input type="text" class="form-control" name="html_entity"
                                                        placeholder="{{ __('Enter HTML entity. E.g., &euro;') }}"
                                                        value="{{ $currency_details->html_entity }}">
                                                </div>
                                            </div>
                                            {{-- Decimal mark --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Decimal mark') }}</label>
                                                    <input type="text" class="form-control" name="decimal_mark"
                                                        placeholder="{{ __('Enter decimal places. E.g., ,') }}"
                                                        value="{{ $currency_details->decimal_mark }}">
                                                </div>
                                            </div>
                                            {{-- Thousand separator --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label
                                                        class="form-label">{{ __('Thousand separator') }}</label>
                                                    <input type="text" class="form-control" name="thousand_separator"
                                                        placeholder="{{ __('Enter thousand separator. E.g., .') }}"
                                                        value="{{ $currency_details->thousand_separator }}">
                                                </div>
                                            </div>
                                            {{-- Iso numberic --}}
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Iso numberic') }}</label>
                                                    <input type="text" class="form-control" name="iso_numeric"
                                                        placeholder="{{ __('Enter iso numeric. E.g., 952') }}"
                                                        value="{{ $currency_details->iso_numeric }}">
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
        var elementSelectors = ['symbol_first'];

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
