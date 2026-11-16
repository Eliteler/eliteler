@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Scoped CSS Overrides --}}
@section('css')
    <style>
        .card .table-responsive,
        .card>.table-responsive {
            padding: 8px !important;
            border-radius: 16px !important;
        }

        /* Search & Pagination Visibility */
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
            border-radius: 4px;
            padding: .4375rem .75rem;
            font-size: .875rem;
            line-height: 1.4285714286;
            background-color: var(--tblr-bg-forms, #fff);
            width: 100% !important;
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

        /* Empty State Icon */
        .empty-state-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: var(--tblr-primary-lt, rgba(32, 107, 196, 0.12));
            color: var(--tblr-primary, #206bc4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
        }

        /* Empty State Centering */
        #withdrawal-request-table.is-table-empty,
        #withdrawal-request-table.is-table-empty tbody {
            display: block !important;
            width: 100% !important;
        }

        #withdrawal-request-table tbody tr.empty-table-row {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        #withdrawal-request-table tbody tr.empty-table-row td.dataTables_empty {
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

        #withdrawal-request-table tbody tr.empty-table-row td.dataTables_empty::before,
        #withdrawal-request-table tbody tr.empty-table-row td.dataTables_empty::after {
            display: none !important;
            content: none !important;
        }

        /* Desktop Skeleton Alignment */
        @media (min-width: 768px) {
            .placeholder-row td {
                vertical-align: middle !important;
                text-align: center !important;
            }

            .skeleton-label {
                display: none !important;
            }

            .skeleton-value {
                display: inline-block !important;
                width: 80% !important;
                height: 12px !important;
                border-radius: 4px !important;
            }
        }

        /* Responsive Mobile Layout & Skeleton Overrides */
        @media (max-width: 767.98px) {
            .table-responsive {
                overflow-x: hidden !important;
                overflow-y: visible !important;
            }

            .responsive-card-table {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                white-space: normal !important;
                /* Prevents skeleton from overflowing horizontally */
            }

            .responsive-card-table thead,
            .dataTables_info {
                display: none !important;
            }

            .responsive-card-table tbody {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 0.5rem 0 !important;
            }

            /* Real data card & skeleton card alignment */
            .responsive-card-table tbody tr:not(.empty-table-row) {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                background-color: var(--tblr-bg-surface, #fff) !important;
                border: 1px solid var(--tblr-border-color-translucent, rgba(0, 0, 0, 0.08)) !important;
                border-radius: 12px !important;
                padding: 1.15rem 1.25rem !important;
                margin-bottom: 1rem !important;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04) !important;
                overflow: hidden !important;
            }

            .responsive-card-table tbody tr:not(.empty-table-row) td {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                padding: 0.65rem 0 !important;
                border: none !important;
                border-bottom: 1px solid var(--tblr-border-color-translucent, rgba(0, 0, 0, 0.08)) !important;
                font-size: 0.875rem !important;
                text-align: right !important;
                overflow: hidden !important;
            }

            .responsive-card-table tbody tr:not(.empty-table-row) td:last-child {
                border-bottom: none !important;
                padding-bottom: 0 !important;
            }

            .responsive-card-table tbody tr:not(.empty-table-row):not(.placeholder-row) td::before {
                content: attr(data-label) !important;
                display: inline-block !important;
                font-weight: 500 !important;
                color: var(--tblr-muted, #626976) !important;
                text-align: left !important;
                margin-right: 1rem !important;
                flex-shrink: 0 !important;
            }

            .responsive-card-table tbody tr.placeholder-row td::before {
                display: none !important;
                content: none !important;
            }

            .responsive-card-table td.rc-hide,
            .responsive-card-table tbody tr.placeholder-row td.rc-hide {
                display: none !important;
            }

            /* Responsive non-overflowing skeleton bars */
            .skeleton-label {
                display: inline-block !important;
                width: 32% !important;
                max-width: 120px !important;
                height: 12px !important;
                border-radius: 4px !important;
                flex-shrink: 0 !important;
            }

            .skeleton-value {
                display: inline-block !important;
                width: 48% !important;
                max-width: 160px !important;
                height: 12px !important;
                border-radius: 4px !important;
                margin-left: auto !important;
                flex-shrink: 0 !important;
            }

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
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 6px !important;
            }

            div.dataTables_wrapper div.dataTables_paginate,
            .dataTables_paginate,
            .pagination-wrapper,
            .pagination {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 100% !important;
                justify-content: center !important;
                flex-wrap: wrap !important;
                margin: 10px auto 0 auto !important;
                padding: 0 !important;
            }

            .dataTables_paginate .paginate_button,
            .pagination .page-item,
            .page-link {
                display: inline-flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .card-footer {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 12px !important;
                text-align: center !important;
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
                    <div class="col">
                        <!-- Breadcrumb Navigation -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.referrals') }}" class="text-secondary text-decoration-none">
                                        {{ __('Referrals') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('Withdrawal Requests') }}
                                </li>
                            </ol>
                        </nav>

                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('Withdrawal Request') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your target payout coordinates and track historical withdrawal transactions.') }}
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

                {{-- Update Bank Details configuration widget --}}
                <div class="row mb-4 d-print-none">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Update Bank Details') }}</h3>
                            </div>
                            <form action="{{ route('user.update.bank.details') }}" method="post"
                                enctype="multipart/form-data" class="border-0">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 px-0">
                                            <div class="form-group">
                                                <label
                                                    class="form-label required">{{ __('Bank Details / Paypal Email Address') }}</label>
                                                <textarea name="bank_details" class="form-control" id="bank_details" rows="5">{{ $bankDetails }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end bg-transparent border-top-0 pt-0">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <circle cx="12" cy="14" r="2" />
                                            <polyline points="14 4 14 8 8 8 8 4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Withdrawal requests list grid --}}
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">{{ __('Withdrawal Requests') }}</h3>
                                {{-- Raise new request trigger --}}
                                <a href="{{ route('user.new.withdrawal.request') }}"
                                    class="btn btn-primary btn-md btn-icon-only d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus me-2"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    <span class="d-none d-sm-inline-block">{{ __('New Request') }}</span>
                                </a>
                            </div>

                            <!-- Withdrawal Request Table container -->
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="withdrawal-request-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Request ID') }} </th>
                                            <th>{{ __('Transaction ID') }}</th>
                                            <th>{{ __('Requested Amount') }} </th>
                                            <th>{{ __('Transfer To') }}</th>
                                            <th>{{ __('Notes') }}</th>
                                            <th>{{ __('Transfer Status') }}</th>
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

        <!-- Footer -->
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom scripts pushed inside correct stack --}}
@push('custom-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script type="text/javascript">
        "use strict";

        // Dark Mode detection
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        tinymce.init({
            selector: 'textarea#bank_details',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | pagebreak | link',
            toolbar_sticky: true,
            height: 200,
            menubar: false,
            statusbar: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'dark'
            ] : [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'default'
            ],
            setup: function(editor) {
                editor.on('init', function() {
                    if (document.body.dataset.bsTheme === 'dark') {
                        editor.getBody().setAttribute('data-bs-theme', 'dark');
                    }
                });
            },
            content_style: isDark ? `
                body {
                    background-color: #151f2c !important;
                    font-family: "Figtree", sans-serif !important;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    color: rgba(34,47,62,.5) !important;
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            ` : `
                body {
                    font-family: "Figtree", sans-serif !important;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',

            // Correct mobile settings
            mobile: {
                plugins: 'paste',
                toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            // Generate clean percentage-based skeleton rows that will not overflow
            function generateSkeletonRows(columnsCount, rowCount) {
                let rows = '';
                for (let r = 0; r < rowCount; r++) {
                    rows += '<tr class="placeholder-row">';
                    for (let c = 0; c < columnsCount; c++) {
                        if (c === 0) {
                            // First column (DT_RowIndex) is hidden on mobile
                            rows += '<td class="rc-hide"><span class="placeholder skeleton-value"></span></td>';
                        } else {
                            // Left span mimics label, right span mimics value
                            rows += '<td>' +
                                '<span class="placeholder skeleton-label"></span>' +
                                '<span class="placeholder skeleton-value"></span>' +
                                '</td>';
                        }
                    }
                    rows += '</tr>';
                }
                return rows;
            }

            var requestsTable = $('#withdrawal-request-table').DataTable({
                processing: false,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                dom: "<'card-body d-flex flex-column flex-sm-row align-items-center gap-3'<'text-muted'l><'ms-sm-auto search-wrapper'f>>" +
                    "<'table-responsive px-3 pt-2 py-2'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: "{{ route('user.referrals.withdrawal.request') }}",
                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: ``,
                    sSearchPlaceholder: `{{ __('Search requests...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    sInfoPostFix: "",
                    sUrl: "",
                    oAria: {
                        sSortAscending: `{{ __(': activate to sort column in ascending order') }}`,
                        sSortDescending: `{{ __(': activate to sort column in descending order') }}`
                    },
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    // Centered Empty State HTML
                    emptyTable: `
                        <div class="d-flex flex-column align-items-center justify-content-center text-center w-100">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-wallet">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
                                    <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Withdrawal Requests') }}</h4>
                            <p class="text-muted small mb-0">{{ __('You have not submitted any payout withdrawal requests yet.') }}</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="d-flex flex-column align-items-center justify-content-center text-center w-100">
                            <div class="empty-state-icon" style="background-color: var(--tblr-danger-lt); color: var(--tblr-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-wallet">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
                                    <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching Requests Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('We could not find any requests matching your search keyword.') }}</p>
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
                        name: 'referral_withdraw_requests.created_at',
                        searchable: true
                    },
                    {
                        data: 'request_id',
                        name: 'referral_withdraw_requests.referral_withdraw_request_id',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'transfer_id',
                        name: 'referral_withdraw_transactions.transfer_id',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'amount',
                        name: 'referral_withdraw_requests.amount',
                        searchable: true
                    },
                    {
                        data: 'bank_details',
                        name: 'referral_withdraw_requests.bank_details',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'notes',
                        name: 'referral_withdraw_transactions.notes',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'payment_status',
                        name: 'referral_withdraw_requests.payment_status',
                        searchable: true
                    },
                ],
                preDrawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $tbody = $(settings.nTable).find('tbody');

                    $tbody.closest('.table-responsive').addClass('placeholder-glow');
                    $tbody.html(generateSkeletonRows(total_columns, 4));
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    // Check if table has active records
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

                    // Dynamic response header loop
                    var headers = [];
                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    // Assign data-label only to real data rows
                    $table.find('tbody tr:not(.empty-table-row):not(.placeholder-row)').each(
                function() {
                        $(this).find('td').each(function(index) {
                            var label = headers[index] || '';
                            $(this).attr('data-label', label);
                        });
                    });
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing
            $('#withdrawal-request-table').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    $(this).find('tbody').html(generateSkeletonRows(total_columns, 4));
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });

            // Delegated touch-input filtering to catch search queries cleanly on any mobile viewport
            var searchTimer;
            $(document).on('input', '#withdrawal-request-table_filter input', function() {
                var value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    requestsTable.search(value).draw();
                }, 350);
            });
        });
    </script>
@endpush
