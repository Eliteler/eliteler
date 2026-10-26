{{-- Luxury Furniture Product Share Component --}}
@php
    $currentProductUrl = $productUrl ?? url()->current();
    $currentProductName = $productName ?? ($product_details->product_name ?? $card_details->title);
    $rawShareMessage = "Hey! Check out this product: {$currentProductName} 👉 {$currentProductUrl}";
    $encodedShareMessage = rawurlencode($rawShareMessage);
    $urlEncodedLink = urlencode($currentProductUrl);
@endphp

<style>
    /* 1. Luxury Product Share Card Container */
    .furniture-share-card {
        background-color: #ffffff;
        border: 1px solid #ede8df;
        border-radius: 18px !important;
        padding: 20px 22px;
        margin-top: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    .furniture-share-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        border-bottom: 1px solid #f1ede4;
        padding-bottom: 12px;
    }

    .furniture-share-title {
        font-family: 'DM Serif Display', serif;
        font-size: 18px;
        letter-spacing: 0.5px;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .furniture-share-title i {
        color: #cca47a;
        font-size: 20px;
    }

    .furniture-share-subtitle {
        font-size: 13px;
        color: #94a3b8;
        font-weight: 500;
    }

    /* 2. Symmetrical 50% Circular Social Share Grid */
    .furniture-share-grid {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .furniture-share-circle-btn {
        width: 44px;
        height: 44px;
        border-radius: 50% !important;
        background-color: #faf8f5;
        border: 1.5px solid #ede8df;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        text-decoration: none !important;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .furniture-share-circle-btn svg,
    .furniture-share-circle-btn i {
        width: 20px;
        height: 20px;
        font-size: 20px;
        transition: transform 0.2s ease;
    }

    /* Brand Accent Hover Effects */
    .furniture-share-circle-btn:hover {
        transform: translateY(-3px) scale(1.08);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
    }

    .furniture-share-circle-btn.btn-wa:hover {
        background-color: #25d366;
        border-color: #25d366;
        color: #ffffff;
    }

    .furniture-share-circle-btn.btn-tg:hover {
        background-color: #229ed9;
        border-color: #229ed9;
        color: #ffffff;
    }

    .furniture-share-circle-btn.btn-fb:hover {
        background-color: #1877f2;
        border-color: #1877f2;
        color: #ffffff;
    }

    .furniture-share-circle-btn.btn-x:hover {
        background-color: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    .furniture-share-circle-btn.btn-mail:hover {
        background-color: #ea4335;
        border-color: #ea4335;
        color: #ffffff;
    }

    .furniture-share-circle-btn.btn-copy:hover {
        background-color: #1f4b48;
        border-color: #1f4b48;
        color: #ffffff;
    }

    .furniture-share-circle-btn.btn-native:hover {
        background-color: #cca47a;
        border-color: #cca47a;
        color: #ffffff;
    }

    @media (max-width: 576px) {
        .furniture-share-circle-btn {
            width: 40px;
            height: 40px;
        }

        .furniture-share-circle-btn svg,
        .furniture-share-circle-btn i {
            width: 18px;
            height: 18px;
            font-size: 18px;
        }
    }
</style>

<div class="furniture-share-card">
    <div class="furniture-share-header">
        <h4 class="furniture-share-title">
            <i class="ti ti-share"></i>
            <span>{{ __('Share This Product') }}</span>
        </h4>
        <span class="furniture-share-subtitle">{{ __('Spread the collection') }}</span>
    </div>

    <div class="furniture-share-grid">
        {{-- WhatsApp --}}
        <a href="https://api.whatsapp.com/send/?text={{ $encodedShareMessage }}" target="_blank"
            class="furniture-share-circle-btn btn-wa" title="WhatsApp" aria-label="Share on WhatsApp">
            <i class="ti ti-brand-whatsapp"></i>
        </a>

        {{-- Telegram --}}
        <a href="https://t.me/share/url?url={{ $urlEncodedLink }}&text={{ $encodedShareMessage }}" target="_blank"
            class="furniture-share-circle-btn btn-tg" title="Telegram" aria-label="Share on Telegram">
            <i class="ti ti-brand-telegram"></i>
        </a>

        {{-- Facebook --}}
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $urlEncodedLink }}" target="_blank"
            class="furniture-share-circle-btn btn-fb" title="Facebook" aria-label="Share on Facebook">
            <i class="ti ti-brand-facebook"></i>
        </a>

        {{-- X (Twitter) --}}
        <a href="https://twitter.com/intent/tweet?text={{ $encodedShareMessage }}" target="_blank"
            class="furniture-share-circle-btn btn-x" title="X" aria-label="Share on X">
            <i class="ti ti-brand-x"></i>
        </a>

        {{-- Email --}}
        <a href="mailto:?subject={{ rawurlencode($currentProductName) }}&body={{ $encodedShareMessage }}"
            class="furniture-share-circle-btn btn-mail" title="Email" aria-label="Share via Email">
            <i class="ti ti-mail"></i>
        </a>

        {{-- Instant Copy Link --}}
        <button type="button" class="furniture-share-circle-btn btn-copy" data-url="{{ $currentProductUrl }}"
            onclick="copyProductDirectUrl(this)" title="{{ __('Copy Link') }}" aria-label="Copy Product Link">
            <i class="ti ti-copy"></i>
        </button>

        {{-- Native Web Share API (Mobile Trigger) --}}
        <button type="button" class="furniture-share-circle-btn btn-native" data-name="{{ $currentProductName }}"
            data-url="{{ $currentProductUrl }}" onclick="triggerProductNativeShare(this)"
            title="{{ __('More Options') }}" aria-label="More Share Options">
            <i class="ti ti-share"></i>
        </button>
    </div>
</div>

<script>
    // Pure Clipboard Copy Function with Direct Store Toast
    function copyProductDirectUrl(el) {
        const link = el.getAttribute('data-url');
        if (!link) return;

        navigator.clipboard.writeText(link).then(() => {
            if (typeof window.showStoreToast === 'function') {
                window.showStoreToast("{{ __('Link copied to clipboard!') }}", "success");
            }
        }).catch(() => {
            if (typeof window.showStoreToast === 'function') {
                window.showStoreToast("{{ __('Copy failed. Please try again.') }}", "error");
            }
        });
    }

    // Native Web Share API Trigger with Fallback
    async function triggerProductNativeShare(el) {
        const title = el.getAttribute('data-name') || "{{ $card_details->title }}";
        const url = el.getAttribute('data-url') || window.location.href;

        if (navigator.share) {
            try {
                await navigator.share({
                    title: title,
                    text: `Check out ${title} on {{ $card_details->title }}! ✨`,
                    url: url,
                });
            } catch (err) {
                // User dismissed share dialog
            }
        } else {
            copyProductDirectUrl(el);
        }
    }
</script>
