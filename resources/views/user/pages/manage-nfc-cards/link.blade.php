@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
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
                                        {{ __('My NFC Cards') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ __('Link Card') }}
                                </li>
                            </ol>
                        </nav>

                        <!-- Page Title -->
                        <h2 class="page-title">
                            {{ __('Link Card to NFC Card Key') }}
                        </h2>

                        <!-- Descriptive Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Select a digital business card or store to link directly to this physical NFC card key.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Success Alert --}}
                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>
                                {{ Session::get('success') }}
                            </div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

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

                <div class="row row-deck row-cards">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.update.card.link') }}" method="post" class="card border-0">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row">
                                                {{-- NFC Card Key ID --}}
                                                <input type="hidden" class="form-control" name="key_id" id="key_id"
                                                    value="{{ $nfcCardKey->nfc_card_key_id }}">

                                                {{-- Cards --}}
                                                <div class="col-md-6 col-xl-6 px-0">
                                                    <div class="mb-3">
                                                        <div class="form-label required">
                                                            {{ __('Business Cards & Stores') }}</div>
                                                        <select class="form-control cards" name="card_id" id="card_id"
                                                            required>
                                                            <option value="" selected disabled>{{ __('Select a business card/store') }}</option>
                                                            @foreach ($businessCards as $businessCard)
                                                                <option value="{{ $businessCard->card_id }}">
                                                                    {{ $businessCard->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end bg-transparent border-top-0 pt-0">
                                    <button class="btn btn-primary btn-md d-inline-flex align-items-center" type="submit">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <circle cx="12" cy="14" r="2" />
                                            <polyline points="14 4 14 8 8 8 8 4" />
                                        </svg>
                                        {{ __('Link Card') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        @include('user.includes.footer')
    </div>
@endsection

{{-- Custom JS loaded cleanly inside script stack --}}
@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            "use strict";

            // Array of element selectors
            var elementSelectors = ['card_id'];

            // Function to initialize TomSelect and enforce the "required" attribute
            function initializeTomSelectWithRequired(el) {
                new TomSelect(el, {
                    copyClassesToDropdown: false,
                    dropdownClass: 'dropdown-menu ts-dropdown',
                    optionClass: 'dropdown-item',
                    controlInput: '<input>',
                    maxOptions: null,
                    render: {
                        item: function(data, escape) {
                            if (data.customProperties) {
                                return '<div><span class="dropdown-item-indicator">' + data
                                    .customProperties + '</span>' + escape(data.text) + '</div>';
                            }
                            return '<div>' + escape(data.text) + '</div>';
                        },
                        option: function(data, escape) {
                            if (data.customProperties) {
                                return '<div><span class="dropdown-item-indicator">' + data
                                    .customProperties + '</span>' + escape(data.text) + '</div>';
                            }
                            return '<div>' + escape(data.text) + '</div>';
                        },
                    },
                });

                // Ensure the "required" attribute is enforced
                el.addEventListener('change', function() {
                    if (el.value) {
                        el.setCustomValidity('');
                    } else {
                        el.setCustomValidity('This field is required');
                    }
                });

                // Trigger validation on load
                el.dispatchEvent(new Event('change'));
            }

            // Loop through each element ID
            elementSelectors.forEach(function(id) {
                // Check if the element exists
                var el = document.getElementById(id);
                if (el) {
                    // Apply TomSelect and enforce the "required" attribute
                    initializeTomSelectWithRequired(el);
                }
            });
        });
    </script>
@endpush
