{{--
    Modern store popups (home page): newsletter sign-up and the owner's information popup.
--}}
{{-- Newsletter Popup --}}
@if ($business_card_details != null)
    @if (
        !empty($business_card_details->is_newsletter_pop_active) &&
            $business_card_details->is_newsletter_pop_active == 1 &&
            !request()->cookie('newsletter_' . $business_card_details->card_id))
        <div class="modal fade" id="newsletterModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title fs-2">{{ __('Subscribe to our newsletter') }}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form action="{{ url('subscribe/store/newsletter') }}" method="post">
                            @csrf
                            <div class="row g-2">
                                <input type="hidden" id="card_id" name="card_id"
                                    value="{{ $business_card_details->card_id }}">
                                <div class="col-8">
                                    <input type="text" class="form-control" id="newsletter_email"
                                        placeholder="{{ __('Your email address') }}" name="email" required />
                                </div>
                                <div class="col-4">
                                    <button type="submit"
                                        class="btn btn-{{ $storeTheme->color }} w-100">{{ __('Subscribe') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

{{-- Information Popup --}}
@if ($business_card_details != null)
    @if (!empty($business_card_details->is_info_pop_active) && $business_card_details->is_info_pop_active == 1)
        @php
            $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();

            $confetti_effect = $information_pop->confetti_effect ?? 0;
            $img = $information_pop->info_pop_image ?? '';
            $title = $information_pop->info_pop_title ?? '';
            $desc = $information_pop->info_pop_desc ?? '';
            $button_text = $information_pop->info_pop_button_text ?? '';
            $button_url = $information_pop->info_pop_button_url ?? '#';
        @endphp

        <div class="modal fade" id="informationModal" tabindex="-1" aria-hidden="true"
            data-bs-backdrop="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-end align-items-center">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="row d-flex justify-content-center align-items-center">
                            @if (!empty($img))
                                <div class="w-25 h-25 mb-3 text-center">
                                    <img src="{{ url($img) }}" alt="{{ $title }}"
                                        class="img-fluid object-fit-contain rounded-4" />
                                </div>
                            @endif
                            <div class="mb-3">
                                <h2 class="text-center mb-1">{{ __($title) }}</h2>
                                <p class="text-center mb-1 text-muted">{{ __($desc) }}</p>
                            </div>
                            <div class="d-flex justify-content-center align-items-center">
                                <a href="{{ $button_url }}" target="_blank"
                                    class="btn btn-{{ $storeTheme->color }} w-100">{{ __($button_text) }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($confetti_effect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        @endif
    @endif
@endif

{{-- Newsletter first, then the information popup (with confetti when enabled) --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        "use strict";

        const newsletterEl = document.getElementById('newsletterModal');
        const infoEl = document.getElementById('informationModal');

        let newsletterModal = newsletterEl ? new bootstrap.Modal(newsletterEl, {
            backdrop: true,
            keyboard: false
        }) : null;
        let infoModal = infoEl ? new bootstrap.Modal(infoEl, {
            backdrop: true,
            keyboard: false
        }) : null;

        function triggerConfetti() {
            if (typeof confetti === 'function' &&
                {{ isset($confetti_effect) && $confetti_effect == 1 ? 'true' : 'false' }}) {
                confetti({
                    particleCount: window.innerWidth > 768 ? 150 : 80,
                    spread: 100,
                    origin: {
                        y: 0.6
                    }
                });
            }
        }

        // Show Newsletter first if active
        if (newsletterModal) {
            setTimeout(() => {
                newsletterModal.show();
            }, 2000);

            // Show Information modal only after Newsletter is closed
            newsletterEl.addEventListener('hidden.bs.modal', function() {
                if (infoModal) {
                    setTimeout(() => {
                        infoModal.show();
                        triggerConfetti();
                    }, 800);
                }
            });
        } else if (infoModal) {
            // If Newsletter is disabled, open Information modal directly
            setTimeout(() => {
                infoModal.show();
                triggerConfetti();
            }, 2500);
        }
    });
</script>
