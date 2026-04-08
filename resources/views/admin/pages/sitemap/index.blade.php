@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>
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
                        <h2 class="page-title mb-2">
                            {{ __('Generate Sitemap') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
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

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <form id="sitemapForm" action="{{ route('admin.generate.sitemap') }}" method="POST">
                                @csrf

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label required">{{ __('Categories') }}</label>
                                            <select class="form-select" name="categories[]" id="categories" multiple
                                                required>
                                                <option value="all" selected>{{ __('All') }}</option>
                                                <option value="pages">{{ __('Website Pages') }}</option>
                                                <option value="blog">{{ __('Blogs') }}</option>
                                                <option value="vcards">{{ __('vCards') }}</option>
                                                <option value="store">{{ __('Stores') }}</option>
                                                <option value="webtools">{{ __('Web Tools') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary" id="generateBtn">
                                        {{ __('Generate') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="modal modal-blur fade" id="sitemapModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <h4 class="mb-3">{{ __('Generating Sitemap') }}</h4>

                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>

                        <p class="text-muted mt-2 mb-0">{{ __('Please wait...') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    {{-- Custom JS --}}
@section('scripts')
    <script>
        // Array of element IDs
        var elementSelectors = ['categories'];

        // Function to initialize TomSelect and enforce the "required" attribute
        function initializeTomSelectWithRequired(el) {
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

            // Ensure the "required" attribute is enforced
            el.addEventListener('change', function() {
                if (el.value) {
                    el.setCustomValidity('');
                } else {
                    el.setCustomValidity('This field is required');
                }
            });

            // Trigger validation on load
            el.dispatchEvent(new Event('change'));
        }

        // Loop through each element ID
        elementSelectors.forEach(function(id) {
            // Check if the element exists
            var el = document.getElementById(id);
            if (el) {
                // Apply TomSelect and enforce the "required" attribute
                initializeTomSelectWithRequired(el);
            }
        });

        // Generate sitemap
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('sitemapForm');
            const btn = document.getElementById('generateBtn');
            const modalEl = document.getElementById('sitemapModal');
            const progressBar = document.getElementById('progressBar');

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: 'static',
                keyboard: false
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                btn.disabled = true;
                modal.show();

                let progress = 0;

                const setProgress = (val) => {
                    progressBar.style.width = val + '%';
                    progressBar.textContent = val + '%';
                };

                setProgress(10);

                const interval = setInterval(() => {
                    if (progress < 90) {
                        progress += 10;
                        setProgress(progress);
                    }
                }, 300);

                setTimeout(() => {
                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('[name=_token]').value,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new FormData(form)
                        })
                        .then(() => {
                            clearInterval(interval);
                            setProgress(100);

                            setTimeout(() => {
                                btn.disabled = false;
                                modal.hide();
                            }, 1000);
                        })
                        .catch(() => {
                            clearInterval(interval);
                            btn.disabled = false;
                            modal.hide();
                        });
                }, 2000);
            });
        });
    </script>
@endsection
@endsection
