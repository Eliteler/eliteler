@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            {{ __('Overview') }}
                        </div>
                        <h2 class="page-title">
                            {{ __('SEO Configuration Settings') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

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
                                <h4 class="subheader">{{ __('Update Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'seo'])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            {{-- Update SEO --}}
                            <form action="{{ route('user.update.store.seo') }}" method="post" enctype="multipart/form-data" class="card">
                                @csrf 

                                <div class="card-header">
                                    <h3 class="card-title">{{ __('SEO Configuration Settings') }}</h3>
                                </div>

                                <div class="card-body">
                                    <input type="hidden" class="form-control" name="store_id" value="{{ $business_card->card_id }}">
                                    <div class="row">
                                        {{-- Favicon --}}
                                        <div class="col-md-6 col-xl-12">
                                            <div class="mb-3">
                                                <div class="form-label">{{ __('Favicon') }}</div>
                                                <div>
                                                    <input type="file" class="form-control" name="favicon" id="favicon" accept="image/*">
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $seoConfig = isset($business_card->seo_configurations) ? json_decode($business_card->seo_configurations) : null;
                                        @endphp

                                        {{-- Meta title --}}
                                        <div class="col-md-6 col-xl-12">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Meta Title') }}</div>
                                                <div>
                                                    <input type="text" class="form-control" name="meta_title" 
                                                        value="{{ $seoConfig->meta_title ?? '' }}" 
                                                        placeholder="{{ __('Meta Title') }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Meta description --}}
                                        <div class="col-md-6 col-xl-12">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Meta Description') }}</div>
                                                <div>
                                                    <input type="text" class="form-control" name="meta_description" 
                                                        value="{{ $seoConfig->meta_description ?? '' }}" 
                                                        placeholder="{{ __('Meta Description') }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Meta keywords --}}
                                        <div class="col-md-6 col-xl-12">
                                            <div class="mb-3">
                                                <div class="form-label required">{{ __('Meta Keywords') }}</div>
                                                <div>
                                                    <input type="text" class="form-control" name="meta_keywords" 
                                                        value="{{ $seoConfig->meta_keywords ?? '' }}" 
                                                        placeholder="{{ __('Meta Keywords') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            @include('user.includes.footer')
        </div>
    </div>
@endsection
