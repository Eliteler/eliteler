{{-- Modern Luxury Product Share Component --}}
@php
    $currentProductUrl = $productUrl ?? url()->current();
    $rawShareMessage = "Hey! Check out this product: {$productName} 👉 {$currentProductUrl}";
    $encodedShareMessage = rawurlencode($rawShareMessage);
    $urlEncodedLink = urlencode($currentProductUrl);
@endphp

<style>
    /* Share Container */
    .fashion-share-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0px;
        padding: 20px;
        margin-top: 24px;
    }

    .fashion-share-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 10px;
    }

    .fashion-share-title {
        font-family: 'Oswald', sans-serif;
        font-size: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Social Icons Flex Grid */
    .fashion-share-grid {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .fashion-share-btn {
        width: 44px;
        height: 44px;
        border-radius: 0px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        text-decoration: none !important;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fashion-share-btn svg {
        width: 20px;
        height: 20px;
        transition: transform 0.2s ease;
    }

    /* Brand Accent Hovers */
    .fashion-share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
    }

    .fashion-share-btn:hover svg {
        transform: scale(1.1);
    }

    .fashion-share-btn.btn-wa:hover {
        background-color: #25d366;
        border-color: #25d366;
        color: #ffffff;
    }

    .fashion-share-btn.btn-tg:hover {
        background-color: #229ed9;
        border-color: #229ed9;
        color: #ffffff;
    }

    .fashion-share-btn.btn-fb:hover {
        background-color: #1877f2;
        border-color: #1877f2;
        color: #ffffff;
    }

    .fashion-share-btn.btn-x:hover {
        background-color: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    .fashion-share-btn.btn-mail:hover {
        background-color: #ea4335;
        border-color: #ea4335;
        color: #ffffff;
    }

    .fashion-share-btn.btn-copy:hover {
        background-color: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    .fashion-share-btn.btn-native:hover {
        background-color: #d97706;
        border-color: #d97706;
        color: #ffffff;
    }

    @media (max-width: 576px) {
        .fashion-share-btn {
            width: 40px;
            height: 40px;
        }

        .fashion-share-btn svg {
            width: 18px;
            height: 18px;
        }
    }
</style>

<div class="fashion-share-card">
    <div class="fashion-share-header">
        <h4 class="fashion-share-title">
            <i class="ti ti-share fs-2 text-dark"></i>
            <span>{{ __('Share This Piece') }}</span>
        </h4>
        <small class="text-muted fs-5">{{ __('Spread the style') }}</small>
    </div>

    <div class="fashion-share-grid">
        {{-- WhatsApp --}}
        <a href="https://api.whatsapp.com/send/?text={{ $encodedShareMessage }}" target="_blank"
            class="fashion-share-btn btn-wa" title="WhatsApp" aria-label="Share on WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                <path
                    d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
            </svg>
        </a>

        {{-- Telegram --}}
        <a href="https://t.me/share/url?url={{ $urlEncodedLink }}&text={{ $encodedShareMessage }}" target="_blank"
            class="fashion-share-btn btn-tg" title="Telegram" aria-label="Share on Telegram">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" />
            </svg>
        </a>

        {{-- Facebook --}}
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $urlEncodedLink }}" target="_blank"
            class="fashion-share-btn btn-fb" title="Facebook" aria-label="Share on Facebook">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
            </svg>
        </a>

        {{-- X (Twitter) --}}
        <a href="https://twitter.com/intent/tweet?text={{ $encodedShareMessage }}" target="_blank"
            class="fashion-share-btn btn-x" title="X" aria-label="Share on X">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
            </svg>
        </a>

        {{-- Email --}}
        <a href="mailto:?subject={{ rawurlencode($productName) }}&body={{ $encodedShareMessage }}"
            class="fashion-share-btn btn-mail" title="Email" aria-label="Share via Email">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                <path d="M3 7l9 6l9 -6" />
            </svg>
        </a>

        {{-- Instant Copy Link --}}
        <button type="button" class="fashion-share-btn btn-copy" data-url="{{ $currentProductUrl }}"
            onclick="copyProductUrl(this)" title="{{ __('Copy Link') }}" aria-label="Copy Product Link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" />
            </svg>
        </button>

        {{-- Native Web Share API (Mobile Trigger) --}}
        <button type="button" class="fashion-share-btn btn-native" data-name="{{ $productName }}"
            data-url="{{ $currentProductUrl }}" onclick="triggerNativeShare(this)" title="{{ __('More Options') }}"
            aria-label="More Share Options">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M8.7 10.7l6.6 -3.4" />
                <path d="M8.7 13.3l6.6 3.4" />
            </svg>
        </button>
    </div>
</div>

<script>
    // Pure Clipboard Copy Function with Direct Toast
    function copyProductUrl(el) {
        const link = el.getAttribute('data-url');
        if (!link) return;

        navigator.clipboard.writeText(link).then(() => {
            if (window.showStoreToast) window.showStoreToast("{{ __('Link copied to clipboard') }}", 'success');
        }).catch(() => {
            if (window.showStoreToast) window.showStoreToast("{{ __('Copy failed. Please try again.') }}", 'error');
        });
    }

    // Native Web Share API Trigger with WhatsApp Fallback
    async function triggerNativeShare(el) {
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
                console.log("Share dismissed");
            }
        } else {
            // Desktop fallback
            copyProductUrl(el);
        }
    }
</script>
