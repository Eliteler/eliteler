{{-- Restaurant: all categories --}}
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
                    <!-- Categories -->
                    <div class="col-md-12 mb-3 mt-7 px-3 px-lg-4">
                        <div class="position-relative">
                            <img class="position-absolute float-loop"
                                src="{{ asset('img/templates/restaurant/1.png') }}"
                                style="
                                    width: 120px;
                                    height: 120px;
                                    right: -18px;
                                    top: -10px;
                                    opacity: 0.6;
                                    filter: blur(0.8px);
                                " />
                        </div>

                        <h2 class="head pt-4 pb-5 text-center position-relative fs-custom fw-bold">
                            {{ __('Categories') }}
                            <div class="position-absolute start-50 translate-middle-x bg-restaurant-red bottom-bar3">
                            </div>
                        </h2>

                        <!-- All Categories -->
                        @if (count($categories) > 0)
                            <div class="col-12 my-3">
                                <div class="row g-3">
                                    <!-- Foreach -->
                                    @foreach ($categories as $category)
                                        <div class="cursor-pointer col-6 col-md-3">
                                            <a
                                                href="{{ $storeUrl . '?category=' . urlencode(strtolower($category->category_name)) }}">
                                                <div class="card rounded-restaurant">
                                                    <div
                                                        class="card-body d-flex flex-column align-items-center justify-content-center gap-2 p-3">
                                                        <!-- Thumbnail -->
                                                        <div
                                                            class="ratio ratio-1x1 d-flex align-items-center justify-content-center">
                                                            <img src="{{ url($category->thumbnail) }}"
                                                                class="img-fluid category-thumbnail"
                                                                alt="{{ $category->category_name }}" />
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
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="col-12 my-3">
                                <h2 class="text-center">{{ __('No Categories Found') }}</h2>
                            </div>
                        @endif
                    </div>
                    @include('templates.stores.restaurant.includes.footer')
                </div>
            </div>
        </div>

        <!-- Bottom Navbar -->
        @include('templates.stores.includes.bottom-bar', ['color' => 'restaurant-red', 'bg' => 'light'])
        <!-- End Bottom Navbar -->
    </div>

    @include('templates.stores.restaurant.partials.cart-order-modal')
    @include('templates.stores.restaurant.includes.scripts')
</body>

</html>
