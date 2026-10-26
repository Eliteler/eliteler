<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.gift-shop.includes.head')

    <style>
        :root {
            --store-radius-card: 24px;
        }

        /* Centered Breadcrumbs Navigation */
        .store-breadcrumbs-wrap {
            padding: 1.5rem 0 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .store-breadcrumbs-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--store-radius-pill) !important;
            padding: 6px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .breadcrumb-link {
            color: var(--store-text-muted) !important;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
        }

        .breadcrumb-link:hover {
            color: var(--store-primary) !important;
        }

        .breadcrumb-sep {
            color: #cbd5e1;
            font-size: 0.75rem;
        }

        .breadcrumb-current {
            color: var(--store-primary);
            font-weight: 700;
        }

        /* Policy Header & Trust Badge */
        .policy-header-container {
            text-align: center;
            padding: 0.75rem 1rem 1.75rem;
            position: relative;
        }

        .policy-trust-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 4px 14px;
            border-radius: 50px !important;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .policy-main-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--store-dark);
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .heading-divider-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 0.75rem auto 0;
            max-width: 280px;
        }

        .heading-divider-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        /* 100% Full-Width Luxury Document Card */
        .policy-document-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: var(--store-radius-card);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
            padding: 2.5rem 2rem;
            margin-bottom: 4rem;
            position: relative;
        }

        @media (max-width: 768px) {
            .policy-document-card {
                padding: 1.75rem 1.25rem;
                border-radius: 20px;
            }

            .policy-main-title {
                font-size: 1.75rem;
            }
        }

        /* Rich Typography for Dynamic Policy Content */
        .policy-content-area {
            color: #334155;
            font-size: 1rem;
            line-height: 1.85;
        }

        .policy-content-area h1,
        .policy-content-area h2,
        .policy-content-area h3,
        .policy-content-area h4,
        .policy-content-area h5,
        .policy-content-area h6 {
            color: #1c1917;
            font-weight: 700;
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
            line-height: 1.35;
        }

        .policy-content-area p {
            margin-bottom: 1.25rem;
        }

        .policy-content-area ul,
        .policy-content-area ol {
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .policy-content-area li {
            margin-bottom: 0.5rem;
        }

        .policy-content-area a {
            color: var(--store-primary) !important;
            font-weight: 600;
            border-bottom: 1px dashed var(--store-primary);
        }

        .policy-content-area a:hover {
            color: var(--store-primary-hover) !important;
        }

        .policy-content-area table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.95rem;
        }

        .policy-content-area th,
        .policy-content-area td {
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            text-align: left;
        }

        .policy-content-area th {
            background-color: #f8fafc;
            color: #1c1917;
            font-weight: 700;
        }

        .policy-card-footer-note {
            border-top: 1px solid #f1f5f9;
            padding-top: 1.5rem;
            margin-top: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            color: #94a3b8;
            font-size: 0.85rem;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.gift-shop.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Top Greeting Bar with Subtitle (Above Header) --}}
        @if (!empty($card_details->sub_title) || !empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
            @php
                $storeSubTitle = $card_details->sub_title ?? $business_card_details->sub_title;
            @endphp
            <div class="store-top-greeting-bar">
                <i class="ti ti-sparkles greeting-sparkle-icon"></i>
                <span>@include('templates.stores.shared.subtitle-lines'){{ $storeSubTitle }}</span>
            </div>
        @endif

        {{-- Luxury Header Include --}}
        @include('templates.stores.gift-shop.includes.header')

        <div class="store-body-content-area">
            <div class="container-xl px-3 px-lg-4 py-2">

                {{-- Centered Breadcrumbs Navigation --}}
                <div class="store-breadcrumbs-wrap">
                    <nav class="store-breadcrumbs-pill" aria-label="breadcrumb">
                        <a href="{{ $storeUrl }}" class="breadcrumb-link">
                            <i class="ti ti-home fs-4"></i>
                            <span>{{ __('Shop') }}</span>
                        </a>
                        <i class="ti ti-chevron-right breadcrumb-sep"></i>
                        <span class="breadcrumb-current">{{ __($pageTitle) }}</span>
                    </nav>
                </div>

                {{-- Policy Hero Heading & Verified Badge --}}
                <div class="policy-header-container">
                    <h1 class="policy-main-title">{{ __($pageTitle) }}</h1>
                    <div class="heading-divider-wrap">
                        <div class="heading-divider-line"></div>
                        <img src="{{ asset('img/templates/gift-shop/heading.png') }}" class="heading-divider-icon"
                            alt="divider" onerror="this.style.display='none'" />
                        <div class="heading-divider-line right"></div>
                    </div>
                </div>

                {{-- 100% Full-Width Luxury Document Container --}}
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10">
                        <div class="policy-document-card">
                            <div class="policy-content-area">
                                {!! $pageContent ?? '' !!}
                            </div>

                            {{-- Document Footer Note --}}
                            <div class="policy-card-footer-note">
                                <div>
                                    <i class="ti ti-building-store me-1"></i>
                                    <span>{{ $card_details->title }}</span>
                                </div>
                                <div>
                                    <i class="ti ti-clock me-1"></i>
                                    <span>{{ __('Official Store Policy') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Luxury Footer Include --}}
        @include('templates.stores.gift-shop.includes.footer')
    </div>

    {{-- Bottom Navigation Bar --}}
    @include('templates.stores.gift-shop.includes.bottom-bar')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.gift-shop.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.gift-shop.includes.popups')

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.gift-shop.includes.whatsapp-float')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.gift-shop.partials.cart-order-modal')
    @include('templates.stores.gift-shop.includes.scripts')
</body>

</html>
