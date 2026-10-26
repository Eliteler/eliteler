@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-products.css') }}" rel="stylesheet">

    {{-- Dropzone CSS --}}
    <link href="{{ asset('css/dropzone.min.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-arrows">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.dashboard') }}"
                                        class="text-muted d-inline-flex align-items-center gap-1">
                                        {{ __('Dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('user.stores') }}" class="text-muted">{{ __('Stores') }}</a>
                                </li>
                                <li class="breadcrumb-item active fw-semibold" aria-current="page">
                                    {{ __('Products') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Store Products') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Manage, track, import, and export your digital store catalogs and service listings.') }}
                        </div>
                    </div>

                    <div class="col-auto ms-auto d-print-none">
                        <div class="d-flex gap-2">
                            {{-- Export Button --}}
                            <a href="{{ route('user.export.products', $business_card->card_id) }}"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Export Products') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package-export"
                                    width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" />
                                    <path d="M12 12l8 -4.5" />
                                    <path d="M12 12v9" />
                                    <path d="M12 12l-8 -4.5" />
                                    <path d="M15 18h7" />
                                    <path d="M19 15l3 3l-3 3" />
                                </svg>
                                <span class="d-none d-sm-inline">{{ __('Export') }}</span>
                            </a>

                            {{-- Import Modal Trigger --}}
                            <button type="button"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#modal-import" title="{{ __('Import Products') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package-import"
                                    width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" />
                                    <path d="M12 12l8 -4.5" />
                                    <path d="M12 12v9" />
                                    <path d="M12 12l-8 -4.5" />
                                    <path d="M22 18h-7" />
                                    <path d="M18 15l-3 3l3 3" />
                                </svg>
                                <span class="d-none d-sm-inline">{{ __('Import') }}</span>
                            </button>

                            {{-- Add Product Button --}}
                            <button type="button"
                                class="btn btn-primary btn-icon-only d-inline-flex align-items-center gap-1 shadow-sm"
                                onclick="addProduct()">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                    width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                <span class="d-none d-sm-inline">{{ __('Add Product') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Flash Notifications --}}
                @if (Session::has('failed'))
                    <div class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('failed') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                @if (Session::has('success'))
                    <div class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <div>{{ Session::get('success') }}</div>
                        </div>
                        <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                @endif

                <div id="successMessage" style="display:none;"
                    class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"></div>
                <div id="errorMessage" style="display:none;"
                    class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"></div>

                <!-- Unified Settings Card -->
                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        {{-- Left Navigation Sidebar --}}
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-muted fw-bold">{{ __('Store Settings') }}</h4>
                                <div class="list-group list-group-transparent mb-0">
                                    @include('user.pages.edit-store.include.nav-link', [
                                        'link' => 'products',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Data Table Container --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <div class="card-body m-0">
                                <table id="productsTable" class="table card-table table-vcenter text-nowrap table-hover">
                                    <thead>
                                        <tr>
                                            <th class="w-1">{{ __('#') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Image') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Stock') }}</th>
                                            <th class="w-1 text-end">{{ __('Actions') }}</th>
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

        @include('user.includes.footer')
    </div>

    {{-- Add Product Modal --}}
    <div class="modal modal-bg fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addProductModalLabel">{{ __('Add New Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <input type="hidden" id="cardId" value="{{ $business_card->card_id }}">

                        <div class="row g-3">
                            {{-- Category --}}
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('Category') }}</label>
                                <select name="product_category" id="productCategory" class="form-control categories"
                                    required>
                                    <option value="" selected disabled>{{ __('Choose a category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->category_id }}">{{ __($category->category_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product Badge --}}
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Product Badge') }}</label>
                                <input type="text" class="form-control" id="productBadge" name="product_badge"
                                    placeholder="{{ __('e.g., 20% OFF, Popular') }}">
                            </div>

                            {{-- Product Images Selector --}}
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('Product Images') }}</label>
                                <div class="input-group">
                                    <input type="text" class="image form-control" id="productImage"
                                        name="product_image" placeholder="{{ __('Select one or more images') }}" required
                                        readonly>
                                    <button class="btn btn-primary d-inline-flex align-items-center gap-1" type="button"
                                        onclick="openMedia('productImage')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo"
                                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M15 8h.01" />
                                            <path
                                                d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" />
                                            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                                            <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" />
                                        </svg>
                                        <span>{{ __('Browse') }}</span>
                                    </button>
                                </div>
                                <small class="text-muted"
                                    style="font-size: 0.75rem;">{{ __('You can select multiple photos from the media library.') }}</small>
                                <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 800 px']) }}</small>
                            </div>

                            {{-- Product Name --}}
                            <div class="col-md-6">
                                <label for="productName" class="form-label required">{{ __('Product Name') }}</label>
                                <input type="text" class="form-control" id="productName" name="product_name"
                                    placeholder="{{ __('Enter product name') }}" required>
                            </div>

                            {{-- Product Short Description --}}
                            <div class="col-md-6">
                                <label for="productShortDescription"
                                    class="form-label required">{{ __('Product Short Description') }}</label>
                                <input type="text" class="form-control" id="productShortDescription"
                                    name="product_short_description" placeholder="{{ __('Enter brief summary') }}"
                                    required>
                            </div>

                            {{-- Product Detailed Description --}}
                            <div class="col-12">
                                <label for="productDescription"
                                    class="form-label required">{{ __('Product Description') }}</label>
                                <textarea class="form-control product-description" id="productDescription" name="product_description" rows="4"
                                    placeholder="{{ __('Enter complete product details') }}"></textarea>
                            </div>

                            {{-- Regular Price --}}
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Regular Price') }}</label>
                                <input type="number" class="form-control" id="productRegularPrice"
                                    name="product_regular_price" min="0" placeholder="{{ __('0.00') }}"
                                    step=".001">
                            </div>

                            {{-- Sales Price --}}
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('Sales Price') }}</label>
                                <input type="number" class="form-control" id="productSalesPrice"
                                    name="product_sales_price" min="0" step=".001"
                                    placeholder="{{ __('0.00') }}" required>
                            </div>

                            {{-- Stock Status --}}
                            <div class="col-md-4">
                                <label class="form-label required"
                                    for="productStatus">{{ __('Inventory Status') }}</label>
                                <select id="productStatus" name="product_status" class="form-control product_status"
                                    required>
                                    <option value="instock" selected>{{ __('In Stock') }}</option>
                                    <option value="outstock">{{ __('Out of Stock') }}</option>
                                </select>
                            </div>

                            {{-- Modern Tabler Variations Toggle Box --}}
                            <div class="col-12 mt-3 pt-3 border-top">
                                <div class="card bg-surface-secondary border shadow-none mb-3">
                                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <h4 class="card-title mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-layers-intersect text-primary"
                                                    width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <rect x="8" y="4" width="12" height="12" rx="2" />
                                                    <rect x="4" y="8" width="12" height="12" rx="2" />
                                                </svg>
                                                {{ __('Product Variations') }}
                                            </h4>
                                            <div class="text-secondary small">
                                                {{ __('Enable this if you offer different options such as size, color, or material with distinct prices.') }}
                                            </div>
                                        </div>
                                        <label class="form-check form-switch form-switch-lg m-0 cursor-pointer">
                                            <input class="form-check-input" type="checkbox" id="addHasVariants"
                                                onchange="toggleVariants('add')">
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Modern Tabler Variations Table Section --}}
                            <div class="col-12 d-none" id="addVariantsSection">
                                <div class="card border rounded-3 overflow-hidden shadow-none mb-0">
                                    <div
                                        class="card-header bg-surface py-2 px-3 d-flex align-items-center justify-content-between">
                                        <h4 class="card-title text-muted fw-semibold small m-0 text-uppercase"
                                            style="letter-spacing: 0.05em;">
                                            {{ __('Manage Variations & Stock') }}
                                        </h4>
                                        <button type="button"
                                            class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-3"
                                            onclick="addVariantRow('add')">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-plus" width="16" height="16"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                            <span>{{ __('Add Variation') }}</span>
                                        </button>
                                    </div>
                                    <div class="table-responsive m-0">
                                        <table class="table card-table table-vcenter text-nowrap" id="addVariantsTable">
                                            <thead class="bg-surface-secondary text-secondary small text-uppercase">
                                                <tr>
                                                    <th style="min-width: 250px;">
                                                        {{ __('Variant Name (e.g., Size, Color)') }} <span
                                                            class="text-danger">*</span></th>
                                                    <th style="width: 160px;">{{ __('Regular Price') }}</th>
                                                    <th style="width: 160px;">{{ __('Sales Price') }} <span
                                                            class="text-danger">*</span></th>
                                                    <th style="width: 160px;">{{ __('Status') }}</th>
                                                    <th class="w-1 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="saveProduct()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M14 4l0 4l-4 0l0 -4" />
                        </svg>
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Product Modal --}}
    <div class="modal modal-bg fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editProductModalLabel">{{ __('Edit Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm">
                        <input type="hidden" id="productId">
                        <div class="row g-3">
                            {{-- Category --}}
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('Category') }}</label>
                                <select name="product_category" id="editProductCategory" class="form-control categories"
                                    required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->category_id }}">{{ __($category->category_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Product Badge --}}
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Product Badge') }}</label>
                                <input type="text" class="form-control" id="editProductBadge" name="product_badge"
                                    placeholder="{{ __('e.g., 20% OFF') }}">
                            </div>

                            {{-- Product Images Selector --}}
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('Product Images') }}</label>
                                <div class="input-group">
                                    <input type="text" class="image form-control" id="editProductImage"
                                        name="product_image" placeholder="{{ __('Select one or more images') }}" required
                                        readonly>
                                    <button class="btn btn-primary d-inline-flex align-items-center gap-1" type="button"
                                        onclick="openMedia('editProductImage')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo"
                                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M15 8h.01" />
                                            <path
                                                d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" />
                                            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" />
                                            <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" />
                                        </svg>
                                        <span>{{ __('Browse') }}</span>
                                    </button>
                                </div>
                                <small class="text-muted"
                                    style="font-size: 0.75rem;">{{ __('You can select multiple photos from the media library.') }}</small>
                                <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 800 px']) }}</small>
                            </div>

                            {{-- Product Name --}}
                            <div class="col-md-6">
                                <label for="editProductName" class="form-label required">{{ __('Product Name') }}</label>
                                <input type="text" class="form-control" id="editProductName"
                                    placeholder="{{ __('Enter product name') }}" name="product_name" required>
                            </div>

                            {{-- Product Short Description --}}
                            <div class="col-md-6">
                                <label for="editProductShortDescription"
                                    class="form-label required">{{ __('Product Short Description') }}</label>
                                <input type="text" class="form-control" id="editProductShortDescription"
                                    placeholder="{{ __('Enter brief summary') }}" name="product_short_description"
                                    required>
                            </div>

                            {{-- Product Description --}}
                            <div class="col-12">
                                <label for="editProductDescription"
                                    class="form-label required">{{ __('Product Description') }}</label>
                                <textarea class="form-control product-description" id="editProductDescription" name="product_description"
                                    rows="4" placeholder="{{ __('Enter product description') }}"></textarea>
                            </div>

                            {{-- Regular Price --}}
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Regular Price') }}</label>
                                <input type="number" class="form-control" id="editProductRegularPrice"
                                    name="product_regular_price" min="0" placeholder="{{ __('0.00') }}"
                                    step=".001">
                            </div>

                            {{-- Sales Price --}}
                            <div class="col-md-4">
                                <label class="form-label required">{{ __('Sales Price') }}</label>
                                <input type="number" class="form-control" id="editProductSalesPrice"
                                    name="product_sales_price" min="0" step=".001"
                                    placeholder="{{ __('0.00') }}" required>
                            </div>

                            {{-- Stock Status --}}
                            <div class="col-md-4">
                                <label class="form-label required"
                                    for="editProductStatus">{{ __('Inventory Status') }}</label>
                                <select id="editProductStatus" name="product_status" class="form-control product_status"
                                    required>
                                    <option value="instock">{{ __('In Stock') }}</option>
                                    <option value="outstock">{{ __('Out of Stock') }}</option>
                                </select>
                            </div>

                            {{-- Modern Tabler Variations Toggle Box --}}
                            <div class="col-12 mt-3 pt-3 border-top">
                                <div class="card bg-surface-secondary border shadow-none mb-3">
                                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <h4 class="card-title mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-layers-intersect text-primary"
                                                    width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <rect x="8" y="4" width="12" height="12" rx="2" />
                                                    <rect x="4" y="8" width="12" height="12" rx="2" />
                                                </svg>
                                                {{ __('Product Variations') }}
                                            </h4>
                                            <div class="text-secondary small">
                                                {{ __('Enable this if you offer different options such as size, color, or material with distinct prices.') }}
                                            </div>
                                        </div>
                                        <label class="form-check form-switch form-switch-lg m-0 cursor-pointer">
                                            <input class="form-check-input" type="checkbox" id="editHasVariants"
                                                onchange="toggleVariants('edit')">
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Modern Tabler Variations Table Section --}}
                            <div class="col-12 d-none" id="editVariantsSection">
                                <div class="card border rounded-3 overflow-hidden shadow-none mb-0">
                                    <div
                                        class="card-header bg-surface py-2 px-3 d-flex align-items-center justify-content-between">
                                        <h4 class="card-title text-muted fw-semibold small m-0 text-uppercase"
                                            style="letter-spacing: 0.05em;">
                                            {{ __('Manage Variations & Stock') }}
                                        </h4>
                                        <button type="button"
                                            class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-3"
                                            onclick="addVariantRow('edit')">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-plus" width="16" height="16"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                            <span>{{ __('Add Variation') }}</span>
                                        </button>
                                    </div>
                                    <div class="table-responsive m-0">
                                        <table class="table card-table table-vcenter text-nowrap" id="editVariantsTable">
                                            <thead class="bg-surface-secondary text-secondary small text-uppercase">
                                                <tr>
                                                    <th style="min-width: 250px;">
                                                        {{ __('Variant Name (e.g., Size, Color)') }} <span
                                                            class="text-danger">*</span></th>
                                                    <th style="width: 160px;">{{ __('Regular Price') }}</th>
                                                    <th style="width: 160px;">{{ __('Sales Price') }} <span
                                                            class="text-danger">*</span></th>
                                                    <th style="width: 160px;">{{ __('Status') }}</th>
                                                    <th class="w-1 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="updateProduct()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                            <path d="M14 4l0 4l-4 0l0 -4" />
                        </svg>
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Product Modal --}}
    <div class="modal modal-bg fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-bs-backdrop="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-danger-lt mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash text-danger"
                            width="28" height="28" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 7l16 0" />
                            <path d="M10 11l0 6" />
                            <path d="M14 11l0 6" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </div>
                    <h3 class="fw-bold">{{ __('Are you sure?') }}</h3>
                    <div class="text-muted">{{ __('This product item will be permanently removed from your catalog.') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-primary-lt w-100" data-bs-dismiss="modal">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100" id="confirmDeleteButton">
                                    {{ __('Yes, Delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Multi-Select Media Library Modal --}}
    <div class="modal modal-bg fade" id="openMediaModel" tabindex="-1" aria-labelledby="openMediaModelLabel"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div
                    class="modal-header border-bottom py-3 px-3 px-md-4 bg-surface d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 gap-md-3">
                        <div class="avatar avatar-md bg-primary-lt rounded-circle flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-photo text-primary" width="22" height="22"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <line x1="15" y1="8" x2="15.01" y2="8" />
                                <rect x="4" y="4" width="16" height="16" rx="3" />
                                <path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" />
                                <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold mb-0 text-reset fs-3" id="openMediaModelLabel">
                                {{ __('Media Library') }}
                            </h4>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                {{ __('Select multiple photos or upload new assets') }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-1 bg-surface-secondary p-1 rounded-3" role="tablist">
                        <button
                            class="nav-link active px-3 py-1 fw-semibold small d-inline-flex align-items-center gap-1 border-0 bg-transparent text-muted"
                            data-bs-toggle="tab" data-bs-target="#modal-tab-browse" type="button" role="tab">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-folders text-primary" width="16" height="16"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M9 4h3l2 2h5a2 2 0 0 1 2 2v7a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" />
                                <path d="M17 17v2a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2h2" />
                            </svg>
                            <span class="text-primary fw-bold">{{ __('Browse') }}</span>
                        </button>
                        <button
                            class="nav-link px-3 py-1 fw-semibold small d-inline-flex align-items-center gap-1 border-0 bg-transparent text-muted"
                            data-bs-toggle="tab" data-bs-target="#modal-tab-upload" type="button" role="tab">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud-upload"
                                width="16" height="16" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7h-1" />
                                <polyline points="9 15 12 12 15 15" />
                                <line x1="12" y1="12" x2="12" y2="21" />
                            </svg>
                            <span>{{ __('Upload') }}</span>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-3 p-md-4">
                    <div id="uploadMessage"></div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="modal-tab-browse" role="tabpanel">
                            @include('user.pages.cards.media.list')
                        </div>
                        <div class="tab-pane fade" id="modal-tab-upload" role="tabpanel">
                            @include('user.pages.cards.media.upload')
                        </div>
                    </div>
                    <div id="pagination" class="mt-2"></div>
                </div>

                <div class="modal-footer border-top py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-primary-lt fw-semibold" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold px-4 shadow-sm"
                        onclick="applySelectedMedia()">
                        {{ __('Select Images') }} (<span id="selectedMediaCount">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Product CSV Import Modal --}}
    <div class="modal modal-bg fade" id="modal-import" tabindex="-1" aria-labelledby="modal-import-label"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modal-import-label">{{ __('Import Products Catalog') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm">
                        <input type="hidden" id="cardId" value="{{ $business_card->card_id }}">

                        <div class="mb-3">
                            <label class="form-label required">{{ __('CSV File') }}</label>
                            <input type="file" class="form-control" name="csv_file" id="importFile" accept=".csv"
                                required>
                            <small class="form-hint">{{ __('Supported format: CSV (.csv)') }}</small>
                        </div>

                        <div
                            class="p-3 bg-surface-secondary rounded border d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-file-type-csv text-success" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                                    <path d="M7 16.5a1.5 1.5 0 0 0 -3 0v3a1.5 1.5 0 0 0 3 0" />
                                    <path
                                        d="M10 20.25c0 .414 .336 .75 .75 .75h1.25a1 1 0 0 0 1 -1v-1a1 1 0 0 0 -1 -1h-1a1 1 0 0 1 -1 -1v-1a1 1 0 0 1 1 -1h1.25a.75 .75 0 0 1 .75 .75" />
                                    <path d="M16 15l2 6l2 -6" />
                                </svg>
                                <span class="small text-muted">{{ __('Need sample CSV template?') }}</span>
                            </div>
                            <a href="{{ asset('storage/import/store/store_products.csv') }}"
                                class="btn btn-primary d-inline-flex align-items-center gap-1"
                                download="sample_import_data_products.csv">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download"
                                    width="16" height="16" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                    <polyline points="7 11 12 16 17 11" />
                                    <line x1="12" y1="4" x2="12" y2="16" />
                                </svg>
                                <span>{{ __('Download') }}</span>
                            </a>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="importProduct()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload me-1"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                            <polyline points="7 9 12 4 17 9" />
                            <line x1="12" y1="4" x2="12" y2="16" />
                        </svg>
                        {{ __('Import') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@push('custom-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script src="{{ asset('js/clipboard.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <script>
        let targetInputId = 'productImage';
        let selectedMediaUrls = [];

        $(document).ready(function() {
            "use strict";

            var productsTable = $('#productsTable').DataTable({
                processing: false,
                serverSide: true,
                dom: "<'d-flex justify-content-end px-3 pt-3 search-wrapper'f>" +
                    "<'table-responsive px-3 pt-2 py-2'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: {
                    url: "{{ route('user.edit.products', $business_card->card_id) }}",
                    dataSrc: 'data'
                },
                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: ``,
                    sSearchPlaceholder: `{{ __('Search products...') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,
                    emptyTable: `{{ __('No products found in catalog.') }}`
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'rc-hide'
                    },
                    {
                        data: 'product_category',
                        name: 'product_category',
                        searchable: true
                    },
                    {
                        data: 'product_image',
                        name: 'product_image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product_name',
                        name: 'product_name',
                        searchable: true
                    },
                    {
                        data: 'product_description',
                        name: 'product_description',
                        searchable: true
                    },
                    {
                        data: 'product_status',
                        name: 'product_status',
                        searchable: true
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                preDrawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $tbody = $(settings.nTable).find('tbody');

                    $tbody.closest('.table-responsive').addClass('placeholder-glow');

                    var placeholderRows = '';
                    for (var i = 0; i < 5; i++) {
                        placeholderRows += '<tr>' +
                            '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                            .repeat(total_columns) +
                            '</tr>';
                    }
                    $tbody.html(placeholderRows);
                },
                drawCallback: function(settings) {
                    var api = new $.fn.dataTable.Api(settings);
                    var total_columns = api.columns().count();
                    var $table = $(settings.nTable);

                    $table.closest('.table-responsive').removeClass('placeholder-glow');
                    $table.find('tbody tr td.dataTables_empty').attr('colspan', total_columns);

                    var headers = [];
                    $table.find('thead th').each(function() {
                        headers.push($(this).text().trim());
                    });

                    $table.find('tbody tr').each(function() {
                        $(this).find('td').each(function(index) {
                            var label = headers[index] || '';
                            $(this).attr('data-label', label);
                        });
                    });
                }
            });

            $(document).on('click', '.modal', function(e) {
                if ($(e.target).hasClass('modal')) {
                    $(this).modal('hide');
                }
            });
        });

        var productSearchTimer;
        $(document).on('input', '#productsTable_filter input', function() {
            var value = this.value;
            var table = $('#productsTable').DataTable();
            clearTimeout(productSearchTimer);
            productSearchTimer = setTimeout(function() {
                table.search(value).draw();
            }, 300);
        });

        // Toggle variations container
        function toggleVariants(type) {
            if ($('#' + type + 'HasVariants').is(':checked')) {
                $('#' + type + 'VariantsSection').removeClass('d-none');
                if ($('#' + type + 'VariantsTable tbody tr').length === 0) {
                    addVariantRow(type);
                }
            } else {
                $('#' + type + 'VariantsSection').addClass('d-none');
            }
        }

        // Render Tabler UI styled variation row
        function addVariantRow(type, data = null) {
            let name = data ? (data.variant_name || '') : '';
            let regularPrice = data && data.regular_price ? data.regular_price : '';
            let salesPrice = data ? (data.sales_price || '') : '';
            let status = data ? (data.status || 'instock') : 'instock';

            let row = `
                <tr>
                    <td class="py-2">
                        <input type="text" class="form-control variant-name" 
                               placeholder="{{ __('e.g., XL / Red or 256GB') }}" 
                               value="${name}" required>
                    </td>
                    <td class="py-2">
                        <input type="number" step=".001" class="form-control variant-regular-price" 
                               placeholder="0.00" 
                               value="${regularPrice}">
                    </td>
                    <td class="py-2">
                        <input type="number" step=".001" class="form-control variant-sales-price" 
                               placeholder="0.00" 
                               value="${salesPrice}" required>
                    </td>
                    <td class="py-2">
                        <select class="form-select variant-status">
                            <option value="instock" ${status === 'instock' ? 'selected' : ''}>{{ __('In Stock') }}</option>
                            <option value="outstock" ${status === 'outstock' ? 'selected' : ''}>{{ __('Out of Stock') }}</option>
                        </select>
                    </td>
                    <td class="py-2 text-center">
                        <button type="button" class="btn btn-icon btn-danger rounded-circle" 
                                title="{{ __('Remove variant') }}" 
                                onclick="$(this).closest('tr').remove()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="4" y1="7" x2="20" y2="7" />
                                <line x1="10" y1="11" x2="10" y2="17" />
                                <line x1="14" y1="11" x2="14" y2="17" />
                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
            $('#' + type + 'VariantsTable tbody').append(row);
        }

        // Extract variants array from table
        function getVariantsData(type) {
            let variants = [];
            $('#' + type + 'VariantsTable tbody tr').each(function() {
                let name = $(this).find('.variant-name').val();
                let regularPrice = $(this).find('.variant-regular-price').val();
                let salesPrice = $(this).find('.variant-sales-price').val();
                let status = $(this).find('.variant-status').val();

                if (name && salesPrice) {
                    variants.push({
                        name: name,
                        regular_price: regularPrice,
                        sales_price: salesPrice,
                        status: status
                    });
                }
            });
            return variants;
        }

        function openMedia(inputId = 'productImage') {
            targetInputId = inputId;
            let existingVal = $('#' + targetInputId).val();
            selectedMediaUrls = existingVal ? existingVal.split(',').map(item => item.trim()).filter(Boolean) : [];
            updateSelectedCount();

            showModalSkeleton();
            $('#openMediaModel').modal('show');
            loadMedia(1);
        }

        function toggleCardSelection(mediaUrl, element) {
            if (selectedMediaUrls.includes(mediaUrl)) {
                selectedMediaUrls = selectedMediaUrls.filter(url => url !== mediaUrl);
                $(element).removeClass('selected').find('.media-card-checkbox').prop('checked', false);
            } else {
                selectedMediaUrls.push(mediaUrl);
                $(element).addClass('selected').find('.media-card-checkbox').prop('checked', true);
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            $('#selectedMediaCount').text(selectedMediaUrls.length);
        }

        function applySelectedMedia() {
            if (selectedMediaUrls.length > 0) {
                $('#' + targetInputId).val(selectedMediaUrls.join(','));
            } else {
                $('#' + targetInputId).val('');
            }
            $('#openMediaModel').modal('hide');
        }

        // Open Add Product Modal with clean state 
        function addProduct() {
            $('#addProductForm').trigger("reset");
            $('#addHasVariants').prop('checked', false);
            $('#addVariantsSection').addClass('d-none');
            $('#addVariantsTable tbody').empty();

            const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
            modal.show();

            setTimeout(function() {
                tinymce.remove('#productDescription');
                tinymce.init({
                    selector: '#productDescription',
                    height: 200,
                    menubar: false,
                    statusbar: false,
                    content_style: 'body { font-family: "Figtree", sans-serif; font-size: 14px; }'
                });
            }, 300);
        }

        // Save Product and Variations
        function saveProduct() {
            "use strict";
            var cardId = $('#cardId').val();
            var productBadge = $('#productBadge').val();
            var productCategory = $('#productCategory').val();
            var productImage = $('#productImage').val();
            var productName = $('#productName').val();
            var productShortDescription = $('#productShortDescription').val();
            var productDescription = tinymce.get('productDescription') ? tinymce.get('productDescription').getContent() :
            '';
            var productRegularPrice = $('#productRegularPrice').val();
            var productSalesPrice = $('#productSalesPrice').val();
            var productStatus = $('#productStatus').val();

            var hasVariants = $('#addHasVariants').is(':checked') ? 1 : 0;
            var variantsData = hasVariants ? getVariantsData('add') : [];

            function showError(message) {
                $('#errorMessage').text(message).show();
                $('#errorMessage').delay(2500).fadeOut(500);
            }

            if (!productCategory) {
                showError("{{ __('Product category is required.') }}");
                return;
            }
            if (!productImage) {
                showError("{{ __('Product image is required.') }}");
                return;
            }
            if (!productName) {
                showError("{{ __('Product name is required.') }}");
                return;
            }
            if (!productShortDescription) {
                showError("{{ __('Product short description is required.') }}");
                return;
            }

            // Auto-fill sales price from first variant if variations are enabled
            if (hasVariants) {
                if (variantsData.length === 0) {
                    showError("{{ __('Please add at least one complete variation.') }}");
                    return;
                }
                if (!productSalesPrice) {
                    productSalesPrice = variantsData[0].sales_price;
                }
            } else {
                if (!productSalesPrice) {
                    showError("{{ __('Product sales price is required.') }}");
                    return;
                }
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
                    has_variants: hasVariants,
                    variants: JSON.stringify(variantsData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('New Product added successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#addProductModal').modal('hide');
                        $('#addProductForm').trigger("reset");
                        $('#productsTable').DataTable().ajax.reload(null, false);
                    } else {
                        $('#errorMessage').text(response.message || "{{ __('Failed to add product.') }}")
                            .show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#errorMessage').text("{{ __('Something went wrong! Please try again.') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Fetch & Open Edit Product Modal
        function editProduct(productId) {
            "use strict";
            $.ajax({
                url: '/user/store/get-products/' + productId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#productId').val(response.data.id);
                        $('#editProductBadge').val(response.data.badge);
                        $('#editProductCategory').val(response.data.category_id).trigger('change');
                        $('#editProductImage').val(response.data.product_image);
                        $('#editProductName').val(response.data.product_name);
                        $('#editProductShortDescription').val(response.data.product_short_description);
                        $('#editProductRegularPrice').val(response.data.regular_price);
                        $('#editProductSalesPrice').val(response.data.sales_price);
                        $('#editProductStatus').val(response.data.product_status);

                        // Populate variants data
                        $('#editVariantsTable tbody').empty();
                        if (response.data.has_variants == 1 && response.data.variants && response.data.variants
                            .length > 0) {
                            $('#editHasVariants').prop('checked', true);
                            $('#editVariantsSection').removeClass('d-none');
                            response.data.variants.forEach(function(v) {
                                addVariantRow('edit', v);
                            });
                        } else {
                            $('#editHasVariants').prop('checked', false);
                            $('#editVariantsSection').addClass('d-none');
                        }

                        $('#editProductModal').modal('show');

                        tinymce.remove('#editProductDescription');
                        tinymce.init({
                            selector: '#editProductDescription',
                            height: 200,
                            menubar: false,
                            statusbar: false,
                            content_style: 'body { font-family: "Figtree", sans-serif; font-size: 14px; }',
                            setup: function(editor) {
                                editor.on('init', function() {
                                    editor.setContent(response.data.product_description ||
                                        '');
                                });
                            }
                        });
                    } else {
                        $('#errorMessage').text("{{ __('Failed to fetch product data.') }}").show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Update Existing Product and Variations
        function updateProduct() {
            "use strict";
            var productId = $('#productId').val();
            var productBadge = $('#editProductBadge').val();
            var productCategory = $('#editProductCategory').val();
            var productImage = $('#editProductImage').val();
            var productName = $('#editProductName').val();
            var productShortDescription = $('#editProductShortDescription').val();
            var productDescription = tinymce.get('editProductDescription') ? tinymce.get('editProductDescription')
                .getContent() : '';
            var productRegularPrice = $('#editProductRegularPrice').val();
            var productSalesPrice = $('#editProductSalesPrice').val();
            var productStatus = $('#editProductStatus').val();

            var hasVariants = $('#editHasVariants').is(':checked') ? 1 : 0;
            var variantsData = hasVariants ? getVariantsData('edit') : [];

            function showError(message) {
                $('#errorMessage').text(message).show();
                $('#errorMessage').delay(2500).fadeOut(500);
            }

            if (!productCategory) {
                showError("{{ __('Product category is required.') }}");
                return;
            }
            if (!productImage) {
                showError("{{ __('Product image is required.') }}");
                return;
            }
            if (!productName) {
                showError("{{ __('Product name is required.') }}");
                return;
            }
            if (!productShortDescription) {
                showError("{{ __('Product short description is required.') }}");
                return;
            }

            if (hasVariants) {
                if (variantsData.length === 0) {
                    showError("{{ __('Please add at least one complete variation.') }}");
                    return;
                }
                if (!productSalesPrice) {
                    productSalesPrice = variantsData[0].sales_price;
                }
            } else {
                if (!productSalesPrice) {
                    showError("{{ __('Product sales price is required.') }}");
                    return;
                }
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
                    has_variants: hasVariants,
                    variants: JSON.stringify(variantsData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('Product updated successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#editProductModal').modal('hide');
                        $('#productsTable').DataTable().ajax.reload(null, false);
                    } else {
                        $('#errorMessage').text(response.message || "{{ __('Failed to update product.') }}")
                            .show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Open Delete Confirmation Modal
        function deleteProduct(productId) {
            $('#deleteProductModal').data('product-id', productId).modal('show');
        }

        // Handle Delete Confirmation
        $(document).ready(function() {
            $('#confirmDeleteButton').click(function() {
                var productId = $('#deleteProductModal').data('product-id');
                $.ajax({
                    url: '/user/delete-product/' + productId,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#successMessage').text("{{ __('Product deleted successfully!') }}")
                            .show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#deleteProductModal').modal('hide');
                        $('#productsTable').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        $('#deleteProductModal').modal('hide');
                        $('#errorMessage').text("{{ __('Error deleting product.') }}").show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                });
            });
        });

        // Dropzone Configurations
        Dropzone.options.dropzone = {
            maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            timeout: 180000,
            init: function() {
                this.on("success", function(file, response) {
                    $('#uploadMessage').empty();
                    if (response.status === 'success') {
                        $('#uploadMessage').html(`
                            <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
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
                            <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                                ${response.message || '{{ __('Upload failed. Please try again.') }}'}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `);
                    }
                });

                this.on("error", function(file, response) {
                    $('#uploadMessage').html(`
                        <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                            {{ __('Upload failed. Please try again.') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                    this.removeFile(file);
                });
            }
        };

        var currentPage = 1;
        var totalPages = 1;

        function loadPreviousPage() {
            if (currentPage > 1) {
                currentPage--;
                loadMedia(currentPage);
            }
        }

        function loadNextPage() {
            if (currentPage < totalPages) {
                currentPage++;
                loadMedia(currentPage);
            }
        }

        function showModalSkeleton() {
            let skeletonHtml = '';
            for (let i = 0; i < 8; i++) {
                skeletonHtml += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="modal-media-card placeholder-glow">
                            <div class="modal-media-thumb placeholder"></div>
                            <div class="modal-media-body">
                                <div class="placeholder col-8 rounded mb-2"></div>
                            </div>
                        </div>
                    </div>
                `;
            }
            $('#mediaCardsContainer').html(skeletonHtml);
            $('#noImagesFound').hide();
            $('#showPagination').hide();
        }

        function loadMedia(page = 1) {
            showModalSkeleton();

            $.ajax({
                url: '{{ route('user.media') }}',
                method: 'GET',
                data: {
                    page: page
                },
                dataType: 'json',
                success: function(response) {
                    var mediaData = response.media.data;
                    if (mediaData && mediaData.length > 0) {
                        $('#noImagesFound').hide();
                        $('#showPagination').show();

                        var mediaCardsHtml = '';
                        mediaData.forEach(function(media) {
                            const isSelected = selectedMediaUrls.includes(media.media_url);
                            mediaCardsHtml += `
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="modal-media-card ${isSelected ? 'selected' : ''}" onclick="toggleCardSelection('${media.media_url}', this)">
                                        <input type="checkbox" class="form-check-input media-card-checkbox" ${isSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleCardSelection('${media.media_url}', $(this).closest('.modal-media-card'))">
                                        <div class="modal-media-thumb" style="background-image: url('${media.base_url}${media.media_url}');"></div>
                                        <div class="modal-media-body">
                                            <div class="modal-media-title" title="${media.media_name}">${media.media_name}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#mediaCardsContainer').html(mediaCardsHtml);

                        $('#paginationStartIndex').text(response.media.from || 1);
                        $('#paginationEndIndex').text(response.media.to || response.media.total);
                        $('#paginationTotalCount').text(response.media.total || 0);
                        currentPage = response.media.current_page;
                        totalPages = response.media.last_page;

                        $('#prevPageBtn').prop('disabled', currentPage <= 1).toggleClass('disabled',
                            currentPage <= 1);
                        $('#nextPageBtn').prop('disabled', currentPage >= totalPages).toggleClass('disabled',
                            currentPage >= totalPages);
                    } else {
                        $('#noImagesFound').show();
                        $('#showPagination').hide();
                        $('#mediaCardsContainer').html('');
                    }
                }
            });
        }

        function importProduct() {
            "use strict";
            var form = $('#importForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('card_id', $('#cardId').val());

            $.ajax({
                url: '{{ route('user.import.products') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text(response.message ||
                            "{{ __('Products imported successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#modal-import').modal('hide');
                        $('#importForm').trigger("reset");
                        $('#productsTable').DataTable().ajax.reload(null, false);
                    } else {
                        $('#modal-import').modal('hide');
                        $('#errorMessage').text(response.message || "{{ __('Import failed.') }}").show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#modal-import').modal('hide');
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }
    </script>
@endpush
