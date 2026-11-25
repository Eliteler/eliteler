@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }

        /* Force empty state table and tbody to span full width */
        #nfc-card-order-table.is-table-empty,
        #nfc-card-order-table.is-table-empty tbody {
            display: block !important;
            width: 100% !important;
        }

        /* Remove default mobile card border and background from empty state row */
        #nfc-card-order-table tbody tr.empty-table-row {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        /* Center empty table content horizontally and vertically */
        #nfc-card-order-table tbody tr.empty-table-row td.dataTables_empty {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            width: 100% !important;
            min-height: 380px !important;
            margin: 0 auto !important;
            border: none !important;
            background: transparent !important;
        }

        /* Hide mobile pseudo-label elements */
        #nfc-card-order-table tbody tr.empty-table-row td.dataTables_empty::before,
        #nfc-card-order-table tbody tr.empty-table-row td.dataTables_empty::after {
            display: none !important;
            content: none !important;
        }

        /* Style NFC empty state icon badge */
        .empty-state-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: rgba(32, 107, 196, 0.12);
            color: #206bc4;
            margin: 0 auto 1rem auto;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('My Orders') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('My Orders') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Track your Smart Product orders from payment to delivery.') }}
                        </div>
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

                {{-- Failed alert --}}
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

                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2 py-2">
                                <!-- Table renders normally on desktop and transforms automatically via CSS on mobile -->
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="nfc-card-order-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Order Date') }}</th>
                                            <th>{{ __('Activation Code') }}</th>
                                            <th>{{ __('Order ID') }}</th>
                                            <th>{{ __('Print files') }}</th>
                                            <th>{{ __('Item') }}</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Payment Status') }}</th>
                                            <th>{{ __('Delivery Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="custom-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="mobile-prev-btn" class="btn btn-primary btn-sm" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>

                                <span id="mobile-page-info" class="text-secondary small font-weight-medium"></span>

                                <button id="mobile-next-btn" class="btn btn-primary btn-sm" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom scripts --}}
@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var table = $('#nfc-card-order-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('user.manage.nfc.orders') }}",
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "sSearchPlaceholder": `{{ __('Search') }}`,
                    "oPaginate": {
                        "sNext": `{{ __('Next') }}`,
                        "sPrevious": `{{ __('Previous') }}`
                    },
                    "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    // Centered Empty State HTML for Orders
                    emptyTable: `
                        <div class="d-flex flex-column align-items-center justify-content-center text-center w-100">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-credit-card">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <rect x="3" y="5" width="18" height="14" rx="3" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                    <line x1="7" y1="15" x2="7.01" y2="15" />
                                    <line x1="11" y1="15" x2="13" y2="15" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Orders Placed Yet') }}</h4>
                            <p class="text-muted small mb-0">{{ __('You have not placed any NFC card orders yet.') }}</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="d-flex flex-column align-items-center justify-content-center text-center w-100">
                            <div class="empty-state-icon" style="background-color: var(--tblr-danger-lt); color: var(--tblr-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-credit-card">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <rect x="3" y="5" width="18" height="14" rx="3" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                    <line x1="7" y1="15" x2="7.01" y2="15" />
                                    <line x1="11" y1="15" x2="13" y2="15" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching Orders Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('We could not find any orders matching your search keyword.') }}</p>
                        </div>
                    `
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'rc-hide'
                    },
                    {
                        data: 'created_at',
                        name: 'nfc_card_orders.created_at',
                        className: 'rc-info-start'
                    },
                    {
                        data: 'order_details',
                        name: 'nfc_card_orders.order_details',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'nfc_card_order_id',
                        name: 'nfc_card_orders.nfc_card_order_id',
                        className: 'rc-heading'
                    },
                    {
                        data: 'nfc_card_logo',
                        name: 'nfc_card_orders.nfc_card_logo',
                        orderable: false,
                        searchable: false,
                        className: 'rc-info-next'
                    },
                    {
                        data: 'nfc_card_name',
                        name: 'nfc_card_designs.nfc_card_name',
                        className: 'rc-heading'
                    },
                    {
                        data: 'nfc_card_price',
                        name: 'nfc_card_designs.nfc_card_price',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'payment_status',
                        name: 'nfc_card_order_transactions.payment_status',
                        className: 'rc-badge-top-right'
                    },
                    {
                        data: 'delivery_status',
                        name: 'nfc_card_orders.order_status',
                        className: 'rc-badge-top-right'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                preDrawCallback: function(settings) {
                    // Add loading skeleton state to wrapper
                    $('#nfc-card-order-table_wrapper').addClass('placeholder-glow');

                    // Desktop Placeholder Rows (Adjusted to match 10 columns)
                    if ($('#nfc-card-order-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 10; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(10) + '</tr>';
                        }
                        $('#nfc-card-order-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    var $table = $(settings.nTable);

                    // Remove loading placeholder glow indicators
                    $('#nfc-card-order-table_wrapper').removeClass('placeholder-glow');

                    // Clear desktop dynamic action placeholders
                    $('#nfc-card-order-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(9);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 10 columns correctly
                    $('#nfc-card-order-table tbody tr td.dataTables_empty').attr('colspan', 10);

                    // Check if table has active rows
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    if (rowsCount === 0) {
                        $table.addClass('is-table-empty');
                        $table.find('tbody tr').addClass('empty-table-row');
                    } else {
                        $table.removeClass('is-table-empty');
                        $table.find('tbody tr').removeClass('empty-table-row');
                    }

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#nfc-card-order-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    // Exclude empty state rows from mobile data labels
                    $('#nfc-card-order-table tbody tr:not(.empty-table-row)').each(function() {
                        $(this).find('td').each(function(index) {
                            if (headers[index]) {
                                $(this).attr('data-label', headers[index]);
                            }
                        });
                    });

                    // Update Custom Mobile Pagination Footer State dynamically
                    const pageInfo = api.page.info();

                    // Hide/show dynamic mobile footer based on pages total and active record count
                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#custom-mobile-pagination').addClass('d-none');
                        $('#custom-mobile-pagination').removeClass('d-flex');
                    } else {
                        $('#custom-mobile-pagination').removeClass('d-none');
                        $('#custom-mobile-pagination').addClass('d-flex');
                    }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#mobile-page-info').text(pageInfoText);

                    // Toggle disabled state on Previous button
                    if (pageInfo.page === 0) {
                        $('#mobile-prev-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-prev-btn').removeClass('disabled').prop('disabled', false);
                    }

                    // Toggle disabled state on Next button
                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#mobile-next-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-next-btn').removeClass('disabled').prop('disabled', false);
                    }
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#nfc-card-order-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 10; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(10) + '</tr>';
                    }
                    $('#nfc-card-order-table tbody').html(placeholderRows);
                }
            });

            // Bind click events on Mobile pagination triggers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                table.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                table.page('next').draw('page');
            });
        });
    </script>
@endsection
