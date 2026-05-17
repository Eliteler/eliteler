@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    {{-- TinyMCE --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .form-colorinput-color {
            position: relative;
            display: inline-block;
        }

        .form-colorinput-color::after {
            content: "";
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 8.5l2.5 2.5l5.5 -5.5'/%3e%3c/svg%3e") center center / 1.1rem no-repeat;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-colorinput-input:checked+.form-colorinput-color::after {
            opacity: 1;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('Template Configuration') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

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

                <div class="row row-deck row-cards">
                    <div class="col-sm-12 col-lg-12">
                        {{-- Config Form --}}
                        <form action="{{ route('admin.web-template.gobiz-original.update-config') }}" method="post"
                            enctype="multipart/form-data" class="card">
                            @csrf
                            {{-- Card Body --}}
                            <div class="card-body">
                                <div class="row">
                                    {{-- Theme Colors --}}
                                    <div class="col-md-12 col-xl-12">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Template Color') }}</label>
                                            <div class="row g-2">
                                                @php
                                                    $themes = [
                                                        'blue' => 'bg-blue',
                                                        'indigo' => 'bg-indigo',
                                                        'green' => 'bg-green',
                                                        'yellow' => 'bg-yellow',
                                                        'red' => 'bg-red',
                                                        'purple' => 'bg-purple',
                                                        'pink' => 'bg-pink',
                                                        'gray' => 'bg-muted',
                                                        'slate' => '#0f172a',
                                                        'zinc' => '#18181b',
                                                        'neutral' => '#171717',
                                                        'stone' => '#1c1917',
                                                        'orange' => '#7c2d12',
                                                        'amber' => '#78350f',
                                                        'lime' => '#365314',
                                                        'emerald' => '#064e3b',
                                                        'teal' => '#134e4a',
                                                        'cyan' => '#164e63',
                                                        'sky' => '#0c4a6e',
                                                        'violet' => '#4c1d95',
                                                        'fuchsia' => '#701a75',
                                                        'rose' => '#881337',
                                                    ];
                                                @endphp

                                                @foreach ($themes as $value => $color)
                                                    @php
                                                        $isHex = str_starts_with($color, '#');
                                                    @endphp

                                                    <div class="col-auto">
                                                        <label class="form-colorinput form-colorinput-light"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{ ucfirst($value) }}">
                                                            <input type="radio" name="template_color"
                                                                value="{{ $value }}" class="form-colorinput-input"
                                                                {{ $template_config->template_color === $value ? 'checked' : '' }}
                                                                title="{{ ucfirst($value) }}" />

                                                            <span
                                                                class="form-colorinput-color rounded-circle {{ !$isHex ? $color : '' }}"
                                                                @if ($isHex) style="background-color: {{ $color }};" @endif
                                                                title="{{ ucfirst($value) }}"></span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Themes Slider on/off in home page --}}
                                    <div class="col-xl-4">
                                        <div class="mb-3">
                                            <label
                                                class="form-label required">{{ __('Show themes slider in home page?') }}</label>
                                            <select name="theme_slider" id="theme_slider" class="form-select theme_slider"
                                                required>
                                                <option value="1"
                                                    {{ $template_config->theme_slider == 1 ? 'selected' : '' }}>
                                                    {{ __('Yes') }}</option>
                                                <option value="0"
                                                    {{ $template_config->theme_slider == 0 ? 'selected' : '' }}>
                                                    {{ __('No') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Banner Image --}}
                                    <div class="col-xl-4">
                                        <div class="mb-3">
                                            <div class="form-label">{{ __('Banner Image') }}</div>
                                            <input type="file" class="form-control" name="banner_image"
                                                placeholder="{{ __('Banner Image') }}"
                                                accept=".png,.jpg,.jpeg,.gif,.webp,.svg" />
                                            <small class="text-muted">
                                                {{ __('Recommended size : 1000 x 667') }}</small>
                                        </div>
                                    </div>

                                    {{-- Signup/Signin Image --}}
                                    <div class="col-xl-4">
                                        <div class="mb-3">
                                            <div class="form-label">{{ __('Signup/Signin Image') }}</div>
                                            <input type="file" class="form-control" name="auth_image"
                                                placeholder="{{ __('Signup/Signin Image') }}"
                                                accept=".png,.jpg,.jpeg,.gif,.webp,.svg" />
                                            <small class="text-muted">
                                                {{ __('Recommended size : 486 x 605') }}</small>
                                        </div>
                                    </div>

                                    {{-- Custom CSS --}}
                                    <div class="col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Custom CSS') }}</label>
                                            <textarea class="form-control code" name="custom_css" rows="4" data-bs-toggle="autosize" maxlength="25000"
                                                style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                placeholder="{{ __('Custom CSS') }}">{{ $template_config->custom_css }}</textarea>
                                        </div>
                                    </div>

                                    {{-- Custom JS --}}
                                    <div class="col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Custom JS') }}</label>
                                            <textarea class="form-control code" name="custom_js" rows="4" data-bs-toggle="autosize" maxlength="25000"
                                                style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                placeholder="{{ __('Custom JS') }}">{{ $template_config->custom_js }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md ms-auto">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Mobile Application Action Banner --}}
                    <form method="POST" action="{{ route('admin.web-template.gobiz-original.app-action-banner') }}">
                        @csrf
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Mobile Application Action Banner') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 col-xl-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Mobile Application Action Banner') }}</label>
                                            <div class="form-select-wrapper">
                                                <select name="app_action" id="app_action" class="form-select app_action"
                                                    required>
                                                    <option value="1"
                                                        {{ $template_config->app_action == 1 ? 'selected' : '' }}>
                                                        {{ __('Yes') }}</option>
                                                    <option value="0"
                                                        {{ $template_config->app_action == 0 ? 'selected' : '' }}>
                                                        {{ __('No') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-xl-8"></div>

                                    {{-- App Heading --}}
                                    <div class="col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('App Heading') }}</label>
                                            <input type="text" class="form-control" name="app_heading"
                                                value="{{ $template_config->app_heading }}"
                                                placeholder="{{ __('Example') }}: {{ __('Your Business, In Your Pocket') }}" required>
                                        </div>
                                    </div>

                                    {{-- App Description --}}
                                    <div class="col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('App Description') }}</label>
                                            <textarea class="form-control" name="app_description"
                                                placeholder="{{ __('Example') }}: {{ __('Control your business cards, store, and NFC tools from a single mobile app. Stay connected and never miss an opportunity.') }}" required>{{ $template_config->app_description }}</textarea>
                                        </div>
                                    </div>

                                    {{-- Google Play Store Link --}}
                                    <div class="col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Google Play Store Link') }}</label>
                                            <input type="url" class="form-control" name="google_play_store_link"
                                                value="{{ $template_config->google_play_store_link }}"
                                                placeholder="{{ __('Example') }}: https://play.google.com/store/apps/details?id=com.nativecode.gobiz">
                                        </div>
                                    </div>

                                    {{-- Apple App Store Link --}}
                                    <div class="col-md-6 col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Apple App Store Link') }}</label>
                                            <input type="url" class="form-control" name="apple_app_store_link"
                                                value="{{ $template_config->apple_app_store_link }}"
                                                placeholder="{{ __('Example') }}: https://apps.apple.com/us/app/gobiz-vcard-saas/id1601234773">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md ms-auto">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Announcement Bar --}}
                    <form method="POST" action="{{ route('admin.web-template.gobiz-original.update-announcements') }}"
                        class="col-12 mb-1">
                        @csrf

                        @php
                            $config = $announcements->announcement_configuration ?? [];
                            $items = $config['items'] ?? [];
                        @endphp

                        <div class="card">
                            {{-- Header --}}
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="card-title mb-1">{{ __('Announcement Bar') }}</h3>
                                    <small class="text-muted">
                                        {{ __('General settings apply to all announcements') }}
                                    </small>
                                </div>

                                <div class="d-flex gap-3 align-items-center">
                                    {{-- Add Button --}}
                                    <button type="button" class="btn btn-primary btn-icon" id="addAnnouncement">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 5v14" />
                                            <path d="M5 12h14" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- General Settings --}}
                            <div class="card-body border-bottom">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label required">{{ __('Background Color') }}</label>
                                        <input type="color" name="announcement_bg"
                                            value="{{ $config['bg_color'] ?? '#000000' }}"
                                            class="form-control form-control-color" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label required">{{ __('Text Color') }}</label>
                                        <input type="color" name="announcement_text_color"
                                            value="{{ $config['text_color'] ?? '#ffffff' }}"
                                            class="form-control form-control-color" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Marquee') }}</label>
                                        <div class="form-select-wrapper">
                                            <select name="announcement_marquee" id="announcement_marquee"
                                                class="form-select announcement_marquee">
                                                <option value="1" {{ !empty($config['marquee']) ? 'selected' : '' }}>
                                                    {{ __('Enable') }}
                                                </option>
                                                <option value="0" {{ empty($config['marquee']) ? 'selected' : '' }}>
                                                    {{ __('Disable') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Marquee Speed --}}
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Marquee Speed') }}</label>
                                        <input type="number" class="form-control" name="announcement_marquee_speed"
                                            value="{{ !empty($config['marquee_speed']) ? $config['marquee_speed'] : 150 }}"
                                            min="1" step="1">
                                    </div>
                                </div>
                            </div>

                            {{-- Dynamic Announcements --}}
                            <div class="card-body {{ count($items) === 0 ? 'd-none' : '' }}" id="announcement_container">
                                {{-- Existing Announcements --}}
                                @foreach ($items as $index => $item)
                                    <div class="mb-3 announcement-item">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong>{{ __('Announcement') }} #{{ $index + 1 }}</strong>

                                                <label class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="announcements[{{ $index }}][active]"
                                                        {{ $item['active'] ? 'checked' : '' }}>
                                                    <span class="form-check-label">
                                                        {{ __('Visible') }}
                                                    </span>
                                                </label>
                                            </div>

                                            <textarea id="editor_{{ $index }}" class="announcement-editor"
                                                name="announcements[{{ $index }}][text]">{{ $item['text'] }}</textarea>

                                            <div class="text-end mt-2">

                                                <button type="button"
                                                    class="btn btn-outline-danger btn-icon remove-announcement">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Footer --}}
                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Template --}}
                    <template id="announcement_template">
                        <div class="card mb-3 announcement-item">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ __('Announcement') }}</strong>

                                    <label class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" checked>
                                        <span class="form-check-label">{{ __('Visible') }}</span>
                                    </label>
                                </div>

                                <textarea class="announcement-editor"></textarea>

                                <div class="text-end mt-2">
                                    <button type="button" class="btn btn-outline-danger btn-icon remove-announcement">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 7l16 0" />
                                            <path d="M10 11l0 6" />
                                            <path d="M14 11l0 6" />
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Popup --}}
                    <form method="POST" action="{{ route('admin.web-template.gobiz-original.update-popup') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Popup') }}</h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    {{-- Show popup --}}
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Show?') }}</label>
                                            <select name="popup_status" id="popup_status"
                                                class="form-select popup_status">
                                                <option value="1"
                                                    {{ isset($popups['status']) && $popups['status'] == '1' ? 'selected' : '' }}>
                                                    {{ __('Yes') }}
                                                </option>
                                                <option value="0"
                                                    {{ isset($popups['status']) && $popups['status'] == '0' ? 'selected' : '' }}>
                                                    {{ __('No') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Popup Image --}}
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Popup Image') }}</label>
                                            <input type="file" name="popup_image" class="form-control"
                                                accept="image/*">

                                            @if (!empty($popups['image']))
                                                <div class="mt-2">
                                                    {{-- fslightbox --}}
                                                    <a href="{{ asset($popups['image']) }}" data-fslightbox="gallery"
                                                        class="d-block">
                                                        <img src="{{ asset($popups['image']) }}" alt="Popup Image"
                                                            style="max-width:200px;border-radius:6px;">
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Optional Link --}}
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Popup Link') }}</label>
                                            <input type="url" name="popup_link" class="form-control"
                                                placeholder="https://example.com" value="{{ $popups['link'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @include('admin.includes.footer')
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    {{-- Lightbox --}}
    <script src="{{ asset('js/fslightbox.js') }}"></script>
    <script>
        // Array of element IDs
        var elementSelectors = ['theme_slider', 'announcement_marquee', 'popup_status', 'app_action'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });
    </script>

    {{-- Announcement Bar --}}
    <script>
        let index = {{ count($announcements?->announcement_configuration['items'] ?? []) }};

        function initTiny(id) {
            if (tinymce.get(id)) {
                tinymce.get(id).remove();
            }

            tinymce.init({
                selector: '#' + id,
                height: 120,
                menubar: false,
                statusbar: false,
                plugins: [
                    'lists',
                    'link',
                    'autolink',
                    'searchreplace',
                    'wordcount',
                    'charmap',
                    'directionality',
                    'emoticons',
                ],
                toolbar: `
                undo redo |
                bold italic underline strikethrough |
                alignleft aligncenter alignright alignjustify |
                bullist numlist |
                link |
                emoticons |
                ltr rtl |
                removeformat
            `,
                content_style: `
                body {
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                    line-height: 1.5;
                }
            `
            });
        }

        // Init existing editors
        document.querySelectorAll('.announcement-editor').forEach(el => {
            if (el.id) {
                initTiny(el.id);
            }
        });

        // Add new announcement
        document.getElementById('addAnnouncement').addEventListener('click', function() {
            const tpl = document.getElementById('announcement_template').content.cloneNode(true);
            const textarea = tpl.querySelector('textarea');
            const toggle = tpl.querySelector('.form-check-input');

            textarea.id = 'editor_' + index;
            textarea.name = `announcements[${index}][text]`;
            toggle.name = `announcements[${index}][active]`;

            // Show announcement container if hidden
            const container = document.getElementById('announcement_container');
            if (container.classList.contains('d-none')) {
                container.classList.remove('d-none');
            }

            document.getElementById('announcement_container').appendChild(tpl);

            requestAnimationFrame(() => initTiny(textarea.id));
            index++;
        });

        // Remove announcement
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-announcement');
            if (!btn) return;

            const item = btn.closest('.announcement-item');
            const textarea = item.querySelector('textarea');

            if (textarea?.id && tinymce.get(textarea.id)) {
                tinymce.get(textarea.id).remove();
            }

            // Hide announcement container if empty
            const container = document.getElementById('announcement_container');
            if (container.querySelectorAll('.announcement-item').length === 1) {
                container.classList.add('d-none');
            }

            item.remove();
        });
    </script>
@endsection
@endsection
