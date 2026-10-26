@php
    $waNumber = preg_replace('/[^0-9]/', '', $enquiry_button ?? '');
    $svgAvatarFallback =
        'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2236%22%20height%3D%2236%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2218%22%20cy%3D%2218%22%20r%3D%2218%22%20fill%3D%22%23cca47a%22%2F%3E%3C%2Fsvg%3E';
@endphp

@if (!empty($waNumber))
    <style>
        /* 1. Floating Container with Mobile Bottom Clearance */
        .wa-float-container {
            position: fixed;
            bottom: 90px;
            /* Sits safely above 75px bottom bar */
            right: 16px;
            z-index: 1035;
        }

        @media (min-width: 768px) {
            .wa-float-container {
                bottom: 24px;
                right: 24px;
            }
        }

        /* 2. 56px Main WhatsApp Floating Button */
        .wa-circle-btn {
            width: 56px;
            height: 56px;
            border-radius: 50% !important;
            background-color: #25d366;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4);
            cursor: pointer;
            position: relative;
            border: none;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
            padding: 0;
        }

        .wa-circle-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 28px rgba(37, 211, 102, 0.5);
            color: #ffffff !important;
        }

        .wa-circle-btn i {
            font-size: 30px;
            color: #ffffff !important;
            line-height: 1;
        }

        /* Pulsing Red Attention Dot */
        .wa-pulse-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 14px;
            height: 14px;
            background-color: #dc2626;
            border: 2px solid #ffffff;
            border-radius: 50%;
            animation: waDotPulse 1.5s infinite;
        }

        @keyframes waDotPulse {
            0% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(220, 38, 38, 0);
            }

            100% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
            }
        }

        /* 3. Luxury Slide-Up Interactive Chat Dialog */
        .wa-chat-card {
            position: absolute;
            bottom: 68px;
            right: 0;
            width: 320px;
            background-color: #ffffff;
            border-radius: 20px !important;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(204, 164, 122, 0.35);
            display: none;
            overflow: hidden;
            animation: waCardSlide 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes waCardSlide {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .wa-chat-header {
            background-color: #1f4b48;
            color: #ffffff;
            padding: 16px;
        }

        .wa-avatar-img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid rgba(255, 255, 255, 0.5);
            background-color: #ffffff;
        }

        .wa-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.85);
        }

        .wa-online-indicator {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #25d366;
        }

        .wa-chat-close-btn {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            transition: color 0.2s ease;
        }

        .wa-chat-close-btn:hover {
            color: #ffffff;
        }

        /* Chat Body & Bubble */
        .wa-chat-body {
            padding: 16px;
            background-color: #faf8f5;
        }

        .wa-chat-bubble {
            background-color: #ffffff;
            border: 1px solid #ede8df;
            border-radius: 14px 14px 14px 4px;
            padding: 12px 14px;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.5;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            margin-bottom: 14px;
        }

        /* Pill-Rounded WhatsApp CTA Button */
        .wa-start-chat-btn {
            height: 44px;
            border-radius: 9999px !important;
            background-color: #25d366;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 14px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .wa-start-chat-btn:hover {
            background-color: #1eb956;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
    </style>

    <div class="wa-float-container">
        {{-- Interactive Slide-Up Chat Box --}}
        <div class="wa-chat-card" id="waChatBox">
            <div class="wa-chat-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ !empty($business_card_details->profile) ? url($business_card_details->profile) : $svgAvatarFallback }}"
                        class="wa-avatar-img" alt="{{ $card_details->title }}"
                        onerror="this.onerror=null;this.src='{{ $svgAvatarFallback }}';">
                    <div>
                        <h5 class="mb-0 text-white fs-4 fw-bold text-truncate" style="max-width: 190px;">
                            {{ $card_details->title }}</h5>
                        <div class="wa-status-badge">
                            <span class="wa-online-indicator"></span>
                            <span>{{ __('Online Support') }}</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="wa-chat-close-btn" onclick="toggleWaChat()" aria-label="Close Chat">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <div class="wa-chat-body">
                <div class="wa-chat-bubble">
                    {{ __('Hello! How can we assist you with our store products today?') }}
                </div>

                <a href="https://api.whatsapp.com/send?phone={{ $waNumber }}&text={{ urlencode('Hello, I would like to inquire about your store products.') }}"
                    target="_blank" class="wa-start-chat-btn">
                    <i class="ti ti-brand-whatsapp fs-2"></i>
                    <span>{{ __('Start WhatsApp Chat') }}</span>
                </a>
            </div>
        </div>

        {{-- Main 56px Floating Button --}}
        <button type="button" class="wa-circle-btn" onclick="toggleWaChat()" aria-label="WhatsApp Support">
            <i class="ti ti-brand-whatsapp"></i>
            <span class="wa-pulse-dot"></span>
        </button>
    </div>

    <script>
        function toggleWaChat() {
            const chatBox = document.getElementById('waChatBox');
            if (chatBox) {
                chatBox.style.display = (chatBox.style.display === 'block') ? 'none' : 'block';
            }
        }

        // Auto close dialog when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.querySelector('.wa-float-container');
            const chatBox = document.getElementById('waChatBox');
            if (container && !container.contains(e.target) && chatBox) {
                chatBox.style.display = 'none';
            }
        });
    </script>
@endif
