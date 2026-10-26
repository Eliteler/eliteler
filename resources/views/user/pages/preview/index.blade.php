<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $datas['business_card_details']->title }} - {{ __('Preview') }}
    </title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset($datas['business_card_details']->profile) }}">

    {{-- Tabler CSS --}}
    <link rel="stylesheet" href="{{ asset('css/tabler.min.css') }}">

    {{-- Preview CSS --}}
    <link rel="stylesheet" href="{{ asset('css/preview.css') }}">
</head>

<body class="bg-light">

    <!-- Preview Header -->
    <div class="navbar navbar-dark bg-primary border-0 preview-header">

        <div class="container-fluid">

            <div class="preview-title">
                {{ __('You are viewing this page in preview mode') }}
            </div>

            <!-- Device Switcher: layout changes ONLY when these buttons are clicked -->
            <div class="device-switcher" role="group" aria-label="{{ __('Preview device') }}">

                <button type="button" id="deviceDesktop" class="device-btn active" data-device="desktop"
                    title="{{ __('Desktop') }}" aria-label="{{ __('Desktop') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <rect x="3" y="4" width="18" height="12" rx="1" />
                        <line x1="7" y1="20" x2="17" y2="20" />
                        <line x1="9" y1="16" x2="9" y2="20" />
                        <line x1="15" y1="16" x2="15" y2="20" />
                    </svg>
                </button>

                <button type="button" id="deviceTablet" class="device-btn" data-device="tablet"
                    title="{{ __('Tablet') }}" aria-label="{{ __('Tablet') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <rect x="4" y="3" width="16" height="18" rx="1" />
                        <line x1="11" y1="17" x2="13" y2="17" />
                    </svg>
                </button>

                <button type="button" id="deviceMobile" class="device-btn" data-device="mobile"
                    title="{{ __('Mobile') }}" aria-label="{{ __('Mobile') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <rect x="7" y="4" width="10" height="16" rx="1" />
                        <line x1="11" y1="17" x2="13" y2="17" />
                    </svg>
                </button>

            </div>

            <a href="{{ $previewReturnUrl }}" class="btn btn-light preview-exit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icon-tabler-logout-2 me-1">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />

                    <path d="M10 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" />

                    <path d="M15 12h-12l3 -3" />

                    <path d="M6 15l-3 -3" />
                </svg>

                {{ __('Exit') }}
            </a>

        </div>

    </div>


    <!-- Preview Content -->
    <div id="previewContent" class="preview-content device-desktop">

        <div class="device-frame">
            <iframe id="previewIframe" src="{{ $previewUrl }}" title="{{ __('VCard Preview') }}"
                class="iframe-preview"></iframe>
        </div>

    </div>


    <script>
        // Iframe scrollbar hide (unchanged behaviour)
        document.getElementById('previewIframe').addEventListener('load', function() {
            try {
                const iframeDocument = this.contentDocument || this.contentWindow.document;

                const style = iframeDocument.createElement('style');

                style.textContent = `
                    html,
                    body {
                        scrollbar-width: none !important;
                    }

                    html::-webkit-scrollbar,
                    body::-webkit-scrollbar {
                        width: 0 !important;
                        height: 0 !important;
                        display: none !important;
                    }
                `;

                iframeDocument.head.appendChild(style);

            } catch (error) {
                console.warn('Unable to modify preview scrollbar:', error);
            }
        });

        // Device switcher — layout ONLY changes on click, never automatically
        (function() {
            const buttons = document.querySelectorAll('.device-btn');
            const content = document.getElementById('previewContent');

            buttons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const device = btn.getAttribute('data-device');

                    // update active state
                    buttons.forEach(function(b) {
                        b.classList.remove('active');
                    });
                    btn.classList.add('active');

                    // update layout class only — nothing else reacts to resize
                    content.classList.remove('device-desktop', 'device-tablet', 'device-mobile');
                    content.classList.add('device-' + device);
                });
            });
        })();
    </script>

</body>

</html>
