@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    @php
        $googleFonts = [
            'Poppins',
            'Roboto',
            'Inter',
            'League Spartan',
            'Rethink Sans',
            'Outfit',
            'Figtree',
            'Teachers',
            'Saira Stencil One',
            'Gloock',
            'Pacifico',
            'Playfair Display',
            'Cairo',
            'Almarai',
            'Tajawal',
            'IBM Plex Sans Arabic',
            'Readex Pro',
        ];
    @endphp

    {{-- Google Fonts CDN --}}
    @foreach ($googleFonts as $font)
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $font) }}&display=swap"
            rel="stylesheet">
    @endforeach

    {{-- Customization CSS --}}
    <link rel="stylesheet" href="{{ asset('css/customizations.css') }}">

    {{-- Arabic font preview classes (custom) --}}
    <style>
        .cairo {
            font-family: 'Cairo', sans-serif;
        }

        .almarai {
            font-family: 'Almarai', sans-serif;
        }

        .tajawal {
            font-family: 'Tajawal', sans-serif;
        }

        .ibm-plex-sans-arabic {
            font-family: 'IBM Plex Sans Arabic', sans-serif;
        }

        .readex-pro {
            font-family: 'Readex Pro', sans-serif;
        }
    </style>

    {{-- Mockup CSS --}}
    <link rel="stylesheet" href="{{ asset('css/mockup.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/spectrum.min.css') }}">
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
                                {{ __('Design Customization') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Design Customization') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Customize visual themes, select custom font styles, assign interface color codes, and review responsive mobile layout alignments.') }}
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
                                        'link' => 'customization',
                                    ])
                                </div>
                            </div>
                        </div>

                        @php
                            $custom_styles = json_decode($business_card->custom_styles, true);
                        @endphp

                        <div class="col-12 col-md-9 col-lg-10">
                            {{-- Live Ajax Alert --}}
                            <div class="alert alert-important alert-danger alert-dismissible m-3 d-none" role="alert"
                                id="error-message">
                                <div class="d-flex">
                                    <div id="status-message"></div>
                                </div>
                                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>

                            <div class="row g-0">
                                {{-- Left Column: Customization Controls --}}
                                <div class="col-12 col-xl-7 p-3 p-lg-4 border-end">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">
                                            {{ __('Visual Theme & Style Editor') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Customize header styles, fonts, backgrounds, buttons, and colors in real-time.') }}
                                        </p>
                                    </div>

                                    {{-- Section 1: Header Section --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Header Section') }}</h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Configure profile header alignments and typography colors.') }}</p>
                                        </div>

                                        {{-- Profile Layout --}}
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Profile Layout') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['row', 'column'] as $layout)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="layout" value="{{ $layout }}"
                                                            onclick="updateCustomStyle('layout')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['layout'] ?? '') == $layout ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($layout) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Profile Image Style --}}
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Profile Image Style') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['circle', 'square', 'rounded'] as $profileImageStyle)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="profile_image_style"
                                                            value="{{ $profileImageStyle }}"
                                                            onclick="updateCustomStyle('profile_image_style')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['profile_image_style'] ?? '') == $profileImageStyle ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($profileImageStyle) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Header Colors Grid --}}
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4 mb-2">
                                                <label class="form-label"
                                                    for="colorPickerTitle">{{ __('Title Color') }}</label>
                                                <input type="text" id="colorPickerTitle"
                                                    value="{{ $custom_styles['title_color'] ?? '#000000' }}" />
                                            </div>
                                            <div class="col-12 col-md-4 mb-2">
                                                <label class="form-label"
                                                    for="colorPickerSubTitle">{{ __('Subtitle Color') }}</label>
                                                <input type="text" id="colorPickerSubTitle"
                                                    value="{{ $custom_styles['sub_title_color'] ?? '#666666' }}" />
                                            </div>
                                            <div class="col-12 col-md-4 mb-2">
                                                <label class="form-label"
                                                    for="colorPickerDescription">{{ __('Description Color') }}</label>
                                                <input type="text" id="colorPickerDescription"
                                                    value="{{ $custom_styles['description_color'] ?? '#333333' }}" />
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 2: Body & Typography --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Body & Typography') }}</h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Select your typeface, page background style, and headings color.') }}
                                            </p>
                                        </div>

                                        {{-- Fonts --}}
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Font Family') }}</label>
                                            <div class="form-selectgroup">
                                                @php
                                                    $fonts = [
                                                        'Pacifico',
                                                        'Poppins',
                                                        'Roboto',
                                                        'Inter',
                                                        'Arial',
                                                        'Times New Roman',
                                                        'Spartan',
                                                        'Rethink Sans',
                                                        'Outfit',
                                                        'Figtree',
                                                        'Teachers',
                                                        'Saira Stencil One',
                                                        'Gloock',
                                                        'Playfair Display',
                                                        'Cairo',
                                                        'Almarai',
                                                        'Tajawal',
                                                        'IBM Plex Sans Arabic',
                                                        'Readex Pro',
                                                    ];
                                                    $fontClasses = [
                                                        'Pacifico' => 'pacifico',
                                                        'Poppins' => 'poppins',
                                                        'Roboto' => 'roboto',
                                                        'Inter' => 'inter',
                                                        'Arial' => 'arial',
                                                        'Times New Roman' => 'times-new-roman',
                                                        'Spartan' => 'league-spartan',
                                                        'Rethink Sans' => 'rethink-sans',
                                                        'Outfit' => 'outfit',
                                                        'Figtree' => 'figtree',
                                                        'Teachers' => 'teachers',
                                                        'Saira Stencil One' => 'saira-stencil-one',
                                                        'Gloock' => 'gloock',
                                                        'Playfair Display' => 'playfair-display',
                                                        'Cairo' => 'cairo',
                                                        'Almarai' => 'almarai',
                                                        'Tajawal' => 'tajawal',
                                                        'IBM Plex Sans Arabic' => 'ibm-plex-sans-arabic',
                                                        'Readex Pro' => 'readex-pro',
                                                    ];
                                                @endphp
                                                @foreach ($fonts as $font)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="font" value="{{ $font }}"
                                                            onclick="updateCustomStyle('font')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['font_family'] ?? '') == $font ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label {{ $fontClasses[$font] ?? '' }}">
                                                            {{ $font }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Background Style --}}
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Background Style') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['single_color' => __('Single Color'), 'gradient' => __('Gradient'), 'image' => __('Image')] as $bgKey => $bgLabel)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="bg_style"
                                                            value="{{ $bgKey }}"
                                                            onclick="updateCustomStyle('bg_style'); toggleBackgroundStyle('{{ $bgKey }}')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['background_type'] ?? '') == $bgKey ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label">{{ $bgLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>

                                            {{-- Single Color --}}
                                            <div class="mt-3 {{ ($custom_styles['background_type'] ?? '') == 'single_color' ? '' : 'd-none' }}"
                                                id="bg-single-color">
                                                <label class="form-label"
                                                    for="colorPickerBackground">{{ __('Background Color') }}</label>
                                                <input type="text" id="colorPickerBackground"
                                                    value="{{ $custom_styles['background_color'] ?? '#ffffff' }}" />
                                            </div>

                                            {{-- Gradient --}}
                                            <div class="mt-3 {{ ($custom_styles['background_type'] ?? '') == 'gradient' ? '' : 'd-none' }}"
                                                id="bg-gradient">
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label"
                                                            for="colorPickerBackgroundStart">{{ __('Gradient From Color') }}</label>
                                                        <input type="text" id="colorPickerBackgroundStart"
                                                            value="{{ $custom_styles['gradient_start'] ?? '#ffffff' }}" />
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label"
                                                            for="colorPickerBackgroundEnd">{{ __('Gradient To Color') }}</label>
                                                        <input type="text" id="colorPickerBackgroundEnd"
                                                            value="{{ $custom_styles['gradient_end'] ?? '#000000' }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Image --}}
                                            <div class="mt-3 {{ ($custom_styles['background_type'] ?? '') == 'image' ? '' : 'd-none' }}"
                                                id="bg-image">
                                                <label class="form-label"
                                                    for="background_image">{{ __('Background Image') }}</label>
                                                <input type="file" class="form-control" name="background_image"
                                                    id="background_image" accept=".jpeg,.png,.jpg,.svg"
                                                    onchange="updateCustomStyle('background_image')">
                                                <small class="form-hint text-muted mt-1 d-block"><i
                                                        class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '1080 x 1920 px']) }}</small>
                                            </div>
                                        </div>

                                        {{-- Heading Color & Card Style --}}
                                        <div class="row g-2">
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label"
                                                    for="colorPickerHeading">{{ __('Heading Color') }}</label>
                                                <input type="text" id="colorPickerHeading"
                                                    value="{{ $custom_styles['heading_color'] ?? '#000000' }}" />
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label">{{ __('Card Corner Style') }}</label>
                                                <div class="form-selectgroup">
                                                    @foreach (['square', 'rounded'] as $cardEdge)
                                                        <label class="form-selectgroup-item">
                                                            <input type="radio" name="card_edge"
                                                                value="{{ $cardEdge }}"
                                                                onclick="updateCustomStyle('card_edge')"
                                                                class="form-selectgroup-input"
                                                                {{ ($custom_styles['card_edge'] ?? '') == $cardEdge ? 'checked' : '' }}>
                                                            <span
                                                                class="form-selectgroup-label text-capitalize">{{ __($cardEdge) }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Slider Speed (custom) --}}
                                        <div class="mb-2">
                                            <label class="form-label" for="sliderSpeed">{{ __('Slider Speed (seconds)') }}</label>
                                            <input type="number" id="sliderSpeed" min="1" max="30" step="0.5"
                                                value="{{ $custom_styles['slider_speed'] ?? '2.5' }}" class="form-control"
                                                onchange="updateCustomStyle('slider_speed')"
                                                onkeyup="updateCustomStyle('slider_speed')" />
                                            <small
                                                class="form-hint text-muted">{{ __('Controls the auto-scroll speed of all sliders (Services, Products, Gallery, Testimonials)') }}</small>
                                        </div>
                                    </div>

                                    {{-- Title Typography (custom) --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Title 1') }}</h4>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="titleFontSize">{{ __('Title Font Size (px)') }}</label>
                                            <input type="number" id="titleFontSize" min="10" max="100"
                                                value="{{ $custom_styles['title_font_size'] ?? '36' }}" class="form-control"
                                                onchange="updateCustomStyle('title_font_size')"
                                                onkeyup="updateCustomStyle('title_font_size')" />
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Title Alignment') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['left', 'center', 'right'] as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="title_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('title_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['title_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Title in another language Typography (custom) --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Title in another language') }}</h4>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="colorPickerTitle2">{{ __('Title in another language Color') }}</label>
                                            <input type="text" id="colorPickerTitle2"
                                                value="{{ $custom_styles['title2_color'] ?? $custom_styles['title_color'] ?? '#000000' }}" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="title2FontSize">{{ __('Title in another language Font Size (px)') }}</label>
                                            <input type="number" id="title2FontSize" min="10" max="100"
                                                value="{{ $custom_styles['title2_font_size'] ?? '36' }}" class="form-control"
                                                onchange="updateCustomStyle('title2_font_size')"
                                                onkeyup="updateCustomStyle('title2_font_size')" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="title2_font">{{ __('Title in another language Font') }}</label>
                                            <select name="title2_font" id="title2_font" class="form-select"
                                                onchange="updateCustomStyle('title2_font_family')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}"
                                                        {{ ($custom_styles['title2_font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Title in another language Alignment') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['left', 'center', 'right'] as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="title2_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('title2_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['title2_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Subtitle Typography (custom) --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Subtitle 1') }}</h4>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="subTitleFontSize">{{ __('Subtitle Font Size (px)') }}</label>
                                            <input type="number" id="subTitleFontSize" min="10" max="100"
                                                value="{{ $custom_styles['sub_title_font_size'] ?? '18' }}" class="form-control"
                                                onchange="updateCustomStyle('sub_title_font_size')"
                                                onkeyup="updateCustomStyle('sub_title_font_size')" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="subtitle_font">{{ __('Subtitle Font') }}</label>
                                            <select name="subtitle_font" id="subtitle_font" class="form-select"
                                                onchange="updateCustomStyle('subtitle_font_family')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}"
                                                        {{ ($custom_styles['subtitle_font_family'] ?? $custom_styles['font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Subtitle Alignment') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['left', 'center', 'right'] as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="subtitle_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('subtitle_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['subtitle_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Subtitle in another language Typography (custom) --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Subtitle in another language') }}</h4>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="colorPickerSubTitle2">{{ __('Subtitle in another language Color') }}</label>
                                            <input type="text" id="colorPickerSubTitle2"
                                                value="{{ $custom_styles['subtitle2_color'] ?? $custom_styles['sub_title_color'] ?? '#000000' }}" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="subtitle2FontSize">{{ __('Subtitle in another language Font Size (px)') }}</label>
                                            <input type="number" id="subtitle2FontSize" min="10" max="100"
                                                value="{{ $custom_styles['subtitle2_font_size'] ?? '18' }}" class="form-control"
                                                onchange="updateCustomStyle('subtitle2_font_size')"
                                                onkeyup="updateCustomStyle('subtitle2_font_size')" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="subtitle2_font">{{ __('Subtitle in another language Font') }}</label>
                                            <select name="subtitle2_font" id="subtitle2_font" class="form-select"
                                                onchange="updateCustomStyle('subtitle2_font_family')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}"
                                                        {{ ($custom_styles['subtitle2_font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Subtitle in another language Alignment') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['left', 'center', 'right'] as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="subtitle2_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('subtitle2_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['subtitle2_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 3: Button Styles --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Button Styles') }}</h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Design call-to-action buttons, background gradients, and icon colors.') }}
                                            </p>
                                        </div>

                                        {{-- Button Background Type --}}
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Button Background Type') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['single_color' => __('Single Color'), 'gradient' => __('Gradient')] as $btnBgKey => $btnBgLabel)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="button_bg_style"
                                                            value="{{ $btnBgKey }}"
                                                            onclick="updateCustomStyle('button_bg_style'); toogleButtonBackgroundStyle('{{ $btnBgKey }}')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['button_background_type'] ?? '') == $btnBgKey ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label">{{ $btnBgLabel }}</span>
                                                    </label>
                                                @endforeach
                                            </div>

                                            {{-- Button Single Color --}}
                                            <div class="mt-3 {{ ($custom_styles['button_background_type'] ?? '') == 'single_color' ? '' : 'd-none' }}"
                                                id="buton-bg-single-color">
                                                <label class="form-label"
                                                    for="colorPickerButtonBackground">{{ __('Button Background Color') }}</label>
                                                <input type="text" id="colorPickerButtonBackground"
                                                    value="{{ $custom_styles['button_background_color'] ?? '#0284c7' }}" />
                                            </div>

                                            {{-- Button Gradient --}}
                                            <div class="mt-3 {{ ($custom_styles['button_background_type'] ?? '') == 'gradient' ? '' : 'd-none' }}"
                                                id="button-bg-gradient">
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label"
                                                            for="colorPickerButtonBackgroundStart">{{ __('Gradient From Color') }}</label>
                                                        <input type="text" id="colorPickerButtonBackgroundStart"
                                                            value="{{ $custom_styles['button_gradient_start'] ?? '#0284c7' }}" />
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label"
                                                            for="colorPickerButtonBackgroundEnd">{{ __('Gradient To Color') }}</label>
                                                        <input type="text" id="colorPickerButtonBackgroundEnd"
                                                            value="{{ $custom_styles['button_gradient_end'] ?? '#0369a1' }}" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Button Text & Icon Colors --}}
                                        <div class="row g-2">
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label"
                                                    for="colorPickerButtonText">{{ __('Button Text Color') }}</label>
                                                <input type="text" id="colorPickerButtonText"
                                                    value="{{ $custom_styles['button_text_color'] ?? '#ffffff' }}" />
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="form-label"
                                                    for="colorPickerButtonIcon">{{ __('Button Icon Color') }}</label>
                                                <input type="text" id="colorPickerButtonIcon"
                                                    value="{{ $custom_styles['button_icon_color'] ?? '#ffffff' }}" />
                                            </div>
                                        </div>

                                        {{-- Button Edge --}}
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Button Edge Style') }}</label>
                                            <div class="form-selectgroup">
                                                @foreach (['rounded', 'rectangle'] as $buttonEdge)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="button_edge"
                                                            value="{{ $buttonEdge }}"
                                                            onclick="updateCustomStyle('button_edge')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['button_edge'] ?? '') == $buttonEdge ? 'checked' : '' }}>
                                                        <span
                                                            class="form-selectgroup-label text-capitalize">{{ __($buttonEdge) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section 4: Bottom Navigation Bar --}}
                                    <div class="customization-section-card">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Bottom Navigation Bar') }}</h4>
                                            <p class="text-muted small mb-0">
                                                {{ __('Set the background color of the fixed mobile bottom bar.') }}</p>
                                        </div>
                                        <div>
                                            <label class="form-label"
                                                for="colorPickerBottomBar">{{ __('Bottom Bar Color') }}</label>
                                            <input type="text" id="colorPickerBottomBar"
                                                value="{{ $custom_styles['bottom_bar_color'] ?? '#ffffff' }}" />
                                        </div>
                                    </div>

                                    {{-- Section 5: Copyright (custom) --}}
                                    <div class="customization-section-card mb-0">
                                        <div class="mb-3">
                                            <h4 class="fw-bold text-dark mb-1">{{ __('Copyright') }}</h4>
                                        </div>
                                        <div>
                                            <label class="form-label"
                                                for="colorPickerCopyright">{{ __('Copyright Text Color') }}</label>
                                            <input type="text" id="colorPickerCopyright"
                                                value="{{ $custom_styles['copyright_color'] ?? $custom_styles['title_color'] ?? '#000000' }}" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Column: Sticky Live Mockup Preview --}}
                                <div class="col-12 col-xl-5 p-3 p-lg-4 bg-light-subtle">
                                    <div class="preview-sticky-wrapper">
                                        <h4 class="fw-bold text-dark mb-3 d-flex align-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary"
                                                width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z" />
                                                <path d="M11 4h2" />
                                                <path d="M12 17v.01" />
                                            </svg>
                                            {{ __('Live Mockup Preview') }}
                                        </h4>

                                        <div id="iphone-x">
                                            <div class="device device-iphone-x d-flex justify-content-center">
                                                <div class="device-frame">
                                                    <div class="device-content">
                                                        <iframe id="vcardPreview"
                                                            src="{{ route('user.view.preview', ['id' => $business_card->card_id]) }}"
                                                            frameborder="0"></iframe>
                                                    </div>
                                                    <div class="device-stripe"></div>
                                                    <div class="device-header"></div>
                                                    <div class="device-sensors"></div>
                                                    <div class="device-btns"></div>
                                                    <div class="device-power"></div>
                                                    <div class="device-home"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    <script src="{{ asset('js/spectrum.min.js') }}"></script>
    <script>
        "use strict";

        $(document).ready(function() {
            function initSpectrum(selector, styleKey) {
                $(selector).spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $(selector).val(hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $(selector).val(hex);
                        updateCustomStyle(styleKey);
                    }
                });
            }

            initSpectrum('#colorPickerTitle', 'title_color');
            initSpectrum('#colorPickerSubTitle', 'sub_title_color');
            initSpectrum('#colorPickerDescription', 'description_color');
            initSpectrum('#colorPickerBackground', 'background_color');
            initSpectrum('#colorPickerBackgroundStart', 'gradient_background_color');
            initSpectrum('#colorPickerBackgroundEnd', 'gradient_background_color');
            initSpectrum('#colorPickerHeading', 'heading_color');
            initSpectrum('#colorPickerButtonBackground', 'button_background_color');
            initSpectrum('#colorPickerButtonBackgroundStart', 'button_gradient_background_color');
            initSpectrum('#colorPickerButtonBackgroundEnd', 'button_gradient_background_color');
            initSpectrum('#colorPickerButtonText', 'button_text_color');
            initSpectrum('#colorPickerButtonIcon', 'button_icon_color');
            initSpectrum('#colorPickerBottomBar', 'bottom_bar_color');
            initSpectrum('#colorPickerTitle2', 'title2_color');
            initSpectrum('#colorPickerSubTitle2', 'subtitle2_color');
            initSpectrum('#colorPickerCopyright', 'copyright_color');
        });

        function updateCustomStyle(type) {
            const card_id = "{{ $business_card->card_id }}";
            var formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('card_id', card_id);
            formData.append('type', type);
            formData.append('title_color', $('#colorPickerTitle').val());
            formData.append('sub_title_color', $('#colorPickerSubTitle').val());
            formData.append('description_color', $('#colorPickerDescription').val());
            formData.append('layout', $('input[name="layout"]:checked').val());
            formData.append('profile_image_style', $('input[name="profile_image_style"]:checked').val());
            formData.append('font', $('select[name="font"]').val() || $('input[name="font"]:checked').val());
            formData.append('bg_style', $('input[name="bg_style"]:checked').val());
            formData.append('background_color', $('#colorPickerBackground').val());
            formData.append('gradient_from_color', $('#colorPickerBackgroundStart').val());
            formData.append('gradient_end_color', $('#colorPickerBackgroundEnd').val());

            var bgImgInput = $('#background_image')[0];
            if (bgImgInput && bgImgInput.files.length > 0) {
                formData.append('background_image', bgImgInput.files[0]);
            }

            formData.append('button_bg_style', $('input[name="button_bg_style"]:checked').val());
            formData.append('button_background_color', $('#colorPickerButtonBackground').val());
            formData.append('button_gradient_from_color', $('#colorPickerButtonBackgroundStart').val());
            formData.append('button_gradient_to_color', $('#colorPickerButtonBackgroundEnd').val());
            formData.append('button_text_color', $('#colorPickerButtonText').val());
            formData.append('button_icon_color', $('#colorPickerButtonIcon').val());
            formData.append('button_edge', $('input[name="button_edge"]:checked').val());
            formData.append('heading_color', $('#colorPickerHeading').val());
            formData.append('card_edge', $('input[name="card_edge"]:checked').val());
            formData.append('bottom_bar_color', $('#colorPickerBottomBar').val());

            // Custom: per-language title/subtitle typography, copyright color, slider speed
            formData.append('title_font_size', $('#titleFontSize').val());
            formData.append('sub_title_font_size', $('#subTitleFontSize').val());
            formData.append('title2_font_size', $('#title2FontSize').val());
            formData.append('subtitle2_font_size', $('#subtitle2FontSize').val());
            formData.append('title_alignment', $('input[name="title_alignment"]:checked').val());
            formData.append('title2_alignment', $('input[name="title2_alignment"]:checked').val());
            formData.append('subtitle_alignment', $('input[name="subtitle_alignment"]:checked').val());
            formData.append('subtitle2_alignment', $('input[name="subtitle2_alignment"]:checked').val());
            formData.append('title2_color', $('#colorPickerTitle2').val());
            formData.append('subtitle2_color', $('#colorPickerSubTitle2').val());
            formData.append('subtitle_font_family', $('select[name="subtitle_font"]').val());
            formData.append('title2_font_family', $('select[name="title2_font"]').val());
            formData.append('subtitle2_font_family', $('select[name="subtitle2_font"]').val());
            formData.append('copyright_color', $('#colorPickerCopyright').val());
            formData.append('slider_speed', $('#sliderSpeed').val());

            $.ajax({
                url: "{{ route('user.update.customization') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            }).done(function(res) {
                if (res.status == 'success') {
                    $('#vcardPreview')[0].contentWindow.location.reload();
                    $('#error-message').addClass('d-none');
                } else {
                    $('#status-message').html(res.message);
                    $('#error-message').removeClass('d-none');
                }
            });
        }

        function toggleBackgroundStyle(style) {
            if (style == 'single_color') {
                $('#bg-single-color').removeClass('d-none');
                $('#bg-gradient').addClass('d-none');
                $('#bg-image').addClass('d-none');
            } else if (style == 'gradient') {
                $('#bg-single-color').addClass('d-none');
                $('#bg-gradient').removeClass('d-none');
                $('#bg-image').addClass('d-none');
            } else {
                $('#bg-single-color').addClass('d-none');
                $('#bg-gradient').addClass('d-none');
                $('#bg-image').removeClass('d-none');
            }
        }

        function toogleButtonBackgroundStyle(style) {
            if (style == 'single_color') {
                $('#buton-bg-single-color').removeClass('d-none');
                $('#button-bg-gradient').addClass('d-none');
            } else {
                $('#buton-bg-single-color').addClass('d-none');
                $('#button-bg-gradient').removeClass('d-none');
            }
        }
    </script>
@endpush
