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

/**
 * Floating WhatsApp chat button (config show_whatsapp_chatbot,
 * show_whatsapp_chatbot_on_customer_panel, whatsapp_chatbot_mobile_number,
 * whatsapp_chatbot_message).
 *
 * Eliteler customization: up to 6 WhatsApp contacts (name / number / message),
 * stored as whatsapp_chatbot_name_{i}, whatsapp_chatbot_mobile_number_{i} and
 * whatsapp_chatbot_message_{i} (contact 1 uses the original vendor keys).
 * The settings page is the Blade view (plugins/WhatsAppChatButton/Views/index.blade.php)
 * because the vendor React page (resources/js/settings.tsx) only supports one contact.
 */
class WhatsAppChatButtonController extends Controller
{
    private const CONTACTS = 6;

    public function whatsAppChatButtonSettings()
    {
        $config = DB::table('config')->pluck('config_value', 'config_key')->toArray();

        $settings = Setting::where('id', 1)->first();

        return view()->file(base_path('plugins/WhatsAppChatButton/Views/index.blade.php'), compact('config', 'settings'));
    }

    public function whatsAppChatButtonSettingsUpdate(Request $request)
    {
        // wa.me links need digits only, with the country code
        $numbers = [];
        for ($i = 1; $i <= self::CONTACTS; $i++) {
            $numKey           = $this->numberKey($i);
            $numbers[$numKey] = preg_replace('/[^0-9]/', '', (string) $request->input($numKey));
        }
        $request->merge($numbers);

        $shown = $request->boolean('show_whatsapp_chatbot') || $request->boolean('show_whatsapp_chatbot_on_customer_panel');

        $rules = [
            'show_whatsapp_chatbot'                   => 'required|boolean',
            'show_whatsapp_chatbot_on_customer_panel' => 'nullable|boolean',
        ];
        $messages   = [];
        $attributes = [];
        for ($i = 1; $i <= self::CONTACTS; $i++) {
            $numKey  = $this->numberKey($i);
            $msgKey  = $this->messageKey($i);
            $nameKey = "whatsapp_chatbot_name_{$i}";

            $rules[$numKey]  = [($i === 1 && $shown) ? 'required' : 'nullable', 'digits_between:8,15'];
            $rules[$msgKey]  = 'nullable|string|max:500';
            $rules[$nameKey] = 'nullable|string|max:255';

            $messages["{$numKey}.digits_between"] = __('Use the full number with country code, like 919876543210.');
            $attributes[$numKey]                  = __('WhatsApp number') . ($i > 1 ? " {$i}" : '');
            $attributes[$msgKey]                  = __('message') . ($i > 1 ? " {$i}" : '');
        }

        $data = $request->validate($rules, $messages, $attributes);

        $values = [
            'show_whatsapp_chatbot'                   => $request->boolean('show_whatsapp_chatbot') ? '1' : '0',
            'show_whatsapp_chatbot_on_customer_panel' => $request->boolean('show_whatsapp_chatbot_on_customer_panel') ? '1' : '0',
        ];
        for ($i = 1; $i <= self::CONTACTS; $i++) {
            $values[$this->numberKey($i)]         = (string) ($data[$this->numberKey($i)] ?? '');
            $values[$this->messageKey($i)]        = trim((string) ($data[$this->messageKey($i)] ?? ''));
            $values["whatsapp_chatbot_name_{$i}"] = trim((string) ($data["whatsapp_chatbot_name_{$i}"] ?? ''));
        }

        foreach ($values as $key => $value) {
            DB::table('config')->updateOrInsert(['config_key' => $key], ['config_value' => $value]);
        }

        return redirect()->route('admin.plugin.whatsapp_chat_button.settings')->with('success', __('WhatsApp chat button settings saved.'));
    }

    private function numberKey(int $i): string
    {
        return $i === 1 ? 'whatsapp_chatbot_mobile_number' : "whatsapp_chatbot_mobile_number_{$i}";
    }

    private function messageKey(int $i): string
    {
        return $i === 1 ? 'whatsapp_chatbot_message' : "whatsapp_chatbot_message_{$i}";
    }
}
