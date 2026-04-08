@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <style>
        input[type="password"],
        input[type="text"] {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
    </style>
@endsection

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
                        <h2 class="page-title mb-2">
                            {{ __('Update AiBuilder Settings') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('admin.plugins') }}" class="btn btn-primary text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l14 0" />
                                    <path d="M5 12l6 6" />
                                    <path d="M5 12l6 -6" />
                                </svg>
                                {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
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

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <form action="{{ route('admin.plugin.update.aibuilder') }}" method="post">
                                @csrf
                                <div class="card-body">

                                    <div class="row g-4">

                                        {{-- Enable AiBuilder --}}
                                        <div class="col-12 col-md-2">
                                            <div class="form-label d-flex justify-content-between align-items-center">
                                                <span>{{ __('Enable AiBuilder') }}</span>
                                                <label class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="aibuilder"
                                                        value="1"
                                                        {{ isset($aibuilder_settings->aibuilder) && $aibuilder_settings->aibuilder == 1 ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Generate Profile and cover image using AI --}}
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <div class="form-label d-none">
                                                <span>{{ __('Generate Profile and Cover Image using AI') }}</span>
                                                <label class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="generate_image"
                                                        value="1"
                                                        {{ isset($aibuilder_settings->generate_image) && $aibuilder_settings->generate_image == 1 ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Provider --}}
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="form-label required">{{ __('AI Provider') }}</label>
                                            <select name="provider" id="provider" class="form-select" required>
                                                <option value="openai"
                                                    {{ isset($aibuilder_settings->provider) && $aibuilder_settings->provider == 'openai' ? 'selected' : '' }}>
                                                    {{ __('OpenAI') }}
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Model --}}
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="form-label required">{{ __('Model') }}</label>
                                            {{-- Modals --}}
                                            <select name="model" id="model" class="form-select" required></select>
                                        </div>

                                        {{-- AiBuilder Key 1 --}}
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ __('API Key 1') }}</label>
                                            <div class="input-group input-group-flat">
                                                <input type="password" id="key_1" name="key_1" class="form-control"
                                                    value="{{ $aibuilder_settings->key_1 ?? '' }}"
                                                    placeholder="{{ __('Key') }}">

                                                <span class="input-group-text toggle-key cursor-pointer"
                                                    data-target="key_1">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-eye" width="20"
                                                        height="20" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <circle cx="12" cy="12" r="2" />
                                                        <path
                                                            d="M22 12c-2.667 4 -6 6 -10 6s-7.333 -2 -10 -6c2.667 -4 6 -6 10 -6s7.333 2 10 6" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Fallback API key --}}
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ __('API Key 2 (Optional)') }}</label>
                                            <div class="input-group input-group-flat">
                                                <input type="password" id="key_2" name="key_2" class="form-control"
                                                    value="{{ $aibuilder_settings->key_2 ?? '' }}"
                                                    placeholder="{{ __('Key') }}">

                                                <span class="input-group-text toggle-key cursor-pointer"
                                                    data-target="key_2">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-eye" width="20"
                                                        height="20" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <circle cx="12" cy="12" r="2" />
                                                        <path
                                                            d="M22 12c-2.667 4 -6 6 -10 6s-7.333 -2 -10 -6c2.667 -4 6 -6 10 -6s7.333 2 10 6" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4">
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

    {{-- Custom JS --}}
@section('scripts')
    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {

            const providerEl = document.getElementById("provider");
            const modelEl = document.getElementById("model");

            if (!providerEl || !modelEl) return;

            // Initialize TomSelect
            const providerTom = new TomSelect(providerEl, {
                copyClassesToDropdown: false,
                dropdownClass: "dropdown-menu ts-dropdown",
                optionClass: "dropdown-item",
                controlInput: "<input>",
                maxOptions: null
            });

            const modelTom = new TomSelect(modelEl, {
                copyClassesToDropdown: false,
                dropdownClass: "dropdown-menu ts-dropdown",
                optionClass: "dropdown-item",
                controlInput: "<input>",
                maxOptions: null
            });

            const savedModel = "{{ $aibuilder_settings->model ?? '' }}";

            function validateField(el) {
                if (el.value) {
                    el.setCustomValidity("");
                } else {
                    el.setCustomValidity("This field is required");
                }
            }

            function filterModels() {
                const provider = providerTom.getValue();

                modelTom.clear();
                modelTom.clearOptions();

                if (provider === "openai") {
                    modelTom.addOptions([{
                            value: "gpt-5.2",
                            text: "{{ __('GPT-5.2') }}"
                        },
                        {
                            value: "gpt-5.1",
                            text: "{{ __('GPT-5.1') }}"
                        },
                        {
                            value: "gpt-5",
                            text: "{{ __('GPT-5') }}"
                        },
                        {
                            value: "gpt-4.1",
                            text: "{{ __('GPT-4.1') }}"
                        },
                        {
                            value: "gpt-4o",
                            text: "{{ __('GPT-4o') }}"
                        },
                        {
                            value: "gpt-4o-mini",
                            text: "{{ __('GPT-4o Mini') }}"
                        }
                    ]);
                }

                modelTom.refreshOptions(false);

                if (savedModel && modelTom.options[savedModel]) {
                    modelTom.setValue(savedModel, true);
                } else {
                    const first = Object.keys(modelTom.options)[0] || "";
                    modelTom.setValue(first);
                }

                validateField(modelEl);
            }

            // Validation events
            providerTom.on("change", function() {
                validateField(providerEl);
                filterModels();
            });

            modelTom.on("change", function() {
                validateField(modelEl);
            });

            // Initial load
            filterModels();
            validateField(providerEl);
        });

        // Show/Hide Key
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".toggle-key").forEach(function(el) {
                el.addEventListener("click", function() {
                    const input = document.getElementById(this.dataset.target);

                    if (input.type === "password") {
                        input.type = "text";
                        this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off"
                        width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 3l18 18"/>
                        <path d="M10.584 10.587a2 2 0 0 0 2.828 2.828"/>
                        <path d="M9.363 5.365a9.466 9.466 0 0 1 2.637 -.365c4 0 7.333 2 10 6a13.16 13.16 0 0 1 -1.67 2.388"/>
                        <path d="M6.53 6.53a13.16 13.16 0 0 0 -4.53 5.47c2.667 4 6 6 10 6a9.74 9.74 0 0 0 5.47 -1.53"/>
                    </svg>`;
                    } else {
                        input.type = "password";
                        this.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye"
                        width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="12" cy="12" r="2"/>
                        <path d="M22 12c-2.667 4 -6 6 -10 6s-7.333 -2 -10 -6c2.667 -4 6 -6 10 -6s7.333 2 10 6"/>
                    </svg>`;
                    }
                });
            });
        });
    </script>
@endsection
@endsection
