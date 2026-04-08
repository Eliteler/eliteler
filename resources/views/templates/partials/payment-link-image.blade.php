<!-- LightGallery CSS -->
<link href="{{ asset('css/lightgallery.min.css') }}" rel="stylesheet">

{{-- Payment Icon/Image Display --}}
@if ($payment->type == 'image' && !empty($payment->content))
    <!-- {{ $payment->label }} Image -->
    <div class="lightgallery rounded-lg flex items-center justify-center">
        <a href="{{ url($payment->content) }}" data-sub-html="{{ $payment->label }}" onclick="return false;">
            <img
                src="{{ url($payment->content) }}"
                alt="{{ $payment->label }}"
                class="w-full h-full object-cover rounded-lg"
                style="max-width: 320px; max-height: 320px;"
            />
        </a>
    </div>
@else
    <!-- {{ $payment->label }} Icon -->
    <i class="{{ $payment->icon }} text-4xl {{ $business_card_details->theme_id == "588969111143" || $business_card_details->theme_id == "588969111140" ? 'text-white' : '' }}"></i>
@endif

<!-- LightGallery JS -->
<script src="{{ asset('js/lightgallery.min.js') }}"></script>
<script src="{{ asset('js/lightgallery-thumbnail.min.js') }}"></script>
<script src="{{ asset('js/lightgallery-zoom.min.js') }}"></script>

<!-- Initialize LightGallery -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const galleries = document.querySelectorAll(".lightgallery");
        galleries.forEach(gallery => {
            lightGallery(gallery, {
                selector: 'a',
                plugins: [lgZoom, lgThumbnail],
                speed: 500,
            });
        });
    });
</script>