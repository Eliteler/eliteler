@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

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
                            {{ __('Categories') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <button type="button" class="btn btn-icon btn-primary" onclick="addCategory()"
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
                                    @include('user.pages.edit-store.include.nav-link', ['link' => 'categories'])
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-10 d-flex flex-column">
                            <div class="card-body m-0 py-0 px-2">
                                {{-- Categories --}}
                                <div class="row">
                                    <div class="table-responsive">
                                        <table id="categoriesTable" class="table table-vcenter card-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('#') }}</th>
                                                    <th>{{ __('ID') }}</th>
                                                    <th>{{ __('Thumbnail') }}</th>
                                                    <th>{{ __('Category Name') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th class="w-1"></th>
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

        {{-- Add category --}}
        <div class="modal modal-blur fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">{{ __('Add Category') }}</h5>
                    </div>
                    <div class="modal-body">
                        <form id="addCategoryForm">
                            <div class="row">
                                <input type="hidden" id="storeId" value="{{ $business_card->card_id }}">
                                
                                {{-- Thumbnail --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <div class="form-label required">{{ __('Thumbnail') }}</div>
                                        <input type="file" class="form-control" name="category_image" id="categoryImage"
                                            placeholder="{{ __('Thumbnail') }}"
                                            accept=".jpeg,.jpg,.png" required />
                                    </div>
                                </div>

                                {{-- Category Name --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Category Name') }}</label>
                                        <input type="text" class="form-control" name="category_name" id="categoryName"
                                            placeholder="{{ __('Category Name') }}" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary"
                            onclick="saveCategory()">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Update category --}}
        <div class="modal modal-blur fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">{{ __('Edit Category') }}</h5>
                    </div>
                    <div class="modal-body">
                        <form id="editCategoryForm">
                            <div class="row">
                                <input type="hidden" id="categoryId" name="category_id">
                                {{-- Thumbnail --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <div class="form-label">{{ __('Thumbnail') }}</div>
                                        <input type="file" class="form-control" name="category_image" id="updateCategoryImage"
                                            placeholder="{{ __('Thumbnail') }}"
                                            accept=".jpeg,.jpg,.png" />
                                    </div>
                                </div>

                                {{-- Category Name --}}
                                <div class="col-md-12 col-xl-12">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Category Name') }}</label>
                                        <input type="text" class="form-control" name="category_name" id="updateCategoryName"
                                            placeholder="{{ __('Category Name') }}" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" class="btn btn-primary"
                            onclick="updateCategory()">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete category modal -->
        <div class="modal modal-blur fade" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-status"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 9v2m0 4v.01" />
                            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
                        </svg>
                        <h3>{{ __('Are you sure?') }}</h3>
                        <div id="delete_status" class="text-muted">{{ __('Are you sure you want to delete this category?') }}</div>
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
    </div>

    {{-- Custom JS --}}
    @push('custom-js')
        <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
        <!-- Initialize DataTables -->
        <script>
            // Get categories
            $(document).ready(function() {
                "use strict";
                $('#categoriesTable').DataTable({
                    processing: false,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('user.edit.categories', $business_card->card_id) }}", // Replace with your actual API endpoint
                        dataSrc: 'data' // If your data is nested under a key 'data' in the response
                    },
                    language: {
                        "sProcessing": `{{ __("Processing...") }}`,
                        "sLengthMenu": `{{ __("Show _MENU_ entries") }}`,
                        "sSearch": `{{ __("Search:") }}`,
                        "oPaginate": {
                            "sNext": `{{ __("Next") }}`,
                            "sPrevious": `{{ __("Previous") }}`
                        },
                        "sInfo": `{{ __("Showing _START_ to _END_ of _TOTAL_ entries") }}`,
                        "sInfoEmpty": `{{ __("Showing 0 to 0 of 0 entries") }}`,
                        "sInfoFiltered": `{{ __("(filtered from _MAX_ total entries)") }}`,
                        "sInfoPostFix": "",
                        "sUrl": "",
                        "oAria": {
                            "sSortAscending": `{{ __(": activate to sort column in ascending order") }}`,
                            "sSortDescending": `{{ __(": activate to sort column in descending order") }}`
                        },
                        loadingRecords: `{{ __("Please wait - loading...") }}`,
                        emptyTable: `{{ __("No data available in the table") }}` // Message for an empty table
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {data: 'category_id', name: 'category_id'},
                        {
                            data: 'thumbnail', 
                            name: 'thumbnail'
                        },
                        {data: 'category_name', name: 'category_name'},
                        {data: 'status', name: 'status'},
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });
            });

            // Open add category modal
            function addCategory() {
                "use strict";
                $('#addCategoryModal').modal('show');
            }

            // Save Category
            function saveCategory() {
                "use strict";

                var form = $('#addCategoryForm')[0]; // Get the form element
                var formData = new FormData(form); // Create FormData from the form
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('store_id', $('#storeId').val()); // Add extra field not in form

                $.ajax({
                    url: '{{ route('user.save.store.category') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#successMessage').text("{{ __('New Category added successfully!') }}").show();
                            $('#successMessage').delay(1000).fadeOut(1000);
                            $('#addCategoryModal').modal('hide');
                            $('#addCategoryForm').trigger("reset");
                            $('#categoriesTable').DataTable().ajax.reload();
                        } else {
                            $('#addCategoryModal').modal('hide');
                            $('#errorMessage').text(response.message || "{{ __('Failed to add category.') }}").show();
                            $('#errorMessage').delay(1000).fadeOut(1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#addCategoryModal').modal('hide');
                        $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                });
            }

            // Edit category modal
            function editCategory(categoryId) {
                "use strict";
                $.ajax({
                    url: '/user/edit-category/' + categoryId,
                    method: 'GET',
                    success: function(response) {
                        console.log(response);
                        
                        if (response.success) {
                            $('#categoryId').val(response.data.category_id);
                            $('#updateCategoryThumbnail').val(response.data.thumbnail);
                            $('#updateCategoryName').val(response.data.category_name);
                            $('#editCategoryModal').modal('show');
                        } else {
                            // Display error message
                            $('#errorMessage').text("{{ __('Failed to fetch category data.') }}").show();
                            $('#errorMessage').delay(1000).fadeOut(1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Display error message
                        $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                });
            }

            // Update category
            function updateCategory() {
                "use strict";

                var form = $('#editCategoryForm')[0]; // Get the form element
                var formData = new FormData(form); // Create FormData from the form
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('categoryId', $('#categoryId').val()); // Add extra field not in form

                $.ajax({
                    url: '{{ route('user.update.store.category') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Display success message
                            $('#successMessage').text("{{ __('Category updated successfully!') }}").show();
                            $('#successMessage').delay(1000).fadeOut(1000);
                            $('#editCategoryModal').modal('hide');

                            // Reload category table
                            $('#categoriesTable').DataTable().ajax.reload();
                        } else {
                            // Display error message
                            $('#editCategoryModal').modal('hide');
                            $('#errorMessage').text("{{ __('Failed to update category.') }}").show();
                            $('#errorMessage').delay(1000).fadeOut(1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Display error message
                        $('#editCategoryModal').modal('hide');
                        $('#errorMessage').text("{{ __('Something went wrong!') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                });
            }

            // Function to delete the category
            function deleteCategory(categoryId) {
                $('#deleteCategoryModal').data('category-id', categoryId).modal('show');
            }

            // jQuery to handle the modal "Okay" button click
            $(document).ready(function() {
                $('#confirmDeleteButton').click(function() {
                    deleteConfirmCategory();
                });
            });

            // Confirm delete category
            function deleteConfirmCategory() {
                var categoryId = $('#deleteCategoryModal').data('category-id');
                $.ajax({
                    url: '/user/delete-category/' + categoryId,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') // Include CSRF token
                    },
                    success: function(response) {
                        // Display success message
                        $('#successMessage').text("{{ __('Category deleted successfully!') }}").show();
                        $('#successMessage').delay(1000).fadeOut(1000);
                        $('#deleteCategoryModal').modal('hide');

                        // Reload category table
                        $('#categoriesTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        // Display error message
                        $('#addCategoryModal').modal('hide');
                        $('#errorMessage').text("{{ __('Error deleting category') }}").show();
                        $('#errorMessage').delay(1000).fadeOut(1000);
                    }
                });
            }
        </script>
    @endpush
@endsection
