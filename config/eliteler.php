<?php

/*
 | Eliteler-specific settings (kept apart from the platform's own config files).
 */
return [
    // Self-registration is closed on this site: /register sends visitors to the application form.
    // Set REGISTER_REDIRECT_URL= (empty) in .env to bring the built-in registration page back.
    'register_url' => env('REGISTER_REDIRECT_URL', 'https://apply.eliteler.net/'),
];
