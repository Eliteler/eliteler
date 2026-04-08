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
                            {{ __('Settings') }}
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
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'settings'])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            {{-- Delivery Options --}}
                            <form action="{{ route('user.update.store.settings') }}" method="post" class="card">
                                @csrf 

                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Delivery Options') }}</h3>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" class="form-control" name="store_id" value="{{ $business_card->card_id }}">
                                    <div class="row">
                                        {{-- Delivery Options --}}
                                        <div class="col-md-12 col-xl-12">
                                            <div class="mb-3">
                                                <h2 class="card-title">{{ __('Delivery Options') }}</h2>
                                                <div>
                                                    @php
                                                        $deliveryOptions = isset($business_card->delivery_options) ? json_decode($business_card->delivery_options) : null;
                                                    @endphp

                                                    <label class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" value="1" name="order_for_delivery" 
                                                            {{ isset($deliveryOptions->order_for_delivery) && $deliveryOptions->order_for_delivery == 1 ? 'checked' : '' }}>
                                                        <span class="form-check-label">{{ __('Order for delivery') }}</span>
                                                    </label>

                                                    <label class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" value="1" name="take_away" 
                                                            {{ isset($deliveryOptions->take_away) && $deliveryOptions->take_away == 1 ? 'checked' : '' }}>
                                                        <span class="form-check-label">{{ __('Take away') }}</span>
                                                    </label>

                                                    <label class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" value="1" name="dine_in" 
                                                            {{ isset($deliveryOptions->dine_in) && $deliveryOptions->dine_in == 1 ? 'checked' : '' }}>
                                                        <span class="form-check-label">{{ __('Dine in') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 col-xl-12">
                                            <div class="mb-3">
                                                <h2 class="card-title">{{ __('Invoice Settings') }}</h2>
                                                {{-- Invoice Prefix --}}
                                                <div class="col-md-6 col-xl-12">
                                                    <div class="mb-3">
                                                        <div class="form-label">{{ __('Invoice Prefix') }}</div>
                                                        <div>
                                                            <input type="text" class="form-control" name="invoice_prefix" value="{{ $business_card->description['invoice_prefix'] }}">
                                                        </div>
                                                    </div>                                            
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
