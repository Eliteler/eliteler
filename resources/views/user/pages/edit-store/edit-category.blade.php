@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

{{-- Custom CSS --}}
@section('css')
    {{-- Store CSS File --}}
    <link href="{{ asset('css/store-category.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <!-- Hierarchical Breadcrumb Navigation -->
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
                                    {{ __('Categories') }}
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <!-- Title & Action Button -->
                    <div class="col">
                        <h2 class="page-title text-reset fw-bold">
                            {{ __('Store Categories') }}
                        </h2>
                        <div class="text-muted mt-1">
                            {{ __('Organize your store products into structured browseable categories.') }}
                        </div>
                    </div>

                    <div class="col-auto ms-auto d-print-none">
                        <button type="button"
                            class="btn btn-primary btn-icon-only d-inline-flex align-items-center shadow-sm"
                            onclick="addCategory()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="20"
                                height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            <span class="d-none d-sm-inline-block">{{ __('Add New') }}</span>
                        </button>
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

                {{-- Dynamic AJAX Alerts --}}
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
                                        'link' => 'categories',
                                    ])
                                </div>
                            </div>
                        </div>

                        {{-- Right Data Table Container --}}
                        <div class="col-12 col-md-9 col-lg-10">
                            <div class="card-body m-0">
                                <table id="categoriesTable" class="table card-table table-vcenter text-nowrap table-hover">
                                    <thead>
                                        <tr>
                                            <th class="w-1">{{ __('#') }}</th>
                                            <th>{{ __('ID') }}</th>
                                            <th>{{ __('Thumbnail') }}</th>
                                            <th>{{ __('Category Name') }}</th>
                                            <th>{{ __('Status') }}</th>
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

        {{-- Footer --}}
        @include('user.includes.footer')
    </div>

    {{-- Add Category Modal --}}
    <div class="modal modal-bg fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel">{{ __('Add New Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCategoryForm">
                        <input type="hidden" id="storeId" value="{{ $business_card->card_id }}">

                        {{-- Category Name --}}
                        <div class="mb-3">
                            <label class="form-label required" for="categoryName">{{ __('Category Name') }}</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tag"
                                        width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        <path
                                            d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                    </svg>
                                </span>
                                <input type="text" class="form-control" name="category_name" id="categoryName"
                                    placeholder="{{ __('e.g., Beverages') }}" required>
                            </div>
                        </div>

                        {{-- Thumbnail Upload --}}
                        <div class="mb-3">
                            <label class="form-label required" for="categoryImage">{{ __('Category Thumbnail') }}</label>
                            <input type="file" class="form-control" name="category_image" id="categoryImage"
                                accept=".jpeg,.jpg,.png" required />
                            <small class="form-hint">{{ __('Supported formats: JPEG, JPG, PNG (Max: 2MB)') }}</small>
                        </div>
                    </form>
                </div>
                {{-- Symmetrical Action Footer --}}
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="saveCategory()">
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

    {{-- Edit Category Modal --}}
    <div class="modal modal-bg fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editCategoryModalLabel">{{ __('Edit Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="categoryId" name="category_id">

                        {{-- Category Name --}}
                        <div class="mb-3">
                            <label class="form-label required" for="updateCategoryName">{{ __('Category Name') }}</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tag"
                                        width="18" height="18" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        <path
                                            d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                    </svg>
                                </span>
                                <input type="text" class="form-control" name="category_name" id="updateCategoryName"
                                    placeholder="{{ __('Enter category name') }}" required>
                            </div>
                        </div>

                        {{-- Thumbnail Upload --}}
                        <div class="mb-3">
                            <label class="form-label" for="updateCategoryImage">{{ __('Category Thumbnail') }}</label>
                            <input type="file" class="form-control" name="category_image" id="updateCategoryImage"
                                accept=".jpeg,.jpg,.png" />
                            <small
                                class="form-hint">{{ __('Leave empty to keep current thumbnail. Formats: JPEG, JPG, PNG') }}</small>
                        </div>
                    </form>
                </div>
                {{-- Symmetrical Action Footer --}}
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center px-4 shadow-sm"
                        onclick="updateCategory()">
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

    {{-- Delete Category Modal --}}
    <div class="modal modal-bg fade" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <div class="text-muted">{{ __('This category and its associations will be permanently removed.') }}
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="w-100">
                        <div class="row g-2">
                            <div class="col">
                                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">
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
@endsection

{{-- Custom JS --}}
@push('custom-js')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    {{-- Initialize DataTables --}}
    <script>
        $(document).ready(function() {
            "use strict";

            var categoriesTable = $('#categoriesTable').DataTable({
                processing: false,
                serverSide: true,
                dom: "<'d-flex justify-content-end px-3 pt-3 search-wrapper'f>" +
                    "<'table-responsive px-3 pt-2 py-2'tr>" +
                    "<'card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 bg-transparent border-top-0'<'text-muted'i><'pagination-wrapper'p>>",
                ajax: {
                    url: "{{ route('user.edit.categories', $business_card->card_id) }}",
                    dataSrc: 'data'
                },
                language: {
                    "sProcessing": `{{ __('Processing...') }}`,
                    "sLengthMenu": `{{ __('Show _MENU_ entries') }}`,
                    "sSearch": ``,
                    "sSearchPlaceholder": `{{ __('Search categories...') }}`,
                    "oPaginate": {
                        "sNext": `{{ __('Next') }}`,
                        "sPrevious": `{{ __('Previous') }}`
                    },
                    "sInfo": `{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}`,
                    "sInfoEmpty": `{{ __('Showing 0 to 0 of 0 entries') }}`,
                    "sInfoFiltered": `{{ __('(filtered from _MAX_ total entries)') }}`,
                    loadingRecords: `{{ __('Please wait - loading...') }}`,
                    emptyTable: `{{ __('No categories found.') }}`
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'rc-hide'
                    },
                    {
                        data: 'category_id',
                        name: 'category_id'
                    },
                    {
                        data: 'thumbnail',
                        name: 'thumbnail'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'status',
                        name: 'status'
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

            // Handle processing animation
            $('#categoriesTable').on('processing.dt', function(e, settings, processing) {
                var $responsiveWrapper = $(this).closest('.table-responsive');
                if (processing) {
                    $responsiveWrapper.addClass('placeholder-glow');
                } else {
                    $responsiveWrapper.removeClass('placeholder-glow');
                }
            });
        });

        // Trigger Add Category Modal
        function addCategory() {
            "use strict";
            $('#addCategoryForm').trigger("reset");
            $('#addCategoryModal').modal('show');
        }

        // Save New Category
        function saveCategory() {
            "use strict";

            var form = $('#addCategoryForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('store_id', $('#storeId').val());

            $.ajax({
                url: '{{ route('user.save.store.category') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('New category added successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#addCategoryModal').modal('hide');
                        $('#addCategoryForm').trigger("reset");
                        $('#categoriesTable').DataTable().ajax.reload(null, false);
                    } else {
                        $('#addCategoryModal').modal('hide');
                        $('#errorMessage').text(response.message || "{{ __('Failed to add category.') }}")
                            .show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function(xhr) {
                    $('#addCategoryModal').modal('hide');
                    $('#errorMessage').text("{{ __('Something went wrong! Please try again.') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Fetch & Open Edit Category Modal
        function editCategory(categoryId) {
            "use strict";
            $.ajax({
                url: '/user/edit-category/' + categoryId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#categoryId').val(response.data.category_id);
                        $('#updateCategoryName').val(response.data.category_name);
                        $('#editCategoryModal').modal('show');
                    } else {
                        $('#errorMessage').text("{{ __('Failed to fetch category data.') }}").show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Update Existing Category
        function updateCategory() {
            "use strict";

            var form = $('#editCategoryForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('categoryId', $('#categoryId').val());

            $.ajax({
                url: '{{ route('user.update.store.category') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#successMessage').text("{{ __('Category updated successfully!') }}").show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#editCategoryModal').modal('hide');
                        $('#categoriesTable').DataTable().ajax.reload(null, false);
                    } else {
                        $('#editCategoryModal').modal('hide');
                        $('#errorMessage').text(response.message || "{{ __('Failed to update category.') }}")
                            .show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                },
                error: function() {
                    $('#editCategoryModal').modal('hide');
                    $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                    $('#errorMessage').delay(2500).fadeOut(500);
                }
            });
        }

        // Open Delete Confirmation Modal
        function deleteCategory(categoryId) {
            $('#deleteCategoryModal').data('category-id', categoryId).modal('show');
        }

        // Handle Delete Confirmation
        $(document).ready(function() {
            $('#confirmDeleteButton').click(function() {
                var categoryId = $('#deleteCategoryModal').data('category-id');
                $.ajax({
                    url: '/user/delete-category/' + categoryId,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#successMessage').text("{{ __('Category deleted successfully!') }}")
                            .show();
                        $('#successMessage').delay(2500).fadeOut(500);
                        $('#deleteCategoryModal').modal('hide');
                        $('#categoriesTable').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        $('#deleteCategoryModal').modal('hide');
                        $('#errorMessage').text("{{ __('Error deleting category.') }}").show();
                        $('#errorMessage').delay(2500).fadeOut(500);
                    }
                });
            });
        });
    </script>
@endpush
