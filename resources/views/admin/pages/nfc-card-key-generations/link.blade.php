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
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.designs') }}" class="text-secondary text-decoration-none">
                                    {{ __('NFC Sales') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.key.generations') }}" class="text-secondary text-decoration-none">
                                    {{ __('Activation Keys') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Link NFC Card') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Link NFC Card') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('Select a registered customer and allocate one of their business cards to link with this NFC key.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
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

                <div class="row row-deck row-cards">
                    <div class="col-md-6 col-sm-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.update.link.key') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row g-3">
                                                {{-- NFC Card Key ID --}}
                                                <input type="hidden" name="key_id" id="key_id"
                                                    value="{{ $nfcCardKey->nfc_card_key_id }}">

                                                {{-- Customers select dropdown --}}
                                                <div class="col-md-12 col-lg-6">
                                                    <div>
                                                        <label for="customer"
                                                            class="form-label required">{{ __('Select a customer') }}</label>
                                                        <select class="form-select customer" name="customer" id="customer"
                                                            onchange="getCustomerCards(this.value)" required>
                                                            <option value="" selected disabled>
                                                                {{ __('Select a customer') }}
                                                            </option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->user_id }}">
                                                                    {{ $customer->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Cards select dropdown --}}
                                                <div class="col-md-12 col-lg-6">
                                                    <div>
                                                        <label for="card_id"
                                                            class="form-label required">{{ __('Select a card') }}</label>
                                                        <select class="form-select cards" name="card_id" id="card_id"
                                                            required>
                                                            <option value="" selected disabled>
                                                                {{ __('Select a card') }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Simplified Right-Aligned Card Footer (Without Cancel Button) --}}
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <!-- Device Floppy Icon -->
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
                                        {{ __('Link') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element selectors
        var elementSelectors = ['customer'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
            "use strict";

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
            "use strict";

            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });

        // Get customer cards
        function getCustomerCards(customerId) {
            "use strict";

            // Get the customer ID
            var customerId = document.getElementById("customer").value;

            // CSRF token
            var token = document.querySelector('meta[name="csrf-token"]').content;

            // Ajax request to get the customer cards
            $.ajax({
                url: "{{ route('admin.get.customer.cards') }}",
                headers: {
                    'X-CSRF-TOKEN': token
                },
                method: "POST",
                data: {
                    customerId: customerId
                },
                success: function(response) {
                    // Update the cards dropdown
                    $(".cards").empty();
                    // Check response count
                    if (response.length == 0) {
                        // No cards found
                        $(".cards").append('<option value="" selected disabled>No cards found</option>');
                    } else {
                        // Cards found
                        $(".cards").append('<option value="" selected disabled>Select a card</option>');
                        // Loop through each card
                        response.forEach(function(card) {
                            $(".cards").append('<option value="' + card.card_id + '">' + card.title +
                                '</option>');
                        });

                        // Set tom select options
                        initializeTomSelectWithRequired(document.getElementById("card_id"));
                    }
                }
            });
        }
    </script>
@endsection
