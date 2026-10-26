@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('css')
    <style>
        /* ==========================================================================
                                           DATATABLES GLOBAL VISIBILITY (Desktop & Mobile)
                                           ========================================================================== */
        div.dataTables_wrapper div.dataTables_filter,
        div.dataTables_wrapper div.dataTables_paginate,
        .dataTables_filter,
        .dataTables_paginate,
        .search-wrapper,
        .pagination-wrapper {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .search-wrapper {
            justify-content: flex-end !important;
        }

        /* Style the search inputs to match clean Tabler form controls */
        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 400;
            color: var(--tblr-body-color, #626976);
            margin-bottom: 0;
        }

        .dataTables_filter input {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 4px;
            padding: .4375rem .75rem;
            font-size: .875rem;
            line-height: 1.4285714286;
            background-color: var(--tblr-bg-forms, #fff);
            min-width: 220px;
        }

        .dataTables_filter input:focus {
            border-color: #b0d4f1;
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(9, 93, 197, .1);
        }

        div.dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 4px;
            padding: 0.35rem 1.5rem 0.35rem 0.5rem;
        }

        .no-action {
            margin-left: 13px;
        }

        /* ==========================================================================
                                           RESPONSIVE MOBILE OVERRIDES (max-width: 767.98px)
                                           ========================================================================== */
        @media (max-width: 767.98px) {

            div.dataTables_wrapper div.dataTables_filter,
            .dataTables_filter,
            .search-wrapper {
                width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
                margin: 0 0 10px 0 !important;
            }

            .dataTables_filter label {
                width: 100% !important;
                justify-content: center !important;
                margin-bottom: 0 !important;
            }

            .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 6px !important;
            }

            .pagination,
            .pagination-wrapper {
                width: 100% !important;
                justify-content: center !important;
                flex-wrap: wrap !important;
                margin: 10px auto 0 auto !important;
            }

            .dataTables_paginate .paginate_button,
            .pagination .page-item {
                display: inline-block !important;
            }

            .card-footer {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 12px !important;
                text-align: center !important;
            }

            .no-action {
                margin-left: 0 !important;
            }
        }
    </style>
@endsection

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
                                <a href="{{ route('user.transactions') }}" class="text-secondary text-decoration-none">
                                    {{ __('Transactions') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('NFC Card') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('Transactions') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Track billing operations, payment logs, transaction credentials, and invoices for your store nfc order card entries.') }}
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

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            {{-- Table is wrapped dynamically inside the responsive scroll context --}}
                            <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                id="nfc-card-order-transactions-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Unique Transaction ID') }}</th>
                                        <th>{{ __('Payment ID') }}</th>
                                        <th>{{ __('Order ID') }}</th>
                                        <th>{{ __('Payment Method') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status modal --}}
        <div class="modal modal-bg fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-sm">
                    <div class="modal-status"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 9v2m0 4v.01" />
                            <path
                                d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                        </svg>
                        <h3>{{ __('Are you sure?') }}</h3>
                        <div id="action_status" class="text-secondary"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                                <div class="col">
                                    <a class="btn btn-danger w-100" id="status_id">
                                        {{ __('Yes, proceed') }}
                                    </a>
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
@endsection

{{-- Custom scripts pushed inside correct stack --}}
@push('custom-js')
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            var transactionsTable = $('#nfc-card-order-transactions-table').DataTable({
                processing: false,
                serverSide: true,
                order: [
                    [1, 'desc']
                ], // Default sorting target to Column 1 (Date)
                dom: "<'card-body py-3 d-flex flex-column flex-sm-row align-items-center gap-3'<'text-muted'l><'ms-sm-auto search-wrapper'f>>" +
                    "<'table-responsive px-3 pt-2 py-2'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: "{{ route('user.transaction.nfc.cards') }}",
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "sSearchPlaceholder": `{{ __('Search transactions...') }}`,
                    "oPaginate": {
                        "sNext": `{{ __('Next') }}`,
                        "sPrevious": `{{ __('Previous') }}`
                    },
                    "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                    "sInfoPostFix": "",
                    "sUrl": "",
                    "oAria": {
                        "sSortAscending": `{{ __(': activate to sort column in ascending order') }}`,
                        "sSortDescending": `{{ __(': activate to sort column in descending order') }}`
                    },
                    loadingRecords: `{{ __('Please wait - loading...') }}`,
                    emptyTable: `{{ __('No data available in the table') }}`
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
                        name: 'nfc_card_order_transactions.created_at',
                        searchable: true
                    },
                    {
                        data: 'nfc_card_order_transaction_id',
                        name: 'nfc_card_order_transactions.nfc_card_order_transaction_id',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'payment_transaction_id',
                        name: 'nfc_card_order_transactions.payment_transaction_id',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'nfc_card_order_id',
                        name: 'nfc_card_order_transactions.nfc_card_order_id',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'payment_method',
                        name: 'nfc_card_order_transactions.payment_method',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'amount',
                        name: 'nfc_card_order_transactions.amount',
                        searchable: true
                    },
                    {
                        data: 'payment_status',
                        name: 'nfc_card_order_transactions.payment_status',
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                preDrawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $tbody = $(settings.nTable).find('tbody');

                    $tbody.closest('.table-responsive').addClass('placeholder-glow');

                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(total_columns) + '</tr>';
                    }
                    $tbody.html(placeholderRows);
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    // Dynamic response header loop
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

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing
            $('#nfc-card-order-transactions-table').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });

            // Delegated touch-input filtering to catch search queries cleanly on any mobile viewport
            var searchTimer;
            $(document).on('input', '#nfc-card-order-transactions-table_filter input', function() {
                var value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    transactionsTable.search(value).draw();
                }, 350);
            });
        });
    </script>

    {{-- Update status --}}
    <script type="text/javascript">
        function updateStatus(pageId, status) {
            "use strict";

            // Show modal
            $("#status-modal").modal("show");

            // Modal message
            var modalMessage = document.getElementById("action_status");
            let messageStatus = status;
            let message =
                `{{ __('If you proceed, this transaction will be :status.', ['status' => '${messageStatus}']) }}`;
            modalMessage.innerHTML = message.replace(':status', status);

            // Status ID
            var link = document.getElementById("status_id");
            link.setAttribute("href", "{{ route('admin.action.transaction') }}?id=" + pageId + "&status=" + status);
        }
    </script>
@endpush
