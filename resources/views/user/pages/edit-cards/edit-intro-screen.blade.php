@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Modern Tabler Design & Typography --}}
@section('css')
    {{-- Intro Screen CSS --}}
    <link rel="stylesheet" href="{{ asset('css/intro-screen.css') }}">
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
                                {{ __('Intro Screens') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Intro Screens') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Select interactive introductory splash-screen templates to capture user focus upon launch.') }}
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
                                        'link' => 'intro-screen',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.intro-screen', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body p-3 p-lg-4">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">{{ __('Choose Intro Splash Screen') }}
                                        </h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Choose an animated splash screen template displayed before your digital card opens, or disable it.') }}
                                        </p>
                                    </div>

                                    <!-- Intro Screen Templates Grid -->
                                    <div class="row g-3 g-lg-4">
                                        {{-- Option 1: None (Disabled) --}}
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <label class="w-100 h-100 m-0">
                                                <input type="radio" name="intro_screen" value="none"
                                                    class="d-none intro-radio"
                                                    {{ $business_card->intro_screen == null ? 'checked' : '' }}>
                                                <div class="intro-selection-card intro-none-card">
                                                    <span class="selected-badge">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                            width="16" height="16" viewBox="0 0 24 24"
                                                            stroke-width="3" stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M5 12l5 5l10 -10" />
                                                        </svg>
                                                    </span>
                                                    <small
                                                        class="text-muted d-block mt-1">{{ __('No Intro Splash') }}</small>
                                                </div>
                                            </label>
                                        </div>

                                        {{-- Dynamic Intro Templates --}}
                                        @foreach ($intro_screens as $intro_screen)
                                            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                                <label class="w-100 h-100 m-0">
                                                    <input type="radio" name="intro_screen"
                                                        value="{{ $intro_screen->business_card_intro_id }}"
                                                        class="d-none intro-radio"
                                                        {{ $business_card->intro_screen == $intro_screen->business_card_intro_id ? 'checked' : '' }}>
                                                    <div class="intro-selection-card">
                                                        <span class="selected-badge">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                width="16" height="16" viewBox="0 0 24 24"
                                                                stroke-width="3" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M5 12l5 5l10 -10" />
                                                            </svg>
                                                        </span>
                                                        <img src="{{ asset('img/intros/' . $intro_screen->intro_thumbnail) }}"
                                                            class="intro-thumb-img" alt="{{ __('Intro Screen') }}">
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
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
