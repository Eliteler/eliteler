{{--
    "Our Work" section: live cards the admin marked as examples
    (Admin > Digital Business Cards > ⋯ > Show in "Our Work").
    Hidden when no card is marked. Data: showcase_cards() in app/helpers.php.
--}}
@php
    $showcase = showcase_cards(12);
    $owAccents = ['red' => '#dc2626', 'orange' => '#ea580c', 'amber' => '#d97706', 'yellow' => '#ca8a04', 'lime' => '#65a30d', 'green' => '#16a34a', 'emerald' => '#059669', 'teal' => '#0d9488', 'cyan' => '#0891b2', 'sky' => '#0284c7', 'blue' => '#2563eb', 'indigo' => '#4f46e5', 'violet' => '#7c3aed', 'purple' => '#9333ea', 'fuchsia' => '#c026d3', 'pink' => '#db2777', 'rose' => '#e11d48', 'gray' => '#4b5563'];
    $owAccent = $owAccents[$template_config->template_color ?? ''] ?? '#2563eb';
@endphp

@if (count($showcase) > 0)
    <style>
        .ow-section { padding: 6rem 0 3rem; overflow: hidden; }
        .ow-kicker { margin-bottom: 1.25rem; font-weight: 600; font-size: .75rem; letter-spacing: .1em; text-transform: uppercase; text-align: center; color: #9ca3af; }
        .ow-title { margin-bottom: 1.25rem; font-weight: 600; font-size: 2.5rem; line-height: 1.15; text-align: center; color: #111827; overflow-wrap: break-word; }
        .ow-sub { max-width: 40rem; margin: 0 auto 3.5rem; text-align: center; color: #6b7280; line-height: 1.8; }
        .ow-grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1.5rem; }
        .ow-card { display: flex; flex-direction: column; background: #fff; border: 1px solid #e5e7eb; border-radius: 1.25rem; overflow: hidden; text-decoration: none; color: inherit; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .ow-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px -18px rgba(17, 24, 39, .35); border-color: var(--ow-accent); }
        .ow-cover { height: 9rem; background: linear-gradient(135deg, var(--ow-accent), #111827) center / cover no-repeat; }
        .ow-body { display: flex; flex-direction: column; align-items: center; padding: 0 1.25rem 1.5rem; text-align: center; flex: 1; }
        .ow-logo { width: 5.5rem; height: 5.5rem; margin-top: -2.75rem; border-radius: 9999px; border: 4px solid #fff; background: #f3f4f6; object-fit: cover; box-shadow: 0 6px 16px -6px rgba(17, 24, 39, .4); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 700; color: var(--ow-accent); }
        .ow-name { margin-top: .9rem; font-weight: 600; font-size: 1.125rem; color: #111827; overflow-wrap: anywhere; }
        .ow-role { margin-top: .25rem; font-size: .875rem; color: #6b7280; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ow-btn { margin-top: auto; padding-top: 1.1rem; }
        .ow-btn span { display: inline-block; padding: .55rem 1.25rem; border-radius: 9999px; font-size: .875rem; font-weight: 600; color: #fff; background: var(--ow-accent); }
        @media (min-width: 640px) { .ow-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .ow-title { font-size: 3.25rem; } }
        @media (min-width: 1024px) { .ow-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } .ow-section { padding-left: 6rem; padding-right: 6rem; } }
        @media (min-width: 1280px) { .ow-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    </style>

    <section class="ow-section" id="our-work" style="--ow-accent: {{ $owAccent }};">
        <div class="container mx-auto px-4">
            <p class="ow-kicker">{{ __('Our Work') }}</p>
            <h2 class="ow-title font-heading">{{ __('Digital cards we have created') }}</h2>
            <p class="ow-sub">{{ __('Real examples made for our clients. Open any card to see it live.') }}</p>

            <div class="ow-grid">
                @foreach ($showcase as $item)
                    <a class="ow-card" href="{{ $item->url }}" target="_blank" rel="noopener">
                        <div class="ow-cover" @if ($item->cover) style="background-image: url('{{ $item->cover }}');" @endif></div>
                        <div class="ow-body">
                            @if ($item->logo)
                                <img class="ow-logo" src="{{ $item->logo }}" alt="{{ $item->title }}" loading="lazy">
                            @else
                                <div class="ow-logo">{{ mb_strtoupper(mb_substr($item->title, 0, 1)) }}</div>
                            @endif
                            <h3 class="ow-name font-heading">{{ $item->title }}</h3>
                            @if ($item->subtitle)
                                <p class="ow-role">{{ $item->subtitle }}</p>
                            @endif
                            <div class="ow-btn"><span>{{ __('View card') }}</span></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
