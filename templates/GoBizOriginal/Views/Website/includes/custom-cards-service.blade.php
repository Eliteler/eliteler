{{-- "Custom Cards" service section on the website home page. Texts are translatable (resources/lang/*.json). --}}
@php
    $ccAccents = ['red' => '#dc2626', 'orange' => '#ea580c', 'amber' => '#d97706', 'yellow' => '#ca8a04', 'lime' => '#65a30d', 'green' => '#16a34a', 'emerald' => '#059669', 'teal' => '#0d9488', 'cyan' => '#0891b2', 'sky' => '#0284c7', 'blue' => '#2563eb', 'indigo' => '#4f46e5', 'violet' => '#7c3aed', 'purple' => '#9333ea', 'fuchsia' => '#c026d3', 'pink' => '#db2777', 'rose' => '#e11d48', 'gray' => '#4b5563'];
    $ccAccent = $ccAccents[$template_config->template_color ?? ''] ?? '#2563eb';
    $ccFeatures = [
        ['ti ti-palette', 'Your brand colors', 'Background, buttons, titles and icons in your own colors.'],
        ['ti ti-typography', 'Arabic and Latin fonts', 'Pick the font and size of every title, aligned right, center or left.'],
        ['ti ti-layout', 'Your own layout', 'Profile layout, image style, card corners and bottom bar the way you want.'],
        ['ti ti-apps', 'Everything in one card', 'Services, products, gallery, testimonials, booking and WhatsApp contact.'],
        ['ti ti-qrcode', 'QR code ready to print', 'A QR code that opens your card directly, with your card name on it.'],
        ['ti ti-headset', 'We set it up for you', 'Send us your details and we deliver the card ready to share.'],
    ];
@endphp

<style>
    .cc-section { padding: 5rem 0 3rem; overflow: hidden; }
    .cc-box { border-radius: 1.75rem; padding: 3rem 1.5rem; background: linear-gradient(135deg, #111827, #1f2937); color: #fff; position: relative; overflow: hidden; }
    .cc-box::after { content: ""; position: absolute; width: 22rem; height: 22rem; border-radius: 9999px; background: var(--cc-accent); opacity: .22; filter: blur(60px); top: -8rem; inset-inline-end: -6rem; pointer-events: none; }
    .cc-kicker { margin-bottom: 1rem; font-weight: 600; font-size: .75rem; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; }
    .cc-title { font-weight: 600; font-size: 2.25rem; line-height: 1.2; margin-bottom: 1rem; overflow-wrap: break-word; }
    .cc-sub { max-width: 42rem; color: #d1d5db; line-height: 1.9; margin-bottom: 2.5rem; }
    .cc-grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; position: relative; z-index: 1; }
    .cc-item { display: flex; gap: .9rem; align-items: flex-start; padding: 1.1rem; border-radius: 1rem; background: rgba(255, 255, 255, .06); border: 1px solid rgba(255, 255, 255, .08); }
    .cc-icon { flex: 0 0 2.75rem; width: 2.75rem; height: 2.75rem; border-radius: .85rem; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; background: var(--cc-accent); color: #fff; }
    .cc-icon i { font-size: 1.5rem !important; line-height: 1; }
    .cc-item h3 { font-weight: 600; font-size: 1.05rem; margin-bottom: .25rem; }
    .cc-item p { font-size: .9rem; line-height: 1.7; color: #d1d5db; }
    .cc-cta { display: inline-block; margin-top: 2.25rem; padding: .85rem 1.75rem; border-radius: 9999px; font-weight: 600; background: var(--cc-accent); color: #fff; text-decoration: none; position: relative; z-index: 1; transition: transform .2s ease, opacity .2s ease; }
    .cc-cta:hover { transform: translateY(-2px); opacity: .92; color: #fff; }
    @media (min-width: 640px) { .cc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .cc-title { font-size: 3rem; } .cc-box { padding: 3.5rem 2.5rem; } }
    @media (min-width: 1024px) { .cc-section { padding-left: 6rem; padding-right: 6rem; } .cc-box { padding: 4rem; } }
    @media (min-width: 1280px) { .cc-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
</style>

<section class="cc-section" id="custom-cards" style="--cc-accent: {{ $ccAccent }};">
    <div class="container mx-auto px-4">
        <div class="cc-box">
            <p class="cc-kicker">{{ __('Custom Cards') }}</p>
            <h2 class="cc-title font-heading">{{ __('A digital card designed just for your brand') }}</h2>
            <p class="cc-sub">{{ __('Beyond the ready-made themes, we build a custom digital card that matches your identity, in Arabic and English.') }}</p>

            <div class="cc-grid">
                @foreach ($ccFeatures as [$icon, $heading, $text])
                    <div class="cc-item">
                        <div class="cc-icon"><i class="{{ $icon }}"></i></div>
                        <div>
                            <h3 class="font-heading">{{ __($heading) }}</h3>
                            <p>{{ __($text) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (Route::has('contact'))
                <a class="cc-cta" href="{{ route('contact') }}">{{ __('Request a custom card') }}</a>
            @endif
        </div>
    </div>
</section>
