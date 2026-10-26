@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.users') }}" class="text-secondary text-decoration-none">
                                    {{ __('Administrators & Staff') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __($user_details->name) }}</span>
                            {{ __('Details') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Update the details and permissions of a user.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form action="{{ route('admin.update.user') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    <input type="hidden" name="user_id" value="{{ $user_details->user_id }}">

                                    {{-- Role select field --}}
                                    <div class="col-md-6 col-lg-3">
                                        <div class="mb-3">
                                            <div class="form-label required">{{ __('Role') }}</div>
                                            <select class="form-select role" name="role" required>
                                                <option value="">{{ __('Choose a role') }}</option>
                                                <option value="3" {{ $user_details->role_id == 3 ? 'selected' : '' }}>
                                                    {{ __('Administrator') }}</option>
                                                <option value="4" {{ $user_details->role_id == 4 ? 'selected' : '' }}>
                                                    {{ __('Manager') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Name --}}
                                    <div class="col-md-6 col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Name') }}</label>
                                            <input type="text" class="form-control" name="name"
                                                value="{{ $user_details->name }}" placeholder="{{ __('Enter the Name') }}"
                                                required>
                                        </div>
                                    </div>

                                    {{-- Email --}}
                                    <div class="col-md-6 col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Email') }}</label>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="{{ __('Enter the Email') }}"
                                                value="{{ $user_details->email }}" required>
                                        </div>
                                    </div>

                                    {{-- Password --}}
                                    <div class="col-md-6 col-lg-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Password') }}</label>
                                            <input type="password" class="form-control" name="password"
                                                placeholder="{{ __('Enter a new password') }}">
                                        </div>
                                    </div>

                                    {{-- Permissions Board --}}
                                    <div class="col-12">
                                        <div class="row g-3">
                                            {{-- Permissions with Sub-heading Helper Text --}}
                                            <div class="col-12 mt-4 mb-2">
                                                <h3 class="card-title mb-1">
                                                    {{ __('Permissions') }}
                                                </h3>
                                                <div class="text-muted small">
                                                    {{ __('Choose the permissions you want to grant this user.') }}
                                                </div>
                                            </div>

                                            {{-- Permission Options Grid --}}
                                            <div class="row g-2">
                                                @php
                                                    $permissions = [
                                                        'themes' => __('Themes'),
                                                        'plans' => __('Plans'),
                                                        'customers' => __('Customers'),
                                                        'payment_methods' => __('Payment Methods'),
                                                        'coupons' => __('Coupons'),
                                                        'currencies' => __('Currencies'),
                                                        'transactions' => __('Transactions'),
                                                        'web_templates' => __('Web Templates'),
                                                        'blogs' => __('Blogs'),
                                                        'users' => __('Users'),
                                                        'custom_domain' => __('Custom Domains'),
                                                        'backup' => __('Backup'),
                                                        'general_settings' => __('General Settings'),
                                                        'translations' => __('Translations'),
                                                        'marketing' => __('Marketing'),
                                                        'sitemap' => __('Generate Sitemap'),
                                                        'invoice_tax' => __('Invoice & Tax'),
                                                        'maintenance_mode' => __('Maintenance Mode'),
                                                        'demo_mode' => __('Demo Mode'),
                                                        'software_update' => __('Software Update'),
                                                        'nfc_card_design' => __('NFC Card Design'),
                                                        'nfc_card_orders' => __('NFC Card Orders'),
                                                        'nfc_card_order_transactions' => __(
                                                            'NFC Card Order Transactions',
                                                        ),
                                                        'nfc_card_key_generations' => __('NFC Card Key Generations'),
                                                        'referral_system' => __('Referral System'),
                                                        'email_templates' => __('Email Templates'),
                                                        'plugins' => __('Plugins'),
                                                        'in_app_purchases' => __('In App Purchases'),
                                                        'vcard_store' => __('Admin Digital Business Cards & Stores'),
                                                        'business_card_intros' => __('Business Card Intros'),
                                                        'ai_credits' => __('AI Credits'),
                                                    ];

                                                    // Decode user permissions safely
                                                    $userPermissions =
                                                        json_decode($user_details->permissions, true) ?? [];
                                                @endphp

                                                @foreach ($permissions as $key => $label)
                                                    <div class="col-md-3 col-lg-4 col-xl-3">
                                                        <label class="form-check form-switch mt-2">
                                                            <input type="hidden" name="{{ $key }}"
                                                                value="0">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{ $key }}" name="{{ $key }}"
                                                                value="1"
                                                                {{ isset($userPermissions[$key]) && $userPermissions[$key] === 1 ? 'checked' : '' }}>
                                                            <span class="form-check-label">{{ $label }}</span>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Simplified card footer with single device-floppy button (No Cancel Button) -->
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                        <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                        <path d="M14 4l0 4h-6l0 -4" />
                                    </svg>
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        // Array of element selectors (Optimized to prevent unnecessary lookups)
        var elementSelectors = ['.role'];

        // Function to initialize TomSelect on an element
        function initializeTomSelect(el) {
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
        }

        // Initialize TomSelect on existing elements
        elementSelectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            elements.forEach(function(el) {
                initializeTomSelect(el);
            });
        });

        // Observe the document for dynamically added elements
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Ensure it's an element node
                        elementSelectors.forEach(function(selector) {
                            if (node.matches(selector)) {
                                initializeTomSelect(node);
                            }
                            // Also check if new nodes have children that match
                            var childElements = node.querySelectorAll(selector);
                            childElements.forEach(function(childEl) {
                                initializeTomSelect(childEl);
                            });
                        });
                    }
                });
            });
        });

        // Configure the observer
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
@endsection
