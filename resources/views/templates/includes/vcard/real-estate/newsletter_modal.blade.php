@php
    use App\Newsletter;
    $newsletter_pop = Newsletter::where('card_id', $business_card_details->card_id)->first();
    $nl_title = $newsletter_pop->title ?? __('Market Insights');
    $nl_desc =
        $newsletter_pop->description ??
        __('Subscribe to receive our monthly local market report, off-market property alerts, and staging tips!');
    $nl_button_text = $newsletter_pop->button_text ?? __('Subscribe');
@endphp

<div id="newsletterModal" data-csrf="{{ csrf_token() }}" data-email-error="{{ __('Please enter your email.') }}"
    data-vaild-email-error="{{ __('Please enter valid email.') }}">

    <div id="customNewsBox">

        {{-- Close button --}}
        <button class="custom-news-close" onclick="closeNewsModal()">
            <i class="fas fa-times"></i>
        </button>

        {{-- Icon badge --}}
        <div class="custom-news-icon-wrap">
            <i class="fas fa-home custom-news-icon"></i>
        </div>

        {{-- Title --}}
        <h3 class="custom-news-title">{{ $nl_title }}</h3>

        {{-- Gold divider --}}
        <div class="custom-news-divider">
            <span class="custom-news-divider__line"></span>
            <span class="custom-news-divider__diamond">◆</span>
            <span class="custom-news-divider__line"></span>
        </div>

        {{-- Description --}}
        <div class="custom-news-desc">{!! $nl_desc !!}</div>

        {{-- Messages --}}
        <div id="errorNewsMessage" class="news-message news-error"></div>
        <div id="successNewsMessage" class="news-message news-success"></div>

        {{-- Hidden card id --}}
        <input type="hidden" id="card_id" name="card_id" value="{{ $business_card_details->card_id }}">

        {{-- Email input --}}
        <input type="email" id="newsletter_email" name="email" class="custom-news-input"
            placeholder="{{ __('Email Address') }}" required>

        {{-- Subscribe button --}}
        <button id="subscribeButton" class="custom-news-btn">
            {{ $nl_button_text }}
        </button>

        {{-- Privacy note --}}
        <p class="custom-news-privacy">
            <i class="fas fa-lock"></i>{{ __('No spam. Unsubscribe at any time.') }}
        </p>

    </div>
</div>
