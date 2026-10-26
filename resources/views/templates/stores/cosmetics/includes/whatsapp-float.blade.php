<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    /* --------------------------------------------- */
    /* Floating Button (LTR Default) */
    .whatsapp-float-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 999;
        background-color: #25d366;
        border-radius: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px 16px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        transition: all 0.3s ease;
        max-width: 240px;
        font-family: 'Inter', sans-serif;
        direction: ltr;
    }

    .whatsapp-float-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    .whatsapp-float-btn .btn-inner {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        flex-direction: row;
    }

    .whatsapp-float-btn img {
        width: 32px;
        height: 32px;
    }

    .whatsapp-float-btn .btn-text {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        white-space: nowrap;
        text-align: left;
    }

    /* Notification Dot */
    .notification-dot {
        position: absolute;
        top: -12px;
        right: -12px;
        background-color: red;
        border-radius: 50%;
        width: 12px;
        height: 12px;
        animation: pulse-glow 1.5s infinite;
    }

    @keyframes pulse-glow {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
        }
    }

    /* Chat Box */
    .whatsapp-chat {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 300px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        z-index: 10000;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        opacity: 0;
        transform: translateY(20px);
        transition: 0.3s;
    }

    .whatsapp-chat.active {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    .whatsapp-chat.closing {
        opacity: 0;
        transform: translateY(20px);
    }

    /* Header */
    .whatsapp-header {
        background-color: #075e54;
        color: #fff;
        display: flex;
        align-items: center;
        padding: 10px;
    }

    .whatsapp-header .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .whatsapp-header .info {
        flex: 1;
    }

    .whatsapp-header .info strong {
        display: block;
        font-size: 14px;
    }

    .whatsapp-header .info small {
        font-size: 12px;
    }

    .whatsapp-header .close-chat {
        font-size: 20px;
        cursor: pointer;
    }

    /* Body */
    .whatsapp-body {
        background-color: #e5ddd5;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .whatsapp-body .message {
        background-color: #fff;
        color: #1f2d3d;
        border-radius: 8px;
        padding: 10px;
        font-size: 14px;
    }

    .chat-button {
        background-color: #25d366;
        color: white;
        text-align: center;
        padding: 8px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 16px;
    }

    .chat-button:hover {
        background-color: #1ebd5b;
    }

    /* RTL */
    [dir="rtl"] .whatsapp-float-btn {
        right: auto;
        left: 20px;
    }

    [dir="rtl"] .whatsapp-chat {
        right: auto;
        left: 20px;
    }

    /* ---------------- MOBILE ---------------- */
    @media (max-width: 767px) {
        .whatsapp-float-btn {
            bottom: 90px;
            right: 12px;
            padding: 12px;
            max-width: 180px;
        }

        .whatsapp-float-btn img {
            width: 28px;
            height: 28px;
        }

        .whatsapp-float-btn .btn-text {
            font-size: 14px;
        }

        .whatsapp-chat {
            bottom: 150px;
            right: 12px;
            width: 90%;
        }
    }

    /* ---------------- TABLET ---------------- */
    @media (min-width: 768px) and (max-width: 1024px) {
        .whatsapp-float-btn {
            bottom: 90px;
            right: 18px;
            padding: 14px;
            max-width: 220px;
        }

        .whatsapp-chat {
            bottom: 150px;
            right: 18px;
            width: 280px;
        }
    }

    /* ---------------- SMALL DEVICES ---------------- */
    @media (max-width: 400px) {
        .whatsapp-body .message {
            font-size: 13px;
        }

        .chat-button {
            font-size: 14px;
        }
    }
</style>

<!-- WhatsApp Chat Box -->
<div id="whatsapp-chat" class="whatsapp-chat">
    <div class="whatsapp-header">
        <img src="{{ asset($businessImage) }}" alt="{{ __($businessName) }}" class="avatar">
        <div class="info">
            <strong>{{ __($businessName) }}</strong>
            <small>{{ __('Typically replies within a day') }}</small>
        </div>
        <div class="close-chat" onclick="toggleWhatsAppChat()">×</div>
    </div>
    <div class="whatsapp-body">
        <div class="message">
            {{ __('Hi there') }} 👋<br>
            {{ __('How can I help you?') }}
        </div>
        <a href="https://wa.me/{{ $whatsappNumber }}" class="chat-button"
            target="_blank">{{ __('Chat on WhatsApp') }}</a>
    </div>
</div>

<!-- Floating WhatsApp Button -->
<div class="whatsapp-float-btn" onclick="toggleWhatsAppChat()">
    <div class="btn-inner">
        <img src="{{ asset('img/whatsapp-float-icon.svg') }}" alt="{{ __('Chat') }}">
        <span class="notification-dot"></span>
    </div>
</div>

<!-- Toggle Script -->
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
        }, 300); // Match your CSS transition time
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        const isClickInsideChat = chatBox.contains(e.target);
        const isClickOnButton = floatBtn.contains(e.target);

        if (!isClickInsideChat && !isClickOnButton && chatBox.classList.contains('active')) {
            closeWhatsAppChat();
        }
    });
</script>
