@extends('admin.layouts.index', ['header' => true, 'nav' => true, 'demo' => true])

{{-- Custom CSS --}}
@section('css')
    {{-- Tiny MCE --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.0.1/tinymce.min.js"
        integrity="sha512-KGtsnWohFUg0oksKq7p7eDgA1Rw2nBfqhGJn463/rGhtUY825dBqGexj8eP04LwfnsSW6dNAHAlOqKJKquHsnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- QrCode --}}
    <script src="{{ url('js/qrious.min.js') }}"></script>

    <style>
        .activation-code {
            font-size: 16px;
            font-weight: bold;
            color: #d9534f;
            margin: 10px 0;
        }

        .qr-code {
            margin-top: 20px;
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 150px;
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <!-- Page title header -->
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
                                <a href="{{ route('admin.designs') }}" class="text-secondary text-decoration-none">
                                    {{ __('NFC Sales') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item" aria-current="page">
                                <a href="{{ route('admin.key.generations') }}" class="text-secondary text-decoration-none">
                                    {{ __('Activation Keys') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="#">{{ __('Greeting Letter') }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col">
                        <h2 class="page-title">
                            {{ __('Greeting Letter') }}
                        </h2>
                        <!-- Help Subtitle -->
                        <div class="text-muted mt-1">
                            {{ __('Edit the greeting letter and use short codes to personalize it.') }}
                        </div>
                    </div>
                    {{-- Print Action --}}
                    <div class="col-auto">
                        {{-- send email --}}
                        <button type="button" class="btn btn-dark btn-icon-only d-inline-flex align-items-center"
                            onclick="sendEmail()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail me-1">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" />
                                <path d="M3 7l9 6l9 -6" />
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Send Email') }}</span>
                        </button>

                        {{-- Print Action --}}
                        <button type="button" class="btn btn-primary btn-icon-only d-inline-flex align-items-center"
                            onclick="window.print();">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon icon-tabler icon-tabler-printer me-1">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2">
                                </path>
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"></path>
                                <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z">
                                </path>
                            </svg>
                            <span class="d-none d-sm-inline">{{ __('Print') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">
            <div class="container-fluid">
                <div class="row g-3">
                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="alert alert-important alert-success alert-dismissible mb-2" role="alert">
                            <div class="d-flex">
                                <div>
                                    {{ Session::get('success') }}
                                </div>
                            </div>
                            <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    {{-- Failed Message --}}
                    @if (session('failed'))
                        <div class="alert alert-important alert-danger alert-dismissible mb-2" role="alert">
                            <div class="d-flex">
                                <div>
                                    {{ Session::get('failed') }}
                                </div>
                            </div>
                            <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    @endif

                    {{-- Preview Greeting Letter Section (Borderless Shadowed Card) --}}
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('Preview Letter') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="card-text">
                                    @php
                                        if (!$keyDetails) {
                                            abort(404, 'Activation key not found.');
                                        }

                                        $replacements = [
                                            ':logo' => asset($settings->site_logo),
                                            '%3Alogo' => asset($settings->site_logo),
                                            ':websitename' => config('app.name'),
                                            ':customername' => $customerName ?? '',
                                            ':activationcode' => $keyDetails->unqiue_key ?? 'N/A',
                                            ':supportemail' => empty($config[17]->config_value)
                                                ? 'N/A'
                                                : $config[17]->config_value,
                                            ':supportphone' => empty($config[18]->config_value)
                                                ? 'N/A'
                                                : $config[18]->config_value,
                                        ];

                                        $greetingLetter = str_replace(
                                            array_keys($replacements),
                                            array_values($replacements),
                                            $config[74]->config_value,
                                        );

                                        // Split the rendered HTML around the QR marker so the QR div
                                        // renders inline, at the correct position, but is never part
                                        // of the actual stored/editable HTML that TinyMCE touches.
                                        $parts = explode(':qrcode', $greetingLetter, 2);
                                        $beforeQr = $parts[0] ?? $greetingLetter;
                                        $afterQr = $parts[1] ?? '';
                                    @endphp

                                    {{-- Content before the QR position --}}
                                    {!! $beforeQr !!}

                                    {{-- QR code container — rendered here, never stored/editable --}}
                                    <div class="qr-code">
                                        <h3>{{ __('Scan to Activate') }}</h3>
                                        <div class="d-flex flex-wrap gap-3" id="activateQrCode"></div>
                                    </div>

                                    {{-- Content after the QR position --}}
                                    {!! $afterQr !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Update Greeting Letter Form (Borderless Shadowed Card) --}}
                    <div class="col-12 col-lg-7 d-print-none">
                        <div class="card border-0 shadow-sm">
                            <form action="{{ route('admin.update.key.greeting.letter', $keyDetails->unqiue_key) }}"
                                method="post">
                                @csrf
                                <div class="card-header">
                                    <h3 class="card-title">{{ __('Update Greeting Letter') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <textarea name="greeting_letter" id="greeting_letter" class="form-control" rows="5">{{ $config[74]->config_value ?? $template }}</textarea>
                                    </div>
                                    {{-- Available Short Codes --}}
                                    <div class="table-responsive mt-4">
                                        <h3 class="card-title mb-3">{{ __('Available Short Codes') }}</h3>

                                        <table class="table table-bordered table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="20%">{{ __('Short Code') }}</th>
                                                    <th width="30%">{{ __('Value') }}</th>
                                                    <th width="20%">{{ __('Short Code') }}</th>
                                                    <th width="30%">{{ __('Value') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code>:logo</code></td>
                                                    <td class="fw-semibold">{{ __('Logo') }}</td>
                                                    <td><code>:websitename</code></td>
                                                    <td class="fw-semibold">{{ __('Website Name') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><code>:customername</code></td>
                                                    <td class="fw-semibold">{{ __('Customer Name') }}</td>
                                                    <td><code>:activationcode</code></td>
                                                    <td class="fw-semibold">{{ __('Activation Code') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><code>:supportemail</code></td>
                                                    <td class="fw-semibold">{{ __('Support Email') }}</td>
                                                    <td><code>:supportphone</code></td>
                                                    <td class="fw-semibold">{{ __('Support Phone') }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center">
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

    {{-- Copy clipboard modal indicator --}}
    <div class="modal modal-bg fade" id="copy-clipboard" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="icon mb-2 text-green icon-lg">
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                        <path d="M9 12l2 2l4 -4"></path>
                    </svg>
                    <h3 id="status_id"></h3>
                </div>
            </div>
        </div>
    </div>

    {{-- NFC Modal --}}
    <div class="modal modal-bg fade" id="sendEmail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">

            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{{ __('Send Email') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.send.greeting.letter', $keyDetails->unqiue_key) }}" method="post">
                        @csrf
                        {{-- Mail Send To --}}
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Send To') }}</label>
                            <select class="form-select tom-select" name="email_send_to" id="email_send_to" required>
                                <option value="new_customer" selected>{{ __('New Customer') }}</option>
                                <option value="existing_customer">{{ __('Existing Customer') }}</option>
                            </select>
                        </div>

                        {{-- Customer Select --}}
                        <div class="mb-3 d-none">
                            <label class="form-label required">{{ __('Customer') }}</label>
                            <select class="form-select tom-select" name="customer" id="customer" required>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->email }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Email') }}</label>
                            <input type="email" class="form-control" name="email" id="email"
                                placeholder="Ex: example@example.com" required>
                        </div>

                        <input type="hidden" name="qr_code" id="qr_code">

                        {{-- Send Email --}}
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                {{ __('Send') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Custom JS --}}
@section('scripts')
    <script type="text/javascript" src="{{ asset('js/tom-select.base.min.js') }}"></script>

    <script>
        // Dark Mode
        const isDark = document.body.getAttribute('data-bs-theme') === 'dark';

        // Array of element IDs to initialize with TomSelect
        var elementSelectors = ['email_send_to', 'customer'];

        let qrBase64 = '';

        // HTML Editor
        tinymce.init({
            selector: '#greeting_letter',
            plugins: 'code preview importcss searchreplace autolink autosave save directionality visualblocks visualchars link charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount charmap quickbars emoticons',
            menubar: 'file edit view insert format tools',
            toolbar: 'code undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl',
            content_style: 'body { font-family:"Inter Var",Inter,-apple-system,BlinkMacSystemFont,San Francisco,Segoe UI,Roboto,Helvetica Neue,sans-serif; font-size:16px }',
            menubar: false,
            statusbar: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'dark'
            ] : [
                'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap',
                'default'
            ],
            setup: function(editor) {
                editor.on('init', function() {
                    if (document.body.dataset.bsTheme === 'dark') {
                        editor.getBody().setAttribute('data-bs-theme', 'dark');
                    }
                });
            },
            content_style: isDark ? `
            body {
                    background-color: #151f2c !important;
                    font-family: "Figtree", sans-serif !important;
                }

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    color: rgba(34,47,62,.5) !important;
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            ` : `body {
                    font-family: "Figtree", sans-serif !important;
                }

                .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before {
                    opacity: 1 !important;
                    font-family: "Figtree", sans-serif !important;
                    font-size: 0.85rem !important;
                    font-weight: 600 !important;
                }
            `,
            height: 450,

            // Correct mobile settings
            mobile: {
                toolbar: 'code undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | preview save print | insertfile link anchor | ltr rtl'
            }
        });

        // Activate QR Code
        document.addEventListener("DOMContentLoaded", function() {
            "use strict";

            let uniqueKeys = @json($keyDetails->unqiue_key ?? []);
            // Convert single value to array
            if (!Array.isArray(uniqueKeys)) {
                uniqueKeys = [uniqueKeys];
            }

            const qrContainer = document.getElementById('activateQrCode');
            if (!qrContainer) {
                return;
            }

            uniqueKeys.forEach(function(key) {
                const canvas = document.createElement('canvas');
                const emailCanvas = document.createElement('canvas');
                qrContainer.appendChild(canvas);

                new QRious({
                    element: canvas,
                    value: "{{ route('user.activate.nfc.card') }}?id=" + key,
                    size: 150,
                    background: 'white',
                    foreground: 'black',
                    level: 'H'
                });

                new QRious({
                    element: emailCanvas,
                    value: `{{ route('read.nfc.card', $keyDetails->unqiue_key) }}`,
                    size: 150,
                    background: 'white',
                    foreground: 'black',
                    level: 'H'
                });

                $('#qr_code').val(emailCanvas.toDataURL('image/png'));
            });
        });

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
            var el = document.getElementById(id);
            if (el) {
                initializeTomSelectWithRequired(el);
            }
        });

        // send email
        function sendEmail() {
            const modal = new bootstrap.Modal(document.getElementById('sendEmail'));
            modal.show();
        }

        $(function() {

            function toggleCustomerFields() {
                const sendTo = $('#email_send_to')[0].tomselect.getValue();

                // Reset email whenever Send To changes
                $('#email').val('');

                if (sendTo === 'existing_customer') {
                    $('#customer').closest('.mb-3').removeClass('d-none');

                    const customerEmail = $('#customer')[0].tomselect.getValue();

                    $('#email')
                        .prop('readonly', true)
                        .val(customerEmail);
                } else {
                    $('#customer').closest('.mb-3').addClass('d-none');

                    $('#email')
                        .prop('readonly', false)
                        .val('');
                }
            }

            // Send To changed
            $('#email_send_to')[0].tomselect.on('change', function() {
                toggleCustomerFields();
            });

            // Customer changed
            $('#customer')[0].tomselect.on('change', function(value) {
                if ($('#email_send_to')[0].tomselect.getValue() === 'existing_customer') {
                    $('#email').val(value);
                }
            });

            // Initial load
            toggleCustomerFields();

        });
    </script>
@endsection
