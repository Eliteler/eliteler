{{-- Pet store: a store policy page (privacy, terms, refund, shipping, cookies, contact) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.pet-store.includes.head')
    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-warning" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #FFEDD5;">

        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.pet-store.includes.header', [
                        'bg_color' => 'warning',
                        'badge_color' => 'danger',
                    ])

                    <div class="page-wrapper mt-6 p-3">
                        {{-- Content --}}
                        <div class="page-body">
                            <div class="container-xl">
                                <div class="py-4">
                                    <h1 class="fs-1 border-bottom mb-4 fw-bold contact-information-title">
                                        {{ __($pageTitle) }}</h1>

                                    <div class="col-lg-12 col-md-12 col-sm-12 px-2">
                                        {!! $pageContent ?? '' !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    @include('templates.stores.pet-store.includes.footer')

                    <!-- Bottom Navbar -->
                    @include('templates.stores.includes.bottom-bar', ['color' => 'warning', 'bg' => 'light'])
                    <!-- End Bottom Navbar -->
                </div>
            </div>
        </div>
    </div>

    @include('templates.stores.pet-store.partials.cart-order-modal')
    @include('templates.stores.pet-store.includes.scripts')
</body>

</html>
