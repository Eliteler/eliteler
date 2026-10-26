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
                    <!-- Standard Tabler Breadcrumbs (Home › Dashboard › My Account) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('My Account') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('My Account') }}
                        </h2>
                        <!-- Header Helper Description -->
                        <div class="text-muted mt-1">
                            {{ __('View and manage your account information, update your profile, and change your password.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed --}}
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

                {{-- Success --}}
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

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- SIDEBAR PANEL (Only two options) --}}
                        <div class="col-12 col-md-3 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3">{{ __('Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    <a href="#"
                                        class="list-group-item list-group-item-action d-flex align-items-center active">
                                        {{ __('My Account') }}
                                    </a>
                                    <a href="{{ route('admin.change.password') }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center text-secondary">
                                        {{ __('Change Password') }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- WORKSPACE PANEL --}}
                        <div class="col-12 col-md-9 d-flex flex-column">
                            <form action="{{ route('admin.update.account') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title text-secondary mb-3">{{ __('Profile Details') }}</h3>
                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-4 col-xl-4">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Profile Picture') }}</div>
                                                <input type="file" class="form-control" name="profile_picture"
                                                    placeholder="{{ __('Choose a profile picture') }}"
                                                    accept=".jpeg,.jpg,.png,.gif,.svg" />
                                                <small
                                                    class='text-muted'>{{ __('Recommended : 200 x 200 pixels or 500 x 500 pixels') }}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-xl-8"></div>
                                        <div class="col-md-4 col-xl-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Name') }}</label>
                                                <input type="text" class="form-control" name="name"
                                                    placeholder="{{ __('Enter your name') }}"
                                                    value="{{ $account_details->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-xl-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Email') }}</label>
                                                <input type="text" class="form-control" name="email"
                                                    placeholder="{{ __('Enter your email') }}"
                                                    value="{{ $account_details->email }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Actions --}}
                                <div class="card-footer bg-transparent mt-auto border-top">
                                    <div class="btn-list justify-content-end">
                                        <button type="submit" class="btn btn-primary">
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
        @include('admin.includes.footer')
    </div>
@endsection
