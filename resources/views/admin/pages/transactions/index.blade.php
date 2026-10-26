@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Scoped CSS Overrides --}}
@section('css')
    <style>
        /* Force alignment inside TomSelect wrappers for Tabler-native styles */
        .ts-wrapper {
            border: none !important;
            padding: 0 !important;
        }

        .ts-control {
            border: none !important;
            box-shadow: var(--tblr-box-shadow-sm, 0 1px 3px rgba(0, 0, 0, 0.05)) !important;
            padding: 0.5rem 2.25rem 0.5rem 1rem !important;
            background-color: var(--tblr-bg-surface, #fff) !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            color: var(--tblr-body-color, #1e293b) !important;
            cursor: pointer;
        }

        .ts-control>input {
            display: none !important;
        }

        .ts-dropdown {
            border: 1px solid var(--tblr-border-color, #dbdfe6) !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            margin-top: 4px !important;
            background-color: var(--tblr-bg-surface, #fff) !important;
        }

        /* Styling for header search inputs to match screenshot layout exactly */
        .header-search-wrapper {
            position: relative;
            min-width: 220px;
        }

        .header-search-wrapper .form-control {
            border-radius: 8px;
            padding-right: 2.5rem;
        }

        .header-search-btn {
            position: absolute;
            right: 2px;
            top: 2px;
            bottom: 2px;
            border: none;
            background: transparent;
            width: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tblr-secondary, #626976);
            border-radius: 6px;
            transition: background-color 0.15s ease;
        }

        .header-search-btn:hover {
            background-color: var(--tblr-bg-hover, #f1f5f9);
        }

        /* ==========================================================================
                                           RESPONSIVE MOBILE CARD CONVERSION (max-width: 767.98px)
                                           ========================================================================== */
        @media (max-width: 767.98px) {

            .responsive-card-table,
            .responsive-card-table tbody,
            .responsive-card-table tr,
            .responsive-card-table td {
                display: block !important;
                width: 100% !important;
            }

            .responsive-card-table thead {
                display: none !important;
            }

            .responsive-card-table tbody tr {
                background-color: var(--tblr-bg-surface, #ffffff);
                border: 1px solid var(--tblr-border-color, #e6e8eb) !important;
                border-radius: 12px !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
                margin-bottom: 1.25rem !important;
                padding: 1rem !important;
            }

            .responsive-card-table tbody td {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
                border: none !important;
                padding: 0.5rem 0 !important;
                text-align: right !important;
                white-space: normal !important;
                font-size: 0.875rem;
            }

            .responsive-card-table td.rc-hide {
                display: none !important;
            }

            .responsive-card-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--tblr-secondary, #626976);
                text-align: left !important;
                margin-right: 1.5rem;
                flex-shrink: 0;
            }

            /* Custom Mobile Pagination Alignment */
            #custom-mobile-pagination {
                border-radius: 0 0 12px 12px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page header wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-3 align-items-center justify-content-between">

                    <!-- Left Column: Breadcrumbs and Title Info -->
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb" class="mb-1">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('Transactions') }}
                                </li>
                            </ol>
                        </nav>
                        <h2 class="page-title">
                            {{ __('Transactions') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Consolidated list of both online and offline processed one-time and recurring transactions statements log.') }}
                        </div>
                    </div>

                    <!-- Right Column: Interactive Themes-Style Filters Matrix -->
                    <div
                        class="col-auto col-md-6 d-flex flex-wrap align-items-center justify-content-start justify-content-md-end gap-2">

                        <!-- Filter 1: Type Selection (All / Online / Offline) with TomSelect classes -->
                        <select id="filter-type" class="form-select w-auto">
                            <option value="all">{{ __('All') }}</option>
                            <option value="online">{{ __('Online') }}</option>
                            <option value="offline">{{ __('Offline') }}</option>
                        </select>

                        <!-- Filter 2: Payment Status Selection (All / Paid / Unpaid) with TomSelect classes -->
                        <select id="filter-status" class="form-select w-auto">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="unpaid">{{ __('Unpaid') }}</option>
                        </select>

                        <!-- Custom Themes-Style Search Bar with Icon Button -->
                        <div class="header-search-wrapper shadow-sm">
                            <input type="text" id="custom-search" class="form-control border-0 py-2 ps-3"
                                placeholder="{{ __('Search transactions...') }}">
                            <button class="header-search-btn" type="button" aria-label="{{ __('Search Button') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <circle cx="10" cy="10" r="7" />
                                    <line x1="21" y1="21" x2="15" y2="15" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Alerts fallback --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="transactions-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Transaction Date') }}</th>
                                            <th class="w-1">{{ __('Trans ID') }}</th>
                                            <th>{{ __('Payment Trans ID / Notes') }}</th>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Gateway Name') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- App-like Mobile Pagination footer --}}
                            <div id="custom-mobile-pagination"
                                class="d-none d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="mobile-prev-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                                    type="button">
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

                                <button id="mobile-next-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                                    type="button">
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Universal confirmation action Modal --}}
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <div id="status_message" class="text-secondary">
                        {{ __('If you proceed with this transaction, you will have payment status success, and user chosen plan will be activated.') }}
                    </div>
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
                                <a class="btn btn-danger w-100" id="transaction_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 9;

            // Initialize TomSelect dropdowns natively
            var selectType = new TomSelect('#filter-type', {
                create: false,
                controlInput: null,
                hideSearch: true
            });

            var selectStatus = new TomSelect('#filter-status', {
                create: false,
                controlInput: null,
                hideSearch: true
            });

            // Re-usable skeleton injector providing instant feedback when filters/search interaction fires
            function injectSkeletonLoading() {
                $('#transactions-table_wrapper').addClass('placeholder-glow');
                var placeholderRows = '';
                for (var i = 0; i < 8; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#transactions-table tbody').html(placeholderRows);
            }

            var table = $('#transactions-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.transactions.index') }}",
                    data: function(d) {
                        d.type = $('#filter-type').val();
                        d.status = $('#filter-status').val();
                    }
                },
                dom: "<'table-responsive'tr>" +
                    "<'card-footer d-none d-md-flex align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-secondary'i><'pagination-wrapper'p>>",
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "oPaginate": {
                        "sNext": `{{ __('Next') }}`,
                        "sPrevious": `{{ __('Previous') }}`
                    },
                    "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
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
                        name: 'transactions.created_at',
                        className: 'rc-info-start'
                    },
                    {
                        data: 'gobiz_transaction_id',
                        name: 'transactions.gobiz_transaction_id',
                        className: 'rc-heading'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transactions.transaction_id',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'user',
                        name: 'users.name',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'payment_gateway_name',
                        name: 'transactions.payment_gateway_name',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'transaction_amount',
                        name: 'transactions.transaction_amount',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'payment_status',
                        name: 'transactions.payment_status',
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
                    // Unconditionally apply the placeholder-glow wrapper on every single draw
                    $('#transactions-table_wrapper').addClass('placeholder-glow');

                    // Generate modern, fully filled skeleton layouts on all view changes
                    var placeholderRows = '';
                    for (var i = 0; i < 10; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(totalColumns) + '</tr>';
                    }
                    $('#transactions-table tbody').html(placeholderRows);
                },
                drawCallback: function(settings) {
                    $('#transactions-table_wrapper').removeClass('placeholder-glow');

                    // Clear dynamic skeleton actions (index 8)
                    $('#transactions-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(8);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force span dimensions on empty rows
                    $('#transactions-table tbody tr td.dataTables_empty').attr('colspan', totalColumns);

                    // Dynamically generate 'data-label' attributes for mobile card rendering
                    var headers = [];
                    $('#transactions-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#transactions-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index] || '');
                        });
                    });

                    // Manage dynamic Mobile Pagination Footer layout state
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    // Bootstrap classes allow this to display on mobile and hide on desktops natively
                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#custom-mobile-pagination').addClass('d-none').removeClass('d-flex');
                    } else {
                        $('#custom-mobile-pagination').removeClass('d-none').addClass('d-flex');
                    }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#mobile-page-info').text(pageInfoText);

                    $('#mobile-prev-btn').prop('disabled', pageInfo.page === 0);
                    $('#mobile-next-btn').prop('disabled', pageInfo.page >= pageInfo.pages - 1);
                }
            });

            // Re-trigger Skeleton Loader on dynamic filter changes
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#transactions-table_wrapper').addClass('placeholder-glow');
                }
            });

            // Trigger instantaneous loader state on filter change
            $('#filter-type, #filter-status').on('change', function() {
                injectSkeletonLoading();
                table.draw();
            });

            // High-performance custom search input debouncer with instant visual feedback
            var searchTimer;
            $('#custom-search').on('input', function() {
                var value = this.value;
                clearTimeout(searchTimer);

                // Instantly visualizes the placeholder glow transition upon user typing interaction
                injectSkeletonLoading();

                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 350);
            });

            // Bind click events on Mobile pagination triggers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                injectSkeletonLoading(); // Triggers the card skeletons instantly on mobile
                table.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                injectSkeletonLoading(); // Triggers the card skeletons instantly on mobile
                table.page('next').draw('page');
            });
        });

        // Trigger Offline status modal confirmation
        function getOfflineTransaction(transactionId) {
            "use strict";
            $("#delete-modal").modal("show");
            var link = document.getElementById("transaction_id");
            link.setAttribute("href", "/admin/offline-transaction-status/" + transactionId + "/SUCCESS");
        }

        // Trigger Online status modal confirmation (Fallback helper)
        function getTransaction(transactionId) {
            "use strict";
            $("#delete-modal").modal("show");
            var link = document.getElementById("transaction_id");
            link.setAttribute("href", "/admin/transaction-status/" + transactionId + "/SUCCESS");
        }
    </script>
@endsection
