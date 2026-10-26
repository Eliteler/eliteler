@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-business-hours.css') }}" rel="stylesheet">
@endsection

@php
    $hours = $storeHours && $storeHours->business_hours ? json_decode($storeHours->business_hours) : null;
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
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
                                    {{ __('Business Hours') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title and Subtitle -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Operating & Business Hours') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Establish weekly service timelines, determine operating hours, and configure specific shop schedules.') }}
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
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
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
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'business-hours',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Business Hours Form --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <form action="{{ route('user.update.store.hours') }}" method="POST">
                                @csrf
                                <input type="hidden" name="store_id" value="{{ $business_card->card_id }}">

                                <div
                                    class="card-header border-bottom bg-transparent py-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="card-title fw-bold mb-0">{{ __('Weekly Operating Schedule') }}</h3>
                                        <small
                                            class="text-muted">{{ __('Define opening and closing times for each day') }}</small>
                                    </div>
                                    <span class="badge bg-primary-lt d-none d-sm-inline-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clock"
                                            width="16" height="16" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                            <path d="M12 7l0 5l3 3" />
                                        </svg>
                                        {{ __('24h Local Time') }}
                                    </span>
                                </div>

                                <div class="card-body p-0">
                                    @foreach ($days as $day)
                                        @php
                                            $startTime = $hours->$day->start ?? '';
                                            $endTime = $hours->$day->end ?? '';
                                        @endphp
                                        {{-- Fully Inline Horizontal Row --}}
                                        <div
                                            class="business-hour-row d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap gap-2">
                                            {{-- Day Column --}}
                                            <div class="day-badge-title">
                                                <span>{{ __(ucfirst($day)) }}</span>
                                            </div>

                                            {{-- Inline Time Inputs --}}
                                            <div class="d-flex align-items-center gap-2 ms-auto">
                                                <input type="time" name="business_hours[{{ $day }}][start]"
                                                    class="form-control time-input-field" value="{{ $startTime }}"
                                                    placeholder="{{ __('Start') }}">

                                                <span class="time-separator">{{ __('to') }}</span>

                                                <input type="time" name="business_hours[{{ $day }}][end]"
                                                    class="form-control time-input-field" value="{{ $endTime }}"
                                                    placeholder="{{ __('End') }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Standardized Symmetrical Action Footer -->
                                <div class="card-footer bg-transparent border-top p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('user.stores') }}"
                                            class="btn btn-danger d-inline-flex align-items-center gap-1">
                                            {{ __('Cancel') }}
                                        </a>

                                        <button type="submit"
                                            class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-device-floppy" width="18"
                                                height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
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
