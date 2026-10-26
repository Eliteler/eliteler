@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true])

@section('css')
    <style>
        @media print {
            .container {
                max-width: 100% !important;
            }
        }
    </style>

    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('invoice');
            html2pdf()
                .set({
                    filename: `{{ $transaction_details->invoice_prefix ? $transaction_details->invoice_prefix : 'TR' }}{{ $transaction_details->invoice_number ? $transaction_details->invoice_number : $transaction_details->nfc_card_order_transaction_id }}` +
                        '.pdf',
                    html2canvas: {
                        scale: 4
                    }
                })
                .from(element)
                .save();
        }
    </script>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none border-bottom pb-3">
            <!-- Page title -->
            <div class="container d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-item-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left icon-primary">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                    </a>
                    <h2 class="page-title">
                        {{ __('Invoice') }}
                    </h2>
                </div>
                <!-- Page title actions -->
                <div class="d-print-none">
                    <div class="dropdown">
                        <button type="button" class="btn btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2">
                                </path>
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"></path>
                                <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z">
                                </path>
                            </svg>
                            {{ __('Actions') }}
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" onclick="generatePDF()" onclick="javascript:window.print();">
                                {{ __('Download') }}
                            </a>
                            <a class="dropdown-item" onclick="javascript:window.print();">
                                {{ __('Print') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="card card-lg border-0 shadow-sm">
                    {{-- Invoice parameters --}}
                    @php
                        // Decode JSON strings for invoice and order details
                        $invoiceData = json_decode($transaction_details->invoice_details);
                        $orderData = json_decode($transaction_details->order_details);
                        $currencySymbol =
                            $currencies->firstWhere('iso_code', $transaction_details->currency)->symbol ?? '';

                        // Construct dynamic item title
                        $itemTitle =
                            __($transaction_details->nfc_card_name) .
                            ' - ' .
                            $currencySymbol .
                            ($orderData->price ?? 0) .
                            '/' .
                            __('One time');
                    @endphp

                    @include('partials.invoice', [
                        // General configurations
                        'settings' => $settings,
                        'config' => $config,
                        'currencies' => $currencies,
                        'transaction' => $transaction_details,
                        'paymentTerms' => __('One time'),
                    
                        // Sender details (From)
                        'fromName' => $invoiceData->from_billing_name ?? '',
                        'fromAddress' => $invoiceData->from_billing_address ?? null,
                        'fromCity' => $invoiceData->from_billing_city ?? null,
                        'fromState' => $invoiceData->from_billing_state ?? null,
                        'fromCountry' => $invoiceData->from_billing_country ?? null,
                        'fromEmail' => $invoiceData->from_billing_email ?? '',
                        'fromPhone' => $invoiceData->from_billing_phone ?? null,
                        'fromVat' => $invoiceData->from_vat_number ?? null,
                    
                        // Customer details (To / Bill To)
                        'toName' => $invoiceData->to_billing_name ?? '',
                        'toAddress' => $invoiceData->to_billing_address ?? null,
                        'toCity' => $invoiceData->to_billing_city ?? null,
                        'toState' => $invoiceData->to_billing_state ?? null,
                        'toCountry' => $invoiceData->to_billing_country ?? null,
                        'toEmail' => $invoiceData->to_billing_email ?? '',
                        'toPhone' => $invoiceData->to_billing_phone ?? null,
                        'toVat' => $invoiceData->to_vat_number ?? null,
                    
                        // Line item details
                        'itemTitle' => $itemTitle,
                        'itemQty' => $orderData->order_quantity ?? 1,
                        'itemRate' => $orderData->price ?? 0,
                        'itemAmount' => $invoiceData->subtotal ?? 0,
                        'planDetails' => (object) [
                            'plan_name' => $itemTitle,
                            'no_of_ai_credits' => null,
                            'quantity' => $orderData->order_quantity ?? 1,
                            'plan_price' => $orderData->price ?? 0,
                        ],
                    
                        // Billing summary
                        'billing' => [
                            'subtotal' => $invoiceData->subtotal ?? 0,
                            'tax_name' => $invoiceData->tax_name ?? '',
                            'tax_value' => $invoiceData->tax_value ?? 0,
                            'tax_amount' => $invoiceData->tax_amount ?? 0,
                            'applied_coupon' => $invoiceData->applied_coupon ?? null,
                            'discounted_price' => $invoiceData->discounted_price ?? 0,
                            'invoice_amount' => $invoiceData->invoice_amount ?? 0,
                        ],
                    ])
                </div>

                {{-- Footer --}}
                @include('user.includes.footer')
            </div>
        </div>
    </div>
@endsection

{{-- Custom scripts pushed inside correct stack --}}
@push('custom-js')
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        "use strict";

        function generatePDF() {
            const element = document.getElementById('invoice');
            html2pdf()
                .set({
                    filename: `{{ $transaction_details->invoice_prefix ? $transaction_details->invoice_prefix : 'TR' }}{{ $transaction_details->invoice_number ? $transaction_details->invoice_number : $transaction_details->nfc_card_order_transaction_id }}` +
                        '.pdf',
                    html2canvas: {
                        scale: 4
                    }
                })
                .from(element)
                .save();
        }
    </script>
@endpush
