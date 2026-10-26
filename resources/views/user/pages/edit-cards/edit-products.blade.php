@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    {{-- Products CSS --}}
    <link rel="stylesheet" href="{{ asset('css/products.css') }}" />

    {{-- Dropzone CSS --}}
    <link rel="stylesheet" href="{{ asset('css/dropzone.min.css') }}">

    {{-- Dropzone JS & Clipboard --}}
    <script src="{{ asset('js/dropzone.min.js') }}"></script>
    <script src="{{ asset('js/clipboard.min.js') }}"></script>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
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
                        <h2 class="page-title text-dark fw-bold">
                            {{ __('Products') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Add retail products, configure inventory stock levels, determine pricing rules, and deploy instant lookup catalogs.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
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

                <div id="successMessage" style="display:none;"
                    class="alert alert-important alert-success alert-dismissible mb-3 border-0 shadow-sm"></div>
                <div id="errorMessage" style="display:none;"
                    class="alert alert-important alert-danger alert-dismissible mb-3 border-0 shadow-sm"></div>

                <div class="card border-0 shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-3 col-lg-2 border-end">
                            <div class="card-body">
                                <h4 class="subheader mb-3 text-uppercase fw-bold text-muted">{{ __('Card Settings') }}</h4>
                                <div class="list-group list-group-transparent">
                                    @include('user.pages.edit-cards.includes.nav-link', [
                                        'link' => 'products',
                                    ])
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-9 col-lg-10 d-flex flex-column">
                            <div class="card-body p-3 p-lg-3">
                                <div class="row align-items-center mb-3">
                                    <div class="col">
                                        <h3 class="card-title m-0 text-dark fw-bold">{{ __('Products') }}</h3>
                                        <p class="text-muted small mb-0 mt-1">
                                            {{ __('Add product records, assign rich thumbnail graphics, set pricing and toggle inquiry options.') }}
                                        </p>
                                    </div>
                                    <div class="col-auto ms-auto d-print-none">
                                        <button type="button"
                                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm fw-semibold"
                                            onclick="addProduct()">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="icon icon-tabler icon-tabler-plus" width="20" height="20"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <line x1="12" y1="5" x2="12" y2="19" />
                                                <line x1="5" y1="12" x2="19" y2="12" />
                                            </svg>
                                            <span class="d-none d-sm-inline-block">{{ __('Create new') }}</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <table id="productsTable" class="table card-table table-vcenter text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>{{ __('#') }}</th>
                                                <th>{{ __('Image') }}</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Price') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Stock') }}</th>
                                                <th class="w-1 text-end">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer border-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="{{ route('user.cards') }}"
                                        class="btn btn-danger text-decoration-none fw-semibold">
                                        {{ __('Cancel') }}
                                    </a>
                                    <a href="{{ route('user.edit.galleries', Request::segment(3)) }}"
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
                                    </a>
                                </div>
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="addProductModalLabel">{{ __('Add Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <input type="hidden" id="cardId" value="{{ $business_card->card_id }}">
                        <div class="row g-3">
                            {{-- Product Image --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label required"
                                        for="productImage">{{ __('Product Image') }}</label>
                                    <div class="input-group">
                                        <input type="text" class="image form-control" id="productImage"
                                            name="product_image" placeholder="{{ __('No image chosen') }}" required
                                            readonly>
                                        <button class="btn btn-primary d-inline-flex align-items-center" type="button"
                                            onclick="openMedia()">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon me-1">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M7 5.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667l0 -8.666" />
                                                <path
                                                    d="M4.012 7.26a2.005 2.005 0 0 0 -1.012 1.737v10c0 1.1 .9 2 2 2h10c.75 0 1.158 -.385 1.5 -1" />
                                                <path d="M17 7h.01" />
                                                <path d="M7 13l3.644 -3.644a1.21 1.21 0 0 1 1.712 0l3.644 3.644" />
                                                <path d="M15 12l1.644 -1.644a1.21 1.21 0 0 1 1.712 0l2.644 2.644" />
                                            </svg>
                                            <span>{{ __('Choose Image') }}</span>
                                        </button>
                                    </div>
                                    <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 800 px']) }}</small>
                                </div>
                            </div>

                            {{-- Product Name --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productName" class="form-label required">{{ __('Product Name') }}</label>
                                    <input type="text" class="form-control" id="productName" name="product_name"
                                        placeholder="{{ __('Enter product name') }}" required>
                                </div>
                            </div>

                            {{-- Product Badge --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="productBadge">{{ __('Product Badge') }}</label>
                                    <input type="text" class="form-control" id="productBadge" name="product_badge"
                                        placeholder="{{ __('e.g., New / Sale') }}">
                                </div>
                            </div>

                            {{-- Currency --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="productCurrency">{{ __('Currency') }}</label>
                                    <select id="productCurrency" name="product_currency" class="form-control currency">
                                        <option value="" disabled>{{ __('Choose currency') }}</option>
                                        <option value="null" selected>{{ __('No Currency') }}</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->iso_code }}">{{ $currency->name }}
                                                ({{ $currency->symbol }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Regular Price --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="productRegularPrice">{{ __('Regular Price') }}</label>
                                    <input type="number" class="form-control" id="productRegularPrice"
                                        name="product_regular_price" placeholder="{{ __('Enter regular price') }}"
                                        min="1" step=".001">
                                </div>
                            </div>

                            {{-- Sales Price --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="productSalesPrice">{{ __('Sales Price') }}</label>
                                    <input type="number" class="form-control" id="productSalesPrice"
                                        name="product_sales_price" min="1" step=".001"
                                        placeholder="{{ __('Enter sales price') }}">
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label" for="productStatus">{{ __('Status') }}</label>
                                    <select id="productStatus" name="product_status" class="form-control product_status"
                                        {{ $whatsAppNumberExists != true ? 'disabled' : '' }}>
                                        <option value="null" selected>{{ __('No Status') }}</option>
                                        <option value="instock">{{ __('In Stock') }}</option>
                                        <option value="outstock">{{ __('Out of Stock') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Product Description --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="productDescription"
                                        class="form-label required">{{ __('Product Description') }}</label>
                                    <textarea class="form-control" id="productDescription" name="product_description" rows="3"
                                        placeholder="{{ __('Enter product description') }}"></textarea>
                                </div>
                            </div>

                            @if ($whatsAppNumberExists != true)
                                <div class="col-12 mt-0">
                                    <div class="alert alert-important alert-danger mb-0" style="border-radius: 8px;">
                                        {{ __("'Inquiry button' is disabled as you have not entered a WhatsApp number. Go to the 'Social Links' page to add your WhatsApp number.") }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto text-decoration-none fw-semibold"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1 fw-semibold"
                        onclick="saveProduct()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <circle cx="12" cy="14" r="2" />
                            <polyline points="14 4 14 8 8 8 4" />
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="editProductModalLabel">{{ __('Edit Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm">
                        <input type="hidden" id="productId">
                        <div class="row g-3">
                            {{-- Product Image --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label required"
                                        for="editProductImage">{{ __('Product Image') }}</label>
                                    <div class="input-group">
                                        <input type="text" class="image form-control" id="editProductImage"
                                            name="product_image" placeholder="{{ __('No image chosen') }}" required
                                            readonly>
                                        <button class="btn btn-primary d-inline-flex align-items-center gap-1"
                                            type="button" onclick="openMedia()">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="icon">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path
                                                    d="M7 5.667a2.667 2.667 0 0 1 2.667 -2.667h8.666a2.667 2.667 0 0 1 2.667 2.667v8.666a2.667 2.667 0 0 1 -2.667 2.667h-8.666a2.667 2.667 0 0 1 -2.667 -2.667l0 -8.666" />
                                                <path
                                                    d="M4.012 7.26a2.005 2.005 0 0 0 -1.012 1.737v10c0 1.1 .9 2 2 2h10c.75 0 1.158 -.385 1.5 -1" />
                                                <path d="M17 7h.01" />
                                                <path d="M7 13l3.644 -3.644a1.21 1.21 0 0 1 1.712 0l3.644 3.644" />
                                                <path d="M15 12l1.644 -1.644a1.21 1.21 0 0 1 1.712 0l2.644 2.644" />
                                            </svg>
                                            <span>{{ __('Choose Image') }}</span>
                                        </button>
                                    </div>
                                    <small class="form-hint text-muted mt-1 d-block"><i class="ti ti-photo me-1"></i>{{ __(':size px recommended', ['size' => '800 x 800 px']) }}</small>
                                </div>
                            </div>

                            {{-- Product Name --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editProductName"
                                        class="form-label required">{{ __('Product Name') }}</label>
                                    <input type="text" class="form-control" id="editProductName" name="product_name"
                                        placeholder="{{ __('Enter product name') }}" required>
                                </div>
                            </div>

                            {{-- Product Badge --}}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="editProductBadge">{{ __('Product Badge') }}</label>
                                    <input type="text" class="form-control" id="editProductBadge"
                                        name="product_badge" placeholder="{{ __('e.g., New / Sale') }}">
                                </div>
                            </div>

                            {{-- Currency --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="editProductCurrency">{{ __('Currency') }}</label>
                                    <select id="editProductCurrency" name="product_currency"
                                        class="form-control currency">
                                        <option value="null">{{ __('No Currency') }}</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->iso_code }}">{{ $currency->name }}
                                                ({{ $currency->symbol }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Regular Price --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label"
                                        for="editProductRegularPrice">{{ __('Regular Price') }}</label>
                                    <input type="number" class="form-control" id="editProductRegularPrice"
                                        name="product_regular_price" placeholder="{{ __('Enter regular price') }}"
                                        min="1" step=".001">
                                </div>
                            </div>

                            {{-- Sales Price --}}
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="editProductSalesPrice">{{ __('Sales Price') }}</label>
                                    <input type="number" class="form-control" id="editProductSalesPrice"
                                        name="product_sales_price" min="1" step=".001"
                                        placeholder="{{ __('Enter sales price') }}">
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label" for="editProductStatus">{{ __('Status') }}</label>
                                    <select id="editProductStatus" name="product_status"
                                        class="form-control product_status"
                                        {{ $whatsAppNumberExists != true ? 'disabled' : '' }}>
                                        <option value="null">{{ __('No Status') }}</option>
                                        <option value="instock">{{ __('In Stock') }}</option>
                                        <option value="outstock">{{ __('Out of Stock') }}</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Product Description --}}
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="editProductDescription"
                                        class="form-label">{{ __('Product Description') }}</label>
                                    <textarea class="form-control" id="editProductDescription" name="product_description" rows="3"
                                        placeholder="{{ __('Enter product description') }}"></textarea>
                                </div>
                            </div>

                            @if ($whatsAppNumberExists != true)
                                <div class="col-12 mt-0">
                                    <div class="alert alert-important alert-danger mb-0" style="border-radius: 8px;">
                                        {{ __("'Inquiry button' is disabled as you have not entered a WhatsApp number. Go to the 'Social Links' page to add your WhatsApp number.") }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger me-auto text-decoration-none fw-semibold"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-primary px-4 d-inline-flex align-items-center gap-1 fw-semibold"
                        onclick="updateProduct()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                            width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                            <circle cx="12" cy="14" r="2" />
                            <polyline points="14 4 14 8 8 8 4" />
                        </svg>
                        {{ __('Update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Product Modal --}}
    <div class="modal modal-bg fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
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
                    <h3 class="fw-bold text-dark">{{ __('Are you sure?') }}</h3>
                    <div id="delete_status" class="text-secondary">
                        {{ __('Are you sure you want to delete this product?') }}</div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-primary-lt me-auto text-decoration-none fw-semibold"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger fw-semibold px-4" id="confirmDeleteButton">
                        {{ __('Yes, proceed') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Media Library Modal (Synchronized ID: openMediaModel) --}}
    <div class="modal modal-bg fade" id="openMediaModel" tabindex="-1" aria-labelledby="openMediaModelLabel"
        aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

                <!-- Modal Header -->
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
                                <path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="modal-title fw-bold mb-0 text-reset fs-3" id="openMediaModelLabel">
                                {{ __('Media Library') }}
                            </h4>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                {{ __('Upload multiple images or select an uploaded image below') }}
                            </div>
                        </div>
                    </div>

                    <!-- Right Tabs (Browse & Upload) -->
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

                <!-- Modal Body with Tabs -->
                <div class="modal-body p-3 p-md-4">
                    <div id="uploadMessage"></div>

                    <div class="tab-content">
                        {{-- Tab 1: Browse Media (List) --}}
                        <div class="tab-pane fade show active" id="modal-tab-browse" role="tabpanel">
                            @include('user.pages.cards.media.list')
                        </div>

                        {{-- Tab 2: Upload Files (Dropzone) --}}
                        <div class="tab-pane fade" id="modal-tab-upload" role="tabpanel">
                            @include('user.pages.cards.media.upload')
                        </div>
                    </div>

                    <div id="pagination" class="mt-2"></div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="modal-footer border-top py-3 px-3 px-md-4">
                    <button type="button" class="btn btn-primary w-100 fw-bold shadow-sm" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            "use strict";
            const totalColumns = 7;

            const table = $('#productsTable').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: "{{ route('user.edit.vproducts', $business_card->card_id) }}",
                    dataSrc: 'data'
                },
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                pageLength: 10,
                language: {
                    sProcessing: `{{ __('Processing...') }}`,
                    sLengthMenu: `{{ __('Show _MENU_ entries') }}`,
                    sSearch: ``,
                    sSearchPlaceholder: `{{ __('Search') }}`,
                    oPaginate: {
                        sNext: `{{ __('Next') }}`,
                        sPrevious: `{{ __('Previous') }}`
                    },
                    sInfo: `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    sInfoEmpty: `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    sInfoFiltered: `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,
                    emptyTable: `{{ __('No data available in the table') }}`
                },
                dom: "<'row mb-0 align-items-center g-3'<'col-12 col-md-10'l><'col-12 col-md-2 text-md-end'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'d-flex justify-content-between align-items-center'ip>",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product_image',
                        name: 'product_image',
                        render: function(data, type, full, meta) {
                            return '<img src="' + data +
                                '" alt="Product Image" class="rounded" style="width: 44px; height: 44px; object-fit: cover; border: 1px solid rgba(0,0,0,0.08);"/>';
                        }
                    },
                    {
                        data: 'product_name',
                        name: 'product_name',
                        render: function(data, type, full, meta) {
                            return '<a href="javascript:void(0);" class="product-name-link fw-semibold text-reset" onclick="editProduct(' +
                                full.id + ')">' + data + '</a>';
                        }
                    },
                    {
                        data: 'sales_price',
                        name: 'sales_price',
                        render: function(data, type, full, meta) {
                            var currency = full.currency != "null" ? full.currency : '';
                            var price = full.sales_price ? full.sales_price : '-';
                            return '<span class="fw-semibold">' + currency + ' ' + price +
                            '</span>';
                        }
                    },
                    {
                        data: 'product_description',
                        name: 'product_description'
                    },
                    {
                        data: 'product_status',
                        name: 'product_status',
                        render: function(data, type, full, meta) {
                            var cls = 'bg-secondary-lt';
                            var label = "{{ __('No Status') }}";

                            if (data === 'instock') {
                                cls = 'bg-success-lt';
                                label = "{{ __('In Stock') }}";
                            } else if (data === 'outstock') {
                                cls = 'bg-danger-lt';
                                label = "{{ __('Out of Stock') }}";
                            }
                            return '<span class="badge ' + cls + ' px-2 py-1">' + label + '</span>';
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(data, type, full, meta) {
                            return `
                                <a class="btn-action" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-dots">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <circle cx="5" cy="12" r="1" />
                                        <circle cx="12" cy="12" r="1" />
                                        <circle cx="19" cy="12" r="1" />
                                    </svg>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="editProduct(${data})">
                                        {{ __('Edit') }}
                                    </a>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteProduct(${data})">
                                        {{ __('Delete') }}
                                    </a>
                                </div>
                            `;
                        }
                    }
                ],
                initComplete: function() {
                    $('.dataTables_length select').addClass(
                        'form-select form-select-sm mx-2 d-inline-block w-auto');
                    $('.dataTables_filter input').addClass(
                        'form-control form-control-sm d-inline-block w-auto');
                },
                preDrawCallback: function(settings) {
                    $('#productsTable_wrapper').addClass('placeholder-glow');
                },
                drawCallback: function(settings) {
                    $('#productsTable_wrapper').removeClass('placeholder-glow');
                    $('#productsTable tbody tr').each(function() {
                        const actionCell = $(this).find('td').eq(6);
                        if (actionCell.find('span.placeholder').length > 0) {
                            actionCell.empty();
                        }
                    });
                    $('#productsTable tbody tr td.dataTables_empty').attr('colspan', totalColumns);
                }
            });

            $('#productsTable').wrap('<div class="table-responsive px-0"></div>');

            $('.dataTables_filter input').unbind().on('keyup', function() {
                const searchValue = this.value;
                clearTimeout(window.searchTimeout);

                $('#productsTable_wrapper').addClass('placeholder-glow');
                let placeholderRows = '';
                for (let i = 0; i < 5; i++) {
                    placeholderRows += '<tr>' +
                        '<td class="text-center"><div class="placeholder placeholder-xs col-12"></div></td>'
                        .repeat(totalColumns) + '</tr>';
                }
                $('#productsTable tbody').html(placeholderRows);

                window.searchTimeout = setTimeout(function() {
                    table.search(searchValue).draw();
                }, 350);
            });

            // Global Outside Click Backdrop close handler
            $(document).on('click', '.modal', function(e) {
                if ($(e.target).hasClass('modal')) {
                    $(this).modal('hide');
                }
            });
        });

        function openMedia() {
            "use strict";
            showModalSkeleton();
            $('#openMediaModel').modal('show');
            loadMedia(1);
        }

        function addProduct() {
            "use strict";
            $('#addProductModal').modal('show');
        }

        function saveProduct() {
            "use strict";
            var cardId = $('#cardId').val();
            var productBadge = $('#productBadge').val();
            var productCurrency = $('#productCurrency').val();
            var productImage = $('#productImage').val();
            var productName = $('#productName').val();
            var productDescription = $('#productDescription').val();
            var productRegularPrice = $('#productRegularPrice').val();
            var productSalesPrice = $('#productSalesPrice').val();
            var productStatus = $('#productStatus').val();

            function showError(message) {
                $('#errorMessage').text(message).show();
                $('#errorMessage').delay(1500).fadeOut(200);
                $('#addProductModal').modal('hide');
            }

            if (productImage === '') {
                showError("{{ __('Product image is required.') }}");
                return;
            }

            if (productName === '') {
                showError("{{ __('Product name is required.') }}");
                return;
            }

            $.ajax({
                url: '{{ route('user.save.vproduct') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    card_id: cardId,
                    product_badge: productBadge,
                    product_currency: productCurrency,
                    product_image: productImage,
                    product_name: productName,
                    product_description: productDescription,
                    product_regular_price: productRegularPrice,
                    product_sales_price: productSalesPrice,
                    product_status: productStatus,
                },
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('New Product added successfully!') }}").show();
                        $('#successMessage').delay(1000).fadeOut(100);
                        $('#addProductModal').modal('hide');
                        $('#addProductForm').trigger("reset");
                        $('#productsTable').DataTable().ajax.reload();
                    } else {
                        $('#addProductModal').modal('hide');
                        $('#errorMessage').text("{{ __('Failed to add product.') }}").show();
                        $('#errorMessage').delay(1500).fadeOut(200);
                    }
                }
            });
        }

        function editProduct(productId) {
            "use strict";
            $.ajax({
                url: '/user/get-vproducts/' + productId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#productId').val(response.data.id);
                        $('#editProductBadge').val(response.data.badge);
                        $('#editProductCurrency').val(response.data.currency);
                        $('#editProductImage').val(response.data.product_image);
                        $('#editProductName').val(response.data.product_name);
                        $('#editProductDescription').val(response.data.product_description);
                        $('#editProductRegularPrice').val(response.data.regular_price);
                        $('#editProductSalesPrice').val(response.data.sales_price);
                        $('#editProductStatus').val(response.data.product_status);
                        $('#editProductModal').modal('show');
                    } else {
                        $('#errorMessage').text("{{ __('Failed to fetch product data.') }}").show();
                        $('#errorMessage').delay(1500).fadeOut(200);
                    }
                }
            });
        }

        function updateProduct() {
            "use strict";
            var productId = $('#productId').val();
            var productBadge = $('#editProductBadge').val();
            var productCurrency = $('#editProductCurrency').val();
            var productImage = $('#editProductImage').val();
            var productName = $('#editProductName').val();
            var productDescription = $('#editProductDescription').val();
            var productRegularPrice = $('#editProductRegularPrice').val();
            var productSalesPrice = $('#editProductSalesPrice').val();
            var productStatus = $('#editProductStatus').val();

            function showError(message) {
                $('#errorMessage').text(message).show();
                $('#errorMessage').delay(1500).fadeOut(200);
                $('#editProductModal').modal('hide');
            }

            if (productImage === '') {
                showError("{{ __('Product image is required.') }}");
                return;
            }

            if (productName === '') {
                showError("{{ __('Product name is required.') }}");
                return;
            }

            $.ajax({
                url: '{{ route('user.update.vproduct') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_id: productId,
                    product_badge: productBadge,
                    product_currency: productCurrency,
                    product_image: productImage,
                    product_name: productName,
                    product_description: productDescription,
                    product_regular_price: productRegularPrice,
                    product_sales_price: productSalesPrice,
                    product_status: productStatus,
                },
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('Product updated successfully!') }}").show();
                        $('#successMessage').delay(1000).fadeOut(100);
                        $('#editProductModal').modal('hide');
                        $('#productsTable').DataTable().ajax.reload();
                    } else {
                        $('#editProductModal').modal('hide');
                        $('#errorMessage').text("{{ __('Failed to update product.') }}").show();
                        $('#errorMessage').delay(1500).fadeOut(200);
                    }
                }
            });
        }

        function deleteProduct(productId) {
            $('#deleteProductModal').data('product-id', productId).modal('show');
        }

        $(document).ready(function() {
            $('#confirmDeleteButton').click(function() {
                deleteConfirmProduct();
            });
        });

        function deleteConfirmProduct() {
            var productId = $('#deleteProductModal').data('product-id');
            $.ajax({
                url: '/user/delete-vproduct/' + productId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#successMessage').text("{{ __('Product deleted successfully!') }}").show();
                    $('#successMessage').delay(1000).fadeOut(100);
                    $('#deleteProductModal').modal('hide');
                    $('#productsTable').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    $('#addProductModal').modal('hide');
                    $('#errorMessage').text("{{ __('Error deleting product') }}").show();
                    $('#errorMessage').delay(1500).fadeOut(200);
                }
            });
        }
    </script>

    {{-- Dropzone image upload setup --}}
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

    {{-- Paginated Media loader with Skeletons --}}
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

        function showModalSkeleton() {
            let skeletonHtml = '';
            for (let i = 0; i < 8; i++) {
                skeletonHtml += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="modal-media-card placeholder-glow">
                            <div class="modal-media-thumb placeholder"></div>
                            <div class="modal-media-body">
                                <div class="placeholder col-8 rounded mb-2"></div>
                                <div class="placeholder rounded" style="width: 38px; height: 38px;"></div>
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
                $('#showPagination').show();
                displayMediaCards(mediaData);
                updatePaginationInfo(response.media);
            } else {
                $('#noImagesFound').show();
                $('#showPagination').hide();
                $('#mediaCardsContainer').html('');
                updatePaginationInfo(response.media);
            }
        }

        function displayMediaCards(mediaData) {
            "use strict";
            var mediaCardsHtml = '';
            mediaData.forEach(function(media) {
                mediaCardsHtml += `
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="modal-media-card">
                            <div class="modal-media-thumb" style="background-image: url('${media.base_url}${media.media_url}');"></div>
                            <div class="modal-media-body">
                                <div class="modal-media-title" title="${media.media_name}">${media.media_name}</div>
                                <button type="button" class="btn btn-primary btn-modal-copy-icon copyBoard shadow-sm" data-clipboard-text="${media.media_url}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ __('Copy') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-copy m-0" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <rect x="8" y="8" width="12" height="12" rx="2"></rect>
                                        <path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#mediaCardsContainer').html(mediaCardsHtml);
        }

        function updatePaginationInfo(media) {
            "use strict";
            $('#paginationStartIndex').text(media.from || 1);
            $('#paginationEndIndex').text(media.to || media.total);
            $('#paginationTotalCount').text(media.total || 0);
            currentPage = media.current_page;
            totalPages = media.last_page;

            $('#prevPageBtn').prop('disabled', currentPage <= 1).toggleClass('disabled', currentPage <= 1);
            $('#nextPageBtn').prop('disabled', currentPage >= totalPages).toggleClass('disabled', currentPage >=
            totalPages);
        }

        $(document).ready(function() {
            "use strict";
            loadMedia();
        });
    </script>

    {{-- Clipboard actions bindings --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            "use strict";
            var clipboard = new ClipboardJS('.copyBoard');

            clipboard.on('success', function(e) {
                "use strict";
                $('.image').val(e.text);
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
                alertDiv.classList.add('alert', 'alert-important', 'alert-' + type, 'alert-dismissible', 'mb-3');
                alertDiv.setAttribute('role', 'alert');

                var innerContent = '<div class="d-flex align-items-center gap-2">' +
                    '<div>' + message + '</div>' +
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
@endpush
