@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('title', __('Edit Marketing Group'))

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
                                <a href="{{ route('admin.marketing.campaigns') }}" class="text-secondary text-decoration-none">
                                    {{ __('Email Marketing') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.marketing.groups') }}" class="text-secondary text-decoration-none">
                                    {{ __('Groups') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __($group->group_name) }}</span>
                            {{ __('Details') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Update a customer group to send targeted email campaigns.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
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

                <div class="row row-cards">
                    <div class="col-md-6 col-sm-12">
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.marketing.groups.update', $group->group_id) }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 col-lg-6">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Group Name') }}</div>
                                                <!-- Added placeholder to match Create page -->
                                                <input type="text" class="form-control" id="group_name" name="group_name"
                                                    placeholder="{{ __('Enter the group name') }}"
                                                    value="{{ $group->group_name }}" required>
                                            </div>
                                        </div>

                                        <!-- Group Description -->
                                        <div class="col-sm-12 col-lg-6">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Group Description') }}</div>
                                                <!-- Added placeholder to match Create page -->
                                                <input type="text" class="form-control" id="group_desc" name="group_desc"
                                                    placeholder="{{ __('Enter a short description') }}"
                                                    value="{{ $group->group_desc }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!---- Emails (Customers) ---->
                                    @if (count($marketingCustomers) > 0)
                                        <div class="col-sm-12 col-lg-12">
                                            <div class="mb-3">
                                                <!-- Updated label to "Customers" and helper description to "Select one or more..." -->
                                                <div class="form-label required">{{ __('Customers') }}</div>
                                                <select class="form-control" id="emails" name="emails[]" multiple>
                                                    <option value="all" id="select-all-option">
                                                        {{ __('Select all customers') }}</option>
                                                    @foreach ($marketingCustomers as $customer)
                                                        <option value="{{ $customer->email }}"
                                                            @if (in_array($customer->email, json_decode($group->emails, true))) selected @endif>
                                                            {{ $customer->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small
                                                    class="form-text text-muted mt-1">{{ __('Select one or more customers to include in this group.') }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-sm-12 col-lg-12">
                                            <div class="mb-3">
                                                {{-- Heading --}}
                                                <div class="form-label">{{ __('Customers') }}</div>
                                                <div class="form-text fw-bold">{{ __('No customers found') }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Submit --}}
                                    @if (Session::has('success'))
                                        <div class="alert alert-important alert-success alert-dismissible mb-2"
                                            role="alert">
                                            <div class="d-flex">
                                                <div>
                                                    {{ Session::get('success') }}
                                                </div>
                                            </div>

                                            {{-- Redirect --}}
                                            <a class="btn-close btn-close-white" data-bs-dismiss="alert"
                                                aria-label="close"></a>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center {{ count($marketingCustomers) > 0 ? '' : 'disabled' }}">
                                        <!-- Replaced plain save text button with custom layout, Floppy Disk icon, and Save text -->
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
    </div>

    <!---- Footer ---->
    @include('admin.includes.footer')
@endsection

<!-- Custom JS -->
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element IDs
        var elementSelectors = ['emails'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                 plugins: ['remove_button'],
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
    {{-- Select all option --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";
            const emailsSelect = document.getElementById("emails");
            const selectAllOption = document.getElementById("select-all-option");

            if (emailsSelect) {
                emailsSelect.addEventListener("change", function() {
                    const selectedOptions = Array.from(emailsSelect.selectedOptions);
                    const allSelected = selectedOptions.some(option => option.value === "all");

                    if (allSelected) {
                        // If "All" is selected, select all other options
                        Array.from(emailsSelect.options).forEach(option => option.selected = true);
                    } else {
                        // If "All" is unselected, deselect all options
                        const allOptionSelected = Array.from(selectedOptions).some(option => option
                            .value !== "all");
                        if (selectAllOption) {
                            selectAllOption.selected = !allOptionSelected;
                        }
                    }
                });
            }
        });
    </script>
@endsection
