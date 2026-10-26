@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control {
            line-height: 1.7 !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Standard Tabler Breadcrumbs (Home › Dashboard › Change Password) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.account') }}" class="text-secondary text-decoration-none">
                                    {{ __('My Account') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Change Password') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Change Password') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('Establish a secure, customized password to protect your administrator workspace credentials.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- SIDEBAR PANEL (Only two options) --}}
                        <div class="col-12 col-md-3 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3">{{ __('Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    <a href="{{ route('admin.account') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center text-secondary">
                                        {{ __('My Account') }}
                                    </a>
                                    <a href="#"
                                        class="list-group-item list-group-item-action d-flex align-items-center active">
                                        {{ __('Change Password') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- WORKSPACE PANEL --}}
                        <div class="col-12 col-md-9 d-flex flex-column">
                            <form action="{{ route('admin.update.password') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title text-secondary mb-3">{{ __('Security Credentials') }}</h3>
                                    <div class="row g-3 mb-4">
                                        {{-- New Password --}}
                                        <div class="col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="new_password">{{ __('New Password') }}</label>
                                                <input type="password" class="form-control" name="new_password"
                                                    id="new_password" placeholder="{{ __('Enter new password') }}"
                                                    required>
                                            </div>
                                        </div>

                                        {{-- Confirm Password --}}
                                        <div class="col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label required"
                                                    for="confirm_password">{{ __('Confirm Password') }}</label>
                                                <input type="password" class="form-control" name="confirm_password"
                                                    id="confirm_password" placeholder="{{ __('Confirm new password') }}"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Double Button Footer Layout --}}
                                <div class="card-footer bg-transparent mt-auto border-top text-end">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary btn-md ms-auto">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="24"
                                                height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4h-6l0 -4" />
                                            </svg>
                                            {{ __('Change Password') }}
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
        @include('admin.includes.footer')
    </div>
@endsection
