@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry for local styling rules) --}}
@section('css')
    <style>
        .card-footer {
            background-color: transparent !important;
        }
    </style>
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
                                <a href="{{ route('user.account') }}" class="text-secondary text-decoration-none">
                                    {{ __('My Account') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Settings') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Settings') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Customize your global account options, regional configurations, and default preferences.') }}
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

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <div class="row g-0">
                                <div class="col-12 col-md-3 border-end">
                                    <div class="card-body pt-3">
                                        <div class="list-group list-group-transparent">
                                            {{-- Nav links --}}
                                            @include('user.pages.account.includes.navlinks', [
                                                'link' => 'settings',
                                            ])
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-9 d-flex flex-column">
                                    <form action="{{ route('user.update.settings') }}" method="post">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    {{-- Languages --}}
                                                    @if (count(config('app.languages')) > 1)
                                                        <div class="mb-3">
                                                            <label for="chooseLang" class="form-label">
                                                                {{ __('Default Language') }}
                                                            </label>
                                                            <select class="form-select" id="chooseLang"
                                                                name="default_language">
                                                                @foreach (config('app.languages') as $langLocale => $langName)
                                                                    <option value="{{ $langLocale }}"
                                                                        {{ app()->getLocale() == $langLocale ? 'selected' : '' }}>
                                                                        {{ $langName }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection
