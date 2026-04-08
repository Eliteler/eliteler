@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
@endsection

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
                        <h2 class="page-title mb-2">
                            {{ __('Update NFC Card Order System') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('admin.plugins') }}" class="btn btn-primary text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l14 0" />
                                    <path d="M5 12l6 6" />
                                    <path d="M5 12l6 -6" />
                                </svg>
                                {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
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

                <div class="alert alert-important alert-info alert-dismissible" role="alert">
                    <div class="d-flex">
                        <div>
                            {{ __('Note: This will turn off the NFC Card Order System, making it invisible to both the admin and customers.') }}
                        </div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <form action="{{ route('admin.plugin.update.status.nfc.cards.order') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Enable/Disable NFC Card Order System --}}
                                        <div class="col-md-6 col-xl-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="mb-0">{{ __('Enable NFC Card Order System') }}</label>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="enable_disable_nfc_card_order" value="1"
                                                        {{ isset($config[76]->config_value) && $config[76]->config_value == '1' ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Enable/Disable NFC Card Order System in Website --}}
                                        <div class="col-md-6 col-xl-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="mb-0">{{ __('Enable NFC Card Order System in Website') }}</label>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="enable_disable_nfc_card_order_website" value="1"
                                                        {{ isset($config[97]->config_value) && $config[97]->config_value == '1' ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Character limit --}}
                                        <div class="col-md-6 col-xl-3">
                                            <label for="nfc_character_limit" class="form-label">{{ __('Character limit') }}</label>
                                            <input type="number" id="nfc_character_limit" name="nfc_character_limit"
                                                class="form-control"
                                                value="{{ isset($config[92]->config_value) ? $config[92]->config_value : 6 }}"
                                                step="1" min="3" max="25">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection
