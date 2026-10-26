{{-- Ultra-Luxury WhatsApp Floating Engine with Modern Interactive Chat Pod --}}
@if (!empty($enquiry_button))
    <div class="store-wa-float-wrapper">
        <!-- Modern Interactive Slide-Up Chat Pod -->
        <div class="store-wa-chat-pod" id="storeWaChatPod">
            <!-- Luxury Chat Header -->
            <div class="store-wa-pod-header">
                <div class="store-wa-pod-brand">
                    <div class="store-wa-avatar-box">
                        <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : '' }}"
                            alt="{{ $card_details->title }}" class="store-wa-pod-avatar"
                            onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2244%22%20height%3D%2244%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%232e7d32%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-family%3D%22sans-serif%22%20font-size%3D%2216%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3E{{ substr($card_details->title, 0, 1) }}%3C%2Ftext%3E%3C%2Fsvg%3E';">
                        <span class="store-wa-online-status-dot"></span>
                    </div>
                    <div class="store-wa-title-box">
                        <h4 class="store-wa-pod-name">{{ $card_details->title }}</h4>
                        <span class="store-wa-reply-badge">{{ __('Typically replies instantly') }}</span>
                    </div>
                </div>
                <button type="button" class="btn store-wa-pod-close" onclick="toggleWaChatPod()"
                    aria-label="Close Chat">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <!-- Authentic Message Body Canvas -->
            <div class="store-wa-pod-body">
                <div class="store-wa-timestamp">{{ date('h:i A') }}</div>
                <div class="store-wa-message-bubble">
                    <p class="store-wa-bubble-text">
                        {{ __('Hello! 👋 Need help with your grocery order or have any questions?') }}
                    </p>
                    <div class="store-wa-bubble-meta">
                        <span class="store-wa-bubble-time">{{ date('h:i A') }}</span>
                        <i class="ti ti-checks store-wa-checks-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Footer Action Button -->
            <div class="store-wa-pod-footer">
                <a href="https://api.whatsapp.com/send?phone={{ $enquiry_button }}&text={{ urlencode(__('Hello! I am contacting you from your online grocery store.')) }}"
                    target="_blank" class="btn store-wa-pod-btn">
                    <i class="ti ti-brand-whatsapp store-wa-footer-icon"></i>
                    <span>{{ __('Start Chat on WhatsApp') }}</span>
                </a>
            </div>
        </div>

        <!-- 56px Luxury Trigger Button with Pulsing Wave -->
        <button type="button" class="btn store-wa-trigger-btn" id="storeWaTrigger" onclick="toggleWaChatPod()"
            aria-label="WhatsApp Chat">
            <i class="ti ti-brand-whatsapp store-wa-btn-icon"></i>
            <span class="store-wa-pulse-glow"></span>
        </button>
    </div>

    <style>
        /* WhatsApp Floating Engine */
        .store-wa-float-wrapper {
            position: fixed;
            bottom: 30px;
            right: 25px;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        /* RTL Direction Support */
        [dir="rtl"] .store-wa-float-wrapper {
            right: auto;
            left: 25px;
            align-items: flex-start;
        }

        @media (max-width: 991.98px) {
            .store-wa-float-wrapper {
                bottom: 92px !important;
                /* Mobile clearance over 75px bottom navigation bar */
                right: 18px !important;
            }

            [dir="rtl"] .store-wa-float-wrapper {
                right: auto !important;
                left: 18px !important;
            }
        }

        /* 56px Trigger Button with Luxury Emerald Gradient */
        .store-wa-trigger-btn {
            width: 56px;
            height: 56px;
            border-radius: 50% !important;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%) !important;
            color: #ffffff !important;
            border: 2px solid #ffffff !important;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 0 !important;
        }

        .store-wa-trigger-btn:hover {
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 211, 102, 0.6);
            color: #ffffff !important;
        }

        .store-wa-btn-icon {
            font-size: 32px;
            line-height: 1;
        }

        /* Pulsing Glow Ring */
        .store-wa-pulse-glow {
            position: absolute;
            inset: -4px;
            border-radius: 50% !important;
            border: 2px solid rgba(37, 211, 102, 0.7);
            animation: wa-pulse-ring 2s cubic-bezier(0.24, 0, 0.38, 1) infinite;
            pointer-events: none;
        }

        @keyframes wa-pulse-ring {
            0% {
                transform: scale(0.95);
                opacity: 0.8;
            }

            50% {
                transform: scale(1.25);
                opacity: 0;
            }

            100% {
                transform: scale(0.95);
                opacity: 0;
            }
        }

        /* Redesigned Luxury Chat Pod */
        .store-wa-chat-pod {
            width: 330px;
            max-width: calc(100vw - 36px);
            border-radius: 26px !important;
            background: #ffffff;
            border: 1px solid #c8e6c9;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.18);
            margin-bottom: 14px;
            overflow: hidden;
            display: none;
            flex-direction: column;
            transform-origin: bottom right;
            animation: wa-pod-spring 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        [dir="rtl"] .store-wa-chat-pod {
            transform-origin: bottom left;
        }

        .store-wa-chat-pod.open {
            display: flex;
        }

        @keyframes wa-pod-spring {
            from {
                opacity: 0;
                transform: scale(0.85) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Pod Header */
        .store-wa-pod-header {
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
            color: #ffffff;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .store-wa-pod-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .store-wa-avatar-box {
            position: relative;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
        }

        .store-wa-pod-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50% !important;
            object-fit: cover;
            border: 2px solid #ffffff;
            background-color: #f1f8e9;
        }

        .store-wa-online-status-dot {
            position: absolute;
            bottom: 1px;
            right: 1px;
            width: 11px;
            height: 11px;
            background-color: #25d366;
            border-radius: 50% !important;
            border: 2px solid #ffffff;
        }

        [dir="rtl"] .store-wa-online-status-dot {
            right: auto;
            left: 1px;
        }

        .store-wa-title-box {
            display: flex;
            flex-direction: column;
            text-align: start;
        }

        .store-wa-pod-name {
            font-size: 15px;
            font-weight: 800;
            margin: 0;
            color: #ffffff;
            line-height: 1.2;
        }

        .store-wa-reply-badge {
            font-size: 11px;
            color: #c8e6c9;
            font-weight: 500;
            margin-top: 2px;
        }

        .store-wa-pod-close {
            width: 30px;
            height: 30px;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.15) !important;
            border: none !important;
            color: #ffffff !important;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            transition: background 0.2s ease;
        }

        .store-wa-pod-close:hover {
            background: rgba(255, 255, 255, 0.3) !important;
        }

        /* Pod Body (Authentic WhatsApp Chat Pattern) */
        .store-wa-pod-body {
            padding: 20px 16px;
            background-color: #e5ddd5;
            background-image: radial-gradient(#d4cbbf 1px, transparent 1px);
            background-size: 16px 16px;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .store-wa-timestamp {
            align-self: center;
            background: rgba(255, 255, 255, 0.75);
            padding: 3px 10px;
            border-radius: 50px !important;
            font-size: 10px;
            font-weight: 700;
            color: #555555;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .store-wa-message-bubble {
            align-self: flex-start;
            background: #ffffff;
            border-radius: 18px 18px 18px 4px !important;
            padding: 12px 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            max-width: 88%;
            position: relative;
        }

        [dir="rtl"] .store-wa-message-bubble {
            align-self: flex-start;
            border-radius: 18px 18px 4px 18px !important;
        }

        .store-wa-bubble-text {
            font-size: 13px;
            color: #1f2937;
            margin: 0 0 6px 0;
            line-height: 1.45;
            font-weight: 500;
            text-align: start;
        }

        .store-wa-bubble-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
        }

        .store-wa-bubble-time {
            font-size: 10px;
            color: #888888;
            font-weight: 600;
        }

        .store-wa-checks-icon {
            font-size: 13px;
            color: #34b7f1;
        }

        /* Pod Footer */
        .store-wa-pod-footer {
            padding: 14px 16px;
            background-color: #ffffff;
            border-top: 1px solid #f1f5f9;
        }

        .store-wa-pod-btn {
            width: 100%;
            height: 46px;
            border-radius: 50px !important;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%) !important;
            color: #ffffff !important;
            border: none !important;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);
            transition: all 0.2s ease;
        }

        .store-wa-pod-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.5);
            color: #ffffff !important;
        }

        .store-wa-footer-icon {
            font-size: 20px;
        }
    </style>

    <script>
        // Toggle WhatsApp chat pod visibility
        function toggleWaChatPod() {
            const pod = document.getElementById('storeWaChatPod');
            if (pod) {
                pod.classList.toggle('open');
            }
        }
    </script>
@endif
