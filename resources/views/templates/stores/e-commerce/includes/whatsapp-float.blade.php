@php
    // Safe fallbacks & phone number sanitization
    $cleanPhone = preg_replace('/[^0-9]/', '', $whatsappNumber ?? '');
    $bizImg = !empty($businessImage)
        ? $businessImage
        : $business_card_details->profile ?? 'img/templates/all-categories.png';
    $bizName = !empty($businessName) ? $businessName : $card_details->title ?? 'Store';
@endphp

@if (!empty($cleanPhone))
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        /* Perfectly Symmetrical Circular Floating Button */
        .whatsapp-float-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1035;
            width: 56px;
            height: 56px;
            background-color: #25d366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(37, 211, 102, 0.45);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            direction: ltr;
        }

        .whatsapp-float-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.55);
        }

        /* 100% Centered WhatsApp Icon */
        .whatsapp-icon-centered {
            font-size: 32px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        /* Outer Rim Pulsing Notification Dot: Never Crowds the Icon */
        .whatsapp-notification-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: #ef4444;
            border-radius: 50%;
            width: 13px;
            height: 13px;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
            animation: pulse-glow-badge 1.6s infinite;
        }

        @keyframes pulse-glow-badge {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Sliding Chat Box Window */
        .whatsapp-chat {
            position: fixed;
            bottom: 92px;
            right: 25px;
            width: 320px;
            background-color: #ffffff;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.22);
            display: none;
            flex-direction: column;
            z-index: 1040;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            opacity: 0;
            transform: translateY(18px);
            transition: all 0.28s ease;
        }

        .whatsapp-chat.active {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }

        .whatsapp-chat.closing {
            opacity: 0;
            transform: translateY(18px);
        }

        /* Header */
        .whatsapp-header {
            background-color: #075e54;
            color: #ffffff;
            display: flex;
            align-items: center;
            padding: 14px 16px;
        }

        .whatsapp-header .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.25);
        }

        .whatsapp-header .info {
            flex: 1;
            min-width: 0;
        }

        .whatsapp-header .info strong {
            display: block;
            font-size: 15px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .whatsapp-header .info small {
            font-size: 12px;
            opacity: 0.85;
            display: block;
        }

        .whatsapp-header .close-chat {
            font-size: 24px;
            cursor: pointer;
            opacity: 0.8;
            padding: 0 4px;
            line-height: 1;
        }

        .whatsapp-header .close-chat:hover {
            opacity: 1;
        }

        /* Chat Body */
        .whatsapp-body {
            background-color: #efeae2;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .whatsapp-body .message {
            background-color: #ffffff;
            color: #1f2d3d;
            border-radius: 12px;
            border-top-left-radius: 2px;
            padding: 12px 14px;
            font-size: 14px;
            line-height: 1.45;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .chat-button {
            background-color: #25d366;
            color: #ffffff !important;
            text-align: center;
            padding: 11px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
            transition: background-color 0.2s ease;
        }

        .chat-button:hover {
            background-color: #1ebd5b;
        }

        /* RTL Direction */
        [dir="rtl"] .whatsapp-float-btn {
            right: auto;
            left: 25px;
        }

        [dir="rtl"] .whatsapp-chat {
            right: auto;
            left: 25px;
        }

        [dir="rtl"] .whatsapp-header .avatar {
            margin-right: 0;
            margin-left: 12px;
        }

        /* Mobile & Tablet Screens: Elevated safely above the 75px bottom navigation bar */
        @media (max-width: 991.98px) {
            .whatsapp-float-btn {
                bottom: 92px !important;
                /* 75px bottom bar + 17px comfortable gap */
                right: 16px !important;
                width: 52px;
                height: 52px;
            }

            .whatsapp-icon-centered {
                font-size: 28px;
            }

            .whatsapp-chat {
                bottom: 155px !important;
                /* Positioned safely above the elevated float button */
                right: 16px !important;
                width: calc(100% - 32px);
                max-width: 340px;
            }

            [dir="rtl"] .whatsapp-float-btn {
                right: auto !important;
                left: 16px !important;
            }

            [dir="rtl"] .whatsapp-chat {
                right: auto !important;
                left: 16px !important;
            }
        }
    </style>

    <!-- WhatsApp Chat Popup Box -->
    <div id="whatsapp-chat" class="whatsapp-chat">
        <div class="whatsapp-header">
            <img src="{{ url($bizImg) }}" alt="{{ $bizName }}" class="avatar"
                onerror="this.onerror=null; this.src='{{ asset('img/templates/all-categories.png') }}';">
            <div class="info">
                <strong>{{ $bizName }}</strong>
                <small>{{ __('Typically replies within a day') }}</small>
            </div>
            <div class="close-chat" onclick="toggleWhatsAppChat()">&times;</div>
        </div>
        <div class="whatsapp-body">
            <div class="message">
                {{ __('Hi there') }} &#128075;<br>
                {{ __('How can I help you today?') }}
            </div>
            <a href="https://wa.me/{{ $cleanPhone }}" class="chat-button" target="_blank" rel="noopener noreferrer">
                <i class="ti ti-brand-whatsapp fs-2"></i>
                <span>{{ __('Chat on WhatsApp') }}</span>
            </a>
        </div>
    </div>

    <!-- 100% Symmetrical Circular WhatsApp Trigger Button -->
    <div class="whatsapp-float-btn" onclick="toggleWhatsAppChat()" title="{{ __('Chat with us') }}">
        <i class="ti ti-brand-whatsapp whatsapp-icon-centered"></i>
        <span class="whatsapp-notification-dot"></span>
    </div>

    <!-- Toggle & Outside Click Script -->
    <script>
        const chatBox = document.getElementById('whatsapp-chat');
        const floatBtn = document.querySelector('.whatsapp-float-btn');

        function toggleWhatsAppChat() {
            if (chatBox.classList.contains('active')) {
                closeWhatsAppChat();
            } else {
                openWhatsAppChat();
            }
        }

        function openWhatsAppChat() {
            chatBox.style.display = 'flex';
            requestAnimationFrame(() => {
                chatBox.classList.add('active');
            });
        }

        function closeWhatsAppChat() {
            chatBox.classList.remove('active');
            chatBox.classList.add('closing');
            setTimeout(() => {
                chatBox.classList.remove('closing');
                chatBox.style.display = 'none';
            }, 280);
        }

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (chatBox && floatBtn) {
                const isClickInsideChat = chatBox.contains(e.target);
                const isClickOnButton = floatBtn.contains(e.target);

                if (!isClickInsideChat && !isClickOnButton && chatBox.classList.contains('active')) {
                    closeWhatsAppChat();
                }
            }
        });
    </script>
@endif
