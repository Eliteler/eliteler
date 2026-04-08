@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

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

                {{-- Success of modal --}}
                <div id="successMessage" style="display:none;"
                    class="alert alert-important alert-success alert-dismissible mb-2">
                </div>

                {{-- Failed of modal --}}
                <div id="errorMessage" style="display:none;"
                    class="alert alert-important alert-danger alert-dismissible mb-2">
                </div>

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Advanced Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'advanced-settings',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <form action="{{ route('user.update.store.advanced.setting') }}" method="post" id="myForm"
                                class="card">
                                @csrf
                                {{-- Advanced settings --}}
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Advanced Settings') }}</h3>
                                </div>

                                <div class="card-body">
                                    <input type="hidden" class="form-control" name="store_id"
                                        value="{{ $business_card->card_id }}">

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
                                                            {{ __('Show this store publicly?') }}
                                                            <span class="form-help" data-bs-toggle="popover"
                                                                data-bs-placement="top"
                                                                data-bs-content="{{ __('This will shows your store publicly in the website directory.') }}"
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

                                    {{-- Advanced settings --}}
                                    @if ($plan_details->advanced_settings == 1)
                                        <div class="row">
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
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            @include('user.includes.footer')
        </div>
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
    @endpush
@endsection
