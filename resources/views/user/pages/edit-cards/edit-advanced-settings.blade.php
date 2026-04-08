@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
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
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Business Card') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'advanced',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <form action="{{ route('user.update.advanced.setting', Request::segment(3)) }}" method="post"
                                id="myForm" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title mb-4">{{ __('Advanced Settings') }}</h3>

                                    <div class="row">

                                        <div class="row mb-3">
                                            {{-- Directory Listing --}}
                                            @if (is_dir(base_path('plugins/Directory')))
                                                @php
                                                    $directory_settings = DB::table('directory_settings')->first();
                                                @endphp

                                                @if ($directory_settings && $directory_settings->directory == 1)
                                                    <div
                                                        class="col-md-3 col-xl-3 {{ $directory_settings->default_enable_directory_customers == 1 ? 'd-none' : '' }}">
                                                        <div class="mb-3">
                                                            <label class="form-label d-flex align-items-center gap-2">
                                                                {{ __('Show this vCard publicly?') }}
                                                                <span class="form-help" data-bs-toggle="popover"
                                                                    data-bs-placement="top"
                                                                    data-bs-content="{{ __('This will shows your vcard publicly in the website directory.') }}"
                                                                    data-bs-html="true">?</span>
                                                            </label>

                                                            <label class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="directory_listing" id="directory"
                                                                    {{ $directory_settings->default_enable_directory_customers == 1 || $business_card->directory_listing == 1 ? 'checked' : '' }}>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif

                                            @if ($plan_details->password_protected == 1)
                                                {{-- Password protected --}}
                                                <div class="col-md-3 col-xl-3">
                                                    <div class="mb-2">
                                                        <div class="form-label">{{ __('Disable Password Protection') }}
                                                        </div>
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                onchange="displayPasswordProtected()"
                                                                {{ $business_card->password == null ? 'checked' : '' }}
                                                                name="password_protected" id="password-protected">
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($plan_details->advanced_settings == 1)
                                                {{-- Enable/Disable PWA --}}
                                                <div class="col-md-3 col-xl-3">
                                                    <div class="mb-2">
                                                        <div class="form-label">{{ __('Enable PWA') }}
                                                        </div>
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                {{ $business_card->is_enable_pwa == 1 ? 'checked' : '' }}
                                                                name="is_enable_pwa">
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Enable/Disable Language Switcher --}}
                                                <div class="col-md-3 col-xl-3">
                                                    <div class="mb-2">
                                                        <div class="form-label">{{ __('Enable Language Switcher') }}
                                                        </div>
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                {{ $business_card->is_enable_language_switcher == 1 ? 'checked' : '' }}
                                                                name="is_enable_language_switcher">
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="{{ $business_card->password == null ? 'd-none' : '' }}"
                                            id="passwordProtectedForm">
                                            <h2 class="page-title mb-3">
                                                {{ __('Set Password Protection') }}
                                            </h2>

                                            <!-- Password -->
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-4">
                                                    <label class="form-label">{{ __('Password') }}</label>
                                                    <div class="input-group input-group-flat">
                                                        <input type="password" class="form-control no-left-border"
                                                            name="password" id="password"
                                                            value="{{ $business_card->password }}" minlength="3"
                                                            maxlength="20" placeholder="{{ __('Password') }}">

                                                        {{-- Show password --}}
                                                        <span class="input-group-text">
                                                            <a class="input-group-link"
                                                                onclick="showPassword()"><kbd>{{ __('Show Password') }}</kbd></a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Advanced settings --}}
                                        @if ($plan_details->advanced_settings == 1)
                                            <h2 class="page-title mb-3">
                                                {{ __('Custom CSS / JS') }}
                                            </h2>

                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Custom CSS') }}</label>
                                                    <textarea class="form-control code" name="custom_css" rows="4" data-bs-toggle="autosize" maxlength="25000"
                                                        style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                        placeholder="{{ __('Custom CSS') }}">{{ $business_card->custom_css }}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Custom JS') }}</label>
                                                    <textarea class="form-control code" name="custom_js" rows="4" data-bs-toggle="autosize" maxlength="25000"
                                                        style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                        placeholder="{{ __('Custom JS') }}">{{ $business_card->custom_js }}</textarea>
                                                </div>
                                            </div>

                                            {{-- SEO Configuration Settings --}}
                                            <h2 class="page-title mb-3">
                                                {{ __('SEO Configuration Settings') }}
                                            </h2>

                                            @php
                                                $seoConfig = isset($business_card->seo_configurations)
                                                    ? json_decode($business_card->seo_configurations)
                                                    : null;
                                            @endphp

                                            {{-- Favicon --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Favicon') }}</div>
                                                    <div>
                                                        <input type="file" class="form-control" name="favicon"
                                                            id="favicon" accept="image/*">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Meta title --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Meta Title') }}</div>
                                                    <div>
                                                        <input type="text" class="form-control" name="meta_title"
                                                            value="{{ $seoConfig->meta_title ?? '' }}"
                                                            placeholder="{{ __('Meta Title') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Meta description --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Meta Description') }}</div>
                                                    <div>
                                                        <input type="text" class="form-control"
                                                            name="meta_description"
                                                            value="{{ $seoConfig->meta_description ?? '' }}"
                                                            placeholder="{{ __('Meta Description') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Meta keywords --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Meta Keywords') }}</div>
                                                    <div>
                                                        <input type="text" class="form-control" name="meta_keywords"
                                                            value="{{ $seoConfig->meta_keywords ?? '' }}"
                                                            placeholder="{{ __('Meta Keywords') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.edit.section.title', Request::segment(3)) }}"
                                            class="btn btn-outline-primary ms-2">{{ __('Cancel') }}</a>
                                        <button type="submit"
                                            class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('user.includes.footer')
    </div>

    {{-- Password Protected --}}
    @push('custom-js')
        <script>
            function displayPasswordProtected() {
                "use strict";
                var disp = $('input[name="password_protected"]:checked').length;
                if (disp == 0) {
                    $("#passwordProtectedForm").removeAttr('class', 'd-none');;
                    $('#password').attr('required', 'required');
                } else {
                    $("#passwordProtectedForm").attr('class', 'd-none');;
                    $('#password').removeAttr('required', 'required');
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
@endsection
