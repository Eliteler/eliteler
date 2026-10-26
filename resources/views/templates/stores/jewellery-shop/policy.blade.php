{{-- GoBiz Luxury Jewellery Policy & Legal Document Page Engine --}}
@php
    $docTitle = $pageTitle ?? ($title ?? __('Store Policy'));
    $docContent = $pageContent ?? ($description ?? __('No policy content available at this time.'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.jewellery-shop.includes.head')

    <style>
        :root {
            --store-radius-card: 26px;
        }


        /* Policy Reading Container */
        .policy-container-box {
            max-width: 980px;
            margin: 0 auto;
            padding: 24px 20px 60px 20px;
            width: 100%;
        }

        /* Proportionate Breadcrumbs */
        .luxury-breadcrumbs-box {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid var(--store-border);
            border-radius: var(--store-radius-pill) !important;
            padding: 6px 18px;
            font-size: 13px;
            color: var(--store-primary-dark);
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.06);
        }

        .breadcrumb-link {
            color: var(--store-primary-dark) !important;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }

        .breadcrumb-link:hover {
            color: var(--store-primary-hover) !important;
        }

        .breadcrumb-sep {
            font-size: 13px;
            opacity: 0.5;
        }

        .breadcrumb-current {
            font-weight: 700;
            color: var(--store-dark);
        }

        /* 100% Full-Width Document Card */
        .policy-document-card {
            background: var(--store-card-bg);
            border: 1.5px solid var(--store-border);
            border-radius: var(--store-radius-card) !important;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03), 0 0 25px rgba(212, 175, 55, 0.05);
            position: relative;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .policy-document-card {
                padding: 24px 18px;
            }
        }

        /* Document Header */
        .policy-doc-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            border-bottom: 1.5px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 24px;
            margin-bottom: 30px;
        }

        .policy-header-text-block {
            flex: 1 1 320px;
        }

        .policy-doc-title {
            font-family: 'Gloock', serif;
            font-size: 28px;
            color: var(--store-dark);
            margin: 0 0 8px 0;
            line-height: 1.25;
        }

        @media (max-width: 768px) {
            .policy-doc-title {
                font-size: 23px;
            }
        }

        .policy-meta-timestamp {
            font-size: 12.5px;
            color: var(--store-primary-dark);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            font-weight: 600;
        }

        /* Verified Trust Badge */
        .policy-trust-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--store-primary-light);
            border: 1px solid var(--store-border);
            color: var(--store-primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 6px 16px;
            border-radius: var(--store-radius-pill) !important;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.08);
            text-transform: uppercase;
        }

        .policy-trust-badge i {
            color: var(--store-primary-hover);
            font-size: 15px;
        }

        /* Rich Typography Body */
        .policy-content-rich-body {
            font-size: 15px;
            line-height: 1.85;
            color: #433726;
            word-break: break-word;
        }

        .policy-content-rich-body p {
            margin-bottom: 1.4rem;
        }

        .policy-content-rich-body h1,
        .policy-content-rich-body h2,
        .policy-content-rich-body h3,
        .policy-content-rich-body h4 {
            font-family: 'Gloock', serif;
            color: var(--store-dark);
            margin-top: 2rem;
            margin-bottom: 0.8rem;
            line-height: 1.3;
        }

        .policy-content-rich-body h2 {
            font-size: 21px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 8px;
        }

        .policy-content-rich-body h3 {
            font-size: 18px;
        }

        .policy-content-rich-body ul,
        .policy-content-rich-body ol {
            padding-left: 1.5rem;
            margin-bottom: 1.4rem;
        }

        [dir="rtl"] .policy-content-rich-body ul,
        [dir="rtl"] .policy-content-rich-body ol {
            padding-left: 0;
            padding-right: 1.5rem;
        }

        .policy-content-rich-body li {
            margin-bottom: 0.5rem;
        }

        .policy-content-rich-body a {
            color: var(--store-primary-hover) !important;
            font-weight: 600;
            text-decoration: underline !important;
        }

        .policy-content-rich-body a:hover {
            color: var(--store-dark) !important;
        }

        /* Document End Note */
        .policy-doc-footer-note {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed rgba(212, 175, 55, 0.35);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .policy-support-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--store-primary-light);
            border: 1px solid var(--store-border);
            color: var(--store-primary-dark) !important;
            padding: 8px 18px;
            border-radius: var(--store-radius-pill) !important;
            font-size: 12.5px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .policy-support-link-btn:hover {
            background: var(--store-primary);
            color: #ffffff !important;
            border-color: var(--store-primary);
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- Preloader Include --}}
    @include('templates.stores.jewellery-shop.includes.preloader')

    <div class="store-main-wrapper">
        {{-- Luxury Header Include --}}
        @include('templates.stores.jewellery-shop.includes.header')

        <div class="store-body-content-area">
            <div class="policy-container-box">

                {{-- Proportionate Breadcrumbs --}}
                <div class="luxury-breadcrumbs-box">
                    <a href="{{ $storeUrl }}" class="breadcrumb-link">
                        <i class="ti ti-home"></i>
                        <span>{{ __('Home') }}</span>
                    </a>
                    <i class="ti ti-chevron-right breadcrumb-sep"></i>
                    <span class="breadcrumb-current">{{ __($docTitle) }}</span>
                </div>

                {{-- 100% Full-Width Document Card --}}
                <div class="policy-document-card">
                    <!-- Header Row -->
                    <div class="policy-doc-header">
                        <div class="policy-header-text-block">
                            <h1 class="policy-doc-title">{{ __($docTitle) }}</h1>
                            <p class="policy-meta-timestamp">
                                <i class="ti ti-clock"></i>
                                <span>{{ __('Effective date: Current Store Policy') }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Rich Typography Content Body -->
                    <div class="policy-content-rich-body">
                        {!! $docContent !!}
                    </div>

                    <!-- Footer End Note & Contact Shortcut -->
                    <div class="policy-doc-footer-note">
                        <span class="text-muted fs-5">
                            <i class="ti ti-info-circle text-gold me-1"></i>
                            {{ __('Have questions regarding our terms or policies?') }}
                        </span>

                        @if (!empty($business_card_details->customer_support_policy))
                            <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                                class="policy-support-link-btn">
                                <i class="ti ti-headset"></i>
                                <span>{{ __('Contact Support') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Luxury Footer Include --}}
        @include('templates.stores.jewellery-shop.includes.footer')
    </div>

    {{-- Bottom Navigation Bar --}}
    @include('templates.stores.jewellery-shop.includes.bottom-bar')

    {{-- PWA Bottom Sheet Modal --}}
    @include('templates.stores.jewellery-shop.includes.pwa-modal')

    {{-- VIP Promo & Newsletter Popups --}}
    @include('templates.stores.jewellery-shop.includes.popups')

    {{-- Interactive WhatsApp Floating Chat Pod --}}
    @include('templates.stores.jewellery-shop.includes.whatsapp-float')

    {{-- Core JavaScript Bundle --}}
    @include('templates.stores.jewellery-shop.partials.cart-order-modal')
    @include('templates.stores.jewellery-shop.includes.scripts')
</body>

</html>
