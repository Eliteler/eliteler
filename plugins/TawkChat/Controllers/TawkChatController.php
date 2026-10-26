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

namespace Plugins\TawkChat\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tawk.to live chat in the customer dashboard (settings.tawk_chat_bot_key =
 * "propertyId/widgetId" from the embed code). Blank turns the chat off.
 * Page: plugins/TawkChat/resources/js/settings.tsx.
 */
class TawkChatController extends Controller
{
    public function tawkChatSettings()
    {
        return PluginPage::render('TawkChat', 'settings', [
            'tawk_chat_bot_key' => (string) DB::table('settings')->where('id', 1)->value('tawk_chat_bot_key'),
        ]);
    }

    public function tawkChatSettingsUpdate(Request $request)
    {
        // Accept the whole embed link too, e.g. https://embed.tawk.to/abc123/1h2j3k
        $key = trim((string) $request->input('tawk_chat_bot_key'));
        $key = trim(preg_replace('#^https?://embed\.tawk\.to/#i', '', $key), '/');
        $request->merge(['tawk_chat_bot_key' => $key]);

        $data = $request->validate([
            'tawk_chat_bot_key' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9]+\/[A-Za-z0-9]+$/'],
        ], [
            'tawk_chat_bot_key.regex' => __('Use the part after embed.tawk.to/ in your widget code, like 64f1a2b3c4d5e6/1h2j3k4l5.'),
        ]);

        DB::table('settings')->where('id', 1)->update(['tawk_chat_bot_key' => $data['tawk_chat_bot_key'] ?: null]);

        return back()->with('success', filled($data['tawk_chat_bot_key']) ? __('Tawk.to chat is on.') : __('Tawk.to chat is off.'));
    }
}
