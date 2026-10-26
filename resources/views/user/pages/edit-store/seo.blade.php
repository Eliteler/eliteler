@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store SEO CSS --}}
    <link href="{{ asset('css/store-seo.css') }}" rel="stylesheet">
@endsection

@php
    $seoConfig = isset($business_card->seo_configurations) ? json_decode($business_card->seo_configurations) : null;
    $metaTitle = $seoConfig->meta_title ?? $business_card->title;
    $metaDescription = $seoConfig->meta_description ?? $business_card->sub_title;
    $metaKeywords = $seoConfig->meta_keywords ?? '';
    $faviconUrl = $seoConfig->favicon ?? '';
@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Navigation -->
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-muted d-inline-flex align-items-center gap-1">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.stores') }}" class="text-muted">{{ __('Stores') }}</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('SEO Settings') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Search Engine Optimization (SEO)') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Manage your storefront search metadata, browser icon branding, and search engine discoverability.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'seo'])
                                </div>
                            </div>
                        </div>

                        {{-- Right SaaS SEO Settings Pane --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store.seo') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $business_card->card_id }}">

                                <div class="card-body p-4">
                                    <!-- Row 1: Favicon Branding -->
                                    <div class="saas-setting-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-md-4">
                                                <div class="saas-setting-label">{{ __('Store Favicon') }}</div>
                                                <p class="saas-setting-desc">
                                                    {{ __('The icon displayed in browser tabs and bookmark bars.') }}</p>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="saas-favicon-preview">
                                                        <img src="{{ !empty($faviconUrl) ? asset($faviconUrl) : asset('img/favicon.png') }}"
                                                            id="faviconPreviewImg" alt="{{ __('Favicon') }}"
                                                            onerror="this.src='{{ asset('img/favicon.png') }}'">
                                                    </div>
                                                    <div>
                                                        <input type="file" class="d-none" name="favicon"
                                                            id="faviconInput"
                                                            accept="image/png,image/x-icon,image/svg+xml,image/jpeg">
                                                        <button type="button"
                                                            class="btn btn-primary btn-md d-inline-flex align-items-center"
                                                            onclick="$('#faviconInput').trigger('click');">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon icon-tabler icon-tabler-upload" width="16"
                                                                height="16" viewBox="0 0 24 24" stroke-width="2"
                                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                                stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                                                <polyline points="7 9 12 4 17 9" />
                                                                <line x1="12" y1="4" x2="12"
                                                                    y2="16" />
                                                            </svg>
                                                            <span>{{ __('Change Favicon') }}</span>
                                                        </button>
                                                        <div class="small text-muted mt-1">
                                                            {{ __('Recommended size: 32x32px or 64x64px (.ico or .png)') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 2: Meta Title -->
                                    <div class="saas-setting-row">
                                        <div class="row align-items-start g-3">
                                            <div class="col-md-4">
                                                <div class="saas-setting-label required">{{ __('Meta Title') }}</div>
                                                <p class="saas-setting-desc">
                                                    {{ __('The main title indexed and shown by search engines in search results.') }}
                                                </p>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="input-icon mb-1">
                                                    <span class="input-icon-addon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-heading" width="18"
                                                            height="18" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M7 12h10" />
                                                            <path d="M7 5v14" />
                                                            <path d="M17 5v14" />
                                                        </svg>
                                                    </span>
                                                    <input type="text" class="form-control" name="meta_title"
                                                        id="metaTitleInput" value="{{ $metaTitle }}"
                                                        placeholder="{{ __('Enter store page title') }}" maxlength="70"
                                                        required>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small
                                                        class="form-hint">{{ __('Recommended length: 50–60 characters.') }}</small>
                                                    <span class="char-badge"><span id="titleCount">0</span>/60</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 3: Meta Description -->
                                    <div class="saas-setting-row">
                                        <div class="row align-items-start g-3">
                                            <div class="col-md-4">
                                                <div class="saas-setting-label required">{{ __('Meta Description') }}
                                                </div>
                                                <p class="saas-setting-desc">
                                                    {{ __('A concise summary of your store displayed beneath the title in search results.') }}
                                                </p>
                                            </div>
                                            <div class="col-md-8">
                                                <textarea class="form-control mb-1" name="meta_description" id="metaDescInput" rows="3"
                                                    placeholder="{{ __('Enter concise summary describing your store and products') }}" maxlength="200" required>{{ $metaDescription }}</textarea>
                                                <div class="d-flex justify-content-between">
                                                    <small
                                                        class="form-hint">{{ __('Recommended length: 150–160 characters.') }}</small>
                                                    <span class="char-badge"><span id="descCount">0</span>/160</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 4: Meta Keywords -->
                                    <div class="saas-setting-row">
                                        <div class="row align-items-start g-3">
                                            <div class="col-md-4">
                                                <div class="saas-setting-label required">{{ __('Meta Keywords') }}</div>
                                                <p class="saas-setting-desc">
                                                    {{ __('Target keywords and categories relevant to your products.') }}
                                                </p>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="input-icon mb-1">
                                                    <span class="input-icon-addon">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler icon-tabler-tags" width="18"
                                                            height="18" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M7.859 6h-2.834a2.025 2.025 0 0 0 -2.025 2.025v2.834c0 .537 .213 1.052 .593 1.432l6.116 6.116a2.025 2.025 0 0 0 2.864 0l2.834 -2.834a2.025 2.025 0 0 0 0 -2.864l-6.117 -6.116a2.025 2.025 0 0 0 -1.431 -.593z" />
                                                            <path
                                                                d="M17.573 18.407l2.834 -2.834a2.025 2.025 0 0 0 0 -2.864l-7.117 -7.116" />
                                                            <path d="M6 9h-.01" />
                                                        </svg>
                                                    </span>
                                                    <input type="text" class="form-control" name="meta_keywords"
                                                        value="{{ $metaKeywords }}"
                                                        placeholder="{{ __('e.g., bakery, artisan cakes, online shopping, pastries') }}"
                                                        required>
                                                </div>
                                                <small
                                                    class="form-hint">{{ __('Separate multiple search keywords with commas.') }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Row 5: Search Snippet Preview -->
                                    <div class="saas-setting-row">
                                        <div class="row align-items-start g-3">
                                            <div class="col-md-4">
                                                <div class="saas-setting-label">{{ __('Search Preview') }}</div>
                                                <p class="saas-setting-desc">
                                                    {{ __('Live simulation of how your store snippet appears in search engine results.') }}
                                                </p>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="saas-serp-preview">
                                                    <div class="saas-serp-url">
                                                        {{ url('/') }}/{{ $business_card->card_url }}</div>
                                                    <div class="saas-serp-title" id="serpTitlePreview">
                                                        {{ $metaTitle ?: __('Store Title | Online Store') }}</div>
                                                    <div class="saas-serp-desc" id="serpDescPreview">
                                                        {{ $metaDescription ?: __('Browse our exclusive products and catalog. Order directly via WhatsApp with fast delivery.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Symmetrical Action Footer -->
                                <div class="card-footer bg-transparent border-top p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('user.stores') }}"
                                            class="btn btn-danger d-inline-flex align-items-center gap-1">
                                            {{ __('Cancel') }}
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                <path d="M14 4l0 4l-4 0l0 -4" />
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
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@push('custom-js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";

            const metaTitleInput = document.getElementById('metaTitleInput');
            const metaDescInput = document.getElementById('metaDescInput');
            const serpTitlePreview = document.getElementById('serpTitlePreview');
            const serpDescPreview = document.getElementById('serpDescPreview');
            const titleCount = document.getElementById('titleCount');
            const descCount = document.getElementById('descCount');
            const faviconInput = document.getElementById('faviconInput');
            const faviconPreviewImg = document.getElementById('faviconPreviewImg');

            // Synchronize live character counters & snippet preview
            function updateSnippet() {
                if (metaTitleInput && serpTitlePreview) {
                    const titleVal = metaTitleInput.value.trim();
                    titleCount.textContent = metaTitleInput.value.length;
                    serpTitlePreview.textContent = titleVal || "{{ __('Store Title | Online Store') }}";
                }

                if (metaDescInput && serpDescPreview) {
                    const descVal = metaDescInput.value.trim();
                    descCount.textContent = metaDescInput.value.length;
                    serpDescPreview.textContent = descVal ||
                        "{{ __('Browse our exclusive products and catalog. Order directly via WhatsApp with fast delivery.') }}";
                }
            }

            if (metaTitleInput) metaTitleInput.addEventListener('input', updateSnippet);
            if (metaDescInput) metaDescInput.addEventListener('input', updateSnippet);

            // Initial counter sync
            updateSnippet();

            // Favicon instant preview
            if (faviconInput && faviconPreviewImg) {
                faviconInput.addEventListener('change', function(e) {
                    const files = e.target.files;
                    if (files && files.length > 0) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            faviconPreviewImg.src = event.target.result;
                        };
                        reader.readAsDataURL(files[0]);
                    }
                });
            }
        });
    </script>
@endpush
