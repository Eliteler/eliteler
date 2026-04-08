@extends('user.layouts.index', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
    <style>
        .vcard-uploader {
            height: 367px;
            width: 100%;
            border-radius: 18px;
            border: 3px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            cursor: pointer;
            transition: .3s;
        }

        .vcard-uploader:hover {
            border-color: var(--tblr-primary);
        }

        #ai-loading-screen {
            position: fixed;
            inset: 0;
            background: #000;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transition: opacity .8s ease;
        }

        #ai-loading-screen.show {
            opacity: 1;
            pointer-events: auto;
        }

        .loader-center {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }

        .liquid-bg {
            position: absolute;
            inset: 0;
            filter: blur(90px);
        }

        .blob {
            position: absolute;
            width: 100vmax;
            height: 100vmax;
            border-radius: 50%;
            mix-blend-mode: screen;
            opacity: .55;
            animation: fluid 25s infinite alternate ease-in-out;
        }

        .blob-1 {
            background: radial-gradient(circle, #4f46e5 0%, transparent 60%);
            top: -20%;
            left: -20%;
        }

        .blob-2 {
            background: radial-gradient(circle, #7e22ce 0%, transparent 60%);
            bottom: -20%;
            right: -10%;
            animation-duration: 20s;
        }

        .blob-3 {
            background: radial-gradient(circle, #0ea5e9 0%, transparent 60%);
            top: 20%;
            right: -20%;
            animation-duration: 30s;
        }

        @keyframes fluid {
            0% {
                transform: translate(0, 0) scale(1)
            }

            50% {
                transform: translate(10%, 5%) scale(1.1)
            }

            100% {
                transform: translate(-5%, 10%) scale(1)
            }
        }

        .status-text {
            font-size: 2.5rem;
            font-weight: 300;
            letter-spacing: -.03em;
            margin-bottom: 16px;
            background: linear-gradient(to right, #fff, #bbb, #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .loading-bar-mini {
            width: 120px;
            height: 2px;
            background: #ffffff40;
            overflow: hidden;
            position: relative;
        }

        .loading-bar-mini::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: white;
            animation: progress 2s infinite;
        }

        @keyframes progress {
            0% {
                left: -100%
            }

            50% {
                left: 0
            }

            100% {
                left: 100%
            }
        }

        /* Confetti above modal */
        .modal-confetti-canvas {
            position: absolute !important;
            inset: 0;
            z-index: 9999 !important;
            pointer-events: none;
        }
    </style>
@endsection


@section('content')
    <div class="page-wrapper px-2">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('AI Generator') }}
                        </h2>
                        <div class="text-muted">
                            {{ __('Upload your physical business card to generate digital business card.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $uploadSizeMB = round(env('SIZE_LIMIT') / 1024, 2);
        @endphp

        <div class="page-body">
            <div class="container-fluid">
                <div id="errorAlert" class="alert alert-important alert-danger alert-dismissible d-none" role="alert">
                    <div class="d-flex">
                        <div id="errorMessage"></div>
                    </div>
                    <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex align-items-end justify-content-between">
                                <h3 class="card-title">{{ __('Upload') }}</h3>
                                <h4 class="m-0 p-0 text-muted">{{ __('Credits') }}: <span
                                        id="credits">{{ $credits >= 999 ? 'Unlimited' : $credits }}</span></h4>
                            </div>
                            <div class="card-body">
                                <label for="image" class="vcard-uploader">
                                    <h4 class="text-muted">{{ __('Drop or click to upload') }}</h4>
                                    <p class="text-muted small">( {{ __('Max') }} {{ $uploadSizeMB }}{{ __('MB') }} )</p>
                                </label>
                                <input type="file" id="image" class="d-none" accept="image/png,image/jpeg">
                            </div>
                        </div>
                        <p style="color:#b4b4b4" class="small text-center mt-2">
                            {{ __('AI may occasionally make mistakes. You can edit the business card after it is generated.') }}
                        </p>
                    </div>

                    <div class="col-12 col-sm-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Instructions') }}</h3>
                            </div>
                            <div class="card-body">
                                <ol class="list list-numbered fs-4 lh-lg mb-0 space-y-3">
                                    <li>{{ __('Place the business card on a flat surface before taking the photo.') }}</li>

                                    <li>{{ __('Ensure good lighting so all text is clearly visible.') }}</li>

                                    <li>{{ __('Avoid shadows, glare, reflections, or blurred images.') }}</li>

                                    <li>{{ __('Do not upload damaged, folded, or low-quality card photos.') }}</li>

                                    <li>{{ __('Upload only one business card per image.') }}</li>

                                    <li>{{ __('Use a clear, high-resolution photo for better results.') }}</li>

                                    <li>{{ __('Avoid tilted photos. Keep the card straight and aligned.') }}</li>

                                    <li>{{ __('Make sure important details like phone number, email, and address are readable.') }}
                                </ol>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary btn-lg"
                                        onclick="openCardModel()">{{ __('Example Card') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FULLSCREEN LOADER -->
        <div id="ai-loading-screen">
            <div class="liquid-bg">
                <div class="blob blob-1"></div>
                <div class="blob blob-2"></div>
                <div class="blob blob-3"></div>
            </div>
            <div class="loader-center">
                <div class="status-text" id="status">{{ __('Thinking...') }}</div>
                <div class="loading-bar-mini"></div>
            </div>
        </div>
    </div>

    <!-- QR MODAL -->
    <div class="modal modal-blur fade" id="openQR" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    {{-- Title --}}
                    <h3 style="margin-top: 16px;">{{ __('Scan QR') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 position-relative">
                    {{-- QR Code --}}
                    <canvas id="qrCanvas" class="h-100 w-100"></canvas>
                    {{-- Link --}}
                    <a id="card_url" href="#" target="_blank"
                        class="btn btn-primary mt-2 d-inline-flex align-items-center justify-content-center w-100">
                        <span class="d-flex align-items-center">
                            {{ __('View') }}
                            <svg class="ms-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                <path d="M11 13l9 -9" />
                                <path d="M15 4h5v5" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Example Card Modal --}}
    <div class="modal fade" id="exampleCard" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">{{ __('Example Card') }}</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <image src="{{ asset('img/example-card.png') }}" alt="3" class="img-fluid w-100">
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script src="{{ url('js/qrious.min.js') }}"></script>
    <script src="{{ asset('js/confetti.browser.min.js') }}"></script>

    <script>
        $(function() {

            // Loading phrases
            const phrases = [
                "{{ __('Thinking...') }}",
                "{{ __('Analyzing...') }}",
                "{{ __('Generating...') }}",
                "{{ __('Almost ready...') }}"
            ];

            const errorAlert = $('#errorAlert');
            const errorMessage = $('#errorMessage');
            const credits = $('#credits');

            let timer,
                index = 0,
                allowStop = false,
                isFinished = false,
                generatedUrl = null,
                errorText = null;

            // START LOADER
            function startProcessing() {
                $('#ai-loading-screen').addClass('show');

                index = 0;
                allowStop = false;
                isFinished = false;
                generatedUrl = null;
                errorText = null;

                $('#status').text(phrases[0]);

                timer = setInterval(() => {

                    index++;

                    if (index >= phrases.length) {
                        index = phrases.length - 1;
                        allowStop = true;

                        if (isFinished) stopProcessingNow();
                    }

                    $('#status').fadeOut(200, function() {
                        $(this).text(phrases[index]).fadeIn(200);
                    });

                }, 2500);
            }

            // REQUEST FINISHED
            function stopProcessing() {
                isFinished = true;
                if (allowStop) stopProcessingNow();
            }

            // HIDE LOADER
            function stopProcessingNow() {
                clearInterval(timer);
                $('#ai-loading-screen').removeClass('show');

                setTimeout(() => {

                    if (generatedUrl) {
                        showQR(generatedUrl);
                    }

                    if (errorText) {
                        errorAlert.removeClass('d-none');
                        errorMessage.text(errorText);
                    }

                }, 800);
            }

            // SHOW QR
            function showQR(url) {

                new QRious({
                    element: document.getElementById('qrCanvas'),
                    value: url,
                    size: 250
                });

                $('#card_url').attr('href', url);
                $('#openQR').modal('show');

                setTimeout(() => {

                    const modal = document.querySelector('#openQR .modal-content');

                    confetti({
                        particleCount: 250,
                        spread: 120,
                        origin: {
                            y: 0.6
                        },
                        zIndex: 9999
                    });

                    document.querySelectorAll('canvas.confetti-canvas').forEach(c => {
                        c.classList.add('modal-confetti-canvas');
                        modal.appendChild(c);
                    });

                }, 300);
            }

            // FILE UPLOAD
            $('#image').on('change', function() {
                errorAlert.addClass('d-none');
                errorMessage.text('');

                if (!this.files.length) return;

                startProcessing();

                let formData = new FormData();
                formData.append('file', this.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('user.aibuilder.generate') }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(res) {
                        if (!res.success) {
                            errorText = res.message || 'Something went wrong';
                        } else {
                            generatedUrl = res.message;

                            // Decrease credits
                            const creditsVal = `{{ $credits }}`;

                            if (creditsVal < 999) {
                                credits.text(creditsVal - 1);
                            }
                        }

                        stopProcessing();
                    },

                    error: function(res) {
                        errorText = res.responseJSON?.message || 'Something went wrong';
                        stopProcessing();
                    }
                });

                // Clear the file input
                $(this).val('');
            });
        });

        // OPEN EXAMPLE CARD
        function openCardModel() {
            $('#exampleCard').modal('show');
        }
    </script>
@endsection
