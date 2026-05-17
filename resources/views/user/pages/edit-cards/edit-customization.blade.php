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

    {{-- Font Classes --}}
    <style>
        .poppins {
            font-family: 'Poppins', sans-serif;
        }

        .roboto {
            font-family: 'Roboto', sans-serif;
        }

        .inter {
            font-family: 'Inter', sans-serif;
        }

        .league-spartan {
            font-family: 'League Spartan', sans-serif;
        }

        .rethink-sans {
            font-family: 'Rethink Sans', sans-serif;
        }

        .outfit {
            font-family: 'Outfit', sans-serif;
        }

        .figtree {
            font-family: 'Figtree', sans-serif;
        }

        .teachers {
            font-family: 'Teachers', sans-serif;
        }

        .saira-stencil-one {
            font-family: 'Saira Stencil One', sans-serif;
        }

        .gloock {
            font-family: 'Gloock', serif;
        }

        .pacifico {
            font-family: 'Pacifico', cursive;
        }

        .playfair-display {
            font-family: 'Playfair Display', serif;
        }

        .arial {
            font-family: 'Arial', sans-serif;
        }

        .times-new-roman {
            font-family: 'Times New Roman', serif;
        }

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

    <link rel="stylesheet" href="{{ asset('css/mockup.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/spectrum.min.css') }}">
@endsection

