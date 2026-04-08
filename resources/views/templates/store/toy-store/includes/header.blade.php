<header class="navbar navbar-expand m-0 p-0 pb-1">
    {{-- Greeting message --}}
    <h3 class="alert alert-important alert-warning bg-yellow">
        {{ $business_card_details->sub_title }}
    </h3>
    <div class="container-xl mx-auto" style="margin-top: -10px;">
        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3" style="margin-top: -4px;">
            <!-- Logo -->
            <a href="{{ url($business_card_details->card_url) }}">
                <img src="{{ url($business_card_details->profile) }}" alt="{{ $business_card_details->title }}"
                    class="navbar-brand-image logo-height" />
            </a>
        </div>
        <div class="navbar-nav flex-row order-md-last">
            {{-- Language switcher --}}
            @if ($business_card_details->is_enable_language_switcher == 1 && is_array(config('app.languages')) && count(config('app.languages')) > 1)
            @include('templates.includes.bs-language-switcher', ['rounded' => 'rounded-btn'])
            @endif

            <!-- Cart -->
            <div class="nav-item dropdown">
                <div class="nav-item">
                    <a class="position-relative cursor-pointer btn-effect" data-bs-toggle="offcanvas" href="#offcanvasEnd"
                        role="button" aria-controls="offcanvasEnd" onclick="openOffcanvas()">
                        <i class="ti ti-shopping-bag bg-{{ $bg_color }} text-dark p-2 rounded-btn"></i>
                        <span class="badge-2 bg-{{ $badge_color }} text-white fs-5 badge-notification badge-pill"
                            id="badge">0</span></a>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Custom JS --}}
<script>
    function openOffcanvas() {
        "use strict";

        // Open Offcanvas
        const offcanvasEl = document.getElementById('offcanvasEnd');
        if (offcanvasEl) {
            const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
            offcanvas.show();
        }

        // Close PWA Modal
        const pwaModalEl = document.getElementById('pwaModal');
        if (pwaModalEl) {
            pwaModalEl.classList.remove('show');
            pwaModalEl.classList.add('hide');
        }
    }
</script>
