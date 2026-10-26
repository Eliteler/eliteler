<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.furniture.includes.head')

    <style>
        /* 2. Proportionate Luxury Breadcrumbs Bar */
        .store-breadcrumbs-bar {
            background-color: #faf8f5;
            border-bottom: 1px solid var(--color-card-border);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
        }

        .breadcrumb-link {
            color: var(--color-forest-teal);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
            transition: color 0.2s ease;
        }

        .breadcrumb-link i {
            font-size: 15px !important;
            line-height: 1;
        }

        .breadcrumb-link:hover {
            color: var(--color-camel);
        }

        .breadcrumb-separator {
            color: var(--color-camel);
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .breadcrumb-separator i {
            font-size: 12px !important;
            line-height: 1;
        }

        .breadcrumb-current {
            color: var(--color-text-muted);
            font-weight: 600;
            word-break: break-word;
        }

        /* 3. Full-Width Main Policy Content Area */
        .store-policy-body-area {
            flex: 1 0 auto;
            width: 100%;
            padding: 20px 16px 40px;
        }

        @media (min-width: 768px) {
            .store-policy-body-area {
                padding: 28px 24px 50px;
            }
        }

        /* Full-Width Document Card */
        .policy-document-card {
            background: #ffffff;
            border: 1px solid var(--color-card-border);
            border-radius: 20px !important;
            padding: 24px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            width: 100% !important;
            max-width: 100% !important;
        }

        @media (min-width: 768px) {
            .policy-document-card {
                padding: 36px 36px;
            }
        }

        /* Official Trust Badge */
        .policy-trust-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 16px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border-radius: 9999px !important;
            background-color: #f5ece1;
            color: #cca47a;
            border: 1px solid rgba(204, 164, 122, 0.4);
            margin-bottom: 14px;
        }

        .policy-trust-badge i {
            font-size: 14px;
        }

        .policy-heading-title {
            font-family: 'DM Serif Display', serif;
            font-size: 26px;
            color: var(--color-text-main);
            line-height: 1.25;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1.5px solid var(--color-card-border);
        }

        @media (min-width: 768px) {
            .policy-heading-title {
                font-size: 32px;
            }
        }

        /* Rendered HTML Typography Styling */
        .policy-content-rich-text {
            color: #334155;
            font-size: 15px;
            line-height: 1.75;
        }

        .policy-content-rich-text h1,
        .policy-content-rich-text h2,
        .policy-content-rich-text h3,
        .policy-content-rich-text h4 {
            font-family: 'DM Serif Display', serif;
            color: var(--color-text-main);
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .policy-content-rich-text p {
            margin-bottom: 16px;
        }

        .policy-content-rich-text ul,
        .policy-content-rich-text ol {
            padding-left: 24px;
            margin-bottom: 18px;
        }

        .policy-content-rich-text li {
            margin-bottom: 6px;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.furniture.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Header Include --}}
        @include('templates.stores.furniture.includes.header')

        {{-- Luxury Responsive Breadcrumbs Bar (Refined Icon Proportions) --}}
        <div class="store-breadcrumbs-bar">
            <a href="{{ $storeUrl }}" class="breadcrumb-link">
                <i class="ti ti-home"></i>
                <span>{{ __('Home') }}</span>
            </a>
            <span class="breadcrumb-separator"><i class="ti ti-chevron-right"></i></span>
            <span class="breadcrumb-current">{{ __($pageTitle) }}</span>
        </div>

        {{-- Main Full-Width Policy Content Area --}}
        <div class="store-policy-body-area">
            <div class="policy-document-card">
                {{-- Page Title --}}
                <h1 class="policy-heading-title">{{ __($pageTitle) }}</h1>

                {{-- Rendered Policy Content --}}
                <div class="policy-content-rich-text">
                    {!! $pageContent ?? '' !!}
                </div>
            </div>
        </div>

        {{-- Footer Include (Firmly Anchored to Bottom) --}}
        @include('templates.stores.furniture.includes.footer')
    </div>

    {{-- Bottom Bar Include --}}
    @include('templates.stores.furniture.includes.bottom-bar')

    {{-- PWA Modal Include --}}
    @include('templates.stores.furniture.includes.pwa-modal')

    {{-- WhatsApp Interactive Float Include --}}
    @include('templates.stores.furniture.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.furniture.partials.cart-order-modal')
    @include('templates.stores.furniture.includes.scripts')
</body>

</html>
