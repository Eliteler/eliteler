@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Pages') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Pages') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('Manage your supported static system pages and register custom rich-text content pages.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">

                {{-- Failed --}}
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

                {{-- Success --}}
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

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <h2 class="page-title mb-1">
                            {{ __('Static Pages') }}
                        </h2>
                        <span class="text-muted small mb-3 d-block">
                            {{ __('Note: Static pages do not contain an HTML editor. You can update page configurations and statuses.') }}
                        </span>
                    </div>

                    {{-- Static Pages Card --}}
                    <div class="col-sm-12 col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="pages-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Page') }}</th>
                                            <th>{{ __('Slug') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Pages Header --}}
                    <div class="col-12 mt-4">
                        <div class="row align-items-center">
                            <div class="col">
                                <h2 class="page-title">
                                    {{ __('Custom Pages') }}
                                </h2>
                            </div>
                            <!-- Add page button -->
                            <div class="col-auto ms-auto d-print-none">
                                <a href="{{ route('admin.add.page') }}"
                                    class="btn btn-primary d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    {{ __('Add New Page') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Pages Card --}}
                    <div class="col-sm-12 col-lg-12 mt-2">
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="custom-pages-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Page') }}</th>
                                            <th>{{ __('Slug') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Enable/Disable Page Modal --}}
    <div class="modal modal-bg fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <div id="disable_status" class="text-secondary"></div>
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

    {{-- Delete Page Modal --}}
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
                    <div id="delete_status" class="text-secondary"></div>
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
    {{-- Static pages DT setup --}}
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var pagesTable = $('#pages-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.pages') }}",
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
                        name: 'page_name',
                        className: 'rc-heading'
                    },
                    {
                        data: 'url',
                        name: 'url',
                        className: 'rc-info-start',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
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
                    $('#pages-table_wrapper').addClass('placeholder-glow');

                    if ($('#pages-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 5; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(5) + '</tr>';
                        }
                        $('#pages-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    $('#pages-table_wrapper').removeClass('placeholder-glow');

                    $('#pages-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(4);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    $('#pages-table tbody tr td.dataTables_empty').attr('colspan', 5);

                    var headers = [];
                    $('#pages-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#pages-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });
                }
            });

            // Trigger skeleton rows on search
            pagesTable.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#pages-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(5) + '</tr>';
                    }
                    $('#pages-table tbody').html(placeholderRows);
                }
            });
        });

        function getDisablePage(pageName, status) {
            "use strict";

            $("#status-modal").modal("show");

            var disable_status = document.getElementById("disable_status");
            let messageStatus = status;
            let message = `{{ __('If you proceed, this page will be :status.', ['status' => '${messageStatus}']) }}`;
            disable_status.innerHTML = message.replace(':status', status);

            var link = document.getElementById("page_id");
            link.setAttribute("href", "{{ route('admin.disable.page') }}?id=" + pageName);
        }
    </script>

    {{-- Custom pages DT setup --}}
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var customPagesTable = $('#custom-pages-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.custom.pages') }}",
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
                        name: 'section_name',
                        className: 'rc-heading'
                    },
                    {
                        data: 'url',
                        name: 'url',
                        className: 'rc-info-start',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
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
                    $('#custom-pages-table_wrapper').addClass('placeholder-glow');

                    if ($('#custom-pages-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 5; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(5) + '</tr>';
                        }
                        $('#custom-pages-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    $('#custom-pages-table_wrapper').removeClass('placeholder-glow');

                    $('#custom-pages-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(4);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    $('#custom-pages-table tbody tr td.dataTables_empty').attr('colspan', 5);

                    var headers = [];
                    $('#custom-pages-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#custom-pages-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });
                }
            });

            // Trigger skeleton rows on search
            customPagesTable.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#custom-pages-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(5) + '</tr>';
                    }
                    $('#custom-pages-table tbody').html(placeholderRows);
                }
            });
        });

        function getPage(pageId, status) {
            "use strict";

            $("#status-modal").modal("show");

            var disable_status = document.getElementById("disable_status");
            let messageStatus = status;
            let message = `{{ __('If you proceed, this page will be :status.', ['status' => '${messageStatus}']) }}`;
            disable_status.innerHTML = message.replace(':status', status);

            var link = document.getElementById("page_id");
            link.setAttribute("href", "{{ route('admin.status.page') }}?id=" + pageId);
        }

        function deletePage(pageId, status) {
            "use strict";

            $("#delete-modal").modal("show");

            var delete_status = document.getElementById("delete_status");
            let messageStatus = status;
            let message = `{{ __('If you proceed, this page will be :status.', ['status' => '${messageStatus}']) }}`;
            delete_status.innerHTML = message.replace(':status', status);

            var delete_link = document.getElementById("delete_qr_code_id");
            delete_link.setAttribute("href", "{{ route('admin.delete.page') }}?id=" + pageId);
        }
    </script>
@endsection
