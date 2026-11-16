@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Main Digital Business Cards CSS --}}
    <link href="{{ asset('css/all-digital-business-cards.css') }}" rel="stylesheet">

    <style>
        /* Empty state alignment fix for responsive mobile card view */
        .responsive-card-table tbody td.dataTables_empty::before {
            display: none !important;
            content: "" !important;
        }

        .responsive-card-table tbody td.dataTables_empty {
            text-align: center !important;
            width: 100% !important;
            padding: 2.5rem 1rem !important;
        }

        /* Mobile card view alignment optimization */
        @media (max-width: 767.98px) {

            .responsive-card-table tbody tr:has(.dataTables_empty),
            .responsive-card-table tbody tr.odd:has(.dataTables_empty) {
                display: block !important;
                width: 100% !important;
                background: transparent !important;
                box-shadow: none !important;
                border: none !important;
            }

            .responsive-card-table tbody td.dataTables_empty {
                display: block !important;
                width: 100% !important;
            }

            .responsive-card-table tbody td.dataTables_empty>div {
                margin: 0 auto !important;
                width: 100% !important;
            }

            /* Ensure all rows take 100% width on mobile view */
            .responsive-card-table tbody td {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0.65rem 1rem !important;
            }

            .responsive-card-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--tblr-muted, #667382);
                text-align: left;
                flex-shrink: 0;
                margin-right: 1rem;
            }

            /* Align values cleanly to the right edge */
            .responsive-card-table tbody td>* {
                text-align: right !important;
                margin-left: auto;
                max-width: 65% !important;
                word-break: break-word;
            }

            /* Maintain action button layout on mobile */
            .responsive-card-table tbody td[data-label="Actions"] .btn {
                width: auto !important;
                height: auto !important;
                display: inline-flex !important;
                align-items: center !important;
                padding: 4px 10px !important;
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
                    <!-- Hierarchical Breadcrumbs -->
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                        {{ __('Digital Business Cards') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Contact List') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Page Title & Help Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Contact List') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Monitor incoming visitor messages, download dynamic contact directories, and execute direct outreach channels.') }}
                        </div>
                    </div>

                    <!-- Export Action Buttons in Page Header -->
                    <div class="col-auto ms-auto d-print-none">
                        <div class="d-inline-flex align-items-center gap-2">
                            <button type="button" id="btn-export-csv"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm px-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text me-1"
                                    width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    <path d="M9 9l1 0" />
                                    <path d="M9 13l6 0" />
                                    <path d="M9 17l6 0" />
                                </svg>
                                <span>{{ __('CSV') }}</span>
                            </button>

                            <button type="button" id="btn-export-excel"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm px-3">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-file-spreadsheet me-1" width="18" height="18"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                    <path d="M8 11h8v7h-8z" />
                                    <path d="M8 15h8" />
                                    <path d="M11 11v7" />
                                </svg>
                                <span>{{ __('Excel') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Cards Data Table Wrapper -->
                <div class="cards-table-card">
                    <div class="table-responsive">
                        <table id="contactsTable"
                            class="table card-table table-vcenter table-hover text-nowrap responsive-card-table">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('#') }}</th>
                                    <th>{{ __('Source') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Mobile Number') }}</th>
                                    <th class="w-1 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contacts as $contact)
                                    <tr>
                                        <td class="rc-hide">{{ $loop->iteration }}</td>

                                        <td>
                                            <span class="badge bg-blue-lt text-uppercase fw-bold px-2 py-1">
                                                {{ $contact->source }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-capitalize fw-semibold text-reset">
                                                {{ !empty($contact->name) ? $contact->name : '—' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if (!empty($contact->email))
                                                <a href="mailto:{{ trim($contact->email) }}"
                                                    class="text-decoration-none fw-semibold">
                                                    {{ $contact->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if (!empty($contact->phone))
                                                <a href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}"
                                                    class="text-decoration-none fw-semibold">
                                                    {{ $contact->phone }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 text-nowrap">
                                                @if (!empty($contact->phone))
                                                    <a class="btn btn-outline-primary px-2"
                                                        href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}"
                                                        title="{{ __('Call') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-phone me-1" width="16"
                                                            height="16" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                        </svg>
                                                        <span>{{ __('Call') }}</span>
                                                    </a>
                                                @endif

                                                @if (!empty($contact->email))
                                                    <a class="btn btn-primary px-2"
                                                        href="mailto:{{ trim($contact->email) }}"
                                                        title="{{ __('Reply') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-mail-forward me-1"
                                                            width="16" height="16" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M12 18h-7a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v7.5" />
                                                            <path d="M3 6l9 6l9 -6" />
                                                            <path d="M15 18h6" />
                                                            <path d="M18 15l3 3l-3 3" />
                                                        </svg>
                                                        <span>{{ __('Reply') }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Pagination Bar --}}
                    <div id="custom-mobile-pagination"
                        class="d-flex d-md-none justify-content-between align-items-center px-3 py-3">
                        <button id="mobile-prev-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                            type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chevron-left me-1"
                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M15 6l-6 6l6 6"></path>
                            </svg>
                            {{ __('Previous') }}
                        </button>

                        <span id="mobile-page-info" class="text-muted small fw-semibold"></span>

                        <button id="mobile-next-btn" class="btn btn-primary btn-sm d-inline-flex align-items-center"
                            type="button">
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-chevron-right ms-1" width="18" height="18"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M9 6l6 6l-6 6"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            const total_columns = 6;

            const table = $('#contactsTable').DataTable({
                processing: false,
                serverSide: false,
                ordering: false,
                dom: "<'card-body d-flex align-items-center justify-content-between gap-2'<'text-muted d-none d-md-flex align-items-center'l><'d-flex align-items-center ms-auto w-100 w-md-auto'f>>" +
                    "<'table-responsive'tr>" +
                    "<'card-footer d-none d-md-flex align-items-center justify-content-between gap-2 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: ``,
                    sSearchPlaceholder: `{{ __('Search contacts...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    // SVG Empty State for Contacts
                    emptyTable: `
                        <div class="text-center py-4 w-100">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-users-minus">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4c.948 0 1.818 .33 2.504 .88" />
                                    <path d="M16 19h6" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Contacts Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('Visitors who reach out or save your card contacts will appear here.') }}</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="text-center py-4 w-100">
                            <div class="empty-state-icon" style="background-color: var(--tblr-danger-lt); color: var(--tblr-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-user-search">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h1.5" />
                                    <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M20.2 20.2l1.8 1.8" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching Contacts Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('We could not find any contact matching your search keyword.') }}</p>
                        </div>
                    `
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    // Add data-label attributes for mobile cards on valid rows
                    var headers = [];
                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $table.find('tbody tr').each(function() {
                        var $tr = $(this);
                        if ($tr.find('td.dataTables_empty').length === 0) {
                            $tr.find('td').each(function(index) {
                                if (headers[index]) {
                                    $(this).attr('data-label', headers[index]);
                                }
                            });
                        } else {
                            $tr.find('td.dataTables_empty').removeAttr('data-label');
                        }
                    });

                    // Mobile Pagination handler
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

            // Native CSV Export Handler
            $('#btn-export-csv').on('click', function() {
                let csv = ['\uFEFF"#","Source","Full Name","Email","Phone"'];
                table.rows({
                    search: 'applied'
                }).every(function(rowIdx, tableLoop, rowLoop) {
                    let $node = $(this.node());
                    let col0 = rowLoop + 1;
                    let col1 = $node.find('td').eq(1).text().trim().replace(/"/g, '""');
                    let col2 = $node.find('td').eq(2).text().trim().replace(/"/g, '""');
                    let col3 = $node.find('td').eq(3).text().trim().replace(/"/g, '""');
                    let col4 = $node.find('td').eq(4).text().trim().replace(/"/g, '""');
                    csv.push(`"${col0}","${col1}","${col2}","${col3}","${col4}"`);
                });

                let blob = new Blob([csv.join('\n')], {
                    type: 'text/csv;charset=utf-8;'
                });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'contacts_export.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Native Excel (.xls) Export Handler
            $('#btn-export-excel').on('click', function() {
                let tableHtml =
                    '<table border="1"><thead><tr><th>#</th><th>Source</th><th>Full Name</th><th>Email</th><th>Phone</th></tr></thead><tbody>';
                table.rows({
                    search: 'applied'
                }).every(function(rowIdx, tableLoop, rowLoop) {
                    let $node = $(this.node());
                    let col0 = rowLoop + 1;
                    let col1 = $node.find('td').eq(1).text().trim();
                    let col2 = $node.find('td').eq(2).text().trim();
                    let col3 = $node.find('td').eq(3).text().trim();
                    let col4 = $node.find('td').eq(4).text().trim();
                    tableHtml +=
                        `<tr><td>${col0}</td><td>${col1}</td><td>${col2}</td><td>${col3}</td><td>${col4}</td></tr>`;
                });
                tableHtml += '</tbody></table>';

                let blob = new Blob(['\uFEFF' + tableHtml], {
                    type: 'application/vnd.ms-excel;charset=utf-8'
                });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'contacts_export.xls';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Mobile Pagination Click Handlers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                table.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                table.page('next').draw('page');
            });
        });

        // 300ms Debounce search input logic
        var contactSearchTimer;
        $(document).on('input', '#contactsTable_filter input', function() {
            var value = this.value;
            var table = $('#contactsTable').DataTable();
            clearTimeout(contactSearchTimer);
            contactSearchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });
    </script>
@endsection
