{{-- Modern store home: banners, categories and the product listing (light and dark, 8 colours) --}}
@php
    $dark = $storeTheme->isDark();
    $ink = $dark ? 'text-white' : 'text-dark';
    $heading = $dark ? 'text-white' : '';
    $filterPanel = $dark ? 'bg-dark' : 'bg-opacity';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.modern-store.includes.head')
    @include('templates.stores.shared.custom-code')
</head>

<body @if ($dark) data-bs-theme="dark" @endif dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper {{ $dark ? 'bg-dark' : '' }}">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-{{ $storeTheme->color }}" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page">
        <!-- Navbar -->
        @include('templates.stores.includes.header', ['bg_color' => $storeTheme->color, 'badge_color' => 'danger'])

        <div class="page-wrapper mt-4 p-3">
            <div class="page-body">
                <div class="container-xl">
                    <!-- Banners -->
                    <div class="row pt-6">
                        @if (!empty($business_card_details->sub_title))
                            <div class="col-md-12">
                                <h3 class="alert alert-important alert-{{ $storeTheme->color }} p-3">
                                    {{ $business_card_details->sub_title }}
                                </h3>
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

                        <!-- Banner Images -->
                        @php
                            $covers = is_array(json_decode($business_card_details->cover))
                                ? json_decode($business_card_details->cover)
                                : ($business_card_details->cover
                                    ? [$business_card_details->cover]
                                    : [asset('images/default-cover.png')]);
                        @endphp
                        <div class="col-md-12">
                            <swiper-container space-between="20" class="mySwiper" autoplay-delay="2500"
                                pagination="true" autoplay-disable-on-interaction="false"
                                loop="{{ count($covers) > 1 ? 'true' : 'false' }}">
                                @foreach ($covers as $cover)
                                    <swiper-slide>
                                        <img class="radius-img img-fluid d-block mx-auto"
                                            alt="{{ $business_card_details->title }}" src="{{ url($cover) }}" />
                                    </swiper-slide>
                                @endforeach
                            </swiper-container>
                        </div>

                        <div class="row ps-lg-3">
                            <!-- Categories -->
                            <div
                                class="{{ request('category') ? $filterPanel . ' rounded px-3 col-md-3 my-3' : 'col-md-12 my-3' }}">
                                {{-- Filter --}}
                                <form class="row pt-3 {{ request('category') ? '' : 'd-none' }}" method="GET"
                                    action="{{ url()->current() }}">
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
                                            class="btn btn-{{ $storeTheme->color }} w-100">{{ __('Filter') }}</button>
                                    </div>
                                </form>

                                <h2 class="pt-4 pb-5 text-start position-relative fs-custom fw-bold {{ $heading }}">
                                    {{ __('Categories') }}
                                    <div class="position-absolute start-0 bg-{{ $storeTheme->color }} bottom-bar2"></div>
                                </h2>

                                <!-- All Categories Swiper -->
                                <swiper-container space-between="10"
                                    class="mySwiper {{ request('category') ? 'd-none' : '' }}" autoplay-delay="2500"
                                    breakpoints='{
                                        "320": { "slidesPerView": 2 },
                                        "640": { "slidesPerView": 4 }, 
                                        "1024": { "slidesPerView": 5 } 
                                    }'
                                    autoplay-disable-on-interaction="false"
                                    loop="{{ count($categories) > 5 ? 'true' : 'false' }}">

                                    @foreach ($categories as $category)
                                        <swiper-slide class="cursor-pointer">
                                            <a
                                                href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="card radius-img">
                                                    <div
                                                        class="card-body d-flex flex-column align-items-center justify-content-center gap-2 p-3">
                                                        <div
                                                            class="ratio ratio-1x1 d-flex align-items-center justify-content-center">
                                                            <img src="{{ url($category->thumbnail) }}"
                                                                alt="{{ $category->category_name }}"
                                                                class="img-fluid category-thumbnail">
                                                        </div>
                                                        <div>
                                                            <h3 class="responsive-title mt-2 mb-0 {{ $heading }}">
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
                                    <!-- All Categories Link -->
                                    <div class="pb-3">
                                        <a href="{{ url()->current() }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ asset('img/templates/all-categories.png') }}"
                                                    alt="All" class="rounded-custom"
                                                    style="width: 40px; height: 40px; object-fit: contain;">
                                                <h3 class="mb-0 {{ $ink }} fs-3">{{ __('All') }}</h3>
                                            </div>
                                        </a>
                                    </div>
                                    @foreach ($categories as $category)
                                        <div class="py-3 border-top">
                                            <a
                                                href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="text-decoration-none">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ url($category->thumbnail) }}"
                                                            class="rounded-custom"
                                                            style="width: 40px; height: 40px; object-fit: contain;"
                                                            alt="{{ $category->category_name }}" />
                                                        @php
                                                            $category_name = urlencode(
                                                                strtolower($category->category_name),
                                                            );
                                                        @endphp
                                                        <h3
                                                            class="mb-0 fs-3 {{ request('category') == $category_name ? 'text-' . $storeTheme->color : $ink }}">
                                                            {{ __($category->category_name) }}
                                                        </h3>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Products Section -->
                            <div class="my-3 {{ request('category') ? 'col-md-9 ps-lg-4' : 'col-md-12' }}">
                                <div class="row align-items-center {{ request('category') ? '' : 'd-none' }}">
                                    <!-- Search Form -->
                                    <form class="row col-md-8" method="GET" action="{{ url()->current() }}">
                                        @foreach (request()->except(['query', 'page']) as $key => $value)
                                            <input type="hidden" name="{{ $key }}"
                                                value="{{ $value }}">
                                        @endforeach

                                        <div class="col-9">
                                            <input type="text" name="query" placeholder="{{ __('Search') }}"
                                                value="{{ request('query') }}" class="form-control" />
                                        </div>

                                        <div class="col-3">
                                            <button type="submit"
                                                class="btn btn-{{ $storeTheme->color }} w-100">{{ __('Search') }}</button>
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

                                <h2 class="pt-4 pb-5 position-relative fs-custom fw-bold {{ $heading }}">
                                    {{ __('Products') }}
                                    <div class="position-absolute start-0 bg-{{ $storeTheme->color }} bottom-bar2"></div>
                                </h2>

                                @if (count($products) > 0)
                                    <div class="row">
                                        @foreach ($products as $product)
                                            @include('templates.stores.modern-store.partials.product-card', [
                                                'class' => 'col-12 ' . (request('category') ? 'col-md-4' : 'col-md-3') . ' mb-2',
                                            ])
                                        @endforeach

                                        {{-- Paginate --}}
                                        <div class="col-md-12 my-3">
                                            @if (request()->has('category'))
                                                {{ $products->appends(request()->except('page'))->links('vendor.pagination.store', ['color' => $storeTheme->color]) }}
                                            @else
                                                {{ $products->links('vendor.pagination.store', ['color' => $storeTheme->color]) }}
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="row text-center">
                                        <h3 class="fs-1 {{ $heading }}">{{ __('No Products Found!') }}</h3>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('templates.stores.modern-store.includes.footer')
        </div>

        @include('templates.stores.includes.bottom-bar', ['color' => $storeTheme->color, 'bg' => $dark ? 'dark' : 'light'])
    </div>

    @include('templates.stores.modern-store.partials.cart-order-modal')
    @include('templates.stores.modern-store.includes.popups')
    @include('templates.stores.modern-store.includes.scripts')

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
