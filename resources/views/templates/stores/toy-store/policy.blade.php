{{-- Toy store: a store policy page (privacy, terms, refund, shipping, cookies, contact) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.toy-store.includes.head')
    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-red" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #F876761A;">

        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.toy-store.includes.header', [
                        'bg_color' => 'yellow',
                        'badge_color' => 'primary',
                    ])

                    <div class="page-wrapper px-3">
                        {{-- Content --}}
                        <div class="page-body">
                            <div class="container-xl">
                                <div class="py-2">
                                    <h1 class="fs-1 border-bottom mb-4 fw-bold contact-information-title">
                                        {{ __($pageTitle) }}</h1>

                                    <div class="col-lg-12 col-md-12 col-sm-12 px-2">
                                        {!! $pageContent ?? '' !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('templates.stores.toy-store.includes.footer')

                    <!-- Bottom Navbar -->
                    @include('templates.stores.includes.bottom-bar', ['color' => 'red', 'bg' => 'light'])
                    <!-- End Bottom Navbar -->
                </div>
            </div>
        </div>
    </div>

    @include('templates.stores.toy-store.partials.cart-order-modal')
    @include('templates.stores.toy-store.includes.scripts')
</body>

</html>
