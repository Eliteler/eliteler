{{-- Toy store: all categories --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    @include('templates.stores.toy-store.includes.head')
    @include('templates.stores.shared.custom-code')
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="page page-center preloader-wrapper">
        <div class="container container-slim py-4">
            <div class="text-center">
                <div class="spinner-border text-red" role="status"></div>
            </div>
        </div>
    </div>

    {{-- Page --}}
    <div id="wrapper" class="page m-0 min-h-screen" style="background-color: #F876761A;">
        <div class="page-wrapper m-0 p-0">
            <div class="page-body m-0 p-0">
                <div class="container-xl card rounded-0 p-0 overflow-hidden" style="min-height: 100vh;">
                    <!-- Navbar -->
                    @include('templates.stores.toy-store.includes.header', [
                        'bg_color' => 'yellow',
                        'badge_color' => 'primary',
                    ])
                    <!-- Categories -->
                    <div class="page-wrapper px-3">
                        {{-- Content --}}
                        <div class="page-body">
                            <div class="col-md-12 mb-3 px-lg-2">
                                <div class="position-relative">
                                    <img class="position-absolute floater-img"
                                        src="{{ asset('img/templates/toy-store/2.png') }}"
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
                                    <div class="position-absolute start-50 translate-middle-x bg-red bottom-bar2">
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
                                                        <div class="card rounded-toy-store border-red">
                                                            <div
                                                                class="card-body d-flex flex-column align-items-center justify-content-center gap-2 p-3">
                                                                <!-- Thumbnail -->
                                                                <div
                                                                    class="d-flex align-items-center justify-content-center">
                                                                    <img src="{{ url($category->thumbnail) }}"
                                                                        class="rounded-toy-store category-thumbnail"
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

                    @include('templates.stores.toy-store.includes.footer')
                </div>
            </div>
        </div>

        <!-- Bottom Navbar -->
        @include('templates.stores.includes.bottom-bar', ['color' => 'red', 'bg' => 'light'])
        <!-- End Bottom Navbar -->
    </div>

    @include('templates.stores.toy-store.partials.cart-order-modal')
    @include('templates.stores.toy-store.includes.scripts')
</body>

</html>
