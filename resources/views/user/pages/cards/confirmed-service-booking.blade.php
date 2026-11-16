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

        /* Desktop specific styling */
        @media (min-width: 768px) {
            .booking-notes-col {
                max-width: 160px;
                white-space: normal;
                word-break: break-word;
            }
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

            /* Actions dropdown trigger alignment */
            .responsive-card-table tbody td[data-label="Actions"] .btn-action {
                padding: 4px !important;
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
                                    {{ __('Service Bookings') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Page Title & Help Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Service Bookings') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Configure appointments, schedule check-ins, record reservation requests, and export records data.') }}
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
                        <table class="table card-table table-vcenter table-hover text-nowrap responsive-card-table"
                            id="serviceBookingTable">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('#') }}</th>
                                    <th>{{ __('Service Booking ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Mobile Number') }}</th>
                                    <th>{{ __('Address') }}</th>
                                    <th>{{ __('Check-in') }}</th>
                                    <th>{{ __('Check-out') }}</th>
                                    <th class="text-center">{{ __('Guests') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Booking Status') }}</th>
                                    <th class="w-1 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($serviceBooking as $booking)
                                    <tr>
                                        <td class="rc-hide">{{ $loop->iteration }}</td>

                                        <td>
                                            <span class="text-uppercase fw-bold text-reset">
                                                {{ $booking->service_booking_id }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-capitalize fw-semibold text-reset">
                                                {{ !empty($booking->name) ? $booking->name : '—' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if (!empty($booking->email))
                                                <a href="mailto:{{ trim($booking->email) }}"
                                                    class="text-decoration-none fw-semibold">{{ $booking->email }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if (!empty($booking->phone))
                                                <a href="tel:{{ preg_replace('/\s+/', '', $booking->phone) }}"
                                                    class="text-decoration-none fw-semibold">{{ $booking->phone }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span
                                                class="text-secondary">{{ !empty($booking->address) ? $booking->address : '—' }}</span>
                                        </td>

                                        <td>
                                            <span
                                                class="text-secondary fw-semibold">{{ formatDateForUser($booking->checkin) }}</span>
                                        </td>

                                        <td>
                                            <span
                                                class="text-secondary fw-semibold">{{ formatDateForUser($booking->checkout) }}</span>
                                        </td>

                                        <td class="text-center fw-semibold">
                                            {{ $booking->number_of_guests }}
                                        </td>

                                        <td class="booking-notes-col">
                                            <span class="text-secondary small">
                                                {{ !empty($booking->notes) ? $booking->notes : '—' }}
                                            </span>
                                        </td>

                                        <td>
                                            @switch($booking->booking_status)
                                                @case('pending')
                                                    <span
                                                        class="badge bg-warning-lt px-2 py-1 text-uppercase">{{ __('Pending') }}</span>
                                                @break

                                                @case('confirmed')
                                                    <span
                                                        class="badge bg-info-lt px-2 py-1 text-uppercase">{{ __('Confirmed') }}</span>
                                                @break

                                                @case('rejected')
                                                    <span
                                                        class="badge bg-danger-lt px-2 py-1 text-uppercase">{{ __('Canceled') }}</span>
                                                @break

                                                @case('completed')
                                                    <span
                                                        class="badge bg-success-lt px-2 py-1 text-uppercase">{{ __('Completed') }}</span>
                                                @break

                                                @default
                                                    <span
                                                        class="badge bg-warning-lt px-2 py-1 text-uppercase">{{ __('Pending') }}</span>
                                            @endswitch
                                        </td>

                                        <td class="text-end">
                                            @if ($booking->booking_status != 'completed')
                                                <div class="dropdown">
                                                    <a class="btn-action text-decoration-none" href="#"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20"
                                                            height="20" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icon-tabler-dots-vertical">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <circle cx="12" cy="12" r="1" />
                                                            <circle cx="12" cy="19" r="1" />
                                                            <circle cx="12" cy="5" r="1" />
                                                        </svg>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <a onclick="UpdateServiceBooking(`{{ $booking->service_booking_id }}`)"
                                                            class="dropdown-item cursor-pointer">
                                                            {{ __('Update Status') }}
                                                        </a>

                                                        @if ($booking->booking_status != 'rejected')
                                                            <a onclick="completeServiceBooking(`{{ $booking->service_booking_id }}`)"
                                                                class="dropdown-item cursor-pointer">
                                                                {{ __('Complete') }}
                                                            </a>

                                                            <a onclick="addMyGoogleCalendar(`{{ $booking->service_booking_id }}`)"
                                                                class="dropdown-item cursor-pointer">
                                                                {{ __('Add My Google Calendar') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
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

    {{-- Accept or Cancel Modal --}}
    <div class="modal fade" id="acceptServiceBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-warning-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-alert-triangle text-warning" width="28"
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
                    <div id="accept_service_booking_status" class="text-muted"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col-4">
                                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col-4">
                                <a data-method="post" class="btn btn-danger w-100" id="cancel_service_booking_id">
                                    {{ __('Reject') }}
                                </a>
                            </div>
                            <div class="col-4">
                                <a data-method="post" class="btn btn-primary w-100" id="accept_service_booking_id">
                                    {{ __('Accept') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Complete Confirmation Modal --}}
    <div class="modal fade" id="completeServiceBookingModal" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-success-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check text-success"
                            width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l5 5l10 -10" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Confirm Status Update') }}</h3>
                    <div id="complete_service_booking_status" class="text-muted"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-success w-100" id="complete_service_booking_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Google Calendar Modal --}}
    <div class="modal fade" id="addMyGoogleCalendarModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-info-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar-plus text-info">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" />
                            <path d="M16 3v4" />
                            <path d="M8 3v4" />
                            <path d="M4 11h16" />
                            <path d="M16 19h6" />
                            <path d="M19 16v6" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Add my Google Calendar') }}</h3>
                    <div id="add_my_google_calendar_status" class="text-muted"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a class="btn btn-primary w-100" id="service_booking_google_calendar_id" target="_blank">
                                    {{ __('Add') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom Scripts --}}
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            const total_columns = 12;

            const table = $('#serviceBookingTable').DataTable({
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
                    sSearchPlaceholder: `{{ __('Search bookings...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    // Custom SVG Empty State for Service Bookings
                    emptyTable: `
                        <div class="text-center py-4 w-100">
                            <div class="empty-state-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar-off">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 3l18 18" />
                                    <path d="M19.823 19.824a2 2 0 0 1 -1.823 1.176h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 1.175 -1.823" />
                                    <path d="M9 5h9a2 2 0 0 1 2 2v9" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v1" />
                                    <path d="M4 11h7m4 0h5" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Service Bookings Yet') }}</h4>
                            <p class="text-muted small mb-0">{{ __('Customer reservation requests and check-in schedules will appear here.') }}</p>
                        </div>
                    `,
                    zeroRecords: `
                        <div class="text-center py-4 w-100">
                            <div class="empty-state-icon" style="background-color: var(--tblr-danger-lt); color: var(--tblr-danger);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar-search">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4.5" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v4" />
                                    <path d="M4 11h16" />
                                    <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M20.2 20.2l1.8 1.8" />
                                </svg>
                            </div>
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching Bookings Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('We could not find any booking matching your search keyword.') }}</p>
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
                let csv = [
                    '\uFEFF"#","Service Booking ID","Name","Email","Phone","Address","Check-in","Check-out","Guests","Notes","Booking Status"'
                ];
                table.rows({
                    search: 'applied'
                }).every(function(rowIdx, tableLoop, rowLoop) {
                    let $node = $(this.node());
                    let col0 = rowLoop + 1;
                    let col1 = $node.find('td').eq(1).text().trim().replace(/"/g, '""');
                    let col2 = $node.find('td').eq(2).text().trim().replace(/"/g, '""');
                    let col3 = $node.find('td').eq(3).text().trim().replace(/"/g, '""');
                    let col4 = $node.find('td').eq(4).text().trim().replace(/"/g, '""');
                    let col5 = $node.find('td').eq(5).text().trim().replace(/"/g, '""');
                    let col6 = $node.find('td').eq(6).text().trim().replace(/"/g, '""');
                    let col7 = $node.find('td').eq(7).text().trim().replace(/"/g, '""');
                    let col8 = $node.find('td').eq(8).text().trim().replace(/"/g, '""');
                    let col9 = $node.find('td').eq(9).text().trim().replace(/"/g, '""');
                    let col10 = $node.find('td').eq(10).text().trim().replace(/"/g, '""');
                    csv.push(
                        `"${col0}","${col1}","${col2}","${col3}","${col4}","${col5}","${col6}","${col7}","${col8}","${col9}","${col10}"`
                        );
                });

                let blob = new Blob([csv.join('\n')], {
                    type: 'text/csv;charset=utf-8;'
                });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'service_booking_export.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Native Excel (.xls) Export Handler
            $('#btn-export-excel').on('click', function() {
                let tableHtml =
                    '<table border="1"><thead><tr><th>#</th><th>Service Booking ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Check-in</th><th>Check-out</th><th>Guests</th><th>Notes</th><th>Booking Status</th></tr></thead><tbody>';
                table.rows({
                    search: 'applied'
                }).every(function(rowIdx, tableLoop, rowLoop) {
                    let $node = $(this.node());
                    let col0 = rowLoop + 1;
                    let col1 = $node.find('td').eq(1).text().trim();
                    let col2 = $node.find('td').eq(2).text().trim();
                    let col3 = $node.find('td').eq(3).text().trim();
                    let col4 = $node.find('td').eq(4).text().trim();
                    let col5 = $node.find('td').eq(5).text().trim();
                    let col6 = $node.find('td').eq(6).text().trim();
                    let col7 = $node.find('td').eq(7).text().trim();
                    let col8 = $node.find('td').eq(8).text().trim();
                    let col9 = $node.find('td').eq(9).text().trim();
                    let col10 = $node.find('td').eq(10).text().trim();
                    tableHtml +=
                        `<tr><td>${col0}</td><td>${col1}</td><td>${col2}</td><td>${col3}</td><td>${col4}</td><td>${col5}</td><td>${col6}</td><td>${col7}</td><td>${col8}</td><td>${col9}</td><td>${col10}</td></tr>`;
                });
                tableHtml += '</tbody></table>';

                let blob = new Blob(['\uFEFF' + tableHtml], {
                    type: 'application/vnd.ms-excel;charset=utf-8'
                });
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'service_booking_export.xls';
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
        var serviceBookingSearchTimer;
        $(document).on('input', '#serviceBookingTable_filter input', function() {
            var value = this.value;
            var table = $('#serviceBookingTable').DataTable();
            clearTimeout(serviceBookingSearchTimer);
            serviceBookingSearchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });

        // Modal Action Trigger Handlers
        function UpdateServiceBooking(id) {
            "use strict";
            $("#acceptServiceBookingModal").modal("show");
            var accept_service_booking_status = document.getElementById("accept_service_booking_status");
            accept_service_booking_status.innerHTML = "{{ __('If you proceed, you will accept this service booking.') }}";

            var accept_service_booking_link = document.getElementById("accept_service_booking_id");
            accept_service_booking_link.setAttribute("href", "{{ route('user.service.booking.accept') }}?id=" + id);

            var cancel_service_booking_link = document.getElementById("cancel_service_booking_id");
            cancel_service_booking_link.setAttribute("href", "{{ route('user.service.booking.reject') }}?id=" + id);
        }

        function completeServiceBooking(id) {
            "use strict";
            $("#completeServiceBookingModal").modal("show");
            var complete_service_booking_status = document.getElementById("complete_service_booking_status");
            complete_service_booking_status.innerHTML =
                "{{ __('If you proceed, you will complete this service booking.') }}";

            var complete_service_booking_link = document.getElementById("complete_service_booking_id");
            complete_service_booking_link.setAttribute("href", "{{ route('user.service.booking.complete') }}?id=" + id);
        }

        function addMyGoogleCalendar(id) {
            "use strict";
            $("#addMyGoogleCalendarModal").modal("show");
            document.getElementById("add_my_google_calendar_status").innerHTML =
                "{{ __('If you proceed, you will add this service booking to your Google Calendar.') }}";
            document.getElementById("service_booking_google_calendar_id").setAttribute("href",
                "{{ route('user.service.booking.add.my.google.calendar') }}?id=" + id);
        }
    </script>
@endsection
