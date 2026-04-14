@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    <style>
        #uploadLoader {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.75);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        #uploadLoader .cube-grid {
            display: grid;
            grid-template-columns: repeat(3, 18px);
            grid-template-rows: repeat(3, 18px);
            gap: 6px;
        }

        #uploadLoader .cube {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            background: #4f8ef7;
            animation: cubeFade 1.4s ease-in-out infinite;
        }

        #uploadLoader .cube:nth-child(1) {
            animation-delay: 0.0s;
        }

        #uploadLoader .cube:nth-child(2) {
            animation-delay: 0.1s;
        }

        #uploadLoader .cube:nth-child(3) {
            animation-delay: 0.2s;
        }

        #uploadLoader .cube:nth-child(4) {
            animation-delay: 0.3s;
        }

        #uploadLoader .cube:nth-child(5) {
            animation-delay: 0.4s;
        }

        #uploadLoader .cube:nth-child(6) {
            animation-delay: 0.5s;
        }

        #uploadLoader .cube:nth-child(7) {
            animation-delay: 0.6s;
        }

        #uploadLoader .cube:nth-child(8) {
            animation-delay: 0.7s;
        }

        #uploadLoader .cube:nth-child(9) {
            animation-delay: 0.8s;
        }

        #uploadLoader .loader-title {
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            margin: 0;
            letter-spacing: 0.04em;
        }

        #uploadLoader .loader-subtitle {
            color: rgba(255, 255, 255, 0.45);
            font-size: 12px;
            font-family: inherit;
            margin: 0;
        }

        @keyframes cubeFade {

            0%,
            70%,
            100% {
                opacity: 0.15;
                transform: scale(0.85);
            }

            35% {
                opacity: 1;
                transform: scale(1);
            }
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
                        <h2 class="page-title mb-2">
                            {{ __('Plugins') }}
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            {{-- Upload Plugin --}}
                            <button class="btn btn-primary" onclick="openFileManager()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-upload">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                    <path d="M7 9l5 -5l5 5" />
                                    <path d="M12 4l0 12" />
                                </svg>{{ __('Upload') }}
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

                {{-- Plugins --}}
                {{-- check is empty --}}
                @if (empty($plugins))
                    <div class="empty">
                        <div class="empty-img">
                            <img src="{{ asset('img/plugins.svg') }}" height="256" alt="Plugins"
                                style="width: 100%; height: 250px;">
                        </div>
                        <p class="empty-title">{{ __('Coming Soon!') }}</p>
                        <p class="empty-subtitle text-secondary">
                            {{ __('Plugins are used to add extra functionality to GoBiz.') }}
                            <br>
                            {{ __('You can install plugins from the GoBiz Plugins Store.') }}
                        </p>
                        {{-- Notify Me --}}
                        <div class="empty-action">
                            <a href="https://zcmp.in/zThd?ref={{ urlencode(config('app.url')) }}&size=source"
                                target="_blank" class="btn btn-primary btn-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                    <path d="M3 7l9 6l9 -6" />
                                </svg>
                                {{ __('Notify Me') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="row">
                        @php
                            $systemPlugins = [
                                'TawkChat',
                                'WhatsappChatButton',
                                'WhatsAppChatButton',
                                'GoogleRecaptcha',
                                'GoogleOAuth',
                                'GoogleAnalytics',
                                'GoogleAdSense',
                                'MaintenanceMode',
                                'SMTP',
                                'SubDomain',
                                'OrderNFCSystem',
                                'ReferralSystem',
                                'CookieConsent',
                                'WebTools',
                                'AiBuilder',
                            ];
                        @endphp

                        {{-- System plugins --}}
                        @foreach ($plugins as $plugin)
                            @if (in_array($plugin['plugin_id'], $systemPlugins))
                                <div class="col-sm-12 col-md-4 col-lg-4 mb-3">
                                    <div class="card h-100 d-flex flex-column">
                                        <div class="card-body flex-grow-1">
                                            <div class="d-flex align-items-center mb-3">
                                                <img class="rounded-3" src="{{ asset('img/plugins/' . $plugin['img']) }}"
                                                    onerror="this.onerror=null; this.src='{{ asset('img/plugins/default.png') }}';"
                                                    style="width: 10%">
                                                <h3 class="card-title ms-2 mb-0">{{ __($plugin['name']) }}</h3>
                                            </div>
                                            <p class="text-secondary">{{ __($plugin['description']) }}</p>
                                        </div>
                                        <div class="d-flex align-items-end gap-2 justify-content-between me-3 mb-3">
                                            <p class="text-secondary ms-3">v{{ $plugin['version'] }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                {{-- Settings --}}
                                                <a href="{{ route($plugin['main_route']) }}"
                                                    style="width:42px; height:42px;"
                                                    class="btn btn-white btn-icon d-flex align-items-center gap-2 justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        style="width:28px; height:28px;" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="icon icon-tabler icon-tabler-settings">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Custom plugins --}}
                        @foreach ($plugins as $plugin)
                            @if (!in_array($plugin['plugin_id'], $systemPlugins))
                                <div class="col-sm-12 col-md-4 col-lg-4 mb-3">
                                    <div class="card h-100 d-flex flex-column">
                                        <div class="card-body flex-grow-1">
                                            <div class="d-flex align-items-center mb-3">
                                                <img class="rounded-3" src="{{ asset('img/plugins/' . $plugin['img']) }}"
                                                    onerror="this.onerror=null; this.src='{{ asset('img/plugins/default.png') }}';"
                                                    style="width: 10%">
                                                <h3 class="card-title ms-2 mb-0">{{ __($plugin['name']) }}</h3>
                                            </div>
                                            <p class="text-secondary">{{ __($plugin['description']) }}</p>
                                        </div>
                                        <div class="d-flex align-items-end gap-2 justify-content-between me-3 mb-3">
                                            <p class="text-secondary ms-3">v{{ $plugin['version'] }}</p>
                                            <div class="d-flex align-items-center gap-2">
                                                {{-- Delete --}}
                                                <form action="{{ route('admin.plugins.delete', $plugin['plugin_id']) }}"
                                                    method="POST" id="deleteForm{{ $plugin['plugin_id'] }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" style="width:42px; height:42px;"
                                                        class="btn btn-danger btn-icon text-white d-flex justify-content-center align-items-center"
                                                        data-bs-toggle="modal"
                                                        onclick="confirmationModel('{{ $plugin['plugin_id'] }}')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" style="width:28px; height:28px;"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icon-tabler-trash">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M4 7l16 0" />
                                                            <path d="M10 11l0 6" />
                                                            <path d="M14 11l0 6" />
                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                        </svg>
                                                    </button>
                                                </form>

                                                {{-- Settings --}}
                                                <a href="{{ route($plugin['main_route']) }}"
                                                    style="width:42px; height:42px;"
                                                    class="btn btn-white btn-icon d-flex align-items-center gap-2 justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        style="width:28px; height:28px;" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        class="icon icon-tabler icon-tabler-settings">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        @include('admin.includes.footer')
    </div>

    <!-- Confirmation Modal -->
    <div class="modal modal-blur fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">{{ __('Confirm Delete') }}</h5>
                </div>
                <div class="modal-body">
                    {{ __('Are you sure you want to remove this plugin? This action cannot be undone.') }}</div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(this)"
                        id="confirmDeleteBtn">{{ __('Remove') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Loader --}}
    <div id="uploadLoader">
        <div class="cube-grid">
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
            <div class="cube"></div>
        </div>
        <p class="loader-title">{{ __('Installing Template...') }}</p>
        <p class="loader-subtitle">{{ __('Please don\'t close this page') }}</p>
    </div>

@section('scripts')
    <script type="text/javascript">
        "use strict";

        // Confirm delete
        function confirmationModel(pluginId) {
            // trugger alert
            // alert(pluginId);
            $('#confirmDeleteModal').modal('show');

            let btn = document.getElementById('confirmDeleteBtn');
            // add custom value to btn
            btn.setAttribute('data-plugin-id', pluginId);

        }

        // Confirm delete
        function confirmDelete(btn) {
            let pluginId = btn.getAttribute('data-plugin-id');
            let form = document.getElementById('deleteForm' + pluginId);
            form.submit();
        }

        // Open file manager
        function openFileManager() {
            let input = document.createElement('input');
            input.type = 'file';
            input.accept = '.zip'; // Allow only ZIP files
            input.onchange = function(event) {
                let file = event.target.files[0];
                if (file) {
                    sendZipFile(file);
                }
            };
            input.click();
        }

        // Send ZIP file to server
        function sendZipFile(file) {
            let formData = new FormData();
            formData.append('zip_file', file);

            // Show loader
            let loader = document.getElementById('uploadLoader');
            loader.style.display = 'flex';

            fetch("{{ route('admin.plugin.upload') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loader before reload
                    loader.style.display = 'none';

                    if (data.message === 'Plugin installation success!') {
                        window.location.reload(true);
                    } else if (data.message === 'Plugin Installation failed!') {
                        window.location.reload(true);
                    }
                })
                .catch(error => {
                    // Hide loader on error too
                    loader.style.display = 'none';
                });
        }
    </script>
@endsection
@endsection
