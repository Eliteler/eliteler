@php
    use Illuminate\Support\Str;

    // Check and set default locale only if session is not already set
    if (!Session::has('locale')) {
        $locale = $business_card_details->card_lang ?? app()->getLocale();
        Session::put('locale', $locale);
        app()->setLocale($locale);
    }
@endphp

{{-- Languages Switcher Component --}}
@if (!empty(config('app.languages')) && count(config('app.languages')) > 1)
    <div class="nav-item dropdown mx-2 store-lang-container">
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
        /* Standalone Language Switcher Styles */
        .store-lang-container {
            display: inline-flex;
            align-items: center;
        }

        .store-language-select {
            height: 38px;
            padding-top: 4px;
            padding-bottom: 4px;
            padding-left: 10px;
            padding-right: 28px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border-color: rgba(0, 0, 0, 0.15);
            background-color: #ffffff;
            color: #1e293b;
        }

        .store-language-select:focus {
            border-color: #206bc4;
            box-shadow: 0 0 0 2px rgba(32, 107, 196, 0.15);
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
