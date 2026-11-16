<?php

namespace App\Support;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use RuntimeException;

/**
 * Admin "Sign in as" a customer or staff member, with a way back.
 *
 * The session remembers who the admin was (key "impersonator"); the panel shows a
 * banner with "Back to admin", and logging out while impersonating returns to the
 * admin account instead of ending the session. One web guard means one signed-in
 * user per browser: switching applies to every tab until the admin switches back.
 */
class Impersonation
{
    public const SESSION_KEY = 'impersonator';

    /** Roles that can return to the admin panel: admin, staff */
    private const ADMIN_ROLES = [1, 3, 4];

    /** @return array{id: int, name: string, return: string}|null */
    public static function active(): ?array
    {
        $impersonator = session(self::SESSION_KEY);

        return is_array($impersonator) && isset($impersonator['id']) ? $impersonator : null;
    }

    /** What the banners need, or null when nobody is being impersonated. */
    public static function banner(): ?array
    {
        $impersonator = self::active();
        if (! $impersonator || ! Auth::check()) {
            return null;
        }

        return [
            'name'     => Auth::user()->name,
            'admin'    => $impersonator['name'],
            'leaveUrl' => route('impersonate.leave'),
        ];
    }

    /** Sign in as $target, remembering the current admin and where to return. */
    public static function start(Request $request, User $target, string $returnUrl): void
    {
        $admin = $request->user();

        if (self::active()) {
            throw new RuntimeException(__('Go back to your admin account before signing in as someone else.'));
        }
        if ((int) $admin->id === (int) $target->id) {
            throw new RuntimeException(__('You are already signed in as this account.'));
        }

        // New session id against fixation; the data (including the admin's passed 2FA check) is kept
        Auth::login($target);
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, [
            'id'     => $admin->id,
            'name'   => $admin->name,
            'return' => $returnUrl,
        ]);

        Log::info('Impersonation started', ['admin' => $admin->user_id, 'as' => $target->user_id]);
    }

    /** Switch back to the admin account. Returns the URL to go to next. */
    public static function leave(Request $request): string
    {
        $impersonator = self::active();
        $request->session()->forget(self::SESSION_KEY);

        $admin = $impersonator ? User::whereKey($impersonator['id'])->whereIn('role_id', self::ADMIN_ROLES)->where('status', 1)->first() : null;
        $from = optional($request->user())->user_id;

        // Admin account removed or deactivated meanwhile: end the session completely
        if (! $admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return url('/login');
        }

        Auth::login($admin);
        $request->session()->regenerate();

        Log::info('Impersonation ended', ['admin' => $admin->user_id, 'as' => $from]);

        return $impersonator['return'] ?: route('admin.dashboard');
    }

    /** Admin and customer panels are separate Inertia apps: switching needs a full page load. */
    public static function redirect(Request $request, string $url)
    {
        return $request->header('X-Inertia') ? Inertia::location($url) : redirect($url);
    }
}
