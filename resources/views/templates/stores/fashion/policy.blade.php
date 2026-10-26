{{-- Master Legal & Policy View for Fashion Store --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.fashion.includes.head')

    <style>
        /* Luxury Policy Header Banner */
        .fashion-policy-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 28px 0;
            margin-bottom: 32px;
        }

        .fashion-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fashion-breadcrumb a {
            color: #64748b;
            transition: color 0.2s ease;
        }

        .fashion-breadcrumb a:hover {
            color: #0f172a;
        }

        .fashion-breadcrumb-active {
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fashion-policy-title {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        /* Official Trust Badge */
        .fashion-trust-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 12px;
            margin-bottom: 12px;
        }

        /* Policy Card Content */
        .fashion-policy-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 40px;
        }

        /* Rich Content Typography */
        .fashion-policy-content {
            font-size: 15px;
            line-height: 1.8;
            color: #334155;
        }

        .fashion-policy-content h1,
        .fashion-policy-content h2,
        .fashion-policy-content h3,
        .fashion-policy-content h4 {
            font-family: 'Oswald', sans-serif;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .fashion-policy-content h2 {
            font-size: 20px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .fashion-policy-content h3 {
            font-size: 17px;
        }

        .fashion-policy-content p {
            margin-bottom: 16px;
        }

        .fashion-policy-content ul,
        .fashion-policy-content ol {
            padding-left: 20px;
            margin-bottom: 18px;
        }

        .fashion-policy-content li {
            margin-bottom: 8px;
        }

        .fashion-policy-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .fashion-policy-content table th,
        .fashion-policy-content table td {
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 14px;
        }

        .fashion-policy-content table th {
            background-color: #f8fafc;
            color: #0f172a;
            font-weight: 700;
        }

        /* Action Buttons */
        .fashion-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            background-color: #0f172a;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 0px;
            transition: all 0.2s ease;
        }

        .fashion-back-btn:hover {
            background-color: #1e293b;
            color: #ffffff;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .fashion-policy-card {
                padding: 20px 16px;
            }

            .fashion-policy-title {
                font-size: 24px;
            }
        }
    </style>

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    {{-- 1. Preloader --}}
    @include('templates.stores.fashion.includes.preloader')

    {{-- 2. Header --}}
    @include('templates.stores.fashion.includes.header')

    {{-- 3. Breadcrumb & Page Banner --}}
    <div class="fashion-policy-header">
        <div class="container-xl">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="min-w-0">
                    <div class="fashion-breadcrumb">
                        <a href="{{ $storeUrl }}">{{ __('Home') }}</a>
                        <i class="ti ti-chevron-right fs-4"></i>
                        <span>{{ __('Legal') }}</span>
                        <i class="ti ti-chevron-right fs-4"></i>
                        <span class="fashion-breadcrumb-active">{{ __($pageTitle ?? 'Policy') }}</span>
                    </div>

                    <h1 class="fashion-policy-title">{{ __($pageTitle ?? 'Policy') }}</h1>
                </div>

                <div>
                    <a href="{{ $storeUrl }}" class="fashion-back-btn">
                        <i class="ti ti-arrow-left fs-3"></i>
                        <span>{{ __('Return to Shop') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Main Policy Content --}}
    <main class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <article class="fashion-policy-card">
                    <div class="fashion-policy-content">
                        @if (!empty($pageContent))
                            {!! $pageContent !!}
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="ti ti-file-info fs-1 d-block mb-2"></i>
                                <p class="fs-3 mb-0">{{ __('Content is currently unavailable.') }}</p>
                            </div>
                        @endif
                    </div>
                </article>
            </div>
        </div>
    </main>

    {{-- Footer Component --}}
    @include('templates.stores.fashion.includes.footer')

    {{-- Bottom Bar Component --}}
    @include('templates.stores.fashion.includes.bottom-bar')

    {{-- WhatsApp Floating Chat --}}
    @include('templates.stores.fashion.includes.whatsapp-float', ['whatsappNumber' => $enquiry_button])

    {{-- Popups Component --}}
    @include('templates.stores.fashion.includes.popups')

    {{-- PWA Install Modal --}}
    @include('templates.stores.fashion.includes.pwa-modal')

    {{-- Cart drawer, checkout and the shared storefront script --}}
    @include('templates.stores.fashion.partials.cart-order-modal')
    @include('templates.stores.fashion.includes.scripts')
</body>

</html>