@section('content')
    <div class="page-wrapper">
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

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-2 border-lg-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Business Card') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'customization',
                                    ])
                                </div>
                            </div>
                        </div>
                        @php
                            $custom_styles = json_decode($business_card->custom_styles, true);
                        @endphp
                        <div class="col-12 col-md-10">
                            <div class="alert alert-important alert-danger alert-dismissible mb-2 d-none" role="alert"
                                id="error-message">
                                <div class="d-flex">
                                    <div id="status-message">

                                    </div>
                                </div>
                                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>
                            <div class="row">
                                {{-- Customization Form --}}
                                                                <div class="col-12 col-md-7 p-4 border-lg-end">
                                    {{-- Title 1 Section --}}
                                    <h3 class="text-muted">{{ __('Title 1') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Title Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title Color') }}</div>
                                            <input type="text" id="colorPickerTitle"
                                                value="{{ $custom_styles['title_color'] ?? '#000000' }}" class="form-control" />
                                        </div>
                                        {{-- Title Font Size --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title Font Size (px)') }}</div>
                                            <input type="number" id="titleFontSize" min="10" max="100" value="{{ $custom_styles['title_font_size'] ?? '36' }}" class="form-control" onchange="updateCustomStyle('title_font_size')" onkeyup="updateCustomStyle('title_font_size')" />
                                        </div>
                                        {{-- Title Font --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title Font') }}</div>
                                            @php
                                                $fonts = ['Pacifico', 'Poppins', 'Roboto', 'Inter', 'Arial', 'Times New Roman', 'Spartan', 'Rethink Sans', 'Outfit', 'Figtree', 'Teachers', 'Saira Stencil One', 'Gloock', 'Playfair Display', 'Cairo', 'Almarai', 'Tajawal', 'IBM Plex Sans Arabic', 'Readex Pro'];
                                                $fontClasses = ['Pacifico' => 'pacifico', 'Poppins' => 'poppins', 'Roboto' => 'roboto', 'Inter' => 'inter', 'Arial' => 'arial', 'Times New Roman' => 'times-new-roman', 'Spartan' => 'spartan', 'Rethink Sans' => 'rethink-sans', 'Outfit' => 'outfit', 'Figtree' => 'figtree', 'Teachers' => 'teachers', 'Saira Stencil One' => 'saira-stencil-one', 'Gloock' => 'gloock', 'Playfair Display' => 'playfair-display', 'Cairo' => 'cairo', 'Almarai' => 'almarai', 'Tajawal' => 'tajawal', 'IBM Plex Sans Arabic' => 'ibm-plex-sans-arabic', 'Readex Pro' => 'readex-pro'];
                                            @endphp
                                            <select name="font" class="form-select" onchange="updateCustomStyle('font')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}" {{ ($custom_styles['font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Title Alignment --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title Alignment') }}</div>
                                            <div class="form-selectgroup">
                                                @php $alignments = ['left', 'center', 'right']; @endphp
                                                @foreach ($alignments as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="title_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('title_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['title_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Title in another language Section --}}
                                    <h3 class="text-muted">{{ __('Title in another language') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Title 2 Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title in another language Color') }}</div>
                                            <input type="text" id="colorPickerTitle2"
                                                value="{{ $custom_styles['title2_color'] ?? $custom_styles['title_color'] ?? '#000000' }}" class="form-control" />
                                        </div>
                                        {{-- Title 2 Font Size --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title in another language Font Size (px)') }}</div>
                                            <input type="number" id="title2FontSize" min="10" max="100" value="{{ $custom_styles['title2_font_size'] ?? '36' }}" class="form-control" onchange="updateCustomStyle('title2_font_size')" onkeyup="updateCustomStyle('title2_font_size')" />
                                        </div>
                                        {{-- Title 2 Font --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title in another language Font') }}</div>
                                            <select name="title2_font" class="form-select" onchange="updateCustomStyle('title2_font_family')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}" {{ ($custom_styles['title2_font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Title 2 Alignment --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Title in another language Alignment') }}</div>
                                            <div class="form-selectgroup">
                                                @foreach ($alignments as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="title2_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('title2_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['title2_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Subtitle 1 Section --}}
                                    <h3 class="text-muted">{{ __('Subtitle 1') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Subtitle Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle Color') }}</div>
                                            <input type="text" id="colorPickerSubTitle"
                                                value="{{ $custom_styles['sub_title_color'] ?? '#000000' }}" class="form-control" />
                                        </div>
                                        {{-- Subtitle Font Size --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle Font Size (px)') }}</div>
                                            <input type="number" id="subTitleFontSize" min="10" max="100" value="{{ $custom_styles['sub_title_font_size'] ?? '18' }}" class="form-control" onchange="updateCustomStyle('sub_title_font_size')" onkeyup="updateCustomStyle('sub_title_font_size')" />
                                        </div>
                                        {{-- Subtitle Font --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle Font') }}</div>
                                            <select name="subtitle_font" class="form-select" onchange="updateCustomStyle('subtitle_font_family')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}" {{ ($custom_styles['subtitle_font_family'] ?? $custom_styles['font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Subtitle Alignment --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle Alignment') }}</div>
                                            <div class="form-selectgroup">
                                                @foreach ($alignments as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="subtitle_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('subtitle_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['subtitle_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Subtitle in another language Section --}}
                                    <h3 class="text-muted">{{ __('Subtitle in another language') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Subtitle 2 Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle in another language Color') }}</div>
                                            <input type="text" id="colorPickerSubTitle2"
                                                value="{{ $custom_styles['subtitle2_color'] ?? $custom_styles['sub_title_color'] ?? '#000000' }}" class="form-control" />
                                        </div>
                                        {{-- Subtitle 2 Font Size --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle in another language Font Size (px)') }}</div>
                                            <input type="number" id="subtitle2FontSize" min="10" max="100" value="{{ $custom_styles['subtitle2_font_size'] ?? '18' }}" class="form-control" onchange="updateCustomStyle('subtitle2_font_size')" onkeyup="updateCustomStyle('subtitle2_font_size')" />
                                        </div>
                                        {{-- Subtitle 2 Font --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle in another language Font') }}</div>
                                            <select name="subtitle2_font" class="form-select" onchange="updateCustomStyle('subtitle2_font_family')">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" class="{{ $fontClasses[$font] ?? '' }}" {{ ($custom_styles['subtitle2_font_family'] ?? 'Poppins') == $font ? 'selected' : '' }}>
                                                        {{ $font }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Subtitle 2 Alignment --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Subtitle in another language Alignment') }}</div>
                                            <div class="form-selectgroup">
                                                @foreach ($alignments as $alignment)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="subtitle2_alignment" value="{{ $alignment }}"
                                                            onclick="updateCustomStyle('subtitle2_alignment')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['subtitle2_alignment'] ?? 'left') == $alignment ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($alignment) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Body Section --}}
                                    <h3 class="text-muted">{{ __('Body Section') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Profile Layout --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Profile Layout') }}</div>
                                            <div class="form-selectgroup">
                                                @php $layouts = ['row', 'column']; @endphp
                                                @foreach ($layouts as $layout)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="layout" value="{{ $layout }}"
                                                            onclick="updateCustomStyle('layout')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['layout'] ?? 'column') == $layout ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($layout) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        {{-- Profile Image Style --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Profile Image Style') }}</div>
                                            <div class="form-selectgroup">
                                                @php $profileImageStyles = ['circle', 'square', 'rounded']; @endphp
                                                @foreach ($profileImageStyles as $profileImageStyle)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="profile_image_style"
                                                            value="{{ $profileImageStyle }}"
                                                            onclick="updateCustomStyle('profile_image_style')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['profile_image_style'] ?? 'circle') == $profileImageStyle ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($profileImageStyle) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        {{-- Description Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Description Color') }}</div>
                                            <input type="text" id="colorPickerDescription"
                                                value="{{ $custom_styles['description_color'] ?? '#000000' }}" class="form-control" />
                                        </div>
                                        {{-- Background Styles --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Background Style') }}</div>
                                            @php $bg_styles = ['single_color', 'gradient', 'image']; @endphp
                                            <div class="form-selectgroup">
                                                @foreach ($bg_styles as $bg_style)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="bg_style" value="{{ $bg_style }}"
                                                            onclick="updateCustomStyle('bg_style'); toggleBackgroundStyle('{{ $bg_style }}')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['background_type'] ?? 'single_color') == $bg_style ? 'checked' : '' }}>
                                                        @php
                                                            $bgType = match ($bg_style) {
                                                                'single_color' => __('Single Color'),
                                                                'gradient' => __('Gradient'),
                                                                default => __('Image'),
                                                            };
                                                        @endphp
                                                        <span class="form-selectgroup-label">{{ __($bgType) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>

                                            {{-- Single Color Background --}}
                                            <div class="mt-4 {{ ($custom_styles['background_type'] ?? 'single_color') == 'single_color' ? '' : 'd-none' }}" id="bg-single-color">
                                                <div class="form-label">{{ __('Background Color') }}</div>
                                                <input type="text" id="colorPickerBackground" value="{{ $custom_styles['background_color'] ?? '#ffffff' }}" />
                                            </div>

                                            {{-- Gradient Background --}}
                                            <div class="mt-4 {{ ($custom_styles['background_type'] ?? 'single_color') == 'gradient' ? '' : 'd-none' }}" id="bg-gradient">
                                                <div class="d-flex gap-8">
                                                    <div>
                                                        <div class="form-label">{{ __('Gradient From Color') }}</div>
                                                        <input type="text" id="colorPickerBackgroundStart" value="{{ $custom_styles['gradient_start'] ?? '#ffffff' }}" />
                                                    </div>
                                                    <div>
                                                        <div class="form-label">{{ __('Gradient To Color') }}</div>
                                                        <input type="text" id="colorPickerBackgroundEnd" value="{{ $custom_styles['gradient_end'] ?? '#ffffff' }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Background Image --}}
                                            <div class="mt-4 {{ ($custom_styles['background_type'] ?? 'single_color') == 'image' ? '' : 'd-none' }}" id="bg-image">
                                                <div class="form-label">{{ __('Background Image') }}</div>
                                                <input type="file" class="form-control" name="background_image" id="background_image" accept=".jpeg,.png,.jpg,.svg" onchange="updateCustomStyle('background_image')">
                                                <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '1080 x 1920 px']) }}</small>
                                            </div>
                                        </div>
                                        {{-- Heading Color Background --}}
                                        <div class="my-4">
                                            <div class="form-label">{{ __('Heading Color') }}</div>
                                            <input type="text" id="colorPickerHeading" value="{{ $custom_styles['heading_color'] ?? '#000000' }}" />
                                        </div>
                                        {{-- Slider Speed --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Slider Speed (seconds)') }}</div>
                                            <input type="number" id="sliderSpeed" min="1" max="30" step="0.5"
                                                value="{{ $custom_styles['slider_speed'] ?? '2.5' }}"
                                                class="form-control"
                                                onchange="updateCustomStyle('slider_speed')"
                                                onkeyup="updateCustomStyle('slider_speed')" />
                                            <small class="form-hint text-muted">{{ __('Controls the auto-scroll speed of all sliders (Services, Products, Gallery, Testimonials)') }}</small>
                                        </div>

                                        {{-- Card Edge --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Card Style') }}</div>
                                            <div class="form-selectgroup">
                                                @php $cardEdges = ['square', 'rounded']; @endphp
                                                @foreach ($cardEdges as $cardEdge)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="card_edge" value="{{ $cardEdge }}"
                                                            onclick="updateCustomStyle('card_edge')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['card_edge'] ?? 'rounded') == $cardEdge ? 'checked' : '' }}>
                                                        <span class="form-selectgroup-label text-capitalize">{{ __($cardEdge) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Button Style --}}
                                    <h3 class="text-muted">{{ __('Button Styles') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Button Background Type --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Button Background Type') }}</div>
                                            @php $button_bg_styles = ['single_color', 'gradient']; @endphp
                                            <div class="form-selectgroup">
                                                @foreach ($button_bg_styles as $button_bg_style)
                                                    <label class="form-selectgroup-item">
                                                        <input type="radio" name="button_bg_style" value="{{ $button_bg_style }}"
                                                            onclick="updateCustomStyle('button_bg_style'); toogleButtonBackgroundStyle('{{ $button_bg_style }}')"
                                                            class="form-selectgroup-input"
                                                            {{ ($custom_styles['button_background_type'] ?? 'single_color') == $button_bg_style ? 'checked' : '' }}>
                                                        @php
                                                            $buttonBgType = match ($button_bg_style) {
                                                                'single_color' => __('Single Color'),
                                                                'gradient' => __('Gradient'),
                                                            };
                                                        @endphp
                                                        <span class="form-selectgroup-label">{{ __($buttonBgType) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>

                                            {{-- Button Background Color --}}
                                            <div class="mt-4 {{ ($custom_styles['button_background_type'] ?? 'single_color') == 'single_color' ? '' : 'd-none' }}" id="buton-bg-single-color">
                                                <div class="form-label">{{ __('Button Background Color') }}</div>
                                                <input type="text" id="colorPickerButtonBackground" value="{{ $custom_styles['button_background_color'] ?? '#000000' }}" />
                                            </div>

                                            {{-- Button Gradient Background Color --}}
                                            <div class="mt-4 {{ ($custom_styles['button_background_type'] ?? 'single_color') == 'gradient' ? '' : 'd-none' }}" id="button-bg-gradient">
                                                <div class="d-flex gap-8">
                                                    <div>
                                                        <div class="form-label">{{ __('Gradient From Color') }}</div>
                                                        <input type="text" id="colorPickerButtonBackgroundStart" value="{{ $custom_styles['button_gradient_start'] ?? '#000000' }}" />
                                                    </div>
                                                    <div>
                                                        <div class="form-label">{{ __('Gradient To Color') }}</div>
                                                        <input type="text" id="colorPickerButtonBackgroundEnd" value="{{ $custom_styles['button_gradient_end'] ?? '#000000' }}" />
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Button Icon Color Section --}}
                                            <div class="col-12 mt-4">
                                                <div class="form-label">{{ __('Button Icon Color') }}</div>
                                                <input type="text" id="colorPickerButtonIcon" value="{{ $custom_styles['button_icon_color'] ?? '#ffffff' }}" />
                                            </div>

                                            {{-- Button Text Color Section --}}
                                            <div class="col-12 mt-4">
                                                <div class="form-label">{{ __('Button Text Color') }}</div>
                                                <input type="text" id="colorPickerButtonText" value="{{ $custom_styles['button_text_color'] ?? '#ffffff' }}" />
                                            </div>

                                            {{-- Button Style Section --}}
                                            <div class="col-12">
                                                <div class="my-4">
                                                    <div class="form-label">{{ __('Button Edge') }}</div>
                                                    <div class="form-selectgroup">
                                                        @php $buttonEdges = ['rounded', 'rectangle']; @endphp
                                                        @foreach ($buttonEdges as $buttonEdge)
                                                            <label class="form-selectgroup-item">
                                                                <input type="radio" name="button_edge" value="{{ $buttonEdge }}"
                                                                    onclick="updateCustomStyle('button_edge')"
                                                                    class="form-selectgroup-input"
                                                                    {{ ($custom_styles['button_edge'] ?? 'rounded') == $buttonEdge ? 'checked' : '' }}>
                                                                <span class="form-selectgroup-label text-capitalize">{{ __($buttonEdge) }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom Bar --}}
                                    <h3 class="text-muted">{{ __('Bottom Bar') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Bottom Bar Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Bottom Bar Color') }}</div>
                                            <input type="text" id="colorPickerBottomBar" value="{{ $custom_styles['bottom_bar_color'] ?? '#ffffff' }}" />
                                        </div>
                                    </div>

                                    {{-- Copyright Section --}}
                                    <h3 class="text-muted">{{ __('Copyright') }}</h3>
                                    <div class="mb-5 border-bottom pb-4">
                                        {{-- Copyright Color --}}
                                        <div class="mb-4">
                                            <div class="form-label">{{ __('Copyright Text Color') }}</div>
                                            <input type="text" id="colorPickerCopyright"
                                                value="{{ $custom_styles['copyright_color'] ?? $custom_styles['title_color'] ?? '#000000' }}" class="form-control" />
                                        </div>
                                    </div>
                                </div>
{{-- Preview --}}
                                <div class="col-12 col-md-5 position-relative p-4">
                                    <h4 class="page-title">{{ __('Preview') }}</h4>
                                    <div id="iphone-x">
                                        <div
                                            class="device device-iphone-x d-flex justify-content-center ">
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
        @include('user.includes.footer')
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        <script src="{{ asset('js/spectrum.min.js') }}"></script>
        <script>
            // color picker
            $(document).ready(function() {
                "use strict";
                $('#colorPickerTitle').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerTitle').val(hex);
                        updateCustomStyle('title_color');
                    }
                });

                $('#colorPickerTitle2').spectrum({
                    preferredFormat: "hex", showInput: true, showPalette: false, allowEmpty: false, showAlpha: false, showButtons: false,
                    move: function(color) { $('#colorBox').css('background-color', color.toHexString()); },
                    change: function(color) { $('#colorPickerTitle2').val(color.toHexString()); updateCustomStyle('title2_color'); }
                });
                $('#colorPickerSubTitle2').spectrum({
                    preferredFormat: "hex", showInput: true, showPalette: false, allowEmpty: false, showAlpha: false, showButtons: false,
                    move: function(color) { $('#colorBox').css('background-color', color.toHexString()); },
                    change: function(color) { $('#colorPickerSubTitle2').val(color.toHexString()); updateCustomStyle('subtitle2_color'); }
                });


                $('#colorPickerSubTitle').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerSubTitle').val(hex);
                        updateCustomStyle('sub_title_color');
                    }
                });

                $('#colorPickerDescription').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerDescription').val(hex);
                        updateCustomStyle('description_color');
                    }
                });

                $('#colorPickerBackground').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerBackground').val(hex);
                        updateCustomStyle('background_color');
                    }
                });

                $('#colorPickerBackgroundStart').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerBackgroundStart').val(hex);
                        updateCustomStyle('gradient_background_color');
                    }
                });

                $('#colorPickerBackgroundEnd').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerBackgroundEnd').val(hex);
                        updateCustomStyle('gradient_background_color');
                    }
                });

                $('#colorPickerButtonBackground').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerButtonBackground').val(hex);
                        updateCustomStyle('button_background_color');
                    }
                });

                $('#colorPickerButtonBackgroundStart').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerButtonBackgroundStart').val(hex);
                        updateCustomStyle('button_gradient_background_color');
                    }
                });

                $('#colorPickerButtonBackgroundEnd').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerButtonBackgroundEnd').val(hex);
                        updateCustomStyle('button_gradient_background_color');
                    }
                });

                $('#colorPickerButtonText').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerButtonText').val(hex);
                        updateCustomStyle('button_text_color');
                    }
                });

                $('#colorPickerButtonIcon').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerButtonIcon').val(hex);
                        updateCustomStyle('button_icon_color');
                    }
                });

                $('#colorPickerHeading').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerHeading').val(hex);
                        updateCustomStyle('heading_color');
                    }
                });

                $('#colorPickerBottomBar').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerBottomBar').val(hex);
                        updateCustomStyle('bottom_bar_color');
                    }
                });

                $('#colorPickerCopyright').spectrum({
                    preferredFormat: "hex",
                    showInput: true,
                    showPalette: false,
                    allowEmpty: false,
                    showAlpha: false,
                    showButtons: false,
                    move: function(color) {
                        const hex = color.toHexString();
                        $('#colorBox').css('background-color', hex);
                    },
                    change: function(color) {
                        const hex = color.toHexString();
                        $('#colorPickerCopyright').val(hex);
                        updateCustomStyle('copyright_color');
                    }
                });
            });

            function updateCustomStyle(type) {
                "use strict";
                const card_id = "{{ $business_card->card_id }}";
                var title_color = $('#colorPickerTitle').val();
                var sub_title_color = $('#colorPickerSubTitle').val();
                var description_color = $('#colorPickerDescription').val();
                var layout = $('input[name="layout"]:checked').val();
                var profile_image_style = $('input[name="profile_image_style"]:checked').val();
                var font = $('select[name="font"]').val() || $('input[name="font"]:checked').val();
                var background_style = $('input[name="bg_style"]:checked').val();
                var background_color = $('#colorPickerBackground').val();
                var gradient_from_color = $('#colorPickerBackgroundStart').val();
                var gradient_end_color = $('#colorPickerBackgroundEnd').val();
                var background_image = $('#background_image')[0].files[0];
                var button_bg_style = $('input[name="button_bg_style"]:checked').val();
                var button_background_color = $('#colorPickerButtonBackground').val();
                var button_gradient_from_color = $('#colorPickerButtonBackgroundStart').val();
                var button_gradient_to_color = $('#colorPickerButtonBackgroundEnd').val();
                var button_text_color = $('#colorPickerButtonText').val();
                var button_icon_color = $('#colorPickerButtonIcon').val();
                var button_edge = $('input[name="button_edge"]:checked').val();
                var heading_color = $('#colorPickerHeading').val();
                var card_edge = $('input[name="card_edge"]:checked').val();
                var bottom_bar_color = $('#colorPickerBottomBar').val();
                var copyright_color = $('#colorPickerCopyright').val();
                var title_font_size = $('#titleFontSize').val();
                var sub_title_font_size = $('#subTitleFontSize').val();
                var title2_font_size = $('#title2FontSize').val();
                var subtitle2_font_size = $('#subtitle2FontSize').val();
                var title_alignment = $('input[name="title_alignment"]:checked').val();
                var title2_alignment = $('input[name="title2_alignment"]:checked').val();
                var subtitle_alignment = $('input[name="subtitle_alignment"]:checked').val();
                var subtitle2_alignment = $('input[name="subtitle2_alignment"]:checked').val();
                var slider_speed = $('#sliderSpeed').val();
                
                var title2_color = $('#colorPickerTitle2').val();
                var subtitle2_color = $('#colorPickerSubTitle2').val();
                var subtitle_font = $('select[name="subtitle_font"]').val() || $('input[name="subtitle_font"]:checked').val();

                var title2_font = $('select[name="title2_font"]').val() || $('input[name="title2_font"]:checked').val();
                var subtitle2_font = $('select[name="subtitle2_font"]').val() || $('input[name="subtitle2_font"]:checked').val();

                var formData = new FormData();
                formData.append('_token', "{{ csrf_token() }}");
                formData.append('card_id', card_id);
                formData.append('type', type);
                formData.append('title_color', title_color);
                formData.append('sub_title_color', sub_title_color);
                formData.append('description_color', description_color);
                formData.append('layout', layout);
                formData.append('profile_image_style', profile_image_style);
                formData.append('font', font);
                formData.append('bg_style', background_style);
                formData.append('background_color', background_color);
                formData.append('gradient_from_color', gradient_from_color);
                formData.append('gradient_end_color', gradient_end_color);
                formData.append('background_image', background_image);
                formData.append('button_bg_style', button_bg_style);
                formData.append('button_background_color', button_background_color);
                formData.append('button_gradient_from_color', button_gradient_from_color);
                formData.append('button_gradient_to_color', button_gradient_to_color);
                formData.append('button_text_color', button_text_color);
                formData.append('button_icon_color', button_icon_color);
                formData.append('button_edge', button_edge);
                formData.append('heading_color', heading_color);
                formData.append('card_edge', card_edge);
                formData.append('bottom_bar_color', bottom_bar_color);
                formData.append('title_font_size', title_font_size);
                formData.append('sub_title_font_size', sub_title_font_size);
                formData.append('title2_font_size', title2_font_size);
                formData.append('subtitle2_font_size', subtitle2_font_size);
                formData.append('title_alignment', title_alignment);
                formData.append('title2_alignment', title2_alignment);
                formData.append('subtitle_alignment', subtitle_alignment);
                formData.append('subtitle2_alignment', subtitle2_alignment);
                
                formData.append('title2_color', title2_color);
                formData.append('subtitle2_color', subtitle2_color);
                formData.append('subtitle_font_family', subtitle_font);

                formData.append('title2_font_family', title2_font);
                formData.append('subtitle2_font_family', subtitle2_font);
                formData.append('copyright_color', copyright_color);
                formData.append('slider_speed', slider_speed);

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

            // background style
            function toggleBackgroundStyle(style) {
                "use strict";
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

            // button background style
            function toogleButtonBackgroundStyle(style) {
                "use strict";
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
@endsection
