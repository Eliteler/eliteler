{{--
    Toy store <head>: shared meta/SEO/PWA, Tabler, the theme stylesheet (toy-store.css) and fonts.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#d63939'])

<link href="{{ url('css/tabler.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="{{ url('templates/css/store/toy-store.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

{{-- jQuery: the language switcher in the header uses it as soon as it renders --}}
<script src="{{ url('js/jquery.min.js') }}"></script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
        letter-spacing: -0.4px;
    }

    .head {
        font-family: 'Baloo 2', cursive;
        letter-spacing: 1px;
    }
</style>
