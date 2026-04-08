@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
<script src="{{ asset('js/html2pdf.bundle.min.js')}}"></script>
@endsection

@section('content')
<div class="page-wrapper">
    <!-- Page title -->
    <div class="page-header d-print-none">
        <div class="container-fluid">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Orders') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid d-print-none">
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

            {{-- Success of modal --}}
            <div id="successMessage" style="display:none;"
                class="alert alert-important alert-success alert-dismissible mb-2">
            </div>

            {{-- Failed of modal --}}
            <div id="errorMessage" style="display:none;"
                class="alert alert-important alert-danger alert-dismissible mb-2">
            </div>

            <div class="card">
                <div class="row g-0">
                    <div class="col-12 col-md-2 border-end">
                        <div class="card-body">
                            <h4 class="subheader">{{ __('Manage Orders') }}</h4>
                            <div class="list-group list-group-transparent">
                                {{-- Nav links --}}
                                @include('user.pages.edit-store.include.nav-link', ['link' => 'orders'])
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-10 d-flex flex-column">
                        <div class="card-body m-0 py-0 px-2">
                            {{-- Orders --}}
                            <div class="row">
                                <div class="table-responsive">
                                    <table id="ordersTable" class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('#') }}</th>
                                                <th>{{ __('Order Date') }}</th>
                                                <th>{{ __('Order ID') }}</th>
                                                <th>{{ __('Delivery Address') }}</th>
                                                <th>{{ __('Delivery Method') }}</th>
                                                <th>{{ __('Order Status') }}</th>
                                                <th>{{ __('Total') }}</th>
                                                <th>{{ __('Payment Status') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Modal --}}
        <div class="modal fade modal-blur" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="orderModalLabel">{{ __("Order Details") }}</h5>
                        <div class="col-auto ms-auto d-print-none">
                            <button type="button" class="btn btn-primary btn-icon" onclick="javascript:window.print();">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-printer"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                            </button>
                            {{-- Close --}}
                            <button type="button" class="btn btn-primary btn-icon" data-bs-dismiss="modal">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="order-details"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Modal --}}
        <div class="modal fade modal-blur" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="invoiceModalLabel">{{ __("Invoice Details") }}</h5>
                        <div class="col-auto ms-auto d-print-none">
                            {{-- Print --}}
                            <button type="button" class="btn btn-primary btn-icon" onclick="javascript:window.print();">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-printer"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                            </button>
                            {{-- Download --}}
                            <button type="button" class="btn btn-primary btn-icon" id="downloadButton" onclick="generatePDF()">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                            </button>
                            {{-- Close --}}
                            <button type="button" class="btn btn-primary btn-icon" data-bs-dismiss="modal">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="invoice-details" id="viewInvoice"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Update order status modal --}}
        <div class="modal fade modal-blur" id="updateOrderStatusModal" tabindex="-1" aria-labelledby="updateOrderStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateOrderStatusModalLabel">{{ __("Update Order Status") }}</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label required">{{ __("Order Status") }}</label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option value="" selected disabled>{{ __("Select Order Status") }}</option>
                                        <option value="pending">{{ __("Pending") }}</option>
                                        <option value="processing">{{ __("Processing") }}</option>
                                        <option value="shipped">{{ __("Shipped") }}</option>
                                        <option value="out for delivery">{{ __("Out for Delivery") }}</option>
                                        <option value="delivered">{{ __("Delivered") }}</option>
                                        <option value="cancelled">{{ __("Cancelled") }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- Close --}}
                        <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">
                            {{ __("Close") }}
                        </button>
                        {{-- Update --}}
                        <button type="button" class="btn btn-primary" onclick="updateOrderStatus()">
                            {{ __("Update") }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mark as paid modal --}}
        <div class="modal fade modal-blur" id="markAsPaidModal" tabindex="-1" aria-labelledby="markAsPaidModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="markAsPaidModalLabel">{{ __("Update Payment Status") }}</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label required">{{ __("Payment Status") }}</label>
                                    <select class="form-select" name="payment_status" id="payment_status" required>
                                        <option value="" selected disabled>{{ __("Select Payment Status") }}</option>
                                        <option value="paid">{{ __("Paid") }}</option>
                                        <option value="failed">{{ __("Failed") }}</option>
                                        <option value="pending">{{ __("Pending") }}</option>
                                        <option value="processing">{{ __("Processing") }}</option>
                                        <option value="cancelled">{{ __("Cancelled") }}</option>
                                        <option value="refunded">{{ __("Refunded") }}</option>
                                        <option value="partially_refunded">{{ __("Partially Refunded") }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">
                            {{ __("Close") }}
                        </button>
                        <button type="button" class="btn btn-primary" onclick="markAsPaid()">
                            {{ __("Update") }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
</div>

{{-- Custom JS --}}
@push('custom-js')
    <!-- Initialize DataTables -->
    <script>
        // Get products
        $(document).ready(function() {
            "use strict";
            $('#ordersTable').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('user.store.orders', $business_card->card_id) }}", // Replace with your actual API endpoint
                    dataSrc: 'data' // If your data is nested under a key 'data' in the response
                },
                language: {
                    "sProcessing": `{{ __("Processing...") }}`,
                    "sLengthMenu": `{{ __("Show _MENU_ entries") }}`,
                    "sSearch": `{{ __("Search:") }}`,
                    "oPaginate": {
                        "sNext": `{{ __("Next") }}`,
                        "sPrevious": `{{ __("Previous") }}`
                    },
                    "sInfo": `{{ __("Showing _START_ to _END_ of _TOTAL_ entries") }}`,
                    "sInfoEmpty": `{{ __("Showing 0 to 0 of 0 entries") }}`,
                    "sInfoFiltered": `{{ __("(filtered from _MAX_ total entries)") }}`,
                    "sInfoPostFix": "",
                    "sUrl": "",
                    "oAria": {
                        "sSortAscending": `{{ __(": activate to sort column in ascending order") }}`,
                        "sSortDescending": `{{ __(": activate to sort column in descending order") }}`
                    },
                    loadingRecords: `{{ __("Please wait - loading...") }}`,
                    emptyTable: `{{ __("No data available in the table") }}` // Message for an empty table
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {data: 'order_date', name: 'order_date'},
                    {data: 'order_id', name: 'order_id'},
                    {data: 'delivery_address', name: 'delivery_address'},
                    {data: 'delivery_method', name: 'delivery_method'},
                    {data: 'order_status', name: 'order_status'},
                    {data: 'order_total', name: 'order_total'},
                    {data: 'payment_status', name: 'payment_status'},
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
    <script>
        // View order
        function viewOrder(orderId) {
            "use strict";

            var url = '{{ route("user.view.order", ":id") }}';
            url = url.replace(':id', orderId);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Show order modal
                        $('#orderModal').modal('show');

                        // Display order details to table format
                        var orderDetails = response.data;

                        // Date
                        let orderDate = new Date(orderDetails.created_at);
                        let options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                        let formattedDate = orderDate.toLocaleString('en-US', options);
                                                
                        var orderDetailsHtml = '<div class="datagrid">';
                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title fw-bold"><strong>' + '{{ __("Order Date") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold">' + formattedDate + '</div>';
                        orderDetailsHtml += '</div>';

                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title fw-bold"><strong>' + '{{ __("Order ID") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold">' + orderDetails.order_number + '</div>';
                        orderDetailsHtml += '</div>';

                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Delivery Method") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content"><span class="badge bg-primary text-white text-capitalize">' + orderDetails.delivery_method + '</span></div>';
                        orderDetailsHtml += '</div>';

                       var deliveryDetails = JSON.parse(orderDetails.delivery_details);

                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Delivery Address") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize">' + deliveryDetails.name + '</div>';
                            orderDetailsHtml += '<div class="datagrid-content"><a href="https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(deliveryDetails.address) + '" target="_blank">' + deliveryDetails.address + '</a></div>';
                            orderDetailsHtml += '<div class="datagrid-content"><a href="tel:' + deliveryDetails.mobile + '" target="_blank">' + deliveryDetails.mobile + '</a></div>';

                            if (deliveryDetails.notes) {
                                orderDetailsHtml += '<div class="datagrid-content">' + deliveryDetails.notes + '</div>';
                            }

                        orderDetailsHtml += '</div>';

                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Payment Method") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize">' + orderDetails.payment_method + '</div>';
                        orderDetailsHtml += '</div>';
                        
                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Payment Status") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize"><span class="badge bg-success text-white text-capitalize">' + orderDetails.payment_status + '</span></div>';
                        orderDetailsHtml += '</div>';

                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Order Status") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize"><span class="badge bg-success text-white text-capitalize">' + orderDetails.order_status + '</span></div>';
                        orderDetailsHtml += '</div>';

                        orderDetailsHtml += '<div class="datagrid-item">';
                            orderDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Total") }}' + '</strong></div>';
                            orderDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize">' + '{{ $currency }}' + orderDetails.order_total + '</div>';
                        orderDetailsHtml += '</div>';
                        orderDetailsHtml += '</div>';

                        // Items (orderDetails.order_item) for each order
                        orderDetailsHtml += '<div class="row mt-5">';
                            orderDetailsHtml += '<div class="col-md-12">';
                                orderDetailsHtml += '<h4 class="card-title">{{ __("Order Items") }}</h4>';
                                orderDetailsHtml += '<div class="table-responsive">';
                                    orderDetailsHtml += '<table class="table table-vcenter table-mobile-md card-table">';
                                        orderDetailsHtml += '<thead>';
                                            orderDetailsHtml += '<tr>';
                                                orderDetailsHtml += '<th>{{ __('#') }}</th>';
                                                orderDetailsHtml += '<th>{{ __('Product Name') }}</th>';
                                                orderDetailsHtml += '<th>{{ __('Price') }}</th>';
                                                orderDetailsHtml += '<th>{{ __('Quantity') }}</th>';
                                                orderDetailsHtml += '<th>{{ __('Total') }}</th>';
                                            orderDetailsHtml += '</tr>';
                                        orderDetailsHtml += '</thead>';
                                        orderDetailsHtml += '<tbody>';
                                            let orderItems = JSON.parse(orderDetails.order_item);

                                            orderItems.items.map((item, index) => {
                                                orderDetailsHtml += '<tr>';
                                                    orderDetailsHtml += '<td data-label="#">' + (index + 1) + '</td>';
                                                    orderDetailsHtml += '<td data-label="Product Name">' + item.product_name + '</td>';
                                                    orderDetailsHtml += '<td data-label="Price">' + '{{ $currency }}' + item.price + '</td>';
                                                    orderDetailsHtml += '<td data-label="Quantity">' + item.quantity + '</td>';
                                                    orderDetailsHtml += '<td data-label="Total">' + '{{ $currency }}' + item.price * item.quantity + '</td>';
                                                orderDetailsHtml += '</tr>';
                                            });
                                        orderDetailsHtml += '</tbody>';
                                    orderDetailsHtml += '</table>';
                                orderDetailsHtml += '</div>';
                            orderDetailsHtml += '</div>';
                        orderDetailsHtml += '</div>';

                        // Display order details
                        $('#orderModal').find('.order-details').html(orderDetailsHtml);
                    } else {
                        // Display error message
                        $('#errorMessage').text("{{ __('Failed to fetch order data.') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                },
                error: function(xhr, status, error) {
                    // Display error message
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(1000).fadeOut(1000);
                }
            });
        }

        // View invoice
        function viewInvoice(orderId) {
            "use strict";

            var url = '{{ route("user.store.order.invoice", ":id") }}';
            url = url.replace(':id', orderId);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#invoiceModal').modal('show');
                        
                        // Display invoice details to table format
                        var invoiceDetails = response.data;

                        // Date
                        let invoiceDate = new Date(invoiceDetails.created_at);
                        let options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                        let formattedDate = invoiceDate.toLocaleString('en-US', options);
                                                
                        var invoiceDetailsHtml = '<div class="datagrid">';
                        invoiceDetailsHtml += '<div class="datagrid-item">';
                            invoiceDetailsHtml += '<div class="datagrid-title fw-bold"><strong>' + '{{ __("Invoice Date") }}' + '</strong></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content fw-bold">' + formattedDate + '</div>';
                        invoiceDetailsHtml += '</div>';

                        invoiceDetailsHtml += '<div class="datagrid-item">';
                            invoiceDetailsHtml += '<div class="datagrid-title fw-bold"><strong>' + '{{ __("Invoice ID") }}' + '</strong></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content fw-bold">' + invoiceDetails.invoice_prefix + '' + invoiceDetails.invoice_number + '</div>';
                        invoiceDetailsHtml += '</div>';

                        invoiceDetailsHtml += '<div class="datagrid-item">';
                            invoiceDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Delivery Method") }}' + '</strong></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content"><span class="badge bg-primary text-white text-capitalize">' + invoiceDetails.delivery_method + '</span></div>';
                        invoiceDetailsHtml += '</div>';

                       var deliveryDetails = JSON.parse(invoiceDetails.delivery_details);

                        invoiceDetailsHtml += '<div class="datagrid-item">';
                            invoiceDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Delivery Address") }}' + '</strong></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize">' + deliveryDetails.name + '</div>';
                            invoiceDetailsHtml += '<div class="datagrid-content"><a href="https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(deliveryDetails.address) + '" target="_blank">' + deliveryDetails.address + '</a></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content"><a href="tel:' + deliveryDetails.mobile + '" target="_blank">' + deliveryDetails.mobile + '</a></div>';

                            if (deliveryDetails.notes) {
                                invoiceDetailsHtml += '<div class="datagrid-content">' + deliveryDetails.notes + '</div>';
                            }

                        invoiceDetailsHtml += '</div>';

                        invoiceDetailsHtml += '<div class="datagrid-item">';
                            invoiceDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Payment Method") }}' + '</strong></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize">' + invoiceDetails.payment_method + '</div>';
                        invoiceDetailsHtml += '</div>';

                        invoiceDetailsHtml += '<div class="datagrid-item">';
                            invoiceDetailsHtml += '<div class="datagrid-title"><strong>' + '{{ __("Total") }}' + '</strong></div>';
                            invoiceDetailsHtml += '<div class="datagrid-content fw-bold text-capitalize">' + '{{ $currency }}' + invoiceDetails.order_total + '</div>';
                        invoiceDetailsHtml += '</div>';
                        invoiceDetailsHtml += '</div>';

                        // Items (invoiceDetails.invoice_item) for each invoice
                        invoiceDetailsHtml += '<div class="row mt-5">';
                            invoiceDetailsHtml += '<div class="col-md-12">';
                                invoiceDetailsHtml += '<h4 class="card-title">{{ __("Invoice Items") }}</h4>';
                                invoiceDetailsHtml += '<div class="table-responsive">';
                                    invoiceDetailsHtml += '<table class="table table-vcenter table-mobile-md card-table">';
                                        invoiceDetailsHtml += '<thead>';
                                            invoiceDetailsHtml += '<tr>';
                                                invoiceDetailsHtml += '<th>{{ __('#') }}</th>';
                                                invoiceDetailsHtml += '<th>{{ __('Product Name') }}</th>';
                                                invoiceDetailsHtml += '<th>{{ __('Price') }}</th>';
                                                invoiceDetailsHtml += '<th>{{ __('Quantity') }}</th>';
                                                invoiceDetailsHtml += '<th>{{ __('Total') }}</th>';
                                            invoiceDetailsHtml += '</tr>';
                                        invoiceDetailsHtml += '</thead>';
                                        invoiceDetailsHtml += '<tbody>';
                                            let invoiceItems = JSON.parse(invoiceDetails.order_item);

                                            invoiceItems.items.map((item, index) => {
                                                invoiceDetailsHtml += '<tr>';
                                                    invoiceDetailsHtml += '<td data-label="#">' + (index + 1) + '</td>';
                                                    invoiceDetailsHtml += '<td data-label="Product Name">' + item.product_name + '</td>';
                                                    invoiceDetailsHtml += '<td data-label="Price">' + '{{ $currency }}' + item.price + '</td>';
                                                    invoiceDetailsHtml += '<td data-label="Quantity">' + item.quantity + '</td>';
                                                    invoiceDetailsHtml += '<td data-label="Total">' + '{{ $currency }}' + item.price * item.quantity + '</td>';
                                                invoiceDetailsHtml += '</tr>';
                                            });
                                        invoiceDetailsHtml += '</tbody>';
                                    invoiceDetailsHtml += '</table>';
                                invoiceDetailsHtml += '</div>';
                            invoiceDetailsHtml += '</div>';
                        invoiceDetailsHtml += '</div>';

                        // Display invoice details
                        $('#invoiceModal').find('.invoice-details').html(invoiceDetailsHtml);

                        // Invoice number
                        var invoicePrefix = invoiceDetails.invoice_prefix;
                        var invoiceNumber = invoiceDetails.invoice_number;

                        // Set invoice prefix and invoice number in generate PDF button in download button
                        $('#downloadButton').attr('onclick', `generatePDF('${invoicePrefix}', '${invoiceNumber}')`);
                    } else {
                        // Display error message
                        $('#errorMessage').text("{{ __('Failed to fetch invoice data.') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                },
                error: function(xhr, status, error) {
                    // Display error message
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(1000).fadeOut(1000);
                }
            });
        }

        $('#updateOrderStatusModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // The clicked link
            var orderId = button.data('order-id'); // Get the data-order-id from the link

            // Optional: Store it in the modal for later use
            $(this).data('order-id', orderId);
        });

        // Update order status
        function updateOrderStatus() {
            "use strict";

            // Get order id from data attribute
            var orderId = $('#updateOrderStatusModal').data('order-id');

            // Get order status from select
            var orderStatus = $('#status').val();

            var url = '{{ route("user.store.order.update", [":id"]) }}';
            url = url.replace(':id', orderId);

            // Check order status
            if (orderStatus == '' || orderStatus == null) {
                $('#updateOrderStatusModal').modal('hide');
                $('#errorMessage').text("{{ __('Please select order status.') }}").show();
                $('#errorMessage').delay(1000).fadeOut(1000);
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: orderStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Display success message
                        $('#successMessage').text("{{ __('Order updated successfully!') }}").show();
                        $('#successMessage').delay(1000).fadeOut(1000);
                        $('#updateOrderStatusModal').modal('hide');

                        // Reload order table
                        $('#ordersTable').DataTable().ajax.reload();

                        // Open WhatsApp in new tab if URL exists
                        if (response.whatsapp_url) {
                            window.open(response.whatsapp_url, '_blank');
                        }
                    } else {
                        // Display error message
                        $('#updateOrderStatusModal').modal('hide');
                        $('#errorMessage').text("{{ __('Failed to update order.') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                },
                error: function(xhr, status, error) {
                    // Display error message
                    $('#updateOrderStatusModal').modal('hide');
                    $('#errorMessage').text("{{ __('Failed to update order.') }}").show();
                    $('#errorMessage').delay(1000).fadeOut(1000);
                }
            });
        }

        $('#markAsPaidModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // The clicked link
            var orderId = button.data('order-id'); // Get the data-order-id from the link

            // Optional: Store it in the modal for later use
            $(this).data('order-id', orderId);
        });

        // Mark as paid
        function markAsPaid() {
            "use strict";

            // Get order id from data attribute
            var orderId = $('#markAsPaidModal').data('order-id');

            // Get payment status from select
            var paymentStatus = $('#payment_status').val();

            var url = '{{ route("user.store.order.mark.as.paid", [":id"]) }}';
            url = url.replace(':id', orderId);

            // Check payment status
            if (paymentStatus == '' || paymentStatus == null) {
                $('#markAsPaidModal').modal('hide');
                $('#errorMessage').text("{{ __('Please select payment status.') }}").show();
                $('#errorMessage').delay(1000).fadeOut(1000);
                return;
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    payment_status: paymentStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Display success message
                        $('#markAsPaidModal').modal('hide');
                        $('#successMessage').text("{{ __('Payment status updated successfully!') }}").show();
                        $('#successMessage').delay(1000).fadeOut(1000);
                        $('#updateOrderStatusModal').modal('hide');

                        // Reload order table
                        $('#ordersTable').DataTable().ajax.reload();
                    } else {
                        // Display error message
                        $('#markAsPaidModal').modal('hide');
                        $('#errorMessage').text("{{ __('Failed to update order status.') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                },
                error: function(xhr, status, error) {
                    // Display error message
                    $('#markAsPaidModal').modal('hide');
                    $('#errorMessage').text("{{ __('Failed to update order status.') }}").show();
                    $('#errorMessage').delay(1000).fadeOut(1000);
                }
            });
        }

        // Generate PDF
        function generatePDF(prefix, number) {
            "use strict";

            const element = document.getElementById('viewInvoice');
            html2pdf()
                .set({
                    margin: [10, 10, 10, 10], // top, left, bottom, right
                    filename: `${prefix}${number}.pdf`,
                    html2canvas: { scale: 4 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                })
                .from(element)
                .save();
        }
    </script>
@endpush
@endsection
