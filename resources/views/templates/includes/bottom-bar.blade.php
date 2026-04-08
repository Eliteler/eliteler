<nav
    class="navbar-2 border navbar-expand d-print-none p-3 d-lg-none {{ $bg == 'dark' ? 'bg-dark' : 'bg-white' }} fixed-bottom">
    <ul class="navbar-nav nav-justified d-flex justify-content-between w-100 px-5">
        <li class="d-flex justify-content-center"><a
                class="d-flex flex-column justify-content-center align-items-center {{ request()->is($business_card_details->card_url) && ($bg == 'dark' || $bg == 'light') ? 'text-' . $color : ($bg == 'dark' ? 'text-white' : 'text-dark') }}"
                href="{{ config('app.url') }}/{{ $business_card_details->card_url }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                </svg><span class="mt-1 fs-4">{{ __('Home') }}</span>
            </a>
        </li>
        <li class="d-flex justify-content-center"><a
                class="d-flex flex-column justify-content-center align-items-center {{ request()->is($business_card_details->card_url . '/categories') && ($bg == 'dark' || $bg == 'light') ? 'text-' . $color : ($bg == 'dark' ? 'text-white' : 'text-dark') }}"
                href="{{ config('app.url') }}/{{ $business_card_details->card_url }}/categories">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 4h6v6h-6z" />
                    <path d="M14 4h6v6h-6z" />
                    <path d="M4 14h6v6h-6z" />
                    <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                </svg><span class="mt-1 fs-4">{{ __('Categories') }}</span>
            </a>
        </li>
        <li class="d-flex justify-content-center"><a
                class="d-flex flex-column justify-content-center align-items-center {{ $bg == 'dark' ? 'text-white' : 'text-dark' }}"
                data-bs-toggle="offcanvas" href="#offcanvasEnd" role="button" aria-controls="offcanvasEnd">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" />
                    <path d="M9 11v-5a3 3 0 0 1 6 0v5" />
                </svg><span class="mt-1 fs-4">{{ __('Cart') }}</span>
            </a>
        </li>
    </ul>
</nav>
