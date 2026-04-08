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
                            <span class="me-1">{{ __($user_details->name) }}</span>
                            {{ __('Details') }}
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
                        <form action="{{ route('admin.update.customer') }}" method="post" class="card">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Customer Details') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            <input type="hidden" class="form-control" name="user_id"
                                                placeholder="{{ __('Customer ID') }}" value="{{ $user_details->user_id }}"
                                                readonly>
                                            {{-- Full Name --}}
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Full Name') }}</label>
                                                    <input type="text" class="form-control" name="full_name"
                                                        placeholder="{{ __('Full Name') }}"
                                                        value="{{ $user_details->name }}" required>
                                                </div>
                                            </div>

                                            <!-- Email -->
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Email') }} </label>
                                                    <input type="email " class="form-control" name="email"
                                                        placeholder="{{ __('Email') }}"
                                                        value="{{ $user_details->email }}" required>
                                                </div>
                                            </div>

                                            {{-- Status --}}
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('Status') }} </label>
                                                    <select class="form-control" name="status" id="status" required>
                                                        <option value="1"
                                                            {{ $user_details->status == 1 ? 'selected' : '' }}>
                                                            {{ __('Active') }}
                                                        </option>
                                                        <option value="0"
                                                            {{ $user_details->status == 0 ? 'selected' : '' }}>
                                                            {{ __('Inactive') }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Change Password --}}
                                            <h2 class="page-title my-3">
                                                {{ __('Change Password') }}
                                            </h2>
                                            <div class="col-md-4 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label required">{{ __('New Password') }} </label>
                                                    <input type="password" class="form-control" name="password"
                                                        placeholder="{{ __('New Password') }}">
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
                                                        <option value="1">{{ __('Yes') }}</option>
                                                        <option value="0" selected>{{ __('No') }}</option>
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
        var elementSelectors = ['status', 'welcome_email', 'reset_password'];

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
