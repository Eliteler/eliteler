@php
    // Resolve Product Name & Current URL (Method 3)
    $shareTitle = $productName ?? ($product_details->product_name ?? ($card_details->title ?? ''));
    $shareUrl = $productUrl ?? url()->current();
    $brandTitle = $card_details->title ?? config('app.name');

    // URL-encoded strings for social sharing
    $encodedTitle = urlencode($shareTitle);
    $encodedUrl = urlencode($shareUrl);
    $waShareText = urlencode("🎁 Check out {$shareTitle} on {$brandTitle}:\n{$shareUrl}");

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
    /* Scoped Ultra-Luxury Share Component */
    .luxury-share-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 24px !important;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        text-align: center;
        position: relative;
        margin-top: 1.5rem;
    }

    .share-header-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1c1917;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .share-subtitle {
        font-size: 0.85rem;
        color: #78716c;
        margin-bottom: 1.25rem;
    }

    .share-icon-grid {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
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
    }

    .share-icon-btn:hover {
        transform: translateY(-3px) scale(1.06);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.16);
    }

    /* Semantic Brand Social Colors */
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
        background: #dc2626;
    }

    .share-btn-native:hover {
        background: #b91c1c;
    }

    /* Pill Link Copy Container */
    .share-copy-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 50px !important;
        padding: 4px 6px 4px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .share-copy-box:focus-within {
        border-color: #dc2626;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .share-copy-input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
    }

    .btn-share-copy {
        border-radius: 50px !important;
        background: #1c1917;
        color: #ffffff !important;
        border: none;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-share-copy:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }
</style>

<div class="luxury-share-card">
    <h4 class="share-header-title">
        <i class="ti ti-share text-danger"></i>
        <span>{{ __('Share this Gift with Friends') }}</span>
    </h4>
    <p class="share-subtitle">{{ __('Spread joy by sharing with your family and loved ones.') }}</p>

    {{-- Symmetrical Circular Social Action Buttons --}}
    <div class="share-icon-grid">
        {{-- WhatsApp Direct Share --}}
        <a href="{{ $waShareLink }}" target="_blank" class="share-icon-btn share-btn-wa" title="Share via WhatsApp">
            <i class="ti ti-brand-whatsapp"></i>
        </a>

        {{-- Facebook Direct Share --}}
        <a href="{{ $fbShareLink }}" target="_blank" class="share-icon-btn share-btn-fb" title="Share on Facebook">
            <i class="ti ti-brand-facebook"></i>
        </a>

        {{-- X (Twitter) Direct Share --}}
        <a href="{{ $xShareLink }}" target="_blank" class="share-icon-btn share-btn-x" title="Share on X (Twitter)">
            <i class="ti ti-brand-x"></i>
        </a>

        {{-- Telegram Direct Share --}}
        <a href="{{ $tgShareLink }}" target="_blank" class="share-icon-btn share-btn-tg" title="Share via Telegram">
            <i class="ti ti-brand-telegram"></i>
        </a>

        {{-- Native Web Share Button for Mobile Devices --}}
        <button type="button" class="share-icon-btn share-btn-native" onclick="triggerNativeShare()"
            title="More Options">
            <i class="ti ti-dots"></i>
        </button>
    </div>

    {{-- Pill Copy Link Box --}}
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
                text: "🎁 " + @json($shareTitle) + " - " + @json($brandTitle),
                url: @json($shareUrl)
            }).catch((err) => {
                console.log('Share canceled:', err);
            });
        } else {
            copyShareLink();
        }
    }
</script>
