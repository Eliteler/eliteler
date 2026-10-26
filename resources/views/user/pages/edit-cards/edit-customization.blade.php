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
        ];
    @endphp

    {{-- Google Fonts CDN --}}
    @foreach ($googleFonts as $font)
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $font) }}&display=swap"
            rel="stylesheet">
    @endforeach

    {{-- Customization CSS --}}
    <link rel="stylesheet" href="{{ asset('css/customizations.css') }}">

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
                                    <div class="customization-section-card mb-0">
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
            formData.append('font', $('input[name="font"]:checked').val());
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
