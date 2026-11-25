@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
    @php
        $selectedType = old('link_type', $currentType);
        $readUrl = route('read.nfc.card', $nfcCardKey->unqiue_key);
    @endphp
    <div class="page-wrapper">
        <!-- Page header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <!-- Breadcrumb Navigation -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.manage.nfc.cards') }}" class="text-secondary text-decoration-none">
                                        {{ __('My Smart Products') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('Set destination') }}
                                </li>
                            </ol>
                        </nav>

                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('Where should this NFC tag open?') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Change it any time. The tag itself never needs rewriting.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('failed') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="row row-cards">
                    <div class="col-lg-8">
                        <form action="{{ route('user.update.card.link') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <input type="hidden" name="key_id" value="{{ $nfcCardKey->nfc_card_key_id }}">

                            <div class="card-body">
                                {{-- Destination type --}}
                                <div class="mb-3">
                                    <div class="form-label required">{{ __('Opens') }}</div>
                                    <div class="form-selectgroup">
                                        @foreach ($linkTypes as $type => $label)
                                            <label class="form-selectgroup-item">
                                                <input type="radio" name="link_type" value="{{ $type }}"
                                                    class="form-selectgroup-input js-link-type"
                                                    {{ $selectedType === $type ? 'checked' : '' }}>
                                                <span class="form-selectgroup-label">{{ __($label) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Business card --}}
                                <div class="mb-3 js-target" data-type="vcard">
                                    <label class="form-label required" for="card_id">{{ __('Business card') }}</label>
                                    @if ($vcards->isEmpty())
                                        <div class="text-secondary">{{ __('You have no active business cards yet.') }}</div>
                                    @else
                                        <select class="form-select" name="card_id" id="card_id">
                                            <option value="">{{ __('Select a business card') }}</option>
                                            @foreach ($vcards as $card)
                                                <option value="{{ $card->card_id }}"
                                                    {{ old('card_id', $currentType === 'vcard' ? $nfcCardKey->card_id : null) === $card->card_id ? 'selected' : '' }}>
                                                    {{ $card->title }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('card_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Store --}}
                                <div class="mb-3 js-target" data-type="store">
                                    <label class="form-label required" for="store_id">{{ __('WhatsApp store') }}</label>
                                    @if ($stores->isEmpty())
                                        <div class="text-secondary">{{ __('You have no active stores yet.') }}</div>
                                    @else
                                        <select class="form-select" name="store_id" id="store_id">
                                            <option value="">{{ __('Select a store') }}</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->card_id }}"
                                                    {{ old('store_id', $currentType === 'store' ? $nfcCardKey->card_id : null) === $store->card_id ? 'selected' : '' }}>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('card_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Custom URL --}}
                                <div class="mb-3 js-target" data-type="url">
                                    <label class="form-label required" for="link_target">{{ __('Link') }}</label>
                                    <input type="text" class="form-control @error('link_target') is-invalid @enderror"
                                        name="link_target" id="link_target" maxlength="2048" dir="ltr"
                                        value="{{ old('link_target', $nfcCardKey->link_target) }}"
                                        placeholder="https://g.page/r/your-business/review">
                                    <small class="form-hint">
                                        {{ __('For example your Google review link, a WhatsApp chat (https://wa.me/...), a booking page, or a UPI / payment link (upi:...).') }}
                                    </small>
                                    @error('link_target')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Label --}}
                                <div class="mb-0">
                                    <label class="form-label" for="label">{{ __('Label') }}</label>
                                    <input type="text" class="form-control" name="label" id="label" maxlength="100"
                                        value="{{ old('label', $nfcCardKey->label) }}"
                                        placeholder="{{ __('e.g., Front desk stand') }}">
                                    <small class="form-hint">{{ __('Only you see this. Helps when you have several products.') }}</small>
                                </div>
                            </div>

                            <div class="card-footer text-end bg-transparent">
                                <a href="{{ route('user.manage.nfc.cards') }}" class="btn btn-link">{{ __('Cancel') }}</a>
                                <button class="btn btn-primary" type="submit">{{ __('Save destination') }}</button>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title">{{ __('This NFC tag') }}</h3>
                                <div class="text-secondary small mb-1">{{ __('Address on the chip') }}</div>
                                <div class="font-monospace text-break mb-3" dir="ltr">{{ $readUrl }}</div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">{{ __('Taps') }}</span>
                                    <span class="fw-bold">{{ number_format((int) $nfcCardKey->scan_count) }}</span>
                                </div>
                                @if ($nfcCardKey->last_scanned_at)
                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">{{ __('Last tap') }}</span>
                                        <span>{{ formatDateForUser($nfcCardKey->last_scanned_at) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('user.includes.footer')
    </div>
@endsection

@push('custom-js')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            "use strict";
            const radios = document.querySelectorAll('.js-link-type');
            const targets = document.querySelectorAll('.js-target');

            function show() {
                const selected = document.querySelector('.js-link-type:checked');
                const type = selected ? selected.value : 'vcard';
                targets.forEach((el) => el.classList.toggle('d-none', el.dataset.type !== type));
            }

            radios.forEach((radio) => radio.addEventListener('change', show));
            show();
        });
    </script>
@endpush
