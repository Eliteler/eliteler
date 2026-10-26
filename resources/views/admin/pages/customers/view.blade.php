@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS overrides --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }

        /* Style search inputs inside sub-tables */
        div.dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--tblr-border-color, #dbdfe6);
            border-radius: 4px;
            padding: .35rem .55rem;
            font-size: .8125rem;
            background-color: var(--tblr-bg-forms, #fff);
        }
    </style>
@endsection

@php
    use Illuminate\Support\Facades\Auth;
    use App\Transaction;
    use Carbon\Carbon;

    // Fetch current user's details
$user = Auth::user();
$allowedPermissions = json_decode($user->permissions, true);

if (!is_array($allowedPermissions)) {
    $allowedPermissions = [];
}

$defaultPermissions = [
    'currencies' => 1,
    'coupons' => 1,
    'custom_domain' => 1,
    'marketing' => 1,
    'demo_mode' => 1,
    'backup' => 1,
    'nfc_card_design' => 1,
    'nfc_card_orders' => 1,
    'nfc_card_order_transactions' => 1,
    'nfc_card_key_generations' => 1,
    'email_templates' => 1,
    'plugins' => 1,
    'referral_system' => 1,
    'in_app_purchases' => 1,
    'vcard_store' => 1,
    'business_card_intros' => 1,
    'ai_credits' => 1,
    ];

    $allowedPermissions = array_merge($defaultPermissions, $allowedPermissions);

    if ($allowedPermissions !== json_decode($user->permissions, true)) {
        $user->permissions = json_encode($allowedPermissions);
        $user->updated_at = Carbon::now();
        $user->save();
    }

    $allowedPermissions = json_decode($user->permissions, true);
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.customers', ['status' => 'all']) }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Customers') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.customers', ['status' => 'all']) }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Manage Customers') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#" class="text-decoration-none">{{ __('View') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('View Profile') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('View and manage customer profile details, business cards, stores, and transactions.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards g-3">
                    {{-- Profile Header Card --}}
                    <div class="col-12 col-md-12 col-xl-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <span class="avatar avatar-xl mb-3"
                                    style="background-image: url({{ asset($user_details->profile_image == '' ? 'profile.png' : $user_details->profile_image) }})"></span>
                                <h3 class="m-0 mb-1 text-center fw-bold">{{ $user_details->name }}</h3>
                                <div class="font-weight-bold">
                                    {{ __('Email address') }} :
                                    <strong>{{ $user_details->email == '' ? __('Not Available') : $user_details->email }}</strong>
                                </div>
                                <div class="font-weight-bold">
                                    {{ __('Mobile number') }} :
                                    <strong>{{ $user_details->mobile_number == '' ? __('Not Available') : $user_details->country_code . $user_details->mobile_number }}</strong>
                                </div>
                                <div class="mt-3">
                                    <span
                                        class="badge bg-green-600 text-green-50">{{ $user_details->role_id == 2 ? __('Customer') : '' }}</span>
                                </div>
                            </div>
                            <div class="d-flex border-top">
                                <a href="mailto:{{ $user_details->email == '' ? __('Not Available') : $user_details->email }}"
                                    class="card-btn py-3 text-secondary text-decoration-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-mail icon me-2">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" />
                                        <path d="M3 7l9 6l9 -6" />
                                    </svg>
                                    {{ __('Email') }}
                                </a>
                                @if ($user_details->mobile_number != '' || $user_details->mobile_number != null)
                                    <a href="tel:{{ $user_details->mobile_number == '' ? __('Not Available') : $user_details->mobile_number }}"
                                        class="card-btn py-3 text-secondary text-decoration-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-phone icon me-2">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                        </svg>
                                        {{ __('Phone') }}
                                    </a>
                                @endif
                                <a href="#" class="card-btn py-3 text-secondary text-decoration-none"
                                    onclick="loginUser('{{ $user_details->user_id }}'); return false;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-login-2 icon me-2">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M9 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" />
                                        <path d="M3 12h13l-3 -3" />
                                        <path d="M13 15l3 -3" />
                                    </svg>
                                    {{ __('Login') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- 1. Business Cards Dashboard Block --}}
                    <div class="col-12 col-md-6 col-xl-6 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Business Cards') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="cardsTable">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="cards-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button class="btn btn-primary btn-sm mobile-prev-btn" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>
                                <span class="text-secondary small font-weight-medium mobile-page-info"></span>
                                <button class="btn btn-primary btn-sm mobile-next-btn" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Stores Dashboard Block --}}
                    <div class="col-12 col-md-6 col-xl-6 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Stores') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="storesTable">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="stores-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button class="btn btn-primary btn-sm mobile-prev-btn" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>
                                <span class="text-secondary small font-weight-medium mobile-page-info"></span>
                                <button class="btn btn-primary btn-sm mobile-next-btn" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. NFC Card Orders Dashboard Block --}}
                    <div class="col-12 col-md-6 col-xl-6 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('NFC Card Orders') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="ordersTable">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Order Date') }}</th>
                                            <th>{{ __('Order ID') }}</th>
                                            <th>{{ __('Item') }}</th>
                                            <th>{{ __('Total') }}</th>
                                            <th>{{ __('Delivery Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="orders-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button class="btn btn-primary btn-sm mobile-prev-btn" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>
                                <span class="text-secondary small font-weight-medium mobile-page-info"></span>
                                <button class="btn btn-primary btn-sm mobile-next-btn" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 4. AI Credits Transactions Dashboard Block --}}
                    <div class="col-12 col-md-6 col-xl-6 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('AI Credits Transactions') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="ai-credits-transactions-table">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Payment Trans ID') }}</th>
                                            <th>{{ __('Plan') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="ai-credits-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button class="btn btn-primary btn-sm mobile-prev-btn" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>
                                <span class="text-secondary small font-weight-medium mobile-page-info"></span>
                                <button class="btn btn-primary btn-sm mobile-next-btn" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Subscription Plan Transactions Dashboard Block --}}
                    <div class="col-12 col-md-6 col-xl-6 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('Subscription Plan Transactions') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="transactions-table">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="transactions-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button class="btn btn-primary btn-sm mobile-prev-btn" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>
                                <span class="text-secondary small font-weight-medium mobile-page-info"></span>
                                <button class="btn btn-primary btn-sm mobile-next-btn" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 6. NFC Card Order Transactions Dashboard Block --}}
                    <div class="col-12 col-md-6 col-xl-6 mt-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent">
                                <h3 class="card-title">{{ __('NFC Card Order Transactions') }}</h3>
                            </div>
                            <div class="table-responsive px-3 pt-2 py-2">
                                <table class="table card-table table-vcenter text-nowrap responsive-card-table"
                                    id="nfc-transactions-table">
                                    <thead>
                                        <tr>
                                            <th class="rc-hide">{{ __('#') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Transaction') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            {{-- Custom App-like Mobile Pagination footer --}}
                            <div id="nfc-transactions-mobile-pagination"
                                class="d-flex d-md-none justify-content-between align-items-center px-3 py-3 border-top">
                                <button class="btn btn-primary btn-sm mobile-prev-btn" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-left me-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    {{ __('Previous') }}
                                </button>
                                <span class="text-secondary small font-weight-medium mobile-page-info"></span>
                                <button class="btn btn-primary btn-sm mobile-next-btn" type="button">
                                    {{ __('Next') }}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chevron-right ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Login Modal --}}
    <div class="modal modal-bg fade" id="login-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-user-pentagon mb-2 text-danger icon-lg">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M13.163 2.168l8.021 5.828c.694 .504 .984 1.397 .719 2.212l-3.064 9.43a1.978 1.978 0 0 1 -1.881 1.367h-9.916a1.978 1.978 0 0 1 -1.881 -1.367l-3.064 -9.43a1.978 1.978 0 0 1 .719 -2.212l8.021 -5.828a1.978 1.978 0 0 1 2.326 0z" />
                        <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z" />
                        <path d="M6 20.703v-.703a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.707" />
                    </svg>
                    <h3>{{ __('Are you sure login into the user?') }}</h3>
                    <div class="text-muted">{{ __('Note : If you proceed, you will lose your admin session.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <a href="{{ route('admin.login-as.customer', $user_details->user_id) }}" target="_blank"
                                    class="btn btn-danger w-100">{{ __('Yes, proceed') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Copy card to another customer modal --}}
    <div class="modal modal-bg fade" id="assign-card-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header">
                    <h3 class="modal-title">{{ __('Copy Card') }}</h3>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.assign.card') }}" method="post" id="assignForm">
                        @csrf
                        {{-- Card ID --}}
                        <input type="hidden" name="card_id" id="card_id" value="">

                        {{-- Type --}}
                        <input type="hidden" name="type" id="type" value="">

                        <div class="col-md-12 col-xl-12 px-0">
                            <div class="mb-3">
                                {{-- Customers --}}
                                <label class="form-label required">{{ __('Target Customer') }}</label>
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value='' disabled selected>{{ __('Choose a target customer...') }}
                                    </option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->user_id }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="card-footer text-end mt-2 w-100 bg-transparent border-top-0 pt-0">
                        <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-device-floppy me-2" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M14 4l0 4h-6l0 -4" />
                            </svg>
                            {{ __('Copy') }}
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS scripts pushed inside script stack --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        function loginUser(userId) {
            "use strict";
            $("#login-modal").modal("show");
        }
    </script>
    <script>
        $(document).ready(function() {
            "use strict";

            // Array of configurations for each sub-table on the profile page
            var tablesConfig = [{
                    id: '#cardsTable',
                    ajaxUrl: "{{ route('admin.view.customer', $user_details->user_id) }}",
                    type: 'cards',
                    paginator: '#cards-mobile-pagination',
                    cols: 5,
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'rc-hide'
                        },
                        {
                            data: 'created_at',
                            name: 'business_cards.created_at',
                            className: 'rc-info-start'
                        },
                        {
                            data: 'title',
                            name: 'business_cards.title',
                            className: 'rc-heading',
                            render: function(data, type, row) {
                                return '<a href="/' + row.card_url +
                                    '" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary">' +
                                    data + '</a>';
                            }
                        },
                        {
                            data: 'card_status',
                            name: 'business_cards.card_status',
                            className: 'rc-badge-top-right'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ]
                },
                {
                    id: '#storesTable',
                    ajaxUrl: "{{ route('admin.view.customer', $user_details->user_id) }}",
                    type: 'stores',
                    paginator: '#stores-mobile-pagination',
                    cols: 5,
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'rc-hide'
                        },
                        {
                            data: 'created_at',
                            name: 'business_cards.created_at',
                            className: 'rc-info-start'
                        },
                        {
                            data: 'title',
                            name: 'business_cards.title',
                            className: 'rc-heading',
                            render: function(data, type, row) {
                                return '<a href="/' + row.card_url +
                                    '" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary">' +
                                    data + '</a>';
                            }
                        },
                        {
                            data: 'card_status',
                            name: 'business_cards.card_status',
                            className: 'rc-badge-top-right'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ]
                },
                {
                    id: '#ordersTable',
                    ajaxUrl: "{{ route('admin.view.customer', $user_details->user_id) }}",
                    type: 'orders',
                    paginator: '#orders-mobile-pagination',
                    cols: 6,
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'rc-hide'
                        },
                        {
                            data: 'created_at',
                            name: 'nfc_card_orders.created_at',
                            className: 'rc-info-start'
                        },
                        {
                            data: 'nfc_card_order_id',
                            name: 'nfc_card_orders.nfc_card_order_id',
                            className: 'rc-heading',
                            render: function(data, type, row) {
                                return '<a href="/admin/nfc-card-orders/' + data +
                                    '" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary">' +
                                    data + '</a>';
                            }
                        },
                        {
                            data: 'order_item',
                            name: 'order_item',
                            className: 'rc-info-next',
                            searchable: false
                        }, // Prevent search on non-existent JSON columns
                        {
                            data: 'invoice_amount',
                            name: 'invoice_amount',
                            className: 'fw-bold text-dark rc-info-next',
                            searchable: false
                        }, // Prevent search on non-existent JSON columns
                        {
                            data: 'order_status',
                            name: 'nfc_card_orders.order_status',
                            className: 'rc-badge-top-right'
                        }
                    ]
                },
                {
                    id: '#ai-credits-transactions-table',
                    ajaxUrl: "{{ route('admin.view.customer', $user_details->user_id) }}",
                    type: 'ai_credits',
                    paginator: '#ai-credits-mobile-pagination',
                    cols: 6,
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'rc-hide'
                        },
                        {
                            data: 'created_at',
                            name: 'ai_credits_transactions.created_at',
                            className: 'rc-info-start'
                        },
                        {
                            data: 'payment_transaction_id',
                            name: 'ai_credits_transactions.payment_transaction_id',
                            className: 'rc-heading',
                            render: function(data, type, row) {
                                return '<a href="/admin/ai-credits-transactions/' + data +
                                    '" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary">' +
                                    data + '</a>';
                            }
                        },
                        {
                            data: 'plan_name',
                            name: 'ai_credits_plans.plan_name',
                            className: 'fw-bold text-dark rc-info-next'
                        },
                        {
                            data: 'amount',
                            name: 'ai_credits_transactions.amount',
                            className: 'fw-bold text-dark rc-info-next'
                        },
                        {
                            data: 'payment_status',
                            name: 'ai_credits_transactions.payment_status',
                            className: 'rc-badge-top-right'
                        }
                    ]
                },
                {
                    id: '#transactions-table',
                    ajaxUrl: "{{ route('admin.view.customer', $user_details->user_id) }}",
                    type: 'transactions',
                    paginator: '#transactions-mobile-pagination',
                    cols: 5,
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'rc-hide'
                        },
                        {
                            data: 'created_at',
                            name: 'transactions.created_at',
                            className: 'rc-info-start'
                        },
                        {
                            data: 'transaction_id',
                            name: 'transactions.transaction_id',
                            className: 'rc-heading',
                            render: function(data, type, row) {
                                if (row.payment_status === 'SUCCESS') {
                                    return '<a href="/admin/view-invoice/' + data +
                                        '" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary">' +
                                        data + '</a>';
                                }
                                return '<span class="fw-bold text-secondary">' + data + '</span>';
                            }
                        },
                        {
                            data: 'transaction_amount',
                            name: 'transactions.transaction_amount',
                            className: 'fw-bold text-dark rc-info-next'
                        },
                        {
                            data: 'payment_status',
                            name: 'transactions.payment_status',
                            className: 'rc-badge-top-right'
                        }
                    ]
                },
                {
                    id: '#nfc-transactions-table',
                    ajaxUrl: "{{ route('admin.view.customer', $user_details->user_id) }}",
                    type: 'nfc_transactions',
                    paginator: '#nfc-transactions-mobile-pagination',
                    cols: 5,
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'rc-hide'
                        },
                        {
                            data: 'created_at',
                            name: 'nfc_card_order_transactions.created_at',
                            className: 'rc-info-start'
                        },
                        {
                            data: 'nfc_card_order_transaction_id',
                            name: 'nfc_card_order_transactions.nfc_card_order_transaction_id',
                            className: 'rc-heading',
                            render: function(data, type, row) {
                                if (row.payment_status === 'success') {
                                    return '<a href="/admin/nfc-card-orders/transactions/' + data +
                                        '" target="_blank" class="fw-bold text-decoration-none text-dark hover-primary">' +
                                        data + '</a>';
                                }
                                return '<span class="fw-bold text-secondary">' + data + '</span>';
                            }
                        },
                        {
                            data: 'amount',
                            name: 'nfc_card_order_transactions.amount',
                            className: 'fw-bold text-dark rc-info-next'
                        },
                        {
                            data: 'payment_status',
                            name: 'nfc_card_order_transactions.payment_status',
                            className: 'rc-badge-top-right'
                        }
                    ]
                }
            ];

            tablesConfig.forEach(function(config) {
                var el = $(config.id);
                if (!el.length) return;

                var table = el.DataTable({
                    processing: false,
                    serverSide: true, // Enabled true server side processing
                    ajax: {
                        url: config.ajaxUrl,
                        data: {
                            type: config.type
                        }
                    },
                    order: [
                        [1, 'desc']
                    ], // Default sorting target to Column 1 (created_at) on load
                    columns: config.columns,
                    language: {
                        "sProcessing": `{{ __('Processing...') }}`,
                        "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                        "sSearch": ``,
                        "sSearchPlaceholder": `{{ __('Search') }}`,
                        "oPaginate": {
                            "sNext": `{{ __('Next') }}`,
                            "sPrevious": `{{ __('Previous') }}`
                        },
                        "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                        "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                        "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                        "sInfoPostFix": "",
                        "sUrl": "",
                        "oAria": {
                            "sSortAscending": `{{ __(': activate to sort column in ascending order') }}`,
                            "sSortDescending": `{{ __(': activate to sort column in descending order') }}`
                        },
                        loadingRecords: `{{ __('Please wait - loading...') }}`,
                        emptyTable: `{{ __('No data available in the table') }}`
                    },
                    preDrawCallback: function(settings) {
                        el.closest('.card').addClass('placeholder-glow');

                        var placeholderRows = '';
                        for (var i = 0; i < 3; i++) {
                            placeholderRows += '<tr>' +
                                '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                                .repeat(config.cols) + '</tr>';
                        }
                        el.find('tbody').html(placeholderRows);
                    },
                    drawCallback: function(settings) {
                        el.closest('.card').removeClass('placeholder-glow');
                        el.find('tbody tr td.dataTables_empty').attr('colspan', config.cols);

                        // Dynamic header loop
                        var headers = [];
                        el.find('thead th').each(function() {
                            headers.push($(this).text().trim());
                        });

                        el.find('tbody tr').each(function() {
                            $(this).find('td').each(function(index) {
                                var label = headers[index] || '';
                                $(this).attr('data-label', label);
                            });
                        });

                        // Update the Custom Mobile Pagination Footer State dynamically
                        const api = this.api();
                        const pageInfo = api.page.info();
                        const rowsCount = api.rows({
                            page: 'current'
                        }).data().length;

                        const paginatorEl = $(config.paginator);
                        if (paginatorEl.length) {
                            if (pageInfo.pages <= 1 || rowsCount === 0) {
                                paginatorEl.addClass('d-none').removeClass('d-flex');
                            } else {
                                paginatorEl.removeClass('d-none').addClass('d-flex');
                            }

                            const pageInfoText = `{{ __('Page :current of :total') }}`
                                .replace(':current', pageInfo.page + 1)
                                .replace(':total', pageInfo.pages || 1);
                            paginatorEl.find('.mobile-page-info').text(pageInfoText);

                            const prevBtn = paginatorEl.find('.mobile-prev-btn');
                            if (pageInfo.page === 0) {
                                prevBtn.addClass('disabled').prop('disabled', true);
                            } else {
                                prevBtn.removeClass('disabled').prop('disabled', false);
                            }

                            const nextBtn = paginatorEl.find('.mobile-next-btn');
                            if (pageInfo.page >= pageInfo.pages - 1) {
                                nextBtn.addClass('disabled').prop('disabled', true);
                            } else {
                                nextBtn.removeClass('disabled').prop('disabled', false);
                            }
                        }
                    }
                });

                // Bind click events on Mobile pagination triggers
                $(`${config.paginator} .mobile-prev-btn`).on('click', function(e) {
                    e.preventDefault();
                    table.page('previous').draw('page');
                });

                $(`${config.paginator} .mobile-next-btn`).on('click', function(e) {
                    e.preventDefault();
                    table.page('next').draw('page');
                });

                // Trigger skeleton glows when table AJAX operations are triggered
                el.on('processing.dt', function(e, settings, processing) {
                    var $responsiveWrapper = $(this).closest('.table-responsive');
                    if (processing) {
                        $responsiveWrapper.addClass('placeholder-glow');
                    } else {
                        $responsiveWrapper.removeClass('placeholder-glow');
                    }
                });

                // Delegated touch-input filtering to catch search queries cleanly on any mobile viewport
                var searchTimer;
                el.closest('.card').find('.dataTables_filter input').off('keyup search input paste cut').on(
                    'input',
                    function() {
                        var value = this.value;
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(function() {
                            table.search(value).draw();
                        }, 350);
                    });
            });
        });

        // Copy card to another customer helper
        function assignCard(cardId, type) {
            "use strict";
            $("#assign-card-modal").modal("show");

            var cardIdInput = document.getElementById("card_id");
            cardIdInput.value = cardId;

            var typeInput = document.getElementById("type");
            typeInput.value = type;
        }

        // Array of element IDs to initialize with TomSelect
        var elementSelectors = ['customer_id'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            });

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                initializeTomSelectWithRequired(el);
            }
        });
    </script>
@endsection
