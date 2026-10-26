{{-- Restaurant: a store policy page (privacy, terms, refund, shipping, cookies, contact) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.restaurant.includes.head')

    <style>
        .card-title {
            font-size: 24px !important;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-restaurant-red" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #F9C5C5;">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.restaurant.includes.header', [
                        'bg_color' => 'restaurant-red',
                        'badge_color' => 'primary',
                    ])

                    <div class="page-wrapper mt-6 p-3">
                        {{-- Content --}}
                        <div class="page-body">
                            <div class="container-xl">
                                <div class="card border mt-5">
                                    <div class="card-header">
                                        <h1 class="card-title fs-1 contact-information-title">{{ __($pageTitle) }}</h1>
                                    </div>
                                    <div class="card-body">
                                        <div class="col-lg-12 col-md-12 col-sm-12 px-2">
                                            {!! $pageContent ?? '' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('templates.stores.restaurant.includes.footer')
                    </div>

                    <!-- Bottom Navbar -->
                    @include('templates.stores.includes.bottom-bar', [
                        'color' => 'restaurant-red',
                        'bg' => 'light',
                    ])
                    <!-- End Bottom Navbar -->
                </div>
            </div>
        </div>
    </div>

    @include('templates.stores.restaurant.partials.cart-order-modal')
    @include('templates.stores.restaurant.includes.scripts')
</body>

</html>
