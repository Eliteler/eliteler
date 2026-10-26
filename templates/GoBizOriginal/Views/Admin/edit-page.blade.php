@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumbs -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-templates') }}" class="text-secondary text-decoration-none">
                                    {{ __('Web Templates') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.web-template.gobiz-original') }}" class="text-secondary text-decoration-none">
                                    {{ __('GoBiz Original') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Update Page') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1 text-capitalize">{{ __($sections[0]->page_name) }}</span>
                            {{ __('Details') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Modify dynamic landing page content blocks, update localized strings, and edit indexing SEO parameters.') }}
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

                <div class="row row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form
                                action="{{ route('admin.web-template.gobiz-original.update-page', Request::segment(6)) }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Loop through existing page sections --}}
                                        @for ($i = 0; $i < count($sections); $i++)
                                            <div class="col-12 col-xl-6" id="section{{ $i }}">
                                                <div class="mb-3">
                                                    <label class="form-label required">
                                                        {{ __($sections[$i]->section_title == 'desc' ? 'Description' : str_replace('_', ' ', $sections[$i]->section_title)) }}
                                                    </label>
                                                    <textarea rows="6" cols="10" class="form-control" name="section{{ $i }}"
                                                        placeholder="{{ __('Enter ') . __($sections[$i]->section_title) . __(' contents') }}" required>{{ $sections[$i]->section_content }}</textarea>
                                                </div>
                                            </div>
                                        @endfor

                                        {{-- Check footer section visibility logic constraints --}}
                                        @if (Request::segment(3) != 'footer')
                                            {{-- SEO Configurations Title Header --}}
                                            <div class="col-12 border-top pt-4 mt-3">
                                                <h3 class="card-title fw-bold m-0">{{ __('SEO Configurations') }}</h3>
                                            </div>

                                            {{-- Title --}}
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label required"
                                                        for="title">{{ __('Meta Title') }}</label>
                                                    <textarea class="form-control" id="title" name="title" rows="2"
                                                        placeholder="{{ __('Enter search snippet title tag') }}" required>{{ $sections[0]->title }}</textarea>
                                                </div>
                                            </div>

                                            {{-- Meta Description --}}
                                            <div class="col-12 col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label required"
                                                        for="description">{{ __('Meta Description') }}</label>
                                                    <textarea name="description" id="description" rows="2" class="form-control"
                                                        placeholder="{{ __('Enter meta description to summarize content inside index indices') }}" required>{{ $sections[0]->description }}</textarea>
                                                </div>
                                            </div>

                                            {{-- Meta Keywords --}}
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label required"
                                                        for="keywords">{{ __('Meta Keywords') }}</label>
                                                    <textarea name="keywords" id="keywords" rows="2" class="form-control"
                                                        placeholder="{{ __('Enter primary target keywords separated by commas (e.g., pricing, plans, options)') }}"
                                                        required>{{ $sections[0]->keywords }}</textarea>
                                                </div>
                                            </div>
                                        @endif
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
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection
