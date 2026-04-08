@extends('GoBizOriginal::Website.layouts.index', [
    'nav' => true,
    'banner' => false,
    'footer' => true,
    'cookie' => true,
    'setting' => true,
    'title' => __('Directory Listing'),
])

@php
    use App\Services\GoBizCommonService;

    $directory_settings = GoBizCommonService::directorySettings();
    $search = request('search', '');
    $location = request('location', '');
    $typeParam = request('type', 'all');
    $sort = request('sort', 'newest');
    $perPage = request('per_page', 9);
@endphp

@php
    $colors = [
        'slate' => '100,116,139',
        'gray' => '107,114,128',
        'zinc' => '113,113,122',
        'neutral' => '115,115,115',
        'stone' => '120,113,108',

        'red' => '239,68,68',
        'orange' => '249,115,22',
        'amber' => '245,158,11',
        'yellow' => '234,179,8',
        'lime' => '132,204,22',
        'green' => '34,197,94',
        'emerald' => '16,185,129',
        'teal' => '20,184,166',
        'cyan' => '6,182,212',
        'sky' => '14,165,233',
        'blue' => '59,130,246',
        'indigo' => '99,102,241',
        'violet' => '139,92,246',
        'purple' => '168,85,247',
        'fuchsia' => '217,70,239',
        'pink' => '236,72,153',
        'rose' => '244,63,94',
    ];

    $rgb = $colors[$template_config->template_color] ?? '30,64,175';
@endphp

{{-- Custom CSS --}}
@section('custom-script')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap');

        :root {
            --dir-ink: #0d0d12;
            --dir-white: #ffffff;
            --dir-accent: rgb({{ $rgb }});
            --dir-muted: #868590;
            --dir-border: #e2e0db;
            --dir-card-bg: #ffffff;
            --dir-radius: 14px;
            --dir-shadow: 0 2px 16px rgba(0, 0, 0, .07);
            --dir-shadow-h: 0 8px 32px rgba(0, 0, 0, .13);

            --dir-store-bg: rgba({{ $rgb }}, .2);
            --dir-vcard-bg: rgba({{ $rgb }}, .2);

            --dir-store-color: rgba({{ $rgb }}, .8);
            --dir-vcard-color: rgba({{ $rgb }}, .8);
        }

        .dir-wrap * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ── Hero ── */
        .dir-hero {
            background: var(--dir-ink);
            border-radius: 20px;
            padding: 48px 40px 44px;
            margin-bottom: 36px;
            position: relative;
            overflow: hidden;
        }

        .dir-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 90% at 80% -10%, #e8490f26 0%, transparent 60%),
                radial-gradient(ellipse 50% 60% at -10% 110%, #f5a62318 0%, transparent 55%);
            pointer-events: none;
        }

        .dir-hero-label {
            font-family: 'Syne', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--dir-accent);
            margin-bottom: 10px;
        }

        .dir-hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.6rem, 3.5vw, 2.6rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.18;
            margin-bottom: 28px;
        }

        .dir-hero h1 span {
            color: var(--dir-accent);
        }

        .dir-search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto auto auto;
            gap: 10px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .dir-search-form {
                grid-template-columns: 1fr 1fr;
            }

            .dir-search-form .dir-btn-search {
                grid-column: span 2;
            }
        }

        @media (max-width: 560px) {
            .dir-search-form {
                grid-template-columns: 1fr;
            }

            .dir-search-form .dir-btn-search {
                grid-column: span 1;
            }
        }

        .dir-field {
            position: relative;
        }

        .dir-field i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dir-muted);
            font-size: 13px;
            pointer-events: none;
        }

        .dir-field input,
        .dir-field select {
            width: 100%;
            padding: 12px 14px 12px 38px;
            background: rgba(255, 255, 255, .08);
            border: 1.5px solid rgba(255, 255, 255, .13);
            border-radius: 10px;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .dir-field input::placeholder {
            color: rgba(255, 255, 255, .4);
        }

        .dir-field input:focus,
        .dir-field select:focus {
            border-color: var(--dir-accent);
            background: rgba(255, 255, 255, .12);
        }

        .dir-field select option {
            background: #1a1a24;
            color: #fff;
        }

        .dir-btn-search {
            padding: 12px 28px;
            background: var(--dir-accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            white-space: nowrap;
            transition: background .2s, transform .15s;
        }

        .dir-btn-search:hover {
            background: #cf3a00;
            transform: translateY(-1px);
        }

        /* ── Toolbar ── */
        .dir-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }

        .dir-count {
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--dir-muted);
        }

        .dir-count strong {
            color: var(--dir-ink);
            font-weight: 600;
        }

        .dir-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .dir-select-wrap {
            position: relative;
        }

        .dir-select-wrap i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dir-muted);
            font-size: 12px;
            pointer-events: none;
        }

        .dir-select-wrap select {
            padding: 8px 28px 8px 30px;
            border: 1.5px solid var(--dir-border);
            border-radius: 8px;
            background: var(--dir-white);
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            color: var(--dir-ink);
            outline: none;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            transition: border-color .2s;
        }

        .dir-select-wrap::after {
            content: '▾';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dir-muted);
            font-size: 11px;
            pointer-events: none;
        }

        .dir-select-wrap select:focus {
            border-color: var(--dir-accent);
        }

        .dir-view-toggle {
            display: flex;
            border: 1.5px solid var(--dir-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .dir-view-btn {
            padding: 7px 11px;
            background: var(--dir-white);
            border: none;
            cursor: pointer;
            color: var(--dir-muted);
            font-size: 14px;
            transition: background .15s, color .15s;
        }

        .dir-view-btn.active,
        .dir-view-btn:hover {
            background: var(--dir-ink);
            color: #fff;
        }

        /* ── Filter chips ── */
        .dir-active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            min-height: 0;
        }

        .dir-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: var(--dir-vcard-bg);
            border: 1px solid var(--dir-white);
            border-radius: 100px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: var(--dir-accent);
        }

        .dir-filter-chip button {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dir-accent);
            font-size: 14px;
            line-height: 1;
            padding: 0;
            transition: opacity .2s;
        }

        .dir-filter-chip button:hover {
            opacity: .6;
        }

        /* ── Results zone ── */
        #dir-results {
            position: relative;
            min-height: 200px;
        }

        .dir-loading {
            position: absolute;
            inset: 0;
            z-index: 10;
            background: rgba(247, 246, 243, .8);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--dir-radius);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s;
        }

        .dir-loading.show {
            opacity: 1;
            pointer-events: all;
        }

        .dir-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--dir-border);
            border-top-color: var(--dir-accent);
            border-radius: 50%;
            animation: dir-spin .7s linear infinite;
        }

        @keyframes dir-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ── Cards grid ── */
        .dir-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .dir-grid.list-view {
            grid-template-columns: 1fr;
        }

        .dir-grid.list-view .dir-card {
            flex-direction: row;
        }

        .dir-grid.list-view .dir-cover {
            width: 180px;
            min-width: 180px;
            height: auto;
        }

        .dir-grid.list-view .dir-cover img {
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 600px) {
            .dir-grid.list-view .dir-card {
                flex-direction: column;
            }

            .dir-grid.list-view .dir-cover {
                width: 100%;
                min-width: unset;
                height: 160px;
            }
        }

        .dir-card {
            background: var(--dir-card-bg);
            border-radius: var(--dir-radius);
            border: 1.5px solid var(--dir-border);
            overflow: hidden;
            box-shadow: var(--dir-shadow);
            transition: box-shadow .25s, transform .25s, border-color .25s;
            display: flex;
            flex-direction: column;
            animation: dir-fadein .3s ease both;
        }

        .dir-card:hover {
            box-shadow: var(--dir-shadow-h);
            transform: translateY(-3px);
            border-color: #ddd;
        }

        @keyframes dir-fadein {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dir-cover {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            overflow: hidden;
        }

        .dir-cover img,
        .dir-cover iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: none;
        }

        .dir-card:hover .dir-cover img {
            transform: scale(1.04);
        }

        .dir-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .dir-meta-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .dir-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--dir-white);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
            flex-shrink: 0;
        }

        .dir-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 100px;
            font-family: 'Syne', sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .dir-badge.vcard {
            background: var(--dir-vcard-bg);
            color: var(--dir-vcard-color);
        }

        .dir-badge.store {
            background: var(--dir-store-bg);
            color: var(--dir-store-color);
        }

        .dir-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--dir-ink);
            margin-bottom: 6px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .dir-sub {
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            color: var(--dir-muted);
            line-height: 1.5;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 18px;
        }

        .dir-btn-view {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: var(--dir-ink);
            color: #fff;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            align-self: flex-start;
            transition: background .2s, transform .15s;
        }

        .dir-btn-view:hover {
            background: var(--dir-accent);
            transform: translateX(2px);
        }

        .dir-btn-view i {
            font-size: 11px;
        }

        /* ── Empty ── */
        .dir-empty {
            text-align: center;
            padding: 80px 20px;
        }

        .dir-empty-icon {
            font-size: 48px;
            color: var(--dir-border);
            margin-bottom: 16px;
        }

        .dir-empty h3 {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--dir-ink);
            margin-bottom: 8px;
        }

        .dir-empty p {
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--dir-muted);
        }

        .dir-empty a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 22px;
            background: var(--dir-ink);
            color: #fff;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
        }

        .dir-pagination {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        /* Stats row styles */
        .dir-stats-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }

        .dir-stat {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            color: var(--dir-muted);
            font-weight: 500;
        }

        .dir-stat i {
            font-size: 11px;
            color: var(--dir-accent);
            opacity: .8;
        }
    </style>
