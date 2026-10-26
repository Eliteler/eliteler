@php
    use Illuminate\Support\Str;

    if (!Session::has('locale')) {
        $locale = $business_card_details->card_lang;
        Session::put('locale', $locale);
    } else { 
        $locale = Session::get('locale');
    }
 
    app()->setLocale($locale);
@endphp

@php
    $languageFlags = [
        'ar' => '🇸🇦',
        'bn' => '🇧🇩',
        'de' => '🇩🇪',
        'en' => '🇬🇧',
        'es' => '🇪🇸',
        'fr' => '🇫🇷',
        'gu' => '🇮🇳',
        'hi' => '🇮🇳',
        'id' => '🇮🇩',
        'it' => '🇮🇹',
        'ja' => '🇯🇵',
        'ko' => '🇰🇷',
        'mr' => '🇮🇳',
        'nl' => '🇳🇱',
        'pt' => '🇵🇹',
        'pt_BR' => '🇧🇷',
        'ru' => '🇷🇺',
        'ta' => '🇮🇳',
        'te' => '🇮🇳',
        'tr' => '🇹🇷',
        'ur' => '🇵🇰',
        'vi' => '🇻🇳',
        'zh' => '🇨🇳',
    ];
@endphp

<div class="custom-lang-switcher" id="langSwitcherContainer">

    <div class="lang-switcher-btn" id="langSwitcherBtn" onclick="toggleLangMenu(event)">
        <span class="lang-current-flag" id="currentLangFlag">🌐</span>
        <span class="lang-code" id="currentLang">{{ Str::upper(app()->getLocale()) }}</span>
        <i class="fas fa-chevron-down text-xs ml-1"></i>
    </div>

    <ul class="lang-dropdown-menu" id="langMenu">

        <li class="lang-search-li" onclick="event.stopPropagation()">
            <div class="lang-search-wrap">
                <i class="fas fa-search"></i>
                <input class="lang-search-input" id="langSearchInput" type="text"
                    placeholder="{{ __('Search language…') }}" oninput="filterLangs(this.value)"
                    onclick="event.stopPropagation()" autocomplete="off" />
            </div>
        </li>

        <div class="lang-list-inner" id="langListInner">
            @foreach (config('app.languages') as $langLocale => $langName)
                @php($flag = $languageFlags[$langLocale] ?? '🌐')
                <li class="lang-item {{ app()->getLocale() == $langLocale ? 'active' : '' }}"
                    data-name="{{ Str::lower($langName) }} {{ Str::lower($langLocale) }}"
                    onclick="selectLang(event, '{{ $langLocale }}', '{{ Str::upper($langLocale) }}')">
                    <span class="lang-item-code">{{ Str::upper($langLocale) }}</span>
                    <span class="lang-item-name">{{ $langName }}</span>
                </li>
            @endforeach
        </div>

        <li class="lang-empty" id="langEmpty" style="display:none;">
            {{ __('No results') }}
        </li>

    </ul>

</div>

<script>
    "use strict";

    (function() {
        const languageFlags = @json($languageFlags);

        function openLangMenu() {
            var btn = document.getElementById('langSwitcherBtn');
            var menu = document.getElementById('langMenu');
            var input = document.getElementById('langSearchInput');
            if (!btn || !menu) return;

            menu.style.display = 'block';

            requestAnimationFrame(function() {
                menu.classList.add('open');
                btn.classList.add('open');
                if (input) {
                    input.value = '';
                    filterLangs('');
                    setTimeout(function() {
                        input.focus();
                    }, 60);
                }
            });
        }

        function closeLangMenu() {
            var btn = document.getElementById('langSwitcherBtn');
            var menu = document.getElementById('langMenu');
            if (!btn || !menu) return;

            menu.classList.remove('open');
            btn.classList.remove('open');

            setTimeout(function() {
                menu.style.display = 'none';
            }, 200);
        }

        window.toggleLangMenu = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            var menu = document.getElementById('langMenu');
            if (!menu) return;
            menu.classList.contains('open') ? closeLangMenu() : openLangMenu();
        };

        window.selectLang = function(e, locale, label) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            var el = document.getElementById('currentLang');
            if (el) el.textContent = label;
            var flag = document.getElementById('currentLangFlag');
            if (flag) flag.textContent = languageFlags[locale] || '🌐';
            closeLangMenu();

            var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '{{ csrf_token() }}';

            fetch("{{ config('app.url') }}/set-locale", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        locale: locale,
                        card_id: '{{ $business_card_details->card_id ?? '' }}'
                    })
                })
                .then(function() {
                    window.location.reload();
                })
                .catch(function(err) {
                    console.error('Locale switch error:', err);
                    var u = new URL(window.location.href);
                    u.searchParams.set('lang', locale);
                    window.location.href = u.toString();
                });
        };

        window.filterLangs = function(query) {
            var q = (query || '').toLowerCase().trim();
            var items = document.querySelectorAll('#langListInner .lang-item');
            var visible = 0;

            items.forEach(function(li) {
                var name = (li.dataset.name || '').toLowerCase();
                var match = !q || name.indexOf(q) !== -1;
                if (match) {
                    li.style.setProperty('display', 'flex', 'important');
                    visible++;
                } else {
                    li.style.setProperty('display', 'none', 'important');
                }
            });

            var empty = document.getElementById('langEmpty');
            if (empty) {
                empty.style.display = visible === 0 ? 'block' : 'none';
            }
        };

        document.addEventListener('click', function(e) {
            var container = document.getElementById('langSwitcherContainer');
            if (container && !container.contains(e.target)) {
                closeLangMenu();
            }
        });
    })();
</script>
