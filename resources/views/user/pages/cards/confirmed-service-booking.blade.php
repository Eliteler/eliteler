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

            #serviceBookingTable_info {
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
                                {{ __('Service Bookings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Service Bookings') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure appointments, schedule check-ins, record reservation requests, and export records data.') }}
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
                            <table class="table card-table table-vcenter text-nowrap" id="serviceBookingTable">
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
                                        <th class="w-1 text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($serviceBooking as $booking)
                                        <tr>
                                            <td class="text-start">{{ $loop->iteration }}</td>

                                            <td class="text-start text-uppercase fw-bold text-dark">
                                                {{ $booking->service_booking_id }}
                                            </td>

                                            <td class="text-capitalize fw-bold text-dark">
                                                @if (!empty($booking->name))
                                                    {{ $booking->name }}
                                                @else
                                                    —
                                                @endif
                                            </td>

                                            <td>
                                                @if (!empty($booking->email))
                                                    <a href="mailto:{{ trim($booking->email) }}"
                                                        class="text-decoration-none fw-bold">{{ $booking->email }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>

                                            <td>
                                                @if (!empty($booking->phone))
                                                    <a href="tel:{{ preg_replace('/\s+/', '', $booking->phone) }}"
                                                        class="text-decoration-none fw-bold">{{ $booking->phone }}</a>
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
                                                <span
                                                    class="fw-bold text-secondary">{{ formatDateForUser($booking->checkin) }}</span>
                                            </td>

                                            <td>
                                                <span
                                                    class="fw-bold text-secondary">{{ formatDateForUser($booking->checkout) }}</span>
                                            </td>

                                            <td class="text-center fw-bold">
                                                {{ $booking->number_of_guests }}
                                            </td>

                                            <td class="text-wrap" style="max-width: 150px;">
                                                @if (!empty($booking->notes))
                                                    {{ $booking->notes }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                @switch($booking->booking_status)
                                                    @case('pending')
                                                        <span
                                                            class="badge bg-yellow-100 text-yellow-800 px-2 py-1 text-uppercase">{{ __('Pending') }}</span>
                                                    @break

                                                    @case('confirmed')
                                                        <span
                                                            class="badge bg-green-600 text-green-50 px-2 py-1 text-uppercase">{{ __('Confirmed') }}</span>
                                                    @break

                                                    @case('rejected')
                                                        <span
                                                            class="badge bg-red-100 text-red-700 px-2 py-1 text-uppercase">{{ __('Canceled') }}</span>
                                                    @break

                                                    @case('completed')
                                                        <span
                                                            class="badge bg-green-600 text-green-50 px-2 py-1 text-uppercase">{{ __('Completed') }}</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge bg-yellow-100 text-yellow-800 px-2 py-1 text-uppercase">{{ __('Pending') }}</span>
                                                @endswitch
                                            </td>

                                            <td class="w-1">
                                                @if ($booking->booking_status != 'completed')
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
                                                                <a onclick="UpdateServiceBooking(`{{ $booking->service_booking_id }}`)"
                                                                    class="dropdown-item">
                                                                    {{ __('Update Status') }}
                                                                </a>

                                                                @if ($booking->booking_status != 'rejected')
                                                                    <a onclick="completeServiceBooking(`{{ $booking->service_booking_id }}`)"
                                                                        class="dropdown-item">
                                                                        {{ __('Complete') }}
                                                                    </a>

                                                                    <a onclick="addMyGoogleCalendar(`{{ $booking->service_booking_id }}`)"
                                                                        class="dropdown-item">
                                                                        {{ __('Add My Google Calendar') }}
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-center d-block">-</span>
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

    {{-- Accept or cancel service booking modal --}}
    <div class="modal modal-bg fade" id="acceptServiceBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
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
                    <div id="accept_service_booking_status" class="text-secondary"></div>
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
                                <a data-method="post" class="btn btn-danger w-100" id="cancel_service_booking_id">
                                    {{ __('Reject') }}
                                </a>
                            </div>
                            <div class="col">
                                <a data-method="post" class="btn btn-warning w-100" id="accept_service_booking_id">
                                    {{ __('Accept') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Complete service booking modal --}}
    <div class="modal modal-bg fade" id="completeServiceBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
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
                    <div id="complete_service_booking_status" class="text-secondary"></div>
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

    {{-- Add google calendar modal --}}
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
                    <h3>{{ __('Add my google calendar') }}</h3>
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
                                <a class="btn btn-info w-100" id="service_booking_google_calendar_id" target="_blank">
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
            const totalColumns = 12;

            const table = $('#serviceBookingTable').DataTable({
                responsive: false, // Turned off stacked responsive engine to use horizontal tables
                ordering: false,
                // Integrated 'f' selector parameter cleanly with responsive columns structure
                dom: "<'row mb-3 align-items-center g-3'<'col-12 col-md-6'l><'col-12 col-md-6 text-md-end'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'d-flex justify-content-between align-items-center mt-3'ip>",
                buttons: [{
                        extend: 'csv',
                        text: '{{ __('CSV') }}',
                        className: 'btn btn-sm btn-success force-class-csv',
                        filename: 'service_booking_export',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                            format: {
                                header: function(data, columnIdx) {
                                    switch (columnIdx) {
                                        case 0:
                                            return '#';
                                        case 1:
                                            return 'Service Booking ID';
                                        case 2:
                                            return 'Name';
                                        case 3:
                                            return 'Email';
                                        case 4:
                                            return 'Phone';
                                        case 5:
                                            return 'Address';
                                        case 6:
                                            return 'Check-in';
                                        case 7:
                                            return 'Check-out';
                                        case 8:
                                            return 'Number of Guests';
                                        case 9:
                                            return 'Notes';
                                        case 10:
                                            return 'Booking Status';
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
                        filename: 'contacts_export',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                            format: {
                                header: function(data, columnIdx) {
                                    switch (columnIdx) {
                                        case 0:
                                            return '#';
                                        case 1:
                                            return 'Service Booking ID';
                                        case 2:
                                            return 'Name';
                                        case 3:
                                            return 'Email';
                                        case 4:
                                            return 'Phone';
                                        case 5:
                                            return 'Address';
                                        case 6:
                                            return 'Check-in';
                                        case 7:
                                            return 'Check-out';
                                        case 8:
                                            return 'Number of Guests';
                                        case 9:
                                            return 'Notes';
                                        case 10:
                                            return 'Booking Status';
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
                    $('#serviceBookingTable_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#serviceBookingTable_wrapper').removeClass('placeholder-glow');

                    // Clear dynamic action skeleton loaders (Targeting 12th column: Index 11)
                    $('#serviceBookingTable tbody tr').each(function() {
                        const actionCell = $(this).find('td').eq(11);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Match empty display widths
                    $('#serviceBookingTable tbody tr td.dataTables_empty').attr('colspan',
                        totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#serviceBookingTable').wrap('<div class="table-responsive px-0 py-2"></div>');

            // Move button containers cleanly
            table.buttons().container().appendTo('#customButtonsContainer');

            // Implement static 350ms debounced filters execution with matching skeletons
            $('.dataTables_filter input').unbind().on('keyup', function() {
                const searchValue = this.value;
                clearTimeout(window.searchTimeout);

                $('#serviceBookingTable_wrapper').addClass('placeholder-glow');
                let placeholderRows = '';
                for (let i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#serviceBookingTable tbody').html(placeholderRows);

                window.searchTimeout = setTimeout(function() {
                    table.search(searchValue).draw();
                }, 350);
            });

            // Apply custom classes
            const applyButtonClasses = () => {
                document.querySelectorAll('.force-class-csv, .force-class-excel').forEach(btn => {
                    btn.classList.remove('dt-button');
                    btn.classList.add('btn', 'btn-primary', 'btn-sm', 'me-2');
                });
            };
            setTimeout(applyButtonClasses, 100);
        });

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
