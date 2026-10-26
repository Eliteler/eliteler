@props(['nfc_ref', 'plan_assign_type'])

@php

@endphp

{{-- NFC Modal --}}
<div class="modal modal-bg fade" id="nfcModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-status"></div>
            <div class="modal-body text-center py-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="currentColor"
                    class="icon mb-3 text-success icon-lg icon-tabler icons-tabler-filled icon-tabler-gift">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M11 14v8h-4a3 3 0 0 1 -3 -3v-4a1 1 0 0 1 1 -1h6zm8 0a1 1 0 0 1 1 1v4a3 3 0 0 1 -3 3h-4v-8h6zm-2.5 -12a3.5 3.5 0 0 1 3.163 5h.337a2 2 0 0 1 2 2v1a2 2 0 0 1 -2 2h-7v-5h-2v5h-7a2 2 0 0 1 -2 -2v-1a2 2 0 0 1 2 -2h.337a3.486 3.486 0 0 1 -.337 -1.5c0 -1.933 1.567 -3.5 3.483 -3.5c1.755 -.03 3.312 1.092 4.381 2.934l.136 .243c1.033 -1.914 2.56 -3.114 4.291 -3.175l.209 -.002zm-9 2a1.5 1.5 0 0 0 0 3h3.143c-.741 -1.905 -1.949 -3.02 -3.143 -3zm8.983 0c-1.18 -.02 -2.385 1.096 -3.126 3h3.143a1.5 1.5 0 1 0 -.017 -3z" />
                </svg>

                <h3>{{ $plan_assign_type == 'new' ? __('Activate Your Included Plan') : __('Overwrite Your Current Plan') }}
                </h3>

                <div class="text-muted fs-4">
                    {{ $plan_assign_type == 'new'
                        ? __('Your NFC card purchase includes a subscription plan. Activate it now to link the plan to your account.')
                        : __(
                            'Your NFC card purchase includes a subscription plan. You already have an active subscription. Are you sure you want to overwrite your current plan?',
                        ) }}
                </div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                        <div class="col">
                            @if ($plan_assign_type == 'new')
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            @else
                                <a data-method="post" class="btn btn-secondary w-100"
                                    href="{{ route('user.activate.nfc.plan', [
                                        'nfc_ref' => $nfc_ref,
                                        'status' => 'cancel',
                                    ]) }}">
                                    {{ __('Cancel') }}
                                </a>
                            @endif
                        </div>
                        <div class="col">
                            <a data-method="post" class="btn btn-success w-100" href="{{ route('user.activate.nfc.plan', $nfc_ref) }}">
                                {{ $plan_assign_type == 'new' ? __('Activate') : __('Yes, Overwrite') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('nfcModal'));
        modal.show();

        setTimeout(() => {
            confetti({
                particleCount: 300,
                spread: 900,
                zIndex: 99999,
                colors: ['#f1c40f', '#f39c12', '#e67e22', '#e74c3c', '#9b59b6', '#8e44ad',
                    '#7f8c8d'
                ],
            });
        }, 1000);
    });
</script>
