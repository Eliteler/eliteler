@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Cropper CSS --}}
    <link href="{{ asset('css/cropper.min.css') }}" rel="stylesheet">

    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />

    {{-- Digital Business Card CSS --}}
    <link rel="stylesheet" href="{{ asset('css/digital-business-card.css') }}" />

    {{-- TinyMCE CSS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@php
    $themeIds = $themes->pluck('theme_id')->toArray();
    $defaultImage = '';
    $themeId = '';

    if (in_array($business_card->theme_id, $themeIds)) {
        $theme = $themes->firstWhere('theme_id', $business_card->theme_id);
        $defaultImage = asset('img/vCards/' . $theme->theme_thumbnail);
        $themeId = $business_card->theme_id;
    } else {
        $defaultImage = asset('img/vCards/personal-gray.png');
        $themeId = '588969111021';
    }
@endphp

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
                                {{ __('Basic Details') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Basic Details') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Update cover visual styles, select layouts, edit details, and verify personalized link.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Alerts fallback --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

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
                        <!-- Sidebar navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body p-3">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'basic',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Edit Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.business.card', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="theme_id" id="choosedTheme" value="{{ $themeId }}">

                                <div class="card-body p-lg-4">
                                    <div class="row g-4">
                                        {{-- Left Column: Theme Live Preview Mockup --}}
                                        @if ($business_card->type != 'custom')
                                            <div class="col-lg-4 col-md-5">
                                                <div class="theme-preview-card">
                                                    <div>
                                                        <span
                                                            class="badge bg-blue-lt text-uppercase fw-bold mb-3 px-3 py-1">
                                                            {{ __('Active Theme Layout') }}
                                                        </span>
                                                        <div class="theme-mockup-wrap mx-auto">
                                                            <img src="{{ $defaultImage }}" class="theme-image"
                                                                alt="Selected Theme">
                                                        </div>
                                                    </div>

                                                    <button type="button"
                                                        class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold"
                                                        data-bs-toggle="modal" data-bs-target="#themeModal">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                            width="20" height="20" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M12 21a9 9 0 0 1 0 -18c4.97 0 9 3.582 9 8c0 1.06 -.474 2.078 -1.318 2.828c-.844 .75 -1.989 1.172 -3.182 1.172h-2.5a2 2 0 0 0 -1 3.75a1.3 1.3 0 0 1 -.5 1.25a2.08 2.08 0 0 1 -1.5 .5z" />
                                                            <circle cx="7.5" cy="10.5" r="1" />
                                                            <circle cx="12" cy="7.5" r="1" />
                                                            <circle cx="16.5" cy="10.5" r="1" />
                                                        </svg>
                                                        {{ __('Browse All Themes') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Right Column: Parameter Inputs --}}
                                        <div
                                            class="{{ $business_card->type != 'custom' ? 'col-lg-8 col-md-7' : 'col-12' }}">
                                            <div class="row g-3">
                                                <!-- Language Select (TomSelect) -->
                                                <div
                                                    class="col-md-6 {{ is_array(config('app.languages')) && count(config('app.languages')) <= 1 ? 'd-none' : '' }}">
                                                    <label class="form-label required"
                                                        for="card_lang">{{ __('Language') }}</label>
                                                    <select name="card_lang" id="card_lang" class="form-control card_lang"
                                                        required>
                                                        @foreach (config('app.languages') as $langLocale => $langName)
                                                            <option value="{{ $langLocale }}"
                                                                {{ old('card_lang', strtolower($business_card->card_lang)) == $langLocale ? 'selected' : '' }}>
                                                                {{ $langName }} ({{ strtoupper($langLocale) }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Cover Type Select (TomSelect) -->
                                                <div class="col-md-6" id="cover-type-section">
                                                    <label class="form-label required"
                                                        for="coverType">{{ __('Cover type') }}</label>
                                                    <select id="coverType" name="cover_type" class="form-control cover_type"
                                                        required>
                                                        <option value="photo"
                                                            {{ old('cover_type', $business_card->cover_type ?: 'photo') == 'photo' ? 'selected' : '' }}>
                                                            {{ __('Photo') }}</option>
                                                        <option value="youtube"
                                                            {{ old('cover_type', $business_card->cover_type) == 'youtube' ? 'selected' : '' }}>
                                                            {{ __('YouTube Video') }}</option>
                                                        <option value="youtube-ap"
                                                            {{ old('cover_type', $business_card->cover_type) == 'youtube-ap' ? 'selected' : '' }}>
                                                            {{ __('YouTube Video - Autoplay') }}</option>
                                                        <option value="vimeo"
                                                            {{ old('cover_type', $business_card->cover_type) == 'vimeo' ? 'selected' : '' }}>
                                                            {{ __('Vimeo Video') }}</option>
                                                        <option value="vimeo-ap"
                                                            {{ old('cover_type', $business_card->cover_type) == 'vimeo-ap' ? 'selected' : '' }}>
                                                            {{ __('Vimeo Video - Autoplay') }}</option>
                                                        <option value="none"
                                                            {{ old('cover_type', $business_card->cover_type) == 'none' ? 'selected' : '' }}>
                                                            {{ __('Default') }}</option>
                                                    </select>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('Autoplay video will be muted due to browser policies') }}</small>
                                                </div>

                                                <!-- Cover Media Input -->
                                                <div class="col-md-6">
                                                    <div id="cover_url_col" class="d-none">
                                                        <label id="cover_url_label" class="form-label required"
                                                            for="cover_url_input">{{ __('Cover Video URL') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"
                                                                id="cover_url_title">https://www.youtube.com/watch?v=</span>
                                                            <input type="text" class="form-control"
                                                                id="cover_url_input" name="cover_url"
                                                                placeholder="{{ __('Video ID') }}" autocomplete="off"
                                                                value="{{ old('cover_url', $business_card->cover_type != 'none' && $business_card->cover_type != 'photo' ? $business_card->cover : '') }}">
                                                        </div>
                                                    </div>

                                                    <div id="coverChooser">
                                                        <label class="form-label required"
                                                            for="cover">{{ __('Cover') }}</label>
                                                        <input type="file" class="form-control" id="cover"
                                                            accept=".jpeg,.jpg,.png,.gif,.svg">
                                                        <small class="form-hint text-muted mt-1 d-block"><i
                                                                class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '1200 x 400 px']) }}</small>
                                                        <input type="hidden" name="cover" id="cropped_cover_input"
                                                            value="{{ old('cover', $business_card->cover) }}">

                                                        <!-- Modern Cover Preview Box -->
                                                        <div class="{{ !empty($business_card->cover) && $business_card->cover_type == 'photo' ? 'd-flex' : 'd-none' }} media-preview-card"
                                                            id="coverNone">
                                                            <div class="media-preview-thumb-cover" id="coverPreview"
                                                                style="background-image: url({{ asset(old('cover', $business_card->cover)) }});">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Logo File Input -->
                                                <div class="col-md-6">
                                                    <label class="form-label" for="logo">{{ __('Logo') }}</label>
                                                    <input type="file" class="form-control" id="logo"
                                                        accept=".jpeg,.jpg,.png,.gif,.svg">
                                                    <small class="form-hint text-muted mt-1 d-block"><i
                                                            class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 800 px']) }}</small>
                                                    <input type="hidden" name="logo" id="cropped_logo_input"
                                                        value="{{ old('logo', $business_card->profile) }}">

                                                    <!-- Modern Logo Preview Box -->
                                                    <div class="{{ !empty($business_card->profile) ? 'd-flex' : 'd-none' }} media-preview-card"
                                                        id="logoNone">
                                                        <div class="media-preview-thumb-logo" id="logoPreview"
                                                            style="background-image: url({{ asset(old('logo', $business_card->profile)) }});">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Title -->
                                                <div class="col-md-6">
                                                    <label class="form-label required" for="titleInput">
                                                        {{ __('Title') }}
                                                    </label>
                                                    <input type="text" class="form-control" name="title"
                                                        id="{{ \Illuminate\Support\Str::contains($business_card->title, 'Duplicate') ? 'title' : 'default-title' }}"
                                                        value="{{ old('title', $business_card->title) }}"
                                                        placeholder="{{ __('Business name / Your name') }}" required>
                                                </div>

                                                <!-- Personalized Link -->
                                                <div class="col-md-6">
                                                    <label class="form-label required" for="plink">
                                                        {{ __('Personalized Link') }}
                                                    </label>

                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            {{ URL::to('/') }}/
                                                        </span>

                                                        <input type="text" class="form-control font-monospace"
                                                            name="link" id="plink"
                                                            placeholder="{{ __('Personalized Link') }}"
                                                            value="{{ old('link', $business_card->card_url) }}" minlength="2"
                                                            readonly
                                                            @if (!$plan_details->personalized_link) data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="{{ __('Personalized Link is not available in your subscribed plan.') }}" @endif
                                                            required>

                                                        <!-- Status Icon -->
                                                        <span class="input-group-text" id="status">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon text-muted" width="20" height="20"
                                                                viewBox="0 0 24 24" stroke-width="2"
                                                                stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 15l6 -6" />
                                                                <path
                                                                    d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" />
                                                                <path
                                                                    d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" />
                                                            </svg>
                                                        </span>

                                                        <!-- Edit Pencil Icon Button -->
                                                        @if ($plan_details->personalized_link)
                                                            <button type="button" class="btn btn-primary btn-icon"
                                                                id="editPlinkBtn"
                                                                title="{{ __('Edit Personalized Link') }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                    width="20" height="20" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z"
                                                                        fill="none" />
                                                                    <path
                                                                        d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                                    <path d="M13.5 6.5l4 4" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>

                                                    <small class="form-hint text-muted mt-1">
                                                        {{ __('Live Link') }} :
                                                        <a href="{{ url($business_card->card_url) }}" target="_blank"
                                                            class="fw-bold text-decoration-none hover-primary">
                                                            {{ url($business_card->card_url) }}
                                                        </a>
                                                    </small>
                                                </div>

                                                <!-- Subtitle -->
                                                <div class="col-md-12">
                                                    <label class="form-label"
                                                        for="subtitle">{{ __('Sub Title (Optional)') }}</label>
                                                    <input type="text" class="form-control" name="subtitle"
                                                        id="subtitle" value="{{ old('subtitle', $business_card->sub_title) }}"
                                                        placeholder="{{ __('Location / Job title') }}">
                                                </div>

                                                <!-- Description TinyMCE -->
                                                <div class="col-12">
                                                    <label class="form-label"
                                                        for="description">{{ __('Description (Optional)') }}</label>
                                                    <textarea class="form-control" name="description" id="description" placeholder="{{ __('About business / Bio') }}">{{ old('description', $business_card->description) }}</textarea>
                                                </div>

                                                <!-- Title in another language -->
                                                <div class="col-md-6">
                                                    <label class="form-label"
                                                        for="title2">{{ __('Title in another language (Optional)') }}</label>
                                                    <input type="text" class="form-control" name="title2" id="title2"
                                                        placeholder="{{ __('Secondary Title') }}"
                                                        value="{{ old('title2', $business_card->title2) }}">
                                                </div>

                                                <!-- Subtitle in another language -->
                                                <div class="col-md-6">
                                                    <label class="form-label"
                                                        for="subtitle2">{{ __('Subtitle in another language (Optional)') }}</label>
                                                    <input type="text" class="form-control" name="subtitle2"
                                                        id="subtitle2" placeholder="{{ __('Secondary Sub Title') }}"
                                                        value="{{ old('subtitle2', $business_card->subtitle2) }}">
                                                </div>

                                                <!-- Copyright -->
                                                <div class="col-12">
                                                    <label class="form-label text-primary"
                                                        for="copyright">{{ __('Copyright (Optional)') }}</label>
                                                    <input type="text" class="form-control" name="copyright"
                                                        id="copyright"
                                                        value="{{ old('copyright', $business_card->copyright) }}"
                                                        placeholder="{{ parse_url(config('app.url'), PHP_URL_HOST) }}">
                                                    <small class="form-hint">{{ __('Leave blank to use site domain') }}</small>
                                                </div>

                                                <!-- Email Settings -->
                                                <div class="col-12">
                                                    <h2 class="page-title mb-3 mt-3">
                                                        {{ __('Email Settings') }}
                                                    </h2>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Custom Email From Name (Optional)') }}
                                                                <span class="form-help" data-bs-toggle="popover"
                                                                    data-bs-placement="top"
                                                                    data-bs-content="{{ __('This name will appear as the sender when visitors receive emails from your card. (e.g., Dr. Ahmed\'s Clinic)') }}"
                                                                    data-bs-html="true">?</span>
                                                            </label>
                                                            <input type="text" class="form-control" name="email_from_name"
                                                                value="{{ old('email_from_name', $business_card->email_from_name) }}"
                                                                placeholder="{{ __('Custom Email From Name') }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Custom Email From Address (Optional)') }}
                                                                <span class="form-help" data-bs-toggle="popover"
                                                                    data-bs-placement="top"
                                                                    data-bs-content="{{ __('This address will appear as the sender email. If empty, the platform email will be used.') }}"
                                                                    data-bs-html="true">?</span>
                                                            </label>
                                                            <input type="email" class="form-control"
                                                                name="email_from_address"
                                                                value="{{ old('email_from_address', $business_card->email_from_address) }}"
                                                                placeholder="{{ __('example@yourdomain.com') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div
                                    class="card-footer border-top d-flex align-items-center justify-content-end py-5 px-4">
                                    <a href="{{ route('user.cards') }}"
                                        class="btn btn-danger me-auto text-decoration-none fw-semibold">{{ __('Cancel') }}</a>
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold submitBtn">
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

    {{-- Themes Modal --}}
    <div class="modal modal-bg fade" id="themeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Fully Responsive Header -->
                <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-surface">
                    <div class="theme-modal-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="modal-title font-weight-bold mb-0 text-dark">{{ __('Select Premium Theme') }}
                                </h5>
                                <small
                                    class="text-secondary d-none d-sm-block">{{ __('Click on any theme to preview and apply') }}</small>
                            </div>
                            <button type="button" class="btn-close d-sm-none" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="theme-modal-header-actions">
                            <div class="theme-search-box">
                                <span class="theme-search-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <circle cx="10" cy="10" r="7" />
                                        <line x1="21" y1="21" x2="15" y2="15" />
                                    </svg>
                                </span>
                                <input type="text" id="searchInput" class="theme-search-input"
                                    placeholder="{{ __('Search themes...') }}">
                            </div>

                            <button type="button" class="btn-close d-none d-sm-block ms-1" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-3 p-sm-4" style="background-color: var(--tblr-bg-surface-secondary, #f8fafc);">
                    <div class="row g-2 g-sm-3" id="results">
                        @php
                            $vidSupportedIds = [
                                '588969111172',
                                '588969111171',
                                '588969111170',
                                '588969111169',
                                '588969111168',
                                '588969111167',
                                '588969111165',
                                '588969111164',
                                '588969111163',
                                '588969111162',
                                '588969111161',
                                '588969111160',
                                '588969111153',
                                '588969111152',
                                '588969111148',
                                '588969111146',
                                '588969111145',
                                '588969111144',
                                '588969111143',
                                '588969111142',
                                '588969111141',
                                '588969111140',
                                '588969111139',
                                '588969111138',
                                '588969111137',
                                '588969111136',
                                '588969111135',
                                '588969111134',
                                '588969111133',
                                '588969111132',
                                '588969111131',
                                '588969111130',
                                '588969111129',
                                '588969111128',
                                '588969111127',
                                '588969111126',
                                '588969111125',
                                '588969111124',
                                '588969111123',
                                '588969111122',
                                '588969111121',
                                '588969111120',
                                '588969111119',
                                '588969111118',
                                '588969111117',
                                '588969111116',
                                '588969111115',
                                '588969111114',
                                '588969111113',
                                '588969111112',
                                '588969111111',
                                '588969111110',
                                '588969111109',
                                '588969111108',
                                '588969111107',
                                '588969111106',
                                '588969111105',
                                '588969111104',
                                '588969111103',
                                '588969111102',
                                '588969111101',
                                '588969111100',
                                '588969111100',
                                '5889691111099',
                                '588969111098',
                                '588969111097',
                                '588969111096',
                                '588969111094',
                                '588969111095',
                                '588969111093',
                                '588969111092',
                                '588969111091',
                                '588969111090',
                                '588969111088',
                                '588969111087',
                                '588969111086',
                            ];
                        @endphp
                        @foreach ($themes as $theme)
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <div class="card h-100 theme-selection-card text-center p-2 {{ $theme->theme_id == $themeId ? 'active' : '' }}"
                                    onclick="chooseTheme(this, `{{ $theme->theme_id }}`, `{{ asset('img/vCards/' . $theme->theme_thumbnail) }}`)">
                                    <div class="theme-card-img-wrapper mb-2">
                                        <img src="{{ asset('img/vCards/' . $theme->theme_thumbnail) }}"
                                            class="theme-modal-thumb" alt="{{ __($theme->theme_name) }}">
                                    </div>

                                    <div class="fw-bold text-truncate small mb-1 mt-3 text-dark px-1">
                                        {{ __($theme->theme_name) }}</div>

                                    <div class="d-flex justify-content-center pb-1">
                                        @if (in_array($theme->theme_id, $vidSupportedIds))
                                            <span class="badge bg-green-lt px-2 py-1" style="font-size: 11px;"
                                                data-bs-toggle="tooltip" title="{{ __('Cover Video Supported') }}">
                                                <i class="fa-solid fa-video me-1"></i> {{ __('Video') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-lt px-2 py-1" style="font-size: 11px;"
                                                data-bs-toggle="tooltip" title="{{ __('Photo only') }}">
                                                <i class="fa-solid fa-image me-1"></i> {{ __('Photo') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Crop Profile Logo Modal --}}
    <div class="modal modal-bg fade" id="cropModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title font-weight-bold mb-0 text-dark">{{ __('Crop Profile Logo') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="cropper-modal-container">
                        <img id="croppedImage" alt="Crop Profile Picture">
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-danger me-auto text-decoration-none fw-semibold"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary fw-semibold px-4"
                        id="crop">{{ __('Apply Crop') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Crop Cover Image Modal --}}
    <div class="modal modal-bg fade" id="cropCoverModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title font-weight-bold mb-0 text-dark">{{ __('Crop Cover Image') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="cropper-modal-container">
                        <img id="croppedCoverImage" alt="Crop Cover Picture">
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-danger me-auto text-decoration-none fw-semibold"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success fw-semibold px-4"
                        id="coverUploadOriginal">{{ __('No Crop') }}</button>
                    <button type="button" class="btn btn-primary fw-semibold px-4"
                        id="coverCrop">{{ __('Apply Crop') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script src="{{ asset('js/cropper.min.js') }}"></script>

    {{-- TomSelect Initialization Helper for Languages and Cover Type --}}
    <script>
        var elementSelectors = ['.card_lang', '.cover_type'];

        function initializeTomSelectWithRequired(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });

            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            el.dispatchEvent(new Event('change'));
        }

        elementSelectors.forEach(function(id) {
            var elements = document.querySelectorAll(id);
            if (elements) {
                elements.forEach(function(el) {
                    initializeTomSelectWithRequired(el);
                });
            }
        });
    </script>

    {{-- Title to slug handler --}}
    <script>
        let titleId =
            "{{ \Illuminate\Support\Str::contains($business_card->title, 'Duplicate') ? 'title' : 'default-title' }}";
        let titleElement = document.getElementById(titleId);

        if (titleId == "title" && titleElement) {
            document.addEventListener("DOMContentLoaded", function() {
                "use strict";

                titleElement.addEventListener("input", function() {
                    let title = this.value;
                    let slug = title
                        .toLowerCase()
                        .replace(/\s+/g, '-')
                        .replace(/[^\w\-]+/g, '')
                        .replace(/--+/g, '-');

                    document.getElementById("plink").value = slug;
                });

                document.getElementById("plink").addEventListener("input", function() {
                    this.value = this.value.replace(/\s+/g, '-');
                });
            });
        }
    </script>

    <script>
        var coverThemeId = "{{ $themeId }}";

        function chooseTheme(element, themeId, thumbnail) {
            $('.theme-selection-card').removeClass('active');
            $(element).addClass('active');

            $("input[name='theme_id']").val(themeId);
            $("#choosedTheme").val(themeId);
            $(".theme-image").attr("src", thumbnail);
            $("#themeModal").modal("hide");

            coverThemeId = themeId;
        }

        // Cover Type Dropdown Handler
        $("#coverType").change(function() {
            if (this.value == "vimeo-ap" || this.value == "vimeo" || this.value == "youtube-ap" || this.value ==
                "youtube") {
                if (this.value.includes('vimeo')) {
                    $("#cover_url_title").text("https://vimeo.com/");
                } else {
                    $("#cover_url_title").text("https://www.youtube.com/watch?v=");
                }
                $("#cover_url_col").removeClass('d-none');
                $("#cover_url_input").prop("required", true);
                $("#coverChooser").addClass('d-none');
                $("#cover").prop("required", false);
            } else if (this.value == "photo") {
                $("#cover_url_col").addClass('d-none');
                $("#cover_url_input").prop("required", false);
                $("#coverChooser").removeClass('d-none');
                $("#cover").prop("required", false);
            } else {
                $("#cover_url_col").addClass('d-none');
                $("#cover_url_input").prop("required", false);
                $("#coverChooser").addClass('d-none');
                $("#cover").prop("required", false);
            }
        });

        // Initialize Cover Type on Load
        $("#coverType").val("{{ old('cover_type', $business_card->cover_type == null ? 'photo' : $business_card->cover_type) }}").change();

        // Profile Image Cropper
        $(document).ready(function() {
            var cropper;
            $('#cropModal').on('shown.bs.modal', function() {
                cropper = new Cropper(document.getElementById('croppedImage'), {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 0.9,
                    responsive: true,
                    restore: false
                });
            }).on('hidden.bs.modal', function() {
                if (cropper) {
                    cropper.destroy();
                }
                $('#logo').val('');
            });

            $('#logo').change(function(e) {
                var files = e.target.files;
                if (files && files.length > 0) {
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        $('#croppedImage').attr('src', event.target.result);
                        $('#cropModal').modal('show');
                    };
                    reader.readAsDataURL(files[0]);
                }
            });

            // Profile Image Crop & Upload
            $('#crop').click(function() {
                var $btn = $(this).prop('disabled', true).text('{{ __('Uploading...') }}');
                var canvas = cropper.getCroppedCanvas({
                    width: 512,
                    height: 512,
                    imageSmoothingQuality: 'high'
                });
                canvas.toBlob(function(blob) {
                    var formData = new FormData();
                    formData.append('croppedImage', blob);
                    $.ajax({
                        url: '{{ route('user.vcard.cropped.image') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#cropModal').modal('hide');
                            $('#cropped_logo_input').val(response.imageUrl);
                            $('#logo').prop('required', false);

                            $("#logoNone").removeClass('d-none').addClass('d-flex');
                            $("#logoPreview").css('background-image', 'url(' + canvas
                                .toDataURL() + ')');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).text('{{ __('Apply Crop') }}');
                        }
                    });
                });
            });
        });

        // Cover Image Cropper (optional crop: free aspect ratio, or upload the original without cropping)
        $(document).ready(function() {
            var cropper;
            var coverFileOriginal; // Original file for "No Crop" upload
            $('#cropCoverModal').on('shown.bs.modal', function() {
                cropper = new Cropper(document.getElementById('croppedCoverImage'), {
                    aspectRatio: NaN, // Free aspect ratio
                    viewMode: 1,
                    cropBoxResizable: true,
                    autoCropArea: 1,
                    responsive: true,
                    restore: false
                });
            }).on('hidden.bs.modal', function() {
                if (cropper) {
                    cropper.destroy();
                }
                $('#cover').val('');
            });

            $('#cover').change(function(e) {
                var files = e.target.files;
                if (files && files.length > 0) {
                    coverFileOriginal = files[0];
                    var reader = new FileReader();
                    reader.onload = function(event) {
                        $('#croppedCoverImage').attr('src', event.target.result);
                        $('#cropCoverModal').modal('show');
                    };
                    reader.readAsDataURL(files[0]);
                }
            });

            // Cover Image Upload without cropping
            $('#coverUploadOriginal').click(function() {
                if (!coverFileOriginal) return;
                var $btn = $(this).prop('disabled', true).text('{{ __('Uploading...') }}');
                var formData = new FormData();
                formData.append('croppedImage', coverFileOriginal);
                $.ajax({
                    url: '{{ route('user.vcard.cropped.image') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#cropCoverModal').modal('hide');
                        $('#cropped_cover_input').val(response.imageUrl);
                        $('#cover').prop('required', false);

                        $("#coverNone").removeClass('d-none').addClass('d-flex');
                        $("#coverPreview").css('background-image', 'url(' + URL.createObjectURL(
                            coverFileOriginal) + ')');
                    },
                    error: function() {
                        console.log('Upload error');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('{{ __('No Crop') }}');
                    }
                });
            });

            // Cover Image Crop & Upload
            $('#coverCrop').click(function() {
                var $btn = $(this).prop('disabled', true).text('{{ __('Uploading...') }}');
                var canvas = cropper.getCroppedCanvas({
                    imageSmoothingQuality: 'high'
                });
                canvas.toBlob(function(blob) {
                    var formData = new FormData();
                    formData.append('croppedImage', blob);
                    $.ajax({
                        url: '{{ route('user.vcard.cropped.image') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#cropCoverModal').modal('hide');
                            $('#cropped_cover_input').val(response.imageUrl);
                            $('#cover').prop('required', false);

                            $("#coverNone").removeClass('d-none').addClass('d-flex');
                            $("#coverPreview").css('background-image', 'url(' + canvas
                                .toDataURL() + ')');
                        },
                        complete: function() {
                            $btn.prop('disabled', false).text('{{ __('Apply Crop') }}');
                        }
                    });
                });
            });
        });

        /* Detect if current page is in Dark Mode */
        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
            document.body.getAttribute('data-bs-theme') === 'dark' ||
            document.body.classList.contains('theme-dark');

        /* Current theme string */
        const currentTheme = isDarkMode ? 'dark' : 'light';

        tinymce.init({
            selector: 'textarea#description',
            plugins: 'autolink lists link charmap preview searchreplace visualblocks',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | removeformat',
            height: "220",
            menubar: false,
            statusbar: false,

            /* Dynamically switch TinyMCE UI toolbar skin between Light and Dark */
            skin: isDarkMode ? 'oxide-dark' : 'oxide',
            content_css: isDarkMode ? 'dark' : 'default',

            /* Dynamic internal iframe typography and background colors */
            content_style: `
        body {
            font-family: "Figtree", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            background-color: ${isDarkMode ? '#0e172c' : '#ffffff'};
            color: ${isDarkMode ? '#cbd5e1' : '#334155'};
        }
        
        /* Dynamic Placeholder text color inside the editor */
        .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
            color: ${isDarkMode ? '#8599B4' : '#94A1AC'} !important;
            opacity: 0.85 !important;
            font-style: normal !important;
        }
    `,

            /* Automatically set data-bs-theme attribute directly on the editor body */
            setup: function(editor) {
                editor.on('init', function() {
                    editor.getBody().setAttribute('data-bs-theme', currentTheme);
                });
            }
        });

        var APP_URL = '{{ config('app.url') }}';
        var vidSupportedIds = [
            '588969111172', '588969111171', '588969111170', '588969111169', '588969111168', '588969111167',
            '588969111165', '588969111164',
            '588969111163', '588969111162', '588969111161', '588969111160', '588969111153', '588969111152',
            '588969111148', '588969111146', '588969111145', '588969111144', '588969111143', '588969111142',
            '588969111141', '588969111140', '588969111139', '588969111138', '588969111137', '588969111136',
            '588969111135', '588969111134', '588969111133', '588969111132', '588969111131', '588969111130',
            '588969111129', '588969111128', '588969111127', '588969111126', '588969111125', '588969111124',
            '588969111123', '588969111122', '588969111121', '588969111120', '588969111119', '588969111118',
            '588969111117', '588969111116', '588969111115', '588969111114', '588969111113', '588969111112',
            '588969111111', '588969111110', '588969111109', '588969111108', '588969111107', '588969111106',
            '588969111105', '588969111104', '588969111103', '588969111102', '588969111101', '588969111100',
            '588969111100', '5889691111099', '588969111098', '588969111097', '588969111096', '588969111094',
            '588969111095', '588969111093', '588969111092', '588969111091', '588969111090', '588969111088',
            '588969111087', '588969111086'
        ];

        // Search in Theme Modal with Tabler Empty State and Skeleton Placeholder
        var searchTimer;
        $('#searchInput').on('input', function() {
            let query = $(this).val();
            let type = 'vCard';
            let cardType = "{{ $business_card->type ? $business_card->type : 'business' }}";

            clearTimeout(searchTimer);

            let placeholderSkeletons = '';
            for (let i = 0; i < 6; i++) {
                placeholderSkeletons += `
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 placeholder-glow">
                        <div class="card h-100 p-2 text-center border-0 shadow-none bg-transparent">
                            <div class="placeholder rounded-3 w-100 mb-2" style="height: 160px;"></div>
                            <div class="placeholder col-8 mx-auto mb-1"></div>
                            <div class="placeholder col-4 mx-auto"></div>
                        </div>
                    </div>`;
            }
            $('#results').html(placeholderSkeletons);

            searchTimer = setTimeout(function() {
                $.ajax({
                    url: '{{ route('user.search.theme') }}',
                    type: 'GET',
                    data: {
                        query: query,
                        type: type,
                        cardType: cardType
                    },
                    dataType: 'json',
                    success: function(response) {
                        let output = '';
                        if (response.length === 0) {
                            output = `
                                <div class="col-12 py-5">
                                    <div class="empty text-center py-4">
                                        <div class="empty-icon mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-secondary" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <circle cx="10" cy="10" r="7"/>
                                                <line x1="21" y1="21" x2="15" y2="15"/>
                                                <line x1="10" y1="13" x2="10" y2="13.01"/>
                                                <line x1="10" y1="7" x2="10" y2="10"/>
                                            </svg>
                                        </div>
                                        <p class="empty-title h4 font-weight-bold mb-1 text-dark">{{ __('No themes found') }}</p>
                                        <p class="empty-subtitle text-secondary mb-0">
                                            {{ __('Try searching for a different keyword or category.') }}
                                        </p>
                                    </div>
                                </div>`;
                        } else {
                            $.each(response, function(index, card) {
                                let isVideo = vidSupportedIds.includes(String(card
                                    .theme_id));
                                let videoBadge = isVideo ?
                                    `<span class="badge bg-green-lt px-2 py-1" style="font-size: 11px;" data-bs-toggle="tooltip" title="{{ __('Cover Video Supported') }}"><i class="fa-solid fa-video me-1"></i> {{ __('Video') }}</span>` :
                                    `<span class="badge bg-secondary-lt px-2 py-1" style="font-size: 11px;" data-bs-toggle="tooltip" title="{{ __('Photo only') }}"><i class="fa-solid fa-image me-1"></i> {{ __('Photo') }}</span>`;

                                output += `<div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card h-100 theme-selection-card text-center p-2 ${card.theme_id == coverThemeId ? 'active' : ''}" onclick="chooseTheme(this, '${card.theme_id}', '${APP_URL}/img/vCards/${card.theme_thumbnail}')">
                                        <div class="theme-card-img-wrapper mb-2">
                                            <img src="${APP_URL}/img/vCards/${card.theme_thumbnail}" class="theme-modal-thumb" alt="${card.theme_name}">
                                        </div>
                                        <div class="fw-bold text-truncate small mb-1 text-dark px-1">${card.theme_name}</div>
                                        <div class="d-flex justify-content-center pb-1">
                                            ${videoBadge}
                                        </div>
                                    </div>
                                </div>`;
                            });
                        }
                        $('#results').html(output);
                    }
                });
            }, 300);
        });

        // Variable to store original card URL
        var originalCardUrl = "{{ $business_card->card_url }}";

        // Function to check link availability via AJAX
        function checkLink() {
            var plink = $('#plink').val().trim();

            // If the input value matches the original link, show success icon
            if (plink === originalCardUrl) {
                $('#status').html(
                    '<span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2l4 -4"/></svg></span>'
                );
                $('.submitBtn').prop('disabled', false);
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
                        $('.submitBtn').prop('disabled', false);
                    } else {
                        $('#status').html(
                            '<span class="text-danger"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><line x1="10" y1="10" x2="14" y2="14"/><line x1="14" y1="10" x2="10" y2="14"/></svg></span>'
                        );
                        $('.submitBtn').prop('disabled', true);
                    }
                });
            } else {
                $('#status').html(
                    '<svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 15l6 -6"/><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464"/><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463"/></svg>'
                );
            }
        }

        // Handle pencil icon click
        $('#editPlinkBtn').click(function() {
            // Check if plink is already unlocked
            if ($('#plink').prop('readonly')) {
                // Show modal only if it is still readonly
                $('#editLinkConfirmModal').modal('show');
            } else {
                // If already unlocked, simply focus the input without showing modal
                $('#plink').focus();
            }
        });

        // Confirm unlock button handler
        $('#confirmUnlockLinkBtn').click(function() {
            // Hide the modal
            $('#editLinkConfirmModal').modal('hide');

            // Remove readonly and set focus
            $('#plink').removeAttr('readonly').focus();

            // Change pencil button style to active state
            $('#editPlinkBtn').addClass('btn-primary').removeClass('btn-outline-secondary');
        });

        // Realtime check when typing in personalized link
        var linkTimer;
        $('#plink').on('input', function() {
            this.value = this.value.replace(/\s+/g, '-');
            clearTimeout(linkTimer);
            linkTimer = setTimeout(checkLink, 300);
        });

        // Trigger check when Title generates slug
        let titleInputElem = document.getElementById(titleId);
        if (titleInputElem) {
            titleInputElem.addEventListener("input", function() {
                if (!$('#plink').prop('readonly')) {
                    clearTimeout(linkTimer);
                    linkTimer = setTimeout(checkLink, 300);
                }
            });
        }
    </script>
@endpush
