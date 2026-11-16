<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Lets an administrator open a customer's cards inside the user panel
 * without logging in to the customer's account.
 *
 * When an admin opens a card from Admin > Digital Business Cards, the owner
 * id is stored in the session (admin_card_context). For user-panel requests
 * only, the authenticated user is swapped to the owner for the duration of
 * the request. The admin's own session is never changed.
 */
class AdminCardContext
{
    public function handle($request, Closure $next)
    {
        $context = $request->session()->get('admin_card_context');

        if (is_array($context) && ! empty($context['owner_id']) && Auth::check() && (int) Auth::user()->role_id === 1) {
            $owner = User::where('id', $context['owner_id'])->where('role_id', 2)->where('status', 1)->first();

            if ($owner && (int) $owner->id !== (int) Auth::id()) {
                Auth::guard()->setUser($owner);
            } else {
                $request->session()->forget('admin_card_context');
            }
        }

        return $next($request);
    }
}
