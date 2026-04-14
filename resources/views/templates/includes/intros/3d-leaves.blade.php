<!-- Cover Leaf Bg -->
<div id="leaf-bg" class="fixed w-screen h-screen z-[998] lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-bg.png') }}" class="w-full h-full object-cover" alt="">
</div>

<!-- Text -->
<div id="index" class="fixed inset-0 flex items-end justify-center z-[9999] lg:hidden mb-14">
    <div class="text-center text-white">
        <svg id="index-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28"
            fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
            class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-up text-center w-full shine-text mb-3">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M6 15l6 -6l6 6" />
        </svg>
        <a href="#content-screen" id="index-text"
            class="text-lg font-medium text-center inline-block bg-[#000000]/10 border backdrop-blur-md rounded-full px-10 py-2">
            <span class="shine-text">{{ __('Swipe to Continue') }}</span>
        </a>
    </div>
</div>

<!-- Audio Icon -->
<div id="audio-toggle" class="fixed flex items-end justify-center top-4 right-4 z-[9999] lg:hidden cursor-pointer">
    <audio id="bg-audio" src="{{ asset('templates/audio/spa.mp3') }}" loop></audio>
    <div
        class="text-white flex justify-center items-center bg-[#000000]/10 border backdrop-blur-md rounded-xl h-10 w-10">
        <!-- Default: Volume On -->
        <svg id="icon-volume-on" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="icon icon-tabler icon-tabler-volume">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M15 8a5 5 0 0 1 0 8" />
            <path d="M17.7 5a9 9 0 0 1 0 14" />
            <path
                d="M6 15h-2a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2l3.5 -4.5a.8 .8 0 0 1 1.5 .5v14a.8 .8 0 0 1 -1.5 .5l-3.5 -4.5" />
        </svg>

        <!-- Hidden: Volume Off -->
        <svg id="icon-volume-off" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="hidden icon icon-tabler icon-tabler-volume-off">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M15 8a5 5 0 0 1 1.9 5m-1.4 2.6a5 5 0 0 1 -.5 .4" />
            <path d="M17.7 5a9 9 0 0 1 2.3 11.1m-1.7 2.3a9 9 0 0 1 -.7 .6" />
            <path
                d="M9.1 5.1l.4 -.6a.8 .8 0 0 1 1.5 .5v2m0 4v8a.8 .8 0 0 1 -1.5 .5l-3.5 -4.5h-2a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2l1.3 -1.7" />
            <path d="M3 3l18 18" />
        </svg>
    </div>
</div>

<!-- Top left leaf -->
<div id="leaf-tl" class="fixed flex justify-center items-end z-[999] -top-24 -left-24 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-1.png') }}" class="w-10/12 h-auto" alt="">
</div>

{{-- Top right leaf --}}
<div id="leaf-tr" class="fixed flex justify-end items-end z-[998] -top-28 -right-14 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-2.png') }}" class="w-12/12 h-auto" alt="">
</div>

<!-- Top right 2 leaf -->
<div id="leaf-tr2" class="fixed flex justify-center items-end z-[999] -top-28 -right-20 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-3.png') }}" class="w-7/12 h-auto" alt="">
</div>

<!-- left center leaf -->
<div id="leaf-lc" class="fixed flex justify-center items-end z-[999] bottom-24 -left-72 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-4.png') }}" class="w-7/12 h-auto" alt="">
</div>

<!-- left center 2 leaf -->
<div id="leaf-lc2" class="fixed flex justify-center items-end z-[998] top-8 -left-16 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-5.png') }}" class="w-10/12 h-auto" alt="">
</div>

<!-- right center leaf -->
<div id="leaf-rc" class="fixed flex justify-center items-end z-[999] top-32 -right-72 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-6.png') }}" class="w-7/12 h-auto" alt="">
</div>

<!-- Bottom left leaf -->
<div id="leaf-bl" class="fixed flex justify-center items-end z-[999] -bottom-32 -left-24 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-7.png') }}" class="w-11/12 h-auto" alt="">
</div>

<!-- Bottom right leaf -->
<div id="leaf-br" class="fixed flex justify-center items-end z-[998] bottom-36 -right-16 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-8.png') }}" class="w-10/12 h-auto" alt="">
</div>

<!-- Bottom right 2 leaf -->
<div id="leaf-br2" class="fixed flex justify-center items-end z-[998] -bottom-28 -right-16 lg:hidden">
    <img src="{{ asset('img/templates/modern/leaf-9.png') }}" class="w-12/12 h-auto" alt="">
</div>

{{-- Empty space for scroll --}}
<div id="empty-window" class="min-h-screen bg-transparent lg:hidden"></div>

{{-- Animation --}}
@if ($theme !== '588969111160')
    <script src="{{ url('js/gobiz-animation.min.js') }}"></script>
    <script src="{{ url('js/gobiz-animation-scrolltrigger.min.js') }}"></script>
@endif

<script src="{{ url('templates/js/modern-animation.js') }}"></script>
