@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- All Digital Business Cards CSS --}}
    <link href="{{ asset('css/all-digital-business-cards.css') }}" rel="stylesheet">
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
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Digital Business Cards') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Page Title & Help Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Digital Business Cards') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Design, customize, and manage your digital business cards and analyze dynamic profile engagement rates.') }}
                        </div>
                    </div>

                    <!-- Create Action Button -->
                    <div class="col-auto ms-auto d-print-none">
                        <a href="{{ route('user.choose.card.type') }}"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="18"
                                height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Create') }}</span>
                        </a>
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

                <!-- Cards Data Table Card -->
                <div class="cards-table-card">
                    <div class="table-responsive">
                        <table id="businessCardsTable"
                            class="table card-table table-vcenter table-hover text-nowrap responsive-card-table">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('#') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Link') }}</th>
                                    <th>{{ __('Views') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="w-1 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    {{-- Mobile Pagination Bar --}}
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

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Status Toggle Confirmation Modal --}}
    <div class="modal modal-blur fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-primary-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-alert-triangle text-primary" width="28"
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
                    <div class="text-muted">
                        {{ __('If you proceed, you will toggle the active status of this card.') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100"
                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-primary w-100" id="plan_id">{{ __('Yes, Proceed') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- QR Code Modal --}}
    <div class="modal modal-blur fade" id="openQR" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ __('Card QR Code') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p id="qr_card_name" class="text-muted mb-3" style="font-size:1rem;font-weight:600;"></p>
                    <div class="qr-code-box mb-3">
                        <div id="status_message_qr" class="qr-code"></div>
                    </div>
                    <p class="small text-muted mb-0">
                        {{ __('Scan with smartphone camera to view card directly.') }}
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center"
                                    onclick="downloadQr()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                        <polyline points="7 11 12 16 17 11"></polyline>
                                        <line x1="12" y1="4" x2="12" y2="16"></line>
                                    </svg>
                                    {{ __('Download') }}
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-primary w-100"
                                    data-bs-dismiss="modal">{{ __('Done') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Duplicate Card Modal --}}
    <div class="modal modal-blur fade" id="duplicateModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-primary-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy text-primary"
                            width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                            <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Duplicate Card') }}</h3>
                    <div id="duplicate_status" class="text-muted">
                        {{ __('A complete clone of this digital business card will be created.') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100"
                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-primary w-100" id="duplicate_id">{{ __('Yes, Duplicate') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Card Modal --}}
    <div class="modal modal-blur fade" id="forceDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-danger-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash text-danger"
                            width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 7l16 0" />
                            <path d="M10 11l0 6" />
                            <path d="M14 11l0 6" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Are you sure?') }}</h3>
                    <div id="delete_status" class="text-muted">
                        {{ __('This digital card and all associated records will be permanently deleted.') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100"
                                    data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-danger w-100" id="delete_id">{{ __('Yes, Delete') }}</a>
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
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            const total_columns = 8;

            const table = $('#businessCardsTable').DataTable({
                processing: false,
                serverSide: true,
                dom: "<'card-body d-flex align-items-center justify-content-between gap-2'<'text-muted d-none d-md-flex align-items-center'l><'d-flex align-items-center ms-auto w-100 w-md-auto'f>>" +
                    "<'table-responsive'tr>" +
                    "<'card-footer d-none d-md-flex align-items-center justify-content-between gap-2 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: '{{ route('user.cards') }}',
                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: ``,
                    sSearchPlaceholder: `{{ __('Search cards...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    // Empty state displays
                    emptyTable: `
                        <div class="text-center py-4">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-id">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <rect x="3" y="4" width="18" height="16" rx="3" />
                                    <circle cx="9" cy="10" r="2" />
                                    <line x1="15" y1="8" x2="17" y2="8" />
                                    <line x1="15" y1="12" x2="17" y2="12" />
                                    <line x1="7" y1="16" x2="17" y2="16" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Cards Created Yet') }}</h4>
                            <p class="text-muted small mb-0">{{ __('Create your first digital business card to start sharing.') }}</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="text-center py-4">
                            <div class="empty-state-icon" style="background-color: var(--tblr-danger-lt); color: var(--tblr-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-id">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <rect x="3" y="4" width="18" height="16" rx="3" />
                                    <circle cx="9" cy="10" r="2" />
                                    <line x1="15" y1="8" x2="17" y2="8" />
                                    <line x1="15" y1="12" x2="17" y2="12" />
                                    <line x1="7" y1="16" x2="17" y2="16" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching Cards Found') }}</h4>
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
                        name: 'created_at'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'card_url',
                        name: 'card_url'
                    },
                    {
                        data: 'views',
                        name: 'views'
                    },
                    {
                        data: 'card_status',
                        name: 'card_status'
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
                            .repeat(total_columns) +
                            '</tr>';
                    }
                    $tbody.html(placeholderRows);
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var $table = $(settings.nTable);

                    // Re-initialize Bootstrap / Tabler tooltips after Ajax data render
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    // Assign data-label attributes for mobile card rendering
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

            // Handle table processing state
            $('#businessCardsTable').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
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

        // Search debounce binding
        var cardSearchTimer;
        $(document).on('input', '#businessCardsTable_filter input', function() {
            var value = this.value;
            var table = $('#businessCardsTable').DataTable();
            clearTimeout(cardSearchTimer);
            cardSearchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });

        // Trigger Status Toggle Modal
        function toggleCardStatus(id) {
            "use strict";
            $("#deleteModal").modal("show");
            let url = "{{ route('user.card.status', ':id') }}".replace(':id', id);
            document.getElementById("plan_id").setAttribute("href", url);
        }

        // Trigger QR Code Modal
        function updateQr(url, cardName) {
            "use strict";
            // Show card name in the modal (used by downloadQr() too)
            $("#qr_card_name").text(cardName || '');
            $("#openQR").modal("show");
            if ($.fn.qrcode) {
                // Render as <img> so downloadQr() (public/js/user-delete-query.js) can export it
                $("#status_message_qr").empty().qrcode({
                    mode: 0,
                    ecLevel: 'H',
                    text: url,
                    render: 'image',
                    size: 180,
                    fill: '#000000',
                    background: '#ffffff'
                });
            } else {
                $("#status_message_qr").html('<a href="' + url + '" target="_blank">' + url + '</a>');
            }
        }

        // Trigger Duplicate Card Modal
        function duplicateCard(id, type) {
            "use strict";
            $("#duplicateModal").modal("show");
            document.getElementById("duplicate_status").innerHTML =
                "{{ __('If you proceed, you will duplicate this card.') }}";
            document.getElementById("duplicate_id").setAttribute("href", "{{ route('user.duplicate') }}?id=" + id +
                "&type=" + type);
        }

        // Trigger Delete Card Modal
        function deleteCard(id, action) {
            "use strict";
            $("#forceDeleteModal").modal("show");
            const delete_status = document.getElementById("delete_status");
            const message = `{{ __('If you proceed, this card will be :status.', ['status' => '_status_place_']) }}`;
            delete_status.innerHTML = message.replace('_status_place_', action);
            document.getElementById("delete_id").setAttribute("href", "{{ route('user.delete.card') }}?id=" + id);
        }
    </script>
@endsection
