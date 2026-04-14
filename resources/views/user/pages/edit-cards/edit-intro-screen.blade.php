@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    <style>
        .form-imagecheck-input:checked+.form-imagecheck-figure,
        .rounded-3 {
            border-radius: 0.75rem !important;
        }

        .intro-card {
            aspect-ratio: 9 / 16;
            width: 100%;
        }

        .form-imagecheck-figure {
            height: 100%;
            display: block;
        }

        .form-imagecheck-figure img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
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

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Business Card') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'intro-screen',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <form action="{{ route('user.update.intro-screen', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data" id="myForm">
                                @csrf
                                <div class="card-body">
                                    <h3 class="card-title mb-4">{{ __('Intro Screen') }}</h3>

                                    <div class="row g-4">
                                        {{-- None --}}
                                        <div class="col-sm-4 col-md-3">
                                            <label class="form-imagecheck h-100 w-100 rounded-3 intro-card">
                                                <input type="radio" name="intro_screen" value="none"
                                                    class="form-imagecheck-input"
                                                    {{ $business_card->intro_screen == null ? 'checked' : '' }}>

                                                <span class="form-imagecheck-figure d-block h-100">
                                                    <div class="h-100 d-flex justify-content-center align-items-center fs-3 rounded-3"
                                                        style="background-color: #deecfd">
                                                        {{ __('None') }}
                                                    </div>
                                                </span>
                                            </label>
                                        </div>

                                        {{-- Render intro screens --}}
                                        @foreach ($intro_screens as $intro_screen)
                                            <div class="col-sm-4 col-md-3">
                                                <label class="form-imagecheck h-100 w-100 rounded-3 intro-card">
                                                    <input type="radio" name="intro_screen"
                                                        value="{{ $intro_screen->business_card_intro_id }}"
                                                        class="form-imagecheck-input"
                                                        {{ $business_card->intro_screen == $intro_screen->business_card_intro_id ? 'checked' : '' }}>

                                                    <span class="form-imagecheck-figure">
                                                        <img src="{{ asset('img/intros/' . $intro_screen->intro_thumbnail) }}"
                                                            class="img-fluid rounded-3" alt="Intro Screen">
                                                    </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <div class="d-flex">
                                        <a href="{{ route('user.cards') }}"
                                            class="btn btn-outline-primary ms-2">{{ __('Cancel') }}</a>

                                        <a href="{{ route('user.cards') }}" class="btn btn-outline-primary ms-2">
                                            {{ __('Skip') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                                    </div>
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
