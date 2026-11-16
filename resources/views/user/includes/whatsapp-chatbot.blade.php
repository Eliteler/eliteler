{{-- WhatsApp Chatbot (customer panel) — up to 6 contacts, managed in Admin > Plugins > WhatsApp Chat Button --}}
@if (isset($config) && $config)
    @php
        $waContacts = class_exists(\Plugins\WhatsAppChatButton\Controllers\WhatsAppChatButtonController::class)
            ? \Plugins\WhatsAppChatButton\Controllers\WhatsAppChatButtonController::contacts($config)
            : [];
        $waShow = collect($config)->pluck('config_value', 'config_key')['show_whatsapp_chatbot_on_customer_panel'] ?? '0';
    @endphp
    @if ($waShow == '1' && count($waContacts) > 0)
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

                    @if (count($waContacts) > 1)
                        <div class="wa-contact-list" style="display:flex; flex-direction:column; gap:6px; margin-top:10px;">
                            <small class="text-muted">{{ __('Choose who to chat with') }}</small>
                            @foreach ($waContacts as $i => $contact)
                                <button type="button" class="wa-contact btn btn-sm text-start {{ $i === 0 ? 'btn-success' : 'btn-outline-success' }}"
                                    data-phone="{{ $contact['number'] }}" data-message="{{ $contact['message'] }}"
                                    style="display:flex; align-items:center; gap:8px; border-radius:10px;">
                                    <i class="ti ti-brand-whatsapp"></i>
                                    <span>{{ $contact['name'] !== '' ? $contact['name'] : __('Support') }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="wa-chat-footer">
                    <form id="waChatForm">
                        <div class="wa-input-group">
                            <input type="text" id="waMessage" class="wa-chat-input"
                                placeholder="{{ __('Type your message...') }}" autocomplete="off"
                                value="{{ $waContacts[0]['message'] }}">
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
                const contacts = Array.from(document.querySelectorAll("#waChatCard .wa-contact"));
                let phone = @json($waContacts[0]['number']);

                // Pick a contact (when more than one number is configured)
                contacts.forEach(function(button) {
                    button.addEventListener("click", function(e) {
                        e.stopPropagation();
                        contacts.forEach(function(b) {
                            b.classList.remove("btn-success");
                            b.classList.add("btn-outline-success");
                        });
                        button.classList.remove("btn-outline-success");
                        button.classList.add("btn-success");
                        phone = button.dataset.phone;
                        if (messageInput.value.trim() === "" && button.dataset.message) {
                            messageInput.value = button.dataset.message;
                        }
                    });
                });

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

                    let url = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`;

                    window.open(url, '_blank');
                    messageInput.value = '';
                });
            });
        </script>
    @endif
@endif
