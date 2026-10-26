<style>
    /* Scoped Ultra-Luxury Floating WhatsApp Engine */
    .wa-float-container {
        position: fixed;
        bottom: 90px;
        /* 90px mobile bottom clearance for bottom-bar */
        right: 20px;
        z-index: 1030;
        user-select: none;
    }

    @media (min-width: 992px) {
        .wa-float-container {
            bottom: 25px;
            right: 25px;
        }
    }

    .wa-float-btn {
        width: 56px;
        height: 56px;
        border-radius: 50% !important;
        background: #25d366;
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.85rem;
        box-shadow: 0 10px 25px rgba(37, 211, 102, 0.45);
        cursor: pointer;
        position: relative;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border: none;
        outline: none;
    }

    .wa-float-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 14px 30px rgba(37, 211, 102, 0.6);
    }

    .wa-notification-dot {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background: #dc2626;
        border: 2.5px solid #ffffff;
        border-radius: 50%;
        animation: waPulseDot 1.6s infinite;
    }

    /* Interactive Luxury Chat Pod */
    .wa-chat-pod {
        position: absolute;
        bottom: 72px;
        right: 0;
        width: 310px;
        background: #ffffff;
        border-radius: 24px !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        border: 1px solid #f1f5f9;
        display: none;
        overflow: hidden;
        animation: waSlideUpPod 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .wa-chat-header {
        background: #1c1917;
        color: #ffffff;
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .wa-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .wa-avatar-img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #25d366;
        flex-shrink: 0;
    }

    .wa-brand-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wa-status-text {
        color: #22c55e;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 1px;
    }

    .wa-live-beacon {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        display: inline-block;
        box-shadow: 0 0 8px #22c55e;
    }

    .wa-pod-close-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #ffffff;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
        flex-shrink: 0;
    }

    .wa-pod-close-btn:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    .wa-chat-body {
        background: #f8fafc;
        padding: 16px;
    }

    .wa-chat-bubble {
        background: #ffffff;
        border-radius: 16px 16px 16px 4px;
        padding: 12px 14px;
        font-size: 0.88rem;
        color: #334155;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        border: 1px solid #f1f5f9;
        margin-bottom: 14px;
        line-height: 1.45;
    }

    .wa-chat-time {
        font-size: 0.65rem;
        color: #94a3b8;
        text-align: right;
        margin-top: 4px;
    }

    .btn-wa-start-chat {
        width: 100%;
        height: 46px;
        border-radius: 50px !important;
        background: #25d366;
        color: #ffffff !important;
        border: none;
        font-size: 0.95rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 18px rgba(37, 211, 102, 0.35);
        transition: all 0.3s ease;
        text-decoration: none !important;
    }

    .btn-wa-start-chat:hover {
        background: #1eb856;
        box-shadow: 0 8px 24px rgba(37, 211, 102, 0.5);
        transform: translateY(-2px);
    }

    @keyframes waPulseDot {
        0% {
            transform: scale(0.9);
            opacity: 1;
        }

        50% {
            transform: scale(1.3);
            opacity: 0.6;
        }

        100% {
            transform: scale(0.9);
            opacity: 1;
        }
    }

    @keyframes waSlideUpPod {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.96);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>

<div class="wa-float-container">
    {{-- Interactive Chat Pod --}}
    <div class="wa-chat-pod" id="waChatPod">
        {{-- Pod Header --}}
        <div class="wa-chat-header">
            <div class="wa-header-info">
                @if (!empty($business_card_details->profile))
                    <img src="{{ url($business_card_details->profile) }}" class="wa-avatar-img" alt="Support"
                        onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'42\' height=\'42\'><circle cx=\'21\' cy=\'21\' r=\'21\' fill=\'%2325d366\'/></svg>'">
                @endif
                <div>
                    <h5 class="wa-brand-name">{{ $card_details->title }}</h5>
                    <div class="wa-status-text">
                        <span class="wa-live-beacon"></span>
                        <span>{{ __('Online Support') }}</span>
                    </div>
                </div>
            </div>
            <button type="button" class="wa-pod-close-btn" onclick="toggleWaChatPod()" aria-label="Close Chat">
                <i class="ti ti-x"></i>
            </button>
        </div>

        {{-- Pod Body & Message --}}
        <div class="wa-chat-body">
            <div class="wa-chat-bubble">
                <span>{{ __('Hello! Have any questions about our gifts or special orders? Chat with us directly on WhatsApp.') }}</span>
                <div class="wa-chat-time">{{ date('h:i A') }}</div>
            </div>

            <a href="https://api.whatsapp.com/send?phone={{ $enquiry_button }}&text={{ urlencode('Hello, I have a query regarding gifts on ' . $card_details->title) }}"
                target="_blank" class="btn-wa-start-chat">
                <i class="ti ti-brand-whatsapp fs-2"></i>
                <span>{{ __('Start Conversation') }}</span>
            </a>
        </div>
    </div>

    {{-- Main Floating Button --}}
    <button type="button" class="wa-float-btn" onclick="toggleWaChatPod()" aria-label="WhatsApp Support">
        <i class="ti ti-brand-whatsapp"></i>
        <div class="wa-notification-dot"></div>
    </button>
</div>

<script>
    // Toggle Interactive WhatsApp Pod
    function toggleWaChatPod() {
        "use strict";
        const pod = document.getElementById('waChatPod');
        if (pod) {
            pod.style.display = pod.style.display === 'block' ? 'none' : 'block';
        }
    }
</script>
