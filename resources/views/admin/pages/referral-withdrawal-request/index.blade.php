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
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.referrals') }}" class="text-secondary text-decoration-none">
                                    {{ __('Referral Program') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Withdrawal Requests') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Withdrawal Requests') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Review and manage customer withdrawal requests from referral earnings.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed alert --}}
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

                {{-- Success alert --}}
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
                                    id="requests-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Customer') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Transfer To') }}</th>
                                            <th>{{ __('Payment Status') }}</th>
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

        {{-- Withdrawal Request Modal --}}
        <div class="modal modal-bg fade" id="withdrawal-request-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                        <div id="withdrawal-request-status" class="text-secondary"></div>
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
                                    <a class="btn btn-danger w-100" id="withdrawal-request-id">
                                        {{ __('Yes, proceed') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transfer modal --}}
        <div class="modal modal-bg fade" id="transfer-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Transfer Details') }}</h5>
                    </div>
                    <form action="{{ route('admin.transfer.withdrawal.request') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12 col-lg-12">
                                    {{-- Request ID --}}
                                    <input type="hidden" name="request_id" id="request_id" value>

                                    {{-- Transaction ID --}}
                                    <div class="form-group mb-3">
                                        <label class="form-label required">{{ __('Transaction ID') }}</label>
                                        <input type="text" class="form-control" id="transfer-transaction-id"
                                            name="transfer_transaction_id"
                                            placeholder="{{ __('Enter the payment transaction ID') }}" required>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="form-group">
                                        <label class="form-label required">{{ __('Notes') }}</label>
                                        <textarea class="form-control" id="transfer-notes" name="transfer_notes" rows="3"
                                            placeholder="{{ __('Enter the notes') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-danger">
                                {{ __('Transfer') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom scripts --}}
@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var table = $('#requests-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: "{{ route('admin.referral.withdrawal.request') }}",
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
                        data: 'created_at',
                        name: 'referral_withdraw_requests.created_at', // Fully qualified table path
                        className: 'rc-info-start',
                        searchable: true
                    },
                    {
                        data: 'user_id',
                        name: 'users.name', // Route search queries directly to customer text names
                        className: 'rc-heading',
                        searchable: true
                    },
                    {
                        data: 'amount',
                        name: 'referral_withdraw_requests.amount', // Fully qualified table path
                        className: 'rc-info-next',
                        searchable: true
                    },
                    {
                        data: 'bank_details',
                        name: 'referral_withdraw_requests.bank_details', // Fully qualified table path
                        className: 'rc-info-next',
                        searchable: true
                    },
                    {
                        data: 'payment_status',
                        name: 'referral_withdraw_requests.payment_status', // Fully qualified table path
                        className: 'rc-badge-top-right',
                        searchable: true
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                preDrawCallback: function(settings) {
                    $('#requests-table_wrapper').addClass('placeholder-glow');

                    // Desktop Placeholder Rows (Adjusted to match 7 columns)
                    if ($('#requests-table tbody tr').length === 0) {
                        var placeholderRows = '';
                        for (var i = 0; i < 5; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(7) + '</tr>';
                        }
                        $('#requests-table tbody').html(placeholderRows);
                    }
                },
                drawCallback: function(settings) {
                    // Remove loading placeholder glow indicators
                    $('#requests-table_wrapper').removeClass('placeholder-glow');

                    // Clear desktop dynamic action placeholders
                    $('#requests-table tbody tr').each(function() {
                        var actionCell = $(this).find('td').eq(
                            6); // Targeting the 7th column (index 6)
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });

                    // Force empty table message cell to span all 7 columns correctly
                    $('#requests-table tbody tr td.dataTables_empty').attr('colspan', 7);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#requests-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#requests-table tbody tr').each(function() {
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

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing
            table.on('processing.dt', function(e, settings, processing) {
                if (processing) {
                    $('#requests-table_wrapper').addClass('placeholder-glow');
                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(7) + '</tr>';
                    }
                    $('#requests-table tbody').html(placeholderRows);
                }
            });

            // Bind click events on Mobile pagination triggers
            $('#mobile-prev-btn').on('click', function(e) {
                e.preventDefault();
                table.page('previous').draw('page');
            });

            // Bind click events on Mobile pagination triggers
            $('#mobile-next-btn').on('click', function(e) {
                e.preventDefault();
                table.page('next').draw('page');
            });
        });
    </script>

    {{-- Withdrawal Request Modal functions --}}
    <script>
        function updateWithdrawalRequest(requestId, status) {
            "use strict";

            var modalMessage = document.getElementById("withdrawal-request-status");
            let messageStatus = '{{ __('Accepted') }}';
            if (status == 2) {
                messageStatus = '{{ __('Transfer') }}';
            } else if (status == -1) {
                messageStatus = '{{ __('Rejected') }}';
            }
            let message =
                `{{ __('If you proceed, this withdrawal request will be :status.', ['status' => '${messageStatus}']) }}`;
            modalMessage.innerHTML = message.replace(':status', status);

            var link = document.getElementById("withdrawal-request-id");

            if (status == 1) {
                $("#withdrawal-request-modal").modal("show");
                link.setAttribute("href", "/admin/update-withdrawal-request-status?requestId=" + requestId +
                    "&status=accepted");
            } else if (status == 2) {
                $("#transfer-modal").modal("show");
                document.getElementById("request_id").value = requestId;
            } else {
                $("#withdrawal-request-modal").modal("show");
                link.setAttribute("href", "/admin/update-withdrawal-request-status?requestId=" + requestId +
                    "&status=rejected");
            }
        }
    </script>
@endsection
