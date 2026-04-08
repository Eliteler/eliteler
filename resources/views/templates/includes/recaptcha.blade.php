@php
    use App\Setting;

    $settings = Setting::first();

    $recaptcha_configuration = [
        'RECAPTCHA_ENABLE' => env('RECAPTCHA_ENABLE', ''),
        'RECAPTCHA_SITE_KEY' => env('RECAPTCHA_SITE_KEY', ''),
        'RECAPTCHA_SECRET_KEY' => env('RECAPTCHA_SECRET_KEY', '')
    ];

    $settings['recaptcha_configuration'] = $recaptcha_configuration;

    $recaptchaIds = $recaptchaIds ?? [$recaptchaId ?? 'recaptcha-default'];
@endphp

@if ($settings->recaptcha_configuration['RECAPTCHA_ENABLE'] === 'on')
    @foreach ($recaptchaIds as $id)
        <div class="w-full mb-3" id="{{ $id }}"></div>
        <input type="hidden" name="g-recaptcha-response-{{ $id }}" id="g-recaptcha-response-{{ $id }}">
    @endforeach

    @once
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
        <script>
            window.recaptchaWidgets = window.recaptchaWidgets || {};

            function renderRecaptcha(containerId) {
                const container = document.getElementById(containerId);

                // Prevent re-rendering on the same element
                if (!container || window.recaptchaWidgets[containerId] !== undefined) return;

                try {
                    window.recaptchaWidgets[containerId] = grecaptcha.render(containerId, {
                        'sitekey': '{{ env("RECAPTCHA_SITE_KEY") }}'
                    });
                } catch (e) {
                    console.warn('reCAPTCHA render error:', e.message);
                }
            }

            function onloadCallback() {
                document.querySelectorAll('[id^="recaptcha-"]').forEach(function (el) {
                    renderRecaptcha(el.id);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('form').forEach(function (form) {
                    form.addEventListener('submit', function (e) {
                        let allValid = true;

                        form.querySelectorAll('[id^="recaptcha-"]').forEach(function (el) {
                            const widgetId = window.recaptchaWidgets[el.id];
                            const response = widgetId !== undefined ? grecaptcha.getResponse(widgetId) : '';

                            const hiddenInput = form.querySelector('#g-recaptcha-response-' + el.id);
                            if (hiddenInput) hiddenInput.value = response;

                            if (!response) {
                                allValid = false;
                            }
                        });

                        if (!allValid) {
                            e.preventDefault();
                            alert('Please complete all reCAPTCHA fields.');
                        }
                    });
                });
            });

            // Use this for dynamically loaded content (AJAX)
            window.renderNewRecaptcha = function (containerId) {
                renderRecaptcha(containerId);
            };
        </script>
    @endonce
@endif
