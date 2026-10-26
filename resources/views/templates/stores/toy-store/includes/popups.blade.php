{{-- Toy store popups (home page): newsletter sign-up and the owner's information popup --}}
{{-- Newsletter Popup --}}
@if ($business_card_details != null)
    {{-- Check Newsletter --}}
    @if (
        !empty($business_card_details->is_newsletter_pop_active) &&
            $business_card_details->is_newsletter_pop_active == 1 &&
            !request()->cookie('newsletter_' . $business_card_details->card_id) &&
            empty(request()->getQueryString()))
        <div class="modal fade" id="newsletterModal" tabindex="-1" aria-hidden="true"
            style="background: rgba(0, 0, 0, 0.7);">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h3 class="modal-title fs-2">{{ __('Subscribe to our newsletter') }}</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <form action="{{ url('subscribe/store/newsletter') }}" method="post">
                            @csrf
                            <div class="row">
                                <input type="hidden" id="card_id" name="card_id"
                                    value="{{ $business_card_details->card_id }}">
                                <!-- Full Name -->
                                <div class="col-8">
                                    <input type="text" class="form-control" id="newsletter_email"
                                        placeholder="{{ __('Your email address') }}" name="email" required />
                                </div>
                                <!-- Button -->
                                <div class="col-4">
                                    <button type="submit"
                                        class="btn btn-red w-100">{{ __('Subscribe') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auto Open Modal --}}
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                "use strict";

                const newsletterModal = new bootstrap.Modal(document.getElementById('newsletterModal'), {
                    keyboard: false
                });

                // Open after 3 seconds
                setTimeout(() => {
                    newsletterModal.show();
                }, 3000);
            });
        </script>
    @endif
@endif

{{-- Information Popup --}}
@if ($business_card_details != null)
    {{-- Check Information Popup --}}
    @if (
        !empty($business_card_details->is_info_pop_active) &&
            $business_card_details->is_info_pop_active == 1 &&
            empty(request()->getQueryString()))
        @php
            // Use fully qualified class name instead of `use`
            $information_pop = \App\InformationPop::where('card_id', $business_card_details->card_id)->first();

            // Variables
            $confetti_effect = $information_pop->confetti_effect ?? 0;
            $img = $information_pop->info_pop_image ?? '';
            $title = $information_pop->info_pop_title ?? '';
            $desc = $information_pop->info_pop_desc ?? '';
            $button_text = $information_pop->info_pop_button_text ?? '';
            $button_url = $information_pop->info_pop_button_url ?? '#';
        @endphp

        <div class="modal fade" id="informationModal" tabindex="-1" aria-hidden="true"
            style="background: rgba(0, 0, 0, 0.7);">
            <div class="modal-dialog modal-md modal-dialog-end" role="document">

                <div class="modal-content">
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="d-flex justify-content-end align-items-center">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="row d-flex justify-content-center align-items-center">
                            {{-- Image --}}
                            <div class="w-25 h-25 mb-2">
                                <img src="{{ url($img) }}" alt="{{ $title }}"
                                    class="img-fluid object-fit-contain rounded-4" />
                            </div>
                            {{-- Title --}}
                            <div class="mb-2">
                                <h2 class="text-center mb-1">{{ __($title) }}</h2>
                                <p class="text-center mb-1 text-muted">{{ __($desc) }}</p>
                            </div>
                            {{-- Button --}}
                            <div class="d-flex justify-content-center align-items-center">
                                <a href="{{ $button_url }}" target="_blank"
                                    class="btn btn-red w-100">{{ __($button_text) }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auto Open Modal --}}
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                "use strict";

                const informationModal = new bootstrap.Modal(document.getElementById('informationModal'), {
                    keyboard: false
                });

                setTimeout(() => {
                    informationModal.show();
                }, 5000);
            });
        </script>

        {{-- Enable Confetti --}}
        @if ($confetti_effect == 1)
            <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
            <script>
                setTimeout(() => {
                    "use strict";

                    // Create and insert custom canvas above modal
                    const confettiCanvas = document.createElement('canvas');
                    confettiCanvas.id = 'confetti-overlay';
                    confettiCanvas.style.position = 'fixed';
                    confettiCanvas.style.top = 0;
                    confettiCanvas.style.left = 0;
                    confettiCanvas.style.width = '100%';
                    confettiCanvas.style.height = '100%';
                    confettiCanvas.style.pointerEvents = 'none';
                    confettiCanvas.style.zIndex = '1065'; // Higher than modal
                    document.body.appendChild(confettiCanvas);

                    // Bind confetti to this canvas
                    const myConfetti = confetti.create(confettiCanvas, {
                        resize: true,
                        useWorker: true
                    });

                    const randomColors = Array.from({
                            length: 7
                        }, () =>
                        '#' + Math.floor(Math.random() * 16777215).toString(16)
                    );

                    myConfetti({
                        particleCount: window.innerWidth > 768 ? 200 : 100,
                        spread: 120,
                        origin: {
                            x: 0.5,
                            y: 0.75
                        },
                        colors: randomColors
                    });

                    // Optional: remove canvas after 8 seconds
                    setTimeout(() => {
                        document.getElementById('confetti-overlay')?.remove();
                    }, 8000);

                }, 5000);
            </script>
        @endif
    @endif
@endif
