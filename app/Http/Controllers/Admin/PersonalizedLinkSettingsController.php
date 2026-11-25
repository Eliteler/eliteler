<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PersonalizedLinkExclude;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PersonalizedLinkSettingsController extends Controller
{
    public function index(Request $request)
    {
        return to_route('admin.settings', [
            'keyword_search' => $request->query('search'),
            'keyword_page' => $request->query('page'),
        ])->withFragment('personalized-link-settings');
    }

    public function store(Request $request)
    {
        $this->save($request, new PersonalizedLinkExclude());

        return to_route('admin.settings', ['keyword_tab' => 'admin'])
            ->withFragment('personalized-link-settings')
            ->with('success', __('Keyword added.'));
    }

    public function update(Request $request, int $exclusion)
    {
        $this->save($request, $this->findCustomExclusion($exclusion));

        return back()->with('success', __('Keyword updated.'));
    }

    public function destroy(int $exclusion)
    {
        $this->findCustomExclusion($exclusion)->delete();

        return back()->with('success', __('Keyword removed.'));
    }

    private function findCustomExclusion(int $id): PersonalizedLinkExclude
    {
        return PersonalizedLinkExclude::query()->where('is_default', false)->findOrFail($id);
    }

    private function save(Request $request, PersonalizedLinkExclude $exclusion): void
    {
        $keyword = $request->input('keyword');
        if (is_string($keyword)) {
            $request->merge(['keyword' => Str::lower(trim($keyword))]);
        }

        $unique = Rule::unique('personalized_link_excludes', 'keyword');
        if ($exclusion->exists) {
            $unique->ignore($exclusion);
        }

        $data = $request->validate([
            'keyword' => ['bail', 'required', 'string', 'max:255', 'regex:/\A(?=.*[a-z0-9])[a-z0-9._-]+\z/', $unique],
        ], [
            'keyword.regex' => __('Use letters, numbers, hyphens, underscores, or periods in a single keyword.'),
            'keyword.unique' => __('This keyword is already reserved.'),
        ]);

        try {
            $exclusion->fill($data)->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['keyword' => __('This keyword is already reserved.')]);
        }
    }
}
