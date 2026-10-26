{{-- Scoped Luxury Gold Language Switcher Component --}}
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Session;

    // Initialize and persist default locale in session
    if (!Session::has('locale')) {
        $locale = $business_card_details->card_lang ?? app()->getLocale();
        Session::put('locale', $locale);
        app()->setLocale($locale);
    }
@endphp

@if (!empty(config('app.languages')) && count(config('app.languages')) > 1)
    <div class="luxury-lang-wrapper">
        <select class="form-select luxury-language-select" name="store_language_select"
            aria-label="{{ __('Select Language') }}">
            @foreach (config('app.languages') as $langLocale => $langName)
                <option value="{{ $langLocale }}" {{ app()->getLocale() == $langLocale ? 'selected' : '' }}>
                    {{ Str::upper($langLocale) }}
                </option>
            @endforeach
        </select>
    </div>

    <style>
        /* Scoped Luxury Gold Language Switcher */
        .luxury-lang-wrapper {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .luxury-language-select {
            height: 42px;
            min-width: 84px;
            padding: 0 34px 0 16px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            border-radius: 50px !important;
            border: 1.5px solid rgba(212, 175, 55, 0.45) !important;
            background-color: #ffffff !important;
            color: #53401b !important;
            transition: all 0.25s ease-in-out;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.12);
            appearance: none;
            -webkit-appearance: none;
            /* Luxury Gold Chevron Arrow (#d4af37) */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23d4af37' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 13px;
        }

        .luxury-language-select:hover {
            border-color: #d4af37 !important;
            background-color: #fcf8ee !important;
            color: #2c2210 !important;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.22);
        }

        .luxury-language-select:focus {
            border-color: #d4af37 !important;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.25) !important;
            outline: none;
            background-color: #ffffff !important;
            color: #2c2210 !important;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            const langSelects = document.querySelectorAll('.luxury-language-select');

            langSelects.forEach(function(selectElement) {
                selectElement.addEventListener('change', function() {
                    const selectedLocale = this.value;
                    const businessCardId = '{{ $business_card_details->card_id ?? '' }}';
                    const targetUrl = "{{ url('set-locale') }}";

                    // Session-safe AJAX locale switcher
                    $.ajax({
                        url: targetUrl,
                        type: "POST",
                        data: {
                            locale: selectedLocale,
                            card_id: businessCardId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            window.location.replace(window.location.href);
                        },
                        error: function(xhr) {
                            console.error("Language switch failed:", xhr);
                            window.location.reload();
                        }
                    });
                });
            });
        });
    </script>
@endif
