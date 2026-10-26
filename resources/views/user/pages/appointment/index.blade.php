@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Responsive pagination alignment and search layout rules) --}}
@section('css')
    <style>
        .btn {
            border-radius: 12px !important;
            font-size: small !important;
            padding: 10px 18px !important;
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

            #appointmentsTable_info {
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
                                <a href="{{ route('user.dashboard') }}" class="text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.cards') }}" class="text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Appointments') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Appointments') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Review active client bookings, confirm scheduled slots, and manage system reservation statuses.') }}
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
                            <table class="table card-table table-vcenter text-nowrap" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
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
                                    {{-- Static Blade rendering iteration --}}
                                    @foreach ($bookedAppointments as $appointment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><span
                                                    class="fw-bold text-secondary">{{ formatDateForUser($card->created_at ?? $appointment->created_at) }}</span>
                                            </td>
                                            <td><span class="fw-bold text-dark">{{ $appointment->name }}</span></td>
                                            <td>
                                                @if (!empty($appointment->email))
                                                    <a href="mailto:{{ trim($appointment->email) }}"
                                                        class="text-decoration-none fw-bold">{{ $appointment->email }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($appointment->phone))
                                                    <a href="tel:{{ preg_replace('/\s+/', '', $appointment->phone) }}"
                                                        class="text-decoration-none fw-bold">{{ $appointment->phone }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span
                                                    class="fw-bold text-secondary">{{ $appointment->booking_date }}</span>
                                            </td>
                                            <td><span
                                                    class="fw-bold text-secondary">{{ $appointment->booking_time }}</span>
                                            </td>
                                            <td class="text-wrap" style="max-width: 200px;">
                                                {{ $appointment->notes ?? '-' }}</td>
                                            <td>
                                                <!-- Standard Soft-Muted Badges mapping -->
                                                @if ($appointment->booking_status == 0)
                                                    <span
                                                        class="badge bg-yellow-100 text-yellow-800 px-2 py-1">{{ __('Pending') }}</span>
                                                @elseif ($appointment->booking_status == 1)
                                                    <span
                                                        class="badge bg-blue-100 text-blue-800 px-2 py-1">{{ __('Confirmed') }}</span>
                                                @elseif ($appointment->booking_status == 2)
                                                    <span
                                                        class="badge bg-green-600 text-green-50 px-2 py-1">{{ __('Completed') }}</span>
                                                @elseif ($appointment->booking_status == -1)
                                                    <span
                                                        class="badge bg-red-100 text-red-700 px-2 py-1">{{ __('Canceled') }}</span>
                                                @endif
                                            </td>
                                            <td class="w-1">
                                                @if ($appointment->booking_status != 2)
                                                    <div class="btn-list flex-nowrap justify-content-end">
                                                        <a class="btn-action text-decoration-none" href="#"
                                                            data-toggle="dropdown" data-bs-toggle="dropdown"
                                                            data-bs-auto-close="outside" aria-expanded="false">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="icon icon-tabler icon-tabler-dots">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <circle cx="5" cy="12" r="1" />
                                                                <circle cx="12" cy="12" r="1" />
                                                                <circle cx="19" cy="12" r="1" />
                                                            </svg>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <div class="nav-item dropdown">
                                                                <a onclick="acceptAppointment(`{{ $appointment->booked_appointment_id }}`)"
                                                                    class="dropdown-item">
                                                                    {{ __('Update status') }}
                                                                </a>
                                                                <a onclick="rescheduleAppointment(`{{ $appointment->booked_appointment_id }}`, `{{ $appointment->booking_date }}`)"
                                                                    class="dropdown-item">
                                                                    {{ __('Reschedule') }}
                                                                </a>
                                                                <a onclick="completeAppointment(`{{ $appointment->booked_appointment_id }}`)"
                                                                    class="dropdown-item">
                                                                    {{ __('Complete') }}
                                                                </a>
                                                                <a onclick="addMyGoogleCalendar(`{{ $appointment->booked_appointment_id }}`)"
                                                                    class="dropdown-item">
                                                                    {{ __('Add My Google Calendar') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    -
                                                @endif
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

    {{-- Change status modal --}}
    <div class="modal modal-bg fade" id="acceptAppointmentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-warning icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('Are you sure?') }}</h3>
                    <div id="accept_appointment_status" class="text-secondary"></div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn w-100"
                                    data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-danger w-100 m-0" id="cancel_appointment_id">
                                    {{ __('Reject') }}
                                </a>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-warning w-100 m-0" id="plan_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Complete Confirmation Modal --}}
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-success icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('Confirm Status Update') }}</h3>
                    <div id="complete_appointment_status" class="text-secondary"></div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn w-100"
                                    data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-success w-100 m-0" id="complete_appointment_id">
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
    <div class="modal modal-bg fade" id="rescheduleAppointmentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
                <form action="{{ route('user.reschedule.appointment') }}" method="post">
                    @csrf
                    <div class="modal-header border-0">
                        <h3 class="modal-title fw-bold text-dark">{{ __('Reschedule appointment') }}</h3>
                    </div>
                    <div class="modal-body py-1">
                        <div class="mb-3">
                            <input type="hidden" name="booked_appointment_id" id="booked_appointment_id">

                            <label class="form-label required" for="date">{{ __('Date') }}</label>
                            <div class="input-group mb-3">
                                <input type="date" name="date" id="date" class="form-control" required>
                            </div>

                            <label class="form-label required" for="time">{{ __('Time') }}</label>
                            <div class="input-group">
                                <input type="time" name="time" id="time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <div class="w-100">
                            <div class="row g-2">
                                <div class="col">
                                    <button type="button" class="btn w-100"
                                        data-bs-dismiss="modal">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="submit"
                                        class="btn btn-primary w-100 m-0">{{ __('Reschedule') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Google Calendar Modal --}}
    <div class="modal modal-bg fade" id="addMyGoogleCalendarModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon icon-tabler icon-tabler-calendar-plus mb-2 text-info icon-lg">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" />
                        <path d="M16 3v4" />
                        <path d="M8 3v4" />
                        <path d="M4 11h16" />
                        <path d="M16 19h6" />
                        <path d="M19 16v6" />
                    </svg>
                    <h3>{{ __('Add my Google Calendar') }}</h3>
                    <div id="add_my_google_calendar_status" class="text-secondary"></div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn w-100"
                                    data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a class="btn btn-info w-100 m-0" id="add_my_google_calendar_id" target="_blank">
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

@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 10;

            const table = $('#appointmentsTable').DataTable({
                processing: false,
                serverSide: false, // Standard static client-side mode
                dom: "<'row mb-3 align-items-center g-3'<'col-12 col-md-6'l><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'d-flex justify-content-between align-items-center mt-3'ip>",
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
                    $('#appointmentsTable_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#appointmentsTable_wrapper').removeClass('placeholder-glow');

                    // Standard horizontal width empty colspan alignment
                    $('#appointmentsTable tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#appointmentsTable').wrap('<div class="table-responsive px-0 py-2"></div>');

            // Implement static 350ms debounced filters execution with matching skeletons
            $('.dataTables_filter input').unbind().on('keyup', function() {
                const searchValue = this.value;
                clearTimeout(window.searchTimeout);

                $('#appointmentsTable_wrapper').addClass('placeholder-glow');
                let placeholderRows = '';
                for (let i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#appointmentsTable tbody').html(placeholderRows);

                window.searchTimeout = setTimeout(function() {
                    table.search(searchValue).draw();
                }, 350);
            });
        });

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
