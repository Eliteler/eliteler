@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('invoice');
            html2pdf()
                .set({
                    filename: `{{ $transaction->invoice_prefix ? $transaction->invoice_prefix : 'TR' }}{{ $transaction->invoice_number ? $transaction->invoice_number : $transaction->gobiz_transaction_id }}.pdf`,
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

    if ((int) $planDetails->validity == 9999) {
        $term = 'Lifetime';
    } elseif ((int) $planDetails->validity >= 30 && (int) $planDetails->validity <= 31) {
        $term = 'Monthly';
    } elseif ((int) $planDetails->validity >= 365 && (int) $planDetails->validity <= 366) {
        $term = 'Yearly';
    } else {
        $term = $planDetails->validity . ' days';
    }

    // Safe billing array
    $billing = is_array($transaction->billing_details) ? $transaction->billing_details : [];

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
                                <a href="{{ route('admin.transactions.index') }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Transactions') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Invoice') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Invoice') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('View receipt breakdowns, billing targets, tax details, and download digital invoice copies.') }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-auto ms-auto d-print-none">
                        <div class="dropdown">
                            <button type="button" class="btn btn-primary dropdown-toggle d-inline-flex align-items-center"
                                data-bs-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path
                                        d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2">
                                    </path>
                                    <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"></path>
                                    <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z">
                                    </path>
                                </svg>
                                <span class="d-none d-sm-inline">{{ __('Actions') }}</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="generatePDF()">
                                    {{ __('Download') }}
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="javascript:window.print();">
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
                {{-- Failed alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                {{-- Success alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                <div class="card border-0 shadow-sm">
                    <div class="p-2" id="invoice">
                        <div class="card-body">
                            <!-- Header Section -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    {{-- Logo --}}
                                    <img src="{{ asset($settings->site_logo) }}" class="img-fluid mb-2"
                                        style="max-height: 45px;" alt="{{ config('app.name') }}"><br>

                                    <span class="h4 text-dark">{{ $fromName }}</span><br>

                                    <span class="text-secondary small">
                                        @if ($fromAddress)
                                            {{ $fromAddress }},
                                        @endif
                                        @if ($fromCity)
                                            {{ $fromCity }},
                                        @endif
                                        @if ($fromState)
                                            {{ $fromState }}
                                        @endif
                                        @if ($fromCountry)
                                            {{ $fromCountry }} <br>
                                        @endif
                                    </span>

                                    <span class="text-secondary small"><strong>{{ __('Email') }}</strong>:
                                        {{ $fromEmail }}</span><br>

                                    @if (!empty($fromPhone))
                                        <span class="text-secondary small"><strong>{{ __('Phone') }}</strong>:
                                            {{ $fromPhone }}</span><br>
                                    @endif

                                    @if (!empty($fromVat))
                                        <span class="text-secondary small"><strong>{{ __('Tax Number') }}</strong>:
                                            {{ $fromVat }}</span><br>
                                    @endif
                                </div>

                                <div class="col-6 text-end">
                                    <h1 class="text-dark">{{ __('INVOICE') }}</h1>
                                    <h4 class="text-secondary">
                                        #{{ $transaction->invoice_prefix }}{{ $transaction->invoice_number }}</h4>
                                </div>
                            </div>

                            <!-- Billing Information -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <h4 class="text-muted mb-2 text-uppercase font-weight-medium"
                                        style="font-size: 11px; letter-spacing: 0.5px;">{{ __('Bill To') }}</h4>

                                    <span class="h4 text-dark">{{ $toName }}</span><br>

                                    @if (!empty($toAddress))
                                        <span class="text-secondary small">
                                            @if ($toAddress)
                                                {{ $toAddress }},
                                            @endif
                                            @if ($toCity)
                                                {{ $toCity }},
                                            @endif
                                            @if ($toState)
                                                {{ $toState }}
                                            @endif
                                            @if ($toCountry)
                                                {{ $toCountry }} <br>
                                            @endif
                                        </span>
                                    @endif

                                    <span class="text-secondary small"><strong>{{ __('Email') }}</strong>:
                                        {{ $toEmail }}</span><br>

                                    @if (!empty($toPhone))
                                        <span class="text-secondary small"><strong>{{ __('Phone') }}</strong>:
                                            {{ $toPhone }}</span><br>
                                    @endif

                                    @if (!empty($toVat))
                                        <span class="text-secondary small"><strong>{{ __('Tax Number') }}</strong>:
                                            {{ $toVat }}</span><br>
                                    @endif
                                </div>

                                <div class="col-6 text-end text-secondary small">
                                    <p class="mb-1"><strong>{{ __('Date') }}</strong>:
                                        {{ date('M d, Y', strtotime($transaction->transaction_date)) }}</p>
                                    <p class="mb-1"><strong>{{ __('Payment Terms') }}</strong>:
                                        {{ __($transaction->payment_gateway_name != 'TRIAL' ? $term : 'TRIAL') }}</p>
                                    <h5 class="text-dark"><strong>{{ __('Balance Due') }}</strong>:
                                        {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}0.00
                                    </h5>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="table-responsive">
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
                                        <tr class="fw-bold text-dark">
                                            <td>{{ __($planDetails->plan_name) }} -
                                                {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $planDetails->plan_price }}{{ $term == '9999 days' ? '' : '/' . $term }}
                                            </td>
                                            <td class="text-end">1</td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $planDetails->plan_price }}
                                            </td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $planDetails->plan_price }}
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tfoot class="border-top fw-bold text-dark">
                                        <tr>
                                            <td colspan="3" class="text-end text-secondary font-weight-normal">
                                                {{ __('Subtotal') }}</td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $billing['subtotal'] ?? 0 }}
                                            </td>
                                        </tr>

                                        @if (($billing['tax_amount'] ?? 0) > 0)
                                            <tr>
                                                <td colspan="3" class="text-end text-secondary font-weight-normal">
                                                    {{ __($billing['tax_name'] ?? '') }}
                                                    ({{ $billing['tax_value'] ?? 0 }}%)</td>
                                                <td class="text-end">
                                                    {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $billing['tax_amount'] ?? 0 }}
                                                </td>
                                            </tr>
                                        @endif

                                        @if (!empty($billing['applied_coupon']))
                                            <tr>
                                                <td colspan="3" class="text-end text-secondary font-weight-normal">
                                                    {{ __('Before Discount') }}</td>
                                                <td class="text-end">
                                                    {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}
                                                    {{ ($billing['subtotal'] ?? 0) + ($billing['tax_amount'] ?? 0) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td colspan="3" class="text-end">
                                                    <strong>{{ __('Applied Coupon') }}:
                                                        {{ $billing['applied_coupon'] }}</strong>
                                                </td>
                                                <td class="text-end text-danger">
                                                    -
                                                    {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $billing['discounted_price'] ?? 0 }}
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-end text-secondary font-weight-normal">
                                                    {{ __('After Tax') }}</td>
                                                <td class="text-end">
                                                    {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}
                                                    {{ ($billing['subtotal'] ?? 0) + ($billing['tax_amount'] ?? 0) }}
                                                </td>
                                            </tr>
                                        @endif

                                        <tr>
                                            <td colspan="3" class="text-end"><strong>{{ __('Total') }}</strong></td>
                                            <td class="text-end fs-3">
                                                <strong>{{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $billing['invoice_amount'] ?? 0 }}</strong>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="3" class="text-end text-secondary font-weight-normal">
                                                {{ __('Amount Paid') }}</td>
                                            <td class="text-end">
                                                {{ $currencies->firstWhere('iso_code', $transaction->transaction_currency)->symbol ?? '' }}{{ $billing['invoice_amount'] ?? 0 }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Notes Section -->
                            <p class="mt-5 text-secondary small">
                                <strong class="text-dark d-block mb-1">{{ __('Notes') }}</strong>
                                {{ __('Payment from ') }}{{ __($transaction->payment_gateway_name) }}<br>
                                {{ __('Transaction ID: ') }} {{ $transaction->transaction_id }}
                            </p>

                            {{-- Footer Remarks --}}
                            <p class="text-center text-muted mt-5 small">
                                {{ __($config[29]->config_value ?? 'Thank you for your business') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection
