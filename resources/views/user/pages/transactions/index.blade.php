@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Scoped CSS Overrides & Responsive Table Rules --}}
@section('css')
    <style>
        /* TomSelect Dropdown Custom Styling */
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
            z-index: 1050;
            width: 15vh;
        }

        /* Search input styling */
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

        /* Mobile Responsive Card Table */
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

            #custom-mobile-pagination {
                border-radius: 0 0 12px 12px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-3 align-items-center justify-content-between">

                    <!-- Left Column: Breadcrumb and Title -->
                    <div class="col-md-5">
                        <nav aria-label="breadcrumb" class="mb-1">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
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
                            {{ __('Consolidated billing statements for Subscription Plans, NFC Cards, and AI Credits.') }}
                        </div>
                    </div>

                    <!-- Right Column: Unified Filters Matrix -->
                    <div
                        class="col-auto col-md-7 d-flex flex-wrap align-items-center justify-content-start justify-content-md-end gap-2"> 

                        <!-- Filter 1: Transaction Category -->
                        <select id="filter-type" class="form-select w-auto">
                            <option value="all">{{ __('All') }}</option>
                            <option value="plan">{{ __('Plans') }}</option>

                            {{-- NFC Card Option --}}
                            @if (isset($config[76]) && $config[76]->config_value == '1')
                                @if (isset($active_plan) && !empty($active_plan))
                                    @if (isset($active_plan['nfc_card']) && $active_plan['nfc_card'])
                                        <option value="nfc_card">{{ __('NFC Cards') }}</option>
                                    @endif
                                @endif
                            @endif

                            {{-- AI Credits Option --}}
                            @if (isset($aibuilder_settings) && $aibuilder_settings->aibuilder == 1)
                                <option value="ai_credit">{{ __('AI Credits') }}</option>
                            @endif
                        </select>

                        <!-- Filter 2: Payment Status -->
                        <select id="filter-status" class="form-select w-auto">
                            <option value="all">{{ __('All') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="unpaid">{{ __('Unpaid') }}</option>
                        </select>

                        <!-- Search Input -->
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
                        <!-- Card Table Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="transactions-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th class="w-1">{{ __('Payment ID') }}</th>
                                            <th>{{ __('Payment Trans ID') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Item / Plan') }}</th>
                                            <th>{{ __('Gateway') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Mobile Pagination Control --}}
                            <div id="custom-mobile-pagination"
                                class="d-none d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="mobile-prev-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                                    type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="20" height="20"
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
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="20"
                                        height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
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
        @include('user.includes.footer')
    </div>
@endsection

{{-- Scripts --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 10; // Total 10 columns

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

            // Instant feedback skeleton injector
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
                    url: "{{ route('user.transactions') }}",
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
                    emptyTable: `{{ __('No transactions found') }}`
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
                        name: 'created_at'
                    },
                    {
                        data: 'gobiz_transaction_id',
                        name: 'display_id'
                    },
                    {
                        data: 'payment_trans_id',
                        name: 'payment_trans_id'
                    },
                    {
                        data: 'transaction_type',
                        name: 'transaction_type',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'details',
                        name: 'item_name'
                    },
                    {
                        data: 'payment_gateway_name',
                        name: 'gateway'
                    },
                    {
                        data: 'transaction_amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                preDrawCallback: function(settings) {
                    $('#transactions-table_wrapper').addClass('placeholder-glow');

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

                    // Clear skeleton placeholders from actions column (Column Index 9)
                    $('#transactions-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(9);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    $('#transactions-table tbody tr td.dataTables_empty').attr('colspan', totalColumns);

                    // Dynamic data-label for mobile view
                    var headers = [];
                    $('#transactions-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#transactions-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index] || '');
                        });
                    });

                    // Mobile pagination update
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

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

            // Re-trigger skeleton on filter changes
            $('#filter-type, #filter-status').on('change', function() {
                injectSkeletonLoading();
                table.draw();
            });

            // Instant search debounce
            var searchTimer;
            $('#custom-search').on('input', function() {
                var value = this.value;
                clearTimeout(searchTimer);
                injectSkeletonLoading();

                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 350);
            });

            // Mobile Pagination Click Handlers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                injectSkeletonLoading();
                table.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                injectSkeletonLoading();
                table.page('next').draw('page');
            });
        });
    </script>
@endsection
