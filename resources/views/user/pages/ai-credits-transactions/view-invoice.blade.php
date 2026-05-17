@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

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
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('Invoice') }}
                        </h2>
                    </div>

                    <div class="col-auto ms-auto d-print-none">
                        <div class="dropdown">
                            <button type="button" class="btn btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path
                                        d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2">
                                    </path>
                                    <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z">
                                    </path>
                                </svg>
                                {{ __('Actions') }}
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" onclick="generatePDF()">
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
        </div>

        <div class="page-body">
            <div class="container-fluid">

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
                    <div class="p-3" id="invoice">
                        <div class="card-body">

                            <div class="row mb-4">
                                <div class="col-6">

                                    <img src="{{ asset($settings->site_logo) }}" class="img-fluid"
                                        alt="{{ config('app.name') }}"><br>

                                    <span class="h4">{{ $fromName }}</span><br>

                                    <span>
                                        {{ $fromAddress }},
                                        {{ $fromCity }},
                                        {{ $fromState }}
                                        {{ $fromCountry }} <br>
                                    </span>

                                    <span><strong>{{ __('Email') }}</strong>: {{ $fromEmail }}</span><br>

                                    @if (!empty($fromPhone))
                                        <span><strong>{{ __('Phone') }}</strong>: {{ $fromPhone }}</span><br>
                                    @endif

                                    @if (!empty($fromVat))
                                        <span><strong>{{ __('Tax Number') }}</strong>: {{ $fromVat }}</span><br>
                                    @endif

                                </div>

                                <div class="col-6 text-end">
                                    <h1>{{ __('INVOICE') }}</h1>
                                    <h4>#{{ $transaction->invoice_prefix }}{{ $transaction->invoice_number }}</h4>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <h4 class="text-muted">{{ __('Bill To') }}</h4>

                                    <span class="h4">{{ $toName }}</span><br>

                                    <span>
                                        {{ $toAddress }},
                                        {{ $toCity }},
                                        {{ $toState }}
                                        {{ $toCountry }} <br>
                                    </span>

                                    <span><strong>{{ __('Email') }}</strong>: {{ $toEmail }}</span><br>

                                    @if (!empty($toPhone))
                                        <span><strong>{{ __('Phone') }}</strong>: {{ $toPhone }}</span><br>
                                    @endif

                                    @if (!empty($toVat))
                                        <span><strong>{{ __('Tax Number') }}</strong>: {{ $toVat }}</span><br>
                                    @endif
                                </div>

                                <div class="col-6 text-end">
                                    <p><strong>{{ __('Date') }}</strong>:
                                        {{ date('M d, Y', strtotime($transaction->created_at)) }}</p>
                                    <p><strong>{{ __('Payment Terms') }}</strong>:
                                        {{ __('-') }}</p>
                                    <h5><strong>{{ __('Balance Due') }}</strong>:
                                        {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}0.00
                                    </h5>
                                </div>
                            </div>

                            <table class="table table-borderless">
                                <thead class="border-bottom">
                                    <tr>
                                        <th>{{ __('Item') }}</th>
                                        <th class="text-end">{{ __('Quantity') }}</th>
                                        <th class="text-end">{{ __('Rate') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr class="fw-bold">
                                        <td>{{ __($planDetails->plan_name) }} -
                                            {{ $planDetails->no_of_ai_credits }} {{ __('AI Credits') }}
                                        </td>
                                        <td class="text-end">1</td>
                                        <td class="text-end">
                                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $planDetails->plan_price }}
                                        </td>
                                        <td class="text-end">
                                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $planDetails->plan_price }}
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot class="border-top fw-bold">

                                    <tr>
                                        <td colspan="3" class="text-end">{{ __('Subtotal') }}</td>
                                        <td class="text-end">
                                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['subtotal'] ?? 0 }}
                                        </td>
                                    </tr>

                                    @if (($billing['tax_amount'] ?? 0) > 0)
                                        <tr>
                                            <td colspan="3" class="text-end">{{ __($billing['tax_name'] ?? '') }}
                                                ({{ $billing['tax_value'] ?? 0 }}%)</td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['tax_amount'] ?? 0 }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($billing['applied_coupon']))
                                        <tr>
                                            <td colspan="3" class="text-end">{{ __('Before Discount') }}</td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}
                                                {{ ($billing['subtotal'] ?? 0) + ($billing['tax_amount'] ?? 0) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="3" class="font-weight-bold text-end">
                                                <strong>{{ __('Applied Coupon') }}:
                                                    {{ $billing['applied_coupon'] }}</strong>
                                            </td>
                                            <td class="font-weight-bold text-end">
                                                -
                                                {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['discounted_price'] ?? 0 }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-end">{{ __('After Tax') }}</td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}
                                                {{ ($billing['subtotal'] ?? 0) + ($billing['tax_amount'] ?? 0) }}
                                            </td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ __('Total') }}</strong></td>
                                        <td class="text-end">
                                            <strong>{{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['invoice_amount'] ?? 0 }}</strong>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="3" class="text-end">{{ __('Amount Paid') }}</td>
                                        <td class="text-end">
                                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['invoice_amount'] ?? 0 }}
                                        </td>
                                    </tr>

                                </tfoot>
                            </table>

                            <p class="mt-5">
                                <strong>{{ __('Notes') }}</strong>:<br>
                                <span class="text-muted">
                                    {{ __('Payment from ') }}{{ __($transaction->payment_method) }}<br>
                                    {{ __('Transaction ID: ') }} {{ $transaction->payment_transaction_id }}
                                </span>
                            </p>

                            <p class="text-center text-muted mt-5">
                                {{ __($config[29]->config_value ?? 'Thank you for your business') }}
                            </p>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        @include('user.includes.footer')
    </div>
@endsection
