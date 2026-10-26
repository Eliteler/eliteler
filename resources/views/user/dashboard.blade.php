@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Customer Dashboard CSS --}}
    <link href="{{ asset('css/customer/dashboard.css') }}" rel="stylesheet">
    
    {{-- FullCalendar CSS --}}
    <link href="{{ asset('plugins/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
@endsection

{{-- Dynamic subscription plan analysis --}}
@php
    use App\User;
    use App\Plan;
    use App\Category;
    use Illuminate\Support\Facades\Auth;

    // Fetch and parse active plan limits
    $plan = User::where('user_id', Auth::user()->user_id)->first();
    $active_plan = json_decode($plan->plan_details);

    $purchasedPlan = '';

    // check nfc session
    $nfc_ref = session('nfc_ref');

    if ($active_plan != null) {
        if (isset($active_plan->plan_type) == false) {
            $getPlan = Plan::where('plan_id', Auth::user()->plan_id)->first();

            $active_plan->plan_type = env('APP_TYPE');
            $active_plan->no_of_vcard_products = $getPlan->no_of_services;
            $active_plan->no_of_links = $getPlan->no_of_links;
            $active_plan->no_testimonials = $getPlan->no_testimonials;
            $active_plan->business_hours = $getPlan->business_hours;
            $active_plan->contact_form = $getPlan->contact_form;
            $active_plan->no_of_enquires = $getPlan->no_of_enquires;
            $active_plan->no_of_stores = $getPlan->no_of_vcards;
            $active_plan->no_of_categories = $getPlan->no_of_categories;
            $active_plan->no_of_store_products = $getPlan->no_of_services;
            $active_plan->pwa = $getPlan->pwa;
            $active_plan->password_protected = $getPlan->password_protected;
            $active_plan->advanced_settings = $getPlan->advanced_settings;
            $active_plan->additional_tools = $getPlan->additional_tools;

            User::where('user_id', Auth::user()->user_id)->update([
                'plan_details' => json_encode($active_plan),
            ]);

            $category = new Category();
            $category->user_id = Auth::user()->user_id;
            $category->category_id = 'others';
            $category->thumbnail = 'images/categories/others.avif';
            $category->category_name = 'Others';
            $category->save();

            $purchasedPlan = env('APP_TYPE');
        } else {
            $purchasedPlan = $active_plan->plan_type;
        }
    }

    $planAssignType = empty(Auth::user()->plan_id) ? 'new' : 'overwrite';
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold mb-0">
                            <!-- Dynamic greeting calculated in client-side Javascript -->
                            <span id="clientGreetingText"></span>
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Get a quick overview of your business performance.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Metric Summary Grid -->
                <div class="row row-deck row-cards mb-4 g-3">
                    {{-- Card 1: Current Plan --}}
                    <div class="col-6 {{ $active_plan->plan_type == 'BOTH' ? 'col-md-3 col-lg-3' : 'col-md-4 col-lg-4' }}">
                        <div class="card bg-custom1 border-0 shadow-sm">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="subheader text-uppercase small fw-bold mb-1">{{ __('Current Plan') }}</div>
                                    <div class="h2 text-dark-custom fw-bold mb-0 my-1 fs-1 text-uppercase">
                                        {{ __($active_plan->plan_name) }}
                                    </div>
                                </div>
                                <div class="text-secondary small mt-2">
                                    <a class="text-decoration-none text-muted" href="{{ route('user.plans') }}">
                                        {{ __('Show details') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Business Cards --}}
                    @if ($active_plan->plan_type == 'VCARD' || $active_plan->plan_type == 'BOTH')
                        <div
                            class="col-6 {{ $active_plan->plan_type == 'BOTH' ? 'col-md-3 col-lg-3' : 'col-md-4 col-lg-4' }}">
                            <div class="card bg-custom2 border-0 shadow-sm">
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="subheader text-uppercase small fw-bold mb-1">{{ __('eCards') }}
                                        </div>
                                        <div class="h1 text-dark-custom fw-bold mb-0 my-1 fs-1">
                                            {{ $business_card == 999 ? __('Unlimited') : $business_card }}
                                        </div>
                                    </div>
                                    <div class="text-secondary small mt-2">
                                        <a class="text-decoration-none text-muted" href="{{ route('user.cards') }}">
                                            {{ __('Show details') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Card 3: Stores --}}
                    @if ($active_plan->plan_type == 'STORE' || $active_plan->plan_type == 'BOTH')
                        <div
                            class="col-6 {{ $active_plan->plan_type == 'BOTH' ? 'col-md-3 col-lg-3' : 'col-md-4 col-lg-4' }}">
                            <div class="card bg-custom3 border-0 shadow-sm">
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="subheader text-uppercase small fw-bold mb-1">{{ __('Stores') }}</div>
                                        <div class="h1 text-dark-custom fw-bold mb-0 my-1 fs-1">
                                            {{ $storesCount == 999 ? __('Unlimited') : $storesCount }}
                                        </div>
                                    </div>
                                    <div class="text-secondary small mt-2">
                                        <a class="text-decoration-none text-muted" href="{{ route('user.stores') }}">
                                            {{ __('Show details') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Card 4: Remaining Days --}}
                    <div class="col-6 {{ $active_plan->plan_type == 'BOTH' ? 'col-md-3 col-lg-3' : 'col-md-4 col-lg-4' }}">
                        <div class="card bg-custom4 border-0 shadow-sm">
                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="subheader text-uppercase small fw-bold mb-1">{{ __('Remaining Days') }}
                                    </div>
                                    <div class="h1 text-dark-custom fw-bold mb-0 my-1 fs-1">
                                        @if ($active_plan->validity == 9999)
                                            {{ __('Lifetime') }}
                                        @else
                                            {{ $remaining_days > 0 ? floor($remaining_days) : __('Plan Expired!') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="text-secondary small mt-2">
                                    <a class="text-decoration-none text-muted" href="{{ route('user.plans') }}">
                                        {{ __('Show details') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chart Statistics Row --}}
                <div class="row row-cards mb-4 g-3">
                    {{-- Total Analytics Overview --}}
                    <div class="col-12 col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <div class="subheader text-uppercase fw-bold text-secondary overview-title">
                                            {{ __('Overall Performance') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center g-4">
                                    {{-- Line/Area Spline Chart --}}
                                    <div class="col-12 col-lg">
                                        <div id="cards" class="overview-chart"></div>
                                    </div>

                                    {{-- Right Legend Metrics --}}
                                    <div class="col-12 col-lg-auto">
                                        <div class="overview-stats">
                                            {{-- Business Cards Counter --}}
                                            @if ($active_plan->plan_type === 'VCARD' || $active_plan->plan_type === 'BOTH')
                                                <div class="overview-stat overview-stat-warning">
                                                    <div class="stat-title text-orange font-weight-medium">
                                                        <span class="status-dot bg-orange me-2"></span>
                                                        {{ __('eCards') }}
                                                    </div>
                                                    <div class="stat-value text-dark-custom h2 mb-0">
                                                        {{ $totalvCards }}
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Stores Counter --}}
                                            @if ($active_plan->plan_type === 'STORE' || $active_plan->plan_type === 'BOTH')
                                                <div class="overview-stat overview-stat-success">
                                                    <div class="stat-title text-success font-weight-medium">
                                                        <span class="status-dot bg-green me-2"></span>
                                                        {{ __('Stores') }}
                                                    </div>
                                                    <div class="stat-value text-dark-custom h2 mb-0">
                                                        {{ $totalStores }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Platforms Donut Analytics --}}
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="subheader text-uppercase fw-bold text-secondary mb-3">
                                    {{ __('Platforms Overview') }}</div>
                                <div id="platforms" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-cards mb-4 g-3">
                    {{-- This Week Audience spline area --}}
                    <div class="col-12 col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                <div class="subheader text-uppercase fw-bold text-secondary">
                                    {{ __('This week\'s audience of eCards') }}</div>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="thisWeekAudience"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Devices Donut Chart --}}
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="subheader text-uppercase fw-bold text-secondary mb-3">{{ __('Device Types') }}
                                </div>
                                <div id="devices" class="d-flex justify-content-center"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row row-cards g-3">
                    {{-- Calendar Appointments --}}
                    <div class="col-12 col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="subheader text-uppercase fw-bold text-secondary mb-3">
                                    {{ __('Appointments Calendar') }}</div>
                                <div id="appointments"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Top 20 Most Viewed Cards --}}
                    <div class="col-12 col-md-4">
                        <div class="card border-0 shadow-sm visitors-card">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                <div class="subheader text-uppercase fw-bold text-secondary">
                                    {{ __('Top 20 Most Viewed eCards') }}</div>
                            </div>
                            <div class="card-table table-responsive px-3 py-2">
                                <table class="table table-vcenter card-table table-striped table-bordered m-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Card Link') }}</th>
                                            <th class="text-end">{{ __('Hits') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($highestCards as $highestCard)
                                            <tr>
                                                <td>
                                                    <a href="{{ asset($highestCard['card']) }}" target="_blank"
                                                        class="text-decoration-none fw-bold text-truncate d-block"
                                                        style="max-width: 180px;">
                                                        /{{ $highestCard['card'] }}
                                                    </a>
                                                </td>
                                                <td class="text-end font-weight-medium text-dark-custom">
                                                    {{ $highestCard['count'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Appointment Modal Container --}}
                <div id="appointment-modal" class="modal fade" tabindex="-1" aria-hidden="true"
                    style="background-color: rgba(0, 0, 0, 0.5);">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($nfc_ref))
            <x-activate-nfc-plan :nfc_ref="$nfc_ref" :plan_assign_type="$planAssignType" />
        @endif

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script src='{{ asset('js/fullcalendar.min.js') }}'></script>
    <script src="{{ asset('js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('js/confetti.browser.min.js') }}"></script>

    <script>
        "use strict";

        // Localized client-side greeting triggers
        document.addEventListener("DOMContentLoaded", function() {
            const hour = new Date().getHours();
            let greetingText = '';

            if (hour >= 5 && hour < 12) {
                greetingText = "{{ __('Good morning') }}";
            } else if (hour >= 12 && hour < 17) {
                greetingText = "{{ __('Good afternoon') }}";
            } else if (hour >= 17 && hour < 22) {
                greetingText = "{{ __('Good evening') }}";
            } else {
                greetingText = "{{ __('Good night') }}";
            }

            const targetText = document.getElementById('clientGreetingText');
            if (targetText) {
                targetText.innerHTML =
                    `${greetingText}, <span class="text-primary">{{ Auth::user()->name }}!</span> 👋`;
            }
        });

        // Overview spline area parameters
        document.addEventListener("DOMContentLoaded", function() {
            window.ApexCharts && (new ApexCharts(document.getElementById('cards'), {
                chart: {
                    type: "area",
                    fontFamily: 'inherit',
                    height: 240,
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false,
                    },
                    dropShadow: {
                        enabled: true,
                        top: 6,
                        left: 0,
                        blur: 8,
                        opacity: 0.12,
                        color: ["#F76707", "#2FB344"]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.22,
                        opacityTo: 0.01,
                        stops: [0, 95]
                    }
                },
                stroke: {
                    width: 3.5,
                    lineCap: "round",
                    curve: "smooth",
                },
                series: [
                    @if ($active_plan->plan_type === 'VCARD' || $active_plan->plan_type === 'BOTH')
                        {
                            name: `{{ __('eCards') }}`,
                            data: [{{ $vcards }}]
                        }
                        @if ($active_plan->plan_type === 'STORE' || $active_plan->plan_type === 'BOTH')
                            ,
                        @endif
                    @endif

                    @if ($active_plan->plan_type === 'STORE' || $active_plan->plan_type === 'BOTH')
                        {
                            name: `{{ __('Stores') }}`,
                            data: [{{ $stores }}]
                        }
                    @endif
                ],
                tooltip: {
                    theme: 'dark'
                },
                grid: {
                    padding: {
                        top: -10,
                        right: 10,
                        left: 10,
                        bottom: 0
                    },
                    strokeDashArray: 5,
                    borderColor: 'rgba(148, 163, 184, 0.06)'
                },
                xaxis: {
                    labels: {
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontFamily: 'inherit',
                            fontWeight: '500'
                        }
                    },
                    tooltip: {
                        enabled: false
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontFamily: 'inherit',
                            fontWeight: '500'
                        }
                    },
                },
                labels: [`{{ __('Jan') }}`, `{{ __('Feb') }}`, `{{ __('Mar') }}`,
                    `{{ __('Apr') }}`, `{{ __('May') }}`, `{{ __('Jun') }}`,
                    `{{ __('July') }}`, `{{ __('Aug') }}`, `{{ __('Sept') }}`,
                    `{{ __('Oct') }}`, `{{ __('Nov') }}`, `{{ __('Dec') }}`
                ],
                colors: ["#F76707", "#2FB344"],
                legend: {
                    show: false,
                },
            })).render();
        });

        // Platforms Donut layout
        document.addEventListener("DOMContentLoaded", function() {
            window.ApexCharts && (new ApexCharts(document.getElementById('platforms'), {
                chart: {
                    type: "donut",
                    fontFamily: 'inherit',
                    height: 250,
                    sparkline: {
                        enabled: true
                    },
                    animations: {
                        enabled: true
                    },
                },
                fill: {
                    opacity: 1,
                },
                series: {!! json_encode($highestPlatforms['count']) !!},
                labels: {!! json_encode(array_map(fn($word) => __($word), $highestPlatforms['platform'])) !!},
                tooltip: {
                    theme: 'dark',
                    fillSeriesColor: false
                },
                grid: {
                    strokeDashArray: 4,
                },
                colors: ["#4263eb", "#f76707", "#330902", "#1092a3", "#ed023d", "#007a0c", "#db25db"],
                legend: {
                    show: true,
                    position: 'bottom',
                    offsetY: 12,
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 100,
                    },
                    itemMargin: {
                        horizontal: 8,
                        vertical: 8
                    },
                },
            })).render();
        });

        // This Week Audience stacked spline metrics
        document.addEventListener("DOMContentLoaded", function() {
            window.ApexCharts && (new ApexCharts(document.getElementById('thisWeekAudience'), {
                chart: {
                    type: "area",
                    fontFamily: 'inherit',
                    height: 355,
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false,
                    },
                    animations: {
                        enabled: true
                    },
                    dropShadow: {
                        enabled: true,
                        top: 6,
                        left: 0,
                        blur: 8,
                        opacity: 0.10,
                        color: ["#4263eb", "#f76707"]
                    }
                },
                dataLabels: {
                    enabled: false,
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        opacityFrom: 0.20,
                        opacityTo: 0.01,
                        stops: [0, 95]
                    }
                },
                stroke: {
                    width: 3.5,
                    lineCap: "round",
                    curve: "smooth",
                },
                series: [
                    @if ($active_plan->plan_type === 'VCARD' || $active_plan->plan_type === 'BOTH')
                        {
                            name: `{{ __('eCards') }}`,
                            data: {!! json_encode($weekData['vcard']) !!}
                        }
                        @if ($active_plan->plan_type === 'STORE' || $active_plan->plan_type === 'BOTH')
                            ,
                        @endif
                    @endif

                    @if ($active_plan->plan_type === 'STORE' || $active_plan->plan_type === 'BOTH')
                        {
                            name: `{{ __('Stores') }}`,
                            data: {!! json_encode($weekData['store']) !!}
                        }
                    @endif
                ],
                tooltip: {
                    theme: 'dark'
                },
                grid: {
                    padding: {
                        top: -10,
                        right: 10,
                        left: 10,
                        bottom: 0
                    },
                    strokeDashArray: 5,
                    borderColor: 'rgba(148, 163, 184, 0.06)'
                },
                xaxis: {
                    labels: {
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontFamily: 'inherit',
                            fontWeight: '500'
                        }
                    },
                    tooltip: {
                        enabled: false
                    },
                    axisBorder: {
                        show: false,
                    },
                    categories: [`{{ __('Monday') }}`, `{{ __('Tuesday') }}`,
                        `{{ __('Wednesday') }}`, `{{ __('Thursday') }}`, `{{ __('Friday') }}`,
                        `{{ __('Saturday') }}`, `{{ __('Sunday') }}`
                    ],
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d',
                            fontFamily: 'inherit',
                            fontWeight: '500'
                        }
                    },
                },
                colors: ["#4263eb", "#f76707"],
                legend: {
                    show: false,
                },
            })).render();
        });

        // Devices metrics Donut
        document.addEventListener("DOMContentLoaded", function() {
            window.ApexCharts && (new ApexCharts(document.getElementById('devices'), {
                chart: {
                    type: "donut",
                    fontFamily: 'inherit',
                    height: 250,
                    sparkline: {
                        enabled: true
                    },
                    animations: {
                        enabled: true
                    },
                },
                fill: {
                    opacity: 1,
                },
                series: {!! json_encode($highestDevices['count']) !!},
                labels: {!! json_encode(array_map(fn($word) => __($word), $highestDevices['device'])) !!},
                tooltip: {
                    theme: 'dark',
                    fillSeriesColor: false
                },
                grid: {
                    strokeDashArray: 4,
                },
                colors: ["#4263eb", "#f76707", "#330902", "#1092a3", "#ed023d", "#007a0c", "#db25db"],
                legend: {
                    show: true,
                    position: 'bottom',
                    offsetY: 12,
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 100,
                    },
                    itemMargin: {
                        horizontal: 8,
                        vertical: 8
                    },
                },
            })).render();
        });

        // FullCalendar Initialization
        document.addEventListener('DOMContentLoaded', function() {
            var appointmentsCalendarEl = document.getElementById('appointments');
            if (!appointmentsCalendarEl) return;

            var appointmentsCalendar = new FullCalendar.Calendar(appointmentsCalendarEl, {
                initialView: 'dayGridMonth',
                events: "{{ route('user.calendar.appointments') }}",
                locale: "{{ app()->getLocale() }}",
                editable: false,
                selectable: true,
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next'
                },
                eventClick: function(info) {
                    var modal = document.getElementById('appointment-modal');
                    if (!modal) return;

                    var modalContent = modal.querySelector('.modal-content');
                    if (!modalContent) return;

                    // Extract event properties safely
                    var props = info.event.extendedProps || {};
                    var title = info.event.title || (info.event._def ? info.event._def.title : '');
                    var formattedDate = props.created_at ?
                        new Date(props.created_at).toLocaleString('en-US', {
                            month: 'short',
                            day: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        }).replace(',', '') :
                        'N/A';

                    var googleUrl = props.google_calendar_url || '#';

                    modalContent.innerHTML = `
                    {{-- Modal Header --}}
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-dark mb-0">
                            <span class="text-muted fw-normal">{{ __('eCard name') }}:</span> ${title}
                        </h5>
                        <button type="button" class="btn-close" onclick="closeModal()" aria-label="{{ __('Close') }}"></button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body py-3">
                        <div class="subheader text-uppercase small fw-bold text-secondary mb-3">{{ __('Customer Details') }}</div>
                        
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">{{ __('Date') }}</span>
                                    <span class="text-dark fw-semibold d-block">${formattedDate}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">{{ __('Name') }}</span>
                                    <span class="text-dark fw-semibold d-block">${props.name || 'N/A'}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">{{ __('Phone') }}</span>
                                    <span class="text-dark fw-semibold d-block">
                                        <a href="tel:${props.phone || '#'}" class="text-decoration-none">${props.phone || 'N/A'}</a>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">{{ __('Notes') }}</span>
                                    <span class="text-dark fw-semibold d-block">${props.notes || 'N/A'}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">{{ __('Booking Date') }}</span>
                                    <span class="text-dark fw-semibold d-block">${props.booking_date || 'N/A'}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="text-secondary small d-block mb-1">{{ __('Booking Time') }}</span>
                                    <span class="text-dark fw-semibold d-block">${props.booking_time || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer d-flex justify-content-end gap-2 border-top pt-3">
                        <button type="button" class="btn btn-danger px-4" onclick="closeModal()">{{ __('Close') }}</button>
                        <a href="${googleUrl}" target="_blank" class="btn btn-primary px-4 d-inline-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-calendar-plus me-2" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12.5 21h-6.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v5" />
                                <path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M16 19h6" /><path d="M19 16v6" />
                            </svg>
                            {{ __('Add Google Calendar') }}
                        </a>
                    </div>
                `;

                    // Display Modal
                    modal.removeAttribute('aria-hidden');
                    modal.classList.add('show');
                    modal.style.display = 'block';
                }
            });

            appointmentsCalendar.render();
        });

        // Close Modal Handler
        function closeModal() {
            var modal = document.getElementById('appointment-modal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
            }
        }

        // Close Modal on Dark Overlay Click
        window.addEventListener('click', function(event) {
            var modal = document.getElementById('appointment-modal');
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>
@endsection
