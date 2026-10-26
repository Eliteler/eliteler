@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        .ts-control>input {
            display: contents !important;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        {{-- Page title header --}}
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    {{-- Breadcrumbs (Replaces Overview pretitle) --}}
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
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.currencies') }}" class="text-secondary text-decoration-none">
                                    {{ __('Currencies') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Create') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            <span class="me-1">{{ __('Create Currency') }}</span>
                        </h2>
                        {{-- Added helper subtitle under page heading --}}
                        <div class="text-muted mt-1">
                            {{ __('Create a new currency for your business.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
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

            <div class="container-fluid">
                <div class="row row-deck row-cards">
                    <div class="col-md-6 col-sm-12">
                        <form action="{{ route('admin.save.currency') }}" method="post" class="card border-0 shadow-sm">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xl-12 mb-3">
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="row">
                                                    {{-- Priority --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Priority') }}</label>
                                                            <input type="number" class="form-control" name="priority"
                                                                placeholder="{{ __('Enter priority. E.g., 100') }}">
                                                        </div>
                                                    </div>
                                                    {{-- Name --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label required">{{ __('Name') }}</label>
                                                            <input type="text" class="form-control" name="name"
                                                                placeholder="{{ __('Enter currency name') }}" required>
                                                        </div>
                                                    </div>
                                                    {{-- ISO Code --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label required">{{ __('ISO Code') }}</label>
                                                            <input type="text" class="form-control" name="iso_code"
                                                                placeholder="{{ __('Enter ISO code') }}" required>
                                                        </div>
                                                    </div>
                                                    {{-- Symbol --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label required">{{ __('Symbol') }}</label>
                                                            <input type="text" class="form-control" name="symbol"
                                                                placeholder="{{ __('Enter symbol') }}" required>
                                                        </div>
                                                    </div>
                                                    {{-- Subunit --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Subunit') }}</label>
                                                            <input type="text" class="form-control" name="subunit"
                                                                placeholder="{{ __('Enter subunit. E.g., Centime') }}">
                                                        </div>
                                                    </div>
                                                    {{-- Subunit to unit --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Subunit to unit') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="subunit_to_unit"
                                                                placeholder="{{ __('Enter subunit to unit, E.g., 100') }}">
                                                        </div>
                                                    </div>
                                                    {{-- Symbol First --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label required">{{ __('Symbol First') }}</label>
                                                            <select name="symbol_first" id="symbol_first"
                                                                class="form-select" required>
                                                                <option value="false">{{ __('No') }}</option>
                                                                <option value="true">{{ __('Yes') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    {{-- HTML entity --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('HTML entity') }}</label>
                                                            <input type="text" class="form-control" name="html_entity"
                                                                placeholder="{{ __('Enter HTML entity. E.g., &euro;') }}">
                                                        </div>
                                                    </div>
                                                    {{-- Decimal mark --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Decimal mark') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="decimal_mark"
                                                                placeholder="{{ __('Enter decimal places. E.g., ,') }}">
                                                        </div>
                                                    </div>
                                                    {{-- Thousand separator --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label">{{ __('Thousand separator') }}</label>
                                                            <input type="text" class="form-control"
                                                                name="thousand_separator"
                                                                placeholder="{{ __('Enter thousand separator. E.g., .') }}">
                                                        </div>
                                                    </div>
                                                    {{-- Iso numberic --}}
                                                    <div class="col-md-6 col-xl-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Iso numberic') }}</label>
                                                            <input type="text" class="form-control" name="iso_numeric"
                                                                placeholder="{{ __('Enter iso numeric. E.g., 952') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-device-floppy me-2" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />
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

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>
@endsection
