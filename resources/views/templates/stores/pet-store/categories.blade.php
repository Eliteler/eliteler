{{-- Pet store: all categories --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.pet-store.includes.head')
    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-warning" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #FFEDD5;">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.pet-store.includes.header', [
                        'bg_color' => 'warning',
                        'badge_color' => 'primary',
                    ])
                    <!-- Categories -->
                    <div class="page-wrapper mt-6 p-3">
                        {{-- Content --}}
                        <div class="page-body">
                            <div class="col-md-12 my-3 px-lg-2">
                                <div class="position-relative">
                                    <img class="position-absolute floater-img"
                                        src="{{ asset('img/templates/pet-store/3.png') }}"
                                        style="
                                            width: 80px;
                                            height: 80px;
                                            right: -15px;
                                            top: 0px;   
                                            opacity: 0.5;
                                            filter: blur(1px);
                                        " />
                                </div>

                                <h2 class="head pt-4 pb-5 text-center position-relative fs-custom fw-bold">
                                    {{ __('Categories') }}
                                    <div class="position-absolute start-50 translate-middle-x bg-warning bottom-bar2">
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
                                                        <div class="card rounded-pet-store border-warning">
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
                        </div>
                    </div>

                    @include('templates.stores.pet-store.includes.footer')
                </div>
            </div>
        </div>

        <!-- Bottom Navbar -->
        @include('templates.stores.includes.bottom-bar', ['color' => 'warning', 'bg' => 'light'])
        <!-- End Bottom Navbar -->
    </div>

    @include('templates.stores.pet-store.partials.cart-order-modal')
    @include('templates.stores.pet-store.includes.scripts')
</body>

</html>
