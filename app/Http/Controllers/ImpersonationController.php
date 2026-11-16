<?php

namespace App\Http\Controllers;

use App\Support\Impersonation;
use Illuminate\Http\Request;

/** "Back to admin" from the impersonation banner. */
class ImpersonationController extends Controller
{
    public function leave(Request $request)
    {
        if (! Impersonation::active()) {
            return redirect('/');
        }

        return Impersonation::redirect($request, Impersonation::leave($request));
    }
}
