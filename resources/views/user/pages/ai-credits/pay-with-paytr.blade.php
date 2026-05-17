@extends('user.layouts.index', [
    'header' => false,
    'nav' => false,
    'demo' => true,
    'settings' => $settings,
])

@section('content')
    <div class="page-wrapper">

        <!-- Header -->
        <div class="page-header d-print-none border-bottom pb-3">
            <div class="container d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-item-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2 text-white">

                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icon-tabler-arrow-left icon-primary">

                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />

                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                    </a>

                    <h2 class="page-title">
                        {{ __('Checkout') }}
                    </h2>
                </div>

                <div class="navbar-brand navbar-brand-autodark">
                    <a href="{{ route('user.dashboard') }}">
                        @if (file_exists(public_path('img/logo-light.png')))
                            <img src="{{ optional(Auth::user())->choosed_theme == 'light' ? asset($settings->site_logo) : asset('img/logo-light.png') }}"
                                width="200" height="50" alt="{{ $settings->site_name }}"
                                class="navbar-brand-image custom-logo">
                        @else
                            <img src="{{ $settings->site_logo }}" width="200" height="50"
                                alt="{{ $settings->site_name }}" class="navbar-brand-image custom-logo">
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="page-body">
            <div class="container">
                {{-- Failed --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body p-2">
                                <iframe src="https://www.paytr.com/odeme/guvenli/{{ $iframe_token }}" id="paytriframe"
                                    frameborder="0" scrolling="yes"
                                    style="width:100%;height:900px;border:0;border-radius:12px;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                @include('user.includes.footer')
            </div>
        </div>
    </div>
@endsection
