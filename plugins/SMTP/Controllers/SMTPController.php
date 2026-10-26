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

namespace Plugins\SMTP\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Outgoing email (MAIL_* in .env) and the customer email verification switch
 * (config disable_user_email_verification: despite the name, "1" = verification required).
 * Page: plugins/SMTP/resources/js/settings.tsx.
 */
class SMTPController extends Controller
{
    private const DRIVERS = ['smtp' => 'SMTP', 'sendmail' => 'Sendmail (server)', 'log' => 'Log only (no emails sent)'];

    private const ENCRYPTIONS = ['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', 'null' => 'None'];

    public function smtpSettings()
    {
        $driver = (string) env('MAIL_MAILER', 'smtp');

        return PluginPage::render('SMTP', 'settings', [
            'values'        => [
                'mail_sender'          => (string) env('MAIL_FROM_NAME', config('app.name')),
                'mail_address'         => (string) env('MAIL_FROM_ADDRESS', ''),
                'mail_driver'          => $driver,
                'mail_host'            => (string) env('MAIL_HOST', ''),
                'mail_port'            => (string) env('MAIL_PORT', '587'),
                'mail_encryption'      => (string) (env('MAIL_ENCRYPTION') ?: 'null'),
                'mail_username'        => (string) env('MAIL_USERNAME', ''),
                'require_verification' => (string) DB::table('config')->where('config_key', 'disable_user_email_verification')->value('config_value') === '1',
            ],
            // The password never leaves the server
            'savedPassword' => filled(env('MAIL_PASSWORD')),
            'drivers'       => collect(self::DRIVERS + [$driver => $driver])->map(fn($label, $value) => ['value' => (string) $value, 'label' => __($label)])->values(),
            'encryptions'   => collect(self::ENCRYPTIONS)->map(fn($label, $value) => ['value' => (string) $value, 'label' => __($label)])->values(),
            'adminEmail'    => Auth::user()->email,
        ]);
    }

    public function smtpSettingsUpdate(Request $request)
    {
        $smtp = $request->input('mail_driver') === 'smtp';

        $data = $request->validate([
            'mail_sender'          => 'required|string|max:100',
            'mail_address'         => 'required|email|max:191',
            'mail_driver'          => ['required', 'string', 'max:30', 'regex:/^[a-z]+$/'],
            'mail_host'            => [$smtp ? 'required' : 'nullable', 'string', 'max:191'],
            'mail_port'            => [$smtp ? 'required' : 'nullable', 'integer', 'between:1,65535'],
            'mail_encryption'      => ['required', Rule::in(array_keys(self::ENCRYPTIONS))],
            'mail_username'        => 'nullable|string|max:191',
            'mail_password'        => 'nullable|string|max:191',
            'require_verification' => 'required|boolean',
        ], [], [
            'mail_sender'   => __('sender name'),
            'mail_address'  => __('sender email'),
            'mail_driver'   => __('mailer'),
            'mail_host'     => __('host'),
            'mail_port'     => __('port'),
            'mail_encryption' => __('encryption'),
            'mail_username' => __('username'),
            'mail_password' => __('password'),
        ]);

        // .env values can't contain quotes
        $clean = fn($value) => str_replace(['"', "'"], '', trim((string) $value));

        $this->updateEnvFile('MAIL_MAILER', $clean($data['mail_driver']));
        $this->updateEnvFile('MAIL_HOST', $clean($data['mail_host'] ?? ''));
        $this->updateEnvFile('MAIL_PORT', $clean($data['mail_port'] ?? ''));
        $this->updateEnvFile('MAIL_USERNAME', '"' . $clean($data['mail_username'] ?? '') . '"');
        // A blank password keeps the saved one
        if (filled($data['mail_password'] ?? null)) {
            $this->updateEnvFile('MAIL_PASSWORD', '"' . $clean($data['mail_password']) . '"');
        }
        $this->updateEnvFile('MAIL_ENCRYPTION', $data['mail_encryption']);
        $this->updateEnvFile('MAIL_FROM_ADDRESS', $clean($data['mail_address']));
        $this->updateEnvFile('MAIL_FROM_NAME', '"' . $clean($data['mail_sender']) . '"');

        DB::table('config')->where('config_key', 'disable_user_email_verification')->update([
            'config_value' => $request->boolean('require_verification') ? '1' : '0',
        ]);

        return back()->with('success', __('Email settings saved.'));
    }

    /** Send a test email with the saved settings to the signed-in admin. */
    public function testEmail(Request $request)
    {
        $to = Auth::user()->email;

        try {
            Mail::to($to)->send(new \App\Mail\TestMail(['msg' => 'Test mail']));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('Couldn’t send the test email: :error', ['error' => $e->getMessage()]));
        }

        return back()->with('success', __('Test email sent to :email.', ['email' => $to]));
    }

    public function updateEnvFile($key, $value)
    {
        $envPath = base_path('.env');

        // Check if the .env file exists
        if (file_exists($envPath)) {

            // Read the .env file
            $contentArray = file($envPath);

            // Loop through each line to find the key and update its value
            foreach ($contentArray as &$line) {

                // Split the line by '=' to get key and value
                $parts = explode('=', $line, 2);

                // Check if the key matches and update its value
                if (isset($parts[0]) && $parts[0] === $key) {
                    $line = $key . '=' . $value . PHP_EOL;
                }
            }

            // Implode the array back to a string and write it to the .env file
            $newContent = implode('', $contentArray);
            file_put_contents($envPath, $newContent);

            // Reload the environment variables
            putenv($key . '=' . $value);
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}
