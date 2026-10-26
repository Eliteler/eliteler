@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Scoped CSS Overrides --}}
@section('css')
    <style>
        /* Modern styled search overrides matching Tabler designs */
        .search-wrapper {
            display: flex !important;
            align-items: center;
            justify-content: flex-end;
            width: 100% !important;
        }

        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 400;
            color: var(--tblr-body-color, #626976);
            margin-bottom: 0;
            width: 100%;
        }

        .dataTables_filter input {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 6px;
            padding: .4375rem .75rem;
            font-size: .875rem;
            line-height: 1.4285714286;
            background-color: var(--tblr-bg-forms, #fff);
            width: 100% !important;
            min-width: 240px;
        }

        .dataTables_filter input:focus {
            border-color: #b0d4f1;
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(9, 93, 197, .1);
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

            /* Pagination Adjustments on Mobile devices */
            div.dataTables_wrapper div.dataTables_paginate {
                display: flex !important;
                justify-content: center !important;
                margin-top: 1rem !important;
            }

            div.dataTables_wrapper div.dataTables_info {
                display: block !important;
                text-align: center !important;
                margin-bottom: 0.5rem !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.ai.credits.plans') }}" class="text-secondary text-decoration-none">
                                    {{ __('AI Credit') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Transactions') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Transactions') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('View and track your financial history, purchase statements, and credit balance upgrades.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
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
                            <div class="table-responsive">
                                <!-- Standard traditional table classes with responsive-card-table upgrades -->
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="ai-credits-transactions-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th class="w-1">{{ __('Order ID') }}</th>
                                            <th>{{ __('Payment Trans ID') }}</th>
                                            <th>{{ __('Plan') }}</th>
                                            <th>{{ __('Payment Method') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
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
@endsection

@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 9;

            const table = $('#ai-credits-transactions-table').DataTable({
                processing: false, // Skeleton placeholders override standard loader
                serverSide: true,
                ajax: "{{ route('user.ai.credits.transactions') }}",
                // DOM property defined explicitly to render beautifully styled Tabler components
                dom: "<'card-body py-3 d-flex flex-column flex-sm-row align-items-center gap-3'<'text-muted'l><'ms-sm-auto search-wrapper'f>>" +
                    "<'table-responsive px-3'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
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
                        className: 'rc-hide' // Hide first column on mobile cards
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'ai_credits_order_id',
                        name: 'ai_credits_order_id'
                    },
                    {
                        data: 'payment_transaction_id',
                        name: 'payment_transaction_id'
                    },
                    {
                        data: 'ai_credits_plan_id',
                        name: 'ai_credits_plan_id'
                    },
                    {
                        data: 'payment_method',
                        name: 'payment_method'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                preDrawCallback: function(settings) {
                    $('#ai-credits-transactions-table_wrapper').addClass('placeholder-glow');

                    // Generate structured horizontal skeleton lines matching totalColumns count
                    let placeholderRows = '';
                    for (let i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(totalColumns) + '</tr>';
                    }
                    $('#ai-credits-transactions-table tbody').html(placeholderRows);
                },
                drawCallback: function(settings) {
                    $('#ai-credits-transactions-table_wrapper').removeClass('placeholder-glow');

                    // Set empty view widths across the full width of the table columns
                    $('#ai-credits-transactions-table tbody tr td.dataTables_empty').attr('colspan',
                        totalColumns);

                    // Map column headers to generate custom labels on mobile card viewports dynamically
                    let headers = [];
                    $('#ai-credits-transactions-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#ai-credits-transactions-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            let label = headers[index] || '';
                            $(this).attr('data-label', label);
                        });
                    });
                }
            });

            // Implement high-performance debounced searching to preserve server capacity
            $('.dataTables_filter input').unbind();
            let searchTimer;
            $(document).on('input', '.dataTables_filter input', function() {
                let value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    table.search(value).draw();
                }, 350);
            });

            // Handle instantaneous skeleton loading during server-side processing
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#ai-credits-transactions-table_wrapper').addClass('placeholder-glow');
                } else {
                    $('#ai-credits-transactions-table_wrapper').removeClass('placeholder-glow');
                }
            });
        });
    </script>
@endsection
