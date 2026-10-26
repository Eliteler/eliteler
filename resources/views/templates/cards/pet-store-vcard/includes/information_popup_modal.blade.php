<!-- Start Information Popup Modal (Orange Design) -->
@php
    use App\InformationPop;

    // Queries
    $information_pop = InformationPop::where('card_id', $business_card_details->card_id)->first();

    if ($information_pop) {
        // Default values if not found or empty
        $confetti_effect = $information_pop->confetti_effect ?? 0;
        $img = $information_pop->info_pop_image ?? null;
        $title = $information_pop->info_pop_title ?? __('Important Info');
        $desc =
            $information_pop->info_pop_desc ??
            __('We pride ourselves on ethical sourcing and a clean, safe environment for all animals.');
        $button_text = $information_pop->info_pop_button_text ?? __('Learn More');
        $button_url = $information_pop->info_pop_button_url ?? '#';
    }
@endphp

@if (isset($information_pop))
    <div id="customInfoOverlay" class="hidden" onclick="closeInfoModal()">
        <div id="customInfoBox" onclick="event.stopPropagation()">

            <!-- Close Button -->
            <button class="custom-info-close" onclick="closeInfoModal()">
                <i class="fas fa-times"></i>
            </button>

            <!-- Dynamic Image or Icon -->
            @if (!empty($img))
                <img src="{{ url($img) }}" alt="{{ $title }}" class="custom-info-img">
            @else
                <i class="fas fa-info-circle custom-info-icon"></i>
            @endif

            <!-- Title -->
            <h3 class="custom-info-title">{{ $title }}</h3>

            <!-- Description (Wrapped for scrolling if it's too long) -->
            <div class="custom-info-desc-wrapper">
                <div class="custom-info-desc">
                    {!! $desc !!}
                </div>
            </div>

            <!-- Action Button (White with Orange Text) -->
            <a href="{{ $button_url }}" target="_blank" class="custom-info-btn">
                {{ $button_text }}
            </a>
        </div>
    </div>

    {{-- Enable confetti effect --}}
    @if ($confetti_effect == 1 && $introScreen == null)
        <script src="{{ asset('js/confetti.browser.min.js') }}"></script>
        <script>
            function getRandomColor() {
                return '#' + Math.floor(Math.random() * 16777215).toString(16);
            }
            const randomColors = Array.from({
                length: 7
            }, getRandomColor);

            function triggerInfoConfetti() {
                if (window.innerWidth > 768) {
                    confetti({
                        particleCount: 200,
                        spread: 120,
                        colors: randomColors,
                        origin: {
                            x: 0.5,
                            y: 0.75
                        }
                    });
                } else {
                    confetti({
                        particleCount: 100,
                        spread: 100,
                        colors: randomColors,
                        origin: {
                            x: 0.5,
                            y: 0.72
                        }
                    });
                }
            }
        </script>
    @endif
@endif
<!-- End Information Popup Modal -->

<script>
    function openInfoModal() {
        const overlay = document.getElementById('customInfoOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            // Force reflow
            void overlay.offsetWidth;
            overlay.classList.add('is-active');

            // Trigger confetti if function exists
            if (typeof triggerInfoConfetti === 'function') {
                setTimeout(triggerInfoConfetti, 300);
            }
        }
    }

    function closeInfoModal() {
        const overlay = document.getElementById('customInfoOverlay');
        if (overlay) {
            overlay.classList.remove('is-active');
            // Wait for CSS transition to finish before hiding display
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }
    }

    // Auto-open on load (if that's what your previous logic did)
    document.addEventListener("DOMContentLoaded", function() {
        // If you want it to pop up automatically:
        setTimeout(() => {
            openInfoModal();
        }, 1000);
    });
</script>
