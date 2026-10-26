@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true, 'settings' => $settings])

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
                    filename: `{{ $transaction->invoice_prefix }}{{ $transaction->invoice_number }}` + '.pdf',
                    html2canvas: {
                        scale: 4
                    }
                })
                .from(element)
                .save();
        }
    </script>
@endsection

@php
    use App\Setting;
    use App\Plan;

    $settings = Setting::first();
    $planDetails = Plan::where('plan_id', $transaction->plan_id)->first();

    // Determine plan term
    if (!empty($planDetails->trial) && $planDetails->trial >= 1) {
        $term = 'Trial';
    } elseif ($planDetails->validity >= 30 && $planDetails->validity <= 31) {
        $term = 'Monthly';
    } elseif ($planDetails->validity >= 365 && $planDetails->validity <= 366) {
        $term = 'Yearly';
    } elseif ((int) $planDetails->validity === 9999) {
        $term = 'Forever';
    } else {
        $term = $planDetails->validity . ' days';
    }
@endphp

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

                <div class="card card-lg">
                    {{-- Invoice parameters --}}
                    @php
                        // Ensure billing details array is safely extracted
                        $billingData = is_array($transaction->billing_details)
                            ? $transaction->billing_details
                            : json_decode($transaction->billing_details, true) ?? [];

                        // Normalize transaction properties to match partial expectations
                        $normalizedTransaction = (object) [
                            'invoice_prefix' => $transaction->invoice_prefix,
                            'invoice_number' => $transaction->invoice_number,
                            'created_at' => $transaction->transaction_date,
                            'currency' => $transaction->transaction_currency ?? ($transaction->currency ?? ''),
                            'payment_method' => $transaction->payment_gateway_name,
                            'payment_transaction_id' => $transaction->transaction_id,
                        ];

                        // Build dynamic item title
                        $itemTitle =
                            __($planDetails->plan_name) .
                            ' - ' .
                            (function_exists('formatCurrency')
                                ? formatCurrency($planDetails->plan_price)
                                : $planDetails->plan_price) .
                            '/' .
                            $term;
                    @endphp

                    @include('partials.invoice', [
                        // General configurations
                        'settings' => $settings,
                        'config' => $config,
                        'currencies' => $currencies,
                        'transaction' => $normalizedTransaction,
                        'paymentTerms' => __($term),
                    
                        // Sender details (From)
                        'fromName' => $billingData['from_billing_name'] ?? '',
                        'fromAddress' => $billingData['from_billing_address'] ?? null,
                        'fromCity' => $billingData['from_billing_city'] ?? null,
                        'fromState' => $billingData['from_billing_state'] ?? null,
                        'fromCountry' => $billingData['from_billing_country'] ?? null,
                        'fromEmail' => $billingData['from_billing_email'] ?? '',
                        'fromPhone' => $billingData['from_billing_phone'] ?? null,
                        'fromVat' => $billingData['from_vat_number'] ?? null,
                    
                        // Customer details (To / Bill To)
                        'toName' => $billingData['to_billing_name'] ?? '',
                        'toAddress' => $billingData['to_billing_address'] ?? null,
                        'toCity' => $billingData['to_billing_city'] ?? null,
                        'toState' => $billingData['to_billing_state'] ?? null,
                        'toCountry' => $billingData['to_billing_country'] ?? null,
                        'toEmail' => $billingData['to_billing_email'] ?? '',
                        'toPhone' => $billingData['to_billing_phone'] ?? null,
                        'toVat' => $billingData['to_vat_number'] ?? null,
                    
                        // Line item details
                        'itemTitle' => $itemTitle,
                        'planDetails' => (object) [
                            'plan_name' => $itemTitle,
                            'no_of_ai_credits' => null,
                            'quantity' => 1,
                            'plan_price' => $planDetails->plan_price,
                        ],
                    
                        // Billing summary
                        'billing' => [
                            'subtotal' => $billingData['subtotal'] ?? 0,
                            'tax_name' => $billingData['tax_name'] ?? '',
                            'tax_value' => $billingData['tax_value'] ?? 0,
                            'tax_amount' => $billingData['tax_amount'] ?? 0,
                            'applied_coupon' => $billingData['applied_coupon'] ?? null,
                            'discounted_price' => $billingData['discounted_price'] ?? 0,
                            'invoice_amount' => $billingData['invoice_amount'] ?? 0,
                        ],
                    ])
                </div>
                {{-- Footer --}}
                @include('user.includes.footer')
            </div>
        </div>
    </div>
@endsection
