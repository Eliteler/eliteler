@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry for local input overrides) --}}
@section('css')
    <style>
        .form-control {
            border-radius: 2px !important;
        }

        .code {
            background: #333;
            color: #fff;
            font-family: monospace;
        }

        [data-bs-theme=light] {
            --tblr-border-radius: 7px !important;
        }

        .form-control {
            border-top-left-radius: 7px !important;
            border-bottom-left-radius: 7px !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
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
                                {{ __('Advanced Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Advanced Settings') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure custom styling sheets, secure dynamic password gates, and optimize SEO indexing metadata.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
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
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Form card wrapper upgraded to Borderless Shadowed Pattern -->
                        <form action="{{ route('user.save.advanced.setting', Request::segment(3)) }}" method="post"
                            enctype="multipart/form-data" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">

                                            <div class="row mb-3">
                                                @if ($plan_details->password_protected == 1)
                                                    {{-- Password protected toggle --}}
                                                    <div class="col-md-3">
                                                        <div class="mb-2">
                                                            <div class="form-label">{{ __('Disable Password Protection') }}
                                                            </div>
                                                            <label class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    onchange="displayPasswordProtected()"
                                                                    name="password_protected" id="password-protected"
                                                                    checked>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($plan_details->advanced_settings == 1)
                                                    {{-- Enable/Disable PWA toggle --}}
                                                    <div class="col-md-3">
                                                        <div class="mb-2">
                                                            <div class="form-label">{{ __('Enable PWA') }}</div>
                                                            <label class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="is_enable_pwa">
                                                            </label>
                                                        </div>
                                                    </div>

                                                    {{-- Enable/Disable Language Switcher toggle --}}
                                                    <div class="col-md-3">
                                                        <div class="mb-2">
                                                            <div class="form-label">{{ __('Enable Language Switcher') }}
                                                            </div>
                                                            <label class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="is_enable_language_switcher">
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="d-none mb-4" id="passwordProtectedForm">
                                                <h2 class="page-title mb-3">
                                                    {{ __('Set Password Protection') }}
                                                </h2>

                                                <!-- Password input -->
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label"
                                                            for="password">{{ __('Password') }}</label>
                                                        <div class="input-group input-group-flat">
                                                            <input type="password" class="form-control" name="password"
                                                                id="password" value="{{ old('password') }}" minlength="3"
                                                                maxlength="20" placeholder="{{ __('Enter password') }}">

                                                            <span class="input-group-text">
                                                                <a class="input-group-link cursor-pointer"
                                                                    onclick="showPassword()">
                                                                    <kbd>{{ __('Show Password') }}</kbd>
                                                                </a>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Advanced Settings Configurations --}}
                                            @if ($plan_details->advanced_settings == 1)
                                                <h2 class="page-title mb-3">
                                                    {{ __('Custom CSS / JS') }}
                                                </h2>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label"
                                                            for="custom_css">{{ __('Custom CSS') }}</label>
                                                        <textarea class="form-control code" name="custom_css" id="custom_css" rows="4" data-bs-toggle="autosize"
                                                            maxlength="25000" style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                            placeholder="{{ __('Enter custom CSS') }}">{{ old('custom_css') }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label"
                                                            for="custom_js">{{ __('Custom JS') }}</label>
                                                        <textarea class="form-control code" name="custom_js" id="custom_js" rows="4" data-bs-toggle="autosize"
                                                            maxlength="25000" style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                            placeholder="{{ __('Enter custom JS') }}">{{ old('custom_js') }}</textarea>
                                                    </div>
                                                </div>

                                                <h2 class="page-title mt-4 mb-3">
                                                    {{ __('SEO Metadata') }}
                                                </h2>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label"
                                                            for="profile_picture">{{ __('SEO Image') }}</label>
                                                        <input type="file" class="form-control" id="profile_picture"
                                                            placeholder="{{ __('Choose a SEO Image') }}"
                                                            name="profile_picture" accept=".jpeg,.jpg,.png,.gif,.svg" />
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="seo_title">{{ __('SEO Title') }}</label>
                                                        <input type="text" class="form-control" id="seo_title"
                                                            name="seo_title" placeholder="{{ __('Enter SEO Title') }}"
                                                            required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="seo_description">{{ __('SEO Description') }}</label>
                                                        <input type="text" class="form-control" id="seo_description"
                                                            name="seo_description"
                                                            placeholder="{{ __('Enter SEO Description') }}" required>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right-Aligned Card Footer Containing Standalone Submit Button -->
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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
        @include('user.includes.footer')
    </div>
@endsection

{{-- Password Protection JS Handler --}}
@push('custom-js')
    <script>
        function displayPasswordProtected() {
            "use strict";
            var disp = $('input[name="password_protected"]:checked').length;
            if (disp == 0) {
                $("#passwordProtectedForm").removeClass('d-none');
                $('#password').attr('required', 'required');
            } else {
                $("#passwordProtectedForm").addClass('d-none');
                $('#password').removeAttr('required');
            }
        }

        function showPassword() {
            "use strict";
            var password = document.getElementById("password");
            if (password.type === "password") {
                password.type = "text";
            } else {
                password.type = "password";
            }
        }
    </script>
@endpush
