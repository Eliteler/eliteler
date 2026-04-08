@php
    $shareText = "Hey 👋 have a look at this product 👀 {$productName} 👉 {$productUrl}";
    $shareMessage = urlencode($shareText);
 
    // Raw message
    $rawShareMessage = "Hey! Check out this product: {$productName}. {$productUrl}";
    $encodedShareMessage = rawurlencode($rawShareMessage);
@endphp

<div class="product-share-card mt-3">
    <div class="share-title">{{ __('Share Product') }}</div>

    <div class="share-icons">
        {{-- WhatsApp --}}
        <a href="https://wa.me/?text={{ $encodedShareMessage }}" target="_blank" class="share-icon whatsapp">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"
                class="icon icon-tabler icons-tabler-filled icon-tabler-brand-whatsapp">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M18.497 4.409a10 10 0 0 1 -10.36 16.828l-.223 -.098l-4.759 .849l-.11 .011a1 1 0 0 1 -.11 0l-.102 -.013l-.108 -.024l-.105 -.037l-.099 -.047l-.093 -.058l-.014 -.011l-.012 -.007l-.086 -.073l-.077 -.08l-.067 -.088l-.056 -.094l-.034 -.07l-.04 -.108l-.028 -.128l-.012 -.102a1 1 0 0 1 0 -.125l.012 -.1l.024 -.11l.045 -.122l1.433 -3.304l-.009 -.014a10 10 0 0 1 1.549 -12.454l.215 -.203a10 10 0 0 1 13.226 -.217m-8.997 3.09a1.5 1.5 0 0 0 -1.5 1.5v1a6 6 0 0 0 6 6h1a1.5 1.5 0 0 0 0 -3h-1l-.144 .007a1.5 1.5 0 0 0 -1.128 .697l-.042 .074l-.022 -.007a4.01 4.01 0 0 1 -2.435 -2.435l-.008 -.023l.075 -.041a1.5 1.5 0 0 0 .704 -1.272v-1a1.5 1.5 0 0 0 -1.5 -1.5" />
            </svg>
        </a>

        {{-- Telegram --}}
        <a href="https://t.me/share/url?url={{ $productUrl }}&text={{ $shareMessage }}" target="_blank"
            class="share-icon telegram">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-telegram">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M15 10l-4 4l6 6l4 -16l-18 7l4 2l2 6l3 -4" />
            </svg>
        </a>

        {{-- Facebook --}}
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $productUrl }}" target="_blank"
            class="share-icon facebook">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brand-facebook">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M18 2a1 1 0 0 1 .993 .883l.007 .117v4a1 1 0 0 1 -.883 .993l-.117 .007h-3v1h3a1 1 0 0 1 .991 1.131l-.02 .112l-1 4a1 1 0 0 1 -.858 .75l-.113 .007h-2v6a1 1 0 0 1 -.883 .993l-.117 .007h-4a1 1 0 0 1 -.993 -.883l-.007 -.117v-6h-2a1 1 0 0 1 -.993 -.883l-.007 -.117v-4a1 1 0 0 1 .883 -.993l.117 -.007h2v-1a6 6 0 0 1 5.775 -5.996l.225 -.004h3z" />
            </svg>
        </a>

        {{-- Instagram (Copy link fallback) --}}
        <a href="javascript:void(0)" class="share-icon instagram" data-share="{{ $shareMessage }}"
            onclick="copyProductLink(this)" title="{{ __('Copy link to share on Instagram') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 8a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4l0 -8" />
                <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                <path d="M16.5 7.5v.01" />
            </svg>
        </a>


        {{-- X --}}
        <a href="https://twitter.com/intent/tweet?text={{ $shareMessage }}" target="_blank" class="share-icon x">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-brand-x">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M8.267 3a1 1 0 0 1 .73 .317l.076 .092l4.274 5.828l5.946 -5.944a1 1 0 0 1 1.497 1.32l-.083 .094l-6.163 6.162l6.262 8.54a1 1 0 0 1 -.697 1.585l-.109 .006h-4.267a1 1 0 0 1 -.73 -.317l-.076 -.092l-4.276 -5.829l-5.944 5.945a1 1 0 0 1 -1.497 -1.32l.083 -.094l6.161 -6.163l-6.26 -8.539a1 1 0 0 1 .697 -1.585l.109 -.006h4.267z" />
            </svg>
        </a>

        {{-- Email --}}
        <a href="mailto:?subject={{ $productName }}&body={{ $shareMessage }}" target="_blank" class="share-icon email">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-mail">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M22 7.535v9.465a3 3 0 0 1 -2.824 2.995l-.176 .005h-14a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-9.465l9.445 6.297l.116 .066a1 1 0 0 0 .878 0l.116 -.066l9.445 -6.297z" />
                <path
                    d="M19 4c1.08 0 2.027 .57 2.555 1.427l-9.555 6.37l-9.555 -6.37a2.999 2.999 0 0 1 2.354 -1.42l.201 -.007h14z" />
            </svg>
        </a>

        {{-- Copy link --}}
        <a href="javascript:void(0)" class="share-icon copy" data-share="{{ $shareMessage }}"
            onclick="copyProductLink(this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-clipboard-check">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path
                    d="M17.997 4.17a3 3 0 0 1 2.003 2.83v12a3 3 0 0 1 -3 3h-10a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 2.003 -2.83a4 4 0 0 0 3.997 3.83h4a4 4 0 0 0 3.98 -3.597zm-3.704 7.123l-3.293 3.292l-1.293 -1.292a1 1 0 1 0 -1.414 1.414l2 2a1 1 0 0 0 1.414 0l4 -4a1 1 0 0 0 -1.414 -1.414m-.293 -9.293a2 2 0 1 1 0 4h-4a2 2 0 1 1 0 -4z" />
            </svg>
        </a>

        {{-- More Share --}}
        <a href="javascript:void(0)" class="share-icon more" data-share="{{ $shareMessage }}"
            onclick="shareProduct('{{ $productName }}', '{{ $productUrl }}')">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-share">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M3 12a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M15 6a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M15 18a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M8.7 10.7l6.6 -3.4" />
                <path d="M8.7 13.3l6.6 3.4" />
            </svg>
        </a>
    </div>
