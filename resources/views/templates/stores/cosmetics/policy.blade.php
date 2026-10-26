<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('templates.stores.cosmetics.includes.head')

    <style>
        /* Pure CSS Classes Replacing Inline Styles */
        .store-bg-wrapper {
            background-color: #ffdcdc;
        }

        .store-main-card {
            min-height: 100vh;
        }

        .btn,
        .btn-rose,
        a[data-bs-toggle="offcanvas"] {
            border-radius: 10px !important;
            color: #ffffff;
            border: none;
        }

        .contact-information-title {
            font-size: 1.5rem !important;
            font-family: 'Merriweather', serif !important;
        }

        .card-lg>.card-body {
            padding: 1.5rem !important;
        }

        .policy-wrapper-mt {
            margin-top: 6rem !important;
        }

        .policy-text-body {
            line-height: 1.85;
        }

    
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">
    <!-- Preloader -->
    @include('templates.stores.cosmetics.includes.preloader')

    {{-- Page Wrapper --}}
    <div id="wrapper" class="page m-0 min-h-screen store-bg-wrapper">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden store-main-card">
                    <!-- Navbar -->
                    @include('templates.stores.cosmetics.includes.header', [
                        'bg' => 'rose',
                        'bg_color' => 'rose',
                        'badge_color' => 'rose',
                    ])

                    <div class="page-wrapper policy-wrapper-mt p-3">

                        {{-- Policy Content --}}
                        <div class="page-body">
                            <div class="container-xl">
                                <div class="card card-lg shadow-sm border rounded-cosmetics">
                                    <div class="card-header bg-white border-bottom py-3">
                                        <h1 class="card-title contact-information-title fw-bold text-dark m-0">
                                            {{ __($pageTitle) }}</h1>
                                    </div>
                                    <div class="card-body">
                                        <div class="col-lg-12 col-md-12 col-sm-12 px-2 text-dark fs-3 policy-text-body">
                                            {!! $pageContent ?? '' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Component Include -->
                        @include('templates.stores.cosmetics.includes.footer')
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Navbar with safe parameters -->
        @include('templates.stores.cosmetics.includes.bottom-bar', [
            'color' => 'rose',
            'bg' => 'light',
            'bg_color' => 'rose',
        ])
    </div>

    @include('templates.stores.cosmetics.partials.cart-order-modal')

    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
        @include('templates.stores.cosmetics.includes.pwa-modal')
    @endif

    @include('templates.stores.cosmetics.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    @include('templates.stores.cosmetics.includes.scripts')
</body>

</html>
