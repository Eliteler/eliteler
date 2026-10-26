@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Forced responsiveness for horizontal scrolling and visible mobile navigation) --}}
@section('css')
    <style>
        @media (max-width: 767.98px) {

            /* Force standard DataTables elements to remain visible and center-aligned on mobile */
            .dataTables_wrapper .dataTables_paginate {
                display: flex !important;
                justify-content: center !important;
                float: none !important;
                margin-top: 1rem !important;
                width: 100% !important;
                padding: 0 0.5rem !important;
            }

            /* Resolve pagination overflow by enabling clean flex-wrapping and compact scale on small screens */
            .dataTables_wrapper .pagination {
                display: flex !important;
                flex-wrap: wrap !important;
                /* Wraps button arrays smoothly on narrow viewports to avoid overflow clipping */
                justify-content: center !important;
                padding-left: 0 !important;
                list-style: none !important;
                gap: 4px !important;
                /* Clean button alignment margins */
            }

            .dataTables_wrapper .pagination .page-item .page-link {
                padding: 4px 8px !important;
                /* Compact scaling for screen layouts under 768px */
                font-size: 11px !important;
                border-radius: 4px !important;
            }

            .dataTables_wrapper .dataTables_info {
                display: block !important;
                float: none !important;
                text-align: center !important;
                margin-bottom: 0.5rem !important;
                font-size: 11px !important;
            }

            /* Smooth touch scrolling momentum specifically for the table */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
                border-bottom: 1px solid rgba(101, 109, 119, 0.16);
            }
        }
    </style> 
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header (Wraps the inner container-fluid) -->
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
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Visitors') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Visitors') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Analyze profile engagement rates, review geographical IP coordinates, and track client platform metrics.') }}
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
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Clean design-time padding) -->
                        <div class="card border-0 shadow-sm p-3">
                            <!-- Removed design-time table-responsive wrapper so Datatables elements stay static -->
                            <table class="table card-table table-vcenter text-nowrap" id="visitorsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('IP') }}</th>
                                        <th>{{ __('Language') }}</th>
                                        <th>{{ __('Platform') }}</th>
                                        <th>{{ __('Browser') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('user.includes.footer')
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 6;

            const table = $('#visitorsTable').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('user.visitors.data', $id) }}",
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
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address'
                    },
                    {
                        data: 'language',
                        name: 'language'
                    },
                    {
                        data: 'platform',
                        name: 'platform'
                    },
                    {
                        data: 'user_agent',
                        name: 'user_agent'
                    }
                ],
                preDrawCallback: function(settings) {
                    $('#visitorsTable_wrapper').addClass('placeholder-glow');

                    // Generates skeleton rows for desktop and traditional mobile horizontal views
                    let placeholderRows = '';
                    for (let i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(totalColumns) + '</tr>';
                    }
                    $('#visitorsTable tbody').html(placeholderRows);
                },
                drawCallback: function(settings) {
                    $('#visitorsTable_wrapper').removeClass('placeholder-glow');

                    // Aligns column dimensions correctly for empty results
                    $('#visitorsTable tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#visitorsTable').wrap('<div class="table-responsive px-0 py-2"></div>');

            // Aligns processing hook directly to dynamic server-side triggers
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#visitorsTable_wrapper').addClass('placeholder-glow');
                    let placeholderRows = '';
                    for (let i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(totalColumns) + '</tr>';
                    }
                    $('#visitorsTable tbody').html(placeholderRows);
                } else {
                    $('#visitorsTable_wrapper').removeClass('placeholder-glow');
                }
            });
        });
    </script>
@endsection
