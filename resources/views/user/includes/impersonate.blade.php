<div class="container-fluid d-print-none bg-primary-lt">
    <div class="container-xl">
        <div class="d-flex align-items-center p-3 gap-3">

            <p class="m-0 fw-semibold flex-fill">
                {!! __(
                    'You are currently impersonating a <span class="fw-bold text-danger">:user admin</span> account. Click the button to go back to the admin dashboard.',
                    ['user' => Auth::user()->name]
                ) !!}
            </p>

            <a href="{{ route('admin.impersonate.logout') }}"
               class="btn btn-danger btn-icon flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2"/>
                    <path d="M15 12h-12l3 -3"/>
                    <path d="M6 15l-3 -3"/>
                </svg>
            </a>

        </div>
    </div>
</div>
