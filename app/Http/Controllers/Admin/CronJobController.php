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

namespace App\Http\Controllers\Admin;

use App\EmailTemplate;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

/**
 * Cron jobs: the daily run hour (config "cron_hour") and the plan expiry
 * reminder days (config "cronjob_dates_in_array", e.g. [7,3,1,-1]).
 * Positive days are before the plan expires, negative days after.
 */
class CronJobController extends Controller
{
    private const EXPIRED_TEMPLATE  = '584922675206';
    private const EXPIRING_TEMPLATE = '584922675207';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $config = DB::table('config')->whereIn('config_key', ['cron_hour', 'cronjob_dates_in_array'])->pluck('config_value', 'config_key');

        return Inertia::render('settings/cron-jobs', [
            'cronHour'     => (int) ($config['cron_hour'] ?? 0),
            'reminderDays' => self::parseDays($config['cronjob_dates_in_array'] ?? ''),
            'artisan'      => str_replace('\\', '/', base_path('artisan')),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'reminder_days'   => 'required|array|min:1|max:20',
            'reminder_days.*' => 'integer|between:-30,366|distinct',
            'cron_hour'       => 'required|integer|between:0,23',
        ], [
            'reminder_days.*.between' => __('Reminders can be up to 366 days before expiry or 30 days after.'),
            'reminder_days.*.distinct' => __('Each reminder day can only be added once.'),
        ], [
            'reminder_days' => __('reminder days'),
            'cron_hour'     => __('run time'),
        ]);

        $days = collect($data['reminder_days'])->map(fn ($day) => (int) $day)->sortDesc()->values()->all();

        DB::table('config')->where('config_key', 'cronjob_dates_in_array')->update(['config_value' => json_encode($days)]);
        DB::table('config')->where('config_key', 'cron_hour')->update(['config_value' => (string) $data['cron_hour']]);

        return back()->with('success', __('Cron settings saved.'));
    }

    // Kept for old forms; the page saves the hour together with the reminder days
    public function setCronjobTime(Request $request)
    {
        $data = $request->validate(['cron_hour' => 'required|integer|between:0,23']);
        DB::table('config')->where('config_key', 'cron_hour')->update(['config_value' => (string) $data['cron_hour']]);

        return back()->with('success', __('Cron settings saved.'));
    }

    /** Send both reminder emails (expiring soon and expired) to the signed-in admin. */
    public function testReminder(Request $request)
    {
        $user      = $request->user();
        $templates = EmailTemplate::whereIn('email_template_id', [self::EXPIRING_TEMPLATE, self::EXPIRED_TEMPLATE])->get()->keyBy('email_template_id');

        if ($templates->count() < 2) {
            return back()->with('failed', __('The plan reminder email templates are missing.'));
        }

        $details = fn (string $template, Carbon $expiry) => [
            'status'          => '',
            'emailSubject'    => $templates[$template]->email_template_subject,
            'emailContent'    => $templates[$template]->email_template_content,
            'registeredName'  => __('Test User') . ' - ' . $user->name,
            'registeredEmail' => $user->email,
            'expiryDate'      => $expiry->format('Y-m-d'),
            'planCode'        => '123456789',
            'planName'        => __('Test Plan'),
            'planPrice'       => 100,
        ];

        try {
            Mail::to($user->email)->send(new \App\Mail\AppointmentMail($details(self::EXPIRING_TEMPLATE, now()->addDays(3))));
            Mail::to($user->email)->send(new \App\Mail\AppointmentMail($details(self::EXPIRED_TEMPLATE, now()->subDays(3))));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('Couldn’t send the test emails. Check your email settings.'));
        }

        return back()->with('success', __('Two test reminders were sent to :email.', ['email' => $user->email]));
    }

    public function runCronjob()
    {
        try {
            Artisan::call('expiry:cron');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('The reminder job failed. Check the log for details.'));
        }

        return back()->with('success', __('Reminder job finished.'));
    }

    /** "[10,5,3]" or "10, 5, 3" → [10, 5, 3] */
    public static function parseDays(?string $value): array
    {
        $decoded = json_decode((string) $value, true);

        if (! is_array($decoded)) {
            $decoded = preg_split('/[\s,]+/', trim((string) $value, " []"), -1, PREG_SPLIT_NO_EMPTY);
        }

        return collect($decoded)->filter(fn ($day) => is_numeric($day))->map(fn ($day) => (int) $day)->unique()->values()->all();
    }
}
