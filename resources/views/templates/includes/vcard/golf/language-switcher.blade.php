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

<!-- Language Switcher -->
<div class="custom-lang-switcher" id="langSwitcherContainer">

    <!-- Visible Button -->
    <div class="lang-switcher-btn" id="langSwitcherBtn" onclick="toggleLangMenu(event)">
        <span id="currentLang">{{ Str::upper(app()->getLocale()) }}</span>
        <i class="fas fa-chevron-down"></i>
    </div>

    <!-- Dropdown Menu — populated from Laravel config -->
    <ul class="lang-dropdown-menu" id="langMenu">
        @foreach (config('app.languages') as $langLocale => $langName)
            <li class="{{ app()->getLocale() == $langLocale ? 'active' : '' }}"
                onclick="selectLang('{{ $langLocale }}', '{{ Str::upper($langLocale) }}')">
                {{ $langName }} ({{ Str::upper($langLocale) }})
            </li>
        @endforeach
    </ul>

</div>

<script>
    "use strict";

    // Position the fixed dropdown under the button
    function positionLangMenu() {
        const btn = document.getElementById('langSwitcherBtn');
        const menu = document.getElementById('langMenu');
        if (!btn || !menu) return;
        const rect = btn.getBoundingClientRect();
        menu.style.top = (rect.bottom + 6) + 'px';
        menu.style.right = (window.innerWidth - rect.right) + 'px';
        menu.style.left = 'auto';
    }

    // Toggle open/close
    function toggleLangMenu(e) {
        e.stopPropagation();
        const btn = document.getElementById('langSwitcherBtn');
        const menu = document.getElementById('langMenu');
        if (!btn || !menu) return;

        const isOpen = menu.classList.contains('open');
        if (isOpen) {
            menu.classList.remove('open');
            btn.classList.remove('open');
        } else {
            positionLangMenu();
            menu.classList.add('open');
            btn.classList.add('open');
        }
    }

    // Select a language and reload
    function selectLang(locale, label) {
        const currentLang = document.getElementById('currentLang');
        if (currentLang) currentLang.textContent = label;

        document.getElementById('langMenu')?.classList.remove('open');
        document.getElementById('langSwitcherBtn')?.classList.remove('open');

        fetch("{{ config('app.url') }}/set-locale", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    locale: locale,
                    card_id: '{{ $business_card_details->card_id }}'
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to set locale');
                window.location.reload();
            })
            .catch(err => console.error('Locale switch error:', err));
    }

    // Close menu on outside click/tap
    document.addEventListener('click', function() {
        document.getElementById('langMenu')?.classList.remove('open');
        document.getElementById('langSwitcherBtn')?.classList.remove('open');
    });

    // Reposition on resize or scroll
    window.addEventListener('resize', function() {
        if (document.getElementById('langMenu')?.classList.contains('open')) positionLangMenu();
    });

    document.addEventListener('scroll', function() {
        if (document.getElementById('langMenu')?.classList.contains('open')) positionLangMenu();
    }, true);
</script>
