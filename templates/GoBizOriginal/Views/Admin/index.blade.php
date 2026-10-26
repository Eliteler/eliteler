@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom Styles --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumbs -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-templates') }}" class="text-secondary text-decoration-none">
                                    {{ __('Web Templates') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-template.gobiz-original') }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('GoBiz Original') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Pages') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Pages') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure system static pages, manage dynamic custom sections, toggle routing paths, and update public content rules.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
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

                {{-- Success Alert --}}
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

                <div class="row row-cards">
                    {{-- Alert Advisory for Static Pages --}}
                    <div class="col-12">
                        <div class="alert alert-important alert-info shadow-sm border-0 mb-3" role="alert">
                            <div class="d-flex">
                                <div>
                                    {{ __('Note: Static pages do not utilize raw HTML Editors. Content structures are updated directly inside pre-compiled localized parameters only.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Static Pages Section --}}
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                <h3 class="card-title fw-bold m-0">{{ __('Static System Pages') }}</h3>
                            </div>
                            <!-- Standard table container with custom padding wrappers -->
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="pages-table" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Page') }}</th>
                                            <th>{{ __('Slug') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Rendered via Ajax --}}
                                    </tbody>
                                </table>
                            </div>

                            {{-- Dynamic Pages Mobile Pagination Footer --}}
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

                    {{-- Custom Pages Section --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div
                                class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pt-4 px-4 pb-0">
                                <h3 class="card-title fw-bold m-0">{{ __('Custom Pages') }}</h3>

                                <a href="{{ route('admin.web-template.gobiz-original.add-custom-page') }}"
                                    class="btn btn-primary btn-md btn-icon-only d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus me-1"
                                        width="20" height="20" viewBox="0 0 24 24" stroke-width="2.5"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span class="d-none d-sm-inline">{{ __('Create New') }}</span>
                                </a>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="custom-pages-table" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Page') }}</th>
                                            <th>{{ __('Slug') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Rendered via Ajax --}}
                                    </tbody>
                                </table>
                            </div>

                            {{-- Dynamic Custom Pages Mobile Pagination Footer --}}
                            <div id="custom-pages-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="custom-pages-mobile-prev" class="btn btn-primary btn-sm" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>

                                <span id="custom-pages-mobile-info"
                                    class="text-secondary small font-weight-medium"></span>

                                <button id="custom-pages-mobile-next" class="btn btn-primary btn-sm" type="button">
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

    {{-- Status Toggle Dialog Modal --}}
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
                    <div id="disable_status" class="text-muted"></div>
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
                                <a class="btn btn-danger w-100" id="page_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Custom Page Modal --}}
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
                    <div id="delete_status" class="text-muted"></div>
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
                                <a class="btn btn-danger w-100" id="delete_qr_code_id">
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
    {{-- Static Pages DataTables --}}
    <script type="text/javascript">
        $(document).ready(function() {
            var pagesTable = $('#pages-table');
            var pages_columns = pagesTable.find('thead th').length;

            var pagesTableInstance = pagesTable.DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.web-template.gobiz-original') }}",
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
                        data: 'page_name',
                        name: 'page_name'
                    },
                    {
                        data: 'url',
                        name: 'url',
                        orderable: false,
                        searchable: false
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
                    }
                ],
                preDrawCallback: function(settings) {
                    pagesTable.parent().addClass('placeholder-glow');

                    // Unconditionally inject skeleton loaders for active search dynamic response
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(pages_columns) + '</tr>';
                    }
                    pagesTable.find('tbody').html(placeholderRows);
                },
                drawCallback: function(settings) {
                    pagesTable.parent().removeClass('placeholder-glow');

                    pagesTable.find('tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(4);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    var emptyRow = pagesTable.find('tbody tr td.dataTables_empty');
                    if (emptyRow.length > 0) {
                        emptyRow.attr('colspan', pages_columns);
                    }

                    // Dynamically map target keys to TD data-label attributes for mobile responsive cards
                    var headers = [];
                    pagesTable.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    pagesTable.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index] || '');
                        });
                    });

                    // Mobile Pagination Footer State Handling
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

                    if (pageInfo.page === 0) {
                        $('#mobile-prev-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-prev-btn').removeClass('disabled').prop('disabled', false);
                    }

                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#mobile-next-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-next-btn').removeClass('disabled').prop('disabled', false);
                    }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#mobile-page-info').text(pageInfoText);
                }
            });

            // Handle live AJAX processing feedback smoothly
            pagesTableInstance.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    pagesTable.parent().addClass('placeholder-glow');
                } else {
                    pagesTable.parent().removeClass('placeholder-glow');
                }
            });

            // Mobile Pagination Bindings
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                pagesTableInstance.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                pagesTableInstance.page('next').draw('page');
            });
        });

        function updatePageStatus(id, status, type) {
            "use strict";

            $("#status-modal").modal("show");

            var disable_status = document.getElementById("disable_status");
            let messageStatus = status;
            let message = `{{ __('If you proceed, this page will be :status.', ['status' => '${messageStatus}']) }}`;
            disable_status.innerHTML = message.replace(':status', status);

            var link = document.getElementById("page_id");
            link.setAttribute("href", "{{ route('admin.web-template.gobiz-original.update-page-status') }}?id=" + id +
                "&type=" + type);
        }
    </script>

    {{-- Custom Pages DataTables --}}
    <script type="text/javascript">
        $(document).ready(function() {
            var customPagesTable = $('#custom-pages-table');
            var custom_columns = customPagesTable.find('thead th').length;

            var customPagesTableInstance = customPagesTable.DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.web-template.gobiz-original.custom-pages') }}",
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
                        data: 'section_name',
                        name: 'section_name'
                    },
                    {
                        data: 'url',
                        name: 'url',
                        orderable: false,
                        searchable: false
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
                    }
                ],
                preDrawCallback: function(settings) {
                    customPagesTable.parent().addClass('placeholder-glow');

                    // Unconditionally inject skeleton loaders for active search dynamic response
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(custom_columns) + '</tr>';
                    }
                    customPagesTable.find('tbody').html(placeholderRows);
                },
                drawCallback: function(settings) {
                    customPagesTable.parent().removeClass('placeholder-glow');

                    customPagesTable.find('tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(4);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    var emptyRow = customPagesTable.find('tbody tr td.dataTables_empty');
                    if (emptyRow.length > 0) {
                        emptyRow.attr('colspan', custom_columns);
                    }

                    // Dynamically map target keys to TD data-label attributes for mobile responsive cards
                    var headers = [];
                    customPagesTable.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    customPagesTable.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index] || '');
                        });
                    });

                    // Custom Pages Mobile Pagination Footer State Handling
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#custom-pages-mobile-pagination').addClass('d-none').removeClass('d-flex');
                    } else {
                        $('#custom-pages-mobile-pagination').removeClass('d-none').addClass('d-flex');
                    }

                    if (pageInfo.page === 0) {
                        $('#custom-pages-mobile-prev').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#custom-pages-mobile-prev').removeClass('disabled').prop('disabled', false);
                    }

                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#custom-pages-mobile-next').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#custom-pages-mobile-next').removeClass('disabled').prop('disabled', false);
                    }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#custom-pages-mobile-info').text(pageInfoText);
                }
            });

            // Handle live AJAX processing feedback smoothly
            customPagesTableInstance.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    customPagesTable.parent().addClass('placeholder-glow');
                } else {
                    customPagesTable.parent().removeClass('placeholder-glow');
                }
            });

            // Custom Pages Mobile Pagination Bindings
            $('#custom-pages-mobile-prev').on('click', function(e) {
                e.preventDefault();
                customPagesTableInstance.page('previous').draw('page');
            });

            $('#custom-pages-mobile-next').on('click', function(e) {
                e.preventDefault();
                customPagesTableInstance.page('next').draw('page');
            });
        });

        function deletePage(pageId, status) {
            "use strict";

            $("#delete-modal").modal("show");

            var delete_status = document.getElementById("delete_status");
            let messageStatus = status;
            let message = `{{ __('If you proceed, this page will be :status.', ['status' => '${messageStatus}']) }}`;
            delete_status.innerHTML = message.replace(':status', status);

            var delete_link = document.getElementById("delete_qr_code_id");
            delete_link.setAttribute("href", "{{ route('admin.web-template.gobiz-original.delete-page') }}?id=" + pageId);
        }
    </script>
@endsection
