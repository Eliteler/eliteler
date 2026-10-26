{{-- Language Switcher Component with Session & AJAX Set-Locale Engine --}}
@php
    use Illuminate\Support\Str;

    // Check and set default locale only if session is not already set
    if (!Session::has('locale')) {
        $locale = $business_card_details->card_lang ?? app()->getLocale();
        Session::put('locale', $locale);
        app()->setLocale($locale);
    }
@endphp

@if (!empty(config('app.languages')) && count(config('app.languages')) > 1)
    <div class="nav-item dropdown store-lang-container">
        <div class="lang">
            <select class="form-select store-language-select {{ isset($rounded) ? $rounded : '' }}" id="language"
                name="language" aria-label="{{ __('Select Language') }}">
                @foreach (config('app.languages') as $langLocale => $langName)
                    <option value="{{ $langLocale }}" {{ app()->getLocale() == $langLocale ? 'selected' : '' }}>
                        {{ Str::upper($langLocale) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <style>
        /* Standalone Language Switcher Styles for Fashion Theme */
        .store-lang-container {
            display: inline-flex;
            align-items: center;
        }

        .store-language-select {
            height: 44px;
            padding-top: 6px;
            padding-bottom: 6px;
            padding-left: 12px;
            padding-right: 32px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 0px !important;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background-color: transparent;
            color: #ffffff;
            text-transform: uppercase;
            transition: all 0.2s ease;
        }

        .store-language-select option {
            background-color: #111827;
            color: #ffffff;
            font-weight: 600;
        }

        .store-language-select:focus {
            border-color: #ffffff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.15);
            outline: none;
        }
    </style>

    <script>
        // Standalone AJAX Language Switch Handler
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            const langSelect = document.getElementById('language');
            if (langSelect) {
                langSelect.addEventListener('change', function() {
                    const selectedLocale = this.value;
                    const businessCardId = '{{ $business_card_details->card_id ?? '' }}';

                    $.ajax({
                        url: "{{ config('app.url') }}/set-locale",
                        type: "POST",
                        data: {
                            locale: selectedLocale,
                            card_id: businessCardId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            // Hard reload to reflect selected locale
                            window.location.replace(window.location.href);
                        },
                        error: function(xhr) {
                            console.error("Language switch error:", xhr);
                        }
                    });
                });
            }
        });
    </script>
@endif
