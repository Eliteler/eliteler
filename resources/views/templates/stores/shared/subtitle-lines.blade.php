{{--
    Eliteler: the store's second title and subtitle, each followed by a line break.
    Themes include it right before $business_card_details->sub_title wherever the store subtitle is shown.
--}}
@if (!empty($business_card_details->title2))
    {{ $business_card_details->title2 }} <br>
@endif
@if (!empty($business_card_details->subtitle2))
    {{ $business_card_details->subtitle2 }} <br>
@endif
