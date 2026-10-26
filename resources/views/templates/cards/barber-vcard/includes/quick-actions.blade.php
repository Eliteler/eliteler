{{-- Barbershop vCard — Dynamic Quick Actions & Social Contact Partial --}}
@php
    // 1. Phone number check
    $phoneFeature = collect($feature_details ?? [])->firstWhere('type', 'tel');

    // 2. WhatsApp check 
    $waFeature = collect($feature_details ?? [])->firstWhere('type', 'wa');
    $hasWhatsApp =
        !empty($waFeature) || !empty($enquiry_button) || (isset($whatsAppNumberExists) && $whatsAppNumberExists);

    // 3. Location / Address check
    $addressFeature = collect($feature_details ?? [])->firstWhere('type', 'address');
    $mapFeature = collect($feature_details ?? [])->firstWhere('type', 'map');
    $hasLocation = !empty($addressFeature) || !empty($mapFeature);

    // 4. Appointment check
    $hasAppointment =
        isset($appointmentEnabled) &&
        $appointmentEnabled == true &&
        (isset($plan_details['appointment']) && $plan_details['appointment'] == 1) &&
        !empty($appointment_slots);
@endphp
 
<div class="bb-quick-actions-wrapper my-6 px-2 bb-scroll-reveal">

    {{-- 1. Dynamic Barbershop Quick Actions Grid --}}
    <div class="bb-qa-grid">

        {{-- Action 1: Book Chair (Only if Appointment feature is enabled and configured) --}}
        @if ($hasAppointment)
            <a href="#appointment-section" class="bb-qa-card group">
                <div class="bb-qa-icon-wrap bb-qa-icon-red">
                    <i class="fas fa-cut text-base group-hover:rotate-12 transition-transform"></i>
                </div>
                <span class="bb-qa-label">{{ __('Book Chair') }}</span>
            </a>
        @endif

        {{-- Action 2: Call Shop (Only if Phone Number is filled) --}}
        @if (!empty($phoneFeature) && !empty($phoneFeature->content))
            <a href="tel:{{ $phoneFeature->content }}" class="bb-qa-card group">
                <div class="bb-qa-icon-wrap bb-qa-icon-gold">
                    <i class="fas fa-phone-alt text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="bb-qa-label">{{ __('Call Shop') }}</span>
            </a>
        @endif

        {{-- Action 3: WhatsApp (Only if WhatsApp/Enquiry is active) --}}
        @if ($hasWhatsApp)
            <button type="button" onclick="toggleWhatsAppModal(true)" class="bb-qa-card group">
                <div class="bb-qa-icon-wrap bb-qa-icon-green">
                    <i class="fab fa-whatsapp text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="bb-qa-label">{{ __('WhatsApp') }}</span>
            </button>
        @endif

        {{-- Action 4: Get Directions (Only if Location/Address is filled) --}}
        @if ($hasLocation)
            <a href="#location-section" class="bb-qa-card group">
                <div class="bb-qa-icon-wrap bb-qa-icon-gold">
                    <i class="fas fa-directions text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="bb-qa-label">{{ __('Directions') }}</span>
            </a>
        @endif

        {{-- Action 5: Save Contact / vCard (Always visible) --}}
        @if (isset($business_card_details->card_id))
            <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}" class="bb-qa-card group">
                <div class="bb-qa-icon-wrap bb-qa-icon-gold">
                    <i class="fas fa-address-book text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="bb-qa-label">{{ __('Save vCard') }}</span>
            </a>
        @endif

    </div>

    {{-- 2. Dynamic Social Contact Buttons (Phone, Snapchat, Instagram) --}}
    @if (isset($feature_details) && count($feature_details) > 0)
        <div class="bb-social-row">
            @foreach ($feature_details as $feature)
                @if (in_array($feature->type, ['tel', 'snapchat', 'instagram']) && !empty($feature->content))
                    {{-- Phone Button --}}
                    @if ($feature->type == 'tel')
                        <a href="tel:{{ $feature->content }}" class="bb-social-btn group" title="{{ __('Call') }}">
                            <i class="{{ $feature->icon }} text-lg group-hover:scale-110 transition-transform"></i>
                        </a>
                    @endif

                    {{-- Snapchat Button --}}
                    @if ($feature->type == 'snapchat')
                        <a href="{{ $feature->content }}" target="_blank" class="bb-social-btn group" title="Snapchat">
                            <i class="{{ $feature->icon }} text-lg group-hover:scale-110 transition-transform"></i>
                        </a>
                    @endif

                    {{-- Instagram Button --}}
                    @if ($feature->type == 'instagram')
                        <a href="{{ $feature->content }}" target="_blank" class="bb-social-btn group"
                            title="Instagram">
                            <i class="{{ $feature->icon }} text-xl group-hover:scale-110 transition-transform"></i>
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    @endif

</div>

{{-- Pure CSS Layout Enforcement --}}
<style>
    .bb-quick-actions-wrapper {
        position: relative;
        z-index: 10;
        width: 100%;
    }

    /* Auto-adjusting Grid: centers & resizes based on active item count */
    .bb-qa-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(85px, 1fr)) !important;
        gap: 0.65rem !important;
        width: 100% !important;
        margin-bottom: 1.25rem !important;
        justify-content: center !important;
    }

    /* Compact Card Styles */
    .bb-qa-card {
        background-color: var(--bb-char-2, #2b241e);
        border: 1px solid var(--bb-line, rgba(201, 162, 75, 0.25));
        padding: 0.75rem 0.25rem !important;
        border-radius: 0.85rem !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-decoration: none !important;
        transition: all 0.22s ease !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
        min-height: 5.5rem !important;
        width: 100% !important;
        box-sizing: border-box !important;
        cursor: pointer !important;
    }

    .bb-qa-card:hover {
        transform: translateY(-3px) !important;
        border-color: var(--bb-gold, #c9a24b) !important;
        box-shadow: 0 6px 18px rgba(201, 162, 75, 0.25) !important;
    }

    .bb-qa-icon-wrap {
        width: 2.5rem !important;
        height: 2.5rem !important;
        border-radius: 9999px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-bottom: 0.4rem !important;
        flex-shrink: 0 !important;
    }

    .bb-qa-icon-red {
        background: rgba(183, 40, 46, 0.22);
        border: 1px solid var(--bb-red, #b7282e);
        color: #f87171;
    }

    .bb-qa-icon-gold {
        background: rgba(201, 162, 75, 0.15);
        border: 1px solid var(--bb-gold, #c9a24b);
        color: var(--bb-gold, #c9a24b);
    }

    .bb-qa-icon-green {
        background: rgba(34, 197, 94, 0.18);
        border: 1px solid rgba(34, 197, 94, 0.4);
        color: #4ade80;
    }

    .bb-qa-label {
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        color: var(--bb-cream, #ede4d3) !important;
        line-height: 1.1 !important;
        text-align: center !important;
        display: block !important;
    }

    /* Social Row */
    .bb-social-row {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 0.75rem !important;
        padding-top: 0.25rem !important;
    }

    .bb-social-btn {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 3.25rem !important;
        height: 3.25rem !important;
        border-radius: 9999px !important;
        background-color: var(--bb-char-2, #2b241e) !important;
        border: 1px solid var(--bb-line, rgba(201, 162, 75, 0.25)) !important;
        color: var(--bb-gold, #c9a24b) !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3) !important;
    }

    .bb-social-btn:hover {
        border-color: var(--bb-gold, #c9a24b) !important;
        color: var(--bb-cream, #ede4d3) !important;
        transform: translateY(-2px) scale(1.05) !important;
    }
</style>
