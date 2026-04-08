@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
<style>
    @media (max-width: 768px) {
        .table-responsive {
            overflow: initial;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
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
                            {{ __('My Orders') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
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

                <div class="row row-deck row-cards">
                    <div class="col-sm-12 col-lg-12">
                        <div class="card card-table">
                            <div class="table-responsive">
                                <table class="table table-vcenter" id="nfc-card-order-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Order Date') }}</th>
                                            <th>{{ __('Activation Code') }}</th>
                                            <th>{{ __('Order ID') }}</th>
                                            <th>{{ __('Attachments') }}</th>
                                            <th>{{ __('Item') }}</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Payment Status') }}</th>
                                            <th>{{ __('Delivery Status') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                </table>
                                <div id="nfcOrderCards" class="mobile-cards p-3"></div>

                                <div class="d-flex justify-content-center">
                                    <div id="nfcOrderPagination" class="pagination-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('user.includes.footer')
    </div>

    {{-- Custom scripts --}}
    @section('scripts')
        <script type="text/javascript">
$(document).ready(function() {

    const table = $('#nfc-card-order-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('user.manage.nfc.orders') }}",

        language: {
            sProcessing: `{{ __('Processing...') }}`,
            sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
            sSearch: `{{ __('Search:') }}`,
            oPaginate: {
                sNext: `{{ __('Next') }}`,
                sPrevious: `{{ __('Previous') }}`
            },
            sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
            sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
            sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
            loadingRecords: `{{ __('Please wait - loading...') }}`,
            emptyTable: `{{ __('No data available in the table') }}`
        },

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'order_details', name: 'order_details' },
            { data: 'nfc_card_order_id', name: 'nfc_card_order_id' },
            { data: 'nfc_card_logo', name: 'nfc_card_logo' },
            { data: 'nfc_card_name', name: 'nfc_card_name' },
            { data: 'nfc_card_price', name: 'nfc_card_price' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'delivery_status', name: 'delivery_status' },
            { data: 'action', name: 'action', searchable: false, orderable: false }
        ],

        preDrawCallback: function() {

            const width = $(window).width();

            if (width < 768) {

                $('#nfc-card-order-table_wrapper').hide();
                $('#nfcOrderCards').empty().addClass('placeholder-glow');

                // Horizontal mobile placeholders
                const placeholderCards = Array(6).fill().map(() => `
                    <div class="d-flex mb-3 p-3 border rounded-4 shadow-sm">

                        <div class="me-3">
                            <div class="placeholder mb-2" style="width:60px; height:60px; border-radius:12px;"></div>
                        </div>

                        <div class="flex-grow-1">
                            <div class="placeholder col-7 mb-2"></div>
                            <div class="placeholder col-5 mb-2"></div>

                            <div class="d-flex justify-content-between">
                                <div class="placeholder col-4"></div>
                                <div class="placeholder col-3"></div>
                            </div>
                        </div>

                    </div>
                `).join('');

                $('#nfcOrderCards').html(placeholderCards);

            } else {

                $('#nfc-card-order-table_wrapper').addClass('placeholder-glow');

                if ($('#nfc-card-order-table tbody tr').length === 0) {
                    let rows = '';
                    for (let i = 0; i < 6; i++) {
                        rows += '<tr>' +
                            '<td><div class="placeholder placeholder-xs col-12"></div></td>'.repeat(10) +
                        '</tr>';
                    }
                    $('#nfc-card-order-table tbody').html(rows);
                }
            }
        },

        drawCallback: function() {

            $('#nfc-card-order-table_wrapper').removeClass('placeholder-glow');
            $('#nfcOrderCards').removeClass('placeholder-glow');

            $('#nfcOrderCards').empty();
            toggleMobileView();
        }
    });

    // Switch to card view on mobile
    function toggleMobileView() {

        const width = $(window).width();

        if (width < 768) {

            $('#nfc-card-order-table_wrapper').hide();
            $('#nfcOrderCards').show().empty();

            // ✅ NO DATA CASE
            if (table.rows({ page: 'current' }).count() === 0) {
                $('#nfcOrderCards').html(`
                    <div class="text-center py-6 text-muted">
                        <h5>{{ __('No orders found') }}</h5>
                    </div>
                `);
                return;
            }

            table.rows({ page: 'current' }).every(function() {
                const data = this.data();

                $('#nfcOrderCards').append(`
                    <div class="d-flex mb-3 p-3 border rounded-4 shadow-sm">

                        <div class="flex-grow-1">

                            <p class="fw-bold mb-1">
                                ${data.nfc_card_name}
                            </p>

                            <p class="fw-bold mb-1">
                                ${data.nfc_card_order_id}
                            </p>

                            <p class="fw-bold mb-1">
                                ${data.order_details}
                            </p>

                            <p class="text-muted fs-5 mb-1">
                                ${data.created_at}
                            </p>

                            <div class="d-flex justify-content-between mb-2">
                                <span>${data.nfc_card_price}</span>
                                ${data.payment_status}
                            </div>

                            <div class="d-flex justify-content-between">
                                ${data.delivery_status}
                                <div>${data.action}</div>
                            </div>
                        </div>
                    </div>
                `);
            });

            $('.dataTables_paginate').appendTo('#nfcOrderPagination');

        } else {

            $('#nfcOrderCards').hide();
            $('#nfc-card-order-table_wrapper').show();
        }
    }

    $(window).resize(toggleMobileView);

});
</script>
    @endsection
@endsection
