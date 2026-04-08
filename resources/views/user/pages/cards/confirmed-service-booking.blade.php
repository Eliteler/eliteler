@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <link href="{{ asset('plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/datatable/css/buttons.bootstrap5.min.css') }}" rel="stylesheet">

    <style>
    .btn {
        border-radius: 12px !important;
        font-size: small !important;
        padding: 10px 18px !important;
    }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('Service Bookings') }}
                        </h2>
                    </div>
                    {{-- Custom buttons --}}
                    <div class="col-auto text-end">
                        <div id="customButtonsContainer"></div>
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
                    <div class="col-sm-12 col-lg-12">
                        <div class="card">
                            <div class="table-responsive p-2">
                                <table class="table table-vcenter card-table" id="serviceBookingTable">
                                    <thead>
                                        <tr>
                                            <th class="text-start">{{ __('#') }}</th>
                                            <th class="text-start">{{ __('Service Booking ID') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Mobile Number') }}</th>
                                            <th>{{ __('Address') }}</th>
                                            <th>{{ __('Check-in') }}</th>
                                            <th>{{ __('Check-out') }}</th>
                                            <th class="text-start">{{ __('Number of Guests') }}</th>
                                            <th>{{ __('Notes') }}</th>
                                            <th>{{ __('Booking Status') }}</th>
                                            <th class="w-1">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($serviceBooking as $booking)
                                            <tr>
                                                <td class="text-start">{{ $loop->iteration }}</td>

                                                <td class="text-start text-uppercase fw-bold">
                                                    {{ $booking->service_booking_id }}
                                                </td>

                                                <td class="text-capitalize fw-bold">
                                                    @if (!empty($booking->name))
                                                        {{ $booking->name }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <td>
                                                    @if (!empty($booking->email))
                                                        <a
                                                            href="mailto:{{ trim($booking->email) }}">{{ $booking->email }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <td>
                                                    @if (!empty($booking->phone))
                                                        <a
                                                            href="tel:{{ preg_replace('/\s+/', '', $booking->phone) }}">{{ $booking->phone }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                
                                                <td>
                                                    @if (!empty($booking->address))
                                                        {{ $booking->address }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>
                                                    {{ $booking->checkin }}
                                                </td>

                                                <td>
                                                    {{ $booking->checkout }}
                                                </td>

                                                <td class="text-center">
                                                    {{ $booking->number_of_guests }}
                                                </td>

                                                <td>
                                                    @if (!empty($booking->notes))
                                                        {{ $booking->notes }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>
                                                    @switch($booking->booking_status)
                                                        @case('pending')
                                                            <span class="badge bg-warning text-white text-uppercase">{{ __('Pending') }}</span>
                                                            @break
                                                        @case('confirmed')
                                                            <span class="badge bg-success text-white text-uppercase">{{ __('Confirmed') }}</span>
                                                            @break
                                                        @case('rejected')
                                                            <span class="badge bg-danger text-white text-uppercase">{{ __('Canceled') }}</span>
                                                            @break
                                                        @case('completed')
                                                            <span class="badge bg-success text-white text-uppercase">{{ __('Completed') }}</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-warning text-white text-uppercase">{{ __('Pending') }}</span>
                                                    @endswitch
                                                </td>

                                                <td class="w-1">
                                                    @if ($booking->booking_status != 'completed')
                                                        <a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                            <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                                <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                                <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                            </svg>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                                            <div class="nav-item dropdown">
                                                                {{-- Update the service booking --}}
                                                                <a onclick="UpdateServiceBooking(`{{ $booking->service_booking_id }}`)"
                                                                    class="dropdown-item">
                                                                    {{ __('Update Status') }}
                                                                </a>

                                                                @if ($booking->booking_status != 'rejected')
                                                                    {{-- Complete the service booking --}}
                                                                    <a onclick="completeServiceBooking(`{{ $booking->service_booking_id }}`)"
                                                                        class="dropdown-item">
                                                                        {{ __('Complete') }}
                                                                    </a>

                                                                    {{-- Add my google calendar --}}
                                                                    <a onclick="addMyGoogleCalendar(`{{ $booking->service_booking_id }}`)"
                                                                        class="dropdown-item">
                                                                        {{ __('Add My Google Calendar') }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-center">
                                                            -
                                                        </span>
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
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Accept or cancel service booking --}}
    <div class="modal modal-blur fade" id="acceptServiceBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
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
                    <div id="accept_service_booking_status"></div>
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
                                <a class="btn btn-danger w-100" id="cancel_service_booking_id">
                                    {{ __('Reject') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-success w-100" id="accept_service_booking_id">
                                    {{ __('Accept') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Complete service booking --}}
    <div class="modal modal-blur fade" id="completeServiceBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
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
                    <div id="complete_service_booking_status"></div>
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
                                <a class="btn btn-danger w-100" id="complete_service_booking_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add my google calendar --}}
    <div class="modal modal-blur fade" id="addMyGoogleCalendarModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-plus mb-2 text-danger icon-md">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" />
                        <path d="M16 3v4" />
                        <path d="M8 3v4" />
                        <path d="M4 11h16" />
                        <path d="M16 19h6" />
                        <path d="M19 16v6" />
                    </svg>
                    <h3>{{ __('Add my google calendar') }}</h3>
                    <div id="add_my_google_calendar_status"></div>
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
                                <a class="btn btn-danger w-100" id="service_booking_google_calendar_id" target="_blank">
                                    {{ __('Add') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script src="{{ asset('plugins/datatable/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatable/js/buttons.html5.min.js') }}"></script>

    <!-- JSZip for Excel export -->
    <script src="{{ asset('plugins/datatable/js/jszip.min.js') }}"></script>
    <script>
    $(document).ready(function () {
        const table = $('#serviceBookingTable').DataTable({
            responsive: true,
            ordering: false,
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'csv',
                    text: '{{ __("CSV") }}',
                    className: 'btn btn-sm btn-success force-class-excel',
                    filename: 'service_booking_export',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                            header: function (data, columnIdx) {
                                switch (columnIdx) {
                                    case 0: return '#';
                                    case 1: return 'Service Booking ID';
                                    case 2: return 'Name';
                                    case 3: return 'Email';
                                    case 4: return 'Phone';
                                    case 5: return 'Address';
                                    case 6: return 'Check-in';
                                    case 7: return 'Check-out';
                                    case 8: return 'Number of Guests';
                                    case 9: return 'Notes';
                                    case 10: return 'Booking Status';
                                    default: return data;
                                }
                            }
                        }
                    }
                },
                {
                    extend: 'excel',
                    text: '{{ __("Excel") }}',
                    className: 'btn btn-sm btn-success force-class-excel',
                    filename: 'contacts_export',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                            header: function (data, columnIdx) {
                                switch (columnIdx) {
                                    case 0: return '#';
                                    case 1: return 'Service Booking ID';
                                    case 2: return 'Name';
                                    case 3: return 'Email';
                                    case 4: return 'Phone';
                                    case 5: return 'Address';
                                    case 6: return 'Check-in';
                                    case 7: return 'Check-out';
                                    case 8: return 'Number of Guests';
                                    case 9: return 'Notes';
                                    case 10: return 'Booking Status';
                                    default: return data;
                                }
                            }
                        }
                    }
                }
            ],
            language: {
                "sProcessing": `{{ __('Processing...') }}`,
                "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                "sSearch": `{{ __('Search:') }}`,
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
                emptyTable: `{{ __('No data available in the table') }}` // Message for an empty table
            }
        });

        // Move button container if needed
        table.buttons().container().appendTo('#customButtonsContainer');

        // Force-apply Bootstrap/Tabler classes manually
        const applyButtonClasses = () => {
            document.querySelectorAll('.force-class-csv, .force-class-excel').forEach(btn => {
                btn.classList.remove('dt-button'); // Optional: remove default DataTables class
                btn.classList.add('btn', 'btn-primary', 'btn-sm', 'me-2'); // Add Tabler classes
            });
        };

        // Wait a bit to let DataTables render then apply classes
        setTimeout(applyButtonClasses, 100);
    });
    </script>
    <script>
        // Update service booking
        function UpdateServiceBooking(id) {
            "use strict";

            $("#acceptServiceBookingModal").modal("show");
            var accept_service_booking_status = document.getElementById("accept_service_booking_status");
            accept_service_booking_status.innerHTML = "<?php echo __('If you proceed, you will accept this service booking.'); ?>"
            var accept_service_booking_link = document.getElementById("accept_service_booking_id");
            accept_service_booking_link.getAttribute("href");
            accept_service_booking_link.setAttribute("href", "{{ route('user.service.booking.accept') }}?id=" + id);
            var cancel_service_booking_link = document.getElementById("cancel_service_booking_id");
            cancel_service_booking_link.getAttribute("href");
            cancel_service_booking_link.setAttribute("href", "{{ route('user.service.booking.reject') }}?id=" + id);
        }

        // Complete service booking
        function completeServiceBooking(id) {            
            "use strict";

            $("#completeServiceBookingModal").modal("show");
            var complete_service_booking_status = document.getElementById("complete_service_booking_status");
            complete_service_booking_status.innerHTML = "<?php echo __('If you proceed, you will complete this service booking.'); ?>"
            var complete_service_booking_link = document.getElementById("complete_service_booking_id");
            complete_service_booking_link.getAttribute("href");
            complete_service_booking_link.setAttribute("href", "{{ route('user.service.booking.complete') }}?id=" + id);
        }

        // Google Calendar
        function addMyGoogleCalendar(id) {
            "use strict";

            // Show the modal
            $("#addMyGoogleCalendarModal").modal("show");

            // Update status text
            document.getElementById("add_my_google_calendar_status").innerHTML = 
                "{{ __('If you proceed, you will add this service booking to your Google Calendar.') }}";

            // Update the link with the passed booking ID
            document.getElementById("service_booking_google_calendar_id")
                .setAttribute("href", "{{ route('user.service.booking.add.my.google.calendar') }}?id=" + id);
        }
    </script>
@endsection
@endsection
