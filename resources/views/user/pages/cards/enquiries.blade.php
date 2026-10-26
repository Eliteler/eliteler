@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Forced traditional mobile responsive scrolling and visible pagination) --}}
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

@php
    $enquiriesLimit = __('Unlimited');
    if ($plan_details->no_of_enquires != 999) {
        $enquiriesLimit = number_format($plan_details->no_of_enquires);
    }
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page title (Wraps the inner container-fluid) -->
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
                                {{ __('Enquiries') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Enquiries') }}
                        </h2>

                        <!-- Descriptive Help Subtitle containing limit parameters natively -->
                        <div class="text-muted mt-1">
                            {{ __('Monitor incoming customer inquiries, manage dynamic leads data, and send quick replies. Based on your current subscription, you can find a maximum of ') }}
                            <span class="text-danger fw-bold">{{ $enquiriesLimit }}</span>
                            {{ __(' inquiries here.') }}
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
                            <table class="table card-table table-vcenter text-nowrap" id="enquriesTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Mobile Number') }}</th>
                                        <th>{{ __('Message') }}</th>
                                        <th class="w-1 text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($businessEnquries as $enquiry)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><span
                                                    class="fw-bold text-secondary">{{ formatDateForUser($card->created_at ?? $enquiry->created_at) }}</span>
                                            </td>
                                            <td><span class="text-capitalize fw-bold text-dark">{{ $enquiry->name }}</span>
                                            </td>
                                            <td><a href="mailto:{{ $enquiry->email }}"
                                                    class="text-decoration-none fw-bold">{{ $enquiry->email }}</a></td>
                                            <td>
                                                @if (!empty($appointment->phone ?? $enquiry->phone))
                                                    <a href="tel:{{ $enquiry->phone }}"
                                                        class="text-decoration-none fw-bold">{{ $enquiry->phone }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-wrap" style="max-width: 250px;">
                                                {{ $enquiry->notes ?? ($enquiry->message ?? '-') }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-white btn-sm" href="mailto:{{ $enquiry->email }}">
                                                    {{ __('Reply') }}
                                                </a>
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
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 7;

            const table = $('#enquriesTable').DataTable({
                processing: false, // Customized processing event listener handles skeletons
                serverSide: false, // Standard static client-side mode
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
                preDrawCallback: function(settings) {
                    // Corrected target wrapper ID to match Datatable's naming pattern (Omitted invalid hyphen)
                    $('#enquriesTable_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#enquriesTable_wrapper').removeClass('placeholder-glow');

                    // Aligns standard colspan on empty triggers
                    $('#enquriesTable tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#enquriesTable').wrap('<div class="table-responsive px-0 py-2"></div>');

            // Implement static 350ms debounced search layout execution
            $('.dataTables_filter input').unbind().on('keyup', function() {
                const searchValue = this.value;
                clearTimeout(window.searchTimeout);

                $('#enquriesTable_wrapper').addClass('placeholder-glow');
                let placeholderRows = '';
                for (let i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#enquriesTable tbody').html(placeholderRows);

                window.searchTimeout = setTimeout(function() {
                    table.search(searchValue).draw();
                }, 350);
            });
        });
    </script>
@endsection
