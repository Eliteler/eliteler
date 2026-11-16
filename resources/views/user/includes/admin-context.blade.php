<div class="container-fluid d-print-none bg-warning-lt">
    <div class="container-xl">
        <div class="d-flex align-items-center p-3 gap-3">

            <p class="m-0 fw-semibold flex-fill">
                {!! __(
                    'You are editing the cards of <span class="fw-bold text-danger">:user</span> as administrator. Click the button to return to the admin dashboard.',
                    ['user' => e(Auth::user()->name)]
                ) !!}
            </p>

            <a href="{{ route('admin.cards.leave') }}" class="btn btn-danger flex-shrink-0">
                {{ __('Back to admin') }}
            </a>

        </div>
    </div>
</div>
