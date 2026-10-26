@php
    use Illuminate\Support\Str;

    $locale = $business_card_details->card_lang;
    Session::put('locale', $locale);
    app()->setLocale($locale);

    // Mapping language locales to country flag codes
    $flagMap = [
        'en' => 'us',
        'ta' => 'in',
        'hi' => 'in',
        'kn' => 'in',
        'bn' => 'bd',
        'ar' => 'sa',
        'pt_br' => 'br',
        'pt' => 'pt',
        'bg' => 'bg',
        'zh' => 'cn',
        'hr' => 'hr',
        'nl' => 'nl',
        'fr' => 'fr',
        'et' => 'ee',
        'de' => 'de',
        'ht' => 'ht',
        'he' => 'il',
        'hu' => 'hu',
        'id' => 'id',
        'it' => 'it',
        'ja' => 'jp',
        'lt' => 'lt',
        'es' => 'es',
        'ru' => 'ru',
        'tr' => 'tr',
        'ur' => 'pk',
        'vi' => 'vn',
        'th' => 'th',
        'ko' => 'kr',
        'pl' => 'pl',
        'sv' => 'se',
        'el' => 'gr',
        'ro' => 'ro',
        'sk' => 'sk',
        'uk' => 'ua',
        'fa' => 'ir',
        'ms' => 'my',
    ];
@endphp

<!-- Tom Select CSS & Inline Horizontal Flag Styling -->
<link rel="stylesheet" href="{{ url('css/tom-select.base.min.css') }}">
<style>
    /* Tom Select Control Pill Container */
    .ts-wrapper.ts-language-select {
        min-width: 76px !important;
        width: auto !important;
    }

    .ts-language-select .ts-control {
        border-radius: 12px !important;
        padding: 4px 8px !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        box-shadow: none !important;
        cursor: pointer !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        min-height: 36px !important;
        height: 36px !important;
        white-space: nowrap !important;
    }

    /* Force Selected Item to be Inline Row */
    .ts-language-select .ts-control .item,
    .ts-language-select .selected-item {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        vertical-align: middle !important;
    }

    /* Dark Mode Control Button */
    .dark .ts-language-select .ts-control {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    .ts-language-select .ts-control input {
        display: none !important;
    }

    /* Dropdown Menu Container */
    .ts-dropdown.ts-language-dropdown {
        border-radius: 14px !important;
        box-shadow: 0 16px 35px -5px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        overflow: hidden !important;
        z-index: 99999 !important;
        margin-top: 6px !important;
        min-width: 105px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
    }

    .dark .ts-dropdown.ts-language-dropdown {
        background-color: #111625 !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.6) !important;
    }

    .ts-dropdown.ts-language-dropdown .ts-dropdown-content {
        max-height: 210px !important;
        overflow-y: auto !important;
        padding: 4px !important;
    }

    /* Dropdown Options Row */
    .ts-dropdown.ts-language-dropdown .option {
        padding: 6px 10px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        color: #334155 !important;
        transition: all 0.15s ease !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 8px !important;
    }

    .dark .ts-dropdown.ts-language-dropdown .option {
        color: #cbd5e1 !important;
    }

    .ts-dropdown.ts-language-dropdown .option:hover,
    .ts-dropdown.ts-language-dropdown .option.active {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }

    /* Inline Flag Image Sizing */
    .lang-flag-img {
        width: 16px !important;
        height: 11px !important;
        border-radius: 2px !important;
        object-fit: cover !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15) !important;
        flex-shrink: 0 !important;
        display: inline-block !important;
        margin: 0 !important;
    }

    /* Scrollbar */
    .ts-dropdown-content::-webkit-scrollbar {
        width: 4px;
    }

    .ts-dropdown-content::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }
</style>

<!-- Language Select Markup -->
<div class="relative inline-flex items-center">
    <select id="tom-language-select" class="ts-language-select" autocomplete="off" aria-label="{{ __('Select Language') }}">
        @foreach (config('app.languages') as $langLocale => $langName)
            @php
                $cleanLocale = strtolower($langLocale);
                $countryCode = $flagMap[$cleanLocale] ?? substr($cleanLocale, 0, 2);
                $flagUrl = "https://flagcdn.com/w40/{$countryCode}.png";
            @endphp
            <option value="{{ $langLocale }}" data-flag="{{ $flagUrl }}"
                {{ app()->getLocale() == $langLocale ? 'selected' : '' }}>
                {{ Str::upper($langLocale) }}
            </option>
        @endforeach
    </select>
</div>

{{-- Custom JS from Backend --}}
@push('custom-js')
    <!-- Tom Select JS -->
    <script src="{{ url('js/tom-select.complete.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            const selectEl = document.getElementById('tom-language-select');
            if (selectEl) {
                const tsInstance = new TomSelect(selectEl, {
                    controlInput: null,
                    dropdownClass: 'ts-dropdown ts-language-dropdown',
                    copyClassesToDropdown: false,
                    maxOptions: 50,
                    allowEmptyOption: false,
                    // Strict Horizontal Inline Rendering
                    render: {
                        item: function(data, escape) {
                            return `<span class="selected-item" style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; gap: 5px !important;">
                            <img src="${data.flag}" alt="${escape(data.text)}" class="lang-flag-img" onerror="this.style.display='none'"/>
                            <span style="font-size: 11px; font-weight: 800; line-height: 1;">${escape(data.text)}</span>
                        </span>`;
                        },
                        option: function(data, escape) {
                            return `<div class="option-item" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 8px !important;">
                            <img src="${data.flag}" alt="${escape(data.text)}" class="lang-flag-img" onerror="this.style.display='none'"/>
                            <span style="font-size: 11px; font-weight: 700;">${escape(data.text)}</span>
                        </div>`;
                        }
                    },
                    onChange: function(selectedLocale) {
                        if (!selectedLocale) return;

                        const businessCardId = '{{ $business_card_details->card_id }}';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '{{ csrf_token() }}';

                        fetch("{{ config('app.url') }}/set-locale", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": csrfToken,
                                    "Accept": "application/json"
                                },
                                body: JSON.stringify({
                                    locale: selectedLocale,
                                    card_id: businessCardId,
                                    _token: csrfToken
                                })
                            })
                            .then(() => {
                                window.location.replace(window.location.href);
                            })
                            .catch(error => {
                                console.error("Language switch error:", error);
                            });
                    }
                });
            }
        });
    </script>
@endpush
