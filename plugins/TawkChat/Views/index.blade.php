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
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'Tawk.to Live Chat'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('Tawk.to Live Chat') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your Tawk.to live chat widget integration to provide instant customer support capabilities globally.') }}
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
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                {{-- Success Alert --}}
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
                    <div class="col-12 col-md-6">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form action="{{ route('admin.tawkchat_settings.update') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                                <h3 class="card-title fw-bold m-0">{{ __('Tawk.to Live Chat Credentials') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        {{-- Tawk Chat Widget Key --}}
                                        <div class="mb-3">
                                            <label
                                                class="form-label required">{{ __('Tawk.to Chatbot URL (s1.src)') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text text-muted">
                                                    {{ __('https://embed.tawk.to/') }}
                                                </span>
                                                <input type="text" class="form-control" name="tawk_chat_bot_key"
                                                    value="{{ $settings->tawk_chat_bot_key }}"
                                                    placeholder="{{ __('Enter your property key (e.g., xxxxxxxx/default)') }}"
                                                    autocomplete="off" required>
                                            </div>
                                        </div>
                                        <span class="small text-muted d-block mt-1">
                                            {{ __('If you do not have a Tawk.to live chat widget, you can register a') }}
                                            <a href="https://www.tawk.to/" target="_blank" class="text-decoration-none">
                                                {{ __('new Tawk.to account.') }}
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <!-- Device Floppy Icon -->
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection
