@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        /* main fix */
        .ga-group {
            display: flex;
            flex-wrap: nowrap;
            width: 100%;
        }

        /* prefix (URL part) */
        .ga-group .ga-prefix {
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* input */
        .ga-group .ga-input-field {
            min-width: 0;
            /* critical */
            width: 100%;
        }

        /* bootstrap compatibility */
        .ga-group .form-control {
            min-width: 0;
        }

        .ads-group .ads-prefix {
            white-space: normal;
            word-break: break-all;
            max-width: 100%;
        }

        /* Desktop (>= 992px) */
        @media (min-width: 992px) {

            .ga-group {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
            }

            /* FIX: allow prefix to shrink */
            .ga-group .ga-prefix {
                flex: 0 1 70%;
                /* shrink allowed */
                min-width: 0;

                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            /* FIX: input should take remaining space */
            .ga-group .ga-input {
                flex: 1 1 auto;
                min-width: 0;
                width: 100%;
            }
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
                            {{ __('Google Analtics Settings') }}
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
                        <form action="{{ route('admin.google_analytics_settings.update') }}" method="post" class="card">
                            @csrf
                            <div class="card-header">
                                <h4 class="page-title">{{ __('Google Analytics Credentials') }}</h4>
                            </div>
                            <div class="card-body">
                                {{-- Google Analytics --}}
                                <div class="row">
                                    <div class="col-xl-6">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Google Analytics ID') }}</label>

                                            <div class="input-group mb-2 ga-group">
                                                <span class="input-group-text ga-prefix">
                                                    https://www.googletagmanager.com/gtag/js?id=
                                                </span>
                                                <input type="text" class="form-control ga-input-field"
                                                    name="google_analytics_id" value="{{ $settings->google_analytics_id }}"
                                                    placeholder="{{ __('Google Analytics ID') }}" autocomplete="off"
                                                    required>
                                            </div>
                                        </div>

                                        <span>
                                            {{ __('If you did not get a Google Analytics ID, create a') }}
                                            <a href="https://analytics.google.com/analytics/web/" target="_blank">
                                                {{ __('new Google Analytics ID.') }}
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
