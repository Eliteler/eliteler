@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        /* Scoped to prevent breaking other inputs globally */
        .input-group-flat>input[type="password"],
        .input-group-flat>input[type="text"] {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'AI Business Card Builder'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('AI Business Card Builder') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure Artificial Intelligence (AI) providers, models, and authorization keys.') }}
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

                <div class="row">
                    <div class="col-12 col-md-6">
                        <!-- Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.plugin.update.aibuilder') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-4">
                                        {{-- Enable AI Builder --}}
                                        <div class="col-12">
                                            <div class="form-label d-flex justify-content-between align-items-center">
                                                <span>{{ __('AI Builder') }}</span>
                                                <label class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="aibuilder"
                                                        value="1"
                                                        {{ isset($aibuilder_settings->aibuilder) && $aibuilder_settings->aibuilder == 1 ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                        </div>

                                        <hr class="mt-1 mb-0">

                                        {{-- Generate Profile and Cover Images Using AI (Omitted) --}}
                                        <div class="form-label d-none">
                                            <span>{{ __('Generate Profile and Cover Images Using AI') }}</span>
                                            <label class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" name="generate_image"
                                                    value="1"
                                                    {{ isset($aibuilder_settings->generate_image) && $aibuilder_settings->generate_image == 1 ? 'checked' : '' }}>
                                            </label>
                                        </div>

                                        {{-- Provider Select --}}
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="form-label required">{{ __('AI Provider') }}</label>
                                            <select name="provider" id="provider" class="form-select" required>
                                                <option value="" disabled selected>{{ __('Choose an AI Provider') }}
                                                </option>
                                                <option value="openai"
                                                    {{ isset($aibuilder_settings->provider) && $aibuilder_settings->provider == 'openai' ? 'selected' : '' }}>
                                                    {{ __('OpenAI') }}
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Model Select --}}
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="form-label required">{{ __('Model') }}</label>
                                            <select name="model" id="model" class="form-select" required>
                                                <option value="" disabled selected>{{ __('Choose a Model') }}
                                                </option>
                                            </select>
                                        </div>

                                        {{-- API Key 1 --}}
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ __('API Key 1 (Primary)') }}</label>
                                            <div class="input-group input-group-flat">
                                                <input type="password" id="key_1" name="key_1" class="form-control"
                                                    value="{{ $aibuilder_settings->key_1 ?? '' }}"
                                                    placeholder="{{ __('Please enter your primary API key') }}">

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

                                        {{-- Fallback API Key --}}
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ __('API Key 2 (Secondary)') }}</label>
                                            <div class="input-group input-group-flat">
                                                <input type="password" id="key_2" name="key_2" class="form-control"
                                                    value="{{ $aibuilder_settings->key_2 ?? '' }}"
                                                    placeholder="{{ __('Enter your secondary API key') }}">

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

                                <!-- Simplified Right-Aligned Card Footer (Omitted Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
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

{{-- Custom JS --}}
@section('scripts')
    <!-- Script asset moved inside the scripts section -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {
            const providerEl = document.getElementById("provider");
            const modelEl = document.getElementById("model");

            if (!providerEl || !modelEl) return;

            // Initialize TomSelect instances with placeholder configurations
            const providerTom = new TomSelect(providerEl, {
                copyClassesToDropdown: false,
                dropdownClass: "dropdown-menu ts-dropdown",
                optionClass: "dropdown-item",
                controlInput: "<input>",
                placeholder: "{{ __('Choose an AI Provider') }}",
                maxOptions: null
            });

            const modelTom = new TomSelect(modelEl, {
                copyClassesToDropdown: false,
                dropdownClass: "dropdown-menu ts-dropdown",
                optionClass: "dropdown-item",
                controlInput: "<input>",
                placeholder: "{{ __('Choose a Model') }}",
                maxOptions: null
            });

            const savedModel = "{{ $aibuilder_settings->model ?? '' }}";

            function validateField(el) {
                if (el.value) {
                    el.setCustomValidity("");
                } else {
                    el.setCustomValidity("This field is required.");
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

            // Initial execution path
            filterModels();
            validateField(providerEl);
        });

        // Hide/Show password fields
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
