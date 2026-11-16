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
            .appointment-notes-col {
                max-width: 200px;
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
                                    {{ __('Appointments') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Page Title & Help Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Appointments') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Review active client bookings, confirm scheduled slots, and manage system reservation statuses.') }}
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
                            id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th class="w-1">{{ __('#') }}</th>
                                    <th>{{ __('Created') }}</th>
                                    <th>{{ __('Customer Name') }}</th>
                                    <th>{{ __('Customer Email') }}</th>
                                    <th>{{ __('Customer Phone') }}</th>
                                    <th>{{ __('Appointment Date') }}</th>
                                    <th>{{ __('Appointment Time') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="w-1 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookedAppointments as $appointment)
                                    <tr>
                                        <td class="rc-hide">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="text-secondary fw-semibold">
                                                {{ formatDateForUser($card->created_at ?? $appointment->created_at) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-reset">{{ $appointment->name }}</span>
                                        </td>
                                        <td>
                                            @if (!empty($appointment->email))
                                                <a href="mailto:{{ trim($appointment->email) }}"
                                                    class="text-decoration-none fw-semibold">{{ $appointment->email }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($appointment->phone))
                                                <a href="tel:{{ preg_replace('/\s+/', '', $appointment->phone) }}"
                                                    class="text-decoration-none fw-semibold">{{ $appointment->phone }}</a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="text-secondary fw-semibold">{{ formatDateOnlyForUser($appointment->booking_date) }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="text-secondary fw-semibold">{{ $appointment->booking_time }}</span>
                                        </td>
                                        <td class="appointment-notes-col">
                                            <span class="text-secondary small">{{ $appointment->notes ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if ($appointment->booking_status == 0)
                                                <span class="badge bg-warning-lt px-2 py-1">{{ __('Pending') }}</span>
                                            @elseif ($appointment->booking_status == 1)
                                                <span class="badge bg-info-lt px-2 py-1">{{ __('Confirmed') }}</span>
                                            @elseif ($appointment->booking_status == 2)
                                                <span class="badge bg-success-lt px-2 py-1">{{ __('Completed') }}</span>
                                            @elseif ($appointment->booking_status == -1)
                                                <span class="badge bg-danger-lt px-2 py-1">{{ __('Canceled') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($appointment->booking_status != 2)
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
                                                        <a onclick="acceptAppointment(`{{ $appointment->booked_appointment_id }}`)"
                                                            class="dropdown-item cursor-pointer">
                                                            {{ __('Update status') }}
                                                        </a>
                                                        <a onclick="rescheduleAppointment(`{{ $appointment->booked_appointment_id }}`, `{{ $appointment->booking_date }}`)"
                                                            class="dropdown-item cursor-pointer">
                                                            {{ __('Reschedule') }}
                                                        </a>
                                                        <a onclick="completeAppointment(`{{ $appointment->booked_appointment_id }}`)"
                                                            class="dropdown-item cursor-pointer">
                                                            {{ __('Complete') }}
                                                        </a>
                                                        <a onclick="addMyGoogleCalendar(`{{ $appointment->booked_appointment_id }}`)"
                                                            class="dropdown-item cursor-pointer">
                                                            {{ __('Add My Google Calendar') }}
                                                        </a>
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

    {{-- Change status modal --}}
    <div class="modal fade" id="acceptAppointmentModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <div id="accept_appointment_status" class="text-muted"></div>
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
                                <a data-method="post" class="btn btn-danger w-100" id="cancel_appointment_id">
                                    {{ __('Reject') }}
                                </a>
                            </div>
                            <div class="col-4">
                                <a data-method="post" class="btn btn-primary w-100" id="plan_id">
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
    <div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <div id="complete_appointment_status" class="text-muted"></div>
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
                                <a data-method="post" class="btn btn-success w-100" id="complete_appointment_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reschedule modal --}}
    <div class="modal fade" id="rescheduleAppointmentModal" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('user.reschedule.appointment') }}" method="post">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark">{{ __('Reschedule appointment') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <input type="hidden" name="booked_appointment_id" id="booked_appointment_id">

                        <div class="mb-3">
                            <label class="form-label required" for="date">{{ __('Date') }}</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>

                        <div class="mb-0">
                            <label class="form-label required" for="time">{{ __('Time') }}</label>
                            <input type="time" name="time" id="time" class="form-control" required>
                        </div>
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
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Reschedule') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Google Calendar Modal --}}
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
                                <a class="btn btn-info w-100" id="add_my_google_calendar_id" target="_blank">
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

            const total_columns = 10;

            const table = $('#appointmentsTable').DataTable({
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
                    sSearchPlaceholder: `{{ __('Search appointments...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,

                    // SVG Empty State for Appointments
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
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Appointments Booked Yet') }}</h4>
                            <p class="text-muted small mb-0">{{ __('Customer appointment schedules and reservation requests will appear here.') }}</p>
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
                            <h4 class="fw-bold mb-1 text-reset">{{ __('No Matching Appointments Found') }}</h4>
                            <p class="text-muted small mb-0">{{ __('We could not find any appointment matching your search keyword.') }}</p>
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
        var appointmentSearchTimer;
        $(document).on('input', '#appointmentsTable_filter input', function() {
            var value = this.value;
            var table = $('#appointmentsTable').DataTable();
            clearTimeout(appointmentSearchTimer);
            appointmentSearchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });

        // Modal Action Trigger Handlers
        function acceptAppointment(id) {
            "use strict";
            $("#acceptAppointmentModal").modal("show");
            var accept_appointment_status = document.getElementById("accept_appointment_status");
            accept_appointment_status.innerHTML = "{{ __('If you proceed, you will accept this appointment.') }}";

            var accept_appointment_link = document.getElementById("plan_id");
            accept_appointment_link.setAttribute("href", "{{ route('user.accept.appointment') }}?id=" + id);

            var cancel_appointment_link = document.getElementById("cancel_appointment_id");
            cancel_appointment_link.setAttribute("href", "{{ route('user.cancel.appointment') }}?id=" + id);
        }

        function completeAppointment(id) {
            "use strict";
            $("#delete-modal").modal("show");
            var complete_appointment_status = document.getElementById("complete_appointment_status");
            complete_appointment_status.innerHTML = "{{ __('If you proceed, you will complete this appointment.') }}";

            var complete_appointment_link = document.getElementById("complete_appointment_id");
            complete_appointment_link.setAttribute("href", "{{ route('user.complete.appointment') }}?id=" + id);
        }

        function rescheduleAppointment(booked_appointment_id, date) {
            "use strict";
            const dateObj = new Date(date);
            const formattedDate = formatDateToYMD(dateObj);

            $("#rescheduleAppointmentModal").modal("show");

            var bookedAppointmentInput = document.getElementById("booked_appointment_id");
            var dateInput = document.getElementById("date");

            bookedAppointmentInput.value = booked_appointment_id;
            dateInput.value = formattedDate;
        }

        function formatDateToYMD(date) {
            const y = date.getFullYear();
            const m = date.getMonth() + 1;
            const d = date.getDate();

            const day = d < 10 ? '0' + d : d;
            const month = m < 10 ? '0' + m : m;

            return `${y}-${month}-${day}`;
        }

        function addMyGoogleCalendar(booked_appointment_id) {
            "use strict";
            $("#addMyGoogleCalendarModal").modal("show");
            var add_my_google_calendar_status = document.getElementById("add_my_google_calendar_status");
            add_my_google_calendar_status.innerHTML =
                "{{ __('If you proceed, you will add this appointment to your Google Calendar.') }}";

            var add_my_google_calendar_link = document.getElementById("add_my_google_calendar_id");
            add_my_google_calendar_link.setAttribute("href", "{{ route('user.add.my.google.calendar') }}?id=" +
                booked_appointment_id);
        }
    </script>
@endsection
