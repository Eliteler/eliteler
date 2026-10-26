@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }

        /* Responsive Mobile Empty Card state overrides */
        @media (max-width: 767.98px) {
            .responsive-card-table td.dataTables_empty {
                display: block !important;
                text-align: center !important;
                width: 100% !important;
                padding: 1.5rem 1rem !important;
                color: #6c757d !important;
            }

            .responsive-card-table td.dataTables_empty::before {
                content: none !important;
                display: none !important;
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
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('System Backups') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('System Backups') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Create and manage file and database backups to protect your data and restore it when needed.') }}
                        </div>
                        <div class="text-secondary mt-2">
                            <strong>{{ __('Note') }}</strong>: {{ __('Backups are stored in the') }} <code
                                class="text-danger bg-light px-1 rounded">storage/app/backups</code> {{ __('folder.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
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

                {{-- Success alert --}}
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

                <div class="row row-deck row-cards g-3">
                    {{-- Section 1: File Backups --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">{{ __('File Backups') }}</h4>
                                <a href="{{ route('admin.create.file.backup') }}" id="create-file-backup-btn"
                                    class="btn btn-primary btn-icon-only d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-file-code">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                                        <path d="M10 13l-1 2l1 2" />
                                        <path d="M14 13l1 2l-1 2" />
                                    </svg>
                                    <span class="d-none d-sm-inline">{{ __('Create Backup') }}</span>
                                    </a>
                                </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="files-backups-table">
                                                    <thead>
                                                        <tr>
                                            <th>{{ __('#') }}</th>
                                                            <th>{{ __('Version') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="files-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="files-prev-btn" class="btn btn-primary btn-sm" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>

                                <span id="files-page-info" class="text-secondary small font-weight-medium"></span>

                                <button id="files-next-btn" class="btn btn-primary btn-sm" type="button">
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

                    {{-- Section 2: Database Backups --}}
                    <div class="col-12 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">{{ __('Database Backups') }}</h4>
                                <a href="{{ route('admin.create.database.backup') }}" id="create-db-backup-btn"
                                    class="btn btn-primary btn-icon-only d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-file-database">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M8 12.75a4 1.75 0 1 0 8 0a4 1.75 0 1 0 -8 0" />
                                        <path d="M8 12.5v3.75c0 .966 1.79 1.75 4 1.75s4 -.784 4 -1.75v-3.75" />
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                                    </svg>
                                    <span class="d-none d-sm-inline">{{ __('Create Backup') }}</span>
                                    </a>
                                </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="database-backups-table">
                                                    <thead>
                                                        <tr>
                                            <th>{{ __('#') }}</th>
                                                            <th>{{ __('Version') }}</th>
                                                            <th>{{ __('Status') }}</th>
                                                            <th>{{ __('Actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="db-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="db-prev-btn" class="btn btn-primary btn-sm" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>

                                <span id="db-page-info" class="text-secondary small font-weight-medium"></span>

                                <button id="db-next-btn" class="btn btn-primary btn-sm" type="button">
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

    {{-- Delete modal --}}
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
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
                    <div id="delete-modal-text" class="text-secondary"></div>
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
                                <a class="btn btn-danger w-100" id="delete-backup">
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

@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            // Files Backups
            var tableFiles = $('#files-backups-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.backups') }}",
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
                        data: 'version',
                        name: 'version'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                },
                ],
                preDrawCallback: function(settings) {
                    $('#files-backups-table_wrapper').addClass('placeholder-glow');

                    if ($('#files-backups-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 5; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(4) + '</tr>';
                        }
                        $('#files-backups-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    $('#files-backups-table_wrapper').removeClass('placeholder-glow');

                    $('#files-backups-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(4);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 4 columns correctly on desktop viewports
                    $('#files-backups-table tr td.dataTables_empty').attr('colspan', 4);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#files-backups-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#files-backups-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });

                    // Update files pagination boundaries
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#files-mobile-pagination').addClass('d-none');
                    } else {
                        $('#files-mobile-pagination').removeClass('d-none');
                    }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#files-page-info').text(pageInfoText);

                    if (pageInfo.page === 0) {
                        $('#files-prev-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#files-prev-btn').removeClass('disabled').prop('disabled', false);
                    }

                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#files-next-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#files-next-btn').removeClass('disabled').prop('disabled', false);
                    }
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing (e.g. searching)
            tableFiles.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#files-backups-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(4) + '</tr>';
                    }
                    $('#files-backups-table tbody').html(placeholderRows);
                }
            });

            // Database backups
            var tableDb = $('#database-backups-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.get.database.backup') }}",
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
                        data: 'version',
                        name: 'version'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                },
                ],
                preDrawCallback: function(settings) {
                    $('#database-backups-table_wrapper').addClass('placeholder-glow');

                    if ($('#database-backups-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 5; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(4) + '</tr>';
                        }
                        $('#database-backups-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    $('#database-backups-table_wrapper').removeClass('placeholder-glow');

                    $('#database-backups-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(4);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 4 columns correctly on desktop viewports
                    $('#database-backups-table tr td.dataTables_empty').attr('colspan', 4);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#database-backups-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#database-backups-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });

                    // Update database pagination boundaries
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#db-mobile-pagination').addClass('d-none');
                    } else {
                        $('#db-mobile-pagination').removeClass('d-none');
                }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#db-page-info').text(pageInfoText);

                    if (pageInfo.page === 0) {
                        $('#db-prev-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#db-prev-btn').removeClass('disabled').prop('disabled', false);
                    }

                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#db-next-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#db-next-btn').removeClass('disabled').prop('disabled', false);
                    }
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing (e.g. searching)
            tableDb.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#database-backups-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(4) + '</tr>';
                    }
                    $('#database-backups-table tbody').html(placeholderRows);
                }
            });

            // Bind click events on Files pagination triggers
            $('#files-prev-btn').on('click', function(e) {
                e.preventDefault();
                tableFiles.page('previous').draw('page');
            });

            // Bind click events on Files pagination triggers
            $('#files-next-btn').on('click', function(e) {
                e.preventDefault();
                tableFiles.page('next').draw('page');
            });

            // Bind click events on Database pagination triggers
            $('#db-prev-btn').on('click', function(e) {
                e.preventDefault();
                tableDb.page('previous').draw('page');
            });

            // Bind click events on Database pagination triggers
            $('#db-next-btn').on('click', function(e) {
                e.preventDefault();
                tableDb.page('next').draw('page');
            });
        });

        // Delete Backup
        function deleteBackup(backupId) {
            "use strict";

            var deleteModalEl = document.getElementById('delete-modal');
            var deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            deleteModal.show();
            
            var delete_status = document.getElementById("delete-modal-text");
            delete_status.innerHTML = `{{ __('If you proceed, you will') }} ` + `{{ __('delete') }}` +
                ` {{ __('this backup.') }}`;
            var deleteLink = document.getElementById("delete-backup");
            deleteLink.setAttribute("href", "{{ route('admin.backup.delete') }}?id=" + backupId);
            deleteLink.setAttribute("class", "btn btn-danger btn-4 w-100");
            deleteLink.innerHTML = "<?php echo __('Delete'); ?>";
        }

        // Prevent multiple backup creation
        $('#create-file-backup-btn, #create-db-backup-btn').on('click', function(e) {
            const $btn = $(this);

            if ($btn.hasClass('disabled')) {
                e.preventDefault();
                return false;
            }

            $btn.addClass('disabled');
            $btn.css('pointer-events', 'none');

            $btn.html(`
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        {{ __('Backup in progress...') }}
    `);
        });
    </script>
@endsection
