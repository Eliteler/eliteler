<div
    class="fixed w-11/12 left-1/2 bottom-4 bg-green-200/40 border border-green-600 rounded-2xl backdrop-blur-md py-4 px-3 flex lg:hidden md:hidden transform -translate-x-1/2 z-50">
    <!-- Profile Icon -->
    <div class="flex-1 flex items-center justify-center">
        <a class="border border-green-600 p-3 rounded-2xl bg-green-200" href="#profile">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-user text-green-800 h-6 w-6">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
            </svg>
        </a>
    </div>

    <!-- Send Icon -->
    <div class="flex-1 flex items-center justify-center">
        <button class="border border-green-600 p-3 rounded-2xl bg-green-200" onclick="toggleWhatsAppModal(true)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-send text-green-800 h-6 w-6">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 14l11 -11" />
                <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
            </svg>
        </button>
    </div>

    <!-- Download Icon -->
    <div class="flex-1 flex items-center justify-center">
        <a href="{{ config('app.url') }}/download/{{ $business_card_details->card_id }}"
            class="border border-green-600 p-3 rounded-2xl bg-green-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-download text-green-800 h-6 w-6">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                <path d="M7 11l5 5l5 -5" />
                <path d="M12 4l0 12" />
            </svg>
        </a>
    </div>

    <!-- Scan Icon -->
    <div class="flex-1 flex items-center justify-center">
        <button class="border border-green-600 p-3 rounded-2xl bg-green-200" onclick="toggleScanModal(true)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-line-scan text-green-800 h-6 w-6">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 8v-2a2 2 0 0 1 2 -2h2" />
                <path d="M4 16v2a2 2 0 0 0 2 2h2" />
                <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                <path d="M16 20h2a2 2 0 0 0 2 -2v-2" />
                <path d="M7 12h10" />
            </svg>
        </button>
    </div>

    <!-- Share Icon -->
    <div class="flex-1 flex items-center justify-center">
        <button class="border border-green-600 p-3 rounded-2xl bg-green-200" onclick="shareToggleModal(true)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="icon icon-tabler icons-tabler-outline icon-tabler-share text-green-800 h-6 w-6">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M8.7 10.7l6.6 -3.4" />
                <path d="M8.7 13.3l6.6 3.4" />
            </svg>
        </button>
    </div>
</div>
