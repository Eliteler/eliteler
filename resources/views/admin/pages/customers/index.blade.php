@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.customers', ['status' => 'all']) }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Customers') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Manage Customers') }}</a>
                            </li>
                        </ol>
                        </div>
                    <div class="col-7">
                        {{-- Filters context setup --}}
                            @php
                                $currentRoute = Route::currentRouteName();
                                $status = request()->segment(3) ?? 'all';
                            @endphp 

                        <h2 class="page-title text-capitalize">
                            @php
                                switch ($status) {
                                    case 'all':
                                        echo trans('All Customers');
                                        break;
                                    case 'verified':
                                        echo trans('Verified Customers');
                                        break;
                                    case 'unverified':
                                        echo trans('Unverified Customers');
                                        break;
                                    case 'banned':
                                        echo trans('Suspended Customers');
                                        break;
                                    case 'deleted':
                                        echo trans('Deleted Customers');
                                        break;
                                    default:
                                        echo trans('All Customers');
                                        break;
                                }
                            @endphp
                        </h2>
                        <!-- Added helper text under heading -->
                        <div class="text-muted mt-1">
                                            @if ($status == 'all')
                                {{ __('View all customers and filter them by account status.') }}
                                            @elseif ($status == 'active')
                                {{ __('Customers who are active and can access the system.') }}
                                            @elseif ($status == 'inactive')
                                {{ __('Customers who are inactive and cannot access the system.') }}
                                            @elseif ($status == 'verified')
                                {{ __('View active customers who have verified their accounts.') }}
                                            @elseif ($status == 'unverified')
                                {{ __('View customers who have not yet verified their accounts.') }}
                                            @elseif ($status == 'banned')
                                {{ __('View customers who are suspended or restricted from accessing the system.') }}
                                            @elseif ($status == 'deleted')
                                {{ __('View customer accounts that have been deleted by customers.') }}
                                            @endif
                                        </div>
                                    </div>

                    <div class="col-5 col-md-auto ms-md-auto mt-2 mt-md-0">
                        <div class="d-flex flex-row align-items-center justify-content-end gap-2">
                                        <div class="dropdown">
                                <button class="btn dropdown-toggle" data-bs-toggle="dropdown">
                                    <span class="text-capitalize">
                                        @php
                                            switch ($status) {
                                                case 'all':
                                                    echo trans('All');
                                                    break;
                                                case 'verified':
                                                    echo trans('Verified');
                                                    break;
                                                case 'unverified':
                                                    echo trans('Unverified');
                                                    break;
                                                case 'banned':
                                                    echo trans('Suspended');
                                                    break;
                                                case 'deleted':
                                                    echo trans('Deleted');
                                                    break;
                                                default:
                                                    echo trans('All');
                                                    break;
                                            }
                                        @endphp
                                    </span>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item {{ $status == 'all' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'all']) }}">
                                        {{ __('All') }}
                                                </a>
                                                <a class="dropdown-item {{ $status == 'active' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'active']) }}">
                                                    {{ __('Active') }}
                                                </a>
                                                <a class="dropdown-item {{ $status == 'inactive' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'inactive']) }}">
                                                    {{ __('Inactive') }}
                                                </a>
                                                <a class="dropdown-item {{ $status == 'verified' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'verified']) }}">
                                                    {{ __('Verified') }}
                                                </a>
                                                <a class="dropdown-item {{ $status == 'unverified' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'unverified']) }}">
                                                    {{ __('Unverified') }}
                                                </a>
                                                <a class="dropdown-item {{ $status == 'banned' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'banned']) }}">
                                        {{ __('Suspended') }}
                                                </a>
                                                <a class="dropdown-item {{ $status == 'deleted' ? 'active' : '' }}"
                                                    href="{{ route('admin.customers', ['status' => 'deleted']) }}">
                                                    {{ __('Deleted') }}
                                                </a>
                                            </div>
                                        </div>

                            {{-- Create customer --}}
                            <a href="{{ route('admin.create.customer') }}"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                    width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
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
                            </div>

        <div class="page-body">
            <div class="container-fluid">

                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-sm-12 col-lg-12">
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="customers-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Full Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Current Plan') }}</th>
                                            <th>{{ __('Joined') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="custom-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button id="mobile-prev-btn" class="btn btn-primary btn-sm" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>

                                <span id="mobile-page-info" class="text-secondary small font-weight-medium"></span>

                                <button id="mobile-next-btn" class="btn btn-primary btn-sm" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Status modal --}}
    <div class="modal modal-bg fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
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
                    <div id="action_status" class="text-secondary">
                        {{ __('If you proceed, you will activate or deactivate this customer\'s account.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="user_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete modal --}}
    <div class="modal modal-bg fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
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
                    <div id="action_status" class="text-secondary">
                        {{ __('If you proceed, you will permanently delete this customer\'s account.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="deleted_user_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer verified modal --}}
    <div class="modal modal-bg fade" id="customer-verified-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
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
                    <div id="modal-message" class="text-secondary"></div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="customer_verified_user_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Undelete modal --}}
    <div class="modal modal-bg fade" id="undelete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
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
                    <div id="action_status" class="text-secondary">
                        {{ __('If you proceed, you will restore this customer\'s account.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <a href="#" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                            <div class="col">
                                <a class="btn btn-danger w-100" id="undeleted_user_id">
                                    {{ __('Yes, proceed') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS URL helper --}}
@php
        $ajaxUrl = route('admin.customers', [
            'status' => request('status', 'all'),
        ]);
@endphp

@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var table = $('#customers-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ $ajaxUrl }}",
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
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'rc-hide'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'rc-heading'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        className: 'rc-info-next'
                    },
                    {
                        data: 'plan',
                        name: 'plan',
                        className: 'rc-info-start'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'rc-hide'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'rc-badge-top-right'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                preDrawCallback: function(settings) {
                    $('#customers-table_wrapper').addClass('placeholder-glow');

                    // Desktop Placeholder Rows (Skeleton Loader)
                    if ($('#customers-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 10; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(7) + '</tr>';
                        }
                        $('#customers-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    $('#customers-table_wrapper').removeClass('placeholder-glow');

                    // Clear desktop dynamic action placeholders
                    $('#customers-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(6);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 7 columns correctly on desktop viewports
                    $('#customers-table tbody tr td.dataTables_empty').attr('colspan', 7);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#customers-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#customers-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });

                    // Update the Custom Mobile Pagination Footer State dynamically
                    const api = this.api();
                    const pageInfo = api.page.info();
                    const rowsCount = api.rows({
                        page: 'current'
                    }).data().length;

                    // Hide/show dynamic mobile footer based on pages total and active record count
                    if (pageInfo.pages <= 1 || rowsCount === 0) {
                        $('#custom-mobile-pagination').addClass('d-none');
                        $('#custom-mobile-pagination').removeClass('d-flex');
                    } else {
                        $('#custom-mobile-pagination').removeClass('d-none');
                        $('#custom-mobile-pagination').addClass('d-flex');
                }

                    const pageInfoText = `{{ __('Page :current of :total') }}`
                        .replace(':current', pageInfo.page + 1)
                        .replace(':total', pageInfo.pages || 1);
                    $('#mobile-page-info').text(pageInfoText);

                    // Toggle disabled state on Previous button
                    if (pageInfo.page === 0) {
                        $('#mobile-prev-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-prev-btn').removeClass('disabled').prop('disabled', false);
                    }

                    // Toggle disabled state on Next button
                    if (pageInfo.page >= pageInfo.pages - 1) {
                        $('#mobile-next-btn').addClass('disabled').prop('disabled', true);
                    } else {
                        $('#mobile-next-btn').removeClass('disabled').prop('disabled', false);
                    }
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing (e.g. searching)
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#customers-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 10; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(7) + '</tr>';
                    }
                    $('#customers-table tbody').html(placeholderRows);
                }
            });

            // Bind click events on Mobile pagination triggers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                table.page('previous').draw('page');
            });

            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                table.page('next').draw('page');
            });
        });

        function activateUser(userId) {
            "use strict";
            $("#status-modal").modal("show");
            var link = document.getElementById("user_id");
            link.getAttribute("href");
            link.setAttribute("href", "/admin/update-status?id=" + userId);
        }

        function deactivateUser(userId) {
            "use strict";
            $("#status-modal").modal("show");
            var link = document.getElementById("user_id");
            link.getAttribute("href");
            link.setAttribute("href", "/admin/update-status?id=" + userId);
        }

        function deleteUser(userId) {
            "use strict";
            $("#delete-modal").modal("show");
            var link = document.getElementById("deleted_user_id");
            link.getAttribute("href");
            link.setAttribute("href", "/admin/delete-customer?id=" + userId);
        }

        // Customer verified status handler
        function customerVerified(userId, status) {
            "use strict";

            $("#customer-verified-modal").modal("show");

            var modalMessage = document.getElementById("modal-message");
            let messageStatus = 'verified';
            if (status == 0) {
                messageStatus = '{{ __('make account unverified') }}';
            } else if (status == 2) {
                messageStatus = '{{ __('suspend') }}';
            } else if (status == 1) {
                messageStatus = '{{ __('make account verified') }}';
            }

            let message =
                `{{ __('If you proceed, this customer will be marked as :status.', ['status' => '${messageStatus}']) }}`;
            modalMessage.innerHTML = message.replace(':status', messageStatus);

            var link = document.getElementById("customer_verified_user_id");
            link.getAttribute("href");
            link.setAttribute("href", "/admin/customer-verified?id=" + userId + "&status=" + status);
        }

        // Undelete / Restore user
        function undeleteUser(userId) {
            "use strict";
            $("#undelete-modal").modal("show");
            var link = document.getElementById("undeleted_user_id");
            link.getAttribute("href");
            link.setAttribute("href", "/admin/undelete-customer?id=" + userId);
        }
    </script>
@endsection
