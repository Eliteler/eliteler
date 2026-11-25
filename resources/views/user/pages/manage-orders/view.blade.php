@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
    <div class="page-wrapper">
        <!-- Page header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumb Navigation -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.manage.nfc.orders') }}" class="text-secondary text-decoration-none">
                                    {{ __('My Orders') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('NFC Card Order') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('NFC Card Order') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Detailed receipt, billing coordinates, tracking coordinates, and fulfillment items for your NFC card order.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
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

                {{-- Order Details Card --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Order & Delivery Details') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="datagrid">
                                    <div class="datagrid-item mx-3 my-1 d-print-none">
                                        <div class="datagrid-title">{{ __('Payment ID') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{ $order->nfc_card_order_transaction_id ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Order ID') }}</div>
                                        <div class="datagrid-content fw-bold">{{ $order->nfc_card_order_id ?? '-' }}</div>
                                    </div>
                                    <div class="datagrid-item mx-3 my-1 d-print-none">
                                        <div class="datagrid-title">{{ __('Order Date') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{ formatDateForUser($order->created_at) }}
                                        </div>
                                    </div>
                                    <div class="datagrid-item mx-3 my-1 d-print-none">
                                        <div class="datagrid-title">{{ __('Delivery Status') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{-- Soft-muted contextual status badges --}}
                                            @if ($order->order_status == 'pending')
                                                <span
                                                    class="badge bg-yellow-100 text-yellow-800 text-uppercase fw-semibold">
                                                    {{ __('Pending') }}
                                                </span>
                                            @elseif($order->order_status == 'processing')
                                                <span class="badge bg-blue-100 text-blue-700 text-uppercase fw-semibold">
                                                    {{ __('Processing') }}
                                                </span>
                                            @elseif($order->order_status == 'out for delivery')
                                                <span class="badge bg-slate-100 text-slate-700 text-uppercase fw-semibold">
                                                    {{ __('Out for delivery') }}
                                                </span>
                                            @elseif($order->order_status == 'delivered')
                                                <span class="badge bg-green-100 text-green-700 text-uppercase fw-semibold">
                                                    {{ __('Delivered') }}
                                                </span>
                                            @elseif($order->order_status == 'cancelled')
                                                <span class="badge bg-red-100 text-red-700 text-uppercase fw-semibold">
                                                    {{ __('Cancelled') }}
                                                </span>
                                            @elseif($order->order_status == 'hold')
                                                <span
                                                    class="badge bg-yellow-100 text-yellow-800 text-uppercase fw-semibold">
                                                    {{ __('Hold') }}
                                                </span>
                                            @elseif($order->order_status == 'shipped')
                                                <span class="badge bg-green-100 text-green-700 text-uppercase fw-semibold">
                                                    {{ __('Shipped') }}
                                                </span>
                                            @elseif($order->order_status == 'printing process begun')
                                                <span class="badge bg-blue-100 text-blue-700 text-uppercase fw-semibold">
                                                    {{ __('Printing Process Begun') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="datagrid mt-4">
                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Payment Mode') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            <span class="badge bg-slate-100 text-slate-700 text-uppercase fw-semibold">
                                                {{ $order->payment_method ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Delivery Partner') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{ json_decode($order->order_details)->courier_partner ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Tracking Number') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{ json_decode($order->order_details)->tracking_number ?? '-' }}
                                        </div>
                                    </div>
                                    <div class="datagrid-item mx-3 my-1 d-print-none">
                                        <div class="datagrid-title">{{ __('Delivery Message') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{ json_decode($order->order_details)->delivery_message ?? '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="datagrid mt-4">
                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Billing Address') }}</div>
                                        <div class="datagrid-content">
                                            @php
                                                $address = $order->delivery_address
                                                    ? json_decode($order->delivery_address)
                                                    : new stdClass();
                                                $billingName = $address->billing_name ?? '';
                                                $billingAddress = $address->billing_address ?? '';
                                                $billingCity = $address->billing_city ?? '';
                                                $billingPostcode = $address->billing_zipcode ?? '';
                                                $billingCountry = $address->billing_country ?? '';
                                                $billingPhone = $address->billing_phone ?? '';
                                                $billingEmail = $address->billing_email ?? '';
                                                $vatNumber = $address->vat_number ?? '';
                                                $type = $address->type ?? '';
                                            @endphp
                                            <span>
                                                <span class="fw-bold">{{ $billingName }}</span>
                                                <br>
                                                <a class="fw-bold text-decoration-none"
                                                    href="http://maps.google.com/?q={{ $billingAddress }},{{ $billingCity }},{{ $billingPostcode }},{{ $billingCountry }}"
                                                    target="_blank">{{ $billingAddress }}, {{ $billingCity }},
                                                    {{ $billingPostcode }}, {{ $billingCountry }}</a>
                                                <br>
                                                {{ __('Phone Number') }} : <span class="fw-bold"><a
                                                        class="text-decoration-none"
                                                        href="tel:0{{ $billingPhone }}">{{ $billingPhone }}</a></span>
                                                <br>
                                                {{ __('Email') }} : <span class="fw-bold"><a class="text-decoration-none"
                                                        href="mailto:{{ $billingEmail }}">{{ $billingEmail }}</a></span>
                                                <br>
                                                {{ __('VAT Number') }} : <span class="fw-bold">{{ $vatNumber }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Shipping Address') }}</div>
                                        <div class="datagrid-content">
                                            @php
                                                $address = $order->delivery_address
                                                    ? json_decode($order->delivery_address)
                                                    : new stdClass();
                                                $billingName = $address->billing_name ?? '';
                                                $billingAddress = $address->billing_address ?? '';
                                                $billingCity = $address->billing_city ?? '';
                                                $billingPostcode = $address->billing_zipcode ?? '';
                                                $billingCountry = $address->billing_country ?? '';
                                                $billingPhone = $address->billing_phone ?? '';
                                                $billingEmail = $address->billing_email ?? '';
                                                $vatNumber = $address->vat_number ?? '';
                                                $type = $address->type ?? '';
                                            @endphp
                                            <span>
                                                <span class="fw-bold">{{ $billingName }}</span>
                                                <br>
                                                <a class="fw-bold text-decoration-none"
                                                    href="http://maps.google.com/?q={{ $billingAddress }},{{ $billingCity }},{{ $billingPostcode }},{{ $billingCountry }}"
                                                    target="_blank">{{ $billingAddress }}, {{ $billingCity }},
                                                    {{ $billingPostcode }}, {{ $billingCountry }}</a>
                                                <br>
                                                {{ __('Phone Number') }} : <span class="fw-bold"><a
                                                        class="text-decoration-none"
                                                        href="tel:0{{ $billingPhone }}">{{ $billingPhone }}</a></span>
                                                <br>
                                                {{ __('Email') }} : <span class="fw-bold"><a class="text-decoration-none"
                                                        href="mailto:{{ $billingEmail }}">{{ $billingEmail }}</a></span>
                                                <br>
                                                {{ __('VAT Number') }} : <span class="fw-bold">{{ $vatNumber }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="datagrid-item mx-3 my-1 d-print-none">
                                        <div class="datagrid-title">{{ __('Delivery Note') }}</div>
                                        <div class="datagrid-content fw-bold">
                                            {{ $order->delivery_note ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="datagrid-item mx-3 my-1">
                                        <div class="datagrid-title">{{ __('Total') }}</div>
                                        <div class="datagrid-content fw-bold text-primary">
                                            {{ formatMoney(json_decode($order->order_details)->invoice_amount, $order->currency) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Items Table --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card card-table border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Order Items') }}</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Item') }}</th>
                                            <th class="text-end">{{ __('Price') }}</th>
                                            <th class="text-end">{{ __('Quantity') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-bold">
                                        <tr>
                                            <td>
                                                @if ($photo)
                                                    <img src="{{ asset($photo) }}" alt="" width="56" height="56"
                                                        class="rounded border float-start me-3 d-none d-sm-block"
                                                        style="object-fit: cover;">
                                                @endif
                                                <p class="strong mb-1">
                                                    {{ json_decode($order->order_details)->order_item }}
                                                </p>
                                                <div class="text-secondary d-none d-sm-block">{!! nl2br(e(wordwrap(json_decode($order->order_details)->order_description, 100, "\n"))) !!}</div>
                                            </td>
                                            <td class="text-end">
                                                {{ formatMoney(json_decode($order->order_details)->price, $order->currency) }}</td>
                                            <td class="text-end">{{ json_decode($order->order_details)->order_quantity }}
                                            </td>
                                            <td class="text-end">
                                                {{ formatMoney(json_decode($order->order_details)->price * json_decode($order->order_details)->order_quantity, $order->currency) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="strong text-end">{{ __('Subtotal') }}</td>
                                            <td class="text-end">
                                                {{ formatMoney(json_decode($order->order_details)->subtotal, $order->currency) }}</td>
                                        </tr>
                                        @if (json_decode($order->order_details)->tax_value != null)
                                            <tr>
                                                <td colspan="3" class="strong text-end">{{ __('Tax') }} <br><small
                                                        class="text-secondary">{{ __(json_decode($order->order_details)->tax_name) }}
                                                        : {{ json_decode($order->order_details)->tax_value }}%</small></td>
                                                <td class="text-end">
                                                    {{ formatMoney(json_decode($order->order_details)->tax_amount, $order->currency) }}
                                                </td>
                                            </tr>
                                        @endif
                                        @if (json_decode($order->order_details)->discounted_price != null)
                                            <tr>
                                                <td colspan="3" class="strong text-end">
                                                    {{ __('Before Applied Coupon') }}
                                                </td>
                                                <td class="text-end">
                                                    {{ formatMoney(json_decode($order->order_details)->subtotal + json_decode($order->order_details)->tax_amount, $order->currency) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="strong text-end">{{ __('Coupon') }} <br><small
                                                        class="text-secondary">{{ __('Applied Coupon') }} :
                                                        {{ json_decode($order->order_details)->applied_coupon }}</small>
                                                </td>
                                                <td class="text-end">
                                                    -{{ formatMoney(json_decode($order->order_details)->discounted_price, $order->currency) }}
                                                </td>
                                            </tr>
                                        @endif
                                        @if ((float) (json_decode($order->order_details)->shipping_amount ?? 0) > 0)
                                            <tr>
                                                <td colspan="3" class="strong text-end">{{ __('Shipping') }}</td>
                                                <td class="text-end">
                                                    {{ formatMoney(json_decode($order->order_details)->shipping_amount, $order->currency) }}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td colspan="3" class="strong text-end text-heading">{{ __('Total') }}
                                            </td>
                                            <td class="text-end text-primary">
                                                {{ formatMoney(json_decode($order->order_details)->invoice_amount, $order->currency) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Image assets (print files) --}}
                @if (count($assets))
                    @php
                        $missingRequired = collect($assets)->filter(fn ($a) => $a['required'] && ! $a['file'])->count();
                    @endphp
                    <div class="row mt-3 d-print-none" id="image-assets">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-transparent">
                                    <div>
                                        <h3 class="card-title">{{ __('Image assets') }}</h3>
                                        <div class="text-secondary small mt-1">
                                            @if ($canUpload)
                                                {{ __('Upload the files we need to print your product. Images (JPG, PNG, WebP, SVG) or PDF.') }}
                                            @else
                                                {{ __('Files can only be uploaded or changed while the order is processing or on hold.') }}
                                            @endif
                                        </div>
                                    </div>
                                    @if ($missingRequired > 0)
                                        <span class="badge bg-red-100 text-red-700 ms-auto">{{ __(':count required missing', ['count' => $missingRequired]) }}</span>
                                    @endif
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach ($assets as $asset)
                                        <div class="list-group-item">
                                            <div class="row align-items-center g-3">
                                                <div class="col-auto">
                                                    <span class="avatar avatar-md bg-light border">
                                                        @if ($asset['file'] && $asset['file']['preview'])
                                                            <img src="{{ $asset['file']['url'] }}" alt="" style="object-fit: contain; width: 100%; height: 100%;">
                                                        @elseif ($asset['file'])
                                                            <span class="text-secondary small fw-bold">{{ strtoupper(pathinfo($asset['file']['path'], PATHINFO_EXTENSION)) }}</span>
                                                        @else
                                                            <span class="text-secondary">—</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="col">
                                                    <div class="fw-bold">
                                                        {{ $asset['name'] }}
                                                        @if ($asset['required'])
                                                            <span class="badge {{ $asset['file'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} ms-1">{{ $asset['file'] ? __('Required') : __('Required · missing') }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($asset['resolution'])
                                                        <div class="text-secondary small">{{ __('Suggested') }}: {{ $asset['resolution'] }}</div>
                                                    @endif
                                                    @if ($asset['file'])
                                                        <a href="{{ $asset['file']['url'] }}" target="_blank" rel="noopener" class="small text-decoration-none">{{ $asset['file']['name'] ?: __('View file') }}</a>
                                                    @else
                                                        <div class="small text-secondary">{{ __('Not uploaded yet') }}</div>
                                                    @endif
                                                </div>
                                                @if ($canUpload)
                                                    <div class="col-12 col-md-auto">
                                                        <form action="{{ route('user.order.nfc.card.asset.upload', [$order->nfc_card_order_id, $asset['key']]) }}"
                                                            method="post" enctype="multipart/form-data" class="js-asset-form">
                                                            @csrf
                                                            <label class="btn {{ $asset['file'] ? 'btn-outline-primary' : 'btn-primary' }} btn-sm mb-0">
                                                                {{ $asset['file'] ? __('Replace') : __('Upload') }}
                                                                <input type="file" name="file" class="d-none js-asset-file"
                                                                    accept=".jpg,.jpeg,.png,.webp,.svg,.pdf,image/jpeg,image/png,image/webp,image/svg+xml,application/pdf">
                                                            </label>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Order Timeline --}}
                <div class="row mt-3 d-print-none">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Order timeline') }}</h3>
                            </div>
                            <div class="card-body">
                                <ul class="steps steps-vertical">
                                    @foreach (array_reverse($timeline) as $index => $entry)
                                        <li class="step-item {{ $index === 0 ? 'active' : '' }}">
                                            <div class="h4 m-0">
                                                @if ($entry['status'] === 'placed')
                                                    {{ __('Order placed') }}
                                                @else
                                                    {{ __(ucwords($entry['status'] === 'printing process begun' ? 'printing' : $entry['status'])) }}
                                                @endif
                                            </div>
                                            <div class="text-secondary small">
                                                {{ $entry['date'] ? formatDateForUser(\Carbon\Carbon::parse($entry['date'])) : '' }}
                                            </div>
                                            @if ($entry['courier'] || $entry['tracking'])
                                                <div class="text-secondary small">
                                                    {{ collect([$entry['courier'], $entry['tracking']])->filter()->implode(' · ') }}
                                                </div>
                                            @endif
                                            @if ($entry['message'])
                                                <div class="mt-1">{{ $entry['message'] }}</div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script>
        "use strict";
        // Image assets: upload as soon as a file is picked
        document.querySelectorAll('.js-asset-file').forEach(function(input) {
            input.addEventListener('change', function() {
                if (!input.files.length) return;
                var label = input.closest('label');
                label.classList.add('disabled');
                label.firstChild.textContent = '{{ __('Uploading…') }} ';
                input.closest('form').submit();
            });
        });
    </script>
@endpush

