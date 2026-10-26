{{--
    Digital products theme <head> (Tailwind build in digital-store.css): shared meta/SEO/PWA, fonts, icons,
    GSAP for the animations, and the saved light/dark choice applied before the page paints.
    Pages add their own <style> after it, then @include('templates.stores.shared.custom-code').
--}}
@include('templates.stores.shared.head', ['themeColor' => '#4f46e5'])

<script>
    // Saved light/dark choice (set by toggleTheme in includes/scripts.blade.php)
    try {
        if (localStorage.getItem('theme') === 'light') document.documentElement.classList.remove('dark');
    } catch (error) {}
</script>

<link href="{{ url('templates/css/store/digital-store.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ url('css/swiper-bundle.min.css') }}" />
<link rel="stylesheet" href="{{ url('css/tabler-icons.min.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="{{ url('js/gsap.min.js') }}"></script>

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
</style>
