<?php

namespace Plugins\WhatsAppChatButton\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Floating WhatsApp chat button with up to 6 contacts.
 *
 * Config keys: show_whatsapp_chatbot, show_whatsapp_chatbot_on_customer_panel and, per slot i (1..6):
 *   slot 1: whatsapp_chatbot_mobile_number, whatsapp_chatbot_message, whatsapp_chatbot_name_1
 *   slot i: whatsapp_chatbot_mobile_number_{i}, whatsapp_chatbot_message_{i}, whatsapp_chatbot_name_{i}
 * Page: plugins/WhatsAppChatButton/resources/js/settings.tsx.
 */
class WhatsAppChatButtonController extends Controller
{
    public const MAX_CONTACTS = 6;

    /** @return array{name:string,number:string,message:string} */
    public static function slotKeys(int $i): array
    {
        return [
            'name'    => "whatsapp_chatbot_name_{$i}",
            'number'  => $i === 1 ? 'whatsapp_chatbot_mobile_number' : "whatsapp_chatbot_mobile_number_{$i}",
            'message' => $i === 1 ? 'whatsapp_chatbot_message' : "whatsapp_chatbot_message_{$i}",
        ];
    }

    /** Contacts that have a number, in slot order. @return array<int, array{name:string,number:string,message:string}> */
    public static function contacts(?iterable $configRows = null): array
    {
        $config = $configRows === null
            ? DB::table('config')->where('config_key', 'like', 'whatsapp_chatbot_%')->pluck('config_value', 'config_key')
            : collect($configRows)->pluck('config_value', 'config_key');

        $contacts = [];
        for ($i = 1; $i <= self::MAX_CONTACTS; $i++) {
            $keys = self::slotKeys($i);
            $number = preg_replace('/[^0-9]/', '', (string) ($config[$keys['number']] ?? ''));
            if ($number === '') {
                continue;
            }
            $contacts[] = [
                'name'    => (string) ($config[$keys['name']] ?? ''),
                'number'  => $number,
                'message' => (string) ($config[$keys['message']] ?? ''),
            ];
        }

        return $contacts;
    }

    public function whatsAppChatButtonSettings()
    {
        $config = DB::table('config')->where('config_key', 'like', '%whatsapp_chatbot%')->pluck('config_value', 'config_key');

        return PluginPage::render('WhatsAppChatButton', 'settings', [
            'values' => [
                'show_whatsapp_chatbot'                   => (string) ($config['show_whatsapp_chatbot'] ?? '0') === '1',
                'show_whatsapp_chatbot_on_customer_panel' => (string) ($config['show_whatsapp_chatbot_on_customer_panel'] ?? '0') === '1',
                'contacts'                                => self::contacts(),
            ],
        ]);
    }

    public function whatsAppChatButtonSettingsUpdate(Request $request)
    {
        // wa.me links need digits only, with the country code
        $contacts = collect((array) $request->input('contacts', []))
            ->take(self::MAX_CONTACTS)
            ->map(fn ($c) => [
                'name'    => trim((string) ($c['name'] ?? '')),
                'number'  => preg_replace('/[^0-9]/', '', (string) ($c['number'] ?? '')),
                'message' => trim((string) ($c['message'] ?? '')),
            ])
            ->filter(fn ($c) => $c['number'] !== '' || $c['name'] !== '' || $c['message'] !== '')
            ->values()
            ->all();
        $request->merge(['contacts' => $contacts]);

        $shown = $request->boolean('show_whatsapp_chatbot') || $request->boolean('show_whatsapp_chatbot_on_customer_panel');

        $request->validate([
            'show_whatsapp_chatbot'                   => 'required|boolean',
            'show_whatsapp_chatbot_on_customer_panel' => 'required|boolean',
            'contacts'                                => [$shown ? 'required' : 'nullable', 'array', 'max:' . self::MAX_CONTACTS],
            'contacts.*.name'                         => 'nullable|string|max:50',
            'contacts.*.number'                       => ['required', 'digits_between:8,15'],
            'contacts.*.message'                      => 'nullable|string|max:500',
        ], [
            'contacts.required'               => __('Add at least one WhatsApp number.'),
            'contacts.*.number.required'      => __('Enter the WhatsApp number for this contact.'),
            'contacts.*.number.digits_between' => __('Use the full number with country code, like 919876543210.'),
        ], [
            'contacts.*.name'    => __('contact name'),
            'contacts.*.number'  => __('WhatsApp number'),
            'contacts.*.message' => __('message'),
        ]);

        $values = [
            'show_whatsapp_chatbot'                   => $request->boolean('show_whatsapp_chatbot') ? '1' : '0',
            'show_whatsapp_chatbot_on_customer_panel' => $request->boolean('show_whatsapp_chatbot_on_customer_panel') ? '1' : '0',
        ];
        for ($i = 1; $i <= self::MAX_CONTACTS; $i++) {
            $keys = self::slotKeys($i);
            $contact = $contacts[$i - 1] ?? ['name' => '', 'number' => '', 'message' => ''];
            $values[$keys['name']]    = $contact['name'];
            $values[$keys['number']]  = $contact['number'];
            $values[$keys['message']] = $contact['message'];
        }

        foreach ($values as $key => $value) {
            DB::table('config')->updateOrInsert(['config_key' => $key], ['config_value' => $value]);
        }

        return back()->with('success', __('WhatsApp chat button settings saved.'));
    }
}
