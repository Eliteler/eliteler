 @php
     use Illuminate\Support\Str;

     $locale = $business_card_details->card_lang;  

     Session::put('locale', $locale);
     app()->setLocale($locale);
 @endphp

 <select class="lang-switcher" id="language" name="language">
     @foreach (config('app.languages') as $langLocale => $langName)
         <option value="{{ $langLocale }}" {{ app()->getLocale() == $langLocale ? 'selected' : '' }}>
             <strong>{{ Str::upper($langLocale) }}</strong>
         </option>
     @endforeach
 </select>

 {{-- Custom JS --}}
 @section('custom-js')
     <script>
         // Language switcher
         $(document).ready(function() {
             "use strict";
             // Language switcher
             $('#language').change(function() {
                 var selectedLocale = $(this).val();

                 // Get business card id
                 var businessCardId = '{{ $business_card_details->card_id }}';

                 $.ajax({
                     url: "{{ config('app.url') }}/set-locale", // Use the route name defined earlier
                     type: "POST",
                     data: {
                         locale: selectedLocale,
                         card_id: businessCardId,
                         _token: '{{ csrf_token() }}' // Include CSRF token
                     },
                     success: function(response) {
                         // Hard reload
                         window.location.replace(window.location.href);
                     },
                     error: function(xhr) {
                         console.error(xhr); // Log error message
                     }
                 });
             });
         });
     </script>
 @endsection
