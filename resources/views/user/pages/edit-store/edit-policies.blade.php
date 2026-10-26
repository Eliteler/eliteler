@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-policies.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Navigation -->
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-muted d-inline-flex align-items-center gap-1">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.stores') }}" class="text-muted">{{ __('Stores') }}</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Policies') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Store Legal Policies') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Manage your store\'s legal terms, privacy standards, return parameters, and customer service agreements.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'policies',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Policies Form --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store.policies') }}" method="POST" id="policiesForm">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $business_card->card_id }}">

                                <div
                                    class="card-header border-bottom bg-transparent py-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="card-title fw-bold mb-0">{{ __('Legal & Compliance Documents') }}</h3>
                                        <small
                                            class="text-muted">{{ __('Click any section below to expand and edit document contents') }}</small>
                                    </div>
                                    <span
                                        class="badge bg-primary-lt d-none d-sm-inline-block">{{ __('6 Policy Documents') }}</span>
                                </div>

                                <div class="card-body p-4">
                                    <div class="accordion" id="storePoliciesAccordion">
                                        {{-- 1. Privacy Policy --}}
                                        <div class="policy-accordion-item">
                                            <button class="policy-accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#privacyCollapse"
                                                aria-expanded="false">
                                                <div class="d-flex align-items-center">
                                                    <span class="policy-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-shield-lock" width="20"
                                                            height="20" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
                                                            <path d="M12 11m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                            <path d="M12 12l0 2.5" />
                                                        </svg>
                                                    </span>
                                                    <div class="text-start">
                                                        <div class="text-reset">{{ __('Privacy Policy') }}</div>
                                                        <small
                                                            class="text-muted fw-normal">{{ __('Explain how customer personal data is collected and protected.') }}</small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ !empty($business_card->privacy_policy) ? 'bg-success-lt' : 'bg-secondary-lt' }} ms-auto me-2">
                                                    {{ !empty($business_card->privacy_policy) ? __('Configured') : __('Empty') }}
                                                </span>
                                            </button>
                                            <div id="privacyCollapse" class="accordion-collapse collapse"
                                                data-bs-parent="#storePoliciesAccordion">
                                                <div class="p-3 bg-surface">
                                                    <textarea class="form-control policy-editor" id="privacy-policy-textarea" name="privacy_policy_textarea"
                                                        placeholder="{{ __('Enter your store privacy policy...') }}">{{ $business_card->privacy_policy }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. Terms and Conditions --}}
                                        <div class="policy-accordion-item">
                                            <button class="policy-accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#termsCollapse"
                                                aria-expanded="false">
                                                <div class="d-flex align-items-center">
                                                    <span class="policy-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-file-text" width="20"
                                                            height="20" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                            <path
                                                                d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                            <path d="M9 9l1 0" />
                                                            <path d="M9 13l6 0" />
                                                            <path d="M9 17l6 0" />
                                                        </svg>
                                                    </span>
                                                    <div class="text-start">
                                                        <div class="text-reset">{{ __('Terms and Conditions') }}</div>
                                                        <small
                                                            class="text-muted fw-normal">{{ __('General store usage rules, guidelines, and contractual agreement.') }}</small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ !empty($business_card->terms_and_conditions) ? 'bg-success-lt' : 'bg-secondary-lt' }} ms-auto me-2">
                                                    {{ !empty($business_card->terms_and_conditions) ? __('Configured') : __('Empty') }}
                                                </span>
                                            </button>
                                            <div id="termsCollapse" class="accordion-collapse collapse"
                                                data-bs-parent="#storePoliciesAccordion">
                                                <div class="p-3 bg-surface">
                                                    <textarea class="form-control policy-editor" id="terms-and-conditions-textarea" name="terms_and_conditions_textarea"
                                                        placeholder="{{ __('Enter your terms and conditions...') }}">{{ $business_card->terms_and_conditions }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 3. Return / Refund Policy --}}
                                        <div class="policy-accordion-item">
                                            <button class="policy-accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#refundCollapse"
                                                aria-expanded="false">
                                                <div class="d-flex align-items-center">
                                                    <span class="policy-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-receipt-refund"
                                                            width="20" height="20" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                                            <path d="M15 14v-2a2 2 0 0 0 -2 -2h-4l2 -2m0 4l-2 -2" />
                                                        </svg>
                                                    </span>
                                                    <div class="text-start">
                                                        <div class="text-reset">{{ __('Return & Refund Policy') }}</div>
                                                        <small
                                                            class="text-muted fw-normal">{{ __('Define replacement windows, warranty terms, and refund eligibility.') }}</small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ !empty($business_card->refund_policy) ? 'bg-success-lt' : 'bg-secondary-lt' }} ms-auto me-2">
                                                    {{ !empty($business_card->refund_policy) ? __('Configured') : __('Empty') }}
                                                </span>
                                            </button>
                                            <div id="refundCollapse" class="accordion-collapse collapse"
                                                data-bs-parent="#storePoliciesAccordion">
                                                <div class="p-3 bg-surface">
                                                    <textarea class="form-control policy-editor" id="refund-policy-textarea" name="refund_policy_textarea"
                                                        placeholder="{{ __('Enter your refund and return policy...') }}">{{ $business_card->refund_policy }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 4. Shipping Policy --}}
                                        <div class="policy-accordion-item">
                                            <button class="policy-accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#shippingCollapse"
                                                aria-expanded="false">
                                                <div class="d-flex align-items-center">
                                                    <span class="policy-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-truck-delivery"
                                                            width="20" height="20" viewBox="0 0 24 24"
                                                            stroke-width="2" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                            <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                            <path
                                                                d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" />
                                                            <path d="M3 9l4 0" />
                                                        </svg>
                                                    </span>
                                                    <div class="text-start">
                                                        <div class="text-reset">{{ __('Shipping & Delivery Policy') }}
                                                        </div>
                                                        <small
                                                            class="text-muted fw-normal">{{ __('Delivery estimated timelines, shipping rates, and dispatch rules.') }}</small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ !empty($business_card->shipping_policy) ? 'bg-success-lt' : 'bg-secondary-lt' }} ms-auto me-2">
                                                    {{ !empty($business_card->shipping_policy) ? __('Configured') : __('Empty') }}
                                                </span>
                                            </button>
                                            <div id="shippingCollapse" class="accordion-collapse collapse"
                                                data-bs-parent="#storePoliciesAccordion">
                                                <div class="p-3 bg-surface">
                                                    <textarea class="form-control policy-editor" id="shipping-policy-textarea" name="shipping_policy_textarea"
                                                        placeholder="{{ __('Enter your shipping policy...') }}">{{ $business_card->shipping_policy }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 5. Cookie Policy --}}
                                        <div class="policy-accordion-item">
                                            <button class="policy-accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#cookieCollapse"
                                                aria-expanded="false">
                                                <div class="d-flex align-items-center">
                                                    <span class="policy-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20"
                                                            height="20" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-cookie">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M8 13v.01" />
                                                            <path d="M12 17v.01" />
                                                            <path d="M12 12v.01" />
                                                            <path d="M16 14v.01" />
                                                            <path d="M11 8v.01" />
                                                            <path
                                                                d="M13.148 3.476l2.667 1.104a4 4 0 0 0 4.656 6.14l.053 .132a3 3 0 0 1 0 2.296q -.745 1.18 -1.024 1.852q -.283 .684 -.66 2.216a3 3 0 0 1 -1.624 1.623q -1.572 .394 -2.216 .661q -.712 .295 -1.852 1.024a3 3 0 0 1 -2.296 0q -1.203 -.754 -1.852 -1.024q -.707 -.292 -2.216 -.66a3 3 0 0 1 -1.623 -1.624q -.397 -1.577 -.661 -2.216q -.298 -.718 -1.024 -1.852a3 3 0 0 1 0 -2.296q .719 -1.116 1.024 -1.852q .257 -.62 .66 -2.216a3 3 0 0 1 1.624 -1.623q 1.547 -.384 2.216 -.661q .687 -.285 1.852 -1.024a3 3 0 0 1 2.296 0" />
                                                        </svg>
                                                    </span>
                                                    <div class="text-start">
                                                        <div class="text-reset">{{ __('Cookie Policy') }}</div>
                                                        <small
                                                            class="text-muted fw-normal">{{ __('Explain browser cookies and local tracking technologies used.') }}</small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ !empty($business_card->cookie_policy) ? 'bg-success-lt' : 'bg-secondary-lt' }} ms-auto me-2">
                                                    {{ !empty($business_card->cookie_policy) ? __('Configured') : __('Empty') }}
                                                </span>
                                            </button>
                                            <div id="cookieCollapse" class="accordion-collapse collapse"
                                                data-bs-parent="#storePoliciesAccordion">
                                                <div class="p-3 bg-surface">
                                                    <textarea class="form-control policy-editor" id="cookie-policy-textarea" name="cookie_policy_textarea"
                                                        placeholder="{{ __('Enter your cookie policy...') }}">{{ $business_card->cookie_policy }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 6. Customer Support Policy --}}
                                        <div class="policy-accordion-item">
                                            <button class="policy-accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#supportCollapse"
                                                aria-expanded="false">
                                                <div class="d-flex align-items-center">
                                                    <span class="policy-icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20"
                                                            height="20" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-headset">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M4 14v-3a8 8 0 1 1 16 0v3" />
                                                            <path d="M18 19c0 1.657 -2.686 3 -6 3" />
                                                            <path
                                                                d="M4 14a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2v-3" />
                                                            <path
                                                                d="M15 14a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2v-3" />
                                                        </svg>
                                                    </span>
                                                    <div class="text-start">
                                                        <div class="text-reset">
                                                            {{ __('Customer Support & Contact Info') }}</div>
                                                        <small
                                                            class="text-muted fw-normal">{{ __('Provide formal customer service hours, email, and dispute channels.') }}</small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ !empty($business_card->customer_support_policy) ? 'bg-success-lt' : 'bg-secondary-lt' }} ms-auto me-2">
                                                    {{ !empty($business_card->customer_support_policy) ? __('Configured') : __('Empty') }}
                                                </span>
                                            </button>
                                            <div id="supportCollapse" class="accordion-collapse collapse"
                                                data-bs-parent="#storePoliciesAccordion">
                                                <div class="p-3 bg-surface">
                                                    <textarea class="form-control policy-editor" id="customer-support-policy-textarea"
                                                        name="customer_support_policy_textarea" placeholder="{{ __('Enter your customer support policies...') }}">{{ $business_card->customer_support_policy }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Symmetrical Action Footer -->
                                <div class="card-footer bg-transparent border-top p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('user.stores') }}"
                                            class="btn btn-danger d-inline-flex align-items-center gap-1">
                                            {{ __('Cancel') }}
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4l-4 0l0 -4" />
                                            </svg>
                                            {{ __('Save') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@push('custom-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

            tinymce.init({
                selector: '.policy-editor',
                plugins: 'code preview searchreplace autolink autosave link charmap lists wordcount',
                menubar: false,
                statusbar: false,
                height: 240,
                toolbar: 'undo redo | bold italic underline | blocks | alignleft aligncenter alignjustify | bullist numlist | link code | removeformat',
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                content_style: `body { font-family: "Figtree", sans-serif; font-size: 14px; }`
            });
        });
    </script>
@endpush
