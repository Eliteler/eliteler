@php
    use Illuminate\Support\Facades\DB;
    use App\Transaction;

    // Current Plan
    $currentPlan = json_decode($user->plan_details);

    // previous transaction
    $previousTransaction = Transaction::where('user_id', Auth::user()->id)
        ->latest()
        ->first();

    // is trial
    $is_trial = optional($previousTransaction)->payment_gateway_name == 'TRIAL';

    // ai credits
    $ai_credits = $is_trial ? $currentPlan->trial_ai_credits ?? 0 : $currentPlan->ai_credits ?? 0;

    // helper to normalize "unlimited" values
    $fmt = fn($val) => $val == '999' || $val == '9999' ? __('Unlimited') : $val;

    // Lighten a hex color by a percentage (0–100) for the gradient's second stop
function lightenColor($hex, $percent = 25)
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (strlen($hex) != 6) {
        return '#4299e1'; // fallback
    }
    [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    $r = min(255, $r + (255 - $r) * ($percent / 100));
    $g = min(255, $g + (255 - $g) * ($percent / 100));
    $b = min(255, $b + (255 - $b) * ($percent / 100));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

$templateColor = $template_config->template_color ?? '#206bc4';
    $templateColorLight = lightenColor($templateColor, 30);
@endphp

<style>
    .datagrid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .datagrid-title {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .02em;
        font-weight: 500;
    }

    .datagrid-content {
        font-size: 16px;
    }

    .plan-hero {
        border-radius: .5rem;
        color: #fff;
    }

    .plan-hero .text-white-75 {
        color: rgba(255, 255, 255, .75);
    }

    .plan-hero .border-white-25 {
        border-color: rgba(255, 255, 255, .25) !important;
    }

    .section-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="modal modal-bg fade" id="currentPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header py-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="section-icon bg-primary-lt text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3l8 4.5v9l-8 4.5l-8 -4.5v-9z" />
                            <path d="M12 12l8 -4.5" />
                            <path d="M12 12v9" />
                            <path d="M12 12l-8 -4.5" />
                        </svg>
                    </span>
                    <div>
                        <h4 class="modal-title mb-1">{{ __('Current Plan') }}</h4>
                        <div class="text-muted small mt-1">{{ __('Your active subscription details') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Plan Hero Summary -->
                <div class="card mb-4 plan-hero"
                    style="background: linear-gradient(135deg, {{ $templateColor }} 0%, {{ $templateColorLight }} 100%);">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-white-75 small">{{ __('Plan Name') }}</div>
                                <div class="fs-1 fw-bold text-uppercase">
                                    {{ $currentPlan->plan_name ?? '-' }}
                                </div>
                                @if ($is_trial)
                                    <span class="badge bg-white text-dark mt-1">{{ __('Trial') }}</span>
                                @endif
                            </div>
                            <div class="col-auto text-end">
                                <div class="text-white-75 small">{{ __('Price') }}</div>
                                <div class="fs-1 fw-bold">
                                    {{ formatCurrency($currentPlan->plan_price ?? '-') }}
                                </div>
                            </div>
                            @if (isset($currentPlan->validity) && $currentPlan->validity != 9999)
                                <div class="col-auto text-end border-start border-white-25 ps-4">
                                    <div class="text-white-75 small">{{ __('Remaining Days') }}</div>
                                    <div class="fs-1 fw-bold">
                                        {{ $remaining_days > 0 ? floor($remaining_days) : __('Expired') }}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if (isset($currentPlan->validity) && $currentPlan->validity != 9999 && $remaining_days > 0)
                            @php
                                $percentUsed = min(100, max(0, 100 - ($remaining_days / $currentPlan->validity) * 100));
                            @endphp
                            <div class="progress progress-sm mt-3" style="background-color: rgba(255,255,255,.25);">
                                <div class="progress-bar bg-white" style="width: {{ $percentUsed }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- vCard Features -->
                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'VCARD')
                    <div class="card mb-4">
                        <div class="card-header">
                            <span class="section-icon bg-blue-lt text-blue me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" />
                                    <path d="M7 9h4" />
                                    <path d="M7 13h6" />
                                </svg>
                            </span>
                            <h4 class="card-title mb-0 d-inline-flex align-middle">{{ __('vCard Features') }}</h4>
                        </div>
                        <div class="card-body">
                            @php
                                $vcardFeatures = [
                                    __('No of vCards') => $fmt($currentPlan->no_of_vcards ?? null),
                                    __('No of Services') => $fmt($currentPlan->no_of_services ?? null),
                                    __('No of vCard Products') => $fmt($currentPlan->no_of_vcard_products ?? null),
                                    __('No of Links') => $fmt($currentPlan->no_of_links ?? null),
                                    __('No of Payments') => $fmt($currentPlan->no_of_payments ?? null),
                                    __('No of Galleries') => $fmt($currentPlan->no_of_galleries ?? null),
                                    __('No of Testimonials') => $fmt($currentPlan->no_testimonials ?? null),
                                    __('Business Hours') => ($currentPlan->business_hours ?? null) == '1',
                                    __('Appointments') => ($currentPlan->appointment ?? null) == '1',
                                    __('Service Booking') => ($currentPlan->service_booking ?? null) == '1',
                                    __('Contact Form') => ($currentPlan->contact_form ?? null) == '1',
                                    __('No of Enquiries') => $fmt($currentPlan->no_of_enquires ?? null),
                                    __('Password Protected') => ($currentPlan->password_protected ?? null) == '1',
                                ];
                            @endphp

                            <div class="datagrid">
                                @foreach ($vcardFeatures as $label => $value)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ $label }}</div>
                                        <div class="datagrid-content">
                                            @if (is_bool($value))
                                                @if ($value)
                                                    <span class="badge bg-success-lt text-success rounded-pill">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-sm me-1" width="12" height="12"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="3" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M5 12l5 5l10 -10" />
                                                        </svg>
                                                        {{ __('Yes') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-lt text-danger rounded-pill">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-sm me-1" width="12" height="12"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="3" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path d="M18 6l-12 12" />
                                                            <path d="M6 6l12 12" />
                                                        </svg>
                                                        {{ __('No') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="fw-medium">{{ $value ?? '-' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                @if (plugin_active('GoogleWallet') && ($currentPlan->google_wallet ?? null) == 1)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ __('Google Wallets') }}</div>
                                        <div class="datagrid-content fw-medium">
                                            {{ $fmt($currentPlan->no_of_google_wallets ?? null) }}
                                        </div>
                                    </div>
                                @endif

                                {{-- AI Builder --}}
                                @php
                                    $aibuilder_settings = DB::table('aibuilder_settings')->first();
                                @endphp

                                @if ($aibuilder_settings && $aibuilder_settings->aibuilder == 1)
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">{{ __('AI Credit') }}</div>
                                        <div class="datagrid-content fw-medium">
                                            {{ $ai_credits == -1 ? __('Unlimited') : $ai_credits }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Store Features -->
                @if ($plan->plan_type == 'BOTH' || $plan->plan_type == 'STORE')
                    <div class="card mb-4">
                        <div class="card-header">
                            <span class="section-icon bg-purple-lt text-purple me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l2 -6h14l2 6" />
                                    <path d="M3 9a3 3 0 0 0 6 0a3 3 0 0 0 6 0a3 3 0 0 0 6 0" />
                                    <path d="M4 9v10a1 1 0 0 0 1 1h14a1 1 0 0 0 1 -1v-10" />
                                </svg>
                            </span>
                            <h4 class="card-title mb-0 d-inline-flex align-middle">{{ __('Store Features') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row row-cards">
                                <div class="col-md-4">
                                    <div class="card card-sm bg-primary-lt">
                                        <div class="card-body text-center">
                                            <div class="text-muted">{{ __('Stores') }}</div>
                                            <div class="fw-bold fs-2">{{ $fmt($currentPlan->no_of_stores ?? null) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-sm bg-primary-lt">
                                        <div class="card-body text-center">
                                            <div class="text-muted">{{ __('Categories') }}</div>
                                            <div class="fw-bold fs-2">
                                                {{ $fmt($currentPlan->no_of_categories ?? null) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-sm bg-primary-lt">
                                        <div class="card-body text-center">
                                            <div class="text-muted">{{ __('Products') }}</div>
                                            <div class="fw-bold fs-2">
                                                {{ $fmt($currentPlan->no_of_store_products ?? null) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Additional Features -->
                <div class="card">
                    <div class="card-header">
                        <span class="section-icon bg-orange-lt text-orange me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 6l-8 4l8 4l8 -4l-8 -4" />
                                <path d="M4 14l8 4l8 -4" />
                            </svg>
                        </span>
                        <h4 class="card-title mb-0 d-inline-flex align-middle">{{ __('Additional Features') }}</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $extras = [
                                __('Custom Domain') => ($currentPlan->custom_domain ?? null) == 1,
                                __('Storage') =>
                                    ($currentPlan->storage ?? null) == '999'
                                        ? __('Unlimited')
                                        : ($currentPlan->storage ?? '-') . ' MB',
                                __('Advanced Settings') => ($currentPlan->advanced_settings ?? null) == 1,
                                __('Progressive Web App (PWA)') => ($currentPlan->pwa ?? null) == 1,
                                __('Personalized Link') => ($currentPlan->personalized_link ?? null) == 1,
                                __('Hide Branding') => ($currentPlan->hide_branding ?? null) == 1,
                                __('Free Setup') => ($currentPlan->free_setup ?? null) == 1,
                                __('Free Support') => ($currentPlan->free_support ?? null) == 1,
                            ];
                        @endphp

                        <div class="datagrid">
                            @foreach ($extras as $label => $value)
                                <div class="datagrid-item">
                                    <div class="datagrid-title">{{ $label }}</div>
                                    <div class="datagrid-content">
                                        @if (is_bool($value))
                                            @if ($value)
                                                <span
                                                    class="badge bg-success-lt text-success rounded-pill">{{ __('Yes') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-danger-lt text-danger rounded-pill">{{ __('No') }}</span>
                                            @endif
                                        @else
                                            <span class="fw-medium">{{ $value ?? '-' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
