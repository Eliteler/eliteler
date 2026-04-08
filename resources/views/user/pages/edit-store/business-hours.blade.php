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
                            {{ __('Business Hours') }}
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
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'business-hours'])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            {{-- Update business hours --}}
                            <form action="{{ route('user.update.store.hours') }}" method="post" class="card">
                                @csrf 

                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Business Hours') }}</h3>
                                </div>

                                <div class="card-body">
                                    <input type="hidden" class="form-control" name="store_id" value="{{ $business_card->card_id }}">
                                    <div class="row">
                                        {{-- Business hours (Monday to Sunday — Start and End Time) --}}

                                        @php
                                            $hours = $storeHours && $storeHours->business_hours ? json_decode($storeHours->business_hours) : null;
                                        @endphp

                                        @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <div class="col-md-6 col-xl-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __(ucfirst($day)) }}</label>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <input type="time" name="business_hours[{{ $day }}][start]" class="form-control"
                                                            value="{{ $hours->$day->start ?? '' }}"
                                                            placeholder="{{ __('Start Time') }}">
                                                        <span>{{ __('to') }}</span>
                                                        <input type="time" name="business_hours[{{ $day }}][end]" class="form-control"
                                                            value="{{ $hours->$day->end ?? '' }}"
                                                            placeholder="{{ __('End Time') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
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
