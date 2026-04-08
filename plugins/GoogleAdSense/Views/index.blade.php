@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        /* container */
        .ads-group {
            display: flex;
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }

        /* prefix */
        .ads-prefix {
            padding: 8px 10px;
            background: #f3f4f6;
            border-right: 1px solid #d1d5db;

            /* key fix */
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* input */
        .ads-input-field {
            flex: 1;
            min-width: 0;
            /* critical fix */
            padding: 8px 10px;
            border: none;
            outline: none;
            width: 100%;
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
                        <h2 class="page-title mb-2">
                            {{ __('Google AdSense Settings') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('admin.plugins') }}" class="btn btn-primary text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l14 0" />
                                    <path d="M5 12l6 6" />
                                    <path d="M5 12l6 -6" />
                                </svg>
                                {{ __('Back') }}
                            </a>
                        </div>
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
                        <form action="{{ route('admin.google_adsense_settings.update') }}" method="post" class="card">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Google AdSence Credentials') }}</h4>
                            </div>
                            <div class="card-body">
                                {{-- Google Analytics --}}
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Google AdSense code') }}</label>

                                            <div class="ads-group">
                                                <span class="ads-prefix text-muted">
                                                    https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=
                                                </span>
                                                <input type="text" class="ads-input-field" name="google_adsense_code"
                                                    placeholder="{{ __('Google AdSense code') }}" autocomplete="off" required>
                                            </div>

                                            <small>{{ __('Type DISABLE_ADSENSE_ONLY for enable Webtools without AdSense') }}</small><br>
                                            <small>{{ __('Enter your AdSense code for enable Webtools with AdSense') }}</small><br>
                                            <small>{{ __('Type DISABLE_BOTH for disable Webtools & AdSense') }}</small><br>
                                        </div>

                                        <span>
                                            {{ __('If you did not get a Google AdSense code, create a') }}
                                            <a href="https://www.google.com/adsense/new" target="_blank">
                                                {{ __('new AdSense code.') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection
