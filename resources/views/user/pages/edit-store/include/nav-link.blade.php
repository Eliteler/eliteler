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
        // Fetch the updated plan details
        $plan_details = json_decode($plan->plan_details, true);
    }
@endphp

<a href="{{ route('user.edit.store', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'basic' ? 'active' : '' }}">{{ __('Basic Details') }}</a>
<a href="{{ route('user.edit.categories', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'categories' ? 'active' : '' }}">{{ __('Categories') }}</a>
<a href="{{ route('user.edit.products', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'products' ? 'active' : '' }}">{{ __('Products') }}</a>
<a href="{{ route('user.store.orders', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'orders' ? 'active' : '' }}">{{ __('Orders') }}</a>
<a href="{{ route('user.edit.store.seo', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'seo' ? 'active' : '' }}">{{ __('SEO') }}</a>
<a href="{{ route('user.edit.store.hours', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'business-hours' ? 'active' : '' }}">{{ __('Business Hours') }}</a>

{{-- Advanced Settings --}}
@if ($plan_details['advanced_settings'] == 1)
    <a href="{{ route('user.edit.store.popups', Request::segment(4)) }}"
        class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'popups' ? 'active' : '' }}">{{ __('Popups') }}</a>
@endif

{{-- Check directory listing --}}
@php
    $directory_settings = null;

    if (plugin_active('Directory')) {
        $directory_settings = DB::table('directory_settings')->first();
    }
@endphp

@if (
    (!empty($plan_details['advanced_settings']) && $plan_details['advanced_settings'] == 1) ||
        (optional($directory_settings)->directory == 1 &&
            optional($directory_settings)->default_enable_directory_customers != 1))
    <a href="{{ route('user.edit.store.advanced.setting', Request::segment(4)) }}"
        class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'advanced-settings' ? 'active' : '' }}">
        {{ __('Advanced Settings') }}
    </a>
@endif 

{{-- Policies --}}
<a href="{{ route('user.edit.store.policies', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'policies' ? 'active' : '' }}">{{ __('Policies') }}</a>

<a href="{{ route('user.edit.store.settings', Request::segment(4)) }}"
    class="list-group-item list-group-item-action d-flex align-items-center no-anim {{ $link == 'settings' ? 'active' : '' }}">{{ __('Settings') }}</a>
