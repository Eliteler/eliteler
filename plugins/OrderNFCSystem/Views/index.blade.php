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
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'NFC Card Orders'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('NFC Card Orders') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Manage NFC card purchase visibility parameters, public ordering capabilities, and engraving validation limits.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
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

                {{-- Success Alert --}}
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

                {{-- Dynamic Status Callout Alert --}}
                <div class="alert alert-important alert-info alert-dismissible mb-3 shadow-sm border-0" role="alert">
                    <div class="d-flex">
                        <div>
                            {{ __('Note: Disabling these features will turn off the NFC Card Order System, hiding corresponding options from both the administrator panel and customer dashboards.') }}
                        </div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                <div class="row">
                    <div class="col-12 col-md-4">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.plugin.update.status.nfc.cards.order') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-4 pb-2">
                                        {{-- Enable/Disable NFC Card Order System --}}
                                        <div class="col-12">
                                            <div class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <p class="fw-bold fs-4">{{ __('NFC Card Order System') }}</p>
                                                <label class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="enable_disable_nfc_card_order" value="1"
                                                        {{ isset($config[76]->config_value) && $config[76]->config_value == '1' ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ __('Globally controls visibility of the ordering module for admins and customers.') }}
                                            </small>
                                        </div>

                                        {{-- Enable/Disable NFC Card Order System in Website --}}
                                        <div class="col-12">
                                            <div class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <p class="fw-bold fs-4">{{ __('Website Ordering') }}</p>
                                                <label class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="enable_disable_nfc_card_order_website" value="1"
                                                        {{ isset($config[97]->config_value) && $config[97]->config_value == '1' ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ __('Allow public-facing site visitors to browse and purchase physical cards from landing pages.') }}
                                            </small>
                                        </div>

                                        {{-- Engraving Character Limit --}}
                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="nfc_character_limit"
                                                    class="form-label fw-bold mb-1">{{ __('NFC Text Character Limit') }}</label>
                                                <input type="number" id="nfc_character_limit" name="nfc_character_limit"
                                                    class="form-control" placeholder="{{ __('Enter limit (e.g., 6)') }}"
                                                    value="{{ isset($config[92]->config_value) ? $config[92]->config_value : 6 }}"
                                                    step="1" min="3" max="25" required>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ __('Defines the length constraints for the customized generated key (between 3 and 25 characters).') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <!-- Device Floppy Icon -->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4h-6l0 -4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <!-- Script assets loaded inside the correct scripts section -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
@endsection
