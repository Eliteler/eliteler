{{-- Header Component with Language Switcher & Cart Trigger --}}
<style>
    .fashion-header {
        background-color: #111827;
        color: #ffffff;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .fashion-brand-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none !important;
        color: #ffffff;
    }

    .fashion-avatar {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 0px;
        border: 2px solid #ffffff;
    }

    .fashion-brand-title {
        font-family: 'Oswald', sans-serif;
        font-size: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
        color: #ffffff;
        line-height: 1.2;
    }

    .fashion-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .fashion-cart-btn {
        position: relative;
        background-color: #ffffff;
        color: #111827;
        border: none;
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s ease;
        text-decoration: none !important;
    }

    .fashion-cart-btn:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    .fashion-cart-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        line-height: 1;
        border-radius: 50%;
        border: 2px solid #ffffff;
        background-color: #dc2626;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 800;
    }
</style>

<header class="fashion-header">
    <div class="container-xl d-flex align-items-center justify-content-between">
        <!-- Store Brand Identity -->
        <a href="{{ $storeUrl }}" class="fashion-brand-wrap">
            <img src="{{ url($business_card_details->profile ?? 'images/default-avatar.png') }}"
                alt="{{ $card_details->title }}" class="fashion-avatar">
            <h1 class="fashion-brand-title mb-0">{{ $card_details->title }}</h1>
        </a>

        <!-- Header Controls: Language Switcher & Cart -->
        <div class="fashion-header-actions">
            {{-- Check if language switcher is enabled --}}
            @if (
                !empty($business_card_details->is_enable_language_switcher) &&
                    $business_card_details->is_enable_language_switcher == 1)
                @include('templates.stores.fashion.includes.language-switcher')
            @endif

            <button type="button" class="fashion-cart-btn" aria-label="{{ __('Cart') }}" data-cart-open>
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                    <path d="M17 17h-11v-14h-2" />
                    <path d="M6 5l14 1l-1 7h-13" />
                </svg>
                <span class="fashion-cart-badge" data-cart-count data-cart-when-filled hidden>0</span>
            </button>
        </div>
    </div>
</header>
