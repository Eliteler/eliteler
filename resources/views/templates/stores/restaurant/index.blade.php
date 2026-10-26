{{-- Restaurant home: banners, categories and the menu --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.restaurant.includes.head')
    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-restaurant-red" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #F9C5C5;">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.restaurant.includes.header', [
                        'bg_color' => 'restaurant-red',
                        'badge_color' => 'primary',
                    ])

                    <div class="row mt-7 px-3 px-lg-4">
                        @if (!empty($business_card_details->sub_title) || !empty($business_card_details->title2) || !empty($business_card_details->subtitle2))
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-restaurant-red p-3">
                                    @include('templates.stores.shared.subtitle-lines')
                                    {{ $business_card_details->sub_title }}
                                </h3>
                            </div>
                        @endif

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
                                            <img class="rounded-restaurant img-fluid d-block mx-auto"
                                                alt="{{ $business_card_details->title }}" src="{{ url($cover) }}" />
                                        </swiper-slide>
                                    @endforeach
                                @else
                                    <swiper-slide>
                                        <img class="rounded-restaurant img-fluid d-block mx-auto"
                                            alt="{{ $business_card_details->title }}"
                                            src="{{ $business_card_details->cover ? url($business_card_details->cover) : asset('images/default-cover.png') }}" />
                                    </swiper-slide>
                                @endif
                            </swiper-container>
                        </div>

                        <div class="row {{ request('category') ? 'ps-2 ps-lg-3' : '' }}">
                            <!-- Categories -->
                            <div
                                class="{{ request('category') ? 'bg-opacity-red card col-md-3 my-3 px-3' : 'col-md-12 my-3' }}">
                                <div class="position-relative {{ request('category') ? 'd-none' : '' }}">
                                    <img class="position-absolute rotate-loop"
                                        src="{{ asset('img/templates/restaurant/2.png') }}"
                                        style="
                                            width: 130px;
                                            height: 130px;
                                            left: -75px;
                                            top: -15px;
                                            opacity: 0.6;
                                            filter: blur(0.8px);
                                        " />
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
                                            class="btn btn-restaurant-red w-100">{{ __('Filter') }}</button>
                                    </div>
                                </form>

                                <h2
                                    class="head pt-4 pb-5 text-center position-relative fs-custom fw-bold {{ request('category') ? 'd-none' : '' }}">
                                    {{ __('Categories') }}
                                    <div
                                        class="position-absolute start-50 translate-middle-x bg-restaurant-red bottom-bar3">
                                    </div>
                                </h2>

                                <h2
                                    class="head pt-4 pb-5 text-start position-relative fs-custom fw-bold {{ request('category') ? '' : 'd-none' }}">
                                    {{ __('Categories') }}
                                    <div class="position-absolute start-0 bg-restaurant-red bottom-bar3"></div>
                                </h2>

                                <!-- All Categories -->
                                <swiper-container space-between="10"
                                    class="mySwiper {{ request('category') ? 'd-none' : '' }}" autoplay-delay="2500"
                                    breakpoints='{
                                        "320": { "slidesPerView": 3 },
                                        "640": { "slidesPerView": 5 }, 
                                        "1024": { "slidesPerView": 8 } 
                                    }'
                                    autoplay-disable-on-interaction="false" loop="true">

                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <swiper-slide class="cursor-pointer">
                                            <a href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="mt-2">
                                                    <!-- Thumbnail -->
                                                    <div class="image-wrapper mx-auto mb-2 card p-2 rounded-restaurant">
                                                        <img src="{{ url($category->thumbnail) }}"
                                                            class="img-fluid category-thumbnail rounded-restaurant"
                                                            alt="{{ $category->category_name }}" />
                                                    </div>
                                                    <!-- Category Name -->
                                                        <h3 class="fs-3 text-truncate single-product text-dark text-center">
                                                            {{ __($category->category_name) }}
                                                        </h3>
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
                                                    alt="All" class="rounded-restaurant"
                                                    style="width: 40px; height: 40px; object-fit: contain;">
                                                <h3 class="mb-0 text-dark fs-3">{{ __('All') }}</h3>
                                            </div>
                                        </a>
                                    </div>
                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <div class="py-3 border-top">
                                            <a
                                                href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="text-decoration-none">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <!-- Thumbnail -->
                                                        <img src="{{ url($category->thumbnail) }}"
                                                            class="rounded-restaurant"
                                                            style="width: 40px; height: 40px; object-fit: contain;"
                                                            alt="{{ $category->category_name }}" />
                                                        @php
                                                            $category_name = urlencode(
                                                                strtolower($category->category_name),
                                                            );
                                                        @endphp
                                                        <!-- Category Name -->
                                                        <h3
                                                            class="mb-0 fs-3 {{ request('category') == $category_name ? 'text-restaurant-red' : 'text-dark' }}">
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
                            <div class="{{ request('category') ? 'col-md-9 ps-lg-4 mt-2' : 'col-md-12' }}">
                                <div class="position-relative {{ request('category') ? 'd-none' : '' }}">
                                    <img class="position-absolute float-loop" src="{{ asset('img/templates/restaurant/3.png') }}"
                                        style="
                                            width: 115px;
                                            height: 115px;
                                            top: -20px;
                                            right: -50px;
                                            opacity: 0.7;
                                            filter: blur(0.8px);
                                        " />
                                </div>
                                <div class="position-relative {{ request('category') ? '' : 'd-none' }}">
                                    <img class="position-absolute blur-img float-loop"
                                        src="{{ asset('img/templates/restaurant/3.png') }}" />
                                </div>
                                <div class="row align-items-center {{ request('category') ? 'mt-2' : 'd-none' }}">
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
                                                class="btn btn-restaurant-red w-100">{{ __('Search') }}</button>
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
                                <h2
                                    class="head pt-3 pb-5 text-center position-relative fs-custom fw-bold {{ request('category') ? 'd-none' : '' }}">
                                    {{ __('Products') }}
                                    <div
                                        class="position-absolute start-50 translate-middle-x bg-restaurant-red bottom-bar3">
                                    </div>
                                </h2>

                                <h2
                                    class="head pt-5 pb-5 text-start position-relative fs-custom fw-bold {{ request('category') ? '' : 'd-none' }}">
                                    {{ __('Products') }}
                                    <div class="position-absolute start-0 bg-restaurant-red bottom-bar3"></div>
                                </h2>

                                <!-- Foreach -->
                                @if (count($products) > 0)
                                    <div class="row">
                                        <!-- Foreach -->
                                        @foreach ($products as $product)
                                            @include('templates.stores.restaurant.partials.product-card', [
                                                'class' => 'col-12 ' . (request('category') ? 'col-md-4' : 'col-md-3') . ' mb-3',
                                            ])
                                        @endforeach

                                        {{-- Paginate --}}
                                        <div class="col-md-12 my-3">
                                            @if (request()->has('category'))
                                                {{ $products->appends(request()->except('page'))->links('vendor.pagination.store', ['color' => 'restaurant-red']) }}
                                            @else
                                                {{ $products->links('vendor.pagination.store', ['color' => 'restaurant-red']) }}
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
                        @include('templates.stores.restaurant.includes.footer')
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Navbar -->
        @include('templates.stores.includes.bottom-bar', ['color' => 'restaurant-red', 'bg' => 'light'])
        <!-- End Bottom Navbar -->
    </div>

    @include('templates.stores.restaurant.partials.cart-order-modal')
    @include('templates.stores.restaurant.includes.popups')
    @include('templates.stores.restaurant.includes.scripts')

    <script>
        // Sort the listing (called by the sort menu)
        function itemSort(sort) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            window.location.href = "{{ url()->current() }}?" + params.toString();
        }
    </script>
</body>

</html>
