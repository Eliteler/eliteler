{{--
    Shared <head> of every store theme: charset, CSRF, icons, app names, SEO tags and PWA.
    Themes include it first in <head>, then their own fonts and styles, then shared.custom-code.
    Params: $themeColor (browser bar colour, e.g. "#e91e63")
--}}
@php
    $seo = json_decode($business_card_details->seo_configurations ?? '');
    $profileIcon =
        !empty($business_card_details->profile) && file_exists(public_path($business_card_details->profile))
            ? url($business_card_details->profile)
            : asset('img/templates/all-categories.png');
    $favicon = is_object($seo) && !empty($seo->favicon) && file_exists(public_path($seo->favicon)) ? url($seo->favicon) : $profileIcon;
@endphp

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="icon" href="{{ $favicon }}" sizes="512x512" type="image/png">
<link rel="apple-touch-icon" href="{{ $favicon }}">
<meta name="theme-color" content="{{ $themeColor ?? '#ffffff' }}">
<meta name="application-name" content="{{ $card_details->title }}">
<meta name="apple-mobile-web-app-title" content="{{ $card_details->title }}">
<meta name="msapplication-TileColor" content="{{ $themeColor ?? '#ffffff' }}">
<meta name="msapplication-TileImage" content="{{ $profileIcon }}">

{!! SEOMeta::generate() !!}
{!! OpenGraph::generate() !!}
{!! Twitter::generate() !!}
{!! JsonLd::generate() !!}

@if (!empty($plan_details) && $plan_details['pwa'] == 1 && $business_card_details->is_enable_pwa == 1)
    @laravelPWA
    @isset($manifest)
        <link rel="manifest" href="{{ $manifest }}">
    @endisset
@endif
