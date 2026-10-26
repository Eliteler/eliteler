{{-- WhatsApp Chatbot --}}
@if (isset($config) && $config)
    @if ($config[40]->config_value == '1')
        <div class="wa-chatbot-wrapper d-print-none">

            {{-- Chat Popup --}}
            <div class="wa-chat-card" id="waChatCard">
                {{-- Header --}}
                <div class="wa-chat-header">
                    <div class="wa-chat-avatar">
                        <i class="ti ti-brand-whatsapp"></i>
                    </div>

                    <div class="wa-chat-info">
                        <h4>{{ __('Customer Support') }}</h4>
                        <span>{{ __('Typically replies instantly') }}</span>
                    </div>

                    <button type="button" class="wa-chat-close" id="waChatClose">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="wa-chat-body">
                    <div class="wa-chat-message">
                        {{ __('Hi there! How can we help you today?') }}
                    </div>
                </div>

                {{-- Footer --}}
                <div class="wa-chat-footer">
                    <form id="waChatForm">
                        <div class="wa-input-group">
                            <input type="text" id="waMessage" class="wa-chat-input"
                                placeholder="{{ __('Type your message...') }}" autocomplete="off">

                            <button type="submit" class="wa-send-btn">
                                <i class="ti ti-send"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Floating Button --}}
            <button type="button" class="wa-floating-btn" id="waChatToggle">
                <i class="ti ti-brand-whatsapp"></i>
            </button>
        </div>

        {{-- Script --}}
        <script>
            "use strict";

            document.addEventListener("DOMContentLoaded", function() {

                const toggleBtn = document.getElementById("waChatToggle");
                const closeBtn = document.getElementById("waChatClose");
                const chatCard = document.getElementById("waChatCard");
                const form = document.getElementById("waChatForm");
                const messageInput = document.getElementById("waMessage");

                // Toggle chatbot
                toggleBtn.addEventListener("click", function(e) {
                    e.stopPropagation();
                    chatCard.classList.toggle("active");
                });

                // Close button
                closeBtn.addEventListener("click", function() {
                    chatCard.classList.remove("active");
                });

                // Prevent closing when clicking inside card
                chatCard.addEventListener("click", function(e) {
                    e.stopPropagation();
                });

                // Close on outside click
                document.addEventListener("click", function() {
                    chatCard.classList.remove("active");
                });

                // Send message
                form.addEventListener("submit", function(e) {
                    e.preventDefault();
                    let message = messageInput.value.trim();

                    if (message === '') {
                        return;
                    }

                    let phone = "{{ $config[41]->config_value }}";
                    let url =
                        `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`;

                    window.open(url, '_blank');
                    messageInput.value = '';
                });
            });
        </script>
    @endif
@endif
