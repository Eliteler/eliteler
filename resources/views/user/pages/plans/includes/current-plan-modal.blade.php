@php
    use Illuminate\Support\Facades\DB;
    
    // Current Plan
    $currentPlan = json_decode($user->plan_details);
    $ai_credits = DB::table('ai_credits')->where('user_id', $user->user_id)->first();
    $ai_credits = $ai_credits->credits ?? 0;
@endphp

<div class="modal modal-blur fade py-5" id="currentPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-sm">

            <!-- Header -->
            <div class="modal-header border-bottom py-3">
                <div>
                    <h4 class="modal-title fw-bold mb-0">{{ __('Current Plan') }}</h4>
                    <small class="text-muted">{{ __('Your active subscription details') }}</small>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Plan Summary -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="text-muted small">{{ __('Plan Name') }}</div>
                            <div class="fw-bold fs-5 h3">{{ $currentPlan->plan_name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-muted small">{{ __('Price') }}</div>
                            <div class="fw-semibold fs-5 h3">
                                {{ formatCurrency($currentPlan->plan_price ?? '-') }}
                            </div>
                        </div>

                        @if ($currentPlan->validity != 9999)
                            <div>
                                <div class="text-muted small">{{ __('Remaining Days') }}</div>
                                <div class="fw-semibold fs-5 h3">{{ $remaining_days }} {{ __('Days') }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- vCard Features -->
                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'VCARD')
                    <h5 class="fw-semibold mb-3">{{ __('vCard Features') }}</h5>
                    <div class="row g-3 mb-4">
                        @php
                            $vcardFeatures = [
                                __('No of vCards') =>
                                    $currentPlan->no_of_vcards == '999' ? __('Unlimited') : $currentPlan->no_of_vcards,
                                __('No of Services') =>
                                    $currentPlan->no_of_services == '999'
                                        ? __('Unlimited')
                                        : $currentPlan->no_of_services,
                                __('No of vCard Products') =>
                                    $currentPlan->no_of_vcard_products == '999'
                                        ? __('Unlimited')
                                        : $currentPlan->no_of_vcard_products,
                                __('No of Links') =>
                                    $currentPlan->no_of_links == '999' ? __('Unlimited') : $currentPlan->no_of_links,
                                __('No of Payments') =>
                                    $currentPlan->no_of_payments == '999'
                                        ? __('Unlimited')
                                        : $currentPlan->no_of_payments,
                                __('No of Galleries') =>
                                    $currentPlan->no_of_galleries == '999'
                                        ? __('Unlimited')
                                        : $currentPlan->no_of_galleries,
                                __('No of Testimonials') =>
                                    $currentPlan->no_testimonials == '999'
                                        ? __('Unlimited')
                                        : $currentPlan->no_testimonials,
                                __('Business Hours') => $currentPlan->business_hours == '1' ? __('Yes') : __('No'),
                                __('Appointments') => $currentPlan->appointment == '1' ? __('Yes') : __('No'),
                                __('Service Booking') => $currentPlan->service_booking == '1' ? __('Yes') : __('No'),
                                __('Contact Form') => $currentPlan->contact_form == '1' ? __('Yes') : __('No'),
                                __('No of Enquiries') =>
                                    $currentPlan->no_of_enquires == '999'
                                        ? __('Unlimited')
                                        : $currentPlan->no_of_enquires,
                                __('Password Protected') =>
                                    $currentPlan->password_protected == '1' ? __('Yes') : __('No'),
                            ];
                        @endphp

                        @foreach ($vcardFeatures as $label => $value)
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between border rounded-3 p-3 bg-white">
                                    <span class="text-muted">{{ $label }}</span>
                                    <span class="fw-semibold">{{ $value ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach

                        @if (is_dir(base_path('plugins/GoogleWallet')) && $currentPlan->google_wallet == 1)
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between border rounded-3 p-3 bg-white">
                                    <span class="text-muted">{{ __('Google Wallets') }}</span>
                                    <span
                                        class="fw-semibold">{{ $currentPlan->no_of_google_wallets == '999' ? __('Unlimited') : $currentPlan->no_of_google_wallets }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- AI Builder --}}
                        @php
                            $aibuilder_settings = DB::table('aibuilder_settings')->first();
                        @endphp

                        @if ($aibuilder_settings->aibuilder == 1)
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between border rounded-3 p-3 bg-white">
                                    <span class="text-muted">{{ __('AI Credits') }}</span>
                                    <span
                                        class="fw-semibold">{{ $ai_credits == '999' ? __('Unlimited') : $ai_credits }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Store Features -->
                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'STORE')
                    <h5 class="fw-semibold mb-3">{{ __('Store Features') }}</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-white text-center">
                                <div class="text-muted small">{{ __('Stores') }}</div>
                                <div class="fw-bold fs-4">
                                    {{ $currentPlan->no_of_stores == '999' ? __('Unlimited') : $currentPlan->no_of_stores }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-white text-center">
                                <div class="text-muted small">{{ __('Categories') }}</div>
                                <div class="fw-bold fs-4">
                                    {{ $currentPlan->no_of_categories == '999' ? __('Unlimited') : $currentPlan->no_of_categories }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-3 p-3 bg-white text-center">
                                <div class="text-muted small">{{ __('Products') }}</div>
                                <div class="fw-bold fs-4">
                                    {{ $currentPlan->no_of_store_products == '999' ? __('Unlimited') : $currentPlan->no_of_store_products }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Additional Features -->
                <h5 class="fw-semibold mb-3">{{ __('Additional Features') }}</h5>
                <div class="row g-3">
                    @php
                        $extras = [
                            __('Custom Domain') => $currentPlan->custom_domain == 1 ? __('Yes') : __('No'),
                            __('Storage') =>
                                ($currentPlan->storage == '999' ? __('Unlimited') : $currentPlan->storage) . ' MB',
                            __('Advanced Settings') => $currentPlan->advanced_settings == 1 ? __('Yes') : __('No'),
                            __('Progressive Web App (PWA)') => $currentPlan->pwa == 1 ? __('Yes') : __('No'),
                            __('Personalized Link') => $currentPlan->personalized_link == 1 ? __('Yes') : __('No'),
                            __('Hide Branding') => $currentPlan->hide_branding == 1 ? __('Yes') : __('No'),
                            __('Free Setup') => $currentPlan->free_setup == 1 ? __('Yes') : __('No'),
                            __('Free Support') => $currentPlan->free_support == 1 ? __('Yes') : __('No'),
                        ];

                        if ($config[76]->config_value == '1') {
                            $extras[__('Order NFC Card')] = $currentPlan->nfc_card == 1 ? __('Yes') : __('No');
                        }
                    @endphp

                    @foreach ($extras as $label => $value)
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between border rounded-3 p-3 bg-white">
                                <span class="text-muted">{{ $label }}</span>
                                <span class="fw-semibold">{{ $value ?? '-' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
            </div>

        </div>
    </div>
</div>
