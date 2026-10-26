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


namespace Plugins\AiBuilder\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Admin\PluginPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin settings for the AI business card builder (table aibuilder_settings).
 * The page itself is React: plugins/AiBuilder/resources/js/settings.tsx,
 * built into dist/ with `npm run plugin:build -- AiBuilder`.
 */
class AiBuilderController extends Controller
{
    /** Provider => model => label */
    private const MODELS = [
        'openai' => [
            'gpt-5.2'     => 'GPT-5.2',
            'gpt-5.1'     => 'GPT-5.1',
            'gpt-5'       => 'GPT-5',
            'gpt-4.1'     => 'GPT-4.1',
            'gpt-4o'      => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
        ],
    ];

    private const PROVIDERS = ['openai' => 'OpenAI'];

    public function index()
    {
        $settings = DB::table('aibuilder_settings')->first();

        return PluginPage::render('AiBuilder', 'settings', [
            'values'    => [
                'aibuilder'      => (bool) ($settings->aibuilder ?? false),
                'generate_image' => (bool) ($settings->generate_image ?? false),
                'provider'       => (string) ($settings->provider ?: 'openai'),
                'model'          => (string) ($settings->model ?? ''),
            ],
            // Keys never leave the server; the page only knows whether they're set
            'savedKeys' => [
                'key_1' => ! empty($settings->key_1),
                'key_2' => ! empty($settings->key_2),
            ],
            'providers' => collect(self::PROVIDERS)->map(fn ($label, $value) => [
                'value'  => $value,
                'label'  => $label,
                'models' => collect(self::MODELS[$value] ?? [])->map(fn ($name, $id) => ['value' => $id, 'label' => $name])->values(),
            ])->values(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'aibuilder'      => 'required|boolean',
            'generate_image' => 'required|boolean',
            'provider'       => ['required', Rule::in(array_keys(self::PROVIDERS))],
            'model'          => ['required', Rule::in(array_keys(self::MODELS[$request->input('provider')] ?? []))],
            'key_1'          => 'nullable|string|max:191',
            'key_2'          => 'nullable|string|max:191',
        ], [], [
            'aibuilder'      => __('AI builder'),
            'generate_image' => __('image generation'),
            'model'          => __('model'),
            'key_1'          => __('primary API key'),
            'key_2'          => __('backup API key'),
        ]);

        $settings = DB::table('aibuilder_settings')->first();

        if ($request->boolean('aibuilder') && empty($data['key_1']) && empty($settings->key_1)) {
            return back()->withErrors(['key_1' => __('Add an API key before turning on the AI builder.')]);
        }

        $update = [
            'aibuilder'      => $request->boolean('aibuilder') ? 1 : 0,
            'generate_image' => $request->boolean('generate_image') ? 1 : 0,
            'provider'       => $data['provider'],
            'model'          => $data['model'],
            'updated_at'     => now(),
        ];

        // Blank key fields keep the saved keys
        foreach (['key_1', 'key_2'] as $key) {
            if (! empty($data[$key])) {
                $update[$key] = trim($data[$key]);
            }
        }

        $settings
            ? DB::table('aibuilder_settings')->where('id', $settings->id)->update($update)
            : DB::table('aibuilder_settings')->insert($update + ['created_at' => now()]);

        return back()->with('success', __('AI builder settings saved.'));
    }

    /** Remove the backup key (it can't be cleared by leaving the field blank). */
    public function removeBackupKey()
    {
        DB::table('aibuilder_settings')->update(['key_2' => null, 'updated_at' => now()]);

        return back()->with('success', __('Backup API key removed.'));
    }
}
