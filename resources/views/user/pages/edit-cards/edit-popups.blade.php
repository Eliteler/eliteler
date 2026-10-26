@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Popups CSS --}}
    <link rel="stylesheet" href="{{ asset('css/popups.css') }}" />

    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const isInfoPopActive = {{ $business_card->is_info_pop_active ? 'true' : 'false' }};
            const isConfettiEffectActive =
                {{ isset($informationPopUpDetails) && $informationPopUpDetails->confetti_effect == 1 ? 'true' : 'false' }};

            if (isInfoPopActive) {
                // Show the popup
                $("#info-popup").removeClass("d-none");

                // Set value to information popup
                $('input[name="is_info_pop_active"]').val(1);

                // Set value to information details
                $('#confetti_effect').prop('checked', isConfettiEffectActive);
                $('#info_pop_title').val("{{ $informationPopUpDetails->info_pop_title ?? '' }}");
                $('#info_pop_desc').val("{{ $informationPopUpDetails->info_pop_desc ?? '' }}");
                $('#info_pop_button_text').val("{{ $informationPopUpDetails->info_pop_button_text ?? '' }}");
                $('#info_pop_button_url').val("{{ $informationPopUpDetails->info_pop_button_url ?? '' }}");

                // Add required attribute to input fields
                $('#info_pop_image').attr('required', 'required');
                $('#info_pop_title').attr('required', 'required');
                $('#info_pop_desc').attr('required', 'required');
                $('#info_pop_button_url').attr('required', 'required');
                $('#info_pop_button_text').attr('required', 'required');
            }
        });
    </script>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Popup Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Popup Settings') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Toggle promotional newsletter blocks, establish personalized popups, and configure automatic call-to-actions.') }}
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

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar Navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'popups',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.popups', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body p-3 p-lg-3">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">{{ __('Popups') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Configure automated promotional modals and lead capture overlays for your card visitors.') }}
                                        </p>
                                    </div>

                                    <div class="row g-4">
                                        {{-- Newsletter Popup Option --}}
                                        <div class="col-12 col-xl-12">
                                            <div class="settings-switch-card h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <h4 class="fw-bold text-dark m-0">
                                                            {{ __('Enable Newsletter Popup') }}</h4>
                                                        <p class="text-muted small mb-0 mt-1">
                                                            {{ __('Prompt visitors to subscribe to your newsletter with email capture.') }}
                                                        </p>
                                                    </div>
                                                    <label class="form-check form-switch m-0 ms-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            onchange="newsletterPopup()" name="is_newsletter_pop_active"
                                                            {{ $business_card->is_newsletter_pop_active ? 'checked' : '' }}>
                                                        <input type="hidden" name="is_newsletter_pop_active"
                                                            value="{{ $business_card->is_newsletter_pop_active ? '1' : '0' }}">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Information Popup Option --}}
                                        <div class="col-12 col-xl-12">
                                            <div class="settings-switch-card h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <h4 class="fw-bold text-dark m-0">
                                                            {{ __('Enable Information Popup') }}</h4>
                                                        <p class="text-muted small mb-0 mt-1">
                                                            {{ __('Display special announcements, offers, or custom modal promotions.') }}
                                                        </p>
                                                    </div>
                                                    <label class="form-check form-switch m-0 ms-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            onchange="informationPopup()" name="is_info_pop_active"
                                                            {{ $business_card->is_info_pop_active ? 'checked' : '' }}>
                                                        <input type="hidden" name="is_info_pop_active" value="0">
                                                    </label>
                                                </div>

                                                {{-- Dynamic Information Form --}}
                                                <div class="row g-3 mt-2 d-none" id="info-popup">
                                                    <div class="col-12 border-top pt-3">
                                                        <h4 class="fw-bold text-dark mb-0">
                                                            {{ __('Information Popup Details') }}
                                                        </h4>
                                                    </div>

                                                    {{-- Confetti Celebration Parameter --}}
                                                    <div class="col-12">
                                                        <div
                                                            class="form-check form-switch p-0 d-flex align-items-center gap-2">
                                                            <input type="checkbox" class="form-check-input ms-0"
                                                                name="confetti_effect" id="confetti_effect" value="1">
                                                            <label class="form-check-label text-secondary small fw-medium"
                                                                for="confetti_effect">{{ __('Activate confetti celebration effect on popup launch') }}</label>
                                                        </div>
                                                    </div>

                                                    {{-- Image Input --}}
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label required"
                                                            for="info_pop_image">{{ __('Image') }}</label>
                                                        <input type="file" class="form-control" name="info_pop_image"
                                                            id="info_pop_image" accept="image/*">
                                                        <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 600 px']) }}</small>
                                                    </div>

                                                    {{-- Title Input --}}
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label required"
                                                            for="info_pop_title">{{ __('Title') }}</label>
                                                        <input type="text" class="form-control" name="info_pop_title"
                                                            id="info_pop_title"
                                                            placeholder="{{ __('Enter popup title') }}">
                                                    </div>

                                                    {{-- Button Text Input --}}
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label required"
                                                            for="info_pop_button_text">{{ __('Button Text') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="info_pop_button_text" id="info_pop_button_text"
                                                            placeholder="{{ __('Enter button text') }}">
                                                    </div>

                                                    {{-- Button URL Input --}}
                                                    <div class="col-12 col-md-6 col-xl-3">
                                                        <label class="form-label required"
                                                            for="info_pop_button_url">{{ __('Button URL') }}</label>
                                                        <input type="url" class="form-control"
                                                            name="info_pop_button_url" id="info_pop_button_url"
                                                            placeholder="{{ __('Enter website URL') }}">
                                                    </div>

                                                    {{-- Description Textarea (Column 12) --}}
                                                    <div class="col-12">
                                                        <label class="form-label required"
                                                            for="info_pop_desc">{{ __('Description') }}</label>
                                                        <textarea class="form-control" name="info_pop_desc" id="info_pop_desc" rows="3"
                                                            placeholder="{{ __('Enter description') }}"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-between py-3">
                                    <a href="{{ route('user.cards') }}"
                                        class="btn btn-danger text-decoration-none fw-semibold">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
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

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script>
        "use strict";

        function newsletterPopup() {
            var newsletterPopup = $('input[name="is_newsletter_pop_active"]:checked').length;

            if (newsletterPopup == 0) {
                $('input[name="is_newsletter_pop_active"]').val(0);
            } else {
                $('input[name="is_newsletter_pop_active"]').val(1);
            }
        }

        function informationPopup() {
            var infoPopup = $('input[name="is_info_pop_active"]:checked').length;

            if (infoPopup == 0) {
                $("#info-popup").addClass("d-none");
                $('input[name="is_info_pop_active"]').val(0);

                $('#info_pop_image').removeAttr('required');
                $('#info_pop_title').removeAttr('required');
                $('#info_pop_desc').removeAttr('required');
                $('#info_pop_button_url').removeAttr('required');
                $('#info_pop_button_text').removeAttr('required');
            } else {
                $("#info-popup").removeClass("d-none");
                $('input[name="is_info_pop_active"]').val(1);

                $('#info_pop_image').attr('required', 'required');
                $('#info_pop_title').attr('required', 'required');
                $('#info_pop_desc').attr('required', 'required');
                $('#info_pop_button_url').attr('required', 'required');
                $('#info_pop_button_text').attr('required', 'required');
            }
        }
    </script>
@endpush
