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
                <div class="row align-items-center">
                    <!-- Breadcrumbs (Replaces Overview pretitle) -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-templates') }}" class="text-secondary text-decoration-none">
                                    {{ __('Website Content') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.blog.categories') }}" class="text-secondary text-decoration-none">
                                    {{ __('Blog Categories') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Update') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __($blogCategoryDetails->blog_category_title) }}</span>
                            {{ __('Details') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Manage categories to organize your blog posts.') }}
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
                    {{-- Save Category --}}
                    <div class="col-md-6 col-sm-12">
                        <form action="{{ route('admin.update.blog.category', request()->segment(3)) }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="row">

                                            {{-- Category Name --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <!-- Updated label to "Category Name" and placeholder to "Enter the category name" -->
                                                    <label class="form-label required">{{ __('Category Name') }}</label>
                                                    <input type="text" class="form-control" name="category_name"
                                                        id="category_name" maxlength="200"
                                                        value="{{ $blogCategoryDetails->blog_category_title }}"
                                                        placeholder="{{ __('Enter the category name') }}" required>
                                                </div>
                                            </div>

                                            {{-- Category Slug --}}
                                            <div class="col-md-12 col-xl-6">
                                                <div class="mb-3">
                                                    <!-- Added help tooltip next to the label and updated placeholder to "Eg: technology" -->
                                                    <label class="form-label required">
                                                        {{ __('Slug') }}
                                                        <span class="form-help" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="{{ __('Used in the category URL.') }}">!</span>
                                                    </label>
                                                    <input type="text" class="form-control" name="category_slug"
                                                        id="category_slug" maxlength="200"
                                                        value="{{ $blogCategoryDetails->blog_category_slug }}"
                                                        placeholder="{{ __('Eg: technology') }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <!-- Replaced + icon with Floppy Disk icon -->
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

{{-- Custom JS --}}
@section('scripts')
    <script>
        // Convert slug
        $('#category_name').on("change keyup paste click", function() {
            "use strict";
            var Text = $(this).val();
            Text = Text.toLowerCase();
            Text = Text.replace(/[^a-zA-Z0-9]+/g, '-');
            $('#category_slug').val(Text);
        });

        // Initialize Tooltips to ensure help icons trigger properly on hover
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