</div>

<style>
    .product-share-card {
        border-radius: 14px;
        padding: 16px 0;
        max-width: 100%;
    }

    .share-title {
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #374151;
    }

    .share-icons {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .share-icon {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, color .15s ease;
        border: 1px solid #e5e7eb;
    }

    .share-icon svg {
        width: 22px;
        height: 22px;
    }

    /* Brand colors */
    .share-icon.whatsapp {
        color: #25d366;
    }

    .share-icon.telegram {
        color: #229ed9;
    }

    .share-icon.facebook {
        color: #1877f2;
    }

    .share-icon.instagram {
        color: #e1306c;
    }

    .share-icon.x {
        color: #111827;
    }

    .share-icon.email {
        color: #4b5563;
    }

    .share-icon.copy {
        color: #2563eb;
    }

    .share-icon.more {
        color: #7c3aed;
    }

    /* Hover effects */
    .share-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
    }

    /* Soft brand hover backgrounds */
    .share-icon.whatsapp:hover {
        background: #eafaf1;
    }

    .share-icon.telegram:hover {
        background: #eaf5ff;
    }

    .share-icon.facebook:hover {
        background: #eef2ff;
    }

    .share-icon.instagram:hover {
        background: #fce7f3;
    }

    .share-icon.x:hover {
        background: #f3f4f6;
    }

    .share-icon.email:hover {
        background: #f1f5f9;
    }

    .share-icon.copy:hover {
        background: #eff6ff;
    }

    /* Mobile tweak */
    @media (max-width: 576px) {
        .share-icon {
            width: 42px;
            height: 42px;
        }

        .share-icon svg {
            width: 25px;
            height: 25px;
        }
    }
</style>

<script>
    "use strict";

    // Copy link to clipboard
    function copyProductLink(el) {
        const text = el.dataset.share;
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            el.classList.add('copied');
            alert('Link copied to clipboard');
        }).catch(() => {
            alert('Copy failed. Please try again.');
        });
    }

    // Share function
    async function shareProduct(productName, productUrl) {
        const shareData = {
            title: productName,
            text: `Check out this ${productName}! 🚀`,
            url: productUrl,
        };

        // Check if the browser supports native sharing (Mobile Chrome/Safari)
        if (navigator.share) {
            try {
                await navigator.share(shareData);
            } catch (err) {
                console.log("Share cancelled or failed");
            }
        } else {
            // Fallback to your WhatsApp link for Desktop/Older browsers
            const text = encodeURIComponent(`${shareData.text} ${shareData.url}`);
            window.open(`https://wa.me/?text=${text}`, "_blank");
        }
    }
</script>
