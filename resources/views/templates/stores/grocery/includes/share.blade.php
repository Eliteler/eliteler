{{-- Luxury Product Share Component - Full Width Left Aligned --}}
@php
    // Resolve Product Name & Current URL
    $shareTitle = $productName ?? ($product_details->product_name ?? ($card_details->title ?? ''));
    $shareUrl = $productUrl ?? url()->current();
    $brandTitle = $card_details->title ?? config('app.name');

    // URL-encoded strings for social sharing
    $encodedTitle = urlencode($shareTitle);
    $encodedUrl = urlencode($shareUrl);
    $waShareText = urlencode("🛒 Check out {$shareTitle} on {$brandTitle}:\n{$shareUrl}");

    // Dynamic Social Share Endpoints
    $waShareLink = "https://api.whatsapp.com/send?text={$waShareText}";
    $fbShareLink = "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
    $xShareLink =
        'https://twitter.com/intent/tweet?text=' .
        urlencode("Check out {$shareTitle} on {$brandTitle}") .
        "&url={$encodedUrl}";
    $tgShareLink = "https://t.me/share/url?url={$encodedUrl}&text=" . urlencode("{$shareTitle} - {$brandTitle}");
@endphp

<style>
    /* Full-Width Left-Aligned Luxury Share Component */
    .luxury-share-card {
        background: #ffffff;
        border: 1px solid #c8e6c9;
        border-radius: 24px !important;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(46, 125, 50, 0.04);
        text-align: start;
        /* Left aligned */
        position: relative;
        margin-top: 1.5rem;
        width: 100%;
        /* Full Width */
        box-sizing: border-box;
    }

    .share-header-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1b5e20;
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        /* Left aligned */
        gap: 8px;
        text-align: start;
    }

    .share-subtitle {
        font-size: 0.88rem;
        color: #6b7280;
        margin-bottom: 1.25rem;
        text-align: start;
        /* Left aligned */
        line-height: 1.4;
    }

    /* Left-Aligned Symmetrical Social Icons */
    .share-icon-grid {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        /* Left aligned */
        gap: 12px;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        width: 100%;
    }

    .share-icon-btn {
        width: 44px;
        height: 44px;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        font-size: 1.25rem;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border: none;
        cursor: pointer;
        text-decoration: none !important;
        flex-shrink: 0;
    }

    .share-icon-btn:hover {
        transform: translateY(-3px) scale(1.06);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
    }

    /* Semantic Social Brand Colors */
    .share-btn-wa {
        background: #25d366;
    }

    .share-btn-wa:hover {
        background: #1eb856;
    }

    .share-btn-fb {
        background: #1877f2;
    }

    .share-btn-fb:hover {
        background: #1464cc;
    }

    .share-btn-x {
        background: #000000;
    }

    .share-btn-x:hover {
        background: #262626;
    }

    .share-btn-tg {
        background: #229ed9;
    }

    .share-btn-tg:hover {
        background: #1b81b2;
    }

    .share-btn-native {
        background: #2e7d32;
    }

    .share-btn-native:hover {
        background: #1b5e20;
    }

    /* 100% Full-Width Pill Copy Link Box */
    .share-copy-box {
        background: #f8fafc;
        border: 1.5px solid #c8e6c9;
        border-radius: 50px !important;
        padding: 4px 6px 4px 18px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        width: 100%;
        /* Full Width */
        box-sizing: border-box;
    }

    [dir="rtl"] .share-copy-box {
        padding: 4px 18px 4px 6px;
    }

    .share-copy-box:focus-within {
        border-color: #2e7d32;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
    }

    .share-copy-input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        text-align: start;
    }

    .btn-share-copy {
        border-radius: 50px !important;
        background: #1f2937;
        color: #ffffff !important;
        border: none;
        padding: 9px 22px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-share-copy:hover {
        background: #2e7d32;
        box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
    }
</style>

<div class="luxury-share-card">
    {{-- Left-Aligned Title --}}
    <h4 class="share-header-title">
        <i class="ti ti-share text-success"></i>
        <span>{{ __('Share this Product with Friends') }}</span>
    </h4>
    <p class="share-subtitle">{{ __('Share fresh finds with your family and loved ones.') }}</p>

    {{-- Left-Aligned Social Action Buttons --}}
    <div class="share-icon-grid">
        <a href="{{ $waShareLink }}" target="_blank" class="share-icon-btn share-btn-wa" title="Share via WhatsApp"
            aria-label="WhatsApp">
            <i class="ti ti-brand-whatsapp"></i>
        </a>
        <a href="{{ $fbShareLink }}" target="_blank" class="share-icon-btn share-btn-fb" title="Share on Facebook"
            aria-label="Facebook">
            <i class="ti ti-brand-facebook"></i>
        </a>
        <a href="{{ $xShareLink }}" target="_blank" class="share-icon-btn share-btn-x" title="Share on X"
            aria-label="X">
            <i class="ti ti-brand-x"></i>
        </a>
        <a href="{{ $tgShareLink }}" target="_blank" class="share-icon-btn share-btn-tg" title="Share via Telegram"
            aria-label="Telegram">
            <i class="ti ti-brand-telegram"></i>
        </a>
        <button type="button" class="share-icon-btn share-btn-native" onclick="triggerNativeShare()"
            title="More Options" aria-label="More Options">
            <i class="ti ti-dots"></i>
        </button>
    </div>

    {{-- 100% Full-Width Copy Link Box --}}
    <div class="share-copy-box">
        <input type="text" id="shareCopyUrl" class="share-copy-input" value="{{ $shareUrl }}" readonly>
        <button type="button" id="btnShareCopy" class="btn-share-copy" onclick="copyShareLink()">
            <i class="ti ti-copy" id="shareCopyIcon"></i>
            <span id="shareCopyText">{{ __('Copy') }}</span>
        </button>
    </div>
</div>

<script>
    // Copy Link with Instant Visual Feedback
    function copyShareLink() {
        "use strict";
        const input = document.getElementById('shareCopyUrl');
        const btnText = document.getElementById('shareCopyText');
        const btnIcon = document.getElementById('shareCopyIcon');

        if (!input) return;

        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            if (window.showStoreToast) {
                window.showStoreToast("{{ __('Product link copied to clipboard!') }}", 'success');
            }

            if (btnText && btnIcon) {
                btnText.innerText = "{{ __('Copied!') }}";
                btnIcon.className = "ti ti-check";
                setTimeout(() => {
                    btnText.innerText = "{{ __('Copy') }}";
                    btnIcon.className = "ti ti-copy";
                }, 2000);
            }
        });
    }

    // Native Web Share API Trigger
    function triggerNativeShare() {
        "use strict";
        if (navigator.share) {
            navigator.share({
                title: @json($shareTitle),
                text: "🛒 " + @json($shareTitle) + " - " + @json($brandTitle),
                url: @json($shareUrl)
            }).catch((err) => {
                console.log('Share canceled:', err);
            });
        } else {
            copyShareLink();
        }
    }
</script>
