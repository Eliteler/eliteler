<style>
    .list-group-transparent {
        margin: 0 -1.0rem !important;
    }
</style>

@php
    use App\User;
    use App\Plan;
    use App\BusinessCard;
    use Carbon\Carbon;

    // Card details
    $business_card = BusinessCard::where('card_id', Request::segment(3))->first();

    // Fetch the user plan
    $plan = User::where('id', Auth::user()->id)
        ->where('status', 1)
        ->first();
    $planData = json_decode($plan->plan_details, true);

    if ($planData) {
        // Fetch the default plan details only once if necessary
        if (!$planData || !isset($planData['appointment'])) {
            $planDefaults = Plan::where('plan_id', $plan->plan_id)->first();
        }

        // Check and assign missing plan details
        $planData['appointment'] = $planData['appointment'] ?? $planDefaults->appointment;

        // Update plan details if necessary
        if ($planData !== json_decode($plan->plan_details, true)) {
            $plan->plan_details = json_encode($planData);
            $plan->updated_at = Carbon::now();
            $plan->save();
        }

        // Fetch the updated plan details
        $plan_details = json_decode($plan->plan_details, true);
    }
@endphp

<a href="{{ route('user.edit.card', Request::segment(3)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'basic' ? 'active' : '' }}">{{ __('Basic Details') }}</a>


@if ($business_card->type != 'business' && $business_card->type != 'custom')
    @if ($plan_details['no_of_links'] > 0)
        <a href="{{ route('user.edit.social.links', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'links' ? 'active' : '' }}">{{ __('Social Links') }}</a>
    @endif

    @if ($plan_details['appointment'] == 1)
        <a href="{{ route('user.edit.appointment', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'appointment' ? 'active' : '' }}">{{ __('Appointment') }}</a>
    @endif
@endif

@if ($business_card->type == 'business' || $business_card->type == 'custom')
    @if ($plan_details['no_of_links'] > 0)
        <a href="{{ route('user.edit.social.links', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'links' ? 'active' : '' }}">{{ __('Social Links') }}</a>
    @endif

    @if ($plan_details['no_of_payments'] > 0)
        <a href="{{ route('user.edit.payment.links', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'payments' ? 'active' : '' }}">{{ __('Payment Links') }}</a>
    @endif

    @if ($plan_details['no_of_services'] > 0)
        <a href="{{ route('user.edit.services', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'services' ? 'active' : '' }}">{{ __('Services') }}</a>
    @endif

    @if ($plan_details['no_of_vcard_products'] > 0)
        <a href="{{ route('user.edit.vproducts', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'products' ? 'active' : '' }}">{{ __('Products') }}</a>
    @endif

    @if ($plan_details['no_of_galleries'] > 0)
        <a href="{{ route('user.edit.galleries', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'galleries' ? 'active' : '' }}">{{ __('Galleries') }}</a>
    @endif

    @if ($plan_details['no_testimonials'] > 0)
        <a href="{{ route('user.edit.testimonials', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'testimonial' ? 'active' : '' }}">{{ __('Testimonials') }}</a>
    @endif

    <a href="{{ route('user.edit.popups', Request::segment(3)) }}"
        class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'popups' ? 'active' : '' }}">{{ __('Popups') }}</a>

    @if ($plan_details['business_hours'] == 1)
        <a href="{{ route('user.edit.business.hours', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'hours' ? 'active' : '' }}">{{ __('Business Hours') }}</a>
    @endif

    @if ($plan_details['appointment'] == 1)
        <a href="{{ route('user.edit.appointment', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'appointment' ? 'active' : '' }}">{{ __('Appointment') }}</a>
    @endif

    @if ($plan_details['contact_form'] == 1)
        <a href="{{ route('user.edit.contact.form', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'contact' ? 'active' : '' }}">{{ __('Contact Form') }}</a>
    @endif

    @if (isset($plan_details['service_booking']) && $plan_details['service_booking'] == 1)
        <a href="{{ route('user.edit.booking', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'booking' ? 'active' : '' }}">{{ __('Service Booking') }}</a>
    @endif

    @if ($business_card->type == 'custom')
        <a href="{{ route('user.edit.customization', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'customization' ? 'active' : '' }}">{{ __('Customization') }}</a>
    @endif
@endif

@if ($plan_details['password_protected'] == 1 || $plan_details['advanced_settings'] == 1)
    <a href="{{ route('user.edit.advanced.setting', Request::segment(3)) }}"
        class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'advanced' ? 'active' : '' }}">{{ __('Advanced Settings') }}</a>
@endif

@if ($business_card->type == 'business' || $business_card->type == 'custom')
    <a href="{{ route('user.edit.section.title', Request::segment(3)) }}"
        class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'section-titles' ? 'active' : '' }}">{{ __('Section Titles') }}</a>
@endif

@if ($business_card->type == 'business' || $business_card->type == 'custom')
    @if (isset($plan_details['google_wallet']) &&
            $plan_details['google_wallet'] == 1 &&
            plugin_active('GoogleWallet'))
        <a href="{{ route('user.edit.google-wallet', Request::segment(3)) }}"
            class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'google-wallet' ? 'active' : '' }}">{{ __('Google Wallet') }}</a>
    @endif
    <a href="{{ route('user.edit.intro-screen', Request::segment(3)) }}"
        class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'intro-screen' ? 'active' : '' }}">{{ __('Intro Screen') }}</a>
@endif
