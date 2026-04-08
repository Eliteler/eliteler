@extends('user.layouts.index', ['header' => false, 'nav' => false, 'demo' => true, 'settings' => $settings])

@php

@endphp

@section('content')
    <div class="page-wrapper">
        <!-- Page title -->
        <div class="page-header d-print-none border-bottom pb-3">
            <div class="container d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-item-center gap-2">
                    <a href="{{ url()->previous() }}" class="border rounded-3 p-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left icon-primary">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 12l14 0" />
                            <path d="M5 12l6 6" />
                            <path d="M5 12l6 -6" />
                        </svg>
                    </a>
                    <h2 class="page-title">
                        {{ __('Bank Transfer') }}
                    </h2>
                </div>
                <div class="navbar-brand navbar-brand-autodark">
                    <a href="{{ route('user.dashboard') }}">
                        @if (file_exists(public_path('img/logo-light.png')))
                            <img src="{{ optional(Auth::user())->choosed_theme == 'light' ? asset($settings->site_logo) : asset('img/logo-light.png') }}"
                                width="200" height="50" alt="{{ $settings->site_name }}"
                                class="navbar-brand-image custom-logo">
                        @else
                            <img src="{{ $settings->site_logo }}" width="200" height="50"
                                alt="{{ $settings->site_name }}" class="navbar-brand-image custom-logo">
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container">
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

                <div class="row row-deck row-cards">
                    <div class="col-sm-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('mark.payment.payment') }}" method="post">
                                    @csrf
                                    <h3 class="card-title">{{ __('Plan Name : ') }}{{ $plan_details->plan_name }}</h3>
                                    <input type="hidden" value="{{ $plan_details->plan_id }}" name="plan_id">
                                    <input type="hidden" value="{{ $couponId }}" name="coupon_id">
                                    <div class="col-md-10 col-xl-10">
                                        <div class="mb-3">
                                            <label class="form-label required">{{ __('Payment Details') }}</label>
                                            <input type="text" class="form-control" name="transaction_id"
                                                placeholder="{{ __('Payment Details') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-xl-6 my-3">
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">{{ __('Bank Details') }}</h3>
                                <pre>{!! $config[31]->config_value !!}</pre>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Footer --}}
                @include('user.includes.footer')
            </div>
        </div>
    </div>
@endsection