@endsection

@section('content')
    <section class="pt-8 pb-28">
        <div class="container mx-auto px-4">

            <div class="dir-wrap">

                {{-- ── HERO SEARCH ─────────────────────────────────────────────────── --}}
                <div class="dir-hero">
                    <p class="dir-hero-label">{{ __('Business Directory') }}</p>
                    <h1>{{ __('Discover') }} <span>{{ __('Local Businesses') }}</span><br>{{ __('& Professionals') }}</h1>

                    <form id="dir-search-form" class="dir-search-form">
                        <div class="dir-field">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" id="dir-input-search"
                                placeholder="{{ __('Name, keyword…') }}" value="{{ $search }}">
                        </div>
                        <div class="dir-field">
                            <i class="fas fa-location-dot"></i>
                            <input type="text" name="location" id="dir-input-location"
                                placeholder="{{ __('City, area…') }}" value="{{ $location }}">
                        </div>
                        <div class="dir-field">
                            <i class="fas fa-layer-group"></i>
                            <select name="type" id="dir-input-type">
                                <option value="all" {{ $typeParam == 'all' ? 'selected' : '' }}>{{ __('All Types') }}
                                </option>
                                <option value="vcard" {{ $typeParam == 'vcard' ? 'selected' : '' }}>{{ __('vCards') }}
                                </option>
                                <option value="store" {{ $typeParam == 'store' ? 'selected' : '' }}>{{ __('Stores') }}
                                </option>
                            </select>
                        </div>
                        <div class="dir-field">
                            <i class="fas fa-arrow-up-wide-short"></i>
                            <select name="sort" id="dir-input-sort">
                                <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>{{ __('Newest') }}
                                </option>
                                <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}
                                </option>
                                <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>{{ __('Name A–Z') }}
                                </option>
                                <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>
                                    {{ __('Name Z–A') }}
                                </option>
                                <option value="most_viewed" {{ $sort == 'most_viewed' ? 'selected' : '' }}>
                                    {{ __('Most Viewed') }}
                                </option>
                            </select>
                        </div>
                        <button type="submit" class="dir-btn-search">
                            <i class="fas fa-magnifying-glass" style="margin-right:6px"></i> {{ __('Search') }}
                        </button>
                    </form>
                </div>

                {{-- ── TOOLBAR ──────────────────────────────────────────────────────── --}}
                <div class="dir-toolbar">
                    <p class="dir-count" id="dir-count">{{ __('Loading…') }}</p>
                    <div class="dir-controls">
                        <div class="dir-select-wrap">
                            <i class="fas fa-arrow-up-wide-short"></i>
                            <select id="dir-ctrl-sort">
                                <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>{{ __('Newest') }}
                                </option>
                                <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>{{ __('Oldest') }}
                                </option>
                                <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>{{ __('Name A–Z') }}
                                </option>
                                <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>
                                    {{ __('Name Z–A') }}
                                </option>
                                <option value="most_viewed" {{ $sort == 'most_viewed' ? 'selected' : '' }}>
                                    {{ __('Most Viewed') }}
                                </option>
                            </select>
                        </div>
                        <div class="dir-select-wrap">
                            <i class="fas fa-grip-lines"></i>
                            <select id="dir-ctrl-per-page">
                                <option value="9" {{ $perPage == 9 ? 'selected' : '' }}>9 / {{ __('page') }}
                                </option>
                                <option value="18" {{ $perPage == 18 ? 'selected' : '' }}>18 / {{ __('page') }}
                                </option>
                                <option value="36" {{ $perPage == 36 ? 'selected' : '' }}>36 / {{ __('page') }}
                                </option>
                            </select>
                        </div>
                        <div class="dir-view-toggle">
                            <button class="dir-view-btn active" id="btn-grid" onclick="setView('grid')"
                                title="{{ __('Grid view') }}">
                                <i class="fas fa-grip"></i>
                            </button>
                            <button class="dir-view-btn" id="btn-list" onclick="setView('list')"
                                title="{{ __('List view') }}">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── ACTIVE FILTER CHIPS ─────────────────────────────────────────── --}}
                <div class="dir-active-filters" id="dir-chips"></div>

                {{-- ── RESULTS ZONE (populated by AJAX) ───────────────────────────── --}}
                <div id="dir-results">
                    <div class="dir-loading" id="dir-loading">
                        <div class="dir-spinner"></div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- Custom JS --}}
