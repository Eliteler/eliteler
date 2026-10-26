@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('content')
    <div class="page-wrapper">
        <!-- Page title header wrapper -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Breadcrumbs -->
                    <div class="mb-1 ms-0">
                        <ol class="breadcrumb" aria-label="breadcrumbs">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.settings') }}" class="text-secondary text-decoration-none">
                                    {{ __('System Utilities') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Invoice & Tax Settings') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Invoice & Tax Settings') }}
                        </h2>
                        <!-- Added helper subtitle under page heading -->
                        <div class="text-muted mt-1">
                            {{ __('Configure your tax details and invoice information used for invoices and billing emails.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed alert --}}
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

                {{-- Success alert --}}
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

                <div class="row row-deck row-cards g-3">
                    {{-- Form 1: General Invoicing & Tax Details --}}
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.update.tax.setting') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Invoice Number Prefix --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label
                                                    class="form-label required">{{ __('Invoice Number Prefix') }}</label>
                                                <input type="text" class="form-control" name="invoice_prefix"
                                                    placeholder="{{ __('Invoice Number Prefix') }}"
                                                    value="{{ $config[15]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- Name --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Name') }}</label>
                                                <input type="text" class="form-control" name="invoice_name"
                                                    placeholder="{{ __('Name') }}"
                                                    value="{{ $config[16]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- Email --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Email') }}</label>
                                                <input type="email" class="form-control" name="invoice_email"
                                                    placeholder="{{ __('Email') }}"
                                                    value="{{ $config[17]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- Phone --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Phone') }}</label>
                                                <input type="text" class="form-control" name="invoice_phone"
                                                    placeholder="{{ __('Phone') }}"
                                                    value="{{ $config[18]->config_value }}">
                                            </div>
                                        </div>

                                        {{-- Address --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Address') }}</label>
                                                <input type="text" class="form-control" name="invoice_address"
                                                    id="invoice_address" placeholder="{{ __('Address') }}"
                                                    value="{{ $config[19]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- City --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('City') }}</label>
                                                <input type="text" class="form-control" name="invoice_city"
                                                    placeholder="{{ __('City') }}"
                                                    value="{{ $config[20]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- State/Province --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('State/Province') }}</label>
                                                <input type="text" class="form-control" name="invoice_state"
                                                    placeholder="{{ __('State/Province') }}"
                                                    value="{{ $config[21]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- ZIP Code --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('ZIP Code') }}</label>
                                                <input type="text" class="form-control" name="invoice_zipcode"
                                                    placeholder="{{ __('ZIP Code') }}"
                                                    value="{{ $config[22]->config_value }}">
                                            </div>
                                        </div>

                                        {{-- Country --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Country') }}</label>
                                                <input type="text" class="form-control" name="invoice_country"
                                                    placeholder="{{ __('Country') }}"
                                                    value="{{ $config[23]->config_value }}" required>
                                            </div>
                                        </div>

                                        {{-- Tax Name --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Tax Name') }}</label>
                                                <input type="text" class="form-control" name="tax_name"
                                                    placeholder="{{ __('Tax Name') }}"
                                                    value="{{ $config[24]->config_value }}">
                                            </div>
                                        </div>

                                        {{-- Tax ID --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Tax ID') }}</label>
                                                <input type="text" class="form-control" name="tax_number"
                                                    placeholder="{{ __('Tax ID') }}"
                                                    value="{{ $config[26]->config_value }}">
                                            </div>
                                        </div>

                                        {{-- Tax Value --}}
                                        <div class="col-md-4 col-lg-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Tax Value') }}</label>
                                                <input type="text" class="form-control" name="tax_value"
                                                    placeholder="{{ __('Tax Value') }}"
                                                    value="{{ $config[25]->config_value }}">
                                            </div>
                                        </div>

                                        {{-- Invoice Footer --}}
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Invoice Footer') }}</label>
                                                <textarea class="form-control" name="invoice_footer" id="invoice_footer" rows="3"
                                                    placeholder="{{ __('Invoice Footer') }}" required>{{ $config[29]->config_value }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Simplified Footer with standard device-floppy save button (No Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4h-6l0 -4" />
                                        </svg>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Form 2: Email Heading & Footer settings --}}
                <div class="row row-deck row-cards mt-4 g-3">
                    <div class="col-12">
                        <h2 class="page-title mb-2 fs-3">
                            {{ __('Invoice Email Settings') }}
                        </h2>
                    </div>
                    <div class="col-12">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.update.email.setting') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Email Heading --}}
                                        <div class="col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Email Heading') }}</label>
                                                <textarea class="form-control" name="email_heading" id="email_heading" rows="3"
                                                    placeholder="{{ __('Email Heading') }}" required>{{ $config[27]->config_value }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Email Footer --}}
                                        <div class="col-md-6 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label required">{{ __('Email Footer') }}</label>
                                                <textarea class="form-control" name="email_footer" id="email_footer" rows="3"
                                                    placeholder="{{ __('Email Footer') }}" required>{{ $config[28]->config_value }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Simplified Footer with standard device-floppy save button (No Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit"
                                        class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
                                            <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                            <path d="M14 4l0 4h-6l0 -4" />
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
        @include('admin.includes.footer')
    </div>
@endsection
