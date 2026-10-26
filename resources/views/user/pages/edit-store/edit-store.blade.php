@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Edit Store CSS File --}}
    <link href="{{ asset('css/edit-store.css') }}" rel="stylesheet">

    {{-- Cropper CSS --}}
    <link href="{{ asset('css/cropper.min.css') }}" rel="stylesheet">
@endsection

@php
    $defaultImage = '';
    foreach ($themes as $theme) {
        if ($theme->theme_id == $business_card->theme_id) {
            $defaultImage = asset('img/vCards/' . $theme->theme_thumbnail);
            break;
        }
    }

    // Parse existing cover banners safely
    $existingBanners = [];
    if (!empty($business_card->cover)) {
        $decoded = json_decode($business_card->cover, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $existingBanners = $decoded;
        } else {
            $existingBanners = explode(',', $business_card->cover);
        }
    }
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumbs -->
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
                                    {{ __('Update Store') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Update Store') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Customize your digital store theme, logo, contact configurations, and transactional parameters.') }}
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

                <!-- Unified Store Edit Layout -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'basic'])
                                </div>
                            </div>
                        </div>

                        {{-- Right Main Edit Form --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store', Request::segment(4)) }}" method="POST"
                                enctype="multipart/form-data" id="storeUpdateForm">
                                @csrf
                                <input type="hidden" name="theme_id" id="choosedTheme"
                                    value="{{ $business_card->theme_id }}">
                                <input type="hidden" name="banner" id="banner_payload"
                                    value="{{ $business_card->cover }}">
                                <input type="hidden" name="logo" id="logo_payload"
                                    value="{{ $business_card->profile ?? $business_card->logo }}">

                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        {{-- Left Sub-Column: Active Theme Preview --}}
                                        <div class="col-lg-5">
                                            <div class="store-section-card sticky-top" style="top: 1.5rem; z-index: 10;">
                                                {{-- Section Header --}}
                                                <div class="store-section-header">
                                                    <span class="store-section-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-palette" width="20"
                                                            height="20" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M12 21a9 9 0 0 1 0 -18c4.97 0 9 3.582 9 8c0 1.06 -.474 2.078 -1.318 2.828c-.844 .75 -1.989 1.172 -3.182 1.172h-2.5a2 2 0 0 0 -1 3.75a1.3 1.3 0 0 1 -1 2.25" />
                                                            <path d="M8.5 10.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                            <path d="M12.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                            <path d="M16.5 10.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <h4 class="card-title mb-0">{{ __('Storefront Theme') }}</h4>
                                                        <span
                                                            class="small text-muted">{{ __('Current active layout') }}</span>
                                                    </div>
                                                </div>

                                                {{-- Section Body --}}
                                                <div class="card-body p-3">
                                                    <div class="theme-preview-card">
                                                        {{-- Dynamic Theme Thumbnail --}}
                                                        <img src="{{ $defaultImage }}" id="currentThemePreview"
                                                            class="theme-image-display" alt="{{ __('Selected Theme') }}">

                                                        {{-- Modal Trigger Button --}}
                                                        <button type="button"
                                                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2"
                                                            data-bs-toggle="modal" data-bs-target="#themeModal">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon icon-tabler icon-tabler-layout-grid"
                                                                width="18" height="18" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                                                <path
                                                                    d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                                                <path
                                                                    d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                                                <path
                                                                    d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                                            </svg>
                                                            {{ __('Change Theme') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Right Sub-Column: Editable Fields --}}
                                        <div class="col-lg-7">
                                            <!-- Section 1: Store Identity & Localization -->
                                            <div class="store-section-card">
                                                <div class="store-section-header">
                                                    <span class="store-section-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-building-store"
                                                            width="20" height="20" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 21l18 0" />
                                                            <path
                                                                d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
                                                            <path d="M5 21l0 -10.15" />
                                                            <path d="M19 21l0 -10.15" />
                                                            <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <h4 class="card-title mb-0">
                                                            {{ __('Store Identity & Localization') }}</h4>
                                                        <span
                                                            class="small text-muted">{{ __('Basic info, storefront link, and currency') }}</span>
                                                    </div>
                                                </div>

                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        {{-- Store Name --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label required">
                                                                {{ __('Store Name') }}
                                                            </label>

                                                            <div class="input-icon">
                                                                <span class="input-icon-addon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-tag"
                                                                        width="18" height="18" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path
                                                                            d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                                        <path
                                                                            d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                                                    </svg>
                                                                </span>

                                                                <input type="text" class="form-control" name="title"
                                                                    id="{{ \Illuminate\Support\Str::contains($business_card->title, 'Duplicate') ? 'title' : 'default-title' }}"
                                                                    value="{{ old('title', $business_card->title) }}"
                                                                    placeholder="{{ __('Enter store name') }}" required>
                                                            </div>
                                                        </div>

                                                        {{-- Store Greeting --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">
                                                                {{ __('Store Greeting / Tagline') }}
                                                                <span class="text-muted small">({{ __('Optional') }})</span>
                                                            </label>

                                                            <div class="input-icon">
                                                                <span class="input-icon-addon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon icon-tabler icon-tabler-message-2"
                                                                        width="18" height="18" viewBox="0 0 24 24"
                                                                        stroke-width="2" stroke="currentColor"
                                                                        fill="none" stroke-linecap="round"
                                                                        stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z"
                                                                            fill="none" />
                                                                        <path d="M8 9h8" />
                                                                        <path d="M8 13h6" />
                                                                        <path
                                                                            d="M9 18h-3a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-3l-3 3l-3 -3z" />
                                                                    </svg>
                                                                </span>

                                                                <input type="text" class="form-control"
                                                                    name="subtitle"
                                                                    value="{{ old('subtitle', $business_card->sub_title) }}"
                                                                    placeholder="{{ __('Enter store greeting') }}">
                                                            </div>
                                                        </div>

                                                        {{-- Title 2 (another language) --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Title in another language (Optional)') }}</label>
                                                            <input type="text" class="form-control" name="title2"
                                                                placeholder="{{ __('Secondary Title') }}"
                                                                value="{{ old('title2', $business_card->title2) }}">
                                                        </div>

                                                        {{-- Subtitle 2 (another language) --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Subtitle in another language (Optional)') }}</label>
                                                            <input type="text" class="form-control" name="subtitle2"
                                                                placeholder="{{ __('Secondary Sub Title') }}"
                                                                value="{{ old('subtitle2', $business_card->subtitle2) }}">
                                                        </div>

                                                        {{-- Copyright --}}
                                                        <div class="col-12">
                                                            <label class="form-label">{{ __('Copyright (Optional)') }}</label>
                                                            <input type="text" class="form-control" name="copyright" id="store-copyright"
                                                                value="{{ old('copyright', $business_card->copyright) }}"
                                                                placeholder="{{ parse_url(config('app.url'), PHP_URL_HOST) }}">
                                                            <small class="form-hint">{{ __('Leave blank to use site domain') }}</small>
                                                        </div>

                                                        {{-- Personalized Link --}}
                                                        <div class="col-12">
                                                            <label class="form-label required" for="plink">
                                                                {{ __('Personalized Link') }}
                                                            </label>

                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    {{ url('/') }}/
                                                                </span>

                                                                <input type="text" class="form-control font-monospace"
                                                                    name="link" id="plink"
                                                                    placeholder="{{ __('my-store-link') }}"
                                                                    value="{{ old('link', $business_card->card_url) }}"
                                                                    autocomplete="off" minlength="3" readonly
                                                                    @if (!$plan_details->personalized_link) data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="{{ __('Personalized Link is not available in your subscribed plan.') }}" @endif
                                                                    required>

                                                                <!-- Status Icon -->
                                                                <span class="input-group-text" id="status">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="icon text-muted" width="20"
                                                                        height="20" viewBox="0 0 24 24"
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

                                                                <!-- Edit Pencil Icon Button -->
                                                                @if ($plan_details->personalized_link)
                                                                    <button type="button"
                                                                        class="btn btn-primary btn-icon" id="editPlinkBtn"
                                                                        title="{{ __('Edit Link') }}">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            class="icon" width="20" height="20"
                                                                            viewBox="0 0 24 24" stroke-width="2"
                                                                            stroke="currentColor" fill="none"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round">
                                                                            <path stroke="none" d="M0 0h24v24H0z"
                                                                                fill="none" />
                                                                            <path
                                                                                d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                                            <path d="M13.5 6.5l4 4" />
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>

                                                            <small class="form-hint">
                                                                {{ __('Public Link: ') }}
                                                                <a href="{{ url($business_card->card_url) }}"
                                                                    target="_blank"
                                                                    class="text-primary text-decoration-none">
                                                                    {{ url($business_card->card_url) }}
                                                                </a>
                                                            </small>
                                                        </div>

                                                        {{-- Language --}}
                                                        <div
                                                            class="col-md-6 {{ is_array(config('app.languages')) && count(config('app.languages')) <= 1 ? 'd-none' : '' }}">
                                                            <label class="form-label required"
                                                                for="card_lang">{{ __('Default Language') }}</label>
                                                            <select name="card_lang" id="card_lang"
                                                                class="form-control card_lang" required>
                                                                @foreach (config('app.languages') as $langLocale => $langName)
                                                                    <option value="{{ $langLocale }}"
                                                                        {{ old('card_lang', strtolower($business_card->card_lang)) == $langLocale ? 'selected' : '' }}>
                                                                        {{ $langName }}
                                                                        ({{ strtoupper($langLocale) }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        {{-- Currency --}}
                                                        <div
                                                            class="col-md-6 {{ $currencies->count() <= 1 ? 'd-none' : '' }}">
                                                            <label class="form-label required"
                                                                for="currency">{{ __('Store Currency') }}</label>
                                                            <select name="currency" id="currency"
                                                                class="form-control currency" required>
                                                                @foreach ($currencies as $currency)
                                                                    <option value="{{ $currency->iso_code }}"
                                                                        {{ old('currency', $store_details->currency) == $currency->iso_code || old('currency', $store_details->currency) == $currency->symbol ? 'selected' : '' }}>
                                                                        {{ $currency->name }} ({{ $currency->symbol }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 2: Branding & Media -->
                                            <div class="store-section-card">
                                                <div class="store-section-header">
                                                    <span class="store-section-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-photo" width="20"
                                                            height="20" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M15 8h.01" />
                                                            <path
                                                                d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" />
                                                            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                                                            <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" />
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <h4 class="card-title mb-0">{{ __('Branding & Media') }}</h4>
                                                        <span
                                                            class="small text-muted">{{ __('Update your logo and promotional banners') }}</span>
                                                    </div>
                                                </div>

                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        {{-- Logo Upload & Existing Preview --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Store Logo') }}</label>
                                                            <div class="upload-dropzone-box"
                                                                onclick="$('#logoInput').trigger('click');">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-cloud-upload text-muted mb-2"
                                                                    width="32" height="32" viewBox="0 0 24 24"
                                                                    stroke-width="1.5" stroke="currentColor"
                                                                    fill="none" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" />
                                                                    <path d="M9 15l3 -3l3 3" />
                                                                    <path d="M12 12l0 9" />
                                                                </svg>
                                                                <div class="fw-semibold text-reset">
                                                                    {{ __('Change Logo Image') }}</div>
                                                                <small
                                                                    class="text-muted d-block mt-1">{{ __('Square ratio (1:1) recommended') }}</small>
                                                            </div>
                                                            <input type="file" class="d-none" id="logoInput"
                                                                accept=".jpeg,.jpg,.png,.gif,.svg">

                                                            {{-- Existing / New Logo Preview Container --}}
                                                            <div id="logoPreviewContainer"
                                                                class="media-preview-badge-container {{ empty($business_card->profile ?? $business_card->logo) ? 'd-none' : '' }}">
                                                                @if (!empty($business_card->profile ?? $business_card->logo))
                                                                    <div class="media-preview-item">
                                                                        <img src="{{ asset($business_card->profile ?? $business_card->logo) }}"
                                                                            alt="{{ __('Store Logo') }}">
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Banner Upload & Existing Previews --}}
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Store Banner(s)') }}</label>
                                                            <div class="upload-dropzone-box"
                                                                onclick="$('#coverInput').trigger('click');">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-carousel-horizontal text-muted mb-2"
                                                                    width="32" height="32" viewBox="0 0 24 24"
                                                                    stroke-width="1.5" stroke="currentColor"
                                                                    fill="none" stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M7 5m0 1a1 1 0 0 1 1 -1h8a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-8a1 1 0 0 1 -1 -1z" />
                                                                    <path
                                                                        d="M2 7m0 1a1 1 0 0 1 1 -1h1a1 1 0 0 1 1 1v8a1 1 0 0 1 -1 1h-1a1 1 0 0 1 -1 -1z" />
                                                                    <path
                                                                        d="M19 7m0 1a1 1 0 0 1 1 -1h1a1 1 0 0 1 1 1v8a1 1 0 0 1 -1 1h-1a1 1 0 0 1 -1 -1z" />
                                                                </svg>
                                                                <div class="fw-semibold text-reset">
                                                                    {{ __('Upload / Replace Banner(s)') }}</div>
                                                                <small
                                                                    class="text-muted d-block mt-1">{{ __('16:9 Landscape ratio recommended') }}</small>
                                                            </div>
                                                            <input type="file" class="d-none" id="coverInput"
                                                                accept=".jpeg,.jpg,.png,.gif,.svg" multiple>

                                                            {{-- Existing / New Banner Previews --}}
                                                            <div id="bannerPreviewContainer"
                                                                class="media-preview-badge-container d-flex flex-wrap gap-2 {{ empty($existingBanners) ? 'd-none' : '' }}">
                                                                @foreach ($existingBanners as $bannerImg)
                                                                    @php
                                                                        $cleanPath = trim($bannerImg, " \"'[]");
                                                                    @endphp
                                                                    @if (!empty($cleanPath))
                                                                        <div class="media-preview-item position-relative"
                                                                            data-path="{{ $cleanPath }}"
                                                                            style="width: 100px; height: 100px;">
                                                                            <img src="{{ asset($cleanPath) }}"
                                                                                alt="{{ __('Store Banner') }}"
                                                                                class="w-100 h-100 object-cover rounded">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-danger p-0 rounded-circle position-absolute d-flex align-items-center justify-content-center"
                                                                                style="top: -5px; right: -5px; width: 22px; height: 22px; z-index: 5;"
                                                                                onclick="removeBanner(this, '{{ $cleanPath }}')">
                                                                                &times;
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section 3: WhatsApp Ordering Parameters -->
                                            <div class="store-section-card">
                                                <div class="store-section-header">
                                                    <span class="store-section-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-brand-whatsapp"
                                                            width="20" height="20" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                                            <path
                                                                d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                                                        </svg>
                                                    </span>
                                                    <div>
                                                        <h4 class="card-title mb-0">{{ __('WhatsApp Order Routing') }}
                                                        </h4>
                                                        <span
                                                            class="small text-muted">{{ __('Direct customer checkout orders straight to your WhatsApp account') }}</span>
                                                    </div>
                                                </div>

                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        {{-- Country Code --}}
                                                        <div class="col-md-5">
                                                            <label
                                                                class="form-label required">{{ __('Country Dialing Code') }}</label>
                                                            @include('user.pages.edit-store.include.country-code')
                                                        </div>

                                                        {{-- WhatsApp Number --}}
                                                        <div class="col-md-7">
                                                            <label
                                                                class="form-label required">{{ __('WhatsApp Phone Number') }}</label>
                                                            @include('user.pages.edit-store.include.whatsapp-no')
                                                        </div>

                                                        {{-- WhatsApp Footer Text --}}
                                                        <div class="col-12">
                                                            <label
                                                                class="form-label required">{{ __('Checkout Message Footer Note') }}</label>
                                                            <textarea class="form-control" name="whatsapp_msg" id="whatsapp_msg" data-bs-toggle="autosize" rows="3"
                                                                placeholder="{{ __('Enter thanks note') }}">{{ old('whatsapp_msg', $store_details->whatsapp_msg) }}</textarea>
                                                            <small
                                                                class="form-hint">{{ __('This custom text appears at the bottom of customer WhatsApp order messages.') }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Symmetrical Action Footer -->
                                            <div class="store-section-card p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <a href="{{ route('user.stores') }}"
                                                        class="btn btn-danger d-inline-flex align-items-center gap-1">
                                                        {{ __('Cancel') }}
                                                    </a>

                                                    <button type="submit"
                                                        class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm px-4">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-device-floppy"
                                                            width="18" height="18" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
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
                                        </div>
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

    <!-- Personalized Link Confirmation Modal -->
    <div class="modal modal-bg fade" id="editLinkConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-primary icon-lg" width="24"
                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 9v2m0 4v.01" />
                        <path
                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                    </svg>
                    <h3>{{ __('Are you sure?') }}</h3>
                    <div class="text-muted">
                        {{ __('If you change this personalized link,') }}
                        <b class="text-dark">{{ __('your old link will not redirect to the new one.') }}</b>
                        {{ __('Are you sure you want to proceed?') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-primary w-100" id="confirmUnlockLinkBtn">
                                    {{ __('Confirm') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Choose Theme Modal with Glowing Search & Empty State --}}
    <div class="modal modal-bg fade" id="themeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 py-3">
                    <div>
                        <h5 class="modal-title fs-3 fw-bold">{{ __('Choose Storefront Theme') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ __('Browse and select a theme layout for your WhatsApp storefront.') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>

                <div class="modal-body">
                    {{-- Glowing Search Bar --}}
                    <div class="mb-4">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-search text-muted" width="18" height="18"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                    <path d="M21 21l-6 -6" />
                                </svg>
                            </span>
                            <input type="text" id="themeSearchInput" class="form-control theme-search-input"
                                placeholder="{{ __('Search themes by name or category...') }}" autocomplete="off">
                        </div>
                    </div>

                    {{-- Themes Grid --}}
                    <div class="row g-3" id="themeResultsGrid">
                        @forelse ($themes as $theme)
                            <div class="col-6 col-md-4 col-lg-3 theme-grid-item"
                                data-name="{{ strtolower($theme->theme_name) }}">
                                <div class="theme-choice-card p-2 text-center {{ $theme->theme_id == $business_card->theme_id ? 'selected' : '' }}"
                                    onclick="selectThemeItem('{{ $theme->theme_id }}', '{{ asset('img/vCards/' . $theme->theme_thumbnail) }}', this)">
                                    <img src="{{ asset('img/vCards/' . $theme->theme_thumbnail) }}"
                                        class="img-fluid rounded mb-2" alt="{{ $theme->theme_name }}">
                                    <div class="fw-bold small text-truncate">{{ $theme->theme_name }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="theme-empty-state">
                                    <div class="theme-empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-mood-empty" width="28" height="28"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                            <path d="M9 10l.01 0" />
                                            <path d="M15 10l.01 0" />
                                            <path d="M9 15l6 0" />
                                        </svg>
                                    </div>
                                    <h4 class="fw-bold text-reset mb-1">{{ __('No Themes Available') }}</h4>
                                    <p class="text-muted small mb-0">{{ __('No store themes are configured yet.') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Client-side Search No-Results State --}}
                    <div id="noThemesFound" class="d-none mt-2">
                        <div class="theme-empty-state">
                            <div class="theme-empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-building-store">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 21l18 0" />
                                    <path
                                        d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
                                    <path d="M5 21l0 -10.15" />
                                    <path d="M19 21l0 -10.15" />
                                    <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                                </svg>
                            </div>
                            <h4 class="fw-bold text-reset mb-1">{{ __('No Themes Found') }}</h4>
                            <p class="text-muted small mb-0">
                                {{ __('We could not find any themes matching your search keyword.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-danger ms-auto d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Logo Crop Modal --}}
    <div id="cropLogoModal" class="modal modal-bg fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Crop Store Logo') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="cropper-wrapper w-100"
                        style="max-height: 420px; overflow: hidden; background: var(--tblr-bg-surface-secondary);">
                        <img id="logoCropperImage" style="max-width: 100%; display: block;" alt="Logo crop image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="applyLogoCrop">
                        {{ __('Crop & Apply') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner Crop Modal --}}
    <div id="cropBannerModal" class="modal modal-bg fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Crop Store Banner') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="cropper-wrapper w-100"
                        style="max-height: 460px; overflow: hidden; background: var(--tblr-bg-surface-secondary);">
                        <img id="bannerCropperImage" style="max-width: 100%; display: block;" alt="Banner crop image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="applyBannerCrop">
                        {{ __('Crop & Add') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script src="{{ asset('js/cropper.min.js') }}"></script>

    <script>
        // Sync copyright from store name until the user edits it manually
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.querySelector('#storeUpdateForm input[name="title"]');
            const copyrightInput = document.getElementById('store-copyright');
            if (!titleInput || !copyrightInput) return;

            let copyrightManuallyEdited = copyrightInput.value.trim() !== '';

            copyrightInput.addEventListener('input', function() {
                copyrightManuallyEdited = this.value.trim() !== '';
            });

            ['input', 'keyup'].forEach(function(evt) {
                titleInput.addEventListener(evt, function() {
                    if (!copyrightManuallyEdited) {
                        copyrightInput.value = this.value;
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clean TomSelect init without heavy MutationObservers
            document.querySelectorAll('.card_lang, .currency, .country_code').forEach(function(el) {
                if (!el.tomselect) {
                    new TomSelect(el, {
                        copyClassesToDropdown: false,
                        dropdownClass: 'dropdown-menu ts-dropdown',
                        optionClass: 'dropdown-item',
                        controlInput: '<input>',
                        maxOptions: 50
                    });
                }
            });

            // Theme Search Filter with Glowing Indicator
            const themeSearchInput = document.getElementById('themeSearchInput');
            const noThemesFound = document.getElementById('noThemesFound');
            const themeGridItems = document.querySelectorAll('.theme-grid-item');

            if (themeSearchInput) {
                themeSearchInput.addEventListener('input', function(e) {
                    const query = e.target.value.toLowerCase().trim();
                    let visibleCount = 0;

                    themeGridItems.forEach(function(item) {
                        const themeName = item.getAttribute('data-name') || '';
                        if (themeName.includes(query)) {
                            item.classList.remove('d-none');
                            visibleCount++;
                        } else {
                            item.classList.add('d-none');
                        }
                    });

                    if (visibleCount === 0 && themeGridItems.length > 0) {
                        noThemesFound.classList.remove('d-none');
                    } else {
                        noThemesFound.classList.add('d-none');
                    }
                });
            }

            // Slug Syncing for Duplicated Stores
            const titleEl = document.getElementById('title');
            const plinkEl = document.getElementById('plink');
            if (titleEl && plinkEl) {
                titleEl.addEventListener('input', function() {
                    plinkEl.value = this.value
                        .toLowerCase()
                        .replace(/\s+/g, '-')
                        .replace(/[^\w\-]+/g, '')
                        .replace(/--+/g, '-');
                });
            }
        });

        // Theme Selection Handler
        function selectThemeItem(themeId, thumbnailUrl, element) {
            document.getElementById('choosedTheme').value = themeId;
            document.getElementById('currentThemePreview').setAttribute('src', thumbnailUrl);

            document.querySelectorAll('.theme-choice-card').forEach(function(el) {
                el.classList.remove('selected');
            });

            if (element) {
                element.classList.add('selected');
            }

            const themeModalEl = document.getElementById('themeModal');
            const modalInstance = bootstrap.Modal.getInstance(themeModalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        // Logo Cropping Handlers
        let logoCropper = null;
        const logoModalEl = document.getElementById('cropLogoModal');
        const logoInput = document.getElementById('logoInput');

        logoInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.getElementById('logoCropperImage');
                    img.src = event.target.result;
                    const modal = new bootstrap.Modal(logoModalEl);
                    modal.show();
                };
                reader.readAsDataURL(files[0]);
            }
        });

        logoModalEl.addEventListener('shown.bs.modal', function() {
            logoCropper = new Cropper(document.getElementById('logoCropperImage'), {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
                background: false
            });
        });

        logoModalEl.addEventListener('hidden.bs.modal', function() {
            if (logoCropper) {
                logoCropper.destroy();
                logoCropper = null;
            }
        });

        document.getElementById('applyLogoCrop').addEventListener('click', function() {
            if (!logoCropper) return;
            const btn = this;
            btn.disabled = true;
            btn.innerHTML =
                `<span class="spinner-border spinner-border-sm me-1" role="status"></span> {{ __('Uploading...') }}`;

            const canvas = logoCropper.getCroppedCanvas({
                width: 512,
                height: 512
            });
            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('croppedImage', blob);

                fetch("{{ route('user.vcard.cropped.image') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('logo_payload').value = data.imageUrl;
                        const previewContainer = document.getElementById('logoPreviewContainer');
                        previewContainer.classList.remove('d-none');
                        previewContainer.innerHTML = `
                        <div class="media-preview-item">
                            <img src="${data.imageUrl}" alt="{{ __('Store Logo') }}">
                        </div>
                    `;
                        bootstrap.Modal.getInstance(logoModalEl).hide();
                    })
                    .catch(err => console.error('Logo upload error:', err))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = `{{ __('Crop & Apply') }}`;
                    });
            });
        });

        // Banner Cropping & Management Handlers
        let bannerCropper = null;
        let bannerFiles = [];
        let bannerIndex = 0;
        let uploadedBanners = [];

        // Initialize with existing banner paths if any
        try {
            const rawExisting = document.getElementById('banner_payload').value;
            if (rawExisting) {
                uploadedBanners = JSON.parse(rawExisting);
            }
        } catch (e) {
            uploadedBanners = [];
        }

        const bannerModalEl = document.getElementById('cropBannerModal');
        const coverInput = document.getElementById('coverInput');

        coverInput.addEventListener('change', function(e) {
            bannerFiles = e.target.files;
            if (bannerFiles && bannerFiles.length > 0) {
                bannerIndex = 0;
                loadNextBannerFile();
            }
        });

        function loadNextBannerFile() {
            if (bannerIndex < bannerFiles.length) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.getElementById('bannerCropperImage');
                    img.src = event.target.result;
                    const modal = new bootstrap.Modal(bannerModalEl);
                    modal.show();
                };
                reader.readAsDataURL(bannerFiles[bannerIndex]);
            }
        }

        bannerModalEl.addEventListener('shown.bs.modal', function() {
            bannerCropper = new Cropper(document.getElementById('bannerCropperImage'), {
                aspectRatio: 16 / 9,
                viewMode: 2,
                autoCropArea: 1,
                responsive: true
            });
        });

        bannerModalEl.addEventListener('hidden.bs.modal', function() {
            if (bannerCropper) {
                bannerCropper.destroy();
                bannerCropper = null;
            }
        });

        document.getElementById('applyBannerCrop').addEventListener('click', function() {
            if (!bannerCropper) return;
            const btn = this;
            btn.disabled = true;
            btn.innerHTML =
                `<span class="spinner-border spinner-border-sm me-1" role="status"></span> {{ __('Uploading...') }}`;

            const canvas = bannerCropper.getCroppedCanvas({
                width: 1920,
                height: 1080
            });
            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('croppedImage', blob, `banner_${bannerIndex}.png`);

                fetch("{{ route('user.store.cropped.images') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        uploadedBanners.push(data.imageUrl);
                        document.getElementById('banner_payload').value = JSON.stringify(
                            uploadedBanners);

                        const previewContainer = document.getElementById('bannerPreviewContainer');
                        previewContainer.classList.remove('d-none');
                        previewContainer.insertAdjacentHTML('beforeend', `
                            <div class="media-preview-item position-relative" data-path="${data.imageUrl}" style="width: 100px; height: 100px;">
                                <img src="${data.imageUrl}" alt="{{ __('Store Banner') }}" class="w-100 h-100 object-cover rounded">
                                <button type="button" class="btn btn-sm btn-danger p-0 rounded-circle position-absolute d-flex align-items-center justify-content-center"
                                    style="top: -5px; right: -5px; width: 22px; height: 22px; z-index: 5;"
                                    onclick="removeBanner(this, '${data.imageUrl}')">
                                    &times;
                                </button>
                            </div>
                        `);

                        bootstrap.Modal.getInstance(bannerModalEl).hide();
                        bannerIndex++;
                        if (bannerIndex < bannerFiles.length) {
                            setTimeout(loadNextBannerFile, 350);
                        }
                    })
                    .catch(err => console.error('Banner upload error:', err))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = `{{ __('Crop & Add') }}`;
                    });
            }, 'image/png');
        });

        // Dynamic Banner Removal Handler
        function removeBanner(buttonElement, pathToRemove) {
            const item = buttonElement.closest('.media-preview-item');
            if (item) {
                item.remove();
            }

            const payloadInput = document.getElementById('banner_payload');
            let currentBanners = [];

            try {
                let rawValue = payloadInput.value;
                if (rawValue) {
                    if (rawValue.trim().startsWith('[')) {
                        currentBanners = JSON.parse(rawValue);
                    } else {
                        currentBanners = rawValue.split(',').map(s => s.trim()).filter(Boolean);
                    }
                }
            } catch (e) {
                currentBanners = [];
            }

            // Normalizing all paths inside array to avoid quote escapes
            currentBanners = currentBanners.map(path => path.replace(/[\"\'\[\]]/g, '').trim());

            // Filter out the deleted path
            uploadedBanners = currentBanners.filter(path => {
                let cleanPath = path.replace(/^[\\\/]+/, '');
                let cleanTarget = pathToRemove.replace(/^[\\\/]+/, '');
                return cleanPath !== cleanTarget && path !== pathToRemove;
            });

            payloadInput.value = JSON.stringify(uploadedBanners);

            const previewContainer = document.getElementById('bannerPreviewContainer');
            if (previewContainer && previewContainer.querySelectorAll('.media-preview-item').length === 0) {
                previewContainer.classList.add('d-none');
            }
        }

        // Variable to keep track of current saved link
        var originalCardUrl = "{{ $business_card->card_url }}";

        // Function to check store link availability via AJAX
        function checkLink() {
            var plink = $('#plink').val().trim();

            // If input matches original store URL, mark success immediately
            if (plink === originalCardUrl) {
                $('#status').html(
                    '<span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2l4 -4"/></svg></span>'
                );
                $('#storeUpdateForm button[type="submit"]').prop('disabled', false);
                return;
            }

            if (plink.length > 0) {
                $.ajax({
                    url: "{{ route('user.check.link') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        link: plink
                    },
                }).done(function(res) {
                    if (res.status == 'success') {
                        $('#status').html(
                            '<span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2l4 -4"/></svg></span>'
                        );
                        $('#storeUpdateForm button[type="submit"]').prop('disabled', false);
                    } else {
                        $('#status').html(
                            '<span class="text-danger"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><line x1="10" y1="10" x2="14" y2="14"/><line x1="14" y1="10" x2="10" y2="14"/></svg></span>'
                        );
                        $('#storeUpdateForm button[type="submit"]').prop('disabled', true);
                    }
                });
            } else {
                $('#status').html(
                    '<svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 15l6 -6"/><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/></svg>'
                );
            }
        }

        // Pencil button click trigger
        $('#editPlinkBtn').click(function() {
            if ($('#plink').prop('readonly')) {
                $('#editLinkConfirmModal').modal('show');
            } else {
                $('#plink').focus();
            }
        });

        // Unlock confirmation handler
        $('#confirmUnlockLinkBtn').click(function() {
            $('#editLinkConfirmModal').modal('hide');
            $('#plink').removeAttr('readonly').focus();
            $('#editPlinkBtn').addClass('btn-primary').removeClass('btn-outline-secondary');
        });

        // Real-time debounce check when editing store link
        var storeLinkTimer;
        $('#plink').on('input', function() {
            this.value = this.value.replace(/\s+/g, '-');
            clearTimeout(storeLinkTimer);
            storeLinkTimer = setTimeout(checkLink, 300);
        });

        // Trigger link availability check on title change if input is unlocked
        const storeTitleEl = document.getElementById('title');
        if (storeTitleEl) {
            storeTitleEl.addEventListener('input', function() {
                if (!$('#plink').prop('readonly')) {
                    clearTimeout(storeLinkTimer);
                    storeLinkTimer = setTimeout(checkLink, 300);
                }
            });
        }
    </script>
@endpush
