@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-orders.css') }}" rel="stylesheet">

    {{-- HTML2PDF CSS --}}
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
@endsection

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
                                    {{ __('Manage Orders') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Store Orders') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Monitor storefront transactions, track package delivery logs, modify order records, and print verified sales invoices.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid d-print-none">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 9v4" />
                                <path d="M12 16v.01" />
                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
                            </svg>
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l5 5l10 -10" />
                            </svg>
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Dynamic AJAX Alerts --}}
                <div id="successMessage" style="display:none;"
                    class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"></div>
                <div id="errorMessage" style="display:none;"
                    class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"></div>

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'orders',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Data Table Container --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <div class="card-body m-0">
                                <table id="ordersTable" class="table card-table table-vcenter text-nowrap table-hover">
                                    <thead>
                                        <tr>
                                            <th class="w-1">{{ __('#') }}</th>
                                            <th>{{ __('Order Date') }}</th>
                                            <th>{{ __('Order ID') }}</th>
                                            <th>{{ __('Delivery Address') }}</th>
                                            <th>{{ __('Delivery Method') }}</th>
                                            <th>{{ __('Order Status') }}</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Payment Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Order Details Modal --}}
    <div class="modal modal-bg fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header border-bottom py-3 d-print-none">
                    <div>
                        <h4 class="modal-title fw-bold mb-0" id="orderModalLabel">{{ __('Order Details') }}</h4>
                        <p class="text-muted small mb-0">{{ __('Complete transaction and customer fulfillment summary') }}
                        </p>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <button type="button"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm"
                            onclick="window.print();">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Print Order') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4">
                    <div class="order-details"></div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-top d-print-none d-flex justify-content-end">
                    <button type="button" class="btn btn-danger px-4"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Receipt Modal --}}
    <div class="modal modal-bg fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header border-bottom py-3 d-print-none">
                    <div>
                        <h4 class="modal-title fw-bold mb-0" id="invoiceModalLabel">{{ __('Invoice Receipt') }}</h4>
                        <p class="text-muted small mb-0">{{ __('Official verified customer billing invoice') }}</p>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <button type="button"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm"
                            onclick="window.print();">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Print') }}</span>
                        </button>
                        <button type="button"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm"
                            id="downloadButton">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                <polyline points="7 11 12 16 17 11" />
                                <line x1="12" y1="4" x2="12" y2="16" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Download PDF') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4">
                    <div id="viewInvoice" class="invoice-details"></div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer border-top d-print-none d-flex justify-content-end">
                    <button type="button" class="btn btn-danger px-4"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Order Status Modal --}}
    <div class="modal modal-bg fade" id="updateOrderStatusModal" tabindex="-1"
        aria-labelledby="updateOrderStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="updateOrderStatusModalLabel">{{ __('Update Order Status') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required" for="status">{{ __('Select Order Status') }}</label>
                        <select class="form-select" name="status" id="status" required>
                            <option value="" selected disabled>{{ __('Choose status...') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="processing">{{ __('Processing') }}</option>
                            <option value="shipped">{{ __('Shipped') }}</option>
                            <option value="out for delivery">{{ __('Out for Delivery') }}</option>
                            <option value="delivered">{{ __('Delivered') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </select>
                    </div>
                </div>
                {{-- Symmetrical Action Footer --}}
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="updateOrderStatus()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M14 4l0 4l-4 0l0 -4" />
                        </svg>
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Payment Status Modal --}}
    <div class="modal modal-bg fade" id="markAsPaidModal" tabindex="-1" aria-labelledby="markAsPaidModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="markAsPaidModalLabel">{{ __('Update Payment Status') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required" for="payment_status">{{ __('Select Payment Status') }}</label>
                        <select class="form-select" name="payment_status" id="payment_status" required>
                            <option value="" selected disabled>{{ __('Choose payment status...') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="processing">{{ __('Processing') }}</option>
                            <option value="failed">{{ __('Failed') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                            <option value="refunded">{{ __('Refunded') }}</option>
                            <option value="partially_refunded">{{ __('Partially Refunded') }}</option>
                        </select>
                    </div>
                </div>
                {{-- Symmetrical Action Footer --}}
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="markAsPaid()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M14 4l0 4l-4 0l0 -4" />
                        </svg>
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@push('custom-js')
    {{-- Initialize DataTables --}}
    <script>
        $(document).ready(function() {
            "use strict";
            const totalColumns = 9;

            const table = $('#ordersTable').DataTable({
                processing: false,
                serverSide: true,
                dom: "<'d-flex justify-content-end px-3 pt-3 search-wrapper'f>" +
                    "<'table-responsive px-3 pt-2 py-2'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: {
                    url: "{{ route('user.store.orders', $business_card->card_id) }}",
                    dataSrc: 'data'
                },
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "sSearchPlaceholder": `{{ __('Search orders...') }}`,
                    "oPaginate": {
                        "sNext": `{{ __('Next') }}`,
                        "sPrevious": `{{ __('Previous') }}`
                    },
                    "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,
                    emptyTable: `{{ __('No orders recorded yet.') }}`
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'rc-hide'
                    },
                    {
                        data: 'order_date',
                        name: 'order_date'
                    },
                    {
                        data: 'order_id',
                        name: 'order_id'
                    },
                    {
                        data: 'delivery_address',
                        name: 'delivery_address'
                    },
                    {
                        data: 'delivery_method',
                        name: 'delivery_method'
                    },
                    {
                        data: 'order_status',
                        name: 'order_status'
                    },
                    {
                        data: 'order_total',
                        name: 'order_total'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                preDrawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var $tbody = $(settings.nTable).find('tbody');
                    $tbody.closest('.table-responsive').addClass('placeholder-glow');

                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(totalColumns) +
                            '</tr>';
                    }
                    $tbody.html(placeholderRows);
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', totalColumns);

                    var headers = [];
                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $table.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            var label = headers[index] || '';
                            $(this).attr('data-label', label);
                        });
                    });
                }
            });

            // Handle processing animation
            $('#ordersTable').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });
        });

        // Search debounce binding
        var orderSearchTimer;
        $(document).on('input', '#ordersTable_filter input', function() {
            var value = this.value;
            var table = $('#ordersTable').DataTable();
            clearTimeout(orderSearchTimer);
            orderSearchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });

        // Render Order Details Modal with Variant Badges
        function viewOrder(orderId) {
            "use strict";
            var url = '{{ route('user.view.order', ':id') }}'.replace(':id', orderId);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#orderModal').modal('show');
                        var order = response.data;

                        let orderDate = new Date(order.created_at);
                        let formattedDate = orderDate.toLocaleString('en-US', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        var delivery = JSON.parse(order.delivery_details || '{}');

                        // Parse order items safely (supports array or object with items key)
                        var parsedItems = JSON.parse(order.order_item || '[]');
                        var itemsList = Array.isArray(parsedItems) ? parsedItems : (parsedItems.items || []);

                        var html = `
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="receipt-info-block">
                                <div class="receipt-info-title">{{ __('Order Summary') }}</div>
                                <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Order ID:') }}</span> <strong class="text-primary">${order.order_number}</strong></div>
                                <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Date:') }}</span> <strong>${formattedDate}</strong></div>
                                <div class="d-flex justify-content-between mb-1"><span class="text-muted">{{ __('Payment Status:') }}</span> <span class="badge bg-success-lt text-capitalize">${order.payment_status}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">{{ __('Order Status:') }}</span> <span class="badge bg-primary-lt text-capitalize">${order.order_status}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="receipt-info-block">
                                <div class="receipt-info-title">{{ __('Customer & Delivery Details') }}</div>
                                <div class="fw-bold mb-1 fs-3">${delivery.name || 'N/A'}</div>
                                <div class="text-muted mb-1"><a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(delivery.address || '')}" target="_blank" class="text-decoration-none text-reset">📍 ${delivery.address || 'N/A'}</a></div>
                                <div class="mb-1"><a href="tel:${delivery.mobile}" class="text-primary text-decoration-none">📞 ${delivery.mobile || 'N/A'}</a></div>
                                ${delivery.notes ? `<div class="small text-muted fst-italic">"${delivery.notes}"</div>` : ''}
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="fw-bold mb-2">{{ __('Purchased Items') }}</h4>
                        <div class="table-responsive border rounded">
                            <table class="table card-table table-vcenter table-hover">
                                <thead class="bg-surface-secondary">
                                    <tr>
                                        <th class="w-1">{{ __('#') }}</th>
                                        <th>{{ __('Product Name') }}</th>
                                        <th class="text-center">{{ __('Unit Price') }}</th>
                                        <th class="text-center">{{ __('Qty') }}</th>
                                        <th class="text-end">{{ __('Line Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsList.map((item, i) => {
                                        var qty = item.quantity || item.qty || 1;
                                        var variantBadgeHtml = item.variant_name ? `
                                                <div class="mt-1">
                                                    <span class="badge bg-indigo-lt border border-indigo-200" style="font-size: 0.72rem; padding: 2px 7px;">
                                                        <i class="ti ti-color-swatch me-1"></i>${item.variant_name}
                                                    </span>
                                                </div>
                                            ` : '';

                                        return `
                                                <tr>
                                                    <td>${i + 1}</td>
                                                    <td>
                                                        <span class="fw-bold text-dark">${item.product_name}</span>
                                                        ${variantBadgeHtml}
                                                    </td>
                                                    <td class="text-center">{{ $currency }}${Number(item.price).toFixed(2)}</td>
                                                    <td class="text-center">${qty}</td>
                                                    <td class="text-end fw-bold">{{ $currency }}${(item.price * qty).toFixed(2)}</td>
                                                </tr>
                                            `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="receipt-summary-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('Payment Method:') }}</span>
                                    <strong class="text-capitalize">${order.payment_method}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('Delivery Option:') }}</span>
                                    <strong class="text-capitalize">${order.delivery_method}</strong>
                                </div>
                                <hr class="my-2 border-translucent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-3 fw-bold">{{ __('Total Amount:') }}</span>
                                    <span class="fs-2 fw-bolder text-primary">{{ $currency }}${order.order_total}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                        $('#orderModal').find('.order-details').html(html);
                    } else {
                        $('#errorMessage').text("{{ __('Failed to fetch order data.') }}").show().delay(2500)
                            .fadeOut(500);
                    }
                },
                error: function() {
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show().delay(2500).fadeOut(
                        500);
                }
            });
        }

        // Render Invoice Receipt Modal with Variant Badges
        function viewInvoice(orderId) {
            "use strict";
            var url = '{{ route('user.store.order.invoice', ':id') }}'.replace(':id', orderId);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#invoiceModal').modal('show');
                        var invoice = response.data;

                        let invoiceDate = new Date(invoice.created_at);
                        let formattedDate = invoiceDate.toLocaleString('en-US', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        var delivery = JSON.parse(invoice.delivery_details || '{}');

                        // Parse invoice items safely
                        var parsedInvoiceItems = JSON.parse(invoice.order_item || '[]');
                        var invoiceItemsList = Array.isArray(parsedInvoiceItems) ? parsedInvoiceItems : (
                            parsedInvoiceItems.items || []);

                        var html = `
                    <div class="p-3 bg-surface rounded">
                        <div class="row align-items-center mb-4">
                            <div class="col-sm-6">
                                <div class="display-6 fw-bold text-primary">{{ __('INVOICE') }}</div>
                                <div class="text-muted small">{{ __('Invoice No:') }} <strong>${invoice.invoice_prefix}${invoice.invoice_number}</strong></div>
                            </div>
                            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                                <div class="text-muted small">{{ __('Issued Date:') }} <strong>${formattedDate}</strong></div>
                                <div class="text-muted small">{{ __('Delivery Type:') }} <span class="badge bg-primary-lt text-capitalize">${invoice.delivery_method}</span></div>
                            </div>
                        </div>

                        <hr class="my-3 border-translucent">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="receipt-info-title">{{ __('Billed To') }}</div>
                                <div class="fw-bold fs-3 mb-1">${delivery.name || 'Customer'}</div>
                                <div class="text-muted small mb-1">${delivery.address || ''}</div>
                                <div class="small text-primary">${delivery.mobile || ''}</div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="receipt-info-title">{{ __('Payment Details') }}</div>
                                <div class="fw-bold text-capitalize">${invoice.payment_method}</div>
                                <div class="badge bg-success-lt mt-1 text-capitalize">{{ __('Paid / Verified') }}</div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded mb-4">
                            <table class="table card-table table-vcenter">
                                <thead class="bg-surface-secondary">
                                    <tr>
                                        <th class="w-1">{{ __('#') }}</th>
                                        <th>{{ __('Item Description') }}</th>
                                        <th class="text-center">{{ __('Price') }}</th>
                                        <th class="text-center">{{ __('Quantity') }}</th>
                                        <th class="text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${invoiceItemsList.map((item, i) => {
                                        var qty = item.quantity || item.qty || 1;
                                        var variantBadgeHtml = item.variant_name ? `
                                                <div class="mt-1">
                                                    <span class="badge bg-indigo-lt border border-indigo-200" style="font-size: 0.72rem; padding: 2px 7px;">
                                                        <i class="ti ti-color-swatch me-1"></i>${item.variant_name}
                                                    </span>
                                                </div>
                                            ` : '';

                                        return `
                                                <tr>
                                                    <td>${i + 1}</td>
                                                    <td>
                                                        <span class="fw-bold text-dark">${item.product_name}</span>
                                                        ${variantBadgeHtml}
                                                    </td>
                                                    <td class="text-center">{{ $currency }}${Number(item.price).toFixed(2)}</td>
                                                    <td class="text-center">${qty}</td>
                                                    <td class="text-end fw-bold">{{ $currency }}${(item.price * qty).toFixed(2)}</td>
                                                </tr>
                                            `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="receipt-summary-box">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('Subtotal:') }}</span>
                                        <strong>{{ $currency }}${invoice.order_total}</strong>
                                    </div>
                                    <hr class="my-2 border-translucent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-3 fw-bold">{{ __('Grand Total:') }}</span>
                                        <span class="fs-2 fw-bolder text-primary">{{ $currency }}${invoice.order_total}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                        $('#invoiceModal').find('.invoice-details').html(html);
                        $('#downloadButton').attr('onclick',
                            `generatePDF('${invoice.invoice_prefix}', '${invoice.invoice_number}')`);
                    } else {
                        $('#errorMessage').text("{{ __('Failed to fetch invoice data.') }}").show().delay(
                            2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show().delay(2500).fadeOut(
                        500);
                }
            });
        }

        // Status Modal Event Linkings
        $('#updateOrderStatusModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var orderId = button.data('order-id');
            $(this).data('order-id', orderId);
        });

        function updateOrderStatus() {
            "use strict";
            var orderId = $('#updateOrderStatusModal').data('order-id');
            var orderStatus = $('#status').val();

            if (!orderStatus) {
                $('#updateOrderStatusModal').modal('hide');
                $('#errorMessage').text("{{ __('Please select order status.') }}").show();
                $('#errorMessage').delay(2500).fadeOut(500);
                return;
            }

            var url = '{{ route('user.store.order.update', ':id') }}'.replace(':id', orderId);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: orderStatus
                },
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('Order updated successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#updateOrderStatusModal').modal('hide');
                        $('#ordersTable').DataTable().ajax.reload(null, false);

                        if (response.whatsapp_url) {
                            window.open(response.whatsapp_url, '_blank');
                        }
                    } else {
                        $('#updateOrderStatusModal').modal('hide');
                        $('#errorMessage').text(response.message || "{{ __('Failed to update order.') }}")
                            .show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#updateOrderStatusModal').modal('hide');
                    $('#errorMessage').text("{{ __('Failed to update order.') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        $('#markAsPaidModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var orderId = button.data('order-id');
            $(this).data('order-id', orderId);
        });

        function markAsPaid() {
            "use strict";
            var orderId = $('#markAsPaidModal').data('order-id');
            var paymentStatus = $('#payment_status').val();

            if (!paymentStatus) {
                $('#markAsPaidModal').modal('hide');
                $('#errorMessage').text("{{ __('Please select payment status.') }}").show();
                $('#errorMessage').delay(2500).fadeOut(500);
                return;
            }

            var url = '{{ route('user.store.order.mark.as.paid', ':id') }}'.replace(':id', orderId);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payment_status: paymentStatus
                },
                success: function(response) {
                    if (response.success) {
                        $('#markAsPaidModal').modal('hide');
                        $('#successMessage').text("{{ __('Payment status updated successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#ordersTable').DataTable().ajax.reload(null, false);
                    } else {
                        $('#markAsPaidModal').modal('hide');
                        $('#errorMessage').text(response.message ||
                            "{{ __('Failed to update order status.') }}").show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#markAsPaidModal').modal('hide');
                    $('#errorMessage').text("{{ __('Failed to update order status.') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Invoice PDF Generator
        function generatePDF(prefix, number) {
            "use strict";
            const element = document.getElementById('viewInvoice');
            html2pdf()
                .set({
                    margin: [10, 10, 10, 10],
                    filename: `${prefix}${number}.pdf`,
                    html2canvas: {
                        scale: 3
                    },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait'
                    }
                })
                .from(element)
                .save();
        }
    </script>
@endpush
