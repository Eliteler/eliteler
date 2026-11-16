{{-- Shown from the Analytics menu item when the subscribed plan has no visitor analytics --}}
<div class="modal modal-blur fade" id="upgradeAnalyticsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            <div class="modal-body text-center py-4">
                <div class="avatar avatar-lg bg-primary-lt mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="28" height="28"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                        <path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                        <path d="M9 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                        <path d="M4 20h14" />
                    </svg>
                </div>
                <h3 class="fw-bold">{{ __('Unlock Visitor Analytics') }}</h3>
                <div class="text-muted">
                    {{ __('See where your visitors come from: country and city, browser, device, and whether they found you through search, social media or a QR code.') }}
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <div class="w-100">
                    <div class="row g-2">
                        <div class="col">
                            <button type="button" class="btn w-100"
                                data-bs-dismiss="modal">{{ __('Not now') }}</button>
                        </div>
                        <div class="col">
                            <a href="{{ route('user.plans') }}" class="btn btn-primary w-100">{{ __('Upgrade Plan') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
