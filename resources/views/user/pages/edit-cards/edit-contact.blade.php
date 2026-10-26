@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Modern Tabler Design & Typography --}}
@section('css')
    {{-- Contact CSS --}}
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}" />
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Contact Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Contact Settings') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Toggle custom inquiry layers, define confirmation rules, and modify automatic email notification receptors.') }}
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
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar Navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'contact',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.contact.form', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body p-3 p-lg-3">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">
                                            {{ __('Contact & Inquiry Form Settings') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Manage the contact form visibility and specify email destinations for user inquiries.') }}
                                        </p>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Section 1: Hide Contact Form Toggle Switch Card -->
                                        <div class="col-12">
                                            <div class="settings-switch-card">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 text-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="28"
                                                                height="28" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-message">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M8 9h8" />
                                                                <path d="M8 13h6" />
                                                                <path
                                                                    d="M18 4a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-5l-5 3v-3h-2a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h4 class="fw-bold text-dark mb-1">
                                                                {{ __('Hide Contact / Inquiry Form') }}</h4>
                                                            <p class="text-muted small mb-0">
                                                                {{ __('Turn this on if you want to completely hide the inquiry form on your digital business card.') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch m-0 ms-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            onchange="displayContactForm()" name="contact_form"
                                                            id="contact-form"
                                                            {{ $business_card->enquiry_email == null ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 2: Contact Form Email Configuration -->
                                        <div class="col-12" id="contactForm">
                                            <div class="contact-section-card">
                                                <div class="mb-3">
                                                    <h4 class="fw-bold text-dark mb-1">
                                                        {{ __('Inquiry Email Configuration') }}</h4>
                                                    <p class="text-muted small mb-0">
                                                        {{ __('Enter the email address where customer inquiries and lead notifications should be delivered.') }}
                                                    </p>
                                                </div>

                                                <div class="row">
                                                    <div class="col-12 col-md-8 col-lg-4">
                                                        <label class="form-label required" for="receive_email">
                                                            {{ __('Email address to receive booking notifications') }}
                                                        </label>
                                                        <div class="input-icon">
                                                            <span class="input-icon-addon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                    width="20" height="20" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path
                                                                        d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                                    <path d="M3 7l9 6l9 -6" />
                                                                </svg>
                                                            </span>
                                                            <input type="email" class="form-control"
                                                                name="receive_email" id="receive_email"
                                                                value="{{ $business_card->enquiry_email }}"
                                                                placeholder="{{ __('Enter email address') }}">
                                                        </div>
                                                        <small class="text-muted mt-1 d-block">
                                                            {{ __('Make sure this is an active email address to avoid missing leads.') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-between py-3">
                                    <a href="{{ route('user.cards') }}"
                                        class="btn btn-danger text-decoration-none fw-semibold">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
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

@push('custom-js')
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            displayContactForm();
        });

        function displayContactForm() {
            const contactForm = document.getElementById('contactForm');
            const contactCheckbox = document.getElementById('contact-form');
            const emailInput = document.getElementById('receive_email');

            if (!contactForm || !contactCheckbox || !emailInput) return;

            if (contactCheckbox.checked) {
                contactForm.classList.add('d-none');
                emailInput.required = false;
            } else {
                contactForm.classList.remove('d-none');
                emailInput.required = true;
            }
        }
    </script>
@endpush
