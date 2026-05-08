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

namespace Plugins\WhatsAppChatButton\Controllers;

use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppChatButtonController extends Controller
{
    public function whatsAppChatButtonSettings(Request $request)
    {
        $config = DB::table('config')->pluck('config_value', 'config_key')->toArray();

        $settings = Setting::where('id', 1)->first();

        return view()->file(base_path('plugins/WhatsAppChatButton/Views/index.blade.php'), compact('config', 'settings'));
    }

    public function whatsAppChatButtonSettingsUpdate(Request $request)
    {
        DB::table('config')->where('config_key', 'show_whatsapp_chatbot')->update([
            'config_value' => $request->show_whatsapp_chatbot,
        ]);

        for ($i = 1; $i <= 6; $i++) {
            $numKey = $i == 1 ? 'whatsapp_chatbot_mobile_number' : "whatsapp_chatbot_mobile_number_{$i}";
            $msgKey = $i == 1 ? 'whatsapp_chatbot_message' : "whatsapp_chatbot_message_{$i}";
            $nameKey = "whatsapp_chatbot_name_{$i}";

            DB::table('config')->updateOrInsert(
                ['config_key' => $numKey],
                ['config_value' => $request->input($numKey) ?? '']
            );

            DB::table('config')->updateOrInsert(
                ['config_key' => $msgKey],
                ['config_value' => $request->input($msgKey) ?? '']
            );

            DB::table('config')->updateOrInsert(
                ['config_key' => $nameKey],
                ['config_value' => $request->input($nameKey) ?? '']
            );
        }

        return redirect()->route('admin.plugin.whatsapp_chat_button.settings')->with('success', __('WhatsApp Chat Button Settings updated successfully.'));
    }

}
