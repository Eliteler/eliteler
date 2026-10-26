 <div class="relative">
     <h2 class="text-3xl lg:text-4xl font-bold py-12 text-center relative custom-head">
         {{ __('Services') }}
     </h2>
     <img src="{{ url('img/templates/modern/leaf-4.png') }}" alt=""
         class="w-28 absolute top-2 -left-16 rotate-12 animate-move-y" />
     <div class="slider">
         {{-- All services --}}
         @foreach ($service_details as $service_detail)
             <!-- Service -->
             <div class="flex flex-col justify-center p-4 rounded-2xl shadow-[0_0_4px_rgba(0,0,0,0.1)] border">
                 {{-- Image --}}
                 <img class="w-full h-48 object-cover mb-4 rounded-2xl" src="{{ url($service_detail->service_image) }}"
                     alt="{{ $service_detail->service_name }}" />
                 {{-- Name --}}
                 <h2 class="text-lg font-medium mb-2">
                     {{ $service_detail->service_name }}
                 </h2>
                 {{-- Description --}}
                 <p class="text-gray-500 font-normal mb-2">
                     {{ $service_detail->service_description }}
                 </p>
                 <!-- Price & Booking Section -->

                 <!-- Enquiry Button -->
                 <div class="mt-4 w-full">
                     @if ($enquiry_button != null)
                         @if ($whatsAppNumberExists == true && $whatsAppNumberExists == true && $service_detail->enable_enquiry == 'Enabled')
                             <a href="https://wa.me/{{ $enquiry_button }}?text={{ __('Hi, I am interested in your product/service:') }} {{ $service_detail->service_name }}. {{ __('Please provide more details.') }}"
                                 target="_blank"
                                 class="text-white w-full px-12 text-lg bg-green-800 py-2 block text-center rounded-2xl">
                                 {{ __('Enquire') }}
                             </a>
                         @endif
                     @endif
                 </div>
                 <!-- End Price & Booking Section -->
             </div>
         @endforeach
     </div>
 </div>
