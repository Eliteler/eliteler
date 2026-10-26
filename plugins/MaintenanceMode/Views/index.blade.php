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
        <!-- Page Title Header Wrapper (Wraps the inner container-fluid) -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        {{-- Breadcrumbs --}}
                        <x-breadcrumb :items="[
                            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
                            ['title' => 'Plugins', 'route' => 'admin.plugins'],
                            ['title' => 'Maintenance Mode'],
                        ]" />

                        <h2 class="page-title">
                            {{ __('Maintenance Mode') }}
                        </h2>

                        <!-- Helper Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Control the global platform status during scheduled upgrades, server updates, or administrative system changes.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Body -->
        <div class="page-body">
            <div class="container-fluid">
                {{-- Failed Alert --}}
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

                {{-- Success Alert --}}
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
                    <div class="col-12 col-md-4">
                        <!-- Upgraded to Borderless Shadowed Card Wrapper -->
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.plugin.maintenance-mode.update') }}" method="post">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        {{-- Maintenance Mode Switch and Helper Copy --}}
                                        <div class="col-12">
                                            <div class="form-label d-flex justify-content-between align-items-center mb-1">
                                                <p class="fw-bold fs-4">{{ __('Maintenance Mode') }}</p>
                                                <label class="form-check form-switch m-0">
                                                    <input name="maintenance_mode" class="form-check-input" type="checkbox"
                                                        {{ app()->isDownForMaintenance() ? 'checked' : '' }} value="1">
                                                </label>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ __('Toggle this switch to take the platform offline. General public traffic will be routed to a clean temporary downtime message.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Standardized Right-Aligned Form Footer (Omitted Cancel Button) -->
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary btn-md d-inline-flex align-items-center">
                                        <!-- Device Floppy Icon -->
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
