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

<div class="custom-lang-switcher" id="langSwitcherContainer">

    <div class="lang-switcher-btn" id="langSwitcherBtn" onclick="toggleLangMenu(event)">
        <div class="lang-globe"></div>
        <span class="lang-code" id="currentLang">{{ Str::upper(app()->getLocale()) }}</span>
        <i class="fas fa-chevron-down"></i>
    </div>

    <ul class="lang-dropdown-menu" id="langMenu">

        <li class="lang-search-li" onclick="event.stopPropagation()">
            <div class="lang-search-wrap">
                <div class="lang-search-icon"></div>
                <input class="lang-search-input" id="langSearchInput" type="text"
                    placeholder="{{ __('Search language…') }}" oninput="filterLangs(this.value)"
                    onclick="event.stopPropagation()" />
            </div>
        </li>

        <div class="lang-list-inner" id="langListInner">
            @foreach (config('app.languages') as $langLocale => $langName)
                <li class="{{ app()->getLocale() == $langLocale ? 'active' : '' }}"
                    data-name="{{ Str::lower($langName) }} {{ Str::lower($langLocale) }}"
                    onclick="selectLang('{{ $langLocale }}', '{{ Str::upper($langLocale) }}')">
                    <span class="lang-item-code">{{ Str::upper($langLocale) }}</span>
                    <span class="lang-item-name">{{ $langName }}</span>
                    <span class="lang-item-check">&#xf00c;</span>
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

        /* ── Open ─────────────────────────────────────────────────────── */
        function openLangMenu() {
            var btn = document.getElementById('langSwitcherBtn');
            var menu = document.getElementById('langMenu');
            var input = document.getElementById('langSearchInput');
            if (!btn || !menu) return;

            if (input) {
                input.value = '';
                filterLangs('');
            }

            menu.style.display = 'block';

            requestAnimationFrame(function() {
                menu.classList.add('open');
                btn.classList.add('open');
            });
        }

        /* ── Close ────────────────────────────────────────────────────── */
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

        /* ── Toggle ───────────────────────────────────────────────────── */
        window.toggleLangMenu = function(e) {
            e.stopPropagation();
            var menu = document.getElementById('langMenu');
            if (!menu) return;
            menu.classList.contains('open') ? closeLangMenu() : openLangMenu();
        };

        /* ── Select ───────────────────────────────────────────────────── */
        window.selectLang = function(locale, label) {
            var el = document.getElementById('currentLang');
            if (el) el.textContent = label;
            closeLangMenu();

            fetch("{{ config('app.url') }}/set-locale", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        locale: locale,
                        card_id: '{{ $business_card_details->card_id }}'
                    })
                })
                .then(function(r) {
                    if (!r.ok) throw new Error('Failed');
                    window.location.reload();
                })
                .catch(function(err) {
                    console.error('Locale switch error:', err);
                });
        };

        /* ── Filter ───────────────────────────────────────────────────── */
        window.filterLangs = function(query) {
            var q = query.toLowerCase().trim();
            var items = document.querySelectorAll('#langListInner li');
            var visible = 0;

            items.forEach(function(li) {
                var match = !q || (li.dataset.name || '').includes(q);
                li.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            var empty = document.getElementById('langEmpty');
            if (empty) empty.style.display = visible === 0 ? '' : 'none';
        };

        /* ── Outside click ────────────────────────────────────────────── */
        document.addEventListener('click', function(e) {
            var container = document.getElementById('langSwitcherContainer');
            if (container && !container.contains(e.target)) {
                closeLangMenu();
            }
        });

        /* ── Reposition ───────────────────────────────────────────────── */
        window.addEventListener('resize', function() {
            if (document.getElementById('langMenu')?.classList.contains('open')) {
            }
        });

        document.addEventListener('scroll', function() {
            if (document.getElementById('langMenu')?.classList.contains('open')) {
            }
        }, true);

    })();
</script>
