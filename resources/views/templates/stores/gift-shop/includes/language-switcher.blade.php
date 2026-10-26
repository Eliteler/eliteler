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
        /* Scoped High-Contrast Language Switcher */
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
            border: 1.5px solid #e2e8f0 !important;
            /* Crisp Visible Border */
            background-color: #f8fafc !important;
            /* Distinct Background against white header */
            color: #1c1917 !important;
            transition: all 0.25s ease-in-out;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            appearance: none;
            -webkit-appearance: none;
            /* Custom Gift-Shop Red Chevron Arrow */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23dc2626' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 13px;
        }

        .store-language-select:hover {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.12);
        }

        .store-language-select:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
            outline: none;
            background-color: #ffffff !important;
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
