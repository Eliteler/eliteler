<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Store icon and color --}}
    @php
        $seo = null;
        if (!empty($business_card_details->seo_configurations)) {
            $decoded = json_decode($business_card_details->seo_configurations);
            $seo = is_object($decoded) ? $decoded : null;
        }
    @endphp

    @if ($seo && !empty($seo->favicon))
        <link rel="icon" href="{{ url($seo->favicon) }}" sizes="512x512" type="image/png">
        <link rel="apple-touch-icon" href="{{ url($seo->favicon) }}">
    @else
        <link rel="icon" href="{{ url($business_card_details->profile) }}" sizes="512x512" type="image/png">
        <link rel="apple-touch-icon" href="{{ url($business_card_details->profile) }}">
    @endif

    <meta name="theme-color" content="yellow" />

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="application-name" content="{{ $card_details->title }}">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">

    <!-- Tile for Win8 -->
    <meta name="msapplication-TileColor" content="yellow">
    <meta name="msapplication-TileImage" content="{{ url($business_card_details->profile) }}">

    {{-- CSS --}}
    <link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="{{ url('app/css/store.css') }}" rel="stylesheet">
    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.4px;
        }

        .navbar {
            background-color: rgba(21, 31, 44, 0.6) !important;
        }

        .border-top {
            border-top: var(--tblr-border-width) var(--tblr-border-style) rgb(179 189 204 / 13%) !important;
        }

        .offcanvas-header {
            border-bottom: var(--tblr-border-width) var(--tblr-border-style) rgb(179 189 204 / 13%) !important;
        }

        .badge-custom {
            background-color: #343d51 !important;
            color: #ccc !important;
        }
    </style>

    {{-- Check business details --}}
    @if ($business_card_details != null)
        @php
            $custom_css = $business_card_details->custom_css;
            $custom_js = $business_card_details->custom_js;

            // Ensure <style> tags for custom CSS
            if (strpos($custom_css, '<style>') === false && strpos($custom_css, '</style>') === false) {
                $custom_css = "<style>" . $custom_css . "</style>";
            }

            // Ensure <script> tags for custom JS
            if (strpos($custom_js, '<script>') === false && strpos($custom_js, '</script>') === false) {
                $custom_js = "<script>" . $custom_js . "</script>";
            }
        @endphp

        {!! $custom_css !!}
        {!! $custom_js !!}

        {{-- Theme CSS --}}
        @if(!empty($business_card_details->theme_css))
            <style>
                {!! $business_card_details->theme_css !!}
            </style>
        @endif

        {{-- Theme JS --}}
        @if(!empty($business_card_details->theme_js))
            <script>
                {!! $business_card_details->theme_js !!}
            </script>
        @endif
    @endif

    {{-- JS --}}
    <script src="{{ url('js/jquery.min.js') }}"></script>
    <script src="{{ url('js/main.js') }}"></script>
    <script src="{{ url('js/sweetalert.all.js') }}"></script>

    {{-- SEO Tags --}}
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    {{-- Check PWA --}}
    @if ($plan_details != null)
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @laravelPWA

            <!-- Web Application Manifest -->
            <link rel="manifest" href="{{ $manifest }}">
        @endif
    @endif

    {{-- Limited Text Function --}}
    @php
        use Illuminate\Support\Facades\DB;
        // Fetch settings from the database
        $config = DB::table('config')->get();

        if (!function_exists('limit_text')) {
            function limit_text($text)
            {
                $limit = 4;
                if (str_word_count($text, 0) > $limit) {
                    $words = str_word_count($text, 2);
                    $pos = array_keys($words);
                    $text = substr($text, 0, $pos[$limit]) . '...';
                }
                return $text;
            }
        }
    @endphp
</head>

