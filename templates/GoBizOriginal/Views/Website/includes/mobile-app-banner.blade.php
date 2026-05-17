<style>
    .app-section {
        padding: 90px 20px;
        text-align: center;
        font-family: NativeCode-OutFit, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
    }

    .container {
        margin: 0 auto;
    }

    .app-title {
        font-size: 46px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 14px;
        letter-spacing: -0.6px;
    }

    .app-subtitle {
        font-size: 17px;
        color: #64748b;
        max-width: 640px;
        margin: 0 auto 45px;
        line-height: 1.7;
    }

    .app-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .app-buttons a {
        display: inline-block;
        transition: transform 0.2s ease;
    }

    .app-buttons a:hover {
        transform: scale(1.05);
    }

    .google-play-badge {
        height: 53.3px;
        display: block;
    }

    .apple-app-store-badge {
        height: 48px;
        display: block;
    }

    /* Responsive */
    @media (max-width: 640px) {
        .app-title {
            font-size: 32px;
        }

        .app-subtitle {
            font-size: 15px;
        }
    }
</style>

<section class="app-section">
    <div class="container">

        <h2 class="app-title">
            {{ __($template_config->app_heading ?? 'Your Business, In Your Pocket') }}
        </h2>

        <p class="app-subtitle">
            {{ __($template_config->app_description ?? 'Control your business cards, store, and NFC tools from a single mobile app. Stay connected and never miss an opportunity.') }}
        </p>

        <div class="app-buttons">

            @if ($template_config->google_play_store_link)
                <a href="{{ $template_config->google_play_store_link ?: '#' }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('img/google-play-badge.png') }}" alt="Google Play" class="google-play-badge">
                </a>
            @endif

            @if ($template_config->apple_app_store_link)
                <a href="{{ $template_config->apple_app_store_link ?: '#' }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('img/apple-app-store-badge.png') }}" alt="Apple App Store"
                        class="apple-app-store-badge">
                </a>
            @endif

        </div>

    </div>
</section>