@section('custom-js')
    <script>
        (function() {
            'use strict';

            const AJAX_URL = '{{ url()->current() }}';
            const CSRF = '{{ csrf_token() }}';

            let state = {
                search: @json($search),
                location: @json($location),
                type: @json($typeParam),
                sort: @json($sort),
                per_page: {{ (int) $perPage }},
                page: {{ (int) request('page', 1) }},
            };

            const sortLabels = {
                newest: @json(__('Newest')),
                oldest: @json(__('Oldest')),
                name_asc: @json(__('Name A–Z')),
                name_desc: @json(__('Name Z–A')),
                most_viewed: @json(__('Most Viewed')),
            };

            const $results = document.getElementById('dir-results');
            const $loading = document.getElementById('dir-loading');
            const $count = document.getElementById('dir-count');
            const $chips = document.getElementById('dir-chips');
            const $form = document.getElementById('dir-search-form');
            const $ctrlSort = document.getElementById('dir-ctrl-sort');
            const $ctrlPer = document.getElementById('dir-ctrl-per-page');

            let abortCtrl = null;

            /* ── Fetch ── */
            function fetchListings(resetPage) {
                if (resetPage) state.page = 1;
                if (abortCtrl) abortCtrl.abort();
                abortCtrl = new AbortController();

                $loading.classList.add('show');

                const params = new URLSearchParams({
                    search: state.search,
                    location: state.location,
                    type: state.type,
                    sort: state.sort,
                    per_page: state.per_page,
                    page: state.page,
                });

                history.replaceState(null, '', '?' + params.toString());

                fetch(AJAX_URL + '?' + params.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                        },
                        signal: abortCtrl.signal,
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        $loading.classList.remove('show');
                        renderResults(data);
                        renderCount(data);
                        renderChips();
                    })
                    .catch(err => {
                        if (err.name !== 'AbortError') {
                            $loading.classList.remove('show');
                            console.error('Directory AJAX error:', err);
                        }
                    });
            }

            /* ── Render HTML partial ── */
            function renderResults(data) {
                /* Remove all previous content except loader */
                $results.querySelectorAll(':scope > *:not(#dir-loading)').forEach(el => el.remove());

                const frag = document.createRange().createContextualFragment(data.html);
                $results.appendChild(frag);

                /* Re-apply list-view if active */
                const newGrid = document.getElementById('dir-grid');
                if (newGrid && localStorage.getItem('dir-view') === 'list') {
                    newGrid.classList.add('list-view');
                }

                /* Wire AJAX pagination */
                $results.querySelectorAll('.dir-pagination a[href]').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const p = new URL(this.href).searchParams.get('page');
                        state.page = parseInt(p) || 1;
                        fetchListings(false);

                        $results.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                });
            }

            /* ── Count label ── */
            function renderCount(data) {
                if (!data.total) {
                    $count.innerHTML = @json(__('No listings found'));
                    return;
                }
                const from = ((state.page - 1) * state.per_page) + 1;
                const to = Math.min(state.page * state.per_page, data.total);
                $count.innerHTML =
                    @json(__('Showing')) + ' <strong>' + from + '–' + to + '</strong>' +
                    ' ' + @json(__('of')) + ' <strong>' + data.total + '</strong> ' +
                    @json(__('listings'));
            }

            /* ── Filter chips ── */
            function renderChips() {
                $chips.innerHTML = '';

                const addChip = (iconClass, label, clearKey, clearVal) => {
                    const chip = document.createElement('span');
                    chip.className = 'dir-filter-chip';
                    chip.innerHTML =
                        '<i class="fas fa-' + iconClass + '" style="font-size:10px"></i>' +
                        document.createTextNode(label).textContent +
                        '<button title="' + @json(__('Remove')) + '">&times;</button>';
                    chip.querySelector('button').addEventListener('click', () => {
                        state[clearKey] = clearVal;
                        syncControls();
                        fetchListings(true);
                    });
                    $chips.appendChild(chip);
                };

                if (state.search)
                    addChip('search', state.search, 'search', '');
                if (state.location)
                    addChip('location-dot', state.location, 'location', '');
                if (state.type && state.type !== 'all')
                    addChip('layer-group', state.type, 'type', 'all');
                if (state.sort && state.sort !== 'newest')
                    addChip('arrow-up-wide-short', sortLabels[state.sort] || state.sort, 'sort', 'newest');
            }

            /* ── Sync UI ↔ state ── */
            function syncControls() {
                document.getElementById('dir-input-search').value = state.search;
                document.getElementById('dir-input-location').value = state.location;
                document.getElementById('dir-input-type').value = state.type;
                document.getElementById('dir-input-sort').value = state.sort;
                $ctrlSort.value = state.sort;
                $ctrlPer.value = state.per_page;
            }

            /* ── Events ── */
            $form.addEventListener('submit', function(e) {
                e.preventDefault();
                state.search = document.getElementById('dir-input-search').value.trim();
                state.location = document.getElementById('dir-input-location').value.trim();
                state.type = document.getElementById('dir-input-type').value;
                state.sort = document.getElementById('dir-input-sort').value;
                $ctrlSort.value = state.sort;
                fetchListings(true);
            });

            $ctrlSort.addEventListener('change', function() {
                state.sort = this.value;
                document.getElementById('dir-input-sort').value = this.value;
                fetchListings(true);
            });

            $ctrlPer.addEventListener('change', function() {
                state.per_page = parseInt(this.value);
                fetchListings(true);
            });

            /* ── View toggle ── */
            window.setView = function(mode) {
                const grid = document.getElementById('dir-grid');
                const btnG = document.getElementById('btn-grid');
                const btnL = document.getElementById('btn-list');
                if (mode === 'list') {
                    if (grid) grid.classList.add('list-view');
                    if (btnL) {
                        btnL.classList.add('active');
                        btnG.classList.remove('active');
                    }
                } else {
                    if (grid) grid.classList.remove('list-view');
                    if (btnG) {
                        btnG.classList.add('active');
                        btnL.classList.remove('active');
                    }
                }
                localStorage.setItem('dir-view', mode);
            };

            /* ── Init ── */
            document.addEventListener('DOMContentLoaded', function() {
                if (localStorage.getItem('dir-view') === 'list') setView('list');
                fetchListings(false);
            });

        })();
    </script>
@endsection
@endsection
