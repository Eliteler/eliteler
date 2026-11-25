<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Rules;

use App\Services\Security\DisposableEmailService;
use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates that an email address does not belong to a disposable or temporary email provider.
 * Prevents automated registration bot scripts, trial abuse, and mail server reputation degradation.
 */
class NotDisposableEmail implements Rule, ValidationRule
{
    protected ?string $failureMessage = null;

    /**
     * Run the validation rule (Laravel 10+ ValidationRule).
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If EmailSecurity plugin is not installed/purchased, immediately pass
        if (! DisposableEmailService::isPluginActive()) {
            return;
        }

        $result = DisposableEmailService::checkEmail(is_string($value) ? $value : null);

        if (! $result['allowed']) {
            $fail($result['message'] ?? __('Disposable or temporary email addresses are not allowed. Please use a valid email address.'));
        }
    }

    /**
     * Determine if the validation rule passes (Legacy Rule interface).
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // If EmailSecurity plugin is not installed/purchased, immediately pass
        if (! DisposableEmailService::isPluginActive()) {
            return true;
        }

        $result = DisposableEmailService::checkEmail(is_string($value) ? $value : null);

        if (! $result['allowed']) {
            $this->failureMessage = $result['message'];
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->failureMessage ?? __('Disposable or temporary email addresses are not allowed. Please use a valid email address.');
    }
}
