@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    <style>
        /* Search & Filter Inputs */
        .dataTables_filter {
            margin-bottom: 0 !important;
        }

        .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            margin-bottom: 0;
            color: var(--tblr-muted);
            font-weight: 500;
        }

        .dataTables_filter input {
            border: 1.5px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
            padding: 0.45rem 0.85rem;
            font-size: 0.875rem;
            background-color: var(--tblr-bg-forms);
            color: var(--tblr-body-color);
            min-width: 240px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .dataTables_filter input:focus {
            border-color: var(--tblr-primary) !important;
            background-color: var(--tblr-bg-surface) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--tblr-primary-rgb), 0.15) !important;
            outline: 0;
        }

        div.dataTables_wrapper .dataTables_length select {
            border: 1.5px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
            padding: 0.35rem 1.75rem 0.35rem 0.75rem;
            background-color: var(--tblr-bg-forms);
            color: var(--tblr-body-color);
        }

        /* Action Dropdown Item Styling */
        .dropdown-menu .dropdown-item {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: var(--tblr-primary-lt);
            color: var(--tblr-primary);
        }

        .table-hover>tbody>tr:hover>* {
            --tblr-table-bg-state: none !important;
        }

        /* Empty State */
        .dataTables_empty {
            padding: 3rem 1rem !important;
            text-align: center !important;
            background: transparent !important;
        }

        .empty-state-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: var(--tblr-primary-lt);
            color: var(--tblr-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        /* Desktop: Show entries and search on the same row */
        @media (min-width: 768px) {
            .dataTables_wrapper .card-body.border-bottom {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-wrap: nowrap !important;
            }

            .dataTables_length {
                display: flex !important;
                align-items: center !important;
                margin-bottom: 0 !important;
            }

            .dataTables_filter {
                display: flex !important;
                align-items: center !important;
                margin-left: auto !important;
                margin-bottom: 0 !important;
                width: auto !important;
                padding: 0 !important;
            }

            .dataTables_filter label,
            .dataTables_filter input {
                width: auto !important;
                display: inline-block !important;
            }

            .dataTables_filter input {
                min-width: 260px !important;
            }
        }

        /* Responsive Mobile Cards Layout */
        @media (max-width: 767.98px) {
            .dataTables_filter {
                width: 100% !important;
                padding: 0.85rem 1rem !important;
            }

            .dataTables_filter label,
            .dataTables_filter input {
                width: 100% !important;
                min-width: 100% !important;
                display: block !important;
                margin-left: 0 !important;
            }

            .responsive-card-table thead,
            .dataTables_paginate,
            .dataTables_info {
                display: none !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .responsive-card-table tbody {
                display: block !important;
                padding: 0.5rem 1rem !important;
            }

            .responsive-card-table tbody tr {
                display: block !important;
                background-color: var(--tblr-bg-surface) !important;
                border: 1px solid var(--tblr-border-color-translucent) !important;
                border-radius: 12px !important;
                padding: 1.15rem 1.25rem !important;
                margin-bottom: 1rem !important;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04) !important;
            }

            .responsive-card-table tbody td {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 0.65rem 0 !important;
                border: none !important;
                border-bottom: 1px solid var(--tblr-border-color-translucent) !important;
                font-size: 0.875rem !important;
                text-align: right !important;
            }

            .responsive-card-table tbody td:last-child {
                border-bottom: none !important;
                padding-bottom: 0 !important;
            }

            .responsive-card-table tbody td::before {
                content: attr(data-label) !important;
                display: inline-block !important;
                font-weight: 500 !important;
                color: var(--tblr-muted) !important;
                text-align: left !important;
                margin-right: 1rem !important;
                flex-shrink: 0 !important;
            }

            .responsive-card-table td.rc-hide,
            .responsive-card-table td.rc-hide::before {
                display: none !important;
            }

            .responsive-card-table tbody tr:has(.dataTables_empty),
            .responsive-card-table tbody td.dataTables_empty {
                display: block !important;
                width: 100% !important;
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                padding: 2.5rem 1rem !important;
            }

            .responsive-card-table tbody td.dataTables_empty::before {
                display: none !important;
                content: none !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col-12">
                        <!-- Hierarchical Breadcrumb Trail -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('My NFC Cards') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('My NFC Cards') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Manage, link, and activate your physical NFC-enabled cards and digital stores.') }}
                        </div>
                    </div>

                    <!-- Activate NFC Card Button -->
                    <div class="col-auto ms-auto d-print-none">
                        <a href="{{ route('user.activate.nfc.card') }}"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-key" width="18"
                                height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.558 6.558a2 2 0 0 1 -1.239 .578l-.175 .008h-1.172a1 1 0 0 1 -.993 -.883l-.007 -.117v-1.172a2 2 0 0 1 .467 -1.284l.119 -.13l.414 -.414h2v-2h2v-2l2.144 -2.144l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z" />
                                <path d="M15 9h.01" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Activate NFC Card') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- NFC Cards Table Card -->
                <div class="nfc-table-card shadow-sm">
                    <div class="table-responsive">
                        <table id="nfc-card-order-table"
                            class="table card-table table-vcenter table-hover text-nowrap responsive-card-table">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('#') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('NFC Card Name') }}</th>
                                    <th>{{ __('Linked Card') }}</th>
                                    <th>{{ __('Linked Status') }}</th>
                                    <th class="w-1 text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    {{-- Custom App-like Mobile Pagination Bar --}}
                    <div id="custom-mobile-pagination"
                        class="d-flex d-md-none justify-content-between align-items-center px-3 py-3">
                        <button id="mobile-prev-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                            type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left me-1"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M15 6l-6 6l6 6"></path>
                            </svg>
                            {{ __('Previous') }}
                        </button>

                        <span id="mobile-page-info" class="text-muted small fw-semibold"></span>

                        <button id="mobile-next-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                            type="button">
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-right ms-1"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M9 6l6 6l-6 6"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Link / Unlink Status Modal --}}
        <div class="modal modal-blur fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body text-center py-4">
                        <div class="avatar avatar-lg bg-danger-lt mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-alert-triangle text-danger" width="28"
                                height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 9v4" />
                                <path d="M12 16v.01" />
                                <path
                                    d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                            </svg>
                        </div>
                        <h3 class="fw-bold">{{ __('Are you sure?') }}</h3>
                        <div id="action_status" class="text-muted small"></div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <div class="w-100">
                            <div class="row g-2">
                                <div class="col">
                                    <button type="button" class="btn btn-primary-lt w-100" data-bs-dismiss="modal">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                                <div class="col">
                                    <a class="btn btn-danger w-100" id="status_id">
                                        {{ __('Yes, Proceed') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            const total_columns = 6;

            const nfcTable = $('#nfc-card-order-table').DataTable({
                processing: false,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                dom: "<'card-body d-flex align-items-center justify-content-between'<'text-muted d-none d-md-flex align-items-center'l><'d-flex align-items-center ms-auto w-100 w-md-auto'f>>" +
                    "<'table-responsive'tr>" +
                    "<'card-footer d-none d-md-flex align-items-center justify-content-between gap-2 bg-transparent'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: "{{ route('user.manage.nfc.cards') }}",
                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: ``,
                    sSearchPlaceholder: `{{ __('Search NFC cards...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    emptyTable: `
                        <div class="text-center py-4">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-nfc">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M11 20a3 3 0 0 1 -3 -3v-11l5 5" />
                                    <path d="M13 4a3 3 0 0 1 3 3v11l-5 -5" />
                                    <rect x="4" y="4" width="16" height="16" rx="3" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No NFC Cards Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('Activate your physical NFC card key to start linking cards.') }}</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="text-center py-4">
                            <div class="empty-state-icon" style="background-color: var(--tblr-danger-lt); color: var(--tblr-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-nfc">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M11 20a3 3 0 0 1 -3 -3v-11l5 5" />
                                    <path d="M13 4a3 3 0 0 1 3 3v11l-5 -5" />
                                    <rect x="4" y="4" width="16" height="16" rx="3" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching NFC Cards Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('We could not find any cards matching your search keyword.') }}</p>
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
                        searchable: true
                    },
                    {
                        data: 'nfc_card_name',
                        name: 'nfc_card_designs.nfc_card_name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'card_id',
                        name: 'card_id',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'link_status',
                        name: 'nfc_card_keys.link_status',
                        searchable: true
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
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    // Assign data-label attributes for mobile cards
                    var headers = [];
                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $table.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            if (headers[index]) {
                                $(this).attr('data-label', headers[index]);
                            }
                        });
                    });

                    // Manage mobile pagination state
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

                    $('#mobile-prev-btn').prop('disabled', pageInfo.page === 0).toggleClass('disabled',
                        pageInfo.page === 0);
                    $('#mobile-next-btn').prop('disabled', pageInfo.page >= pageInfo.pages - 1)
                        .toggleClass('disabled', pageInfo.page >= pageInfo.pages - 1);
                }
            });

            // Table processing events
            $('#nfc-card-order-table').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });

            // Search input debounce binding
            var searchTimer;
            $(document).on('input', '#nfc-card-order-table_filter input', function() {
                var value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    nfcTable.search(value).draw();
                }, 300);
            });

            // Mobile Pagination Click Handlers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                nfcTable.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                nfcTable.page('next').draw('page');
            });
        });

        // Trigger Status Toggle Modal
        function updateStatus(pageId, status) {
            "use strict";
            $("#status-modal").modal("show");

            const modalMessage = document.getElementById("action_status");
            const message =
                `{{ __('If you continue, the vcard/store link will be :status.', ['status' => '_status_place_']) }}`;
            modalMessage.innerHTML = message.replace('_status_place_', status);

            const link = document.getElementById("status_id");
            link.setAttribute("href", "{{ route('user.action.key.generation') }}?id=" + pageId + "&status=" + status);
        }
    </script>
@endpush
