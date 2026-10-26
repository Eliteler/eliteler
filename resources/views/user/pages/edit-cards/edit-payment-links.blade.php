@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@php
    use Illuminate\Support\Facades\DB;
    $config = DB::table('config')->get();
@endphp

{{-- Custom CSS for Enhanced Typography, Readability & Modern Tabler Design --}}
@section('css')
    {{-- Font Awesome CSS --}}
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />

    {{-- Payment Links CSS --}}
    <link rel="stylesheet" href="{{ asset('css/payment-links.css') }}" />
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <nav aria-label="breadcrumb" class="mb-1">
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
                                {{ __('Payment Links') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Payment Links') }}
                        </h2>
                        <div class="text-secondary mt-1">
                            {{ __('Configure payout coordinates, verify localized gateway references, and deploy instant transfer tags.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Alerts fallback --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <!-- Sidebar navigation -->
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body p-3">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'payments',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Main Form Area -->
                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <form action="{{ route('user.update.payment.links', Request::segment(3)) }}" method="post"
                                id="myForm" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body p-lg-3">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h3 class="card-title mb-1">{{ __('Manage Payment Options') }}</h3>
                                            <p class="text-muted small mb-0">
                                                {{ __('Add Bank account details, UPI IDs, payment gateways, or QR Code images.') }}
                                            </p>
                                        </div>

                                        <button type="button" onclick="addPayment()"
                                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm fw-semibold">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20"
                                                height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                            <span class="d-none d-sm-inline-block">{{ __('Add New') }}</span>
                                        </button>
                                    </div>

                                    {{-- Empty State when no payment options exist --}}
                                    <div id="no-payments-empty"
                                        class="{{ count($payments) > 0 ? 'd-none' : '' }} py-5 text-center">
                                        <div class="empty">
                                            <div class="empty-icon mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-secondary"
                                                    width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <rect x="3" y="5" width="18" height="14" rx="3" />
                                                    <line x1="3" y1="10" x2="21" y2="10" />
                                                    <line x1="7" y1="15" x2="7.01" y2="15" />
                                                    <line x1="11" y1="15" x2="13" y2="15" />
                                                </svg>
                                            </div>
                                            <p class="empty-title h4 font-weight-bold mb-2 text-dark">
                                                {{ __('No payment methods added yet') }}</p>
                                            <p class="empty-subtitle text-secondary mb-3">
                                                {{ __('Click the button below to add your bank details, UPI ID, or custom payment links.') }}
                                            </p>
                                            <button type="button" onclick="addPayment()"
                                                class="btn btn-primary fw-semibold">
                                                <i class="fa-solid fa-plus me-1"></i> {{ __('Add Payment Option') }}
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Loop Existing Payments --}}
                                    <div id="payments-container">
                                        @for ($i = 0; $i < count($payments); $i++)
                                            <div class="payment-link-row" id="{{ $i }}">
                                                <div class="row g-3 align-items-end">
                                                    <!-- Display Type -->
                                                    <div class="col-md-3 col-lg-3">
                                                        <label class="form-label required"
                                                            for="type{{ $payments[$i]->id }}">{{ __('Display Type') }}</label>
                                                        <select name="type[]" id="type{{ $payments[$i]->id }}"
                                                            class="type{{ $payments[$i]->id }} defaultType form-control"
                                                            onchange="changeLabel({{ $payments[$i]->id }})" required>
                                                            <option value="" disabled>{{ __('Choose Type') }}
                                                            </option>
                                                            <option value="text"
                                                                {{ $payments[$i]->type == 'text' || $payments[$i]->type == 'textarea' ? 'selected' : '' }}>
                                                                {{ __('Bank / Text') }}
                                                            </option>
                                                            <option value="upi"
                                                                {{ $payments[$i]->type == 'upi' ? 'selected' : '' }}>
                                                                {{ __('UPI') }}
                                                            </option>
                                                            <option value="url"
                                                                {{ $payments[$i]->type == 'url' ? 'selected' : '' }}>
                                                                {{ __('Payment Link') }}
                                                            </option>
                                                            <option value="image"
                                                                {{ $payments[$i]->type == 'image' ? 'selected' : '' }}>
                                                                {{ __('QR Code Image') }}
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <!-- Icon Selector Button -->
                                                    <div class="col-auto">
                                                        <label class="form-label required">{{ __('Icon') }}</label>
                                                        <div>
                                                            <button type="button" id="iconpickbtn{{ $i }}"
                                                                class="btn btn-primary btn-icon text-white shadow-sm icon-picker-trigger"
                                                                onclick="openPicker({{ $i }})">
                                                                <i id="displayIcon{{ $i }}"
                                                                    class="{{ $payments[$i]->icon ? $payments[$i]->icon : 'fas fa-money-bill-wave' }}"></i>
                                                            </button>
                                                            <input type="hidden" class="form-control"
                                                                id="iconpick{{ $i }}" name="icon[]"
                                                                value="{{ $payments[$i]->icon }}" required readonly>
                                                        </div>
                                                    </div>

                                                    <!-- Label Input -->
                                                    <div class="col-md-3">
                                                        <label
                                                            class="form-label required">{{ __('Label / Title') }}</label>
                                                        <input type="text"
                                                            class="lbl{{ $payments[$i]->id }} form-control"
                                                            name="label[]" placeholder="{{ __('Label') }}"
                                                            value="{{ $payments[$i]->label }}" required>
                                                    </div>

                                                    <!-- Content / File Input -->
                                                    <div class="col-md">
                                                        <label
                                                            class="form-label required">{{ __('Payment Details / Content') }}</label>
                                                        <textarea class="textlbl{{ $payments[$i]->id }} form-control" name="value[]"
                                                            placeholder="{{ __('Enter content') }}" rows="1"
                                                            style="min-height: 42px; height: 42px; {{ $payments[$i]->type == 'image' ? 'display:none;' : '' }}">{{ $payments[$i]->content }}</textarea>

                                                        <input type="file"
                                                            class="filelbl{{ $payments[$i]->id }} form-control"
                                                            name="value[]" accept="image/*"
                                                            style="{{ $payments[$i]->type == 'image' ? '' : 'display:none;' }}" />
                                                    </div>

                                                    <!-- Solid Red Delete Button -->
                                                    <div class="col-auto">
                                                        <button type="button"
                                                            class="btn btn-danger btn-icon-only btn-icon shadow-sm"
                                                            onclick="removePayment({{ $i }})"
                                                            title="{{ __('Remove Option') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                                width="20" height="20" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <line x1="4" y1="7" x2="20"
                                                                    y2="7" />
                                                                <line x1="10" y1="11" x2="10"
                                                                    y2="17" />
                                                                <line x1="14" y1="11" x2="14"
                                                                    y2="17" />
                                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor

                                        {{-- Dynamic Appended Elements Target --}}
                                        <div id="more-payments"></div>
                                    </div>
                                </div>

                                <!-- Form Footer -->
                                <div class="card-footer border-top d-flex align-items-center justify-content-end py-3">
                                    <button type="submit"
                                        class="btn btn-primary d-inline-flex align-items-center shadow-sm px-4 fw-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20"
                                            height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4l-6 0l0 -4" />
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
        @include('user.includes.footer')
    </div>

    <!-- Icon Picker Modal -->
    <div class="modal modal-bg fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Responsive Header -->
                <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-surface">
                    <div class="icon-modal-header">
                        <div class="d-flex-head d-flex align-items-center justify-content-between">
                            <h5 class="modal-title font-weight-bold mb-0 text-dark">{{ __('Choose an Icon') }}</h5>
                            <button type="button" class="btn-close d-sm-none" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="d-flex align-items-center flex-grow-1 flex-sm-grow-0">
                            <div class="icon-modal-search-box">
                                <span class="theme-search-icon"
                                    style="position: absolute; left: 12px; top: 10px; color: #64748b;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <circle cx="10" cy="10" r="7" />
                                        <line x1="21" y1="21" x2="15" y2="15" />
                                    </svg>
                                </span>
                                <input type="text" id="iconSearchInput" class="icon-modal-search-input"
                                    placeholder="{{ __('Search icon...') }}" autocomplete="off" />
                            </div>

                            <button type="button" class="btn-close d-none d-sm-block ms-1" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Body Grid -->
                <div class="modal-body p-3 p-sm-4"
                    style="background-color: var(--tblr-bg-surface-secondary, #f8fafc); min-height: 280px; max-height: 480px;">
                    @php
                        $icons = include resource_path('views/user/includes/icons.php');
                    @endphp

                    <div class="row g-2" id="iconList">
                        @foreach ($icons as $icon)
                            <div class="col-3 col-sm-2 col-md-2 col-lg-1 icon-item" data-icon-name="{{ $icon }}">
                                <div role="button" class="icon-option-card icon-option" data-icon="{{ $icon }}"
                                    title="{{ $icon }}">
                                    <i class="{{ $icon }} fa-lg"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/fontawesome-iconpicker.min.js') }}"></script>

    <script>
        var count = {{ count($payments) }};

        function checkEmptyState() {
            if (count === 0) {
                $('#no-payments-empty').removeClass('d-none');
            } else {
                $('#no-payments-empty').addClass('d-none');
            }
        }

        function addPayment() {
            "use strict";
            if (count >= {{ $plan_details->no_of_payments }}) {
                new swal({
                    title: `{{ __('Oops!') }}`,
                    icon: 'warning',
                    text: `{{ __('You have reached your current plan limit.') }}`,
                    timer: 2000,
                    buttons: false,
                    showConfirmButton: false,
                });
            } else {
                count++;
                checkEmptyState();

                var id = getRandomInt();
                @php
                    $upiCurrency = 'INR';
                @endphp

                var payments = `
                    <div class='payment-link-row' id="${id}">
                        <div class='row g-3 align-items-end'>
                            <div class='col-md-3 col-lg-3'>
                                <label class='form-label required' for='type${id}'>{{ __('Display Type') }}</label>
                                <select name='type[]' id='type${id}' class='type${id} form-control' onchange='changeLabel(${id})' required>
                                    <option value='' disabled selected>{{ __('Choose Type') }}</option>
                                    <option value='text'>{{ __('Bank / Text') }}</option>
                                    @if ($config[1]->config_value === $upiCurrency)
                                        <option value="upi">{{ __('UPI') }}</option>
                                    @endif
                                    <option value='url'>{{ __('Payment Link') }}</option>
                                    <option value="image">{{ __('QR Code Image') }}</option>
                                </select>
                            </div>

                            <div class='col-auto'>
                                <label class='form-label required'>{{ __('Icon') }}</label>
                                <div>
                                    <button type="button" id="iconpickbtn${id}" class="btn btn-primary btn-icon text-white shadow-sm icon-picker-trigger" onclick="openPicker(${id})">
                                        <i id="displayIcon${id}" class="fas fa-money-bill-wave"></i>
                                    </button>
                                    <input type='hidden' class='form-control' id='iconpick${id}' name='icon[]' value="fas fa-money-bill-wave" required readonly>
                                </div>
                            </div>

                            <div class='col-md-3'>
                                <label class='form-label required'>{{ __('Label / Title') }}</label>
                                <input type='text' class='lbl${id} form-control' name='label[]' placeholder='{{ __('Enter label') }}' required>
                            </div>

                            <div class='col-md'>
                                <label class='form-label required'>{{ __('Payment Details / Content') }}</label>
                                <textarea class='textlbl${id} form-control' name='value[]' rows='1' style='min-height: 42px; height: 42px;' placeholder='{{ __('Enter content') }}'></textarea>
                                <input type="file" class="filelbl${id} form-control" name="value[]" accept="image/*" style="display:none;" />
                            </div>

                            <div class='col-auto'>
                                <button type="button" class='btn btn-danger btn-icon-only btn-icon shadow-sm' onclick='removePayment(${id})' title='{{ __('Remove Option') }}'>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>`;

                $("#more-payments").append(payments);
                dynamicSelect('type' + id);
            }
        }

        function removePayment(id) {
            "use strict";
            $("#" + id).remove();
            count--;
            checkEmptyState();
        }

        function getRandomInt() {
            var min = Math.ceil(0);
            var max = Math.floor(9999999999);
            return Math.floor(Math.random() * (max - min) + min);
        }

        // Icon Search Filter with Skeletons & Empty State
        var iconSearchTimer;
        $('#iconSearchInput').on('input', function() {
            "use strict";
            const search = $(this).val().toLowerCase().trim();

            clearTimeout(iconSearchTimer);

            let skeletonHtml = '';
            for (let i = 0; i < 16; i++) {
                skeletonHtml += `
                    <div class="col-3 col-sm-2 col-md-2 col-lg-1 icon-skeleton placeholder-glow">
                        <div class="placeholder rounded-2 w-100" style="height: 48px;"></div>
                    </div>`;
            }

            $('#iconList > div.icon-item').hide();
            $('#icon-empty-state').remove();
            $('.icon-skeleton').remove();
            $('#iconList').append(skeletonHtml);

            iconSearchTimer = setTimeout(function() {
                $('.icon-skeleton').remove();

                if (search === '') {
                    $('#iconList > div.icon-item').show();
                    return;
                }

                let matchCount = 0;
                $('#iconList > div.icon-item').each(function() {
                    const fullIconName = $(this).attr('data-icon-name') || "";
                    if (fullIconName.toLowerCase().includes(search)) {
                        $(this).show();
                        matchCount++;
                    } else {
                        $(this).hide();
                    }
                });

                if (matchCount === 0) {
                    let emptyStateHtml = `
                        <div class="col-12 py-5" id="icon-empty-state">
                            <div class="empty text-center py-3">
                                <div class="empty-icon mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-secondary" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="10" cy="10" r="7"/>
                                        <line x1="21" y1="21" x2="15" y2="15"/>
                                        <line x1="10" y1="13" x2="10" y2="13.01"/>
                                        <line x1="10" y1="7" x2="10" y2="10"/>
                                    </svg>
                                </div>
                                <p class="empty-title h4 font-weight-bold mb-1 text-dark">{{ __('No icons found') }}</p>
                                <p class="empty-subtitle text-secondary mb-0 small">
                                    {{ __('Try searching for a different keyword or brand name.') }}
                                </p>
                            </div>
                        </div>`;
                    $('#iconList').append(emptyStateHtml);
                }
            }, 250);
        });

        let selectedIconId = null;

        function openPicker(id) {
            "use strict";
            selectedIconId = id;
            $("#iconPickerModal").modal('show');
        }

        $(document).on('click', '.icon-option', function() {
            "use strict";
            const selectedIcon = $(this).data('icon');

            if (selectedIconId !== null) {
                $('#iconpick' + selectedIconId).val(selectedIcon);
                $('#displayIcon' + selectedIconId).attr('class', `${selectedIcon}`);
            }
            $('#iconPickerModal').modal('hide');
        });

        $('#iconPickerModal').on('hidden.bs.modal', function() {
            $('#iconSearchInput').val('');
            $('#icon-empty-state').remove();
            $('.icon-skeleton').remove();
            $('#iconList > div.icon-item').show();
        });

        function changeLabel(id) {
            "use strict";
            var label = `{{ __('Label') }}`;
            var textlabel = `{{ __('Type something') }}`;
            let lbl = document.querySelector('.lbl' + id);
            let textlbl = document.querySelector('.textlbl' + id);
            let fileInput = document.querySelector('.filelbl' + id);
            let type = document.querySelector('.type' + id).value;

            if (type == 'text') {
                label = `{{ __('Bank') }}`;
                textlabel = `{{ __('Your bank account details') }}`;
                textlbl.style.display = 'block';
                fileInput.style.display = 'none';
                textlbl.required = true;
                fileInput.required = false;
            } else if (type == 'upi') {
                label = `{{ __('UPI') }}`;
                textlabel = `{{ __('For ex: YOUR UPI ID') }}`;
                textlbl.style.display = 'block';
                fileInput.style.display = 'none';
                textlbl.required = true;
                fileInput.required = false;
            } else if (type == 'url') {
                label = `{{ __('Payment Link') }}`;
                textlabel = `{{ __('For ex: https://rzp.io/i/nxrHnLJ') }}`;
                textlbl.style.display = 'block';
                fileInput.style.display = 'none';
                textlbl.required = true;
                fileInput.required = false;
            } else if (type == 'image') {
                label = `{{ __('Image') }}`;
                textlabel = `{{ __('Upload your image') }}`;
                textlbl.style.display = 'none';
                fileInput.style.display = 'block';
                textlbl.required = false;
                fileInput.required = true;
            }

            if (lbl) lbl.placeholder = label;
            if (textlbl) textlbl.placeholder = textlabel;
        }

        document.addEventListener("DOMContentLoaded", function() {
            @foreach ($payments as $p)
                changeLabel({{ $p->id }});
            @endforeach
        });

        document.getElementById("myForm").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        $(document).ready(function() {
            $('.defaultType').each(function() {
                var id = $(this).attr('id');
                dynamicSelect(id);
            });
        });

        function dynamicSelect(id) {
            "use strict";
            var el;
            window.TomSelect && (new TomSelect(el = document.getElementById(id), {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: null,
                hideSearch: true,
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    },
                },
            }));
        }
    </script>
@endpush
