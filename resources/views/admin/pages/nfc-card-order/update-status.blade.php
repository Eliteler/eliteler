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
                                <a href="{{ route('admin.orders') }}" class="text-secondary text-decoration-none">
                                    {{ __('Orders') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update Status') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Update Status & Tracking Details') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('Update the delivery status, tracking details, and courier information for this order.') }}
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
                            <form action="{{ route('admin.updated.order') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row g-3">
                                                {{-- Order ID --}}
                                                <input type="hidden" name="order_id" id="order_id"
                                                    value="{{ $order->nfc_card_order_id }}">

                                                {{-- Delivery Status --}}
                                                <div class="col-md-12 col-lg-4">
                                                    <div>
                                                        <label for="status"
                                                            class="form-label required">{{ __('Delivery Status') }}</label>
                                                        <select class="form-select status" name="status" id="status"
                                                            required>
                                                            <option selected disabled value="">
                                                                {{ __('Choose a delivery status') }}</option>
                                                            <option value="pending"
                                                                {{ $order->order_status == 'pending' ? 'selected' : '' }}>
                                                                {{ __('Pending') }}</option>
                                                            <option value="processing"
                                                                {{ $order->order_status == 'processing' ? 'selected' : '' }}>
                                                                {{ __('Processing') }}</option>
                                                            <option value="hold"
                                                                {{ $order->order_status == 'hold' ? 'selected' : '' }}>
                                                                {{ __('Hold') }}</option>
                                                            <option value="printing process begun"
                                                                {{ $order->order_status == 'printing process begun' ? 'selected' : '' }}>
                                                                {{ __('Printing Process Begun') }}</option>
                                                            <option value="shipped"
                                                                {{ $order->order_status == 'shipped' ? 'selected' : '' }}>
                                                                {{ __('Shipped') }}</option>
                                                            <option value="out for delivery"
                                                                {{ $order->order_status == 'out for delivery' ? 'selected' : '' }}>
                                                                {{ __('Out for delivery') }}</option>
                                                            <option value="delivered"
                                                                {{ $order->order_status == 'delivered' ? 'selected' : '' }}>
                                                                {{ __('Delivered') }}</option>
                                                            <option value="cancelled"
                                                                {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>
                                                                {{ __('Cancelled') }}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Tracking Number --}}
                                                <div class="col-md-12 col-lg-4">
                                                    <div>
                                                        <label for="tracking_number"
                                                            class="form-label">{{ __('Tracking Number') }}</label>
                                                        <input type="text" class="form-control" name="tracking_number"
                                                            id="tracking_number"
                                                            value="{{ isset(json_decode($order->order_details)->tracking_number) ? json_decode($order->order_details)->tracking_number : '' }}"
                                                            placeholder="{{ __('E.g. TRK123456789') }}">
                                                    </div>
                                                </div>

                                                {{-- Courier Partner --}}
                                                <div class="col-md-12 col-lg-4">
                                                    <div>
                                                        <label for="courier_partner"
                                                            class="form-label">{{ __('Courier Partner') }}</label>
                                                        <input type="text" class="form-control" name="courier_partner"
                                                            id="courier_partner"
                                                            value="{{ isset(json_decode($order->order_details)->courier_partner) ? json_decode($order->order_details)->courier_partner : '' }}"
                                                            placeholder="{{ __('E.g. DHL, FedEx, UPS') }}">
                                                    </div>
                                                </div>

                                                {{-- Delivery Message --}}
                                                <div class="col-12">
                                                    <div>
                                                        <label for="delivery_message"
                                                            class="form-label">{{ __('Delivery Message') }}</label>
                                                        <textarea class="form-control" name="delivery_message" id="delivery_message" rows="3"
                                                            placeholder="{{ __('E.g. Package has been dispatched and is currently in transit.') }}">{{ isset(json_decode($order->order_details)->delivery_message) ? json_decode($order->order_details)->delivery_message : '' }}</textarea>
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

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        "use strict";

        // Array of element selectors
        var elementSelectors = ['.status'];

        // Function to initialize TomSelect on an element
        function initializeTomSelect(el) {
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
        }

        // Initialize TomSelect on existing elements
        elementSelectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            elements.forEach(function(el) {
                initializeTomSelect(el);
            });
        });

        // Observe the document for dynamically added elements
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Ensure it's an element node
                        elementSelectors.forEach(function(selector) {
                            if (node.matches(selector)) {
                                initializeTomSelect(node);
                            }
                            // Also check if new nodes have children that match
                            var childElements = node.querySelectorAll(selector);
                            childElements.forEach(function(childEl) {
                                initializeTomSelect(childEl);
                            });
                        });
                    }
                });
            });
        });

        // Configure the observer
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
@endsection
