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
                        {{ __('Update') }}
                    </div>
                    <h2 class="page-title">
                        <span class="me-1">{{ __($currency_details->name) }}</span>
                        {{ __('Details') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid">
            {{-- Failed --}}
            @if(Session::has("failed"))
            <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('failed')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            {{-- Success --}}
            @if(Session::has("success"))
            <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('success')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('admin.update.currency') }}" method="post" class="card">
                        @csrf
                        <div class="card-header">
                            <h4 class="page-title">{{ __('Currency Details') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <input type="hidden" class="form-control" name="id"
                                            placeholder="{{ __('Currency ID') }}" value="{{ $currency_details->id }}"
                                            readonly>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Name') }}</label>
                                                <input type="text" class="form-control" name="name"
                                                    placeholder="{{ __('Name') }}"
                                                    value="{{ $currency_details->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('ISO Code') }}</label>
                                                <input type="text" class="form-control" name="iso_code"
                                                    placeholder="{{ __('ISO Code') }}"
                                                    value="{{ $currency_details->iso_code }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Symbol') }}</label>
                                                <input type="text" class="form-control" name="symbol"
                                                    placeholder="{{ __('Symbol') }}"
                                                    value="{{ $currency_details->symbol }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Symbol First') }}</label>
                                                <select name="symbol_first" id="symbol_first" class="form-select"
                                                    required>
                                                    <option value="false" {{ $currency_details->symbol_first == 'false' ? 'selected' : '' }}>{{ __('No') }}</option>
                                                    <option value="true" {{ $currency_details->symbol_first == 'true' ? 'selected' : '' }}>{{ __('Yes') }}</option>
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
                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
                option: function(data, escape) {
                    if (data.customProperties) {
                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
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