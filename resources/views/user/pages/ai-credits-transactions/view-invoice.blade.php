@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true])

@section('css')
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('invoice');
            html2pdf()
                .set({
                    filename: `{{ $transaction->invoice_prefix ? $transaction->invoice_prefix : 'TR' }}{{ $transaction->invoice_number ? $transaction->invoice_number : $transaction->gobiz_payment_transaction_id }}.pdf`,
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
    // Safe billing array
    $billing = json_decode($transaction->invoice_details, true) ?? [];

    // From billing
    $fromName = $billing['from_billing_name'] ?? '';
    $fromAddress = $billing['from_billing_address'] ?? '';
    $fromCity = $billing['from_billing_city'] ?? '';
    $fromState = $billing['from_billing_state'] ?? '';
    $fromCountry = $billing['from_billing_country'] ?? '';
    $fromEmail = $billing['from_billing_email'] ?? '';
    $fromPhone = $billing['from_billing_phone'] ?? '';
    $fromVat = $billing['from_vat_number'] ?? '';

    // To billing
    $toName = $billing['to_billing_name'] ?? '';
    $toAddress = $billing['to_billing_address'] ?? '';
    $toCity = $billing['to_billing_city'] ?? '';
    $toState = $billing['to_billing_state'] ?? '';
    $toCountry = $billing['to_billing_country'] ?? '';
    $toEmail = $billing['to_billing_email'] ?? '';
    $toPhone = $billing['to_billing_phone'] ?? '';
    $toVat = $billing['to_vat_number'] ?? '';
@endphp

@section('content')
    <div class="page-wrapper">
        <div class="page-header d-print-none border-bottom pb-3">
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

                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                <div class="card card-lg">
                    {{-- Invoice parameters --}}
                    @include('partials.invoice', [
                        // General configurations
                        'settings' => $settings,
                        'config' => $config,
                        'currencies' => $currencies,
                        'transaction' => $transaction,
                    
                        // Sender details (From)
                        'fromName' => $fromName,
                        'fromAddress' => $fromAddress,
                        'fromCity' => $fromCity,
                        'fromState' => $fromState,
                        'fromCountry' => $fromCountry,
                        'fromEmail' => $fromEmail,
                        'fromPhone' => $fromPhone,
                        'fromVat' => $fromVat,
                    
                        // Customer details (To / Bill To)
                        'toName' => $toName,
                        'toAddress' => $toAddress,
                        'toCity' => $toCity,
                        'toState' => $toState,
                        'toCountry' => $toCountry,
                        'toEmail' => $toEmail,
                        'toPhone' => $toPhone,
                        'toVat' => $toVat,
                    
                        // Plan & Line item details
                        'planDetails' => $planDetails,
                        'itemTitle' =>
                            __($planDetails->plan_name) .
                            ' - ' .
                            $planDetails->no_of_ai_credits .
                            ' ' .
                            __('AI Credit'),
                        'itemQty' => 1,
                        'itemRate' => $planDetails->plan_price ?? 0,
                        'itemAmount' => $planDetails->plan_price ?? 0,
                    
                        // Billing summary
                        'billing' => $billing,
                    ])
                </div>

                @include('user.includes.footer')
            </div>
        </div>

    </div>
@endsection
