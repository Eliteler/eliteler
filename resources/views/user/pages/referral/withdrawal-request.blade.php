@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Scoped CSS Overrides --}}
@section('css')
    <style>
        /* ==========================================================================
                                   GLOBAL DATATABLES VISIBILITY (Desktop & Mobile)
                                   ========================================================================== */
        div.dataTables_wrapper div.dataTables_filter,
        div.dataTables_wrapper div.dataTables_paginate,
        .dataTables_filter,
        .dataTables_paginate,
        .search-wrapper,
        .pagination-wrapper {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .search-wrapper {
            justify-content: flex-end !important;
            width: 100% !important;
        }

        /* Style search input */
        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 400;
            color: var(--tblr-body-color, #626976);
            margin-bottom: 0;
            width: 100%;
        }

        .dataTables_filter input {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 4px;
            padding: .4375rem .75rem;
            font-size: .875rem;
            line-height: 1.4285714286;
            background-color: var(--tblr-bg-forms, #fff);
            width: 100% !important;
            min-width: 220px;
        }

        .dataTables_filter input:focus {
            border-color: #b0d4f1;
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(9, 93, 197, .1);
        }

        div.dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 4px;
            padding: 0.35rem 1.5rem 0.35rem 0.5rem;
        }

        /* ==========================================================================
                                   RESPONSIVE MOBILE OVERRIDES (max-width: 767.98px)
                                   ========================================================================== */
        @media (max-width: 767.98px) {

            /* Force Search Container and Input box to be 100% full width */
            div.dataTables_wrapper div.dataTables_filter,
            .dataTables_filter,
            .search-wrapper {
                width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
                margin: 0 0 10px 0 !important;
            }

            .dataTables_filter label {
                width: 100% !important;
                justify-content: center !important;
                margin-bottom: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
            }

            .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
                margin-top: 6px !important;
            }

            /* Force Pagination elements to be visible and center on mobile */
            div.dataTables_wrapper div.dataTables_paginate,
            .dataTables_paginate,
            .pagination-wrapper,
            .pagination {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 100% !important;
                justify-content: center !important;
                flex-wrap: wrap !important;
                margin: 10px auto 0 auto !important;
                padding: 0 !important;
            }

            .dataTables_paginate .paginate_button,
            .pagination .page-item,
            .page-link {
                display: inline-flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .card-footer {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 12px !important;
                text-align: center !important;
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
                    <div class="col">
                        <!-- Breadcrumb Navigation -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.referrals') }}" class="text-secondary text-decoration-none">
                                        {{ __('Referrals') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('Withdrawal Requests') }}
                                </li>
                            </ol>
                        </nav>

                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('Withdrawal Request') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your target payout coordinates and track historical withdrawal transactions.') }}
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
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Update Bank Details configuration widget --}}
                <div class="row row-deck row-cards mb-4 d-print-none">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Update Bank Details') }}</h3>
                            </div>
                            <form action="{{ route('user.update.bank.details') }}" method="post"
                                enctype="multipart/form-data" class="card border-0">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 px-0">
                                            <div class="form-group">
                                                <label
                                                    class="form-label required">{{ __('Bank Details / Paypal Email Address') }}</label>
                                                <textarea name="bank_details" class="form-control" id="bank_details" rows="5">{{ $bankDetails }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end bg-transparent border-top-0 pt-0">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <circle cx="12" cy="14" r="2" />
                                            <polyline points="14 4 14 8 8 8 8 4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Withdrawal requests list grid --}}
                <div class="row row-deck row-cards mt-1">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                                <h3 class="card-title mb-0">{{ __('Withdrawal Requests') }}</h3>
                                {{-- Raise new request trigger --}}
                                <a href="{{ route('user.new.withdrawal.request') }}"
                                    class="btn btn-primary btn-md btn-icon-only d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus me-2"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    <span class="d-none d-sm-inline-block">{{ __('New Request') }}</span>
                                </a>
                            </div>

                            <!-- Withdrawal Request Table container -->
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="withdrawal-request-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Request ID') }} </th>
                                            <th>{{ __('Transaction ID') }}</th>
                                            <th>{{ __('Requested Amount') }} </th>
                                            <th>{{ __('Transfer To') }}</th>
                                            <th>{{ __('Notes') }}</th>
                                            <th>{{ __('Transfer Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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

{{-- Custom scripts pushed inside correct stack --}}
@push('custom-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script type="text/javascript">
        "use strict";

        // Dark Mode detection
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        tinymce.init({
            selector: 'textarea#bank_details',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
            menubar: 'file edit view insert format tools table help',
            toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | pagebreak | link',
            toolbar_sticky: true,
            height: 200,
            menubar: false,
            statusbar: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'dark'
            ] : [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'default'
            ],
            setup: function(editor) {
                editor.on('init', function() {
                    if (document.body.dataset.bsTheme === 'dark') {
                        editor.getBody().setAttribute('data-bs-theme', 'dark');
                    }
                });
            },
            content_style: isDark ? `
                body {
                    background-color: #151f2c !important;
                    font-family: "Figtree", sans-serif !important;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    color: rgba(34,47,62,.5) !important;
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            ` : `
                body {
                    font-family: "Figtree", sans-serif !important;
                }
                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            autosave_interval: '30s',
            autosave_prefix: '{path}{query}-{id}-',
            autosave_restore_when_empty: false,
            autosave_retention: '2m',

            // Correct mobile settings (limited fields allowed)
            mobile: {
                plugins: 'paste',
                toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            "use strict";

            var requestsTable = $('#withdrawal-request-table').DataTable({
                processing: false,
                serverSide: true,
                order: [
                    [1, 'desc']
                ], // Default sorting target to Column 1 (Date)
                dom: "<'card-body py-3 d-flex flex-column flex-sm-row align-items-center gap-3'<'text-muted'l><'ms-sm-auto search-wrapper'f>>" +
                    "<'table-responsive px-3 pt-2 py-2'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: "{{ route('user.referrals.withdrawal.request') }}",
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "sSearchPlaceholder": `{{ __('Search requests...') }}`,
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
                        data: 'created_at',
                        name: 'referral_withdraw_requests.created_at', // Fully qualified
                        searchable: true
                    },
                    {
                        data: 'request_id',
                        name: 'referral_withdraw_requests.referral_withdraw_request_id', // Fixed database column name mapping
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'transfer_id',
                        name: 'referral_withdraw_transactions.transfer_id', // Fully qualified
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'amount',
                        name: 'referral_withdraw_requests.amount', // Fully qualified
                        searchable: true
                    },
                    {
                        data: 'bank_details',
                        name: 'referral_withdraw_requests.bank_details', // Fully qualified
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'notes',
                        name: 'referral_withdraw_transactions.notes', // Fully qualified
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'payment_status',
                        name: 'referral_withdraw_requests.payment_status', // Fully qualified
                        searchable: true
                    },
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
                            .repeat(total_columns) + '</tr>';
                    }
                    $tbody.html(placeholderRows);
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    // Dynamic response header loop
                    var headers = [];
                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $table.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            var label = headers[index] || '';
                            $(this).attr('data-label', label);
                        });
                    });
                }
            });

            // Trigger Skeleton Glow Loader state dynamically when table AJAX transactions are processing
            $('#withdrawal-request-table').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });

            // Delegated touch-input filtering to catch search queries cleanly on any mobile viewport
            var searchTimer;
            $(document).on('input', '#withdrawal-request-table_filter input', function() {
                var value = this.value;
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    requestsTable.search(value).draw();
                }, 350);
            });
        });
    </script>
@endpush
