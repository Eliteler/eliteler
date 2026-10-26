@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@php
    use Illuminate\Support\Facades\DB;

    // Fetch config
    $config = DB::table('config')->get();
@endphp

{{-- Custom CSS (Kept dry) --}}
@section('css')
    <link rel="stylesheet" href="{{ asset('css/all.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/bootstrap-iconpicker.min.css') }}" />
@endsection

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
                                {{ __('Payments') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Payments') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Configure localized payout options, verify automatic API gateways, and manage client deposit links.') }}
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
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper (Decoupled form structure) -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('user.save.payment.links', Request::segment(3)) }}" id="myForm"
                                method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="card-body">
                                    <div class="row g-3">
                                        <div id="more-payments" class="row g-3"></div>
                                        <div class="col-12 mt-4">
                                            <button type="button" onclick="addPayment()"
                                                class="btn btn-primary d-inline-flex align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-plus me-2" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <line x1="12" y1="5" x2="12" y2="19" />
                                                    <line x1="5" y1="12" x2="19" y2="12" />
                                                </svg>
                                                {{ __('Add New') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Footer containing standalone save trigger mapping standard styles -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
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

    <!-- Icon Picker Modal upgraded to Borderless Shadowed Pattern -->
    <div class="modal modal-bg fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable py-7" role="document">
            <div class="modal-content border-0 shadow-sm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">{{ __('Choose an Icon') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-4">
                        <input type="text" id="iconSearchInput" class="form-control" placeholder="Search icons..." />
                    </div>

                    @php
                        $icons = include resource_path('views/user/includes/icons.php');
                    @endphp

                    <div class="row g-2 justify-content-center" id="iconList">
                        @foreach ($icons as $icon)
                            <div class="col-3 col-sm-2 col-md-1 p-2 text-center" data-icon-name="{{ $icon }}">
                                <div role="button" class="icon-option p-2 border rounded shadow-none hover-primary"
                                    data-icon="{{ $icon }}">
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
        var count = 0;

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
                var id = getRandomInt();

                @php
                    $upiCurrency = 'INR';
                @endphp

                var payments = `
    <div class='row' id="${id}">
        <div class='col-lg-6 col-md-6'>
            <div class='mb-3 mt-2'>
                <label class='form-label required'>{{ __('Icon') }}</label>
                <div class='input-group'>
                    <input type='text' class='form-control' placeholder='{{ __('Choose Icon') }}' id='iconpick${id}' onclick='openPicker(${id})' name='icon[]' required readonly>
                </div>
            </div>
        </div>
        <div class='col-lg-6 col-md-6'>
            <div class='mb-3 mt-2'>
                <label class='form-label required' for='type'>{{ __('Display type') }}</label>
                <select name='type[]' id='type' class='type${id} form-control type' onchange='changeLabel(${id})' required>
                    <option value='' disabled selected>{{ __('Choose Type') }}</option>
                    <option value='text'>{{ __('Default') }}</option>
                    @if ($config[1]->config_value === $upiCurrency)
                        <option value="upi">{{ __('UPI') }}</option>
                    @endif
                    <option value='url'>{{ __('Link') }}</option>
                    <option value="image">{{ __('Image') }}</option>
                </select>
            </div>
        </div>
        <div class='col-lg-6 col-md-6'>
            <div class='mb-3 mt-2'>
                <label class='form-label required'>{{ __('Label') }}</label>
                <input type='text' class='lbl${id} form-control' name='label[]' placeholder='{{ __('Enter label') }}' required>
            </div>
        </div>
        <div class='col-md-5 mt-1 col-lg-5'>
            <div class='mb-3'>
                <label class='form-label required'>{{ __('Content') }}</label>
                <textarea class='textlbl${id} form-control' name='value[]' cols='30' rows='2' placeholder='{{ __('Enter content') }}'></textarea>

                <input type="file" class="filelbl${id} form-control" name="value[]" accept="image/*" style="display:none;" />

                <small class="text-muted mt-1 d-block">{{ __('If you need to break the word, use a . (dot) as a separator of every sentence.') }}</small>
            </div>
        </div>
        <div class='col-lg-1 col-md-1'>
            <div class='mb-2 pt-1 mt-5'>
                <button class='btn btn-danger btn-sm' onclick='removePayment(${id})'>
                    <i class='fa fa-times text-white'></i>
                </button>
            </div>
        </div>
    </div>`;
                $("#more-payments").append(payments);
            }
        }

        function removePayment(id) {
            "use strict";
            $("#" + id).remove();
            count--;
        }

        function getRandomInt() {
            var min = Math.ceil(0);
            var max = Math.floor(9999999999);
            return Math.floor(Math.random() * (max - min) + min);
        }

        $('#iconSearchInput').on('keyup', function() {
            "use strict";
            const search = $(this).val().toLowerCase().trim();

            $('#iconList > div').css('display', 'none');

            if (search === '') {
                $('#iconList > div').css('display', 'flex');
                return;
            }

            $('#iconList > div').each(function() {
                const fullIconName = $(this).attr('data-icon-name') || "";
                const nameParts = fullIconName.split(' ');
                const iconPart = nameParts.length > 1 ? nameParts[1] : fullIconName;
                const iconNameOnly = iconPart.replace('fa-', '');

                if (fullIconName.toLowerCase().includes(search) ||
                    iconNameOnly.toLowerCase().includes(search)) {
                    $(this).css('display', 'flex');
                }
            });
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
            } else {
                console.warn('selectedIconId is not set.');
            }

            $('#iconPickerModal').modal('hide');
        });

        function changeLabel(id) {
            "use strict";

            var label = 'Label';
            var textlabel = 'Type Something';
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
                textlabel = `{{ __('For ex: Upload your image') }}`;

                textlbl.style.display = 'none';
                fileInput.style.display = 'block';

                textlbl.required = false;
                fileInput.required = true;
            }

            lbl.placeholder = label;
            textlbl.placeholder = textlabel;
        }

        document.getElementById("myForm").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }
    </script>

    <script>
        var elementSelectors = ['.type'];

        function initializeTomSelect(el) {
            new TomSelect(el, {
                copyClassesToDropdown: false,
                dropdownClass: 'dropdown-menu ts-dropdown',
                optionClass: 'dropdown-item',
                controlInput: '<input>',
                maxOptions: null,
                render: {
                    item: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
                        }
                        return '<div>' + escape(data.text) + '</div>';
                    },
                    option: function(data, escape) {
                        if (data.customProperties) {
                            return '<div><span class="dropdown-item-indicator">' + data.customProperties +
                                '</span>' + escape(data.text) + '</div>';
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
    </script>
@endpush
