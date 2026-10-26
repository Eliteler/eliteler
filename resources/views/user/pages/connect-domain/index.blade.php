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

        .dataTables_filter input {
            height: 40px !important;
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
                height: 40px !important;
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
                font-size: 14px !important;
            }

            #previousDomain_info {
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
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Custom Domain Cofiguration') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Connect with Custom Domain') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Map personalized web domains, configure CNAME resource pointers, and manage connected web hosting targets.') }}
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

                {{-- Important Notice --}}
                <div class="alert alert-important alert-primary alert-dismissible mb-4 border-0 shadow-sm" role="alert">
                    <div class="d-flex">
                        <div>
                            {{ __('DNS record changes can take 24 to 48 hours to propagate to your connected domain. During this time, your digital business card or store may not be visible on the connected domain.') }}
                        </div>
                    </div>
                </div>

                <div class="row row-deck row-cards g-3">
                    {{-- Domain Request Form --}}
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Decoupled form structure) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.new.domain.request') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <input type="hidden" name="card_id" value="{{ Request::segment(3) }}">
                                    <div class="row align-items-center">
                                        <label
                                            class="col-md-3 col-form-label required fw-bold">{{ __('Custom Domain') }}</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="domain" name="domain"
                                                placeholder="example.com" required>
                                            <small
                                                class="form-hint text-muted mt-1">{{ __('Don\'t use http:// or https://') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer containing standalone save trigger styled alone -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        {{ __('Request') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Previous Domain list --}}
                    <div class="col-md-6">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Clean design-time padding) -->
                        <div class="card border-0 shadow-sm p-3">
                            <h3 class="card-title mb-3">{{ __('Connected Domain') }}</h3>
                            <!-- Removed design-time table-responsive wrapper so Datatables elements stay static -->
                            <table class="table card-table table-vcenter text-nowrap" id="previousDomain">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Domain') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="w-1 text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($previous_domains as $domain)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="fw-bold text-dark">{{ $domain->current_domain }}</span>
                                                <a href="http://{{ $domain->current_domain }}" target="_blank"
                                                    class="ms-1 text-decoration-none" aria-label="Open website">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon text-secondary">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                        <path d="M9 15l6 -6"></path>
                                                        <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464">
                                                        </path>
                                                        <path
                                                            d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463">
                                                        </path>
                                                    </svg>
                                                </a>
                                            </td>
                                            <td>
                                                <!-- Soft-muted status label badges mapping -->
                                                @if ($domain->transfer_status == 0)
                                                    <span
                                                        class="badge bg-yellow-100 text-yellow-800 px-2 py-1">{{ __('Processing') }}</span>
                                                @elseif ($domain->transfer_status == 1)
                                                    <span
                                                        class="badge bg-green-600 text-green-50 px-2 py-1">{{ __('Connected') }}</span>
                                                @elseif ($domain->transfer_status == 2)
                                                    <span
                                                        class="badge bg-red-100 text-red-700 px-2 py-1">{{ __('Disconnected') }}</span>
                                                @else
                                                    <span
                                                        class="badge bg-red-100 text-red-700 px-2 py-1">{{ __('Rejected') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="dropdown">
                                                    <button class="btn btn-white btn-sm dropdown-toggle align-text-top"
                                                        data-bs-boundary="viewport" data-bs-toggle="dropdown"
                                                        aria-expanded="false">{{ __('Action') }}</button>
                                                    <div class="actions dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item text-danger"
                                                            onclick="UnlinkDomain(`{{ $domain->custom_domain_request_id }}`, `{{ $domain->card_id }}`)">{{ __('Unlink') }}</a>
                                                    </div>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Documentation Instructions --}}
                    <div class="col-md-6">
                        <!-- Upgraded to Borderless Shadowed Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h1 class="card-title fw-bold text-dark fs-2 mb-3">
                                    {{ __('Steps to Add a Custom Domain to Your vCard or Store') }}</h1>

                                <h2 class="fs-3 mt-3 mb-2 text-secondary">1. {{ __('Access Your DNS Management') }}</h2>
                                <ul class="text-muted">
                                    <li>{{ __('Log in to your domain registrar’s account (e.g., GoDaddy, Namecheap, Bluehost, etc.).') }}
                                    </li>
                                    <li>{{ __('Navigate to the DNS management section for the domain you wish to connect.') }}
                                    </li>
                                </ul>

                                <h2 class="fs-3 mt-3 mb-2 text-secondary">2. {{ __('Add a CNAME Record') }}</h2>
                                <ul class="text-muted">
                                    <li>{{ __('Look for an option to add a new DNS record.') }}</li>
                                    <li>{!! __('Choose <strong>CNAME</strong> as the record type.') !!}</li>
                                    <li>{{ __('Fill in the details as follows:') }}</li>
                                    <ul class="mt-1">
                                        <li>{!! __('<strong>Host</strong>: <code>@</code> (This represents the root domain.)') !!}</li>
                                        <li>{!! __(
                                            '<strong>Value</strong>: <code>' . str_replace(['http://', 'https://'], '', config('app.url')) . '</code>',
                                        ) !!}</li>
                                        <li>{!! __('<strong>Proxy Status</strong>: DNS only') !!}</li>
                                        <li>{!! __('<strong>TTL</strong>: Leave it as the auto or set to your desired value.') !!}</li>
                                    </ul>
                                </ul>

                                <div class="table-responsive px-0 py-2 rounded mb-3">
                                    <table class="table card-table table-vcenter text-nowrap mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Host') }}</th>
                                                <th>{{ __('Value') }}</th>
                                                <th>{{ __('Proxy Status') }}</th>
                                                <th>{{ __('TTL') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-muted">
                                                <td>CNAME</td>
                                                <td>@</td>
                                                <td>{{ str_replace(['http://', 'https://'], '', config('app.url')) }}
                                                </td>
                                                <td>DNS only</td>
                                                <td>auto</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <p class="text-muted small">{{ __('If you are using Cloudflare,') }}
                                    {{ __('Please refer to the following link to complete your custom domain.') }}</p>
                                <a href="{{ route('user.custom.domain.cloudflare.rules') }}" target="_blank"
                                    class="fw-bold text-decoration-none hover-primary">{{ __('Cloudflare DNS Settings') }}</a><br><br>

                                <h2 class="fs-3 mt-2 mb-2 text-secondary">3. {{ __('Save Changes') }}</h2>
                                <ul class="text-muted">
                                    <li>{{ __('After adding the CNAME record, ensure to save your changes in the DNS management panel.') }}
                                    </li>
                                </ul>

                                <h2 class="fs-3 mt-3 mb-2 text-secondary">4. {{ __('Configure Your vCard or Store') }}
                                </h2>
                                <ul class="text-muted">
                                    <li>{{ __('After adding the custom domain to DNS, enter the custom domain you set up') }}
                                        {!! __('(e.g., <code>yourdomain.com</code>).') !!}</li>
                                </ul>

                                <h2 class="fs-3 mt-3 mb-2 text-secondary">5. {{ __('Verification') }}</h2>
                                <ul class="text-muted">
                                    <li>{{ __('After requesting, the custom domain will go to the website admin. They will update you by adding the custom domain name to your vCard or store. Until then, your request will be under processing. It may take some time for the website admin to handle this.') }}
                                    </li>
                                </ul>

                                <h2 class="fs-3 mt-3 mb-2 text-danger">{{ __('Important Note') }}</h2>
                                <p class="text-muted small">
                                    {{ __('Methods may vary by provider: The steps for adding a CNAME record may differ based on your domain provider. Some registrars might have a different interface or terminology. If you encounter any difficulties, refer to your provider’s documentation or customer support for assistance.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Unlink domain modal upgraded to borderless shadowed style --}}
    <div class="modal modal-bg fade" id="unlinkDomainModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <div id="unlink_status" class="text-secondary"></div>
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
                                <a data-method="post" class="btn btn-danger w-100" id="request_id">
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

@section('scripts')
    <!-- Validate domain -->
    <script type="text/javascript">
        "use strict";

        $(document).ready(function() {
            const totalColumns = 4;

            const table = $('#previousDomain').DataTable({
                processing: false,
                serverSide: false, // Static client-side table listing
                ordering: false,
                // Integrated 'f' selector parameter cleanly with responsive columns structure
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
                    $('#previousDomain_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#previousDomain_wrapper').removeClass('placeholder-glow');

                    // Match empty display widths
                    $('#previousDomain tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            // Wrap ONLY the table element in the responsive wrapper container dynamically
            $('#previousDomain').wrap('<div class="table-responsive px-0 py-2"></div>');

            // Form validation strip parameters inputs
            $('#domain').on('input', function() {
                var domain = $('#domain').val();
                if (domain.indexOf('http://') == 0 || domain.indexOf('https://') == 0) {
                    $('#domain').val(domain.replace('http://', '').replace('https://', ''));
                }
            });

            // Implement static 350ms debounced filters execution with matching skeletons
            $('.dataTables_filter input').unbind().on('keyup', function() {
                const searchValue = this.value;
                clearTimeout(window.searchTimeout);

                $('#previousDomain_wrapper').addClass('placeholder-glow');
                let placeholderRows = '';
                for (let i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#previousDomain tbody').html(placeholderRows);

                window.searchTimeout = setTimeout(function() {
                    table.search(searchValue).draw();
                }, 350);
            });
        });

        // Validate domain format parameters
        function validateDomain(domain) {
            "use strict";
            var pattern = /^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/;
            return pattern.test(domain);
        }

        // Unlink domain requests modal configurations
        function UnlinkDomain(id, card_id) {
            "use strict";
            $("#unlinkDomainModal").modal("show");
            var unlink_status = document.getElementById("unlink_status");

            let messageStatus = "unlinked";
            let message = `{{ __('If you proceed, this domain will be :status.', ['status' => '${messageStatus}']) }}`;
            unlink_status.innerHTML = message.replace(':status', status);

            var unlink_link = document.getElementById("request_id");
            unlink_link.setAttribute("href", "{{ route('user.unlink.domain') }}?id=" + id + '&card_id=' + card_id);
        }
    </script>
@endsection
