@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
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
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.referrals') }}" class="text-secondary text-decoration-none">
                                    {{ __('Referral Program') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Configuration') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Configuration') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Configure referral commissions and withdrawal rules.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
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
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.update.referral.system.configuration') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Referral Commission Type --}}
                                        <div class="col-md-12 col-lg-4">
                                            <div>
                                                <label for="referral_commission_type"
                                                    class="form-label required">{{ __('Type') }}</label>
                                                <select class="form-select" name="referral_commission_type"
                                                    id="referral_commission_type" required>
                                                    <option value="" selected disabled>{{ __('Select Type') }}
                                                    </option>
                                                    <option value="0"
                                                        {{ old('referral_commission_type', $config[81]->config_value) == '0' ? 'selected' : '' }}>
                                                        {{ __('Percentage') }}</option>
                                                    <option value="1"
                                                        {{ old('referral_commission_type', $config[81]->config_value) == '1' ? 'selected' : '' }}>
                                                        {{ __('Fixed Amount') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Referral Commission --}}
                                        <div class="col-md-12 col-lg-4">
                                            <div>
                                                <label class="form-label required">{{ __('Commission') }}</label>
                                                <input type="number" class="form-control" name="referral_commission_amount"
                                                    id="referral_commission_amount"
                                                    value="{{ old('referral_commission_amount', $config[82]->config_value) }}"
                                                    placeholder="{{ __('E.g. 10') }}" min="0" max step="0.01"
                                                    required>
                                            </div>
                                        </div>

                                        {{-- Minimum Withdrawal Amount --}}
                                        <div class="col-md-12 col-lg-4">
                                            <div>
                                                <label class="form-label required">{{ __('Minimum Withdrawal') }}</label>
                                                <input type="number" class="form-control" name="minimum_withdrawal_amount"
                                                    value="{{ old('minimum_withdrawal_amount', $config[83]->config_value) }}"
                                                    placeholder="{{ __('E.g. 50') }}" min="0" step="0.01" max="1000000" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Simplified Footer with standard device-floppy save button (No Cancel Button) -->
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
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom scripts --}}
@section('scripts')
    <script>
        $(document).ready(function() {

            function updateCommissionMax() {
                const type = $('#referral_commission_type').val();
                const $amount = $('#referral_commission_amount');

                if (type == '0') {
                    // Percentage
                    $amount.attr({
                        max: 100,
                        placeholder: 'E.g. 10'
                    });

                    if (parseFloat($amount.val()) > 100) {
                        $amount.val(100);
                    }
                } else if (type == '1') {
                    // Fixed Amount
                    $amount.attr({
                        max: 1000000,
                        placeholder: 'E.g. 1000'
                    });

                    if (parseFloat($amount.val()) > 1000000) {
                        $amount.val(1000000);
                    }
                }
            }

            // Initial load
            updateCommissionMax();

            // On change
            $('#referral_commission_type').on('change', function() {
                updateCommissionMax();
            });

        });

        // Array of element IDs
        var elementSelectors = ['referral_commission_type'];

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
