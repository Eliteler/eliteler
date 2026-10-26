@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Responsive pagination alignment and search layout rules) --}}
@section('css')
    <link href="{{ asset('plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/datatable/css/buttons.bootstrap5.min.css') }}" rel="stylesheet">

    <style>
        .btn {
            border-radius: 12px !important;
            font-size: small !important;
            padding: 10px 18px !important;
        }

        div.dt-container div.dt-search input {
            height: 40px !important;
            font-size: 14px !important;
        }

        /* Desktop Mode: Force pagination alignment cleanly to the right side */
        @media (min-width: 768px) {
            .dataTables_wrapper .dataTables_paginate {
                display: flex !important;
                justify-content: flex-end !important;
                margin-left: auto !important;
                /* Pulls pagination cleanly to the right boundary margin */
                width: auto !important;
            }

            /* Overwrite Tabler's global flex centering on list elements with high CSS specificity */
            .dataTables_wrapper .pagination,
            .dataTables_wrapper .dataTables_paginate .pagination,
            .dataTables_wrapper div.dataTables_paginate ul.pagination {
                display: flex !important;
                justify-content: flex-end !important;
                margin: 0 !important;
            }
        }

        /* Mobile Mode: Stack column structures, center pagination, and optimize search inputs */
        @media (max-width: 767.98px) {

            /* Hide the DataTables informational statistics summary exclusively on mobile viewports */
            .dataTables_wrapper .dataTables_info {
                display: none !important;
            }

            /* Transform the bottom wrapper from row-flex to column-flex */
            div.dataTables_wrapper .d-flex.justify-content-between {
                flex-direction: column !important;
                justify-content: center !important;
                text-align: center !important;
                gap: 12px !important;
            }

            div.dataTables_wrapper .dataTables_paginate {
                display: flex !important;
                justify-content: center !important;
                float: none !important;
                margin-top: 1rem !important;
                width: 100% !important;
                padding: 0 0.5rem !important;
            }

            div.dataTables_wrapper .dataTables_paginate ul.pagination {
                display: flex !important;
                flex-wrap: wrap !important;
                /* Wraps button arrays smoothly on narrow viewports to avoid overflow clipping */
                justify-content: center !important;
                padding-left: 0 !important;
                list-style: none !important;
                gap: 4px !important;
                margin: 0 auto !important;
            }

            div.dataTables_wrapper .dataTables_paginate ul.pagination .page-item .page-link {
                padding: 4px 8px !important;
                /* Compact mobile touch targets */
                font-size: 11px !important;
                border-radius: 4px !important;
            }

            /* Enforce full-width block alignment for the search input on mobile viewports */
            .dataTables_wrapper .dataTables_filter {
                width: 100% !important;
                margin-top: 0.5rem !important;
                text-align: left !important;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                display: block !important;
                margin-left: 0 !important;
            }

            /* Enable touch horizontal scrolling inside table boundaries */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                border-bottom: 1px solid rgba(101, 109, 119, 0.16);
            }

            div.dt-container div.dt-search input {
                width: 100% !important;
                display: block !important;
                margin-left: 0 !important;
                height: 40px !important;
                font-size: 14px !important;
            }

            #emailSubscribed_info {
                display: none !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
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
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Subscribed Emails') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Subscribed Emails') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Review campaign subscription logs, verify registered client emails, and export contact registers.') }}
                        </div>
                    </div>

                    {{-- Custom Buttons Destination Element --}}
                    <div class="col-auto text-end">
                        <div id="customButtonsContainer" class="d-inline-flex gap-2"></div>
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
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Clean design-time padding) -->
                        <div class="card border-0 shadow-sm p-3">
                            <!-- Removed design-time table-responsive wrapper so Datatables elements stay static -->
                            <table class="table card-table table-vcenter text-nowrap" id="emailSubscribed">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Email') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subscripedEmails as $subscripedEmail)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><span
                                                    class="fw-bold text-secondary">{{ formatDateForUser($card->created_at ?? $subscripedEmail->created_at) }}</span>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $subscripedEmail->email }}"
                                                    class="text-decoration-none fw-bold">{{ $subscripedEmail->email }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
    <script src="{{ asset('plugins/datatable/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/buttons.html5.min.js') }}"></script>

    <!-- JSZip for Excel export -->
    <script src="{{ asset('plugins/datatable/js/jszip.min.js') }}"></script>
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 3;

            const table = $('#emailSubscribed').DataTable({
                responsive: false, // Omitted stacked transformations to use horizontal tables
                ordering: false,
                // Integrated 'f' selector parameter cleanly with responsive columns structure
                dom: "<'row mb-3 align-items-center g-3'<'col-12 col-md-6'l><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'d-flex justify-content-between align-items-center mt-3'ip>",
                buttons: [{
                        extend: 'csv',
                        text: '{{ __('CSV') }}',
                        className: 'btn btn-sm btn-success force-class-csv',
                        filename: 'newsletter_subscribed_emails',
                        exportOptions: {
                            columns: [0, 1, 2],
                            format: {
                                header: function(data, columnIdx) {
                                    switch (columnIdx) {
                                        case 0:
                                            return '#';
                                        case 1:
                                            return 'Date';
                                        case 2:
                                            return 'Email';
                                        default:
                                            return data;
                                    }
                                }
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        text: '{{ __('Excel') }}',
                        className: 'btn btn-sm btn-success force-class-excel',
                        filename: 'newsletter_subscribed_emails',
                        exportOptions: {
                            columns: [0, 1, 2],
                            format: {
                                header: function(data, columnIdx) {
                                    switch (columnIdx) {
                                        case 0:
                                            return '#';
                                        case 1:
                                            return 'Date';
                                        case 2:
                                            return 'Email';
                                        default:
                                            return data;
                                    }
                                }
                            }
                        }
                    }
                ],
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
                preDrawCallback: function(settings) {
                    $('#emailSubscribed_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#emailSubscribed_wrapper').removeClass('placeholder-glow');

                    // Match empty display widths
                    $('#emailSubscribed tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#emailSubscribed').wrap('<div class="table-responsive px-0 py-2"></div>');

            // Move button container dynamically to aligned page header block
            table.buttons().container().appendTo('#customButtonsContainer');

            // Implement static 350ms debounced filters execution with matching skeletons
            $('.dataTables_filter input').unbind().on('keyup', function() {
                const searchValue = this.value;
                clearTimeout(window.searchTimeout);

                $('#emailSubscribed_wrapper').addClass('placeholder-glow');
                let placeholderRows = '';
                for (let i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#emailSubscribed tbody').html(placeholderRows);

                window.searchTimeout = setTimeout(function() {
                    table.search(searchValue).draw();
                }, 350);
            });

            // Re-apply standard Bootstrap utility layout classes to DataTable export nodes
            const applyButtonClasses = () => {
                document.querySelectorAll('.force-class-csv, .force-class-excel').forEach(btn => {
                    btn.classList.remove('dt-button');
                    btn.classList.add('btn', 'btn-primary', 'btn-sm', 'me-2');
                });
            };
            setTimeout(applyButtonClasses, 100);
        });
    </script>
@endsection
