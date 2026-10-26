@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry) --}}
@section('css')
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Trail -->
                    <nav aria-label="breadcrumb">
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
                                {{ __('Contact & Inquiry Form') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Contact & Inquiry Form') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Toggle inquiry features, customize automatic confirmation workflows, and verify notification endpoints.') }}
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

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Resolved chaotic card nesting) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.contact.form', Request::segment(3)) }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Hide Contact Form toggle --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                    for="contact-form">{{ __('Hide Contact / Inquiry Form') }}</label>
                                                <label class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        onchange="displayContactForm()" name="contact_form"
                                                        id="contact-form">
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Email configuration layout block --}}
                                        <div class="col-md-6" id="contactForm">
                                            <div class="mb-1">
                                                <label class="form-label required"
                                                    for="receive_email">{{ __('Want to receive email') }}</label>
                                                <input type="email" class="form-control" name="receive_email"
                                                    id="receive_email" value="{{ old('receive_email') }}"
                                                    placeholder="{{ __('Enter email address') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer containing standard save trigger styled alone -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
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
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script>
        function displayContactForm() {
            "use strict";
            var disp = $('input[name="contact_form"]:checked').length;
            if (disp == 0) {
                $("#contactForm").show();
                $('#receive_email').attr('required', 'required');
            } else {
                $("#contactForm").hide();
                $('#receive_email').removeAttr('required');
            }
        }
    </script>
@endpush
