<!-- LightGallery CSS -->
<link href="{{ asset('css/lightgallery.min.css') }}" rel="stylesheet">

{{-- Payment Icon / Image Display --}}
@if ($payment->type == 'image' && !empty($payment->content))
    {{-- Image with LightGallery zoom ── --}}
    <div class="lightgallery payment-img-gallery">
        <a href="{{ url($payment->content) }}"
            data-sub-html="<span style='font-family:Playfair Display,serif;font-size:16px;color:#DDB96A'>{{ $payment->label }}</span>"
            onclick="return false;">
            <img src="{{ url($payment->content) }}" alt="{{ $payment->label }}" />
        </a>
    </div>
@else
    {{-- Icon display ── --}}
    @php
        $isDarkTheme = in_array($business_card_details->theme_id, ['588969111143', '588969111140']);
    @endphp

    <div class="payment-icon-wrap {{ $isDarkTheme ? 'payment-icon-wrap--dark' : 'payment-icon-wrap--light' }}">
        <i class="{{ $payment->icon }}"></i>
    </div>
@endif

<!-- LightGallery JS -->
<script src="{{ asset('js/lightgallery.min.js') }}"></script>
<script src="{{ asset('js/lightgallery-thumbnail.min.js') }}"></script>
<script src="{{ asset('js/lightgallery-zoom.min.js') }}"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const galleries = document.querySelectorAll(".lightgallery");
        galleries.forEach(gallery => {
            lightGallery(gallery, {
                selector: 'a',
                plugins: [lgZoom, lgThumbnail],
                speed: 400,
                backdropDuration: 300,
                zoomFromOrigin: true,
            });
        });
    });
</script>
