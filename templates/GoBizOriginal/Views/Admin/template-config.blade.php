@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    {{-- CodeMirror Core CSS Link --}}
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/codemirror.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/codemirror/css/dracula.min.css') }}">
    <style>
        .ts-control>input {
            display: contents !important;
        }

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

        /* CodeMirror Editor Height & Custom Styling */
        .CodeMirror {
            height: 180px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            padding: 4px;
            border: 1px solid #e6e8ec;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumbs -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-templates') }}" class="text-secondary text-decoration-none">
                                    {{ __('Web Templates') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-template.gobiz-original') }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('GoBiz Original') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Settings') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Customize template presentation colors, landing page slider configurations, announcement alerts, dynamic popups, and app banner links.') }}
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
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

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

                <div class="row row-cards">
                    {{-- Form 1: Core Theme & Template Configurations --}}
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm mb-4">
                            <form action="{{ route('admin.web-template.gobiz-original.update-config') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Theme Colors Input --}}
                                        <div class="col-12">
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
                                                                    value="{{ $value }}"
                                                                    class="form-colorinput-input"
                                                                    {{ $template_config->template_color === $value ? 'checked' : '' }} />

                                                                <span
                                                                    class="form-colorinput-color rounded-circle {{ !$isHex ? $color : '' }}"
                                                                    @if ($isHex) style="background-color: {{ $color }};" @endif></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Themes Slider Selector --}}
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="theme_slider">{{ __('Show themes slider in home page?') }}</label>
                                                <select name="theme_slider" id="theme_slider" class="form-select" required>
                                                    <option value="" disabled>{{ __('Choose an option') }}</option>
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
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Banner Image') }}</label>
                                                <input type="file" class="form-control" name="banner_image"
                                                    accept=".png,.jpg,.jpeg,.gif,.webp,.svg" />
                                                <small
                                                    class="text-muted d-block mt-1">{{ __('Recommended dimension: 1000 x 667 px') }}</small>
                                            </div>
                                        </div>

                                        {{-- Signup/Signin Image --}}
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Signup / Signin Image') }}</label>
                                                <input type="file" class="form-control" name="auth_image"
                                                    accept=".png,.jpg,.jpeg,.gif,.webp,.svg" />
                                                <small
                                                    class="text-muted d-block mt-1">{{ __('Recommended dimension: 486 x 605 px') }}</small>
                                            </div>
                                        </div>

                                        {{-- Custom CSS --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Custom CSS') }}</label>
                                                <textarea id="custom_css_editor" class="form-control code font-mono text-xs" name="custom_css" rows="5"
                                                    maxlength="25000" placeholder="{{ __('Enter custom CSS styling resets') }}">{{ $template_config->custom_css }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Custom JS --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Custom JS') }}</label>
                                                <textarea id="custom_js_editor" class="form-control code font-mono text-xs" name="custom_js" rows="5"
                                                    maxlength="25000" placeholder="{{ __('Enter custom dynamic javascript actions') }}">{{ $template_config->custom_js }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Right-Aligned Card Footer (Omitted Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <!-- Device Floppy Icon -->
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

                    {{-- Form 2: Mobile Application Action Banner --}}
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm mb-4">
                            <form method="POST"
                                action="{{ route('admin.web-template.gobiz-original.app-action-banner') }}">
                                @csrf
                                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                    <h3 class="card-title fw-bold m-0">{{ __('Mobile Application Action Banner') }}</h3>
                                </div>
                                <div class="card-body px-4 pb-4">
                                    <div class="row g-3">
                                        {{-- Toggle Visibility switch --}}
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="app_action">{{ __('Display Action Banner?') }}</label>
                                                <select name="app_action" id="app_action" class="form-select" required>
                                                    <option value="" disabled>{{ __('Choose visibility') }}</option>
                                                    <option value="1"
                                                        {{ $template_config->app_action == 1 ? 'selected' : '' }}>
                                                        {{ __('Yes') }}</option>
                                                    <option value="0"
                                                        {{ $template_config->app_action == 0 ? 'selected' : '' }}>
                                                        {{ __('No') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-8"></div>

                                        {{-- App Heading --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('App Heading') }}</label>
                                                <input type="text" class="form-control" name="app_heading"
                                                    value="{{ $template_config->app_heading }}"
                                                    placeholder="{{ __('e.g., Your Business, In Your Pocket') }}"
                                                    required>
                                            </div>
                                        </div>

                                        {{-- App Description --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('App Description') }}</label>
                                                <textarea class="form-control" name="app_description" rows="2"
                                                    placeholder="{{ __('e.g., Control your business cards, store, and NFC tools from a single mobile app.') }}"
                                                    maxlength="500" required>{{ $template_config->app_description }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Google Play Store Link --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Google Play Store Link') }}</label>
                                                <input type="url" class="form-control" name="google_play_store_link"
                                                    value="{{ $template_config->google_play_store_link }}"
                                                    placeholder="{{ __('https://play.google.com/store/apps/details?id=...') }}">
                                            </div>
                                        </div>

                                        {{-- Apple App Store Link --}}
                                        <div class="col-12 col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Apple App Store Link') }}</label>
                                                <input type="url" class="form-control" name="apple_app_store_link"
                                                    value="{{ $template_config->apple_app_store_link }}"
                                                    placeholder="{{ __('https://apps.apple.com/us/app/...') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

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

                    {{-- Form 3: Announcement Bar Settings --}}
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm mb-4">
                            <form method="POST"
                                action="{{ route('admin.web-template.gobiz-original.update-announcements') }}">
                                @csrf

                                @php
                                    $config = $announcements->announcement_configuration ?? [];
                                    $items = $config['items'] ?? [];
                                @endphp

                                <div
                                    class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pt-4 px-4 pb-0">
                                    <div class="ms-1">
                                        <h3 class="card-title fw-bold mb-1">{{ __('Announcement Bar Settings') }}</h3>
                                        <small class="text-muted d-block">
                                            {{ __('Configuring parameters establishes style parameters across all active announcements') }}
                                        </small>
                                    </div>

                                    <div class="d-flex gap-3 align-items-center">
                                        <button type="button"
                                            class="btn btn-primary btn-md btn-icon-only d-inline-flex align-items-center"
                                            id="addAnnouncement">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-plus me-1" width="20"
                                                height="20" viewBox="0 0 24 24" stroke-width="2.5"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M12 5v14" />
                                                <path d="M5 12h14" />
                                            </svg>
                                            <span class="d-none d-sm-inline">{{ __('Add') }}</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="card-body px-4 pb-4 border-bottom">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label required">{{ __('Background Color') }}</label>
                                            <input type="color" name="announcement_bg"
                                                value="{{ $config['bg_color'] ?? '#000000' }}"
                                                class="form-control form-control-color w-100" required>
                                        </div>

                                        <div class="col-12 col-md-3">
                                            <label class="form-label required">{{ __('Text Color') }}</label>
                                            <input type="color" name="announcement_text_color"
                                                value="{{ $config['text_color'] ?? '#ffffff' }}"
                                                class="form-control form-control-color w-100" required>
                                        </div>

                                        <div class="col-12 col-md-3">
                                            <label class="form-label"
                                                for="announcement_marquee">{{ __('Marquee Effect') }}</label>
                                            <select name="announcement_marquee" id="announcement_marquee"
                                                class="form-select">
                                                <option value="1" {{ !empty($config['marquee']) ? 'selected' : '' }}>
                                                    {{ __('Enable') }}</option>
                                                <option value="0" {{ empty($config['marquee']) ? 'selected' : '' }}>
                                                    {{ __('Disable') }}</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-3">
                                            <label class="form-label"
                                                for="announcement_marquee_speed">{{ __('Marquee Speed') }}</label>
                                            <input type="number" class="form-control" name="announcement_marquee_speed"
                                                id="announcement_marquee_speed"
                                                value="{{ !empty($config['marquee_speed']) ? $config['marquee_speed'] : 150 }}"
                                                min="1" step="1">
                                        </div>
                                    </div>
                                </div>

                                {{-- Dynamic Active Announcements --}}
                                <div class="card-body px-4 pb-4 {{ count($items) === 0 ? 'd-none' : '' }}"
                                    id="announcement_container">
                                    @foreach ($items as $index => $item)
                                        <div class="card shadow-none mb-3 announcement-item">
                                            <div class="card-body px-4 py-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong
                                                        class="text-uppercase small text-dark">{{ __('Announcement') }}
                                                        #{{ $index + 1 }}</strong>

                                                    <label class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="announcements[{{ $index }}][active]"
                                                            {{ $item['active'] ? 'checked' : '' }}>
                                                        <span
                                                            class="form-check-label font-weight-medium small text-secondary">
                                                            {{ __('Visible') }}
                                                        </span>
                                                    </label>
                                                </div>

                                                <textarea id="editor_{{ $index }}" class="announcement-editor form-control" maxlength="1000"
                                                    name="announcements[{{ $index }}][text]">{{ $item['text'] }}</textarea>

                                                <div class="text-end mt-2">
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-md btn-icon-only d-inline-flex align-items-center remove-announcement">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M4 7l16 0" />
                                                            <path d="M10 11l0 6" />
                                                            <path d="M14 11l0 6" />
                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                        </svg>
                                                        <span class="d-none d-sm-inline">{{ __('Remove') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="card-footer text-end">
                                    <button class="btn btn-primary btn-md d-inline-flex align-items-center"
                                        type="submit">
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

                    {{-- Dynamic HTML Template for Announcement additions --}}
                    <template id="announcement_template">
                        <div class="card shadow-none mb-3 announcement-item">
                            <div class="card-body px-4 py-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong class="text-uppercase small text-dark">{{ __('Announcement') }}</strong>

                                    <label class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" checked>
                                        <span
                                            class="form-check-label font-weight-medium small text-secondary">{{ __('Visible') }}</span>
                                    </label>
                                </div>

                                <textarea class="announcement-editor"></textarea>

                                <div class="text-end mt-2">
                                    <button type="button"
                                        class="btn btn-outline-danger btn-sm d-inline-flex align-items-center remove-announcement">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-trash me-1" width="18" height="18"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 7l16 0" />
                                            <path d="M10 11l0 6" />
                                            <path d="M14 11l0 6" />
                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                        </svg>
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Form 4: Popup Overlay Configuration --}}
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm mb-4">
                            <form method="POST" action="{{ route('admin.web-template.gobiz-original.update-popup') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                    <h3 class="card-title fw-bold m-0">{{ __('Overlay Splash Popups') }}</h3>
                                </div>

                                <div class="card-body px-4 pb-4">
                                    <div class="row g-3">
                                        {{-- Trigger Visibility status --}}
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="popup_status">{{ __('Display Modal Status') }}</label>
                                                <select name="popup_status" id="popup_status" class="form-select">
                                                    <option value="1"
                                                        {{ isset($popups['status']) && $popups['status'] == '1' ? 'selected' : '' }}>
                                                        {{ __('Yes') }}</option>
                                                    <option value="0"
                                                        {{ isset($popups['status']) && $popups['status'] == '0' ? 'selected' : '' }}>
                                                        {{ __('No') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Popup Image --}}
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Popup Image') }}</label>
                                                <input type="file" name="popup_image" class="form-control"
                                                    accept="image/*">

                                                @if (!empty($popups['image']))
                                                    <div class="mt-2">
                                                        <a href="{{ asset($popups['image']) }}" data-fslightbox="gallery"
                                                            class="d-block">
                                                            <img src="{{ asset($popups['image']) }}"
                                                                alt="Popup Image Preview"
                                                                style="max-width:200px; border-radius:6px;"
                                                                class="shadow-sm">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Optional Redirect Link --}}
                                        <div class="col-12 col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Popup Redirect Link') }}</label>
                                                <input type="url" name="popup_link" class="form-control"
                                                    placeholder="https://example.com"
                                                    value="{{ $popups['link'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button class="btn btn-primary btn-md d-inline-flex align-items-center"
                                        type="submit">
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    {{-- TinyMCE import moved cleanly to top of scripts section --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    {{-- Lightbox --}}
    <script src="{{ asset('js/fslightbox.js') }}"></script>
    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {
            var elementSelectors = ['theme_slider', 'announcement_marquee', 'popup_status', 'app_action'];

            // Initialize TomSelect configurations
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
                                return '<div><span class="dropdown-item-indicator">' + data
                                    .customProperties +
                                    '</span>' + escape(data.text) + '</div>';
                            }
                            return '<div>' + escape(data.text) + '</div>';
                        },
                        option: function(data, escape) {
                            if (data.customProperties) {
                                return '<div><span class="dropdown-item-indicator">' + data
                                    .customProperties +
                                    '</span>' + escape(data.text) + '</div>';
                            }
                            return '<div>' + escape(data.text) + '</div>';
                        },
                    },
                });

                el.addEventListener('change', function() {
                    if (el.value) {
                        el.setCustomValidity('');
                    } else {
                        el.setCustomValidity('This field is required.');
                    }
                });

                el.dispatchEvent(new Event('change'));
            }

            elementSelectors.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    initializeTomSelectWithRequired(el);
                }
            });
        });
    </script>

    {{-- TinyMCE Announcement Bar Management --}}
    <script>
        "use strict";

        let index = {{ count($announcements?->announcement_configuration['items'] ?? []) }};

        // Dark Mode
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

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
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? [
                    'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                    'dark'
                ] : [
                    'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                    'default'
                ],
                setup: function(editor) {
                    editor.on('init', function() {
                        if (document.body.dataset.bsTheme === 'dark') {
                            editor.getBody().setAttribute('data-bs-theme', 'dark');
                        }
                    });
                },
                content_style: isDark ? `
                body {
                        background-color: #151f2c !important;
                        font-family: "Figtree", sans-serif !important;
                    }

                    .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                        color: rgba(34,47,62,.5) !important;
                        opacity: 1 !important;
                        font-family: "Figtree", sans-serif !important;
                        font-size: 0.85rem !important;
                        font-weight: 600 !important;
                    }
                ` : `body {
                        font-family: "Figtree", sans-serif !important;
                    }

                    .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                        opacity: 1 !important;
                        font-family: "Figtree", sans-serif !important;
                        font-size: 0.85rem !important;
                        font-weight: 600 !important;
                    }
                `,
            });
        }

        // Initialize active editor containers
        document.querySelectorAll('.announcement-editor').forEach(el => {
            if (el.id) {
                initTiny(el.id);
            }
        });

        // Add dynamic elements
        document.getElementById('addAnnouncement').addEventListener('click', function() {
            const tpl = document.getElementById('announcement_template').content.cloneNode(true);
            const textarea = tpl.querySelector('textarea');
            const toggle = tpl.querySelector('.form-check-input');

            textarea.id = 'editor_' + index;
            textarea.name = `announcements[${index}][text]`;
            textarea.setAttribute('maxlength', '1000');
            toggle.name = `announcements[${index}][active]`;

            const container = document.getElementById('announcement_container');
            if (container.classList.contains('d-none')) {
                container.classList.remove('d-none');
            }

            container.appendChild(tpl);
            requestAnimationFrame(() => initTiny(textarea.id));
            index++;
        });

        // Remove dynamic announcement sets
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-announcement');
            if (!btn) return;

            const item = btn.closest('.announcement-item');
            const textarea = item.querySelector('textarea');

            if (textarea?.id && tinymce.get(textarea.id)) {
                tinymce.get(textarea.id).remove();
            }

            const container = document.getElementById('announcement_container');
            if (container.querySelectorAll('.announcement-item').length === 1) {
                container.classList.add('d-none');
            }

            item.remove();
        });
    </script>

    {{-- CodeMirror Core Scripts --}}
    <script src="{{ asset('plugins/codemirror/js/codemirror.min.js') }}"></script>
    {{-- CodeMirror Language Modes --}}
    <script src="{{ asset('plugins/codemirror/js/css.min.js') }}"></script>
    <script src="{{ asset('plugins/codemirror/js/javascript.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize CodeMirror editor for Custom CSS
            if (document.getElementById('custom_css_editor')) {
                var cssEditor = CodeMirror.fromTextArea(document.getElementById('custom_css_editor'), {
                    lineNumbers: true,
                    mode: 'css',
                    theme: 'dracula',
                    lineWrapping: true
                });
                // Auto-sync CodeMirror value to the underlying textarea
                cssEditor.on('change', function() {
                    cssEditor.save();
                });
            }

            // Initialize CodeMirror editor for Custom JS
            if (document.getElementById('custom_js_editor')) {
                var jsEditor = CodeMirror.fromTextArea(document.getElementById('custom_js_editor'), {
                    lineNumbers: true,
                    mode: 'javascript',
                    theme: 'dracula',
                    lineWrapping: true
                });
                // Auto-sync CodeMirror value to the underlying textarea
                jsEditor.on('change', function() {
                    jsEditor.save();
                });
            }
        });
    </script>
@endsection
