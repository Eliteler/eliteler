@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

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
                            {{ __('Visitors') }}
                        </h2>
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
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table" id="visitorsTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('IP') }}</th>
                                            <th>{{ __('Language') }}</th>
                                            <th>{{ __('Platform') }}</th>
                                            <th>{{ __('Browser') }}</th>
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
        @include('user.includes.footer')
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script>
        "use strict";
        $('#visitorsTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: "{{ route('user.visitors.data', $id) }}",
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
                loadingRecords: `{{ __('Please wait - loading...') }}`,
                emptyTable: `{{ __('No data available in the table') }}`
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'ip_address',
                    name: 'ip_address'
                },
                {
                    data: 'language',
                    name: 'language'
                },
                {
                    data: 'platform',
                    name: 'platform'
                },
                {
                    data: 'user_agent',
                    name: 'user_agent'
                }
            ]
        });
    </script>
@endsection
@endsection
