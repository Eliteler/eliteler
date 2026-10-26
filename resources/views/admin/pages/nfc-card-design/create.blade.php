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
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
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
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.designs') }}" class="text-secondary text-decoration-none">
                                    {{ __('NFC Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create NFC Card') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Create NFC Card') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('Configure card artwork assets, pricing details, initial stock, and product descriptions.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
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

                <div class="row row-deck row-cards">
                    <div class="col-md-6 col-lg-12">
                        <form action="{{ route('admin.save.design') }}" method="post" enctype="multipart/form-data"
                            class="card border-0 shadow-sm">
                            @csrf

                            <div class="card-body">
                                <div class="row g-4">
                                    {{-- Image Upload Section --}}
                                    <div class="col-md-12 col-lg-6">
                                        <div>
                                            <label class="form-label required">{{ __('Front Image') }}</label>
                                            <input type="file" class="form-control" name="nfc_card_front_image" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6">
                                        <div>
                                            <label class="form-label required">{{ __('Back Image') }}</label>
                                            <input type="file" class="form-control" name="nfc_card_back_image" required>
                                        </div>
                                    </div>

                                    {{-- Basic Specifications Section --}}
                                    <div class="col-md-12 col-lg-4">
                                        <div>
                                            <label class="form-label required">{{ __('Name') }}</label>
                                            <input type="text" class="form-control" name="nfc_card_name"
                                                placeholder="{{ __('E.g., Wooden NFC Card') }}" required minlength="3"
                                                maxlength="999">
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-4">
                                        <div>
                                            <label class="form-label required">{{ __('Price') }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="nfc_card_price"
                                                    placeholder="{{ __('E.g., 50.00') }}" required min="0"
                                                    max="9999999999" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-4">
                                        <div>
                                            <label class="form-label required">{{ __('Stock') }}</label>
                                            <input type="number" class="form-control" name="nfc_card_available_stocks"
                                                placeholder="{{ __('Enter stock availability') }}" value="1" required
                                                min="0" max="9999999999" step="1">
                                        </div>
                                    </div>

                                    {{-- Description Section --}}
                                    <div class="col-12">
                                        <div>
                                            <label class="form-label required">{{ __('Description') }}</label>
                                            <textarea class="form-control" name="nfc_card_description" rows="5"
                                                placeholder="{{ __('Provide a descriptive introduction to help customers evaluate the nfc card...') }}" required
                                                minlength="5" maxlength="4999"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Polished Single Button Footer Layout --}}
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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

        <!-- Footer -->
        @include('admin.includes.footer')
    </div>
@endsection
