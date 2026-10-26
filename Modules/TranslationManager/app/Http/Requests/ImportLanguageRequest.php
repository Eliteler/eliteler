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

namespace Modules\TranslationManager\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'regex:/^[a-z]{2,3}(_[A-Za-z]{2,4})?$/'],
            'zip_file' => 'required|file|mimes:zip|max:10240',
        ];
    }
}