<body data-bs-theme="dark"
    dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">
    <!-- Preloader -->
    <div class="page page-center preloader-wrapper bg-dark">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-yellow" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page">
        <!-- Navbar -->
        @include('templates.includes.header', ['bg_color' => 'yellow', 'badge_color' => 'danger'])

        <div class="page-wrapper mt-4 p-3">
            <div class="page-body">
                <div class="container-xl">
                    <!-- Banners -->
                    <div class="row pt-6">
                        {{-- Greeting message --}}
                        <div class="col-md-12">
                            <h3 class="alert alert-important alert-yellow p-3">{{ $business_card_details->sub_title }}
                            </h3>
                        </div>

                        {{-- Success Message (hidden by default) --}}
                        @if (session('success'))
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-success p-3">{{ session('success') }}</h3>
                            </div>
                        @endif

                        {{-- Failed Message (hidden by default) --}}
                        @if (session('failed'))
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-danger p-3">{{ session('failed') }}</h3>
                            </div>
                        @endif

                        <!-- Banner Images -->
                        <div class="col-md-12">
                            <swiper-container space-between="20" class="mySwiper" autoplay-delay="2500"
                                pagination="true" autoplay-disable-on-interaction="false" loop="true">
                                {{-- Cover --}}
                                @if (is_array(json_decode($business_card_details->cover)) == true)
                                    @foreach (json_decode($business_card_details->cover) as $cover)
                                        <swiper-slide>
                                            <img class="radius-img w-100 h-100 object-fit-cover"
                                                alt="{{ $business_card_details->title }}" src="{{ url($cover) }}" />
                                        </swiper-slide>
                                    @endforeach
                                @else
                                    <swiper-slide>
                                        <img class="radius-img w-100 h-100 object-fit-cover"
                                            alt="{{ $business_card_details->title }}"
                                            src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}" />
                                    </swiper-slide>
                                @endif
                            </swiper-container>
                        </div>

                        <div class="row ps-lg-3">
                            <!-- Categories -->
                            <div
                                class="{{ request('category') ? 'bg-dark rounded px-3 col-md-3 my-3' : 'col-md-12 my-3' }}">
                                {{-- Filter --}}
                                <form class="row pt-3 {{ request('category') ? '' : 'd-none' }}" method="GET"
                                    action="{{ url()->current() }}">
                                    <!-- Queries -->
                                    @foreach (request()->except(['min', 'max', 'page']) as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach

                                    <!-- Min Price -->
                                    <div class="col-md-4 col-6">
                                        <input type="number" name="min" class="form-control"
                                            placeholder="{{ __('Min') }}" step="0.01" min="0"
                                            value="{{ request('min') }}">
                                    </div>

                                    <!-- Max Price -->
                                    <div class="col-md-4 col-6">
                                        <input type="number" name="max" class="form-control"
                                            placeholder="{{ __('Max') }}" step="0.01" min="0"
                                            value="{{ request('max') }}">
                                    </div>

                                    <!-- Submit -->
                                    <div class="col-md-4 mt-2 mt-md-0">
                                        <button type="submit"
                                            class="btn btn-yellow w-100">{{ __('Filter') }}</button>
                                    </div>
                                </form>

                                <h2 class="pt-4 pb-5 text-start position-relative fs-custom fw-bold text-white">
                                    {{ __('Categories') }}
                                    <div class="position-absolute start-0 bg-yellow bottom-bar2"></div>
                                </h2>

                                <!-- All Categories -->
                                <swiper-container space-between="10"
                                    class="mySwiper {{ request('category') ? 'd-none' : '' }}" autoplay-delay="2500"
                                    breakpoints='{
                                        "320": { "slidesPerView": 2 },
                                        "640": { "slidesPerView": 4 }, 
                                        "1024": { "slidesPerView": 5 } 
                                    }'
                                    autoplay-disable-on-interaction="false" loop="true">

                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <swiper-slide class="cursor-pointer">
                                            <a
                                                href="{{ url($business_card_details->card_url) . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="card radius-img">
                                                    <div
                                                        class="card-body d-flex flex-column align-items-center justify-content-center gap-2 p-3">
                                                        <!-- Thumbnail -->
                                                        <div class="ratio ratio-1x1 d-flex align-items-center justify-content-center">
                                                            <img
                                                                src="{{ url($category->thumbnail) }}"
                                                                alt="{{ $category->category_name }}"
                                                                class="img-fluid category-thumbnail"
                                                            >
                                                        </div>
                                                        <!-- Category Name -->
                                                        <div class="">
                                                            <h3 class="responsive-title mt-2 mb-0">
                                                                {{ __($category->category_name) }}
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </swiper-slide>
                                    @endforeach
                                </swiper-container>

                                <div class="row {{ request('category') ? '' : 'd-none' }}">
                                    <!-- All Categories -->
                                    <div class="pb-3">
                                        <a href="{{ url()->current() }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ asset('img/templates/all-categories.png') }}" alt="All"
                                                    class="rounded-custom"
                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                <h3 class="mb-0 text-white fs-3">{{ __('All') }}</h3>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <div class="py-3 border-top">
                                            <a
                                                href="{{ url($business_card_details->card_url) . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="text-decoration-none">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <!-- Thumbnail -->
                                                        <img src="{{ url($category->thumbnail) }}" class="rounded-custom"
                                                            style="width: 40px; height: 40px; object-fit: cover;"
                                                            alt="{{ $category->category_name }}" />
                                                        @php
                                                            $category_name = urlencode(
                                                                strtolower($category->category_name),
                                                            );
                                                        @endphp
                                                        <!-- Category Name -->
                                                        <h3
                                                            class="mb-0 fs-3 {{ request('category') == $category_name ? 'text-yellow' : 'text-white' }}">
                                                            {{ __($category->category_name) }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>


                            <!-- Products -->
                            <div class="my-3 {{ request('category') ? 'col-md-9 ps-lg-4' : 'col-md-12' }}">
                                <div class="row align-items-center {{ request('category') ? '' : 'd-none' }}">
                                    <!-- Search Form -->
                                    <form class="row col-md-8" method="GET" action="{{ url()->current() }}">
                                        <!-- Queries -->
                                        @foreach (request()->except(['query', 'page']) as $key => $value)
                                            <input type="hidden" name="{{ $key }}"
                                                value="{{ $value }}">
                                        @endforeach

                                        <!-- Search Query -->
                                        <div class="col-9">
                                            <input type="text" name="query" placeholder="{{ __('Search') }}"
                                                value="{{ request('query') }}" class="form-control" />
                                        </div>

                                        <!-- Submit -->
                                        <div class="col-3">
                                            <button type="submit"
                                                class="btn btn-yellow w-100">{{ __('Search') }}</button>
                                        </div>
                                    </form>

                                    <!-- Sort Dropdown -->
                                    <div class="col-md-4 mt-2 mt-md-0">
                                        <select name="sort" class="form-select" onchange="itemSort(this.value)">
                                            <option value="default"
                                                {{ request('sort') == 'default' ? 'selected' : '' }}>
                                                {{ __('Recently Added') }}</option>
                                            <option value="price_asc"
                                                {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                                {{ __('Price: Low to High') }}</option>
                                            <option value="price_desc"
                                                {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                                {{ __('Price: High to Low') }}</option>
                                            <option value="name_asc"
                                                {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                                                {{ __('Name: A to Z') }}</option>
                                            <option value="name_desc"
                                                {{ request('sort') == 'name_desc' ? 'selected' : '' }}>
                                                {{ __('Name: Z to A') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Products -->
                                <h2 class="pt-4 pb-5 position-relative fs-custom fw-bold">
                                    {{ __('Products') }}
                                    <div class="position-absolute start-0 bg-yellow bottom-bar2"></div>
                                </h2>
                                <!-- Foreach -->
                                @if (count($products) > 0)
                                    <div class="row">
                                        <!-- Foreach -->
                                        @foreach ($products as $product)
                                            <div
                                                class="col-12 {{ request('category') ? 'col-md-4' : 'col-md-3' }} mb-2">
                                                <div class="card d-flex flex-column p-3">
                                                    <div class="d-flex flex-column">
                                                        @php
                                                            $productImages = explode(',', $product->product_image);
                                                        @endphp
                                                        <a
                                                            href="{{ url($business_card_details->card_url . '/product/' . $product->id) }}">
                                                            <div id="carousel-controls" class="carousel slide mb-3"
                                                                data-bs-ride="carousel">
                                                                <!-- Carousel Images -->
                                                                <div class="carousel-inner">
                                                                    @foreach ($productImages as $productImage)
                                                                        <div
                                                                            class="carousel-item {{ $loop->index == 0 ? 'active' : '' }} ratio ratio-4x3">
                                                                            <img class="d-block img-fluid object-fit-cover w-100 rounded"
                                                                                id="{{ $product->product_id }}_product_image"
                                                                                style="object-fit: contain;"
                                                                                alt="{{ $product->product_name }}"
                                                                                src="{{ url($productImage) }}">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            @if ($product->badge)
                                                                <!-- Badge -->
                                                                <div
                                                                    class="badge-container d-flex flex-wrap align-items-center gap-2 mb-2">
                                                                    <span class="badge badge-custom">
                                                                        {{ $product->badge }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <!-- Product Name -->
                                                            <h3 id="{{ $product->product_id }}_product_name"
                                                                class="fs-2 single-product mb-1 text-truncate text-white"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ $product->product_name }}">
                                                                {{ $product->product_name }}
                                                            </h3>
                                                            <!-- Product Subtitle -->
                                                            <p class="text-white mb-2 text-truncate">
                                                                {{ $product->product_short_description }}</p>
                                                            <!-- Price -->
                                                            <span class="d-none"
                                                                id="{{ $product->product_id }}_price">{{ $product->sales_price }}</span>
                                                        </a>

                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <!-- Price Column -->
                                                            <div class="">
                                                                <!-- Sales Price -->
                                                                <p class="m-0 fs-3 text-white">
                                                                    <span id="{{ $product->product_id }}_currency">
                                                                        <span>{{ formatCurrencyCard($product->sales_price, $currency) }}</span>
                                                                    </span>
                                                                </p>

                                                                <!-- Regular Price -->
                                                                @if ($product->sales_price != $product->regular_price)
                                                                    @if ($product->regular_price)
                                                                        <p class="text-muted fs-4 m-0">
                                                                            <del>{{ formatCurrencyCard($product->regular_price, $currency) }}</del>
                                                                        </p>
                                                                    @endif
                                                                @else
                                                                    <del
                                                                        class="text-dark fs-4 m-0">{{ formatCurrencyCard($product->sales_price, $currency) }}</del>
                                                                @endif
                                                            </div>

                                                            <!-- Icon Button -->
                                                            @if ($product->product_status == 'instock')
                                                                <a onclick="addToCart('{{ $product->product_id }}')"
                                                                    class="cursor-pointer">
                                                                    <i
                                                                        class="ti ti-shopping-bag-plus bg-yellow text-white p-custom fs-1 rounded-4"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Paginate --}}
                                        <div class="col-md-12 my-3">
                                            @if (request()->has('category'))
                                                {{ $products->appends(request()->except('page'))->links('vendor.pagination.store', ['color' => 'yellow']) }}
                                            @else
                                                {{ $products->links('vendor.pagination.store', ['color' => 'yellow']) }}
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <!-- No Products Found -->
                                    <div class="row text-center">
                                        <h3 class="fs-1">{{ __('No Products Founds!') }}</h3>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none mb-7 mb-lg-0">
                <div class="container-xl">
                    <!-- First Row: Copyright and Social Links -->
                    <div class="row text-center text-lg-start align-items-center">
                        <div class="col-lg-6 mb-2 mb-lg-0">
                            <span class="text-white">
                                {{ __('Copyright') }} &copy;
                                <span id="year"></span>
                                <a href="{{ url($business_card_details->card_url) }}" class="text-white fw-semibold">
                                    {{ $card_details->title }}
                                </a>.
                                {{ __('All rights reserved.') }} <br>
                                @if (!isset($plan_details['hide_branding']) || $plan_details['hide_branding'] != 1)
                                    {{ __('Made with') }} <i class="ti ti-heart text-danger"></i> <a href="{{ config('app.url') }}">{{ config('app.name') }}</a>.
                                @endif
                            </span>
                        </div>
                        <div class="col-lg-6 text-lg-end">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    <a href="{{ $shareComponent['facebook'] }}" target="_blank" class="link-light">
                                        <i class="ti ti-brand-facebook-filled text-yellow"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ $shareComponent['twitter'] }}" target="_blank" class="link-light">
                                        <i class="ti ti-brand-twitter-filled text-yellow"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ $shareComponent['linkedin'] }}" target="_blank" class="link-light">
                                        <i class="ti ti-brand-linkedin text-yellow"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ $shareComponent['telegram'] }}" target="_blank" class="link-light">
                                        <i class="ti ti-brand-telegram text-yellow"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ $shareComponent['whatsapp'] }}" target="_blank" class="link-light">
                                        <i class="ti ti-brand-whatsapp text-yellow"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Second Row: Policy Links -->
                    <div class="row mt-3">
                        <div class="col">
                            <ul class="list-inline list-inline-dots text-center text-md-start text-lg-start mb-0">
                                {{-- Privacy Policy --}}
                                @if ($business_card_details->privacy_policy)
                                    <li class="list-inline-item">
                                        <a href="{{ route('store.privacy.policy', $business_card_details->card_url) }}"
                                            class="link-secondary">{{ __('Privacy Policy') }}</a>
                                    </li>
                                @endif

                                {{-- Terms and Conditions --}}
                                @if ($business_card_details->terms_and_conditions)
                                    <li class="list-inline-item">
                                        <a href="{{ route('store.terms.and.conditions', $business_card_details->card_url) }}"
                                            class="link-secondary">{{ __('Terms & Conditions') }}</a>
                                    </li>
                                @endif

                                {{-- Return/Refund Policy --}}
                                @if ($business_card_details->refund_policy)
                                    <li class="list-inline-item">
                                        <a href="{{ route('store.refund.policy', $business_card_details->card_url) }}"
                                            class="link-secondary">{{ __('Return/Refund Policy') }}</a>
                                    </li>
                                @endif

                                {{-- Shipping Policy --}}
                                @if ($business_card_details->shipping_policy)
                                    <li class="list-inline-item">
                                        <a href="{{ route('store.shipping.policy', $business_card_details->card_url, $business_card_details->card_url) }}"
                                            class="link-secondary">{{ __('Shipping Policy') }}</a>
                                    </li>
                                @endif

                                {{-- Cookie Policy --}}
                                @if ($business_card_details->cookie_policy)
                                    <li class="list-inline-item">
                                        <a href="{{ route('store.cookie.policy', $business_card_details->card_url) }}"
                                            class="link-secondary">{{ __('Cookie Policy') }}</a>
                                    </li>
                                @endif

                                {{-- Contact Information / Customer Support Policy --}}
                                @if ($business_card_details->customer_support_policy)
                                    <li class="list-inline-item">
                                        <a href="{{ route('store.contact.information', $business_card_details->card_url) }}"
                                            class="link-secondary">{{ __('Contact Us') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- Bottom Navbar -->
        @include('templates.includes.bottom-bar', ['color' => 'yellow', 'bg' => 'dark'])
        <!-- End Bottom Navbar -->
    </div>

    {{-- Cart Items --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title fs-custom" id="offcanvasEndLabel">{{ __('Cart Items') }}</h2>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="row">
                <!-- Cart Items -->
                <div class="row" id="cart_items"></div>
            </div>

            <div id="empty-cart" class="p-3">
                <!-- Empty Cart -->
                <p class="px-4 py-4 mb-4 text-center fs-2">{{ __('Your cart is empty.') }}</p>

                <!-- Start Shopping -->
                <a class="btn btn-yellow d-flex" data-bs-dismiss="offcanvas"
                    aria-label="Close">{{ __('Start Shopping') }}</a>
            </div>
        </div>
        <div class="offcanvas-footer border-top">
            <div id="cart-pricing"></div>
            <!-- Place Order -->
            <a class="btn btn-yellow fs-2" id="place-order"
                onclick="placeOrder()">{{ __('Place WhatsApp Order') }}</a>
        </div>
    </div>

    {{-- Place order --}}
    <div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Please fill following details:') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label class="form-label required" for="cus_name">{{ __('Full Name') }}</label>
                        <input type="text" class="form-control" id="cus_name" required />
                    </div>
                    <!-- Mobile -->
                    <div class="mb-3">
                        <label class="form-label required" for="cus_mobile">{{ __('Mobile') }}</label>
                        <input type="number" class="form-control" placeholder="{{ __('Mobile number with country code, excluding +') }}" id="cus_mobile" required />
                    </div>
                    <!-- Address -->
                    <div class="mb-3">
                        <label class="form-label required" for="cus_address">{{ __('Address') }}</label>
                        <input type="text" class="form-control" id="cus_address" required />
                    </div>

                    {{-- Check delivery options --}}
                    @if ($deliveryOptions != null)
                        <!-- Delivery Type -->
                        <div class="mb-3">
                            <div class="form-label required mb-3">{{ __('Delivery Type') }}</div>
                            <div class="d-flex flex-column gap-2">
                                {{-- Order For Delivery --}}
                                @if (isset($deliveryOptions->order_for_delivery) && $deliveryOptions->order_for_delivery == 1)
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="cus_delivery_type"
                                            id="delivery_order" value="Order For Delivery" checked>
                                        <span class="form-check-label">{{ __('Order For Delivery') }}</span>
                                    </label>
                                @endif

                                {{-- Take Away --}}
                                @if (isset($deliveryOptions->take_away) && $deliveryOptions->take_away == 1)
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="cus_delivery_type"
                                            id="take_away" value="Take Away">
                                        <span class="form-check-label">{{ __('Take Away') }}</span>
                                    </label>
                                @endif

                                {{-- Dine In --}}
                                @if (isset($deliveryOptions->dine_in) && $deliveryOptions->dine_in == 1)
                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="cus_delivery_type"
                                            id="dine_in" value="Dine In">
                                        <span class="form-check-label">{{ __('Dine In') }}</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label class="form-label" for="cus_notes">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="cus_notes" name="cus_notes" rows="3"></textarea>
                    </div>

                    {{-- Customer Notes --}}
                    <div class="mb-3">
                        <small>{{ __('Customer Notes') }}: </small>
                        <small
                            class="text-muted">{{ __("After you click the Confirm button, WhatsApp will open. Tap 'Send' in WhatsApp to send your order to the shop owner.") }}</small>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <!-- Close -->
                    <button type="button" class="btn btn-dark"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <!-- Confirm -->
                    <button type="button" class="btn btn-yellow"
                        onclick="confirmOrder()">{{ __('Confirm') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Newsletter Popup --}}
    @if ($business_card_details != null)
        {{-- Check Newsletter --}}
        @if (!empty($business_card_details->is_newsletter_pop_active) && $business_card_details->is_newsletter_pop_active == 1 && !request()->cookie('newsletter_' . $business_card_details->card_id))
            <div class="modal fade" id="newsletterModal" tabindex="-1" aria-hidden="true"
                style="background: rgba(0, 0, 0, 0.7);">
                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h3 class="modal-title fs-2">{{ __('Subscribe to our newsletter') }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <!-- Modal Body -->
                        <div class="modal-body">
                            <form action="{{ config('app.url') }}/subscribe/store/newsletter" method="post">
                                @csrf
                                <div class="row">
                                    <input type="hidden" id="card_id" name="card_id"
                                        value="{{ $business_card_details->card_id }}">
                                    <!-- Full Name -->
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="newsletter_email"
                                            placeholder="{{ __('Your email address') }}" name="email" required />
                                    </div>
                                    <!-- Button -->
                                    <div class="col-4">
                                        <button type="submit"
                                            class="btn btn-yellow w-100">{{ __('Subscribe') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Auto Open Modal --}}
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    "use strict";

                    const newsletterModal = new bootstrap.Modal(document.getElementById('newsletterModal'), {
                        keyboard: false
                    });

                    // Open after 3 seconds
                    setTimeout(() => {
                        newsletterModal.show();
                    }, 3000);
                });
            </script>
        @endif
    @endif

    {{-- Information Popup --}}
    @if ($business_card_details != null)
        {{-- Check Information Popup --}}
        @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
            @php
                // Use fully qualified class name instead of `use`
                $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();

                // Variables
                $confetti_effect = $information_pop->confetti_effect ?? 0;
                $img = $information_pop->info_pop_image ?? '';
                $title = $information_pop->info_pop_title ?? '';
                $desc = $information_pop->info_pop_desc ?? '';
                $button_text = $information_pop->info_pop_button_text ?? '';
                $button_url = $information_pop->info_pop_button_url ?? '#';
            @endphp

            <div class="modal fade" id="informationModal" tabindex="-1" aria-hidden="true"
                style="background: rgba(0, 0, 0, 0.7);">
                <div class="modal-dialog modal-md modal-dialog-end" role="document">
                    <div class="modal-content">
                        <!-- Modal Body -->
                        <div class="modal-body">
                            <div class="d-flex justify-content-end align-items-center">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="row d-flex justify-content-center align-items-center">
                                {{-- Image --}}
                                <div class="w-25 h-25 mb-2">
                                    <img src="{{ url($img) }}" alt="{{ $title }}"
                                        class="img-fluid object-fit-cover rounded-4" />
                                </div>
                                {{-- Title --}}
                                <div class="mb-2">
                                    <h2 class="text-center mb-1">{{ __($title) }}</h2>
                                    <p class="text-center mb-1 text-muted">{{ __($desc) }}</p>
                                </div>
                                {{-- Button --}}
                                <div class="d-flex justify-content-center align-items-center">
                                    <a href="{{ $button_url }}" target="_blank"
                                        class="btn btn-yellow w-100">{{ __($button_text) }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Auto Open Modal --}}
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    "use strict";

                    const informationModal = new bootstrap.Modal(document.getElementById('informationModal'), {
                        keyboard: false
                    });

                    setTimeout(() => {
                        informationModal.show();
                    }, 5000);
                });
            </script>

            {{-- Enable Confetti --}}
            @if ($confetti_effect == 1)
                <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
                <script>
                    setTimeout(() => {
                        "use strict";

                        // Create and insert custom canvas above modal
                        const confettiCanvas = document.createElement('canvas');
                        confettiCanvas.id = 'confetti-overlay';
                        confettiCanvas.style.position = 'fixed';
                        confettiCanvas.style.top = 0;
                        confettiCanvas.style.left = 0;
                        confettiCanvas.style.width = '100%';
                        confettiCanvas.style.height = '100%';
                        confettiCanvas.style.pointerEvents = 'none';
                        confettiCanvas.style.zIndex = '1065'; // Higher than modal
                        document.body.appendChild(confettiCanvas);

                        // Bind confetti to this canvas
                        const myConfetti = confetti.create(confettiCanvas, {
                            resize: true,
                            useWorker: true
                        });

                        const randomColors = Array.from({
                                length: 7
                            }, () =>
                            '#' + Math.floor(Math.random() * 16777215).toString(16)
                        );

                        myConfetti({
                            particleCount: window.innerWidth > 768 ? 200 : 100,
                            spread: 120,
                            origin: {
                                x: 0.5,
                                y: 0.75
                            },
                            colors: randomColors
                        });

                        // Optional: remove canvas after 8 seconds
                        setTimeout(() => {
                            document.getElementById('confetti-overlay')?.remove();
                        }, 8000);

                    }, 5000);
                </script>
            @endif
        @endif
    @endif

    <!-- Error alert -->
    <div class="alert alert-important alert-danger alert-dismissible alert-float" id="errorAlertContainer"
        role="alert">
        <div id="errorAlertMessage"></div>
    </div>

    <!-- Success alert -->
    <div class="alert alert-important alert-success alert-dismissible alert-float" id="successAlertContainer"
        role="alert">
        <div id="successAlertMessage"></div>
    </div>

    <!-- Include PWA modal -->
    @if ($plan_details != null)
        {{-- Check PWA --}}
        @if ($plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
            @include('vendor.laravelpwa.bs_pwa_modal')
        @endif
    @endif

    {{-- WharApp Chat --}}
    @include('templates.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    <!-- Core -->
    <script type="text/javascript" src="{{ url('js/tabler.min.js') }}"></script>
    <script src="{{ url('js/script.js') }}"></script>
    <script src="{{ url('js/data-filter.js') }}"></script>
    {{-- Swiper JS --}}
    <script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>

    {{-- Custom JS --}}
    @yield('custom-js')

    <script>
        // Injected from Laravel
        const config = {
            currencyCode: @json($currency),
            formatType: @json($config[55]->config_value ?? '1.234.567,89'),
            decimalPlaces: @json((int) ($config[56]->config_value ?? 2))
        };

        const currencies = {!! json_encode(App\Currency::where('status', 1)
        ->select('iso_code', 'symbol', 'symbol_first')
        ->get()) !!};

        // Determine currency symbol and position
        let currencySymbol = '';
        let symbolFirst = true;

        for (let i = 0; i < currencies.length; i++) {
            if (currencies[i].symbol === config.currencyCode) {
                currencySymbol = currencies[i].symbol;
                symbolFirst = currencies[i].symbol_first !== false && currencies[i].symbol_first !== "false";
                break;
            }
        }

        // Your original function
        function jsFormatCurrency(amount, decimalPlaces = 2, formatType = "1,234,567.89", currencySymbol =
            "{{ $currency }}") {
            let formattedAmount;

            switch (formatType) {
                case "1,234,567.89": // US style
                    formattedAmount = amount.toLocaleString('en-US', {
                        minimumFractionDigits: decimalPlaces,
                        maximumFractionDigits: decimalPlaces
                    });
                    break;

                case "1.234.567,89": // German style
                    formattedAmount = amount.toLocaleString('de-DE', {
                        minimumFractionDigits: decimalPlaces,
                        maximumFractionDigits: decimalPlaces
                    });
                    break;

                case "1 234 567,89": // French style
                    formattedAmount = amount.toLocaleString('fr-FR', {
                        minimumFractionDigits: decimalPlaces,
                        maximumFractionDigits: decimalPlaces
                    });
                    break;

                case "1'234'567.89": // Swiss style
                    formattedAmount = amount.toFixed(decimalPlaces).replace(/\B(?=(\d{3})+(?!\d))/g, "'");
                    break;

                case "12,34,567.89": // Indian style
                    formattedAmount = formatIndianCurrency(amount, decimalPlaces);
                    break;

                default:
                    formattedAmount = amount.toLocaleString('en-US', {
                        minimumFractionDigits: decimalPlaces,
                        maximumFractionDigits: decimalPlaces
                    });
            }

            return symbolFirst ? currencySymbol + formattedAmount : formattedAmount + currencySymbol;
        }

        function formatIndianCurrency(amount, decimalPlaces = 2) {
            let [integerPart, decimalPart] = amount.toFixed(decimalPlaces).split(".");

            let lastThree = integerPart.slice(-3);
            let otherNumbers = integerPart.slice(0, -3);
            if (otherNumbers !== '') {
                otherNumbers = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",");
                integerPart = otherNumbers + "," + lastThree;
            } else {
                integerPart = lastThree;
            }

            return integerPart + "." + decimalPart;
        }
    </script>

    <script>
        // Global variables
        var cart = [];
        var whatsAppNumber = "{{ $enquiry_button }}";
        var whatsAppMessage = `{!! $whatsapp_msg !!}`;
        var currency = "";

        // Function to initialize page
        function initializePage() {
            $('.preloader-wrapper').fadeOut('slow');
            getData();
        }

        // Fetch data function
        function getData() {
            var storageKey = "cart_" + "{{ $business_card_details->card_id }}";
            cart = localStorage.getItem(storageKey) ? JSON.parse(localStorage.getItem(storageKey)) : [];
            updateList();
            updateBadge();
        }

        // Sort function
        function itemSort(sort) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            window.location.href = "{{ url()->current() }}?" + params.toString();
        }

        // Add to cart function
        function addToCart(pid) {
            var productName = $("#" + pid + "_product_name").text();
            var price = $("#" + pid + "_price").text();
            var product_image = $("#" + pid + "_product_image").attr("src");
            var subtitle = $("#" + pid + "_subtitle").text();

            var found = cart.findIndex(item => item.product_id == pid);
            if (found !== -1) {
                cart[found].qty++;
                successAlert('{{ __('Cart updated') }}');
            } else {
                cart.push({
                    product_name: $.trim(productName),
                    price: price,
                    product_id: pid,
                    currency: currency,
                    qty: 1,
                    product_image: product_image,
                    subtitle: subtitle
                });
                successAlert("{{ __('Item added to cart') }}");
            }
            updateList();
            updateBadge();
            updateStorage();
        }

        // Update cart list
        function updateList() {
            var cart_items = "";
            var grandTotal = 0;

            cart.forEach((item, index) => {
                const total_price = item.qty * Number(item.price);
                grandTotal += total_price;

                // PHP values
                var formatType = "{{ $config[55]->config_value ?? '1.234.567,89' }}";
                var setDecimalsPlaces = {{ $config[56]->config_value ?? 2 }};

                cart_items += `<div class="col-12 mb-3">`;
                cart_items += `<div class="card p-3">`;
                cart_items += `<div class="d-flex align-items-center">`;

                // LEFT: Image
                cart_items += `
                <div class="me-3 flex-shrink-0" style="width: 100px; height: 100px;">
                    <img src="${item.product_image}" class="img-fluid rounded object-fit-cover h-100 w-100" alt="${item.product_name}" />
                </div>`;

                // RIGHT: Product Info
                cart_items += `<div class="flex-grow-1">`;
                cart_items += `<h4 class="text-white fs-2 mb-1">${item.product_name}</h4>`;
                cart_items += `<small class="text-muted d-block">${item.subtitle}</small>`;
                cart_items +=
                    `<p class="text-white mt-1 mb-2 fw-semibold fs-3">${jsFormatCurrency(total_price, setDecimalsPlaces, formatType)}</p>`;

                // Buttons: Quantity & Actions
                cart_items += `<div class="d-flex align-items-center flex-wrap">`;

                // Round buttons
                cart_items +=
                    `<button onclick="reduceQty(${index})" class="btn text-white d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="m-0 icon icon-tabler icons-tabler-outline icon-tabler-minus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /></svg></button>`;
                cart_items +=
                    `<span class="text-white p-0 d-flex align-items-center justify-content-center fs-3 fw-bold" style="width:35px;height:35px;">${item.qty}</span>`;
                cart_items +=
                    `<button onclick="addQty(${index})" class="btn text-white d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus m-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg></button>`;
                cart_items +=
                    `<button onclick="removeFromCart(${index})" class="ms-3 btn btn-danger p-0 d-flex align-items-center justify-content-center" style="width:35px;height:35px;"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash m-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>`;

                cart_items += `</div>`; // end buttons
                cart_items += `</div>`; // end text/info
                cart_items += `</div>`; // end row
                cart_items += `</div>`; // end card
                cart_items += `</div>`; // end col

            });

            if (grandTotal > 0) {
                // PHP values
                var formatType = "{{ $config[55]->config_value ?? '1.234.567,89' }}";
                var setDecimalsPlaces = {{ $config[56]->config_value ?? 2 }};

                $("#cart-pricing").html(
                    `<h3 class="font-bold fs-2 d-flex justify-content-between"><span>{{ __('Grand total') }}</span><span class="text-yellow">${jsFormatCurrency(grandTotal, setDecimalsPlaces, formatType)}</span></h3>`
                );
            } else {
                $("#cart-pricing").html(``);
            }

            $("#cart_items").html(cart_items);
        }

        // Update badge function
        function updateBadge() {
            var badgeCount = cart.length;
            if (badgeCount > 0) {
                $("#empty-cart").hide();
                $("#badge").text(badgeCount).show();
                $("#place-order").show().attr("class", "btn btn-yellow d-flex");
            } else {
                $("#place-order").hide().attr("class", "btn btn-yellow d-none");
                $("#badge").hide();
                $("#empty-cart").show();
            }
        }

        // Reduce quantity function
        function reduceQty(i) {
            if (cart[i].qty == 1) {
                removeFromCart(i);
            } else {
                cart[i].qty--;
                updateBadge();
                updateList();
            }
            updateStorage();
        }

        // Add quantity function
        function addQty(i) {
            cart[i].qty++;
            updateBadge();
            updateList();
            updateStorage();
        }

        // Remove from cart function
        function removeFromCart(i) {
            cart.splice(i, 1);
            successAlert(`{{ __('Item Removed') }}`);
            updateStorage();
            updateBadge();
            updateList();
        }

        // Business Hours
        const businessHours = @json(json_decode($businessHours->business_hours ?? '{}', true)); // convert PHP JSON string to JS object

        // Check if within business hours
        function isWithinBusinessHours() {
            "use strict";

            const now = new Date();

            const days = [
                'sunday', 'monday', 'tuesday', 'wednesday',
                'thursday', 'friday', 'saturday'
            ];
            const day = days[now.getDay()];

            const currentTime = now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');

            const hours = businessHours[day];

            if (!hours || !hours.start || !hours.end) return false;

            return (currentTime >= hours.start && currentTime <= hours.end);
        }

        // Place order
        function placeOrder() {
            "use strict";

            if (isWithinBusinessHours()) {
                const myModal = new bootstrap.Modal(document.getElementById('orderModal'), {
                    keyboard: false
                });
                myModal.show();
            } else {
                // Show error message
                errorAlert('{{ __('Sorry, we are currently closed.') }}');
            }
        }

        // Function to confirm order details
        function confirmOrder() {
            var cusName = document.getElementById('cus_name').value;
            var cusMobile = document.getElementById('cus_mobile').value;
            var cusAddress = document.getElementById('cus_address').value;
            var cusDeliveryType = document.querySelector('input[name="cus_delivery_type"]:checked').value;
            var cusNotes = document.getElementById('cus_notes').value;

            if (!cusName || !cusMobile || !cusAddress || !cusDeliveryType) {
                errorAlert('{{ __('Please fill out all fields.') }}');
                return false;
            }

            createWhatsAppLink([cusName, cusMobile, cusAddress, cusDeliveryType, cusNotes]);
            var myModalEl = document.getElementById('orderModal');
            var modal = bootstrap.Modal.getInstance(myModalEl);
            modal.hide();
        }

        // Function to create WhatsApp link for order details
        function createWhatsAppLink(cusDetails) {
            "use strict";
            // Check if customer details are valid
            if (cusDetails[0].length >= 3 && cusDetails[1].length >= 4) {
                // Initialize products list and grand total
                let productsList = `\n- - - - - - - - - - - - - - - - - - - -\n📦 *{{ __('Order Details:') }}* \n\n`;
                let grandTotal = 0;

                // PHP values
                var formatType = "{{ $config[55]->config_value ?? '1.234.567,89' }}";
                var setDecimalsPlaces = {{ $config[56]->config_value ?? 2 }};

                // Iterate through cart items
                cart.forEach(item => {
                    const itemCost = Number(item.qty) * Number(item.price);
                    const cartPrice = Number(item.price);

                    // Append product details to products list
                    productsList +=
                        `${item.product_name} - ${item.qty} X  ${jsFormatCurrency(cartPrice, setDecimalsPlaces, formatType)} = ${jsFormatCurrency(itemCost, setDecimalsPlaces, formatType)}\n`;
                    grandTotal += itemCost;
                });

                // Place order ajax
                $.ajax({
                    url: "{{ config('app.url') }}/order/place",
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        store_id: "{{ $business_card_details->card_id }}",
                        customer_name: cusDetails[0],
                        customer_phone: cusDetails[1],
                        delivery_address: cusDetails[2],
                        delivery_note: cusDetails[4],
                        delivery_method: cusDetails[3],
                        order_items: cart,
                        total_price: grandTotal,
                    },
                    success: function(data) {
                        // Check if order was placed successfully
                        if (data.status == "success") {
                            // Map of dynamic delivery type values to translated strings
                            const deliveryTypeMap = {
                                "Order For Delivery": "{{ __('Order For Delivery') }}",
                                "Take Away": "{{ __('Take Away') }}",
                                "Dine In": "{{ __('Dine In') }}"
                            };

                            let deliveryTypeKey = cusDetails[3]; // e.g., "pickup", "cod", etc.
                            let deliveryType = deliveryTypeMap[deliveryTypeKey] || deliveryTypeKey;

                            // Add total and customer details to products list
                            productsList += `\n- - - - - - - - - - - - - - - - - - - -\n`;
                            productsList +=
                                `*{{ __('Total') }}* : *${jsFormatCurrency(grandTotal, setDecimalsPlaces, formatType)}*\n\n`;
                            productsList += `📞 *{{ __('Customer Details:') }}* \n`;
                            productsList += `{{ __('Customer Name') }} : ${cusDetails[0]}\n`;
                            productsList += `{{ __('Contact Number') }} : ${cusDetails[1]}\n`;
                            productsList += `{{ __('Delivery Address') }} : ${cusDetails[2]}\n`;
                            productsList += `{{ __('Delivery Type') }} : ${deliveryType}\n`;

                            if (cusDetails[4]) {
                                productsList += `{{ __('Notes') }} : ${cusDetails[4]}\n\n`;
                            } else {
                                productsList += `\n\n`;
                            }

                            // Prepare WhatsApp share content
                            let waShareContent = `🎉 *{{ __('New Order') }}* \n`;
                            waShareContent += productsList + `*${whatsAppMessage}*`;

                            // Construct WhatsApp link and open in new tab
                            const link =
                                `https://api.whatsapp.com/send/?phone=${whatsAppNumber}&text=${encodeURI(waShareContent)}`;
                            // Confirm before opening
                            const opened = window.open(link, '_blank');

                            // Fallback if pop-up was blocked (especially on Safari)
                            if (!opened) {
                                window.location.href = link;
                            }

                            // Reset cart and update local storage
                            cart = [];
                            updateStorage();

                            // Show success alert
                            successAlert('{{ __('Order Placed!') }}');
                        } else {
                            // Show error message
                            errorAlert(data.message);
                        }
                    },
                    error: function(error) {
                        // Show error message
                        errorAlert(error.responseJSON.message);
                    }
                });
            } else {
                // If customer details are invalid, prompt to place order
                placeOrder();
            }
        }

        // Update local storage function
        function updateStorage() {
            localStorage.setItem("cart_" + "{{ $business_card_details->card_id }}", JSON.stringify(cart));
        }

        // Show alert function
        function showAlert(containerId, message) {
            const alertContainer = document.getElementById(containerId);
            const alertMessage = alertContainer.querySelector('div');
            alertMessage.innerHTML = message;
            alertContainer.classList.add('show');
            alertContainer.style.display = 'block';

            // Optional styling (add only once)
            alertContainer.style.maxWidth = '500px';
            alertContainer.style.margin = '0 auto';
            alertContainer.style.width = '75%'; // Optional for mobile responsiveness
            alertContainer.style.textAlign = 'center';

            setTimeout(() => {
                alertContainer.classList.remove('show');
                setTimeout(() => {
                    alertContainer.style.display = 'none';
                }, 1000);
            }, 3000);
        }

        // Error alert function
        function errorAlert(message) {
            showAlert('errorAlertContainer', message);
        }

        // Success alert function
        function successAlert(message) {
            showAlert('successAlertContainer', message);
        }

        // Initial function call
        initializePage();
    </script>
</body>

</html>
