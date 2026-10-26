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
                                <a href="#">{{ __('Update NFC Card') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __('Update NFC Card') }}</span>
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Update NFC card images, pricing, specifications, and stock availability.') }}
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
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
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
                        <div class="card border-0 shadow-sm">
                            <form id="designForm" action="{{ route('admin.update.design') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                {{-- Hidden inputs to maintain ID and current form context --}}
                                <input type="hidden" value="{{ $design->nfc_card_id }}" name="nfc_card_id"
                                    id="nfc_card_id">
                                <input type="hidden" name="form_action" id="form_action" value="edit">

                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Front Image (Edit Only) --}}
                                        <div class="col-md-12" data-section="edit">
                                            <div>
                                                <label for="nfc_card_front_image"
                                                    class="form-label">{{ __('Front Image') }}</label>
                                                <input type="file" name="nfc_card_front_image" id="nfc_card_front_image"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        {{-- Back Image (Edit Only) --}}
                                        <div class="col-md-12" data-section="edit">
                                            <div>
                                                <label for="nfc_card_back_image"
                                                    class="form-label">{{ __('Back Image') }}</label>
                                                <input type="file" name="nfc_card_back_image" id="nfc_card_back_image"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        {{-- Name (Edit Only) --}}
                                        <div class="col-md-12" data-section="edit">
                                            <div>
                                                <label for="nfc_card_name"
                                                    class="form-label required">{{ __('Name') }}</label>
                                                <input type="text" name="nfc_card_name" id="nfc_card_name"
                                                    class="form-control" minlength="3" maxlength="999"
                                                    value="{{ $design->nfc_card_name }}"
                                                    placeholder="{{ __('E.g., Wooden NFC Card') }}" required>
                                            </div>
                                        </div>

                                        {{-- Price (Edit Only) --}}
                                        <div class="col-md-12" data-section="edit">
                                            <div>
                                                <label for="nfc_card_price"
                                                    class="form-label required">{{ __('Price') }}</label>
                                                <input type="number" min="0" max="9999999999" step="0.01"
                                                    name="nfc_card_price" id="nfc_card_price" class="form-control"
                                                    value="{{ $design->nfc_card_price }}"
                                                    placeholder="{{ __('E.g., 50.00') }}" required>
                                            </div>
                                        </div>

                                        {{-- Available stocks (Add Stock Only) --}}
                                        <div class="col-md-12" data-section="stock">
                                            <div>
                                                <label for="nfc_card_available_stocks"
                                                    class="form-label required">{{ __('Available Stock') }}</label>
                                                <input type="number" min="0" max="9999999999" step="1"
                                                    value="{{ $design->available_stocks }}"
                                                    name="nfc_card_available_stocks" id="nfc_card_available_stocks"
                                                    class="form-control" placeholder="{{ __('No. of stock available') }}"
                                                    required>
                                            </div>
                                        </div>

                                        {{-- Description (Edit Only) --}}
                                        <div class="col-12" data-section="edit">
                                            <div>
                                                <label for="nfc_card_description"
                                                    class="form-label required">{{ __('Description') }}</label>
                                                <textarea name="nfc_card_description" id="nfc_card_description" class="form-control" rows="5" minlength="5"
                                                    maxlength="4999"
                                                    placeholder="{{ __('Provide a descriptive introduction to help customers evaluate the nfc card...') }}" required>{{ $design->nfc_card_description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Polished Single Button Footer Layout --}}
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
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

        <!-- Footer -->
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom Scripts --}}
@section('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            function handleFormView() {
                const hash = window.location.hash;
                const editSections = document.querySelectorAll('[data-section="edit"]');
                const stockSections = document.querySelectorAll('[data-section="stock"]');
                const cardTitle = document.querySelector('.card-title-dynamic');
                const formActionInput = document.getElementById('form_action');

                if (hash === '#addstock') {
                    // Show Stock fields, Hide Edit fields
                    editSections.forEach(el => el.classList.add('d-none'));
                    stockSections.forEach(el => el.classList.remove('d-none'));

                    if (cardTitle) cardTitle.textContent = "{{ __('Add Stock') }}";
                    if (formActionInput) formActionInput.value = 'addstock';
                } else {
                    // Default view: Show Edit fields, Hide Stock fields
                    editSections.forEach(el => el.classList.remove('d-none'));
                    stockSections.forEach(el => el.classList.add('d-none'));

                    if (cardTitle) cardTitle.textContent = "{{ __('Edit Design') }}";
                    if (formActionInput) formActionInput.value = 'edit';
                }
            }

            // Listen for back/forward navigation or manual hash changes
            window.addEventListener('hashchange', handleFormView);

            // Initial setup on page load
            handleFormView();
        });
    </script>
@endsection
