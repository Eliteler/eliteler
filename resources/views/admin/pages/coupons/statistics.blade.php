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
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.coupons') }}" class="text-secondary text-decoration-none">
                                    {{ __('Coupons') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Statistics') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Statistics') }}
                        </h2>
                        <!-- Added helper subtitle under the heading -->
                        <div class="text-muted mt-1">
                            {{ __('View detailed usage and transaction statistics for this coupon.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <div class="card border-0 shadow-sm">
                            <div class="table-responsive px-3 pt-2 py-2">
                                <!-- Table renders normally on desktop and transforms automatically via CSS on mobile -->
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="transactions-table">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Transaction ID') }}</th>
                                            <th>{{ __('Customer Name') }}</th>
                                            <th>{{ __('Before Discount') }}</th>
                                            <th>{{ __('After Discount') }}</th>
                                            <th>{{ __('Saved') }}</th>
                                        </tr>
                                    </thead>
                                    {{-- Transactions static HTML render body --}}
                                    <tbody>
                                        @foreach ($couponUsage as $transaction)
                                            <tr>
                                                <td class="rc-hide">{{ $loop->iteration }}</td>
                                                <td class="rc-info-next">
                                                    @if ($transaction->used_for == 'plan')
                                                        <a href="{{ route('admin.view.invoice', $transaction->transaction_id) }}"
                                                            target="_blank" rel="noopener noreferrer">
                                                            {{ $transaction->transaction_id }}
                                                        </a>
                                                    @else
                                                        <a href="#" target="_blank" rel="noopener noreferrer">
                                                            {{ $transaction->transaction_id }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td class="rc-heading">
                                                    @if ($transaction->used_for == 'plan')
                                                        <a href="{{ route('admin.view.user', json_decode($transaction->user)->user_id) }}"
                                                            target="_blank" rel="noopener noreferrer">
                                                            {{ json_decode($transaction->user)->name }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin.view.user', json_decode($transaction->user)->user_id) }}"
                                                            target="_blank" rel="noopener noreferrer">
                                                            {{ json_decode($transaction->user)->name }}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td class="fw-bold rc-info-start">
                                                    @if ($transaction->transactions->isNotEmpty())
                                                        @php
                                                            $invoiceDetails = json_decode(
                                                                $transaction->transactions->first()->invoice_details,
                                                            );
                                                        @endphp
                                                        {{ formatCurrency($invoiceDetails->subtotal) ?? 'N/A' }}
                                                    @else
                                                        <span>{{ __('0') }}</span>
                                                    @endif
                                                </td>
                                                <td class="fw-bold rc-hide">
                                                    @if ($transaction->transactions->isNotEmpty())
                                                        @php
                                                            $invoiceDetails = json_decode(
                                                                $transaction->transactions->first()->invoice_details,
                                                            );
                                                        @endphp
                                                        {{ formatCurrency($invoiceDetails->invoice_amount) ?? 'N/A' }}
                                                    @else
                                                        <span>{{ __('0') }}</span>
                                                    @endif
                                                </td>
                                                <td class="rc-badge-top-right">
                                                    <span class="badge bg-success text-white">
                                                        @if ($transaction->transactions->isNotEmpty())
                                                            @php
                                                                $transactionDetails = json_decode(
                                                                    $transaction->transactions->first()
                                                                        ->invoice_details,
                                                                );
                                                                $subtotal = $transactionDetails->subtotal ?? 0;
                                                                $invoiceAmount =
                                                                    $transactionDetails->invoice_amount ?? 0;
                                                            @endphp
                                                            {{ formatCurrency($subtotal - $invoiceAmount) }}
                                                        @else
                                                            <span>{{ __('0') }}</span>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
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
@endsection

{{-- Custom scripts --}}
@section('scripts')
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            var table = $('#transactions-table').DataTable({
                processing: false,
                serverSide: false, // Set to false since data is statically looped in the Blade template
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
                    $('#transactions-table_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#transactions-table_wrapper').removeClass('placeholder-glow');

                    // Force empty table message cell to span all 6 columns correctly on desktop viewports
                    $('#transactions-table tbody tr td.dataTables_empty').attr('colspan', 6);

                    // Generate 'data-label' attributes based on the current <thead> elements
                    var headers = [];
                    $('#transactions-table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $('#transactions-table tbody tr').each(function() {
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

            // Debounced client-side search with dynamic skeleton card triggers
            var searchTimeout;
            $('.dataTables_filter input').unbind().on('input keyup paste', function() {
                var value = this.value;
                clearTimeout(searchTimeout);

                // Instantly trigger full-width skeleton lines inside table cards on mobile
                $('#transactions-table_wrapper').addClass('placeholder-glow');
                var placeholderRows = '';
                for (var i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(6) + '</tr>';
                }
                $('#transactions-table tbody').html(placeholderRows);

                // Debounce the actual local search redraw by 350ms
                searchTimeout = setTimeout(function() {
                    table.search(value).draw();
                    $('#transactions-table_wrapper').removeClass('placeholder-glow');
                }, 350);
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
    </script>
@endsection
