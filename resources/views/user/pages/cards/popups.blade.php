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
                                {{ __('Popups') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Popups') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Enable localized entry modals, deploy interactive confetti overlays, and configure newsletter capture thresholds.') }}
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
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Resolved overlapping card elements) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.popups', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-4">
                                        {{-- Newsletter Popup toggles alignment --}}
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label mb-2"
                                                    for="is_newsletter_pop_active">{{ __('Enable Newsletter Popup') }}</label>
                                                <label class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        onchange="newsletterPopup()" name="is_newsletter_pop_active"
                                                        id="is_newsletter_pop_active">
                                                    <input type="hidden" name="is_newsletter_pop_active" value="0">
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Information Popup toggles alignment --}}
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label mb-2"
                                                    for="is_info_pop_active">{{ __('Enable Information Popup') }}</label>
                                                <label class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        onchange="informationPopup()" name="is_info_pop_active"
                                                        id="is_info_pop_active">
                                                    <input type="hidden" name="is_info_pop_active" value="0">
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Collapsible Popup fields parameters configuration --}}
                                        <div class="col-12 d-none" id="info-popup">
                                            <div class="row g-3">
                                                <div class="col-12 border-top pt-3">
                                                    <h3 class="card-title">{{ __('Information Popup Details') }}</h3>
                                                </div>

                                                {{-- Confetti Effect --}}
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input type="checkbox" class="form-check-input"
                                                            name="confetti_effect" id="confetti_effect" value="1">
                                                        <label class="form-check-label"
                                                            for="confetti_effect">{{ __('Activate confetti celebration effect on popup launch') }}</label>
                                                    </div>
                                                </div>

                                                {{-- Image file --}}
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="info_pop_image">{{ __('Image') }}</label>
                                                        <input type="file" class="form-control" name="info_pop_image"
                                                            id="info_pop_image" placeholder="{{ __('Choose image') }}">
                                                    </div>
                                                </div>

                                                {{-- Title --}}
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="info_pop_title">{{ __('Title') }}</label>
                                                        <input type="text" class="form-control" name="info_pop_title"
                                                            id="info_pop_title" placeholder="{{ __('Enter title') }}">
                                                    </div>
                                                </div>

                                                {{-- Description --}}
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="info_pop_desc">{{ __('Description') }}</label>
                                                        <textarea class="form-control" name="info_pop_desc" id="info_pop_desc" cols="30" rows="3"
                                                            placeholder="{{ __('Enter description') }}"></textarea>
                                                    </div>
                                                </div>

                                                {{-- Button Text --}}
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="info_pop_button_text">{{ __('Button Text') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="info_pop_button_text" id="info_pop_button_text"
                                                            placeholder="{{ __('Enter button text') }}">
                                                    </div>
                                                </div>

                                                {{-- Button URL --}}
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label required"
                                                            for="info_pop_button_url">{{ __('Button URL') }}</label>
                                                        <input type="url" class="form-control"
                                                            name="info_pop_button_url" id="info_pop_button_url"
                                                            placeholder="{{ __('Enter website URL') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer containing standalone save trigger mapping standard styles -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
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

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script>
        "use strict";

        function newsletterPopup() {
            var newsletterPopupVal = $('input[name="is_newsletter_pop_active"]:checked').length;
            if (newsletterPopupVal == 0) {
                $('input[name="is_newsletter_pop_active"]').val(0);
            } else {
                $('input[name="is_newsletter_pop_active"]').val(1);
            }
        }

        function informationPopup() {
            var infoPopupVal = $('input[name="is_info_pop_active"]:checked').length;

            if (infoPopupVal == 0) {
                $("#info-popup").addClass("d-none");
                $('input[name="is_info_pop_active"]').val(0);

                $('#info_pop_image').removeAttr('required');
                $('#info_pop_title').removeAttr('required');
                $('#info_pop_desc').removeAttr('required');
                $('#info_pop_button_url').removeAttr('required');
                $('#info_pop_button_text').removeAttr('required');
            } else {
                $("#info-popup").removeClass("d-none");
                $('input[name="is_info_pop_active"]').val(1);

                $('#info_pop_image').attr('required', 'required');
                $('#info_pop_title').attr('required', 'required');
                $('#info_pop_desc').attr('required', 'required');
                $('#info_pop_button_url').attr('required', 'required');
                $('#info_pop_button_text').attr('required', 'required');
            }
        }
    </script>
@endpush
