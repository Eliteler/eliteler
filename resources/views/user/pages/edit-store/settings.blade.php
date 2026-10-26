@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-settings.css') }}" rel="stylesheet">
@endsection

@php
    $deliveryOptions = isset($business_card->delivery_options) ? json_decode($business_card->delivery_options) : null;
    $storeDescription = is_array($business_card->description)
        ? $business_card->description
        : json_decode($business_card->description, true);
    $invoicePrefix = $storeDescription['invoice_prefix'] ?? 'INV-';
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Navigation -->
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-muted d-inline-flex align-items-center gap-1">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.stores') }}" class="text-muted">{{ __('Stores') }}</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Store Settings') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Fulfillment & Billing Parameters') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __("Configure your store's delivery configurations, transaction rules, fulfillment types, and invoice options.") }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'settings',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Main Settings Form --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store.settings') }}" method="POST" id="storeSettingsForm">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $business_card->card_id }}">

                                <div class="card-body p-4">
                                    {{-- Section 1: Fulfillment & Delivery Methods --}}
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <div>
                                                <h4 class="card-title fw-bold mb-0">
                                                    {{ __('Customer Fulfillment Options') }}</h4>
                                                <small
                                                    class="text-muted">{{ __('Select available order fulfillment types for customer checkouts.') }}</small>
                                            </div>
                                        </div>

                                        <div class="row g-1">
                                            {{-- 1. Doorstep Delivery Toggle --}}
                                            <div class="col-12">
                                                <div class="settings-switch-card">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <span class="settings-switch-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-truck-delivery"
                                                                    width="22" height="22" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                                    <path
                                                                        d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" />
                                                                    <path d="M3 9l4 0" />
                                                                </svg>
                                                            </span>
                                                            <div>
                                                                <div class="fw-bold text-reset">
                                                                    {{ __('Doorstep Delivery') }}</div>
                                                                <p class="text-muted small mb-0">
                                                                    {{ __('Allow customers to order items for delivery directly to their address.') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input" type="checkbox" value="1"
                                                                name="order_for_delivery"
                                                                {{ isset($deliveryOptions->order_for_delivery) && $deliveryOptions->order_for_delivery == 1 ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- 2. Take Away / Pickup Toggle --}}
                                            <div class="col-12">
                                                <div class="settings-switch-card">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <span class="settings-switch-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-shopping-bag"
                                                                    width="22" height="22" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path
                                                                        d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" />
                                                                    <path d="M9 11v-5a3 3 0 0 1 6 0v5" />
                                                                </svg>
                                                            </span>
                                                            <div>
                                                                <div class="fw-bold text-reset">
                                                                    {{ __('Take Away / Store Pickup') }}</div>
                                                                <p class="text-muted small mb-0">
                                                                    {{ __('Allow customers to order online and collect items directly from your shop.') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input" type="checkbox" value="1"
                                                                name="take_away"
                                                                {{ isset($deliveryOptions->take_away) && $deliveryOptions->take_away == 1 ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- 3. Dine In Toggle --}}
                                            <div class="col-12">
                                                <div class="settings-switch-card">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <span class="settings-switch-icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="20"
                                                                    height="20" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-tools-kitchen-2">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M19 3v12h-5c-.023 -3.681 .184 -7.406 5 -12m0 12v6h-1v-3m-10 -14v17m-3 -17v3a3 3 0 1 0 6 0v-3" />
                                                                </svg>
                                                            </span>
                                                            <div>
                                                                <div class="fw-bold text-reset">
                                                                    {{ __('Dine In Ordering') }}</div>
                                                                <p class="text-muted small mb-0">
                                                                    {{ __('Enable table-side ordering and in-restaurant service options.') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="form-check form-switch mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                                value="1" name="dine_in"
                                                                {{ isset($deliveryOptions->dine_in) && $deliveryOptions->dine_in == 1 ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 2: Invoice & Billing Settings --}}
                                    <div class="store-section-card">
                                        <div class="store-section-header">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="store-section-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-file-invoice" width="20"
                                                        height="20" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                        <path
                                                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                        <path d="M9 12l6 0" />
                                                        <path d="M9 16l6 0" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h4 class="card-title mb-0">{{ __('Invoice & Receipt Settings') }}
                                                    </h4>
                                                    <span
                                                        class="small text-muted">{{ __('Customize official invoice numbering prefix and identifiers') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label
                                                        class="form-label required">{{ __('Invoice Number Prefix') }}</label>
                                                    <div class="input-icon">
                                                        <span class="input-icon-addon">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon icon-tabler icon-tabler-receipt"
                                                                width="18" height="18" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2" />

                                                            </svg>
                                                        </span>
                                                        <input type="text" class="form-control" name="invoice_prefix"
                                                            id="invoicePrefixInput" value="{{ $invoicePrefix }}"
                                                            placeholder="{{ __('e.g., INV-, STORE-') }}" required>
                                                    </div>
                                                    <small
                                                        class="form-hint mt-1">{{ __('This prefix will be prepended to all generated customer invoices.') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Symmetrical Action Footer -->
                                <div class="card-footer bg-transparent border-top p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('user.stores') }}"
                                            class="btn btn-danger d-inline-flex align-items-center gap-1">
                                            {{ __('Cancel') }}
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4l-4 0l0 -4" />
                                            </svg>
                                            {{ __('Save') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@push('custom-js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const prefixInput = document.getElementById('invoicePrefixInput');
            const sampleDisplay = document.getElementById('sampleInvoiceDisplay');

            if (prefixInput && sampleDisplay) {
                prefixInput.addEventListener('input', function() {
                    const val = this.value.trim() || 'INV-';
                    sampleDisplay.textContent = val + '00101';
                });
            }
        });
    </script>
@endpush
