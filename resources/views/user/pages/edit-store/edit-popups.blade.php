@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-popups.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Navigation -->
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-muted d-inline-flex align-items-center gap-1">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.stores') }}" class="text-muted">{{ __('Stores') }}</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Popups') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Promotional & Announcement Popups') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Enable promotional newsletters or custom announcement popups with interactive call-to-action elements.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'popups',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Main Popups Form --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store.popups') }}" method="POST"
                                enctype="multipart/form-data" id="popupsForm">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $business_card->card_id }}">

                                <div class="card-body p-4">
                                    {{-- Feature 1: Newsletter Popup Toggle Card --}}
                                    <div class="settings-switch-card">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="settings-switch-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-mail" width="22"
                                                        height="22" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                        <path d="M3 7l9 6l9 -6" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h4 class="fw-bold mb-0 text-reset">
                                                        {{ __('Newsletter Subscription Popup') }}</h4>
                                                    <p class="text-muted small mb-0">
                                                        {{ __('Capture customer email subscribers directly when visitors land on your storefront.') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="newsletterSwitch"
                                                    onchange="toggleNewsletter(this)"
                                                    {{ $business_card->is_newsletter_pop_active ? 'checked' : '' }}>
                                                <input type="hidden" name="is_newsletter_pop_active" id="newsletterInput"
                                                    value="{{ $business_card->is_newsletter_pop_active ? '1' : '0' }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Feature 2: Information / Announcement Modal Card --}}
                                    <div class="settings-switch-card">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="settings-switch-icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-speakerphone" width="22"
                                                        height="22" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M18 8a3 3 0 0 1 0 6" />
                                                        <path d="M10 8v11a1 1 0 0 1 -1 1h-1a1 1 0 0 1 -1 -1v-5" />
                                                        <path
                                                            d="M12 8h0l4.524 -3.77a.9 .9 0 0 1 1.476 .692v12.156a.9 .9 0 0 1 -1.476 .692l-4.524 -3.77h-8a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h8" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h4 class="fw-bold mb-0 text-reset">
                                                        {{ __('Announcement & Offer Popup') }}</h4>
                                                    <p class="text-muted small mb-0">
                                                        {{ __('Display custom promotions, discounts, holiday greetings, or alert banners to store visitors.') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="infoPopupSwitch"
                                                    onchange="toggleInfoPopup(this)"
                                                    {{ $business_card->is_info_pop_active ? 'checked' : '' }}>
                                                <input type="hidden" name="is_info_pop_active" id="infoPopupInput"
                                                    value="{{ $business_card->is_info_pop_active ? '1' : '0' }}">
                                            </div>
                                        </div>

                                        {{-- Collapsible Announcement Configuration Panel --}}
                                        <div class="popup-config-panel {{ $business_card->is_info_pop_active ? '' : 'd-none' }}"
                                            id="info-popup-settings">
                                            <div
                                                class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom border-translucent">
                                                <div>
                                                    <h4 class="card-title fw-bold mb-0 text-reset">
                                                        {{ __('Announcement Modal Details') }}</h4>
                                                    <p class="text-muted small mb-0">
                                                        {{ __('Customize the banner, message, and call-to-action button.') }}
                                                    </p>
                                                </div>

                                                {{-- Confetti Switch --}}
                                                <div
                                                    class="d-inline-flex align-items-center gap-2 p-2 bg-surface rounded border">
                                                    <span
                                                        class="small fw-semibold text-muted d-inline-flex align-items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-sparkles text-warning"
                                                            width="18" height="18" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M16 18a2 2 0 0 1 2 2a2 2 0 0 1 2 -2a2 2 0 0 1 -2 -2a2 2 0 0 1 -2 2zm0 -12a2 2 0 0 1 2 2a2 2 0 0 1 2 -2a2 2 0 0 1 -2 -2a2 2 0 0 1 -2 2zm-7 12a6 6 0 0 1 6 -6a6 6 0 0 1 -6 -6a6 6 0 0 1 -6 6a6 6 0 0 1 6 6z" />
                                                        </svg>
                                                        {{ __('Celebration Confetti') }}
                                                    </span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="confetti_effect" id="confetti_effect" value="1"
                                                            {{ isset($informationPopUpDetails) && $informationPopUpDetails->confetti_effect == 1 ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-4">
                                                {{-- Left Column: Form Fields --}}
                                                <div class="col-lg-7">
                                                    <div class="row g-3">
                                                        {{-- Banner Image Uploader --}}
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label required">{{ __('Popup Banner Image') }}</label>
                                                            <div class="banner-dropzone-box d-flex align-items-center justify-content-between"
                                                                onclick="$('#info_pop_image').trigger('click');">
                                                                <div
                                                                    class="d-flex align-items-center gap-2 text-truncate me-2">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-photo text-muted"
                                                                        width="20" height="20" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M15 8h.01" />
                                                                        <path
                                                                            d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" />
                                                                        <path
                                                                            d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                                                                        <path
                                                                            d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" />
                                                                    </svg>
                                                                    <span class="small text-muted text-truncate"
                                                                        id="imageSelectedName">
                                                                        {{ isset($informationPopUpDetails->info_pop_image) ? basename($informationPopUpDetails->info_pop_image) : __('Choose banner image...') }}
                                                                    </span>
                                                                </div>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-primary flex-shrink-0">{{ __('Browse') }}</button>
                                                            </div>
                                                            <input type="file" class="d-none" name="info_pop_image"
                                                                id="info_pop_image" accept=".jpeg,.jpg,.png,.gif">
                                                            <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 600 px']) }}</small>
                                                        </div>

                                                        {{-- Popup Title --}}
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label required">{{ __('Popup Title') }}</label>
                                                            <div class="input-icon">
                                                                <span class="input-icon-addon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-heading"
                                                                        width="18" height="18" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M7 12h10" />
                                                                        <path d="M7 5v14" />
                                                                        <path d="M17 5v14" />
                                                                    </svg>
                                                                </span>
                                                                <input type="text" class="form-control"
                                                                    name="info_pop_title" id="info_pop_title"
                                                                    placeholder="{{ __('e.g., Special 20% Off Weekend Offer!') }}"
                                                                    value="{{ $informationPopUpDetails->info_pop_title ?? '' }}">
                                                            </div>
                                                        </div>

                                                        {{-- Popup Description --}}
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label required">{{ __('Description Note') }}</label>
                                                            <textarea class="form-control" name="info_pop_desc" id="info_pop_desc" rows="3"
                                                                placeholder="{{ __('Enter promotional message or offer details') }}">{{ $informationPopUpDetails->info_pop_desc ?? '' }}</textarea>
                                                        </div>

                                                        {{-- Button Text --}}
                                                        <div class="col-md-6">
                                                            <label
                                                                class="form-label required">{{ __('Button Label') }}</label>
                                                            <div class="input-icon">
                                                                <span class="input-icon-addon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-cursor-text"
                                                                        width="18" height="18" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M10 12h4" />
                                                                        <path d="M9 4a3 3 0 0 1 3 3v10a3 3 0 0 1 -3 3" />
                                                                        <path d="M15 4a3 3 0 0 0 -3 3v10a3 3 0 0 0 3 3" />
                                                                    </svg>
                                                                </span>
                                                                <input type="text" class="form-control"
                                                                    name="info_pop_button_text" id="info_pop_button_text"
                                                                    placeholder="{{ __('e.g., Claim Deal Now') }}"
                                                                    value="{{ $informationPopUpDetails->info_pop_button_text ?? '' }}">
                                                            </div>
                                                        </div>

                                                        {{-- Button URL --}}
                                                        <div class="col-md-6">
                                                            <label
                                                                class="form-label required">{{ __('Button URL') }}</label>
                                                            <div class="input-icon">
                                                                <span class="input-icon-addon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-link"
                                                                        width="18" height="18" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M9 15l6 -6" />
                                                                        <path
                                                                            d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" />
                                                                        <path
                                                                            d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" />
                                                                    </svg>
                                                                </span>
                                                                <input type="url" class="form-control"
                                                                    name="info_pop_button_url" id="info_pop_button_url"
                                                                    placeholder="{{ __('https://yourstore.com/offer') }}"
                                                                    value="{{ $informationPopUpDetails->info_pop_button_url ?? '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Right Column: Live Modal Popup Simulator --}}
                                                <div class="col-lg-5">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span
                                                            class="small fw-bold text-muted text-uppercase tracking-wider">{{ __('Live Popup Preview') }}</span>
                                                        <span class="badge bg-primary-lt">{{ __('Interactive') }}</span>
                                                    </div>

                                                    <div class="popup-preview-card">
                                                        <img src="{{ !empty($informationPopUpDetails->info_pop_image) ? asset($informationPopUpDetails->info_pop_image) : 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22300%22%20height%3D%22140%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20300%20140%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_18%20text%20%7B%20fill%3A%23999%3Bfont-weight%3Anormal%3Bfont-family%3Asans-serif%3Bfont-size%3A12pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_18%22%3E%3Crect%20width%3D%22300%22%20height%3D%22140%22%20fill%3D%22%23f2f4f8%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%2295%22%20y%3D%2275%22%3EBanner%20Image%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E' }}"
                                                            id="livePopupImgPreview" class="popup-preview-banner"
                                                            alt="Popup Preview"
                                                            onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22300%22%20height%3D%22140%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20300%20140%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22300%22%20height%3D%22140%22%20fill%3D%22%23f2f4f8%22%3E%3C%2Frect%3E%3C%2Fsvg%3E'">

                                                        <div class="popup-preview-body">
                                                            <h4 class="fw-bold mb-2 text-reset"
                                                                id="livePopupTitlePreview">
                                                                {{ $informationPopUpDetails->info_pop_title ?? __('Special Announcement Title') }}
                                                            </h4>
                                                            <p class="text-muted small mb-3" id="livePopupDescPreview">
                                                                {{ $informationPopUpDetails->info_pop_desc ?? __('Your promotional discount message or announcement text will appear here.') }}
                                                            </p>
                                                            <button type="button" class="btn btn-primary w-100 shadow-sm"
                                                                id="livePopupButtonPreview">
                                                                {{ $informationPopUpDetails->info_pop_button_text ?? __('Shop Now') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Symmetrical Action Footer -->
                                <div class="card-footer bg-transparent border-top p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('user.stores') }}"
                                            class="btn btn-danger d-inline-flex align-items-center gap-1">
                                            {{ __('Cancel') }}
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4l-4 0l0 -4" />
                                            </svg>
                                            {{ __('Save') }}
                                        </button>
                                    </div>
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

{{-- Optimized Custom JS --}}
@push('custom-js')
    <script>
        // Toggle Newsletter Switch
        function toggleNewsletter(el) {
            document.getElementById('newsletterInput').value = el.checked ? '1' : '0';
        }

        // Toggle Announcement Popup Switch
        function toggleInfoPopup(el) {
            const panel = document.getElementById('info-popup-settings');
            const hiddenInput = document.getElementById('infoPopupInput');
            const requiredFields = ['info_pop_title', 'info_pop_desc', 'info_pop_button_text', 'info_pop_button_url'];

            if (el.checked) {
                panel.classList.remove('d-none');
                hiddenInput.value = '1';
                requiredFields.forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.setAttribute('required', 'required');
                });
            } else {
                panel.classList.add('d-none');
                hiddenInput.value = '0';
                requiredFields.forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.removeAttribute('required');
                });
            }
        }

        // Lightweight Event-Driven Initializer
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const titleInput = document.getElementById('info_pop_title');
            const descInput = document.getElementById('info_pop_desc');
            const btnTextInput = document.getElementById('info_pop_button_text');
            const imageInput = document.getElementById('info_pop_image');

            const titlePreview = document.getElementById('livePopupTitlePreview');
            const descPreview = document.getElementById('livePopupDescPreview');
            const btnPreview = document.getElementById('livePopupButtonPreview');
            const imgPreview = document.getElementById('livePopupImgPreview');
            const fileNameDisplay = document.getElementById('imageSelectedName');

            // Synchronize text in real time
            if (titleInput && titlePreview) {
                titleInput.addEventListener('input', function() {
                    titlePreview.textContent = this.value.trim() ||
                        "{{ __('Special Announcement Title') }}";
                });
            }

            if (descInput && descPreview) {
                descInput.addEventListener('input', function() {
                    descPreview.textContent = this.value.trim() ||
                        "{{ __('Your promotional discount message or announcement text will appear here.') }}";
                });
            }

            if (btnTextInput && btnPreview) {
                btnTextInput.addEventListener('input', function() {
                    btnPreview.textContent = this.value.trim() || "{{ __('Shop Now') }}";
                });
            }

            // Synchronize uploaded image in real time without network lock
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const files = e.target.files;
                    if (files && files.length > 0) {
                        if (fileNameDisplay) fileNameDisplay.textContent = files[0].name;
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            if (imgPreview) imgPreview.src = event.target.result;
                        };
                        reader.readAsDataURL(files[0]);
                    }
                });
            }
        });
    </script>
@endpush
