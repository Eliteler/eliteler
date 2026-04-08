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
    <!-- Page title -->
    <div class="page-header d-print-none">
        <div class="container-fluid">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Advanced Settings') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-fluid">
            {{-- Failed --}}
            @if(Session::has("failed"))
            <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('failed')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            {{-- Success --}}
            @if(Session::has("success"))
            <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('success')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif
            
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('user.save.advanced.setting', Request::segment(3)) }}" method="post" enctype="multipart/form-data"
                        class="card">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-12"> 
                                    <div class="row">

                                        <div class="row mb-3">
                                            @if ($plan_details->password_protected == 1)
                                                {{-- Password protected --}}
                                                <div class="col-md-3 col-xl-3">
                                                    <div class="mb-2">
                                                        <div class="form-label">{{ __('Disable Password Protection') }}
                                                        </div>
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                onchange="displayPasswordProtected()" name="password_protected" id="password-protected" checked>
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
                                                            <input class="form-check-input" type="checkbox" name="is_enable_pwa">
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Enable/Disable Language Switcher --}}
                                                <div class="col-md-3 col-xl-3">
                                                    <div class="mb-2">
                                                        <div class="form-label">{{ __('Enable Language Switcher') }}
                                                        </div>
                                                        <label class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" name="is_enable_language_switcher">
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-none" id="passwordProtectedForm">
                                            <h2 class="page-title mb-3">
                                                {{ __('Set Password Protection') }}
                                            </h2>

                                            <!-- Password -->
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-4">
                                                    <label class="form-label">{{ __('Password') }}</label>
                                                    <div class="input-group input-group-flat">
                                                        <input type="password" class="form-control" name="password"
                                                            id="password" value="{{ old('password') }}" minlength="3" maxlength="20"
                                                            placeholder="{{ __('Password') }}">

                                                        {{-- Show password --}}
                                                        <span class="input-group-text">
                                                            <a class="input-group-link" onclick="showPassword()"><kbd>{{ __('Show Password') }}</kbd></a>
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
                                                    <textarea class="form-control code" name="custom_css" rows="4"
                                                        data-bs-toggle="autosize" maxlength="25000" style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                        placeholder="{{ __('Custom CSS') }}">{{ old('custom_css') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Custom JS') }}</label>
                                                    <textarea class="form-control code" name="custom_js" rows="4"
                                                        data-bs-toggle="autosize" maxlength="25000" style="border-top-right-radius: 7px !important; border-bottom-right-radius: 7px !important;"
                                                        placeholder="{{ __('Custom JS') }}">{{ old('custom_js') }}</textarea>
                                                </div>
                                            </div>

                                            {{-- SEO Configuration Settings --}}
                                            <h2 class="page-title mb-3">
                                                {{ __('SEO Configuration Settings') }}
                                            </h2>

                                            {{-- Favicon --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label">{{ __('Favicon') }}</div>
                                                    <div>
                                                        <input type="file" class="form-control" name="favicon" id="favicon" accept="image/*">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Meta title --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Meta Title') }}</div>
                                                    <div>
                                                        <input type="text" class="form-control" name="meta_title"
                                                            placeholder="{{ __('Meta Title') }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Meta description --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Meta Description') }}</div>
                                                    <div>
                                                        <input type="text" class="form-control" name="meta_description" 
                                                            placeholder="{{ __('Meta Description') }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Meta keywords --}}
                                            <div class="col-md-6 col-xl-12">
                                                <div class="mb-3">
                                                    <div class="form-label required">{{ __('Meta Keywords') }}</div>
                                                    <div>
                                                        <input type="text" class="form-control" name="meta_keywords" 
                                                            placeholder="{{ __('Meta Keywords') }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <div class="d-flex">
                                {{-- Next link --}}
                                @php
                                    $route = route('user.cards');

                                    if (
                                        $plan_details->google_wallet == 1 &&
                                        is_dir(base_path('plugins/GoogleWallet'))
                                        && $business_card->type != 'personal'
                                    ) {
                                        $route = route('user.create.google-wallet', Request::segment(3));
                                    }
                                @endphp

                                <a href="{{ $route }}" class="btn btn-outline-primary ms-2">
                                    {{ __('Skip') }}
                                </a>

                                <button type="submit" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
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