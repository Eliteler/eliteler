@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Scoped CSS Overrides --}}
@section('css')
    <style>
        /* Modern Minimalist Share Icons */
        .btn-share {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--tblr-bg-surface, #fff);
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            color: var(--tblr-secondary, #626976);
            transition: all 0.2s ease-in-out;
        }

        .btn-share:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .btn-share-facebook:hover {
            background-color: #1877f2;
            border-color: #1877f2;
        }

        .btn-share-x:hover {
            background-color: #000000;
            border-color: #000000;
        }

        .btn-share-linkedin:hover {
            background-color: #0a66c2;
            border-color: #0a66c2;
        }

        .btn-share-instagram:hover {
            background-color: #e1306c;
            border-color: #e1306c;
        }

        .btn-share-whatsapp:hover {
            background-color: #25d366;
            border-color: #25d366;
        }

        .btn-share-email:hover {
            background-color: #206bc4;
            border-color: #206bc4;
        }

        .btn-share-copy:hover {
            background-color: #4299e1;
            border-color: #4299e1;
        }

        .dataTables_filter input {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 6px;
            padding: .4375rem .75rem;
            font-size: .875rem;
            background-color: var(--tblr-bg-forms, #fff);
            min-width: 220px;
        }

        .dataTables_filter input:focus {
            border-color: #b0d4f1;
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(9, 93, 197, .1);
        }

        #custom-pagination {
            display: none !important;
        }

        @media (max-width: 767.98px) {

            .dataTables_filter,
            .dataTables_filter label {
                width: 100%;
            }

            .dataTables_filter input {
                width: 100% !important;
                min-width: 100% !important;
                margin-left: 0 !important;
            }

            .dataTables_wrapper .dataTables_paginate {
                display: block !important;
                float: none !important;
                text-align: center !important;
                margin-top: 1rem;
            }

            .dataTables_wrapper .pagination {
                justify-content: center !important;
                flex-wrap: wrap;
            }

            .dataTables_wrapper .dataTables_info {
                float: none !important;
                text-align: center !important;
                margin-bottom: .75rem;
            }

        }

        @media screen and (max-width: 767px) {
            div.dataTables_wrapper div.dataTables_paginate {
                display: none !important;
            }

            #custom-pagination {
                display: flex !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumb Navigation -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Referrals') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Referrals') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Share your unique invitation link, monitor registration payouts, and manage your current referral earnings.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-4 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-4 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Referral Details Widgets --}}
                <div class="row row-deck row-cards g-3">
                    {{-- Overall Earnings --}}
                    <div class="col-sm-4 col-lg-3">
                        <div class="card border-0 shadow-sm position-relative overflow-hidden h-100">
                            <div class="card-status-start"></div>
                            <div class="card-body d-flex flex-column justify-content-between py-4">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="subheader text-secondary fw-bold">{{ __('Overall Earnings') }}</span>
                                        <span class="avatar avatar-sm bg-primary-lt text-primary rounded-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="icon">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
                                                <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="h1 mb-1 fw-bolder" style="font-size: 2rem;">
                                        {{ formatCurrency($overAllEarning) }}</div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-muted small d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-binary-tree-2 text-success me-2">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 6a2 2 0 1 0 -4 0a2 2 0 0 0 4 0" />
                                            <path d="M7 14a2 2 0 1 0 -4 0a2 2 0 0 0 4 0" />
                                            <path d="M21 14a2 2 0 1 0 -4 0a2 2 0 0 0 4 0" />
                                            <path d="M14 18a2 2 0 1 0 -4 0a2 2 0 0 0 4 0" />
                                            <path d="M12 8v8" />
                                            <path d="M6.316 12.496l4.368 -4.992" />
                                            <path d="M17.684 12.496l-4.366 -4.99" />
                                        </svg>
                                        {{ __('All-time generated commission') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Current Balance --}}
                    @php
                        // Dynamically calculate progress toward minimum withdrawal
                        $safeMin = $minWithdrawalAmount > 0 ? $minWithdrawalAmount : 1;
                        $progressPercent = min(100, round(($currentBalance / $safeMin) * 100));
                        $isEligible = $currentBalance >= $minWithdrawalAmount;
                    @endphp
                    <div class="col-sm-4 col-lg-3">
                        <div class="card border-0 shadow-sm position-relative overflow-hidden h-100">
                            <div class="card-status-start"></div>
                            <div class="card-body d-flex flex-column justify-content-between py-4">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="subheader text-secondary fw-bold">{{ __('Current Balance') }}</span>
                                        <span class="avatar avatar-sm bg-success-lt text-success rounded-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="icon">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M7 15h-3a1 1 0 0 1 -1 -1v-8a1 1 0 0 1 1 -1h12a1 1 0 0 1 1 1v3" />
                                                <path
                                                    d="M7 10a1 1 0 0 1 1 -1h12a1 1 0 0 1 1 1v8a1 1 0 0 1 -1 1h-12a1 1 0 0 1 -1 -1l0 -8" />
                                                <path d="M12 14a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="h1 mb-1 fw-bolder" style="font-size: 2rem;">
                                        {{ formatCurrency($currentBalance) }}</div>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex align-items-center justify-content-between small text-muted mb-1">
                                        <span>{{ __('Payout Threshold') }}</span>
                                        <span class="fw-bold">{{ $progressPercent }}%</span>
                                    </div>
                                    <div class="progress progress-sm mb-2">
                                        <div class="progress-bar {{ $isEligible ? 'bg-success' : 'bg-warning' }}"
                                            style="width: {{ $progressPercent }}%" role="progressbar"
                                            aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between small">
                                        <span class="text-muted">{{ __('Min withdrawal:') }}
                                            <strong>{{ formatCurrency($minWithdrawalAmount) }}</strong></span>
                                        @if ($isEligible)
                                            <span class="badge bg-success-lt text-success">{{ __('Eligible') }}</span>
                                        @else
                                            <span class="badge bg-secondary-lt text-secondary">{{ __('Pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Referral URL + Modern Share Action Block --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm position-relative overflow-hidden h-100">
                            <div class="card-status-start"></div>
                            <div class="card-body d-flex flex-column justify-content-between py-4">
                                <div>
                                    <h3 class="card-title text-secondary fw-bold mb-3">
                                        {{ __('Your Invitation Link') }}</h3>

                                    <div class="input-group input-group-flat mb-3">
                                        <input type="text" class="form-control fw-medium" id="referral-url"
                                            value="{{ $referralUrl }}" readonly>
                                        <button type="button"
                                            class="btn btn-primary d-inline-flex align-items-center gap-1"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ __('Copy to Clipboard') }}"
                                            data-clipboard-text="{{ $referralUrl }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-copy">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <rect x="8" y="8" width="12" height="12" rx="2" />
                                                <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
                                            </svg>
                                            <span>{{ __('Copy') }}</span>
                                        </button>
                                    </div>
                                </div>

                                @php
                                    $referralMessage =
                                        __(
                                            'Hey! I found something awesome that I think you’ll love. Check it out using my referral link: ',
                                        ) .
                                        $referralUrl .
                                        ' 🚀';
                                @endphp

                                <div>
                                    <div class="text-muted fw-bold text-uppercase mb-2"
                                        style="font-size: 0.7rem; letter-spacing: .04em;">
                                        {{ __('Share Instantly') }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($referralUrl) }}&quote={{ urlencode($referralMessage) }}"
                                            class="btn-share btn-share-facebook" target="_blank" rel="noopener"
                                            data-bs-toggle="tooltip" title="{{ __('Facebook') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-facebook">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
                                            </svg>
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($referralMessage) }}"
                                            class="btn-share btn-share-x" target="_blank" rel="noopener"
                                            data-bs-toggle="tooltip" title="{{ __('X (Twitter)') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                                                <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                                            </svg>
                                        </a>
                                        <a href="https://www.linkedin.com/shareArticle?url={{ urlencode($referralUrl) }}&summary={{ urlencode($referralMessage) }}"
                                            class="btn-share btn-share-linkedin" target="_blank" rel="noopener"
                                            data-bs-toggle="tooltip" title="{{ __('LinkedIn') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <rect x="4" y="4" width="16" height="16" rx="2" />
                                                <path d="M8 11v5" />
                                                <path d="M8 8v.01" />
                                                <path d="M12 16v-5" />
                                                <path d="M16 16v-3a2 2 0 0 0 -4 0" />
                                            </svg>
                                        </a>
                                        <a href="https://wa.me/?text={{ urlencode($referralMessage) }}"
                                            class="btn-share btn-share-whatsapp" target="_blank" rel="noopener"
                                            data-bs-toggle="tooltip" title="{{ __('WhatsApp') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                                <path
                                                    d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                                            </svg>
                                        </a>
                                        <a href="mailto:?subject=Check this out&body={{ urlencode($referralMessage) }}"
                                            class="btn-share btn-share-email" data-bs-toggle="tooltip"
                                            title="{{ __('Email') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                                <path d="M3 7l9 6l9 -6" />
                                            </svg>
                                        </a>
                                        <button type="button" class="btn-share btn-share-copy" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="{{ __('Copy entire message') }}"
                                            data-clipboard-text="{{ $referralMessage }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-message-share">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M20 11v-3a3 3 0 0 0 -3 -3h-10a3 3 0 0 0 -3 3v8a3 3 0 0 0 3 3h7" />
                                                <path d="M16 22l5 -5" />
                                                <path d="M21 21.5v-4.5h-4.5" />
                                                <path d="M8 9h8" />
                                                <path d="M8 13h6" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Referral List Table --}}
                <div class="row row-deck row-cards mt-2">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-0 pb-0">
                                <h3 class="card-title">{{ __('Referral History') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="referral-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Registered User') }}</th>
                                            <th>{{ __('Is Registered') }}</th>
                                            <th>{{ __('Is Subscribed') }}</th>
                                            <th>{{ __('Referral Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <div class="d-flex justify-content-between align-items-center mt-3"
                                    id="custom-pagination">
                                    <button class="btn btn-primary" id="prev-page">{{ __('Previous') }}</button>

                                    <span id="page-info"></span>

                                    <button class="btn btn-primary" id="next-page">{{ __('Next') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            var referralTable = $('#referral-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: false,
                autoWidth: false,
                order: [
                    [1, 'desc']
                ],
                dom: "<'row align-items-center g-3 mb-3'<'col-12 col-md-6'l><'col-12 col-md-6'f>>" +
                    "rt" +
                    "<'row align-items-center g-3 mt-3'<'col-12 col-md-5'i><'col-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>",

                ajax: "{{ route('user.referrals') }}",

                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: "",
                    sSearchPlaceholder: `{{ __('Search referrals...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
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
                        data: 'user_id',
                        name: 'referrer.name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'is_registered',
                        name: 'referrals.is_registered'
                    },
                    {
                        data: 'is_subscribed',
                        name: 'referrals.is_subscribed'
                    },
                    {
                        data: 'referral_amount',
                        name: 'referral_amount',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'referrals.status'
                    }
                ],

                preDrawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $tbody = $(settings.nTable).find('tbody');

                    $tbody.closest('.table-responsive').addClass('placeholder-glow');
                    var placeholderRows = '';

                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(total_columns) +
                            '</tr>';
                    }

                    $tbody.html(placeholderRows);
                },

                drawCallback: function(settings) {

                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');

                    $table.find('tbody td.dataTables_empty')
                        .attr('colspan', total_columns);

                    var headers = [];

                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $table.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            $(this).attr('data-label', headers[index] || '');
                        });
                    });

                    // Mobile fixes
                    $('.dataTables_filter input')
                        .addClass('form-control')
                        .css({
                            width: '100%',
                            'min-width': '100%'
                        });

                    $('.dataTables_paginate')
                        .addClass('d-flex justify-content-center justify-content-md-end w-100');

                    $('.dataTables_paginate .pagination')
                        .addClass('justify-content-center flex-wrap');
                }
            });

            $('#referral-table').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });

            function updatePagination() {
                var info = referralTable.page.info();

                $('#page-info').text('Page ' + (info.page + 1) + ' of ' + info.pages);

                $('#prev-page').prop('disabled', info.page === 0);
                $('#next-page').prop('disabled', info.page === info.pages - 1);
            }

            referralTable.on('draw', updatePagination);

            $('#prev-page').on('click', function() {
                referralTable.page('previous').draw('page');
            });

            $('#next-page').on('click', function() {
                referralTable.page('next').draw('page');
            });

            updatePagination();

            var searchTimer;
            $(document).on('input', '#referral-table_filter input', function() {
                var value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    referralTable.search(value).draw();
                }, 350);
            });
        });
    </script>

    {{-- Copy to clipboard with legacy fallback support --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const clipboardButtons = document.querySelectorAll('[data-clipboard-text]');

            clipboardButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const text = button.getAttribute('data-clipboard-text');

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(() => {
                            showTooltip(button);
                        }).catch(err => {
                            console.error('Failed to copy: ', err);
                        });
                    } else {
                        const textArea = document.createElement("textarea");
                        textArea.value = text;
                        textArea.style.position = "fixed";
                        textArea.style.left = "-999999px";
                        textArea.style.top = "-999999px";
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        try {
                            document.execCommand('copy');
                            showTooltip(button);
                        } catch (err) {
                            console.error('Fallback copy failed', err);
                        }
                        document.body.removeChild(textArea);
                    }
                });
            });

            function showTooltip(element) {
                const tooltip = bootstrap.Tooltip.getInstance(element) || new bootstrap.Tooltip(element);
                tooltip.setContent({
                    '.tooltip-inner': '{{ __('Copied!') }}'
                });
                tooltip.show();
                setTimeout(() => tooltip.hide(), 1500);
            }
        });
    </script>
@endpush
