@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

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
                                <a href="{{ route('user.cards') }}" class="text-secondary text-decoration-none">
                                    {{ __('Digital Business Cards') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ __('Choose a Card Type') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Choose a Card Type') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Select your preferred digital profile format or deploy artificial intelligence generation to customize a template.') }}
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
                    {{-- Business Card Selection --}}
                    <div class="col-6 col-md-3">
                        <!-- Upgraded to Borderless Shadowed Card -->
                        <div class="card border-0 shadow-sm">
                            <label class="form-imagecheck mb-2">
                                <input name="card-type" type="radio" value="business" class="form-imagecheck-input"
                                    onclick="chooseCardType(this)">
                                <span class="shadow-none border-0 d-block">
                                    <img src="{{ asset('img/vCards/business.webp') }}" alt="{{ __('Business') }}"
                                        class="form-imagecheck-image p-2 rounded-3">
                                </span>
                                <h4 class="text-center mt-3 mb-2 text-dark">{{ __('Business') }}</h4>
                            </label>
                        </div>
                    </div>

                    {{-- Personal Card Selection --}}
                    <div class="col-6 col-md-3">
                        <!-- Upgraded to Borderless Shadowed Card -->
                        <div class="card border-0 shadow-sm">
                            <label class="form-imagecheck mb-2">
                                <input name="card-type" type="radio" value="personal" class="form-imagecheck-input"
                                    onclick="chooseCardType(this)">
                                <span class="shadow-none border-0 d-block">
                                    <img src="{{ asset('img/vCards/personal.webp') }}" alt="{{ __('Personal') }}"
                                        class="form-imagecheck-image p-2 rounded-3">
                                </span>
                                <h4 class="text-center mt-3 mb-2 text-dark">{{ __('Personal') }}</h4>
                            </label>
                        </div>
                    </div>

                    {{-- Custom Card Selection --}}
                    <div class="col-6 col-md-3">
                        <!-- Upgraded to Borderless Shadowed Card -->
                        <div class="card border-0 shadow-sm">
                            <label class="form-imagecheck mb-2">
                                <input name="card-type" type="radio" value="custom" class="form-imagecheck-input"
                                    onclick="chooseCardType(this)">
                                <span class="shadow-none border-0 d-block">
                                    <img src="{{ asset('img/vCards/custom.webp') }}" alt="{{ __('Custom') }}"
                                        class="form-imagecheck-image p-2 rounded-3">
                                </span>
                                <h4 class="text-center mt-3 mb-2 text-dark">{{ __('Custom') }}</h4>
                            </label>
                        </div>
                    </div>

                    {{-- AI Builder Selection --}}
                    @if ($is_ai_generator_enabled == 1)
                        <div class="col-6 col-md-3">
                            <!-- Upgraded to Borderless Shadowed Card -->
                            <div class="card border-0 shadow-sm">
                                <label class="form-imagecheck mb-2">
                                    <input name="card_type" type="radio" value="aibuilder" class="form-imagecheck-input"
                                        onclick="chooseCardType(this)">
                                    <span class="shadow-none border-0 d-block">
                                        <img src="{{ asset('img/vCards/aibuilder.webp') }}" alt="{{ __('AI Generator') }}"
                                            class="form-imagecheck-image p-2 rounded-3">
                                    </span>
                                    <h4 class="text-center mt-3 mb-2 text-dark">{{ __('AI Generator') }}</h4>
                                </label>
                            </div>
                        </div>
                    @endif

                    @if ($new_business_card_types)
                        @foreach ($new_business_card_types as $new_business_card_type)
                            <div class="col-6 col-md-3">
                                <!-- Upgraded to Borderless Shadowed Card -->
                                <div class="card border-0 shadow-sm">
                                    <label class="form-imagecheck mb-2">
                                        <input name="card_type" type="radio"
                                            value="{{ $new_business_card_type->business_card_type }}"
                                            class="form-imagecheck-input" onclick="chooseCardType(this)">
                                        <span class="shadow-none border-0 d-flex justify-content-center align-items-center">
                                            <img src="{{ asset($new_business_card_type->business_card_type_image) }}"
                                                alt="{{ __($new_business_card_type->business_card_type_name) }}"
                                                class="form-imagecheck-image p-2 rounded-3" style="max-height: 350px">
                                        </span>
                                        <h4 class="text-center mt-3 mb-2 text-dark">
                                            {{ __($new_business_card_type->business_card_type_name) }}</h4>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script>
        "use strict";

        function chooseCardType(selectedCard) {
            var selectedCardValue = selectedCard.value;
            if (selectedCardValue == "business") {
                window.location = `{{ route('user.create.card', 'type=business') }}`;
            } else if (selectedCardValue == "custom") {
                window.location = `{{ route('user.create.card', 'type=custom') }}`;
            } else if (selectedCardValue == "aibuilder" && {{ $is_ai_generator_enabled == 1 ? 'true' : 'false' }} &&
                {{ Route::has('user.aibuilder') ? 'true' : 'false' }}) {
                window.location = `{{ Route::has('user.aibuilder') ? route('user.aibuilder') : '#' }}`;
            } else if (selectedCardValue == "personal") {
                window.location = `{{ route('user.create.card', 'type=personal') }}`;
            } else {
                window.location = `{{ route('user.create.card') }}?type=${selectedCardValue}`;
            }
        }
    </script>
@endpush
