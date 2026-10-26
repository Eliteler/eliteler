{{-- Scoped Grocery Green Language Switcher Component --}}
@php
    use Illuminate\Support\Str;

    // Check and set default locale in session
    if (!Session::has('locale')) {
        $locale = $business_card_details->card_lang ?? app()->getLocale();
        Session::put('locale', $locale);
        app()->setLocale($locale);
    }
@endphp

@if (!empty(config('app.languages')) && count(config('app.languages')) > 1)
    <div class="store-lang-wrapper">
        <select class="form-select store-language-select" name="store_language_select"
            aria-label="{{ __('Select Language') }}">
            @foreach (config('app.languages') as $langLocale => $langName)
                <option value="{{ $langLocale }}" {{ app()->getLocale() == $langLocale ? 'selected' : '' }}>
                    {{ Str::upper($langLocale) }}
                </option>
            @endforeach
        </select>
    </div>

    <style>
        /* Scoped Grocery Green Language Switcher */
        .store-lang-wrapper {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .store-language-select {
            height: 42px;
            min-width: 82px;
            padding: 0 34px 0 16px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            border-radius: 50px !important;
            border: 1.5px solid #c8e6c9 !important;
            background-color: #ffffff !important;
            color: #1b5e20 !important;
            transition: all 0.25s ease-in-out;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 6px rgba(46, 125, 50, 0.08);
            appearance: none;
            -webkit-appearance: none;
            /* Grocery Theme Green Chevron Arrow (#2e7d32) */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232e7d32' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 13px;
        }

        .store-language-select:hover {
            border-color: #2e7d32 !important;
            background-color: #e8f5e9 !important;
            color: #0a3d0c !important;
            box-shadow: 0 4px 10px rgba(46, 125, 50, 0.15);
        }

        .store-language-select:focus {
            border-color: #2e7d32 !important;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.18) !important;
            outline: none;
            background-color: #ffffff !important;
            color: #1b5e20 !important;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            const langSelects = document.querySelectorAll('.store-language-select');

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
