@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.designs') }}" class="text-secondary text-decoration-none">
                                    {{ __('NFC Sales') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Activation Keys') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Activation Keys') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Generate, link, and manage NFC card activation keys.') }}
                        </div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            {{-- Generate NFC Card Key with mobile text wrapping safety --}}
                            <a href="{{ route('admin.create.key.generation') }}"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 5l0 14" />
                                    <path d="M5 12l14 0" />
                                </svg>
                                <span class="d-none d-sm-inline">{{ __('Generate') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
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

                <div class="row row-deck row-cards">
                    <div class="col-sm-12 col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 py-2">
                                <!-- Table renders normally on desktop and transforms automatically via CSS on mobile -->
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="nfc-card-key-generations-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Unique Key') }}</th>
                                            <th>{{ __('Card ID') }}</th>
                                            <th>{{ __('Associated Plan') }}</th>
                                            <th>{{ __('Associated NFC Card') }}</th>
                                            <th>{{ __('Link Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="custom-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top bg-white">
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
                </div>
            </div>
        </div>

        {{-- Link / Unlink status modal --}}
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

        {{-- Delete NFC card key generation --}}
        <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <div id="delete_action_status" class="text-secondary"></div>
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
                                    <a class="btn btn-danger w-100" id="delete_id">
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
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom scripts --}}
@section('scripts')
    {{-- Get nfc card key generations --}}
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var table = $('#nfc-card-key-generations-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.key.generations') }}",
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
                        data: 'created_at',
                        name: 'created_at',
                        className: 'rc-info-start'
                    },
                    {
                        data: 'unqiue_key',
                        name: 'unqiue_key',
                        className: 'rc-heading'
                    },
                    {
                        data: 'card_id',
                        name: 'card_id',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'associated_plan',
                        name: 'associated_plan',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'associated_nfc_card',
                        name: 'associated_nfc_card',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'link_status',
                        name: 'link_status',
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
                    // Add placeholder-glow class to table wrapper before rendering
                    $('#nfc-card-key-generations-table_wrapper').addClass('placeholder-glow');

                    // Desktop Placeholder Rows (Adjusted to match 6 columns)
                    if ($('#nfc-card-key-generations-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 10; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(8) + '</tr>';
                        }
                        $('#nfc-card-key-generations-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    // Remove the placeholder-glow class once the table is fully rendered
                    $('#nfc-card-key-generations-table_wrapper').removeClass('placeholder-glow');

                    // Clear any existing placeholder rows on desktop
                    $('#nfc-card-key-generations-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(5);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 6 columns correctly on desktop viewports
                    $('#nfc-card-key-generations-table tbody tr td.dataTables_empty').attr('colspan',
                        6);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#nfc-card-key-generations-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#nfc-card-key-generations-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });

                    // Update Custom Mobile Pagination Footer State dynamically
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    // Hide/show dynamic mobile footer based on pages total
                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#custom-mobile-pagination').addClass('d-none');
                        $('#custom-mobile-pagination').removeClass('d-flex');
                    } else {
                        $('#custom-mobile-pagination').removeClass('d-none');
                        $('#custom-mobile-pagination').addClass('d-flex');
                    }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#mobile-page-info').text(pageInfoText);

                    // Disable or enable prev boundaries
                    if (pageInfo.page === 0) {
                        $('#mobile-prev-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-prev-btn').removeClass('disabled').prop('disabled', false);
                    }

                    // Disable or enable next boundaries
                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#mobile-next-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-next-btn').removeClass('disabled').prop('disabled', false);
                    }
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing (e.g. searching)
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#nfc-card-key-generations-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 10; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(6) + '</tr>';
                    }
                    $('#nfc-card-key-generations-table tbody').html(placeholderRows);
                }
            });

            // Bind click events on Mobile pagination triggers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                table.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                table.page('next').draw('page');
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
            let messageStatus = status; // Status
            let message =
                `{{ __('If you continue, the business card/store link will be :status\ed.', ['status' => 'deleted']) }}`;
            modalMessage.innerHTML = message.replace(':status', status);

            // Status ID
            var link = document.getElementById("status_id");
            link.getAttribute("href");
            link.setAttribute("href", "{{ route('admin.action.key.generation') }}?id=" + pageId + "&status=" + status);
        }
    </script>

    {{-- Delete NFC card key generation --}}
    <script type="text/javascript">
        function deleteKey(keyId) {
            "use strict";

            // Show modal
            $("#delete-modal").modal("show");

            // Modal message
            var modalMessage = document.getElementById("delete_action_status");
            let message =
                "{{ __('If you continue, the business card/store link will be :status\ed.', ['status' => 'deleted']) }}";
            modalMessage.innerHTML = message.replace(':status', 'deleted');

            // Status ID
            var link = document.getElementById("delete_id");
            link.getAttribute("href");
            link.setAttribute("href", "{{ route('admin.delete.key') }}?id=" + keyId);
        }
    </script>
@endsection
