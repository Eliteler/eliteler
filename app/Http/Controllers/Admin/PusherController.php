<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Pusher Beams settings. Stored in .env (PUSHER_BEAMS_*), which the website
 * templates read to show the subscribe prompt.
 */
class PusherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function configured(): bool
    {
        return ! empty(env('PUSHER_BEAMS_INSTANCE_ID')) && ! empty(env('PUSHER_BEAMS_SECRET_KEY'));
    }

    public function index()
    {
        return Inertia::render('marketing/push-settings', [
            'settings' => [
                'enabled'    => env('PUSHER_BEAMS_ENABLED') === '1',
                'instanceId' => (string) env('PUSHER_BEAMS_INSTANCE_ID', ''),
                // The secret key is never sent to the browser
                'hasSecret'  => ! empty(env('PUSHER_BEAMS_SECRET_KEY')),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enable_beams'      => 'required|boolean',
            'beams_instance_id' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9-]+$/'],
            'beams_secret_key'  => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9-]+$/'],
        ], ['regex' => __('Use only letters, numbers and dashes.')], [
            'beams_instance_id' => __('instance ID'),
            'beams_secret_key'  => __('secret key'),
        ]);

        if (empty($data['beams_secret_key']) && empty(env('PUSHER_BEAMS_SECRET_KEY'))) {
            return back()->withErrors(['beams_secret_key' => __('Enter the secret key.')]);
        }

        $this->updateEnvFile('PUSHER_BEAMS_ENABLED', $request->boolean('enable_beams') ? '1' : '0');
        $this->updateEnvFile('PUSHER_BEAMS_INSTANCE_ID', $data['beams_instance_id']);
        if (! empty($data['beams_secret_key'])) {
            $this->updateEnvFile('PUSHER_BEAMS_SECRET_KEY', $data['beams_secret_key']);
            }

        return back()->with('success', __('Web notification settings saved.'));
            }

    /** Write KEY="value" to .env (values are validated: no quotes or line breaks). */
    public function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
            }

        $value   = '"' . str_replace(['"', "\r", "\n"], '', $value) . '"';
        $content = file_get_contents($envPath);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        $content = preg_match($pattern, $content)
            ? preg_replace($pattern, $key . '=' . str_replace(['\\', '$'], ['\\\\', '\$'], $value), $content)
            : rtrim($content, "\r\n") . PHP_EOL . "{$key}={$value}" . PHP_EOL;

        file_put_contents($envPath, $content);

        $plain = trim($value, '"');
        putenv("{$key}={$plain}");
        $_ENV[$key] = $_SERVER[$key] = $plain;
    }
}
