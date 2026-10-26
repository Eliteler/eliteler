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
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.marketing.pusher.notification') }}" class="text-secondary text-decoration-none">
                                    {{ __('Web Notifications') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Notification') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Notification') }}
                        </h2>
                        <!-- Sub-heading and inline setup documentation link -->
                        <div class="text-muted mt-1">
                            {{ __('Send a push notification to all subscribed users.') }}
                            <span class="ms-1">{{ __('For configuration guides,') }} {!! __(
                                '<a href="https://nativecode.in/docs/gobiz/setup/how-to-get-pusher-beams-instance-id-and-secret-key.html" target="_blank" class="link-primary text-decoration-none fw-bold">click here</a>',
                            ) !!}.</span>
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
                    <div class="col-md-6 col-sm-12">
                        <form action="{{ route('admin.marketing.pusher.notification.send') }}" method="post"
                            class="card border-0 shadow-sm" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Title --}}
                                    <div class="col-md-12 col-lg-6">
                                        <div>
                                            <label class="form-label required" for="title">{{ __('Title') }}</label>
                                            <input type="text" class="form-control" name="title" id="title"
                                                value="{{ old('title') }}"
                                                placeholder="{{ __('Enter notification title') }}" required>
                                        </div>
                                    </div>

                                    {{-- Message --}}
                                    <div class="col-md-12 col-lg-6">
                                        <div>
                                            <label class="form-label required" for="message">{{ __('Message') }}</label>
                                            <input type="text" class="form-control" name="message" id="message"
                                                value="{{ old('message') }}" placeholder="{{ __('Enter your message') }}"
                                                required>
                                        </div>
                                    </div>

                                    {{-- Upload Image --}}
                                    <div class="col-md-12 col-lg-6">
                                        <div>
                                            <label class="form-label required"
                                                for="image">{{ __('Upload Image') }}</label>
                                            <input type="file" class="form-control" name="image" id="image"
                                                accept="image/png, image/jpeg, image/jpg"
                                                placeholder="{{ __('Choose an image') }}" required>
                                        </div>
                                    </div>

                                    {{-- Target URL --}}
                                    <div class="col-md-12 col-lg-6">
                                        <div>
                                            <label class="form-label" for="target_url">{{ __('Target URL') }}</label>
                                            <input type="url" class="form-control" name="target_url" id="target_url"
                                                value="{{ old('target_url') }}"
                                                placeholder="{{ __('Destination URL (Optional)') }}">
                                            <small
                                                class="text-muted d-block mt-1">{{ __('Users will be directed to this page when they tap the notification.') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Single Button Footer Layout --}}
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-send me-2"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 14l11 -11" />
                                        <path
                                            d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                    </svg>
                                    {{ __('Send') }}
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
