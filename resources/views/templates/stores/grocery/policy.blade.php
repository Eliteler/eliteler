{{-- GoBiz Luxury Grocery Store - Legal Policy Document Page with Single-Line Breadcrumbs --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.grocery.includes.head')

    <style>
        /* Single-Line Proportionate Breadcrumbs with Ellipsis */
        .store-breadcrumb-nav {
            max-width: 1100px;
            margin: 20px auto 0 auto;
            padding: 0 20px;
            width: 100%;
            overflow: hidden;
        }

        .store-breadcrumb-list {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            padding: 8px 18px;
            border-radius: 50px !important;
            border: 1px solid #c8e6c9;
            list-style: none;
            margin: 0;
            box-shadow: 0 2px 6px rgba(46, 125, 50, 0.04);
            flex-wrap: nowrap !important;
            /* Strict Single Line */
            white-space: nowrap !important;
            max-width: 100%;
            overflow: hidden;
        }

        .store-breadcrumb-item {
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
            /* Keep parent links intact */
        }

        .store-breadcrumb-link {
            color: #616161;
            transition: color 0.2s ease;
        }

        .store-breadcrumb-link:hover {
            color: #2e7d32;
        }

        .store-breadcrumb-sep {
            color: #a5d6a7;
            font-size: 11px;
            flex-shrink: 0;
        }

        [dir="rtl"] .store-breadcrumb-sep {
            transform: rotate(180deg);
        }

        /* Active Title Truncation with Ellipsis (...) */
        .store-breadcrumb-item.store-breadcrumb-active {
            flex-shrink: 1;
            min-width: 0;
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #1b5e20;
        }

        .store-breadcrumb-active span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .store-breadcrumb-nav {
                padding: 0 16px;
                margin: 16px auto 0 auto;
            }

            .store-breadcrumb-list {
                padding: 6px 14px;
                gap: 6px;
                font-size: 12px;
            }

            .store-breadcrumb-item {
                font-size: 12px;
            }

            /* Compact Ellipsis for Mobile Screens */
            .store-breadcrumb-item.store-breadcrumb-active {
                max-width: 130px;
            }
        }

        /* Full-Width Document Container */
        .store-policy-container {
            max-width: 1100px;
            margin: 25px auto 55px auto;
            padding: 0 20px;
            width: 100%;
        }

        .store-policy-card {
            background: #ffffff;
            border: 1px solid #c8e6c9;
            border-radius: 28px !important;
            box-shadow: 0 6px 24px rgba(46, 125, 50, 0.05);
            overflow: hidden;
        }

        /* Document Header */
        .store-policy-card-header {
            padding: 32px 36px 24px 36px;
            border-bottom: 1px solid #e8f5e9;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .store-policy-card-header {
                padding: 24px 20px 18px 20px;
            }
        }

        /* Verified Trust Badge */
        .store-trust-badge-wrap {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            padding: 5px 14px;
            border-radius: 50px !important;
            font-size: 12px;
            font-weight: 700;
            width: fit-content;
        }

        .store-trust-badge-icon {
            font-size: 15px;
            color: #2e7d32;
        }

        .store-policy-document-title {
            font-size: 28px;
            font-weight: 800;
            color: #1b5e20;
            margin: 0;
            line-height: 1.25;
            text-align: start;
        }

        @media (max-width: 768px) {
            .store-policy-document-title {
                font-size: 22px;
            }
        }

        .store-policy-meta-line {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            color: #757575;
            font-weight: 500;
        }

        /* Document Body & Rich Typography */
        .store-policy-card-body {
            padding: 36px;
            font-size: 15px;
            line-height: 1.8;
            color: #374151;
            text-align: start;
        }

        @media (max-width: 768px) {
            .store-policy-card-body {
                padding: 24px 20px;
                font-size: 14px;
                line-height: 1.75;
            }
        }

        /* Rich Content Styling Engine */
        .store-policy-content h1,
        .store-policy-content h2,
        .store-policy-content h3,
        .store-policy-content h4,
        .store-policy-content h5,
        .store-policy-content h6 {
            color: #1b5e20;
            font-weight: 700;
            margin-top: 1.6em;
            margin-bottom: 0.6em;
            line-height: 1.35;
        }

        .store-policy-content h1 {
            font-size: 1.75rem;
        }

        .store-policy-content h2 {
            font-size: 1.45rem;
        }

        .store-policy-content h3 {
            font-size: 1.25rem;
        }

        .store-policy-content h4 {
            font-size: 1.1rem;
        }

        .store-policy-content p {
            margin-bottom: 1.4em;
        }

        .store-policy-content ul,
        .store-policy-content ol {
            padding-inline-start: 1.8em;
            margin-bottom: 1.4em;
        }

        .store-policy-content li {
            margin-bottom: 0.5em;
        }

        .store-policy-content strong {
            color: #111827;
            font-weight: 700;
        }

        .store-policy-content a {
            color: #2e7d32;
            font-weight: 600;
            text-decoration: underline !important;
        }

        .store-policy-content a:hover {
            color: #1b5e20;
        }

        .store-policy-content blockquote {
            margin: 1.6em 0;
            padding: 14px 20px;
            background-color: #f1f8e9;
            border-left: 4px solid #2e7d32;
            border-radius: 0 14px 14px 0 !important;
            color: #2e382e;
            font-style: italic;
        }

        [dir="rtl"] .store-policy-content blockquote {
            border-left: none;
            border-right: 4px solid #2e7d32;
            border-radius: 14px 0 0 14px !important;
        }

        .store-policy-content table {
            width: 100%;
            margin: 1.6em 0;
            border-collapse: collapse;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #c8e6c9;
        }

        .store-policy-content th {
            background-color: #e8f5e9;
            color: #1b5e20;
            font-weight: 700;
            padding: 12px 14px;
            border: 1px solid #c8e6c9;
        }

        .store-policy-content td {
            padding: 12px 14px;
            border: 1px solid #c8e6c9;
        }

        /* Document Footer Action */
        .store-policy-card-footer {
            padding: 20px 36px;
            background-color: #f7faf7;
            border-top: 1px solid #e8f5e9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .store-policy-card-footer {
                padding: 16px 20px;
            }
        }

        .btn-policy-back {
            height: 42px;
            padding: 0 22px !important;
            border-radius: 50px !important;
            background-color: #2e7d32 !important;
            color: #ffffff !important;
            border: none !important;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-policy-back:hover {
            background-color: #1b5e20 !important;
        }

        [dir="rtl"] .btn-policy-back i {
            transform: rotate(180deg);
        }

        .store-policy-footer-note {
            font-size: 12px;
            color: #757575;
            font-weight: 500;
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

    {{-- 1. Orbit Preloader --}}
    @include('templates.stores.grocery.includes.preloader')

    <div class="store-main-wrapper">
        {{-- 2. Header Component --}}
        @include('templates.stores.grocery.includes.header')

        <div class="store-body-content-area">
            {{-- Single-Line Proportionate Breadcrumbs with Ellipsis --}}
            <nav class="store-breadcrumb-nav" aria-label="breadcrumb">
                <ol class="store-breadcrumb-list">
                    <li class="store-breadcrumb-item">
                        <a href="{{ $storeUrl }}" class="store-breadcrumb-link">
                            <i class="ti ti-home"></i>
                            <span>{{ __('Shop') }}</span>
                        </a>
                    </li>
                    <li class="store-breadcrumb-sep"><i class="ti ti-chevron-right"></i></li>
                    <li class="store-breadcrumb-item">
                        <span class="text-muted">{{ __('Policies') }}</span>
                    </li>
                    <li class="store-breadcrumb-sep"><i class="ti ti-chevron-right"></i></li>
                    <li class="store-breadcrumb-item store-breadcrumb-active" aria-current="page"
                        title="{{ __($pageTitle) }}">
                        <span>{{ __($pageTitle) }}</span>
                    </li>
                </ol>
            </nav>

            {{-- 100% Full-Width Luxury Policy Document Card --}}
            <div class="store-policy-container">
                <article class="store-policy-card">
                    <!-- Header with Verified Trust Badge -->
                    <header class="store-policy-card-header">
                        <div class="store-trust-badge-wrap">
                            <i class="ti ti-shield-check store-trust-badge-icon"></i>
                            <span>{{ __('Verified Store Policy') }}</span>
                        </div>
                        <h1 class="store-policy-document-title">{{ __($pageTitle) }}</h1>
                        <div class="store-policy-meta-line">
                            <span><i class="ti ti-building-store me-1"></i> {{ $card_details->title }}</span>
                            <span><i class="ti ti-calendar me-1"></i> {{ date('F Y') }}</span>
                        </div>
                    </header>

                    <!-- Rich Document Body -->
                    <div class="store-policy-card-body store-policy-content">
                        @if (!empty($pageContent))
                            {!! $pageContent !!}
                        @else
                            <p class="text-muted">
                                {{ __('No detailed content has been published for this policy yet.') }}</p>
                        @endif
                    </div>

                    <!-- Card Footer Navigation -->
                    <footer class="store-policy-card-footer">
                        <a href="{{ $storeUrl }}" class="btn btn-policy-back">
                            <i class="ti ti-arrow-left"></i>
                            <span>{{ __('Return to Shop') }}</span>
                        </a>
                        <span class="store-policy-footer-note">
                            {{ __('All policies are subject to our general Terms & Conditions.') }}
                        </span>
                    </footer>
                </article>
            </div>
        </div>

        {{-- 3. Sticky Luxury Footer --}}
        @include('templates.stores.grocery.includes.footer')

        {{-- 4. Bottom Navigation Bar --}}
        @include('templates.stores.grocery.includes.bottom-bar')
    </div>

    {{-- Interactive Floating WhatsApp --}}
    @include('templates.stores.grocery.includes.whatsapp-float')

    {{-- PWA Install Modal --}}
    @include('templates.stores.grocery.includes.pwa-modal')

    {{-- VIP & Newsletter Popups --}}
    @include('templates.stores.grocery.includes.popups')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.grocery.partials.cart-order-modal')
    @include('templates.stores.grocery.includes.scripts')
</body>

</html>
