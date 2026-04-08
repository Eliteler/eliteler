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
                            {{ __('Contact List') }}
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
                                <table class="table table-vcenter card-table" id="contactsTable">
                                    <thead>
                                        <tr>
                                            <th class="text-start">{{ __('#') }}</th>
                                            <th>{{ __('Source') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Mobile Number') }}</th>
                                            <th class="w-1">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($contacts as $contact)
                                            <tr>
                                                <td class="text-start">{{ $loop->iteration }}</td>

                                                <td>
                                                    <span
                                                        class="badge bg-primary text-white text-uppercase">{{ $contact->source }}</span>
                                                </td>

                                                <td class="text-capitalize fw-bold">
                                                    @if (!empty($contact->name))
                                                        {{ $contact->name }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <td>
                                                    @if (!empty($contact->email))
                                                        <a
                                                            href="mailto:{{ trim($contact->email) }}">{{ $contact->email }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <td>
                                                    @if (!empty($contact->phone))
                                                        <a
                                                            href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}">{{ $contact->phone }}</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-1 text-nowrap">
                                                        @if (!empty($contact->phone))
                                                            <a class="btn btn-1"
                                                                href="tel:{{ preg_replace('/\s+/', '', $contact->phone) }}"
                                                                title="Call {{ $contact->name ?? 'contact' }}">{{ __('Call') }}</a>
                                                        @endif

                                                        @if (!empty($contact->email))
                                                            <a class="btn btn-1" href="mailto:{{ trim($contact->email) }}"
                                                                title="Reply to {{ $contact->email }}">{{ __('Reply') }}</a>
                                                        @endif
                                                    </div>
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
        @include('user.includes.footer')
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
        const table = $('#contactsTable').DataTable({
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
                    filename: 'contacts_export',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            header: function (data, columnIdx) {
                                switch (columnIdx) {
                                    case 0: return '#';
                                    case 1: return 'Source';
                                    case 2: return 'Full Name';
                                    case 3: return 'Email';
                                    case 4: return 'Phone';
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
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            header: function (data, columnIdx) {
                                switch (columnIdx) {
                                    case 0: return '#';
                                    case 1: return 'Source';
                                    case 2: return 'Full Name';
                                    case 3: return 'Email';
                                    case 4: return 'Phone';
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
@endsection
@endsection
