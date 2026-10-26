{{--
    Cosmetics theme scripts, at the end of <body>: Tabler, sliders, then the shared storefront
    (cart, product options, WhatsApp checkout).
    Params: $lightbox (bool) — load the image lightbox (product page)
--}}
<script src="{{ url('js/tabler.min.js') }}"></script>
<script src="{{ url('js/script.js') }}"></script>
<script src="{{ url('js/data-filter.js') }}"></script>
<script src="{{ url('js/swiper-element-bundle.min.js') }}"></script>
@if ($lightbox ?? false)
    <script src="{{ url('js/fslightbox.js') }}"></script>
@endif

@include('templates.stores.shared.scripts')