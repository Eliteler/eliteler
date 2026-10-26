@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Create Store CSS File --}}
    <link href="{{ asset('css/create-store.css') }}" rel="stylesheet">

    {{-- Cropper CSS --}}
    <link href="{{ asset('css/cropper.min.css') }}" rel="stylesheet">
@endsection

@php
    $defaultImage = '';
    foreach ($themes as $value => $theme) {
        if ($value == 0) {
            $defaultImage = asset('img/vCards/' . $theme->theme_thumbnail);
            break;
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
                                    {{ __('Create Store') }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Create New Store') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Design and configure your customized online storefront with dynamic themes, localization, and automated checkout settings.') }}
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
                        <div class="d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 9v4" />
                                <path d="M12 16v.01" />
                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
                            </svg>
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M5 12l5 5l10 -10" />
                            </svg>
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Main Form --}}
                <form action="{{ route('user.save.store') }}" method="POST" enctype="multipart/form-data"
                    id="storeCreateForm">
                    @csrf
                    <input type="hidden" name="theme_id" id="selected_theme_id"
                        value="{{ $themes->first()->theme_id ?? '588969111070' }}">
                    <input type="hidden" name="banner" id="banner_payload" value="{{ old('banner') }}">
                    <input type="hidden" name="logo" id="logo_payload" value="{{ old('logo') }}">

                    <div class="row g-4">
                        <!-- Left Column: Theme Picker & Visual Assets Summary -->
                        <div class="col-lg-4">
                            <div class="store-section-card sticky-top" style="top: 1.5rem; z-index: 10;">
                                <div class="store-section-header">
                                    <span class="store-section-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-palette"
                                            width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
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
                                        <span class="small text-muted">{{ __('Select a pre-designed layout') }}</span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="theme-preview-card">
                                        <img src="{{ $defaultImage }}" id="currentThemePreview" class="theme-image-display"
                                            alt="{{ __('Selected Theme') }}">
                                        <button type="button"
                                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center"
                                            data-bs-toggle="modal" data-bs-target="#themeModal">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-layout-grid" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
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

                                    <div class="mt-3 p-3 bg-surface-secondary rounded border">
                                        <div class="d-flex align-items-center text-muted small">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-info-circle text-primary"
                                                width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                <path d="M12 9h.01" />
                                                <path d="M11 12h1v4h1" />
                                            </svg>
                                            <span>{{ __('You can change themes anytime without losing your products or settings.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Settings & Data Entry -->
                        <div class="col-lg-8">
                            <!-- Section 1: General Details -->
                            <div class="store-section-card">
                                <div class="store-section-header">
                                    <span class="store-section-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-building-store" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
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
                                        <h4 class="card-title mb-0">{{ __('Store Identity & Localization') }}</h4>
                                        <span
                                            class="small text-muted">{{ __('Basic info, storefront link, and primary display currency') }}</span>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        {{-- Store Name --}}
                                        <div class="col-md-6">
                                            <label class="form-label required">
                                                {{ __('Store Name') }}
                                            </label>

                                            <div class="input-icon">
                                                <span class="input-icon-addon">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-tag" width="18"
                                                        height="18" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                        <path
                                                            d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                                    </svg>
                                                </span>

                                                <input type="text" class="form-control" name="title"
                                                    id="store_title" value="{{ old('title') }}"
                                                    placeholder="{{ __('e.g., Artisan Bakery') }}" required
                                                    @if ($plan_details->personalized_link) onkeyup="convertToLink(this.value); checkLink();" @endif>
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
                                                        class="icon icon-tabler icon-tabler-message-2" width="18"
                                                        height="18" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M8 9h8" />
                                                        <path d="M8 13h6" />
                                                        <path
                                                            d="M9 18h-3a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-3l-3 3l-3 -3z" />
                                                    </svg>
                                                </span>

                                                <input type="text" class="form-control" name="subtitle"
                                                    value="{{ old('subtitle') }}"
                                                    placeholder="{{ __('e.g., Welcome to our online catalog') }}">
                                            </div>
                                        </div>

                                        {{-- Title 2 (another language) --}}
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Title in another language (Optional)') }}</label>
                                            <input type="text" class="form-control" name="title2"
                                                value="{{ old('title2') }}"
                                                placeholder="{{ __('Secondary Title') }}">
                                        </div>

                                        {{-- Subtitle 2 (another language) --}}
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Subtitle in another language (Optional)') }}</label>
                                            <input type="text" class="form-control" name="subtitle2"
                                                value="{{ old('subtitle2') }}"
                                                placeholder="{{ __('Secondary Sub Title') }}">
                                        </div>

                                        {{-- Copyright --}}
                                        <div class="col-12">
                                            <label class="form-label">{{ __('Copyright (Optional)') }}</label>
                                            <input type="text" class="form-control" name="copyright" id="store-copyright"
                                                value="{{ old('copyright') }}"
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

                                                <input type="text" class="form-control font-monospace" name="link"
                                                    id="plink" placeholder="{{ __('my-awesome-store') }}"
                                                    value="{{ $plan_details->personalized_link ? old('link') : $cardId }}"
                                                    autocomplete="off" minlength="2"
                                                    @if ($plan_details->personalized_link) onkeyup="checkLink()"
                oninput="convertToLink(this.value)"
                required
            @else
                readonly
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="{{ __('Personalized Link is not available in your subscribed plan.') }}" @endif>

                                                <span class="input-group-text bg-surface" id="status">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-link text-muted"
                                                        width="20" height="20" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M9 15l6 -6" />
                                                        <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" />
                                                        <path
                                                            d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" />
                                                    </svg>
                                                </span>
                                            </div>

                                            <small class="form-hint">
                                                {{ __('Only lowercase letters, numbers, and hyphens are permitted.') }}
                                            </small>
                                        </div>

                                        {{-- Language --}}
                                        <div
                                            class="col-md-6 {{ is_array(config('app.languages')) && count(config('app.languages')) <= 1 ? 'd-none' : '' }}">
                                            <label class="form-label required"
                                                for="card_lang">{{ __('Default Language') }}</label>
                                            <select name="card_lang" id="card_lang" class="form-control card_lang"
                                                required>
                                                @foreach (config('app.languages') as $langLocale => $langName)
                                                    <option value="{{ $langLocale }}"
                                                        {{ old('card_lang', config('app.locale')) == $langLocale ? 'selected' : '' }}>
                                                        {{ $langName }} ({{ strtoupper($langLocale) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Currency --}}
                                        <div class="col-md-6 {{ $currencies->count() <= 1 ? 'd-none' : '' }}">
                                            <label class="form-label required"
                                                for="currency">{{ __('Store Currency') }}</label>
                                            <select name="currency" id="currency" class="form-control currency"
                                                required>
                                                <option value="" disabled {{ old('currency') ? '' : 'selected' }}>{{ __('Select Currency') }}</option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->iso_code }}"
                                                        {{ old('currency', $currencies->count() == 1 ? $currency->iso_code : '') == $currency->iso_code ? 'selected' : '' }}>
                                                        {{ $currency->name }} ({{ $currency->symbol }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Media & Visual Assets -->
                            <div class="store-section-card">
                                <div class="store-section-header">
                                    <span class="store-section-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo"
                                            width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
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
                                            class="small text-muted">{{ __('Upload logo and promotional store banners') }}</span>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        {{-- Logo Upload Box --}}
                                        <div class="col-md-6">
                                            <label class="form-label required">{{ __('Store Logo') }}</label>
                                            <div class="upload-dropzone-box" onclick="$('#logoInput').trigger('click');">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-cloud-upload text-muted mb-2"
                                                    width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" />
                                                    <path d="M9 15l3 -3l3 3" />
                                                    <path d="M12 12l0 9" />
                                                </svg>
                                                <div class="fw-semibold text-reset">{{ __('Choose Logo Image') }}</div>
                                                <small
                                                    class="text-muted d-block mt-1">{{ __('Square ratio (1:1) recommended') }}</small>
                                            </div>
                                            <input type="file" class="d-none" id="logoInput"
                                                accept=".jpeg,.jpg,.png,.gif,.svg" required>

                                            <div id="logoPreviewContainer" class="media-preview-badge-container d-none">
                                            </div>
                                        </div>

                                        {{-- Banner Upload Box --}}
                                        <div class="col-md-6">
                                            <label class="form-label required">{{ __('Store Banner(s)') }}</label>
                                            <div class="upload-dropzone-box" onclick="$('#coverInput').trigger('click');">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-slideshow">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M15 6l.01 0" />
                                                    <path
                                                        d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3l0 -8" />
                                                    <path d="M3 13l4 -4a3 5 0 0 1 3 0l4 4" />
                                                    <path d="M13 12l2 -2a3 5 0 0 1 3 0l3 3" />
                                                    <path d="M8 21l.01 0" />
                                                    <path d="M12 21l.01 0" />
                                                    <path d="M16 21l.01 0" />
                                                </svg>
                                                <div class="fw-semibold text-reset">{{ __('Choose Banner Image(s)') }}
                                                </div>
                                                <small
                                                    class="text-muted d-block mt-1">{{ __('16:9 Landscape ratio recommended') }}</small>
                                            </div>
                                            <input type="file" class="d-none" id="coverInput"
                                                accept=".jpeg,.jpg,.png,.gif,.svg" multiple required>

                                            <div id="bannerPreviewContainer" class="media-preview-badge-container d-none">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: WhatsApp Ordering Channel -->
                            <div class="store-section-card">
                                <div class="store-section-header">
                                    <span class="store-section-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-brand-whatsapp" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                            <path
                                                d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                                        </svg>
                                    </span>
                                    <div>
                                        <h4 class="card-title mb-0">{{ __('WhatsApp Order Routing') }}</h4>
                                        <span
                                            class="small text-muted">{{ __('Direct customer checkout orders straight to your WhatsApp account') }}</span>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        {{-- Country Code --}}
                                        <div class="col-md-5">
                                            <label class="form-label required">{{ __('Country Dialing Code') }}</label>
                                            @include('user.pages.store.include.country-code')
                                        </div>

                                        {{-- WhatsApp Number --}}
                                        <div class="col-md-7">
                                            <label class="form-label required">{{ __('WhatsApp Phone Number') }}</label>
                                            <div class="input-icon">
                                                <span class="input-icon-addon">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-phone" width="18"
                                                        height="18" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                    </svg>
                                                </span>
                                                <input type="number" class="form-control" name="whatsapp_no"
                                                    value="{{ old('whatsapp_no') }}"
                                                    placeholder="{{ __('9876543210') }}" required>
                                            </div>
                                        </div>

                                        {{-- WhatsApp Footer Text --}}
                                        <div class="col-12">
                                            <label
                                                class="form-label required">{{ __('Checkout Message Footer Note') }}</label>
                                            <textarea class="form-control" name="whatsapp_msg" data-bs-toggle="autosize" rows="3"
                                                placeholder="{{ __('Thanks for shopping with us!') }}" required>{{ old('whatsapp_msg', __('Thanks for shopping with us.')) }}</textarea>
                                            <small
                                                class="form-hint">{{ __('This custom text appears at the bottom of customer WhatsApp order messages.') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Standard Form Footer: Symmetrical Actions -->
                            <div class="store-section-card p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('user.stores') }}"
                                        class="btn btn-danger d-inline-flex align-items-center gap-1">
                                        {{ __('Cancel') }}
                                    </a>

                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                            height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-4 0l0 -4" />
                                        </svg>
                                        {{ __('Create') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('user.includes.footer')
    </div>

    {{-- Choose Theme Modal --}}
    <div class="modal modal-bg fade" id="themeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg">
                {{-- Modal Header --}}
                <div class="modal-header border-0 py-3">
                    <div>
                        <h5 class="modal-title fs-3 fw-bold">{{ __('Choose Storefront Theme') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ __('Browse and select a theme layout for your WhatsApp storefront.') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body">
                    {{-- Glowing Search Input --}}
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
                            <input type="text" id="themeSearchInput"
                                class="form-control form-control-lg theme-search-input"
                                placeholder="{{ __('Search themes by name or category...') }}" autocomplete="off">
                        </div>
                    </div>

                    {{-- Themes Grid --}}
                    <div class="row g-3" id="themeResultsGrid">
                        @forelse ($themes as $theme)
                            <div class="col-6 col-md-4 col-lg-2 theme-grid-item"
                                data-name="{{ strtolower($theme->theme_name) }}">
                                <div class="theme-choice-card p-2 text-center {{ $loop->first ? 'selected' : '' }}"
                                    onclick="selectThemeItem('{{ $theme->theme_id }}', '{{ asset('img/vCards/' . $theme->theme_thumbnail) }}', this)">
                                    <img src="{{ asset('img/vCards/' . $theme->theme_thumbnail) }}"
                                        class="img-fluid rounded mb-2" alt="{{ $theme->theme_name }}"
                                        onerror="this.src='{{ asset('img/default-theme.png') }}'">
                                    <div class="fw-bold small text-truncate">{{ $theme->theme_name }}</div>
                                </div>
                            </div>
                        @empty
                            {{-- Server-side Empty State --}}
                            <div class="col-12">
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
                                    <h4 class="fw-bold text-reset mb-1">{{ __('No Themes Available') }}</h4>
                                    <p class="text-muted small mb-0">
                                        {{ __('No store themes are configured yet. Please contact support or check back later.') }}
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Client-side Search No-Results Empty State (Dynamically toggled via JS) --}}
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

                {{-- Modal Footer --}}
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
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize TomSelect dropdowns
            document.querySelectorAll('.card_lang, .currency, .country_code').forEach(function(el) {

                // 1. Fix duplicate values in HTML options before TomSelect starts
                // (Example: multiple options having value="+1" like USA and Canada)
                const seenValues = {};
                Array.from(el.options).forEach((opt) => {
                    const originalVal = opt.value;
                    if (seenValues[originalVal]) {
                        seenValues[originalVal]++;
                        // Assign unique internal value (e.g., +1__dup2)
                        opt.value = `${originalVal}__dup${seenValues[originalVal]}`;
                    } else {
                        seenValues[originalVal] = 1;
                    }
                });

                // 2. Initialize Single-Select TomSelect
                if (!el.tomselect) {
                    new TomSelect(el, {
                        copyClassesToDropdown: false,
                        dropdownClass: 'dropdown-menu ts-dropdown',
                        optionClass: 'dropdown-item',
                        controlInput: '<input>',
                        maxOptions: null,
                        hideSelected: false,
                        duplicates: true,
                        render: {
                            // Display the option text nicely in the dropdown list
                            option: function(data, escape) {
                                return '<div class="dropdown-item">' + escape(data.text) +
                                    '</div>';
                            },
                            // Display the selected item text in the input box
                            item: function(data, escape) {
                                return '<div>' + escape(data.text) + '</div>';
                            }
                        }
                    });
                }
            });
        });

        // Restore logo / banner previews from old() after a failed submit
        document.addEventListener('DOMContentLoaded', function() {
            const logoPayload = document.getElementById('logo_payload');
            if (logoPayload && logoPayload.value) {
                const logoPreview = document.getElementById('logoPreviewContainer');
                logoPreview.classList.remove('d-none');
                logoPreview.innerHTML = `<div class="media-preview-item"><img src="${logoPayload.value}" alt="{{ __('Store Logo') }}"></div>`;
                document.getElementById('logoInput').required = false;
            }

            const bannerPayload = document.getElementById('banner_payload');
            if (bannerPayload && bannerPayload.value) {
                let oldBanners = [];
                try {
                    oldBanners = JSON.parse(bannerPayload.value);
                } catch (e) {
                    oldBanners = bannerPayload.value.split(',');
                }
                if (!Array.isArray(oldBanners)) {
                    oldBanners = [oldBanners];
                }
                const bannerPreview = document.getElementById('bannerPreviewContainer');
                oldBanners.forEach(function(url) {
                    url = (url || '').trim();
                    if (url) {
                        uploadedBanners.push(url);
                        bannerPreview.insertAdjacentHTML('beforeend',
                            `<div class="media-preview-item"><img src="${url}" alt="{{ __('Store Banner') }}"></div>`);
                    }
                });
                if (uploadedBanners.length > 0) {
                    bannerPreview.classList.remove('d-none');
                    bannerPayload.value = JSON.stringify(uploadedBanners);
                    document.getElementById('coverInput').required = false;
                }
            }
        });

        // Sync copyright from store name until the user edits it manually
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('store_title');
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

        // Lightweight Theme Search & Empty State Trigger
        document.addEventListener('DOMContentLoaded', function() {
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

                    // Toggle No Themes Found state
                    if (visibleCount === 0 && themeGridItems.length > 0) {
                        noThemesFound.classList.remove('d-none');
                    } else {
                        noThemesFound.classList.add('d-none');
                    }
                });
            }
        });

        // Theme Selection Handler
        function selectThemeItem(themeId, thumbnailUrl, element) {
            document.getElementById('selected_theme_id').value = themeId;
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

        // Slug / Personalized Link Generator
        function convertToLink(str) {
            str = str.replace(/[`~!@#$%^&*()_\-+=\[\]{};:'"\\|\/,.<>?\s]/g, ' ').toLowerCase();
        str = str.replace(/^\s+|\s+$/gm, '');
        str = str.replace(/^https?:\/\//i, '');
        str = str.replace(/^www\./i, '');
        str = str.replace(/\s+/g, '-');
        const plink = document.getElementById('plink');
        if (plink) {
            plink.value = str;
        }
    }

    // Check Link Availability via AJAX
    function checkLink() {
        const plinkInput = document.getElementById('plink');
        if (!plinkInput) return;
        const plinkVal = plinkInput.value.trim();
        const statusBox = document.getElementById('status');

        if (plinkVal.length > 2) {
            fetch("{{ route('user.check.link') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        link: plinkVal
                    })
                })
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        statusBox.innerHTML = `
                                                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Link is available') }}">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-success icon-tabler icon-tabler-circle-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                                            <path d="M9 12l2 2l4 -4" />
                                                                                        </svg>
                                                                                    </span>`;
                    } else {
                        statusBox.innerHTML = `
                                                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Link is already taken') }}">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger icon-tabler icon-tabler-circle-x" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                                            <path d="M10 10l4 4m0 -4l-4 4" />
                                                                                        </svg>
                                                                                    </span>`;
                    }
                })
                .catch(() => {
                    statusBox.innerHTML = `<span class="text-warning">!</span>`;
                });
        } else {
            statusBox.innerHTML = `
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-link text-muted" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                                <path d="M9 15l6 -6"/>
                                                                                <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/>
                                                                                <path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/>
                                                                            </svg>`;
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

    // Banner Cropping Handlers (Sequential multiple uploads)
    let bannerCropper = null;
    let bannerFiles = [];
    let bannerIndex = 0;
    let uploadedBanners = [];
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

    // Banner Cropping Event in Blade JS
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
                    // Relative path normalize
                    uploadedBanners.push(data.imageUrl);

                    // Set clean JSON in hidden input
                    document.getElementById('banner_payload').value = JSON.stringify(
                        uploadedBanners);

                    const previewContainer = document.getElementById('bannerPreviewContainer');
                    previewContainer.classList.remove('d-none');
                    previewContainer.insertAdjacentHTML('beforeend', `
                        <div class="media-preview-item">
                            <img src="${data.imageUrl}" alt="{{ __('Store Banner') }}">
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
    </script>
@endpush
