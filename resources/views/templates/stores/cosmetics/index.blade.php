<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('templates.stores.cosmetics.includes.head')

    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ App::isLocale('ar') || App::isLocale('ur') || App::isLocale('he') ? 'rtl' : 'ltr' }}">
    <!-- Preloader -->
    @include('templates.stores.cosmetics.includes.preloader')

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #FFDCDC;">

        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.cosmetics.includes.header', [
                        'bg' => 'rose',
                        'bg_color' => 'rose',
                        'badge_color' => 'rose',
                    ])

                    <div class="row mt-7 px-3 px-lg-4">
                        {{-- Hide Greeting & Banner Slider when ?category= is present in URL --}}
                        @if (!request()->has('category'))
                            {{-- Greeting message --}}
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-rose p-3">
                                    @include('templates.stores.shared.subtitle-lines')
                                    {{ $business_card_details->sub_title }}</h3>
                            </div>

                            <!-- Banner Images -->
                            <div class="col-md-12">
                                <swiper-container space-between="20" class="mySwiper" autoplay-delay="2500"
                                    pagination="true" autoplay-disable-on-interaction="false" loop="true">
                                    {{-- Cover --}}
                                    @if (is_array(json_decode($business_card_details->cover)) == true)
                                        @foreach (json_decode($business_card_details->cover) as $cover)
                                            <swiper-slide>
                                                <img class="rounded-cosmetics img-fluid d-block mx-auto"
                                                    alt="{{ $business_card_details->title }}"
                                                    src="{{ url($cover) }}" />
                                            </swiper-slide>
                                        @endforeach
                                    @else
                                        <swiper-slide>
                                            <img class="rounded-cosmetics img-fluid d-block mx-auto"
                                                alt="{{ $business_card_details->title }}"
                                                src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}" />
                                        </swiper-slide>
                                    @endif
                                </swiper-container>
                            </div>
                        @endif

                        {{-- Success Message --}}
                        @if (session('success'))
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-success p-3">{{ session('success') }}</h3>
                            </div>
                        @endif

                        {{-- Failed Message --}}
                        @if (session('failed'))
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-danger p-3">{{ session('failed') }}</h3>
                            </div>
                        @endif

                        <div class="row {{ request('category') ? 'ps-2 ps-lg-3' : '' }}">
                            <!-- Categories -->
                            <div
                                class="{{ request('category') ? 'bg-opacity rounded-cosmetics col-md-3 my-3 px-3' : 'col-md-12 my-3' }}">
                                <div class="position-relative {{ request('category') ? 'd-none' : '' }}">
                                    <img class="position-absolute" src="{{ asset('img/templates/cosmetics/3.png') }}"
                                        style="width: 140px; height: 140px; left: -40px; opacity: 0.4; top: -12px; transform: rotate(34deg);" />
                                </div>
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
                                            class="btn btn-rose w-100">{{ __('Filter') }}</button>
                                    </div>
                                </form>

                                <h2
                                    class="head pt-5 pb-6 text-center position-relative fs-custom fw-bold {{ request('category') ? 'd-none' : '' }}">
                                    {{ __('Categories') }}
                                    <div
                                        class="position-absolute start-50 translate-middle-x bg-rose bottom-bar-custom">
                                    </div>
                                </h2>

                                <h2
                                    class="head pt-4 pb-5 text-start position-relative fs-custom fw-bold {{ request('category') ? '' : 'd-none' }}">
                                    {{ __('Categories') }}
                                    <div class="position-absolute start-0 bg-rose bottom-bar2"></div>
                                </h2>

                                <!-- All Categories -->
                                <swiper-container space-between="10"
                                    class="mySwiper {{ request('category') ? 'd-none' : '' }}" autoplay-delay="2500"
                                    breakpoints='{
                                        "320": { "slidesPerView": 2 },
                                        "640": { "slidesPerView": 4 }, 
                                        "1024": { "slidesPerView": 6 } 
                                    }'
                                    autoplay-disable-on-interaction="false" loop="true">

                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <swiper-slide class="cursor-pointer">
                                            {{-- Dynamic store URL for category link --}}
                                            <a
                                                href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="card rounded-cosmetics">
                                                    <div
                                                        class="card-body d-flex flex-column align-items-center justify-content-center gap-2 p-3">
                                                        <!-- Thumbnail -->
                                                        <div
                                                            class="ratio ratio-1x1 d-flex align-items-center justify-content-center">
                                                            <img src="{{ url($category->thumbnail) }}"
                                                                alt="{{ $category->category_name }}"
                                                                class="rounded-cosmetics img-fluid category-thumbnail">
                                                        </div>
                                                        <!-- Category Name -->
                                                        <h3 class="mt-2 mb-0 fs-3 text-truncate single-product"
                                                            style="max-width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                                            {{ __($category->category_name) }}
                                                        </h3>
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
                                                <img src="{{ asset('img/templates/all-categories.png') }}"
                                                    alt="All" class="rounded-cosmetics"
                                                    style="width: 40px; height: 40px; object-fit: contain;">
                                                <h3 class="mb-0 text-dark fs-3">{{ __('All') }}</h3>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <div class="py-3 border-top">
                                            {{-- Dynamic store URL for category list link --}}
                                            <a
                                                href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="text-decoration-none">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <!-- Thumbnail -->
                                                        <img src="{{ url($category->thumbnail) }}"
                                                            class="rounded-cosmetics"
                                                            style="width: 40px; height: 40px; object-fit: contain;"
                                                            alt="{{ $category->category_name }}" />
                                                        @php
                                                            $category_name = urlencode(
                                                                strtolower($category->category_name),
                                                            );
                                                        @endphp
                                                        <!-- Category Name -->
                                                        <h3
                                                            class="mb-0 fs-3 {{ request('category') == $category_name ? 'text-rose' : 'text-dark' }}">
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
                            <div class="mt-3 {{ request('category') ? 'col-md-9 ps-lg-4' : 'col-md-12' }}">
                                <div class="position-relative {{ request('category') ? 'd-none' : '' }}">
                                    <img class="position-absolute" src="{{ asset('img/templates/cosmetics/1.png') }}"
                                        style="
                                            width: 140px;
                                            height: 140px;
                                            right: -40px;
                                            opacity: 0.4;
                                            top: -16px;
                                            transform: rotate(-9deg);
                                        " />
                                </div>
                                <div class="position-relative {{ request('category') ? '' : 'd-none' }}">
                                    <img class="position-absolute floater-img"
                                        src="{{ asset('img/templates/cosmetics/1.png') }}"
                                        style="
                                            width: 120px;
                                            height: 120px;
                                            right: -40px;
                                            opacity: 0.4;                                           
                                            transform: rotate(-9deg);
                                        " />
                                </div>
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
                                                class="btn btn-rose w-100">{{ __('Search') }}</button>
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

                                <!-- Products Heading -->
                                <h2
                                    class="head pt-5 pb-6 text-center position-relative fs-custom fw-bold {{ request('category') ? 'd-none' : '' }}">
                                    {{ __('Products') }}
                                    <div
                                        class="position-absolute start-50 translate-middle-x bg-rose bottom-bar-custom">
                                    </div>
                                </h2>

                                <h2
                                    class="head pt-4 pb-5 text-start position-relative fs-custom fw-bold {{ request('category') ? '' : 'd-none' }}">
                                    {{ __('Products') }}
                                    <div class="position-absolute start-0 bg-rose bottom-bar2"></div>
                                </h2>

                                <!-- Foreach -->
                                @if (count($products) > 0)
                                    <div class="row">
                                        @foreach ($products as $product)
                                            @include('templates.stores.cosmetics.partials.product-card', [
                                                'class' => 'col-12 mb-3 ' . (request('category') ? 'col-md-4' : 'col-md-3'),
                                            ])
                                        @endforeach

                                        {{-- Paginate --}}
                                        <div class="col-md-12 my-3">
                                            @if (request()->has('category'))
                                                {{ $products->appends(request()->except('page'))->links('templates.stores.cosmetics.partials.pagination', ['color' => 'rose']) }}
                                            @else
                                                {{ $products->links('templates.stores.cosmetics.partials.pagination', ['color' => 'rose']) }}
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <!-- No Products Found -->
                                    <div class="row text-center">
                                        <h3 class="fs-1">{{ __('No products found') }}</h3>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Modular Footer Include --}}
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
    @include('templates.stores.cosmetics.includes.popup')

    @if ($plan_details != null && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
        @include('templates.stores.cosmetics.includes.pwa-modal')
    @endif

    @include('templates.stores.cosmetics.includes.whatsapp-float', [
        'businessImage' => $business_card_details->profile,
        'businessName' => $card_details->title,
        'whatsappNumber' => $enquiry_button,
    ])

    @include('templates.stores.cosmetics.includes.scripts')

    <script>
        function itemSort(sort) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            window.location.href = "{{ url()->current() }}?" + params.toString();
        }
    </script>
</body>

</html>
