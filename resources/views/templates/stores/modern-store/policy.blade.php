{{-- Modern store: a store policy page (privacy, terms, refund, shipping, cookies, contact) --}}
@php
    $dark = $storeTheme->isDark();
    $heading = $dark ? 'text-white' : '';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.modern-store.includes.head')

    <style>
        .policy-title {
            font-size: 1.5rem !important;
        }

        .card-lg>.card-body {
            padding: 1.5rem !important;
        }

        .mt-6 {
            margin-top: 6rem !important;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body @if ($dark) data-bs-theme="dark" @endif dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper {{ $dark ? 'bg-dark' : '' }}">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-{{ $storeTheme->color }}" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page">
        <!-- Navbar -->
        @include('templates.stores.includes.header', ['bg_color' => $storeTheme->color, 'badge_color' => 'danger'])

        <div class="page-wrapper mt-6 p-3">
            <div class="page-body">
                <div class="container-xl">
                    <div class="card card-lg">
                        <div class="card-header">
                            <h1 class="card-title policy-title {{ $heading }}">{{ __($pageTitle) }}</h1>
                        </div>
                        <div class="card-body">
                            <div class="col-lg-12 col-md-12 col-sm-12 px-2 {{ $heading }}">
                                {!! $pageContent ?? '' !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('templates.stores.modern-store.includes.footer')
        </div>

        @include('templates.stores.includes.bottom-bar', ['color' => $storeTheme->color, 'bg' => $dark ? 'dark' : 'light'])
    </div>

    @include('templates.stores.modern-store.partials.cart-order-modal')
    @include('templates.stores.modern-store.includes.scripts')
</body>

</html>
