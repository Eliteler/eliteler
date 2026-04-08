@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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

        .tox-promotion {
            display: none !important;
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
                        <h2 class="page-title">
                            {{ __('Products') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div role="group">
                            {{-- Export --}}
                            <a href="{{ route('user.export.products', $business_card->card_id) }}" class="btn btn-icon btn-primary m-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Export') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-package-export d-lg-none d-inline">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" />
                                    <path d="M12 12l8 -4.5" />
                                    <path d="M12 12v9" />
                                    <path d="M12 12l-8 -4.5" />
                                    <path d="M15 18h7" />
                                    <path d="M19 15l3 3l-3 3" />
                                </svg>
                                <span class="d-lg-inline d-none">{{ __('Export') }}</span>
                            </a>
                            {{-- Import --}}
                            <button type="button" class="btn btn-icon btn-primary m-1" data-bs-toggle="modal"
                                data-bs-target="#modal-import" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                title="{{ __('Import') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-package-import d-lg-none d-inline">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" />
                                    <path d="M12 12l8 -4.5" />
                                    <path d="M12 12v9" />
                                    <path d="M12 12l-8 -4.5" />
                                    <path d="M22 18h-7" />
                                    <path d="M18 15l-3 3l3 3" />
                                </svg>
                                <span class="d-lg-inline d-none">{{ __('Import') }}</span>
                            </button>
                            {{-- Create new --}}
                            <button type="button" class="btn btn-icon btn-primary m-1" onclick="addProduct()"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Create new') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon d-lg-none d-inline" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                <span class="d-lg-inline d-none">{{ __('Create new') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

                {{-- Success of modal --}}
                <div id="successMessage" style="display:none;"
                    class="alert alert-important alert-success alert-dismissible mb-2">
                </div>

                {{-- Failed of modal --}}
                <div id="errorMessage" style="display:none;"
                    class="alert alert-important alert-danger alert-dismissible mb-2">
                </div>

                <div class="card">
                    <div class="row g-0">
                        <div class="col-12 col-md-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader">{{ __('Update Store Category') }}</h4>
                                <div class="list-group list-group-transparent">
                                    {{-- Nav links --}}
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'products',
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <div class="card-body m-0 py-0 px-2">
                                {{-- Categories --}}
                                <div class="row">
                                    <div class="table-responsive">
                                        <table id="productsTable" class="table table-vcenter card-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('#') }}</th>
                                                    <th>{{ __('Category') }}</th>
                                                    <th>{{ __('Image') }}</th>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Description') }}</th>
                                                    <th>{{ __('Stock') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            @include('user.includes.footer')
        </div>

        {{-- Add product --}}
        <div class="modal modal-blur fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">{{ __('Add Product') }}</h5>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm">
                            <div class="row">
                                <input type="hidden" id="cardId" value="{{ $business_card->card_id }}">
                                {{-- Categories --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label required'>{{ __('Categories') }}</label>
                                        <select name='product_category' id='productCategory'
                                            class='form-control categories' required>
                                            <option value='' selected disabled>{{ __('Choose a category') }}
                                            </option>
                                            @foreach ($categories as $category)
                                                <option value='{{ $category->category_id }}'>
                                                    {{ __($category->category_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- Product badge --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label'>{{ __('Product Badge') }}</label>
                                        <input type='text' class='form-control' id='productBadge'
                                            name="product_badge" placeholder='{{ __('Product Badge') }}'>
                                    </div>
                                </div>
                                {{-- Product Image --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Product Image') }}</label>
                                        <div class="input-group mb-2">
                                            <input type="text" class="image form-control" id="productImage"
                                                name="product_image" placeholder="{{ __('Product Image') }}" required>
                                            <button class="btn btn-primary btn-icon" type="button"
                                                onclick="openMedia()">{{ __('Choose image') }}</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Product name --}}
                                <div class="col-md-6 col-xl-6">
                                    <div class="mb-3">
                                        <label for="productName"
                                            class="form-label required">{{ __('Product Name') }}</label>
                                        <input type="text" class="form-control" id="productName" name="product_name"
                                            placeholder="{{ __('Product Name') }}" required>
                                    </div>
                                </div>
                                {{-- Product Short Description --}}
                                <div class="col-md-6 col-xl-6">
                                    <div class="mb-3">
                                        <label for="productShortDescription"
                                            class="form-label required">{{ __('Product Short Description') }}</label>
                                        <input type="text" class="form-control" id="productShortDescription"
                                            name="product_short_description"
                                            placeholder="{{ __('Product Short Description') }}" required>
                                    </div>
                                </div>
                                {{-- Product Description --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <label for="productDescription"
                                            class="form-label required">{{ __('Product Description') }}</label>
                                        <textarea class="form-control product-description" id="productDescription" name="product_description"
                                            placeholder="{{ __('Product Description') }}" required></textarea>
                                    </div>
                                </div>
                                {{-- Regular Price --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label'>{{ __('Regular Price') }}</label>
                                        <input type='number' class='form-control' id="productRegularPrice"
                                            name='product_regular_price' min='1'
                                            placeholder='{{ __('Regular Price') }}' min='1' step='.001'>
                                    </div>
                                </div>
                                {{-- Sales Price --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label required'>{{ __('Sales Price') }}</label>
                                        <input type='number' class='form-control' id="productSalesPrice"
                                            name='product_sales_price[]' min='1' step='.001'
                                            placeholder='{{ __('Sales Price') }}' required>
                                    </div>
                                </div>
                                {{-- Status --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class="mb-3">
                                        <label class='form-label required'
                                            for='product_status'>{{ __('Status') }}</label>
                                        <select id="productStatus" name='product_status'
                                            class='form-control product_status' required>
                                            <option value="instock" selected>{{ __('In Stock') }}</option>
                                            <option value="outstock">{{ __('Out of Stock') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary"
                            onclick="saveProduct()">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Update product --}}
        <div class="modal modal-blur fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">{{ __('Edit Product') }}</h5>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm">
                            <div class="row">
                                {{-- Product category --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label required'>{{ __('Categories') }}</label>
                                        <select name='product_category' id='editProductCategory'
                                            class='form-control categories' required>
                                            @foreach ($categories as $category)
                                                <option value='{{ $category->category_id }}'>
                                                    {{ __($category->category_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- Product badge --}}
                                <div class="col-md-4 col-xl-6">
                                    <input type="hidden" id="productId">
                                    <div class='mb-3'>
                                        <label class='form-label'>{{ __('Product Badge') }}</label>
                                        <input type='text' class='form-control' id='editProductBadge'
                                            name="product_badge" placeholder='{{ __('Product Badge') }}'>
                                    </div>
                                </div>
                                {{-- Product Image --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Product Image') }}</label>
                                        <div class="input-group mb-2">
                                            <input type="text" class="image form-control" id="editProductImage"
                                                name="product_image" placeholder="{{ __('Product Image') }}" required>
                                            <button class="btn btn-primary btn-icon" type="button"
                                                onclick="openMedia()">{{ __('Choose image') }}</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- Product name --}}
                                <div class="col-md-6 col-xl-6">
                                    <div class="mb-3">
                                        <label for="productName"
                                            class="form-label required">{{ __('Product Name') }}</label>
                                        <input type="text" class="form-control" id="editProductName"
                                            name="product_name" required>
                                    </div>
                                </div>
                                {{-- Product Short Description --}}
                                <div class="col-md-6 col-xl-6">
                                    <div class="mb-3">
                                        <label for="productShortDescription"
                                            class="form-label required">{{ __('Product Short Description') }}</label>
                                        <input type="text" class="form-control" id="editProductShortDescription"
                                            name="product_short_description" required>
                                    </div>
                                </div>
                                {{-- Product Description --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <label for="productDescription"
                                            class="form-label required">{{ __('Product Description') }}</label>
                                        <textarea class="form-control product-description" id="editProductDescription" name="product_description" required></textarea>
                                    </div>
                                </div>
                                {{-- Regular Price --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label'>{{ __('Regular Price') }}</label>
                                        <input type='number' class='form-control' id="editProductRegularPrice"
                                            name='product_regular_price' min='1'
                                            placeholder='{{ __('Regular Price') }}' min='1' step='.001'>
                                    </div>
                                </div>
                                {{-- Sales Price --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class='mb-3'>
                                        <label class='form-label required'>{{ __('Sales Price') }}</label>
                                        <input type='number' class='form-control' id="editProductSalesPrice"
                                            name='product_sales_price[]' min='1' step='.001'
                                            placeholder='{{ __('Sales Price') }}' required>
                                    </div>
                                </div>
                                {{-- Status --}}
                                <div class="col-md-4 col-xl-6">
                                    <div class="mb-3">
                                        <label class='form-label required'
                                            for='product_status'>{{ __('Status') }}</label>
                                        <select id="editProductStatus" name='product_status'
                                            class='form-control product_status' required>
                                            <option value="instock">{{ __('In Stock') }}</option>
                                            <option value="outstock">{{ __('Out of Stock') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary"
                            onclick="updateProduct()">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete product modal -->
        <div class="modal modal-blur fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-status"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24"
                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 9v2m0 4v.01" />
                            <path
                                d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                        </svg>
                        <h3>{{ __('Are you sure?') }}</h3>
                        <div id="delete_status" class="text-muted">
                            {{ __('Are you sure you want to delete this product?') }}</div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                                <div class="col">
                                    <a class="btn btn-danger w-100" id="confirmDeleteButton">
                                        {{ __('Yes, proceed') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Media Library --}}
        <div class="modal modal-blur fade" id="openMediaModel" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-full-width modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-status"></div>
                    <div class="modal-body text-center py-4">
                        <h3 class="mb-2">{{ __('Media Library') }}</h3>
                        <div class="text-muted mb-5">
                            {{ __('Upload multiple images') }}
                        </div>

                        {{-- Upload multiple images --}}
                        @include('user.pages.cards.media.upload')

                        {{-- Upload multiple images --}}
                        @include('user.pages.cards.media.list')

                        {{-- Pagination --}}
                        <div id="pagination"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn w-100" data-bs-dismiss="modal">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
                                        {{ __('Select') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Import --}}
        <div class="modal modal-blur fade" id="modal-import" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-import-label">{{ __('Import') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="importForm">
                            <div class="row">
                                <input type="hidden" id="cardId" value="{{ $business_card->card_id }}">
                                {{-- File Import --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('File') }}</label>
                                        <input type="file" class="form-control" name="csv_file" id="importFile"
                                            accept=".csv"
                                            placeholder="{{ __('File') }}" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ __('Close') }}</button>

                        {{-- Sample CSV --}}
                        <a href="{{ asset('storage/import/store/store_products.csv') }}" class="btn btn-secondary" download="sample_import_data_products.csv">
                            {{ __('Sample CSV') }}
                        </a>

                        <button type="button" class="btn btn-primary" onclick="importProduct()">{{ __('Import') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
        <!-- Initialize DataTables -->
        <script>
            // Get products
            $(document).ready(function() {
                "use strict";
                $('#productsTable').DataTable({
                    processing: false,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('user.edit.products', $business_card->card_id) }}", // Replace with your actual API endpoint
                        dataSrc: 'data' // If your data is nested under a key 'data' in the response
                    },
                    language: {
                        "sProcessing": `{{ __('Processing...') }}`,
                        "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                        "sSearch": `{{ __('Search:') }}`,
                        "oPaginate": {
                            "sNext": `{{ __('Next') }}`,
                            "sPrevious": `{{ __('Previous') }}`
                        },
                        "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                        "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                        "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                        "sInfoPostFix": "",
                        "sUrl": "",
                        "oAria": {
                            "sSortAscending": `{{ __(': activate to sort column in ascending order') }}`,
                            "sSortDescending": `{{ __(': activate to sort column in descending order') }}`
                        },
                        loadingRecords: `{{ __('Please wait - loading...') }}`,
                        emptyTable: `{{ __('No data available in the table') }}` // Message for an empty table
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'product_category',
                            name: 'product_category'
                        },
                        {
                            data: 'product_image',
                            name: 'product_image',
                            render: function(data, type, full, meta) {
                                return '<img src="' + data +
                                    '" alt="Product Image" style="width: 50px; height: 50px;"/>';
                            }
                        },
                        {
                            data: 'product_name',
                            name: 'product_name'
                        },
                        {
                            data: 'product_description',
                            name: 'product_description'
                        },
                        {
                            data: 'product_status',
                            name: 'product_status'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });
            });

            // Open Media modal
            function openMedia() {
                "use strict";
                $('#openMediaModel').modal('show');

                loadMedia(); // Initial load

                // Array to store selected media IDs
                var selectedMediaIds = [];

                // Handle individual select
                $(document).on('change', '.select-media', function() {
                    var mediaId = $(this).data('id');
                    if (this.checked) {
                        $(`.media-card[data-id="${mediaId}"]`).addClass('selected');
                        selectedMediaIds.push(mediaId);
                    } else {
                        $(`.media-card[data-id="${mediaId}"]`).removeClass('selected');
                        $('#selectAllMedia').prop('checked', false);
                        // Remove mediaId from the array
                        selectedMediaIds = selectedMediaIds.filter(id => id !== mediaId);
                    }

                    // Set in image url to input field
                    $('.image').val(selectedMediaIds);
                });
            }

            // Open add product modal
            function addProduct() {
                "use strict";
                $('#addProductModal').modal('show');
            }

            // Save Product
            function saveProduct() {
                "use strict";
                var cardId = $('#cardId').val();
                var productBadge = $('#productBadge').val();
                var productCategory = $('#productCategory').val();
                var productImage = $('#productImage').val();
                var productName = $('#productName').val();
                var productShortDescription = $('#productShortDescription').val();
                var productDescription = tinymce.get('productDescription').getContent();
                var productRegularPrice = $('#productRegularPrice').val();
                var productSalesPrice = $('#productSalesPrice').val();
                var productStatus = $('#productStatus').val();

                // Show error message
                function showError(message) {
                    $('#errorMessage').text(message).show();
                    $('#errorMessage').delay(1500).fadeOut(200);
                    $('#addProductModal').modal('hide');
                }

                if (productCategory === '') {
                    showError("{{ __('Product category is required.') }}");
                    return;
                }

                if (productImage === '') {
                    showError("{{ __('Product image is required.') }}");
                    return;
                }

                if (productName === '') {
                    showError("{{ __('Product name is required.') }}");
                    return;
                }

                if (productShortDescription === '') {
                    showError("{{ __('Product short description is required.') }}");
                    return;
                }

                if (productDescription === '') {
                    showError("{{ __('Product description is required.') }}");
                    return;
                }

                if (productSalesPrice === '') {
                    showError("{{ __('Product sales price is required.') }}");
                    return;
                }

                $.ajax({
                    url: '{{ route('user.save.product') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        card_id: cardId,
                        product_badge: productBadge,
                        product_category: productCategory,
                        product_image: productImage,
                        product_name: productName,
                        product_short_description: productShortDescription,
                        product_description: productDescription,
                        product_regular_price: productRegularPrice,
                        product_sales_price: productSalesPrice,
                        product_status: productStatus,
                    },
                    success: function(response) {
                        if (response.success) {
                            // Display success message
                            $('#successMessage').text("{{ __('New Product added successfully!') }}").show();
                            $('#successMessage').delay(1000).fadeOut(1000);
                            $('#addProductModal').modal('hide');
                            $('#addProductForm').trigger("reset");

                            // Reload product table
                            $('#productsTable').DataTable().ajax.reload();
                        } else {
                            // Display error message
                            $('#addProductModal').modal('hide');
                            $('#errorMessage').text("{{ __('Failed to add product.') }}").show();
                            $('#errorMessage').delay(1000).fadeOut(1000);
                        }
                    }
                });
            }

            // Edit product modal
            function editProduct(productId) {
                "use strict";
                $.ajax({
                    url: '/user/store/get-products/' + productId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('#productId').val(response.data.id);
                            $('#editProductBadge').val(response.data.badge);
                            $('#editProductCurrency').val(response.data.currency);
                            $('#editProductImage').val(response.data.product_image);
                            $('#editProductName').val(response.data.product_name);
                            $('#editProductShortDescription').val(response.data.product_short_description);
                            $('#editProductRegularPrice').val(response.data.regular_price);
                            $('#editProductSalesPrice').val(response.data.sales_price);
                            $('#editProductStatus').val(response.data.product_status);

                            let editor = tinymce.get('editProductDescription');
                            if (editor) {
                                let subtitle = response.data.product_description || '';
                                editor.setContent(subtitle);
                            } else {
                                console.error('TinyMCE editor not ready.');
                            }

                            $('#editProductModal').modal('show');
                        } else {
                            // Display error message
                            $('#errorMessage').text("{{ __('Failed to fetch product data.') }}").show();
                            $('#errorMessage').delay(1000).fadeOut(1000);
                        }
                    }
                });
            }

            // Update product
            function updateProduct() {
                "use strict";
                var productId = $('#productId').val();
                var productBadge = $('#editProductBadge').val();
                var productCategory = $('#editProductCategory').val();
                var productImage = $('#editProductImage').val();
                var productName = $('#editProductName').val();
                var productShortDescription = $('#editProductShortDescription').val();
                var productDescription = tinymce.get('editProductDescription').getContent();
                var productRegularPrice = $('#editProductRegularPrice').val();
                var productSalesPrice = $('#editProductSalesPrice').val();
                var productStatus = $('#editProductStatus').val();

                // Show error message
                function showError(message) {
                    $('#errorMessage').text(message).show();
                    $('#errorMessage').delay(1500).fadeOut(200);
                    $('#editProductModal').modal('hide');
                }

                if (productCategory === '') {
                    showError("{{ __('Product category is required.') }}");
                    return;
                }

                if (productImage === '') {
                    showError("{{ __('Product image is required.') }}");
                    return;
                }

                if (productName === '') {
                    showError("{{ __('Product name is required.') }}");
                    return;
                }

                if (productShortDescription === '') {
                    showError("{{ __('Product short description is required.') }}");
                    return;
                }

                if (productDescription === '') {
                    showError("{{ __('Product description is required.') }}");
                    return;
                }

                if (productSalesPrice === '') {
                    showError("{{ __('Product sales price is required.') }}");
                    return;
                }

                $.ajax({
                    url: '{{ route('user.update.product') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_id: productId,
                        product_badge: productBadge,
                        product_category: productCategory,
                        product_image: productImage,
                        product_name: productName,
                        product_short_description: productShortDescription,
                        product_description: productDescription,
                        product_regular_price: productRegularPrice,
                        product_sales_price: productSalesPrice,
                        product_status: productStatus,
                    },
                    success: function(response) {
                        if (response.success) {
                            // Display success message
                            $('#successMessage').text("{{ __('Product updated successfully!') }}").show();
                            $('#successMessage').delay(1000).fadeOut(1000);
                            $('#editProductModal').modal('hide');

                            // Reload products table
                            $('#productsTable').DataTable().ajax.reload();
                        } else {
                            // Display error message
                            $('#editProductModal').modal('hide');
                            $('#errorMessage').text("{{ __('Failed to update product.') }}").show();
                            $('#errorMessage').delay(1000).fadeOut(1000);
                        }
                    }
                });
            }

            // Function to delete the product
            function deleteProduct(productId) {
                $('#deleteProductModal').data('product-id', productId).modal('show');
            }

            // jQuery to handle the modal "Okay" button click
            $(document).ready(function() {
                $('#confirmDeleteButton').click(function() {
                    deleteConfirmProduct();
                });
            });

            // Confirm delete product
            function deleteConfirmProduct() {
                var productId = $('#deleteProductModal').data('product-id');
                $.ajax({
                    url: '/user/delete-product/' + productId,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') // Include CSRF token
                    },
                    success: function(response) {
                        // Display success message
                        $('#successMessage').text("{{ __('Product deleted successfully!') }}").show();
                        $('#successMessage').delay(1000).fadeOut(1000);
                        $('#deleteProductModal').modal('hide');

                        // Reload product table
                        $('#productsTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        // Display error message
                        $('#addProductModal').modal('hide');
                        $('#errorMessage').text("{{ __('Error deleting product') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                });
            }
        </script>
        {{-- Upload image in dropzone --}}
        <script type="text/javascript">
            // Default
            $('#success').hide();
            $('#failed').hide();

            Dropzone.options.dropzone = {
                maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
                acceptedFiles: ".jpeg,.jpg,.png,.gif",
                timeout: 180000,
                success: function(file, response) {
                    if (response.status == 'success') {
                        // Feature Request
                        // $('#success').show();
                        // window.location.href = `{{ route('user.media') }}`;
                        loadMedia();
                    } else {
                        $('#failed').show();
                        $('#failedMessage').html(`<span>` + response.message + `</span>`);
                    }
                }
            };
        </script>
        {{-- Media with pagination --}}
        <script>
            // Default values
            var currentPage = 1;
            var totalPages = 1;

            // Previous image
            function loadPreviousPage() {
                "use strict";

                if (currentPage > 1) {
                    currentPage--;
                    loadMedia(currentPage);
                }
            }

            // Next page
            function loadNextPage() {
                "use strict";

                if (currentPage < totalPages) {
                    currentPage++;
                    loadMedia(currentPage);
                }
            }

            // Load media images
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

            // Media response
            function handleMediaResponse(response) {
                "use strict";

                var mediaData = response.media.data;
                if (mediaData.length > 0) {
                    $('#noImagesFound').hide();
                    $('#showPagination').removeClass('d-none').addClass('card pagination-card');
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

                // Generate media image
                var mediaCardsHtml = '';
                mediaData.forEach(function(media) {
                    mediaCardsHtml += `
        <div class="col-6 col-lg-4 col-xl-2">
            <label class="form-imagecheck mb-2 media-card" data-id="${media.id}">
                <input name="form-imagecheck" type="checkbox" value="3" class="form-imagecheck-input select-media" id="select-media-${media.media_url}" data-id="${media.media_url}">
                    <span class="form-imagecheck-figure">
                    <img src="${media.base_url}${media.media_url}" alt="${media.media_name}" class="form-imagecheck-image" style="height: 200px; object-fit: cover;">
                </span>
            </label>
        </div>
    `;
                });
                $('#mediaCardsContainer').html(mediaCardsHtml);
            }

            // Update pagination
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

            // Load more image in pagination
            $(document).ready(function() {
                "use strict";

                loadMedia(); // Initial load
            });
        </script>

        {{-- Clipboard --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                "use strict";

                var clipboard = new ClipboardJS('.copyBoard');

                // Success
                clipboard.on('success', function(e) {
                    "use strict";

                    // Place value in the field
                    $('.image').val(e.text);

                    // Hide media modal
                    $('#openMediaModel').modal('hide');
                });

                // Error
                clipboard.on('error', function(e) {
                    "use strict";

                    showErrorAlert('{{ __('Failed to copy text to clipboard. Please try again.') }}');
                });

                // Show success message
                function showSuccessAlert(message) {
                    "use strict";

                    showAlert(message, 'success');
                }

                // Show error message
                function showErrorAlert(message) {
                    "use strict";

                    showAlert(message, 'danger');
                }

                // Show alert
                function showAlert(message, type) {
                    "use strict";

                    var alertDiv = document.createElement('div');
                    alertDiv.classList.add('alert', 'alert-important', 'alert-' + type, 'alert-dismissible');
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

            // Tiny MCE
            tinymce.init({
                selector: 'textarea.product-description',
                plugins: 'preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount charmap quickbars emoticons',
                menubar: 'file edit view insert format tools',
                toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl',
                content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:16px }',
                height: "200",
                menubar: false,
                statusbar: false,

                // Correct mobile settings (limited fields allowed)
                mobile: {
                    plugins: 'paste',
                    toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
                }
            });
        </script>
        <script>
            // Import
            function importProduct() {
                "use strict";

                var form = $('#importForm')[0]; // Get the form element
                var formData = new FormData(form); // Create FormData from the form
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('card_id', $('#cardId').val()); // Add extra field not in form

                $.ajax({
                    url: '{{ route('user.import.products') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Display success message
                            $('#successMessage').text(response.message).show();
                            $('#successMessage').delay(3000).fadeOut(1000);
                            $('#modal-import').modal('hide');
                            $('#importForm').trigger("reset");

                            // Reload product table
                            $('#productsTable').DataTable().ajax.reload();
                        } else {
                            // Display error message
                            $('#modal-import').modal('hide');
                            $('#errorMessage').text(response.message).show();
                            $('#errorMessage').delay(3000).fadeOut(1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Display error message
                        $('#modal-import').modal('hide');
                        $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                        $('#errorMessage').delay(3000).fadeOut(1000);
                    }
                });
            }
        </script>
    @endpush
@endsection
