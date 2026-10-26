<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.e-commerce.includes.head')

    <style>
        .card,
        .form-control,
        .rounded-e-commerce,
        .alert,
        input,
        select {
            border-radius: 0px !important;
        }

        /* ========================================================= */
        /* Breadcrumbs with Clean Ellipsis Truncation                */
        /* ========================================================= */
        .policy-hero-banner {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 20px;
            border: 1px solid #edf2f7;
            padding: 22px 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .policy-breadcrumbs {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            line-height: 1;
            margin-bottom: 12px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        .policy-breadcrumb-link {
            color: #206bc4;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            /* Prevents "Home" from being squished */
        }

        .policy-breadcrumb-separator {
            font-size: 0.75rem;
            color: #94a3b8;
            flex-shrink: 0;
        }

        /* Truncates long policy titles with clean ... ellipsis */
        .policy-breadcrumb-current {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: block !important;
            min-width: 0 !important;
            flex: 1 1 auto !important;
            color: #475569;
        }

        .policy-hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.4px;
            line-height: 1.25;
            word-break: break-word;
        }

        .policy-verified-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #206bc4;
            background-color: #e8f2fd;
            border: 1px solid #cfe2ff;
            border-radius: 50px;
            line-height: 1;
            white-space: nowrap !important;
        }

        /* ========================================================= */
        /* Policy Content Luxury Card                                */
        /* ========================================================= */
        .policy-content-card {
            background-color: #ffffff !important;
            border: 1px solid #edf2f7 !important;
            border-radius: 20px !important;
            padding: 28px 24px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
            margin-bottom: 30px;
        }

        .policy-rich-text {
            color: #334155;
            font-size: 1rem;
            line-height: 1.75;
            overflow-wrap: break-word;
        }

        .policy-rich-text h1,
        .policy-rich-text h2,
        .policy-rich-text h3,
        .policy-rich-text h4 {
            font-family: 'Outfit', sans-serif;
            color: #0f172a;
            font-weight: 700;
            margin-top: 24px;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .policy-rich-text h2 {
            font-size: 1.35rem;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .policy-rich-text p {
            margin-bottom: 16px;
        }

        .policy-rich-text ul,
        .policy-rich-text ol {
            padding-left: 24px;
            margin-bottom: 16px;
        }

        .policy-rich-text li {
            margin-bottom: 8px;
        }

        .policy-rich-text a {
            color: #206bc4;
            text-decoration: underline;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    <!-- Fail-safe Auto-Dismiss Preloader -->
    @include('templates.stores.e-commerce.includes.preloader')

    <div id="wrapper" class="page m-0 store-bg-wrapper">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden store-main-container">

                    <!-- Modular Top Header -->
                    @include('templates.stores.e-commerce.includes.header', [
                        'bg_color' => 'e-commerce-blue',
                        'badge_color' => 'red',
                        'color' => 'e-commerce-blue',
                    ])

                    <div class="col-md-12 my-3 px-3 px-lg-4">

                        <!-- Policy Hero Banner -->
                        <div class="policy-hero-banner">
                            <!-- Breadcrumbs with Ellipsis Truncation -->
                            <div class="policy-breadcrumbs">
                                <a href="{{ $storeUrl }}" class="policy-breadcrumb-link">
                                    <i class="ti ti-smart-home me-1"></i>{{ __('Home') }}
                                </a>
                                <i class="ti ti-chevron-right policy-breadcrumb-separator"></i>
                                <span class="policy-breadcrumb-current" title="{{ __($pageTitle) }}">
                                    {{ __($pageTitle) }}
                                </span>
                            </div>
                        </div>

                        <!-- Policy Rich Content Card -->
                        <div class="policy-content-card">
                            <div class="policy-rich-text">
                                {!! $pageContent ?? '<p class="text-muted text-center py-4">' . __('No policy content available.') . '</p>' !!}
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Modular Footer Include -->
                @include('templates.stores.e-commerce.includes.footer')
            </div>
        </div>

        <!-- Modular Mobile Bottom Bar Include -->
        @include('templates.stores.e-commerce.includes.bottom-bar', [
            'color' => 'e-commerce-blue',
            'bg' => 'light',
            'bg_color' => 'e-commerce-blue',
        ])
    </div>

    <!-- Floating WhatsApp Widget Include -->
    @include('templates.stores.e-commerce.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.e-commerce.partials.cart-order-modal')
    @include('templates.stores.e-commerce.includes.scripts')
</body>

</html>
