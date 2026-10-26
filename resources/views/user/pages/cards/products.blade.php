@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS (Kept dry) --}}
@section('css')
    <link href="{{ asset('css/dropzone.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script src="{{ asset('js/clipboard.min.js') }}"></script>
    <style>
        .btn-group-sm>.btn,
        .btn-sm {
            --tblr-btn-line-height: 1.5;
            --tblr-btn-icon-size: .75rem;
            margin-right: 5px;
            font-size: 12px !important;
            margin: 13px 0 10px 5px !important;
        }

        .li-link {
            padding: 10px;
            margin: 4px;
        }

        .btn.disabled,
        .btn:disabled,
        fieldset:disabled .btn {
            border-color: #0000 !important;
        }

        .custom-nav {
            position: absolute;
            right: 5px;
            top: -2px;
        }

        .media-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header (Wraps the inner container-fluid) -->
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
                                {{ __('Products') }}
                            </li>
                        </ol>
                    </nav>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Products') }}
                        </h2>

                        <!-- Descriptive Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Add inventory items, assign retail pricing parameters, configure stock rules, and list dynamic digital products.') }}
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
                            <form action="{{ route('user.save.vproducts', Request::segment(3)) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-xl-12">
                                            <div id="more-products" class="row g-3 mt-1"></div>

                                            <div class="col-12 mt-4">
                                                <button type="button" onclick="addProduct()"
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
                                </div>

                                <!-- Form Footer containing save trigger styled alone -->
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

    {{-- Media Library upgraded to Borderless Shadowed Modal --}}
    <div class="modal modal-bg fade" id="openMediaModel" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-full-width modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-sm">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <h3 class="mb-2">{{ __('Media Library') }}</h3>
                    <div class="text-muted mb-5">
                        {{ __('Upload multiple images') }}
                    </div>

                    <div id="uploadMessage"></div>

                    {{-- Upload template mapping --}}
                    @include('user.pages.cards.media.upload')

                    {{-- Files directory rendering --}}
                    @include('user.pages.cards.media.list')

                    <div id="pagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        var count = 0;
        var currentSelection = 0;

        function addProduct() {
            "use strict";
            if (count >= {{ $plan_details->no_of_vcard_products }}) {
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
                var products = `<div class='row' id=` + id + `>
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Product Badge') }}</label>
                <input type='text' class='form-control' name='badge[]' placeholder='{{ __('Enter product badge') }}'>
            </div>
        </div>

        <div class="col-md-6 col-xl-6">
            <div class="mb-3">
                <label class="form-label" for="currency">${id}">{{ __('Currency') }}</label>
                <select name="currency[]" id="currency" class="form-control currency">
                    <option value="" selected disabled>{{ __('Choose currency') }}</option>
                    @foreach ($currencies as $currency)
                    <option value="null" selected>{{ __('No Currency') }}</option>
                    <option value="{{ $currency->iso_code }}">
                        {{ $currency->name }} ({{ $currency->symbol }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label required'>{{ __('Product Image') }}</label>
                <div class='input-group mb-2'>
                    <input type='text' class='image` + id + ` media-model form-control' name='product_image[]' placeholder='{{ __('Product Image') }}' required>
                    <button class='btn btn-primary btn-md' type='button' onclick='openMedia(` + id +
                    `)'>{{ __('Choose image') }}</button>
                </div>
            </div>
        </div>
        
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label required'>{{ __('Product Name') }}</label>
                <input type='text' class='form-control' name='product_name[]' placeholder='{{ __('Enter product name') }}' required>
            </div>
        </div>
        
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Product Description') }}</label>
                <textarea class='form-control' name='product_description[]' data-bs-toggle='autosize' placeholder='{{ __('Enter product description') }}'></textarea>
            </div>
        </div>
        
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Regular Price') }}</label>
                <input type='number' class='form-control' name='regular_price[]' placeholder='{{ __('Enter regular price') }}' min='1' step='.001'>
            </div>
        </div>
        
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Sales Price') }}</label>
                <input type='number' class='form-control' name='sales_price[]' min='1' step='.001' placeholder='{{ __('Enter sales price') }}'>
            </div>
        </div>
        
        <div class='col-md-6 col-xl-6'>
            <div class='mb-3'>
                <label class='form-label' for='product_status'>{{ __('Status') }}</label>
                <select name='product_status[]' id='product_status' class='form-control product_status' {{ $whatsAppNumberExists != true ? 'disabled' : '' }}>
                    <option value="null" selected>{{ __('No Status') }}</option>
                    <option value='instock'>{{ __('In Stock') }}</option>
                    <option value='outstock'>{{ __('Out of Stock') }}</option>
                </select>

                @if ($whatsAppNumberExists != true)
                    <p class="h6 mt-1 text-danger">{{ __("'Inquiry button' is disabled as you have not entered whatsapp number. Go to the 'Social Links' page and enter the WhatsApp number.") }}</p>
                @endif

                <a href='#' class='btn mt-3 btn-danger btn-sm d-inline-flex align-items-center' onclick='removeProduct(` +
                    id + `)'>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    {{ __('Remove') }}
                </a>
            </div>
        </div>
        </div>
        <br>
        </div>
        </div>`;

                $("#more-products").append(products);
            }
        }

        function removeProduct(id) {
            "use strict";
            $("#" + id).remove();
        }

        function getRandomInt() {
            var min = Math.ceil(0);
            var max = Math.floor(9999999999);
            return Math.floor(Math.random() * (max - min) + min);
        }

        function openMedia(id) {
            "use strict";
            currentSelection = id;
            $('#openMediaModel').modal('show');
        }
    </script>

    {{-- Dropzone configurations mappings --}}
    <script type="text/javascript">
        Dropzone.options.dropzone = {
            maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            timeout: 180000,

            init: function() {
                $('#success').remove();
                $('#failed').remove();

                this.on("success", function(file, response) {
                    $('#uploadMessage').empty();

                    if (response.status === 'success') {
                        $('#uploadMessage').html(`
                            <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm" role="alert">
                                ${response.message || '{{ __('Image uploaded successfully!') }}'}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);

                        this.removeFile(file);
                        loadMedia();

                        setTimeout(function() {
                            $('#uploadMessage .alert').fadeOut();
                        }, 3000);
                    } else {
                        $('#uploadMessage').html(`
                            <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm" role="alert">
                                ${response.message || '{{ __('Upload failed. Please try again.') }}'}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }
                });

                this.on("error", function(file, response) {
                    $('#uploadMessage').html(`
                        <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm" role="alert">
                            {{ __('Upload failed. Please try again.') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    this.removeFile(file);
                });
            }
        };
    </script>

    {{-- Media with custom pagination controls --}}
    <script>
        var currentPage = 1;
        var totalPages = 1;

        function loadPreviousPage() {
            "use strict";
            if (currentPage > 1) {
                currentPage--;
                loadMedia(currentPage);
            }
        }

        function loadNextPage() {
            "use strict";
            if (currentPage < totalPages) {
                currentPage++;
                loadMedia(currentPage);
            }
        }

        function loadMedia(page = 1) {
            $.ajax({
                url: '{{ route('user.media') }}',
                method: 'GET',
                data: {
                    page: page
                },
                dataType: 'json',
                success: handleMediaResponse,
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function handleMediaResponse(response) {
            "use strict";
            var mediaData = response.media.data;
            if (mediaData.length > 0) {
                $('#noImagesFound').hide();
                $('#showPagination').removeClass('d-none').addClass('card border-0 shadow-sm pagination-card');
                displayMediaCards(mediaData);
                updatePaginationInfo(response.media);
            } else {
                $('#noImagesFound').show();
                $('#showPagination').addClass('d-none');
                $('#mediaCardsContainer').html('');
                updatePaginationInfo(response.media);
            }
        }

        function displayMediaCards(mediaData) {
            "use strict";
            var mediaCardsHtml = '';
            mediaData.forEach(function(media) {
                mediaCardsHtml += `
                    <div class="col-6 col-lg-4 col-xl-2 mb-4">
                        <div class="card border-0 shadow-sm">
                            <img src="${media.base_url}${media.media_url}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="${media.media_name}">
                            <div class="card-body">
                                <h5 class="card-title media-name mb-2">${media.media_name}</h5>
                                <a class="btn btn-icon btn-primary btn-md copyBoard w-100 justify-content-center" data-clipboard-text="${media.media_url}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Copy') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <rect x="8" y="8" width="12" height="12" rx="2"></rect>
                                        <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#mediaCardsContainer').html(mediaCardsHtml);
        }

        function updatePaginationInfo(media) {
            "use strict";
            $('#paginationStartIndex').text(media.from);
            $('#paginationEndIndex').text(media.to);
            $('#paginationTotalCount').text(media.total);
            currentPage = media.current_page;
            totalPages = media.last_page;

            $('#prevPageBtn').prop('disabled', currentPage <= 1);
            $('#nextPageBtn').prop('disabled', currentPage >= totalPages);
        }

        $(document).ready(function() {
            "use strict";
            loadMedia();
        });
    </script>

    {{-- Clipboard logic handles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";
            var clipboard = new ClipboardJS('.copyBoard');

            clipboard.on('success', function(e) {
                "use strict";
                $('.image' + currentSelection).val(e.text);
                $('#openMediaModel').modal('hide');
            });

            clipboard.on('error', function(e) {
                "use strict";
                showErrorAlert('{{ __('Failed to copy text to clipboard. Please try again.') }}');
            });

            function showSuccessAlert(message) {
                "use strict";
                showAlert(message, 'success');
            }

            function showErrorAlert(message) {
                "use strict";
                showAlert(message, 'danger');
            }

            function showAlert(message, type) {
                "use strict";
                var alertDiv = document.createElement('div');
                alertDiv.classList.add('alert', 'alert-important', 'alert-' + type, 'alert-dismissible', 'border-0',
                    'shadow-sm', 'mb-3');
                alertDiv.setAttribute('role', 'alert');

                var innerContent = '<div class="d-flex">' +
                    '<div>' +
                    message +
                    '</div>' +
                    '</div>' +
                    '<a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>';

                alertDiv.innerHTML = innerContent;
                document.querySelector('#showAlert').appendChild(alertDiv);

                setTimeout(function() {
                    "use strict";
                    alertDiv.remove();
                }, 3000);
            }
        });
    </script>

    {{-- Initialization handlers mapping selectors --}}
    <script>
        var elementSelectors = ['.currency', '.product_status'];

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

            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            el.dispatchEvent(new Event('change'));
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
