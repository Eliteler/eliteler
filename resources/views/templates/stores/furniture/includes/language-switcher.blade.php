@php
    use Illuminate\Support\Str;

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
        /* True Pill-Rounded Language Switcher */
        .store-lang-wrapper {
            display: inline-flex;
            align-items: center;
        }

        .store-language-select {
            height: 42px;
            padding: 0 34px 0 18px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            border-radius: 50px !important;
            border: 1.5px solid rgba(255, 255, 255, 0.6);
            background-color: #ffffff;
            color: #1e293b;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%231f4b48' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 14px;
        }

        .store-language-select:hover {
            border-color: #1f4b48;
        }

        .store-language-select:focus {
            border-color: #1f4b48;
            box-shadow: 0 0 0 3px rgba(31, 75, 72, 0.2);
            outline: none;
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
