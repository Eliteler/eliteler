@php
    use App\Newsletter;
    $newsletter_pop = Newsletter::where('card_id', $business_card_details->card_id)->first();

    $title = $newsletter_pop->title ?? __('Paw-some Deals!');
    $desc =
        $newsletter_pop->description ??
        __('Join our newsletter for weekly deals and a 15% off coupon for your next grooming service!');
    $button_text = $newsletter_pop->button_text ?? __('Sign Up');
@endphp

<div id="newsletterModal" data-csrf="{{ csrf_token() }}" data-email-error="{{ __('Please enter your email.') }}"
    data-vaild-email-error="{{ __('Please enter valid email.') }}">

    <div id="customNewsBox">

        <!-- Close -->
        <button class="custom-news-close" onclick="closeNewsModal()">
            <i class="fas fa-times"></i>
        </button>

        <!-- Icon -->
        <i class="fas fa-ticket-alt custom-news-icon"></i>

        <!-- Title -->
        <h3 class="custom-news-title">
            {{ $title }}
        </h3>

        <!-- Description -->
        <div class="custom-news-desc">
            {!! $desc !!}
        </div>

        <!-- Messages -->
        <div id="errorNewsMessage" class="news-message news-error"></div>
        <div id="successNewsMessage" class="news-message news-success"></div>

        <input type="hidden" id="card_id" name="card_id" value="{{ $business_card_details->card_id }}">

        <input type="email" id="newsletter_email" name="email" class="custom-news-input"
            placeholder="{{ __('Email Address') }}" required>

        <button id="subscribeButton" class="custom-news-btn">
            {{ $button_text }}
        </button>
    </div>
</div>

<script>
    "use strict";

    function openNewsModal() {
        const overlay = document.getElementById('newsletterModal');
        if (overlay) {
            overlay.classList.add('is-active');
        }
    }

    function closeNewsModal() {
        const overlay = document.getElementById('newsletterModal');
        if (overlay) {
            overlay.classList.remove('is-active');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            openNewsModal();
        }, 1500);
    });
</script>
