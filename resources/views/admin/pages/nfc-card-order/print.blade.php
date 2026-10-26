@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        /* Keeping local overrides dry and scoped */
        .fw-mono {
            font-family: var(--tblr-font-monospace, monospace);
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
                                <a href="{{ route('admin.designs') }}" class="text-secondary text-decoration-none">
                                    {{ __('NFC Sales') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.orders') }}" class="text-secondary text-decoration-none">
                                    {{ __('Orders') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Write NFC Card') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Write to NFC Card') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('Use the link to write data to the NFC card.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                <div class="row g-3">
                    {{-- NFC Card Keys List Card (Upgraded to Borderless Shadowed Card) --}}
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm">

                            @php
                                $uniqueKeys = json_decode($orderDetails->order_details, true)['unique_key'] ?? [];

                                if (!is_array($uniqueKeys)) {
                                    $uniqueKeys = [$uniqueKeys];
                                }
                            @endphp

                            <div class="card-body">
                                @foreach ($uniqueKeys as $key)
                                    <div
                                        class="p-3 rounded-2 d-flex justify-content-between align-items-center mb-2 border">
                                        <span class="text-truncate fw-mono text-dark select-all me-2">
                                            {{ route('read.nfc.card', $key) }}
                                        </span>
                                        <button
                                            class="btn btn-primary btn-icon-only flex-shrink-0 ms-2 d-inline-flex align-items-center"
                                            onclick="copyToClipboard('{{ route('read.nfc.card', $key) }}')"
                                            title="{{ __('Copy to Clipboard') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-copy me-1">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M7 7m0 2.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667z" />
                                                <path
                                                    d="M4.012 16.737a2.005 2.005 0 0 1 -1.012 -1.737v-10c0 -1.1 .9 -2 2 -2h10c.75 0 1.158 .385 1.5 1" />
                                            </svg>
                                            <span class="d-none d-sm-inline">{{ __('Copy') }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- NFC Card Logo Download Section (Upgraded to Borderless Shadowed Card) --}}
                    @if (!empty($nfcCardLogo))
                        <div class="col-12 col-lg-5">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('NFC Card Logo') }}</h3>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <a href="{{ asset($nfcCardLogo) }}" class="d-inline-block" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="{{ __('Download NFC Card Logo') }}" download>
                                            <img src="{{ asset($nfcCardLogo) }}" class="img-fluid rounded border shadow-sm"
                                                style="max-width: 240px;" alt="NFC Card Logo">
                                        </a>
                                    </div>

                                    {{-- Download Button --}}
                                    <a href="{{ asset($nfcCardLogo) }}"
                                        class="btn btn-outline-primary btn-md d-inline-flex align-items-center"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="{{ __('Download NFC Card Logo') }}" download>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-download me-1">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                            <polyline points="7 11 12 16 17 11" />
                                            <line x1="12" y1="4" x2="12" y2="16" />
                                        </svg>
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Copy clipboard modal indicator --}}
    <div class="modal modal-bg fade" id="copy-clipboard" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon mb-2 text-green icon-lg">
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                        <path d="M9 12l2 2l4 -4"></path>
                    </svg>
                    <h3 id="status_id"></h3>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript">
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            // Show modal
            $('#copy-clipboard').modal('show');
            // Update status
            $('#status_id').text('{{ __('Copied to clipboard!') }}');
            setTimeout(function() {
                $('#copy-clipboard').modal('hide');
            }, 2000);
        }
    </script>
@endsection
