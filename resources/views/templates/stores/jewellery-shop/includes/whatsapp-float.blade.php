{{-- Ultra-Luxury Redesigned WhatsApp Floating Widget Component --}}
@php
    $waPhone = preg_replace('/[^0-9]/', '', $whatsappNumber ?? ($enquiry_button ?? ''));
    $storeTitle = $businessName ?? ($card_details->title ?? 'Customer Support');
    $storeProfile = !empty($businessImage)
        ? url($businessImage)
        : (!empty($business_card_details->profile)
            ? url($business_card_details->profile)
            : '');
    $defaultMsg = urlencode(__('Hi, I am interested in your luxury jewellery collection.'));
@endphp

@if (!empty($waPhone))
    <div class="luxury-wa-floater-root">
        <!-- Interactive Desktop Teaser Pill -->
        <div class="wa-teaser-pill d-none d-md-flex" id="waTeaserPill" onclick="toggleWaChatPod()">
            <span class="wa-teaser-text">{{ __('Need help? Chat with us') }}</span>
            <span class="wa-teaser-sparkle">✨</span>
        </div>

        <!-- 56px Circular Trigger Button with Pulsing Dot -->
        <button type="button" class="luxury-wa-trigger-btn" id="waFloaterToggle" onclick="toggleWaChatPod()"
            aria-label="Chat on WhatsApp">
            <i class="ti ti-brand-whatsapp"></i>
            <span class="wa-pulsing-badge"></span>
        </button>

        <!-- Slide-up Interactive Chat Window -->
        <div class="luxury-wa-chat-pod" id="waChatPod">
            <!-- Header -->
            <div class="wa-chat-pod-header">
                <div class="wa-brand-details">
                    <div class="wa-avatar-wrap">
                        <img src="{{ $storeProfile }}" alt="{{ $storeTitle }}"
                            onerror="this.onerror=null;this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2244%22%20height%3D%2244%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%2325D366%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23ffffff%22%20font-size%3D%2218%22%20text-anchor%3D%22middle%22%20dy%3D%22.3em%22%3EWA%3C%2Ftext%3E%3C%2Fsvg%3E';" />
                        <span class="wa-avatar-online-dot"></span>
                    </div>
                    <div>
                        <h5 class="wa-owner-name">{{ $storeTitle }}</h5>
                        <p class="wa-online-status">
                            <i class="ti ti-circle-filled wa-online-glyph"></i>
                            <span>{{ __('Online • Typically replies in minutes') }}</span>
                        </p>
                    </div>
                </div>
                <button type="button" class="wa-pod-close-btn" onclick="toggleWaChatPod()" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <!-- Chat Body with Authentic Bubble -->
            <div class="wa-chat-pod-body">
                <div class="wa-chat-bubble">
                    <p class="wa-bubble-message">
                        {{ __('Hello! Welcome to our store. How can we assist you with our jewellery collection today?') }}
                    </p>
                    <div class="wa-bubble-meta">
                        <span class="wa-bubble-time">{{ date('H:i') }}</span>
                        <span class="wa-bubble-checks">✓✓</span>
                    </div>
                </div>

                <!-- Interactive Quick Query Chips -->
                <div class="wa-quick-chips-wrap">
                    <button type="button" class="wa-chip-btn"
                        onclick="sendQuickWaQuery('{{ urlencode(__('Hi, I have an inquiry about a product.')) }}')">
                        <span>💍 {{ __('Product Inquiry') }}</span>
                    </button>
                    <button type="button" class="wa-chip-btn"
                        onclick="sendQuickWaQuery('{{ urlencode(__('Hi, I would like to track my order status.')) }}')">
                        <span>📦 {{ __('Order Status') }}</span>
                    </button>
                    <button type="button" class="wa-chip-btn"
                        onclick="sendQuickWaQuery('{{ urlencode(__('Hi, I want to inquire about custom jewellery designs.')) }}')">
                        <span>✨ {{ __('Custom Design') }}</span>
                    </button>
                </div>
            </div>

            <!-- Footer Action Button -->
            <div class="wa-chat-pod-footer">
                <a href="https://api.whatsapp.com/send/?phone={{ $waPhone }}&text={{ $defaultMsg }}"
                    target="_blank" class="btn btn-wa-chat-start" id="waDirectStartBtn">
                    <i class="ti ti-brand-whatsapp"></i>
                    <span>{{ __('Start Chat on WhatsApp') }}</span>
                </a>
            </div>
        </div>
    </div>

    <style>
        /* Base Floating Root Container */
        .luxury-wa-floater-root {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1040;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* 90px Mobile Clearance above bottom navigation bar */
        @media (max-width: 991px) {
            .luxury-wa-floater-root {
                bottom: 90px !important;
                right: 18px !important;
            }
        }

        /* Desktop Teaser Pill */
        .wa-teaser-pill {
            background: #ffffff;
            border: 1px solid rgba(212, 175, 55, 0.4);
            border-radius: 50px !important;
            padding: 8px 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            animation: waTeaserBounce 3s ease-in-out infinite;
            transition: all 0.25s ease;
        }

        .wa-teaser-pill:hover {
            transform: translateY(-2px);
            border-color: #25d366;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.2);
        }

        .wa-teaser-text {
            font-size: 13px;
            font-weight: 700;
            color: #2b2316;
            line-height: 1;
        }

        .wa-teaser-sparkle {
            font-size: 14px;
        }

        @keyframes waTeaserBounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        /* 56px Circular Trigger Button */
        .luxury-wa-trigger-btn {
            width: 56px;
            height: 56px;
            border-radius: 50% !important;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: #ffffff;
            border: 2px solid #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.45), 0 0 0 2px rgba(212, 175, 55, 0.3);
            cursor: pointer;
            position: relative;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            flex-shrink: 0;
        }

        .luxury-wa-trigger-btn:hover {
            transform: scale(1.1) rotate(6deg);
        }

        /* Pulsing Notification Red Badge */
        .wa-pulsing-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50% !important;
            background: #ef4444;
            border: 2.5px solid #ffffff;
            animation: waPulseGlow 1.8s infinite;
        }

        @keyframes waPulseGlow {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Slide-up Chat Window */
        .luxury-wa-chat-pod {
            position: absolute;
            bottom: 70px;
            right: 0;
            width: 320px;
            max-width: 90vw;
            background: #ffffff;
            border-radius: 24px !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25), 0 0 20px rgba(212, 175, 55, 0.15);
            border: 1.5px solid rgba(212, 175, 55, 0.35);
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: waSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes waSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Header Styling */
        .wa-chat-pod-header {
            background: linear-gradient(135deg, #075e54 0%, #128c7e 100%);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
        }

        .wa-brand-details {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .wa-avatar-wrap {
            position: relative;
            width: 42px;
            height: 42px;
            border-radius: 50% !important;
            overflow: visible;
            border: 2px solid #d4af37;
            background: #ffffff;
            flex-shrink: 0;
        }

        .wa-avatar-wrap img {
            width: 100%;
            height: 100%;
            border-radius: 50% !important;
            object-fit: cover;
        }

        .wa-avatar-online-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 11px;
            height: 11px;
            border-radius: 50% !important;
            background: #25d366;
            border: 2px solid #ffffff;
        }

        .wa-owner-name {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 2px 0;
            color: #ffffff;
            line-height: 1.2;
        }

        .wa-online-status {
            font-size: 11px;
            color: #d1fae5;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            line-height: 1.2;
        }

        .wa-online-glyph {
            font-size: 7px;
            color: #25d366;
        }

        .wa-pod-close-btn {
            width: 32px;
            height: 32px;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .wa-pod-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* Body & Realistic Bubble */
        .wa-chat-pod-body {
            padding: 16px;
            background: #efeae2;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .wa-chat-bubble {
            background: #ffffff;
            padding: 12px 14px;
            border-radius: 16px 16px 16px 4px !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .wa-bubble-message {
            font-size: 13px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0 0 6px 0;
        }

        .wa-bubble-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
        }

        .wa-bubble-time {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
        }

        .wa-bubble-checks {
            font-size: 11px;
            color: #3b82f6;
            /* WhatsApp Blue Double Ticks */
            font-weight: 700;
            line-height: 1;
        }

        /* Quick Option Chips */
        .wa-quick-chips-wrap {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .wa-chip-btn {
            background: #ffffff;
            border: 1px solid rgba(212, 175, 55, 0.35);
            border-radius: 50px !important;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #4b3e2b;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .wa-chip-btn:hover {
            background: #fbf7ee;
            border-color: #d4af37;
            color: #2b2316;
            transform: translateX(4px);
        }

        /* Footer CTA Button */
        .wa-chat-pod-footer {
            padding: 12px 16px;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
        }

        .btn-wa-chat-start {
            width: 100%;
            height: 44px;
            border-radius: 50px !important;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%) !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.35);
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .btn-wa-chat-start i {
            font-size: 18px;
        }

        .btn-wa-chat-start:hover {
            box-shadow: 0 8px 22px rgba(37, 211, 102, 0.45);
            transform: translateY(-1px);
        }
    </style>

    <script>
        // Toggle Chat Pod Visibility
        function toggleWaChatPod() {
            const pod = document.getElementById('waChatPod');
            const teaser = document.getElementById('waTeaserPill');
            if (!pod) return;

            const isVisible = (pod.style.display === 'flex');
            pod.style.display = isVisible ? 'none' : 'flex';

            if (teaser) {
                teaser.style.display = isVisible ? 'flex' : 'none';
            }
        }

        // Send Custom Pre-filled Quick Query
        function sendQuickWaQuery(encodedMsg) {
            const phone = "{{ $waPhone }}";
            const targetUrl = `https://api.whatsapp.com/send/?phone=${phone}&text=${encodedMsg}`;
            window.open(targetUrl, '_blank');
        }
    </script>
@endif
