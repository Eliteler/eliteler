{{--
    Restaurant header: logo, language switcher and the cart button (shared storefront script).
    Params: $bg_color (cart button colour), $badge_color
--}}
<header class="navbar navbar-expand d-print-none p-2">
    <div class="container-xl mx-auto">
        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <!-- Logo -->
            <a href="{{ $storeUrl }}">
                <img src="{{ url($business_card_details->profile) }}" alt="{{ $business_card_details->title }}"
                    class="navbar-brand-image logo-height" />
            </a>
        </div>
        <div class="navbar-nav flex-row order-md-last">
            {{-- Language switcher --}}
            @if (
                $business_card_details->is_enable_language_switcher == 1 &&
                    is_array(config('app.languages')) &&
                    count(config('app.languages')) > 1)
                @include('templates.stores.includes.bs-language-switcher', ['rounded' => 'rounded-restaurant'])
            @endif

            <!-- Cart -->
            <div class="nav-item dropdown">
                <div class="nav-item">
                    <a class="position-relative cursor-pointer btn-effect" href="#offcanvasEnd" role="button"
                        aria-controls="offcanvasEnd" aria-label="{{ __('Cart') }}" data-cart-open>
                        <i class="ti ti-shopping-bag bg-{{ $bg_color }} text-white p-2 rounded-restaurant"></i>
                        <span class="badge-2 bg-{{ $badge_color }} text-white fs-5 badge-notification badge-pill"
                            data-cart-count data-cart-when-filled hidden>0</span></a>
                </div>
            </div>
        </div>
    </div>
</header>
