<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicThemeController extends Controller
{
    public function publicActivateTheme(Request $request, $theme_id)
    {
        // Allow only valid themes
        if (!in_array($theme_id, ['GoBizOriginal', 'GoBizModern'])) {
            abort(404);
        }

        // Update config
        DB::table('config')
            ->where('config_key', 'web_template')
            ->update(['config_value' => $theme_id]);

        // Redirect to home
        return redirect('/');
    }
}
