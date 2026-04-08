@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@php
    use Illuminate\Support\Facades\DB;

    // Get config
    $config = DB::table('config')->get();
@endphp

@section('css')
<link rel="stylesheet" href="{{ asset('css/all.css') }}" />
<link rel="stylesheet" href="{{ asset('css/bootstrap-iconpicker.min.css') }}" />
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
                    <h2 class="page-title">
                        {{ __('Payments') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-fluid">
            {{-- Failed --}}
            @if(Session::has("failed"))
            <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('failed')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            {{-- Success --}}
            @if(Session::has("success"))
            <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                <div class="d-flex">
                    <div>
                        {{Session::get('success')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif
            
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('user.save.payment.links', Request::segment(3)) }}" id="myForm" method="post"
                        class="card" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <div id="more-payments" class="row"></div>
                                <div class="col-lg-12">
                                    <button type="button" onclick="addPayment()" class="btn btn-primary">
                                        {{ __('Add One More Payment') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <div class="d-flex">
                              <a href="{{ route('user.services', Request::segment(3)) }}" class="btn btn-outline-primary ms-2">{{ __('Skip') }}</a>
                              <button type="submit" class="btn btn-primary ms-auto">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    @include('user.includes.footer')
</div>

<!-- Icon Picker Modal -->
<div class="modal modal-blur fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable py-7" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Choose an Icon') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" id="iconSearchInput" class="form-control" placeholder="Search icons..." />
                </div>

                @php
                    $icons = include resource_path('views/user/includes/icons.php');
                @endphp

                <div class="row gap-2" id="iconList">
                    @foreach ($icons as $icon)
                        <div class="col-2 col-md-1 p-3 icon-padding" data-icon-name="{{ $icon }}">
                            <div role="button" class="icon-option" data-icon="{{ $icon }}">
                                <i class="{{ $icon }} fa-xl"></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom JS --}}
@push('custom-js')
<script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/fontawesome-iconpicker.min.js') }}"></script>
<script>
    var count = 0;
    function addPayment() {
	"use strict";
    if (count>={{ $plan_details->no_of_payments}}) {
        new swal({
        title: `{{ __('Oops!') }}`,
        icon: 'warning',
        text: `{{ __('You have reached your current plan limit.') }}`,
        timer: 2000,
        buttons: false,
        showConfirmButton: false,
    });
    }
    else {
        count++;
        var id = getRandomInt();

        @php
            // UPI is only supported if the configured currency is INR
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
                    <input type='text' class='lbl${id} form-control' name='label[]' placeholder='{{ __('Label') }}' required>
                </div>
            </div>
            <div class='col-md-5 mt-1 col-lg-5'>
                <div class='mb-3'>
                    <label class='form-label required'>{{ __('Content') }}</label>
                    <textarea class='textlbl${id} form-control' name='value[]' cols='30' rows='2' placeholder='{{ __('Type something') }}'></textarea>

                    <input type="file" class="filelbl${id} form-control" name="value[]" accept="image/*" style="display:none;" />

                    {{-- Notes: How to Break word --}}
                    <small>{{ __('If you need to break the word, use a . (dot) as a separator of every sentence.') }}</small>
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
        $("#more-payments").append(payments).html();
    }
    }

    function removePayment(id) {
	"use strict";
        $("#"+id).remove();
        count--;
    }

    function getRandomInt() {
        min = Math.ceil(0);
        max = Math.floor(9999999999);
        return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
    }

    // icon search
    $('#iconSearchInput').on('keyup', function() {
        "use strict";
        const search = $(this).val().toLowerCase().trim();

        // Hide all icons first
        $('#iconList > div').css('display', 'none');

        if (search === '') {
            // If search is empty, show all icons
            $('#iconList > div').css('display', 'flex');
            return;
        }

        // Then show only the matching ones
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

    // Icon Picker
    let selectedIconId = null;

    // Open icon picker
    function openPicker(id) {
        "use strict";

        selectedIconId = id; // Save which input to update
        $("#iconPickerModal").modal('show'); // Show the modal
    }

    // Handle icon selection
    $(document).on('click', '.icon-option', function () {
        "use strict";

        const selectedIcon = $(this).data('icon'); // Get selected icon class or name

        if (selectedIconId !== null) {
            // Update the hidden input or any input with the selected icon
            $('#iconpick' + selectedIconId).val(selectedIcon);
        } else {
            console.warn('selectedIconId is not set.');
        }

        // Hide the modal after selection
        $('#iconPickerModal').modal('hide');
    });

    function changeLabel(id) {
        "use strict";

        var label = 'Label';
        var textlabel = 'Type Something';
        let lbl = document.querySelector('.lbl'+id);
        let textlbl = document.querySelector('.textlbl'+id);
        let fileInput = document.querySelector('.filelbl'+id);
        let type = document.querySelector('.type'+id).value;
        
        if(type == 'text') {
            label = `{{ __('Bank')}}`;
            textlabel = `{{ __('Your bank account details') }}`;

            textlbl.style.display = 'block';
            fileInput.style.display = 'none';

            // Required
            textlbl.required = true;
            fileInput.required = false;
        } else if(type == 'upi') {
            label = `{{ __('UPI') }}`;
            textlabel = `{{ __('For ex: YOUR UPI ID') }}`;

            textlbl.style.display = 'block';
            fileInput.style.display = 'none';

            // Required
            textlbl.required = true;
            fileInput.required = false;
        } else if(type == 'url') {
            label = `{{ __('Payment Link') }}`;
            textlabel = `{{ __('For ex: https://rzp.io/i/nxrHnLJ') }}`;

            textlbl.style.display = 'block';
            fileInput.style.display = 'none';

            // Required
            textlbl.required = true;
            fileInput.required = false;
        } else if(type == 'image') {
            label = `{{ __('Image') }}`;
            textlabel = `{{ __('For ex: Upload your image') }}`;

            textlbl.style.display = 'none';
            fileInput.style.display = 'block';

            // Required
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
    // Array of element selectors
    var elementSelectors = ['.type'];

    // Function to initialize TomSelect on an element
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
                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
                option: function(data, escape) {
                    if (data.customProperties) {
                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                    }
                    return '<div>' + escape(data.text) + '</div>';
                },
            },
        });
    }

    // Initialize TomSelect on existing elements
    elementSelectors.forEach(function(selector) {
        var elements = document.querySelectorAll(selector);
        elements.forEach(function(el) {
            initializeTomSelect(el);
        });
    });

    // Observe the document for dynamically added elements
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Ensure it's an element node
                    elementSelectors.forEach(function(selector) {
                        if (node.matches(selector)) {
                            initializeTomSelect(node);
                        }
                        // Also check if new nodes have children that match
                        var childElements = node.querySelectorAll(selector);
                        childElements.forEach(function(childEl) {
                            initializeTomSelect(childEl);
                        });
                    });
                }
            });
        });
    });

    // Configure the observer
    observer.observe(document.body, { childList: true, subtree: true });
</script>
@endpush
@endsection
