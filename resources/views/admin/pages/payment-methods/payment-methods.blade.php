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
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Payment Gateways') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Payment Gateways') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Manage and configure payment gateways for customer transactions.') }}
                        </div>
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
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="payment-methods-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Logo') }}</th>
                                            <th>{{ __('Payment Method') }}</th>
                                            <th>{{ __('Included') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
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

    {{-- Confirmation Modal --}}
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
                    <div id="modal-message" class="text-secondary"></div>
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
                                <a class="btn btn-danger w-100" id="payment_gateway_id">
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

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var table = $('#payment-methods-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.payment.methods') }}",
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
                        searchable: false,
                        className: 'rc-hide'
                    },
                    {
                        data: 'payment_gateway_logo',
                        name: 'payment_gateway_logo',
                        orderable: false,
                        searchable: false,
                        className: 'rc-info-start'
                    },
                    {
                        data: 'payment_gateway_name',
                        name: 'payment_gateway_name',
                        className: 'rc-heading'
                    },
                    {
                        data: 'is_status',
                        name: 'is_status',
                        className: 'rc-info-next'
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
                    }
                ],
                preDrawCallback: function(settings) {
                    $('#payment-methods-table_wrapper').addClass('placeholder-glow');

                    // Desktop Placeholder Rows (Adjusted to match 6 columns)
                    if ($('#payment-methods-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 10; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(6) + '</tr>';
                        }
                        $('#payment-methods-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    // Remove loading placeholder glow indicators
                    $('#payment-methods-table_wrapper').removeClass('placeholder-glow');

                    // Clear desktop dynamic action placeholders
                    $('#payment-methods-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(
                            5); // Targeting the 6th column (index 5)
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 6 columns correctly
                    $('#payment-methods-table tbody tr td.dataTables_empty').attr('colspan', 6);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#payment-methods-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#payment-methods-table tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index]);
                        });
                    });

                    // Update Custom Mobile Pagination Footer State dynamically
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
                    $('#payment-methods-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 10; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(6) + '</tr>';
                    }
                    $('#payment-methods-table tbody').html(placeholderRows);
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

        function getPaymentMethod(paymentGatewayId, status) {
            "use strict";

            $("#delete-modal").modal("show");
            var link = document.getElementById("payment_gateway_id");

            // Modal message
            var modalMessage = document.getElementById("modal-message");
            let messageStatus = status;
            let message =
                `{{ __('If you proceed, this payment method will be :status.', ['status' => '${messageStatus}']) }}`;
            modalMessage.innerHTML = message.replace(':status', status);

            // Modal link
            link.getAttribute("href");
            link.setAttribute("href", "/admin/delete-payment-method?id=" + paymentGatewayId + '&status=' + status);
        }
    </script>
@endsection
