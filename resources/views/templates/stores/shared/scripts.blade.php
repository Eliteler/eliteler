{{--
    The shared storefront script (cart, product options, WhatsApp checkout) and its settings.
    Include once, at the end of <body>, after the theme's cart partial.
    Params: $hooks  optional JS object literal with theme hooks, e.g. "{ notify: (message, type) => myToast(message, type) }"
--}}
<script src="{{ asset('js/store/storefront.js') }}?v={{ filemtime(public_path('js/store/storefront.js')) }}"></script>
<script>
    GoBizStore.init(@json($storefront->scriptConfig()), {!! $hooks ?? '{}' !!});
</script>
