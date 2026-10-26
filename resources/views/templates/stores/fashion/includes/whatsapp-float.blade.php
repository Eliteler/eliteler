{{-- Modern Luxury WhatsApp Floating Chat Widget Component --}}
@php
    $cleanPhone = preg_replace('/[^0-9]/', '', $whatsappNumber ?? '');
    $bizImg = !empty($businessImage) ? $businessImage : $business_card_details->profile ?? 'images/default-avatar.png';
    $bizName = !empty($businessName) ? $businessName : $card_details->title ?? 'Store';
@endphp

@if (!empty($cleanPhone))
    <style>
        /* Floating Circular Button */
        .fashion-wa-btn {
            position: fixed;
            bottom: 30px;
            right: 25px;
            z-index: 1035;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(18, 140, 126, 0.45);
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
            direction: ltr;
        }

        .fashion-wa-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 30px rgba(18, 140, 126, 0.55);
        }

        .fashion-wa-icon {
            font-size: 30px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* Outer Rim Pulsing Notification Dot */
        .fashion-wa-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: #ef4444;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            animation: fashionWaPulse 1.8s infinite;
        }

        @keyframes fashionWaPulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1.1);
                box-shadow: 0 0 0 7px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Slide-up Chat Window */
        .fashion-wa-chat {
            position: fixed;
            bottom: 96px;
            right: 25px;
            width: 320px;
            background-color: #ffffff;
            border-radius: 0px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
            display: none;
            flex-direction: column;
            z-index: 1040;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.28s ease, transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fashion-wa-chat.active {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }

        .fashion-wa-chat.closing {
            opacity: 0;
            transform: translateY(20px);
        }

        /* Header */
        .fashion-wa-header {
            background-color: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border-bottom: 2px solid #25d366;
        }

        .fashion-wa-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
        }

        .fashion-wa-info {
            flex: 1;
            min-width: 0;
        }

        .fashion-wa-name {
            font-family: 'Oswald', sans-serif;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fashion-wa-status {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .fashion-wa-online-dot {
            width: 7px;
            height: 7px;
            background-color: #25d366;
            border-radius: 50%;
            display: inline-block;
        }

        .fashion-wa-close {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 22px;
            cursor: pointer;
            padding: 0 4px;
            line-height: 1;
            transition: color 0.2s ease;
        }

        .fashion-wa-close:hover {
            color: #ffffff;
        }

        /* Body & Message */
        .fashion-wa-body {
            background-color: #f8fafc;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .fashion-wa-bubble {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #0f172a;
            padding: 12px 14px;
            font-size: 13px;
            line-height: 1.5;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .fashion-wa-link-btn {
            background-color: #0f172a;
            color: #ffffff !important;
            text-align: center;
            padding: 12px 16px;
            border-radius: 0px;
            text-decoration: none !important;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
            transition: background-color 0.2s ease;
        }

        .fashion-wa-link-btn:hover {
            background-color: #1e293b;
            color: #ffffff;
        }

        /* RTL Direction */
        [dir="rtl"] .fashion-wa-btn,
        [dir="rtl"] .fashion-wa-chat {
            right: auto;
            left: 25px;
        }

        [dir="rtl"] .fashion-wa-avatar {
            margin-right: 0;
            margin-left: 12px;
        }

        [dir="rtl"] .fashion-wa-bubble {
            border-left: 1px solid #e2e8f0;
            border-right: 3px solid #0f172a;
        }

        /* Mobile Screens */
        @media (max-width: 767px) {
            .fashion-wa-btn {
                bottom: 85px;
                right: 16px;
                width: 52px;
                height: 52px;
            }

            .fashion-wa-icon {
                font-size: 27px;
            }

            .fashion-wa-chat {
                bottom: 145px;
                right: 16px;
                width: calc(100% - 32px);
                max-width: 340px;
            }

            [dir="rtl"] .fashion-wa-btn,
            [dir="rtl"] .fashion-wa-chat {
                right: auto;
                left: 16px;
            }
        }
    </style>

    <!-- Slide-Up Interactive WhatsApp Chat Dialog -->
    <div id="fashionWhatsAppChat" class="fashion-wa-chat">
        <div class="fashion-wa-header">
            <img src="{{ url($bizImg) }}" alt="{{ $bizName }}" class="fashion-wa-avatar">
            <div class="fashion-wa-info">
                <h5 class="fashion-wa-name">{{ $bizName }}</h5>
                <p class="fashion-wa-status">
                    <span class="fashion-wa-online-dot"></span>
                    <span>{{ __('Online | Instant Support') }}</span>
                </p>
            </div>
            <button type="button" class="fashion-wa-close" onclick="closeFashionWaChat()"
                aria-label="Close Chat">&times;</button>
        </div>

        <div class="fashion-wa-body">
            <div class="fashion-wa-bubble">
                <strong>{{ __('Hello!') }}</strong> 👋<br>
                {{ __('Have any questions about our collection? We are ready to help you.') }}
            </div>
            <a href="https://api.whatsapp.com/send/?phone={{ $cleanPhone }}&text={{ urlencode(__('Hello, I would like to enquire about your products.')) }}"
                class="fashion-wa-link-btn" target="_blank" rel="noopener noreferrer">
                <i class="ti ti-brand-whatsapp fs-2 text-success"></i>
                <span>{{ __('Start Conversation') }}</span>
            </a>
        </div>
    </div>

    <!-- 56px Symmetrical Floating Circular Button -->
    <div class="fashion-wa-btn" id="fashionWaTriggerBtn" onclick="toggleFashionWaChat()"
        title="{{ __('Chat on WhatsApp') }}" aria-label="Open WhatsApp Chat">
        <i class="ti ti-brand-whatsapp fashion-wa-icon"></i>
        <span class="fashion-wa-dot"></span>
    </div>

    <!-- Toggle & Outside Click Script -->
    <script>
        (function() {
            "use strict";

            const chat = document.getElementById('fashionWhatsAppChat');
            const btn = document.getElementById('fashionWaTriggerBtn');

            window.toggleFashionWaChat = function() {
                if (!chat) return;
                if (chat.classList.contains('active')) {
                    window.closeFashionWaChat();
                } else {
                    window.openFashionWaChat();
                }
            };

            window.openFashionWaChat = function() {
                if (!chat) return;
                chat.style.display = 'flex';
                requestAnimationFrame(() => {
                    chat.classList.add('active');
                });
            };

            window.closeFashionWaChat = function() {
                if (!chat) return;
                chat.classList.remove('active');
                chat.classList.add('closing');
                setTimeout(() => {
                    chat.classList.remove('closing');
                    chat.style.display = 'none';
                }, 280);
            };

            // Auto-close on click outside
            document.addEventListener('click', function(e) {
                if (chat && btn && chat.classList.contains('active')) {
                    const isInsideChat = chat.contains(e.target);
                    const isInsideBtn = btn.contains(e.target);
                    if (!isInsideChat && !isInsideBtn) {
                        window.closeFashionWaChat();
                    }
                }
            });
        })();
    </script>
@endif
