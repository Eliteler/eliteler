@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS for Enhanced Typography & Tabler Design --}}
@section('css')
    {{-- Section Titles CSS --}}
    <link rel="stylesheet" href="{{ asset('css/section-titles.css') }}">
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
                                {{ __('Section Title Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Section Title Settings') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Configure custom block labels, adjust ordering priorities, and manage front-end text alignments.') }}
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
                                        'link' => 'section-titles',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.section.title', ['id' => $business_card->card_id]) }}"
                                method="post" id="myForm" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body p-3 p-lg-4">
                                    <div class="mb-4">
                                        <h3 class="card-title m-0 text-dark fw-bold">{{ __('Custom Section Headings') }}
                                        </h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Customize and rename the headings of individual blocks displayed on your digital card.') }}
                                        </p>
                                    </div>

                                    <!-- Draggable Section Items Grid (Desktop: col-lg-4) -->
                                    <div id="sortable-sections" class="row g-3">
                                        @foreach ($sectionTitles as $item)
                                            <div class="col-12 col-md-6 col-lg-3" data-id="{{ $item->id }}">
                                                <div class="section-item-card d-flex flex-column justify-content-between">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <label class="form-label mb-0 fw-bold text-dark">
                                                            {{ __($item->label) }}
                                                        </label>
                                                        <span class="section-badge">{{ __('Default Block') }}</span>
                                                    </div>

                                                    <div class="mt-2">
                                                        <div class="input-icon">
                                                            <span class="input-icon-addon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                    width="18" height="18" viewBox="0 0 24 24"
                                                                    stroke-width="2" stroke="currentColor" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path
                                                                        d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                                    <path d="M13.5 6.5l4 4" />
                                                                </svg>
                                                            </span>
                                                            <input type="text" name="titles[{{ $item->id }}]"
                                                                value="{{ $item->title }}" class="form-control"
                                                                placeholder="{{ __('Enter title') }}" minlength="1"
                                                                maxlength="30" required>
                                                        </div>
                                                    </div>
                                                </div>
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
                                    <button id="saveOrder" type="submit"
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

@push('custom-js')
    <!-- Sortable.js for Drag and Drop -->
    <script src="{{ asset('plugins/drag-and-drop/Sortable.min.js') }}"></script>
    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('sortable-sections');
            if (container) {
                new Sortable(container, {
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen'
                });
            }
        });
    </script>
@endpush
