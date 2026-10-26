@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'WhatsApp Chat Button'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('WhatsApp Chat Button') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your website click-to-chat WhatsApp button, contact mobile numbers, and pre-filled visitor greeting templates.') }}
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

                <div class="row row-deck row-cards">
                    <div class="col-12 col-md-6">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <form action="{{ route('admin.whatsapp_chat_button_settings.update') }}" method="post"
                            class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Display WhatsApp Chat Button --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required" for="show_whatsapp_chatbot">
                                                {{ __('Display WhatsApp Chat Button') }}
                                            </label>
                                            <select name="show_whatsapp_chatbot" id="show_whatsapp_chatbot"
                                                class="form-select show_whatsapp_chatbot" required>
                                                <option value="" disabled>{{ __('Choose an option') }}</option>
                                                <option value="1"
                                                    {{ ($config['show_whatsapp_chatbot'] ?? '0') == '1' ? 'selected' : '' }}>
                                                    {{ __('Yes') }}</option>
                                                <option value="0"
                                                    {{ ($config['show_whatsapp_chatbot'] ?? '0') == '0' ? 'selected' : '' }}>
                                                    {{ __('No') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Display on customer panel --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required" for="show_whatsapp_chatbot_on_customer_panel">
                                                {{ __('Show on customer panel') }}
                                            </label>
                                            <select name="show_whatsapp_chatbot_on_customer_panel" id="show_whatsapp_chatbot_on_customer_panel"
                                                class="form-select show_whatsapp_chatbot" required>
                                                <option value="" disabled>{{ __('Choose an option') }}</option>
                                                <option value="1"
                                                    {{ ($config['show_whatsapp_chatbot_on_customer_panel'] ?? '0') == '1' ? 'selected' : '' }}>
                                                    {{ __('Yes') }}</option>
                                                <option value="0"
                                                    {{ ($config['show_whatsapp_chatbot_on_customer_panel'] ?? '0') == '0' ? 'selected' : '' }}>
                                                    {{ __('No') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                @for($i = 1; $i <= 6; $i++)
                                    @php
                                        $numKey = $i == 1 ? 'whatsapp_chatbot_mobile_number' : "whatsapp_chatbot_mobile_number_{$i}";
                                        $msgKey = $i == 1 ? 'whatsapp_chatbot_message' : "whatsapp_chatbot_message_{$i}";
                                        $nameKey = "whatsapp_chatbot_name_{$i}";
                                    @endphp
                                    <div class="col-12 mb-3 mt-4">
                                        <h3 class="mb-0">{{ __('WhatsApp Configuration') }} {{ $i }} {{ $i==1 ? '('.__('Primary').')' : '('.__('Optional').')' }}</h3>
                                    </div>

                                    {{-- WhatsApp Name --}}
                                    <div class="col-xl-4 col-12">
                                        <div class="mb-3">
                                            <label class="form-label {{ $i == 1 ? 'required' : '' }}">{{ __('Department / Name') }}</label>
                                            <input type="text" class="form-control"
                                                name="{{ $nameKey }}"
                                                value="{{ $config[$nameKey] ?? '' }}"
                                                placeholder="{{ __('e.g. Sales, Support') }}"
                                                {{ $i == 1 ? 'required' : '' }}>
                                        </div>
                                    </div>

                                    {{-- WhatsApp Number --}}
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label {{ $i == 1 ? 'required' : '' }}">{{ __('WhatsApp Number') }}</label>
                                            <input type="tel" class="form-control"
                                                name="{{ $numKey }}"
                                                value="{{ $config[$numKey] ?? '' }}"
                                                placeholder="{{ __('e.g., 919876543210') }}"
                                                oninput="javascript: if (this.value.length > 20) this.value = this.value.slice(0, 20); this.value = this.value.replace(/[^0-9]/g, '');"
                                                {{ $i == 1 ? 'required' : '' }}>
                                            <small>{{ __('With Country code (without +)') }}</small>
                                        </div>
                                    </div>

                                    {{-- Initial Chat Message --}}
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label {{ $i == 1 ? 'required' : '' }}">{{ __('Initial Chat Message') }}</label>
                                            <textarea class="form-control" name="{{ $msgKey }}" id="{{ $msgKey }}" cols="30"
                                                rows="2" placeholder="{{ __('Enter the default text sent by visitors when opening chat') }}" {{ $i == 1 ? 'required' : '' }}>{{ $config[$msgKey] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                @endfor

                                </div>
                            </div>

                            <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <!-- Device Floppy Icon -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <!-- Script asset loaded inside correct scripts section -->
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function() {
    var elementSelectors = ['.show_whatsapp_chatbot'];

    function initializeTomSelect(el) {
        new TomSelect(el, {
            copyClassesToDropdown: false,
            dropdownClass: 'dropdown-menu ts-dropdown',
            optionClass: 'dropdown-item',
            controlInput: '<input>',
                    placeholder: "{{ __('Choose an option') }}",
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
    }

    elementSelectors.forEach(function(selector) {
        var elements = document.querySelectorAll(selector);
        elements.forEach(function(el) {
            initializeTomSelect(el);
        });
    });

            // Handle dynamically generated select tags safely inside the DOM structure
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            elementSelectors.forEach(function(selector) {
                                if (node.matches(selector)) {
                                    initializeTomSelect(node);
                                }
                                var childElements = node.querySelectorAll(selector);
                                childElements.forEach(function(childEl) {
                                    initializeTomSelect(childEl);
                                });
                            });
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
@endsection
