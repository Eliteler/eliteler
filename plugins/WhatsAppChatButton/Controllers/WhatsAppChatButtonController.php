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

namespace Plugins\WhatsAppChatButton\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Floating WhatsApp chat button (config show_whatsapp_chatbot,
 * show_whatsapp_chatbot_on_customer_panel, whatsapp_chatbot_mobile_number,
 * whatsapp_chatbot_message). Page: plugins/WhatsAppChatButton/resources/js/settings.tsx.
 */
class WhatsAppChatButtonController extends Controller
{
    private const KEYS = ['show_whatsapp_chatbot', 'show_whatsapp_chatbot_on_customer_panel', 'whatsapp_chatbot_mobile_number', 'whatsapp_chatbot_message'];

    public function whatsAppChatButtonSettings()
    {
        $config = DB::table('config')->whereIn('config_key', self::KEYS)->pluck('config_value', 'config_key');

        return PluginPage::render('WhatsAppChatButton', 'settings', [
            'values' => [
                'show_whatsapp_chatbot'                   => (string) ($config['show_whatsapp_chatbot'] ?? '0') === '1',
                'show_whatsapp_chatbot_on_customer_panel' => (string) ($config['show_whatsapp_chatbot_on_customer_panel'] ?? '0') === '1',
                'whatsapp_chatbot_mobile_number'          => (string) ($config['whatsapp_chatbot_mobile_number'] ?? ''),
                'whatsapp_chatbot_message'                => (string) ($config['whatsapp_chatbot_message'] ?? ''),
            ],
        ]);
    }

    public function whatsAppChatButtonSettingsUpdate(Request $request)
    {
        // wa.me links need digits only, with the country code
        $request->merge(['whatsapp_chatbot_mobile_number' => preg_replace('/[^0-9]/', '', (string) $request->input('whatsapp_chatbot_mobile_number'))]);
        $shown = $request->boolean('show_whatsapp_chatbot') || $request->boolean('show_whatsapp_chatbot_on_customer_panel');

        $data = $request->validate([
            'show_whatsapp_chatbot'                   => 'required|boolean',
            'show_whatsapp_chatbot_on_customer_panel' => 'required|boolean',
            'whatsapp_chatbot_mobile_number'          => [$shown ? 'required' : 'nullable', 'digits_between:8,15'],
            'whatsapp_chatbot_message'                => 'nullable|string|max:500',
        ], [
            'whatsapp_chatbot_mobile_number.digits_between' => __('Use the full number with country code, like 919876543210.'),
        ], [
            'whatsapp_chatbot_mobile_number' => __('WhatsApp number'),
            'whatsapp_chatbot_message'       => __('message'),
        ]);

        foreach ([
            'show_whatsapp_chatbot'                   => $request->boolean('show_whatsapp_chatbot') ? '1' : '0',
            'show_whatsapp_chatbot_on_customer_panel' => $request->boolean('show_whatsapp_chatbot_on_customer_panel') ? '1' : '0',
            'whatsapp_chatbot_mobile_number'          => (string) ($data['whatsapp_chatbot_mobile_number'] ?? ''),
            'whatsapp_chatbot_message'                => trim((string) ($data['whatsapp_chatbot_message'] ?? '')),
        ] as $key => $value) {
            DB::table('config')->where('config_key', $key)->update(['config_value' => $value]);
        }

        return back()->with('success', __('WhatsApp chat button settings saved.'));
    }
}
