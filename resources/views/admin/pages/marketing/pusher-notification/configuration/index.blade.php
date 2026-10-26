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
                                <a href="{{ route('admin.marketing.pusher.notification') }}"
                                    class="text-secondary text-decoration-none">
                                    {{ __('Web Notifications') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Pusher Configuration') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Pusher Configuration') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Connect your Pusher Beams account by entering the required credentials to enable real-time push notifications.') }}
                            <span class="ms-1">{{ __('How to configure Pusher notifications?') }}
                                {!! __(
                                    '<a href="https://nativecode.in/docs/gobiz/setup/how-to-get-pusher-beams-instance-id-and-secret-key.html" target="_blank" class="link-primary text-decoration-none fw-bold">Click here</a>',
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
                        <form action="{{ route('admin.marketing.pusher.update') }}" method="post"
                            class="card border-0 shadow-sm" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Enable Beams (Form Switch) --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <label class="form-label mb-0"
                                                for="enable_beams">{{ __('Enable Beams') }}</label>
                                            <label class="form-check form-switch mb-0">
                                                <!-- Hidden input sends '0' if the checkbox is unchecked -->
                                                <input type="hidden" name="enable_beams" value="0">
                                                <input class="form-check-input" type="checkbox" name="enable_beams"
                                                    id="enable_beams" value="1"
                                                    {{ env('PUSHER_BEAMS_ENABLED') == '1' ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Beams Instance ID --}}
                                    <div class="col-12">
                                        <div>
                                            <label class="form-label required"
                                                for="beams_instance_id">{{ __('Beams Instance ID') }}</label>
                                            <input type="text" class="form-control" name="beams_instance_id"
                                                id="beams_instance_id" value="{{ env('PUSHER_BEAMS_INSTANCE_ID') }}"
                                                placeholder="{{ __('Enter your Beams Instance ID') }}" required>
                                        </div>
                                    </div>

                                    {{-- Beams Secret Key --}}
                                    <div class="col-12">
                                        <div>
                                            <label class="form-label required"
                                                for="beams_secret_key">{{ __('Beams Secret Key') }}</label>
                                            <input type="text" class="form-control" name="beams_secret_key"
                                                id="beams_secret_key" value="{{ env('PUSHER_BEAMS_SECRET_KEY') }}"
                                                placeholder="{{ __('Enter your Beams Secret Key') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Layout with Save Button on Right Side --}}
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
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

        <!-- Footer -->
        @include('admin.includes.footer')
    </div>
@endsection
