{{-- Women's Salon vCard — Quick Actions Component --}}
@php
    $phoneFeature = collect($feature_details ?? [])->firstWhere('type', 'tel');
    $waFeature = collect($feature_details ?? [])->firstWhere('type', 'wa');
    $hasWhatsApp =
        !empty($waFeature) || !empty($enquiry_button) || (isset($whatsAppNumberExists) && $whatsAppNumberExists); 
    $addressFeature = collect($feature_details ?? [])->firstWhere('type', 'address');
    $mapFeature = collect($feature_details ?? [])->firstWhere('type', 'map');
    $hasLocation = !empty($addressFeature) || !empty($mapFeature);
    $hasAppointment =
        isset($appointmentEnabled) &&
        $appointmentEnabled == true &&
        (isset($plan_details['appointment']) && $plan_details['appointment'] == 1) &&
        !empty($appointment_slots);
@endphp
 
<div class="salon-qa-wrapper my-6 px-2">
    <div class="salon-qa-grid">

        {{-- Action 1: Book Chair --}}
        @if ($hasAppointment)
            <a href="#appointment-section" class="salon-qa-card group">
                <div class="salon-qa-icon-wrap salon-icon-rose">
                    <i class="fas fa-cut text-base group-hover:rotate-12 transition-transform"></i>
                </div>
                <span class="salon-qa-label">{{ __('Book Chair') }}</span>
            </a>
        @endif

        {{-- Action 2: Call Salon --}}
        @if (!empty($phoneFeature) && !empty($phoneFeature->content))
            <a href="tel:{{ $phoneFeature->content }}" class="salon-qa-card group">
                <div class="salon-qa-icon-wrap salon-icon-gold">
                    <i class="fas fa-phone-alt text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="salon-qa-label">{{ __('Call Salon') }}</span>
            </a>
        @endif

        {{-- Action 3: WhatsApp --}}
        @if ($hasWhatsApp)
            <button type="button" onclick="toggleWhatsAppModal(true)" class="salon-qa-card group">
                <div class="salon-qa-icon-wrap salon-icon-green">
                    <i class="fab fa-whatsapp text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="salon-qa-label">{{ __('WhatsApp') }}</span>
            </button>
        @endif

        {{-- Action 4: Directions --}}
        @if ($hasLocation)
            <a href="#location-section" class="salon-qa-card group">
                <div class="salon-qa-icon-wrap salon-icon-gold">
                    <i class="fas fa-directions text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="salon-qa-label">{{ __('Directions') }}</span>
            </a>
        @endif

        {{-- Action 5: Save vCard --}}
        @if (isset($business_card_details->card_id))
            <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
                class="salon-qa-card group">
                <div class="salon-qa-icon-wrap salon-icon-gold">
                    <i class="fas fa-address-book text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <span class="salon-qa-label">{{ __('Save vCard') }}</span>
            </a>
        @endif

    </div>

    {{-- Social Icon Buttons --}}
    @if (isset($feature_details) && count($feature_details) > 0)
        <div class="salon-social-row">
            @foreach ($feature_details as $feature)
                @if (in_array($feature->type, ['tel', 'snapchat', 'instagram']) && !empty($feature->content))
                    @if ($feature->type == 'tel')
                        <a href="tel:{{ $feature->content }}" class="salon-social-btn group"
                            title="{{ __('Call') }}">
                            <i class="{{ $feature->icon }} text-lg group-hover:scale-110 transition-transform"></i>
                        </a>
                    @endif
                    @if ($feature->type == 'snapchat')
                        <a href="{{ $feature->content }}" target="_blank" class="salon-social-btn group"
                            title="Snapchat">
                            <i class="{{ $feature->icon }} text-lg group-hover:scale-110 transition-transform"></i>
                        </a>
                    @endif
                    @if ($feature->type == 'instagram')
                        <a href="{{ $feature->content }}" target="_blank" class="salon-social-btn group"
                            title="Instagram">
                            <i class="{{ $feature->icon }} text-xl group-hover:scale-110 transition-transform"></i>
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    @endif
</div>
