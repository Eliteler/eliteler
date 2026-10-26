{{-- Modern store: all categories --}}
@php
    $dark = $storeTheme->isDark();
    $heading = $dark ? 'text-white' : '';
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
                    <!-- Categories -->
                    <div class="col-md-12 my-3">
                        <h2 class="pt-4 pb-5 text-start position-relative fs-custom fw-bold {{ $heading }}">
                            {{ __('Categories') }}
                            <div class="position-absolute start-0 bg-{{ $storeTheme->color }} bottom-bar2"></div>
                        </h2>

                        <!-- All Categories -->
                        @if (count($categories) > 0)
                            <div class="col-12 my-3">
                                <div class="row g-3">
                                    @foreach ($categories as $category)
                                        <div class="cursor-pointer col-6 col-md-3">
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
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="col-12 my-3">
                                <h2 class="text-center {{ $heading }}">{{ __('No Categories Found') }}</h2>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @include('templates.stores.modern-store.includes.footer')
        </div>

        @include('templates.stores.includes.bottom-bar', ['color' => $storeTheme->color, 'bg' => $dark ? 'dark' : 'light'])
    </div>

    @include('templates.stores.modern-store.partials.cart-order-modal')
    @include('templates.stores.modern-store.includes.scripts')
</body>

</html>
