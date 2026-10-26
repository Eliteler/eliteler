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

namespace App\Http\Controllers\Admin;

use App\Group;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Contact groups for email campaigns. groups.emails is a JSON list of
 * customer email addresses. status -1 = deleted.
 */
class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $groups = Group::where('status', '!=', -1)->orderByDesc('created_at')->get()->map(fn (Group $g) => [
            'id'          => $g->group_id,
            'name'        => $g->group_name,
            'description' => $g->group_desc,
            'emails'      => array_values(json_decode($g->emails ?? '[]', true) ?: []),
            'created'     => $g->created_at?->toIso8601String(),
        ]);

        return Inertia::render('marketing/groups', [
            'groups'    => $groups,
            'customers' => User::where('role_id', 2)->where('status', '!=', -1)->orderBy('name')->get(['name', 'email', 'plan_id'])
                ->map(fn ($u) => ['name' => $u->name, 'email' => $u->email, 'hasPlan' => (bool) $u->plan_id]),
            'open'      => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
        ]);
    }

    // Old pages: open the dialog on the groups page
    public function createGroup()
    {
        return redirect()->route('admin.marketing.groups', ['new' => 1]);
    }

    public function viewGroup($id)
    {
        return redirect()->route('admin.marketing.groups', ['edit' => $id]);
    }

    public function editGroup($id)
    {
        return redirect()->route('admin.marketing.groups', ['edit' => $id]);
    }

    public function saveGroup(Request $request)
    {
        $data = $this->validated($request);

        $group           = new Group();
        $group->group_id = uniqid();
        $this->fill($group, $data)->save();

        return back()->with('success', __('Group “:name” created.', ['name' => $group->group_name]));
    }

    public function updateGroup(Request $request, $id)
    {
        $group = Group::where('group_id', $id)->firstOrFail();
        $this->fill($group, $this->validated($request))->save();

        return back()->with('success', __('Group updated.'));
    }

    public function deleteGroup(Request $request)
    {
        Group::where('group_id', (string) $request->input('id'))->update(['status' => -1]);

        return back()->with('success', __('Group deleted.'));
    }

    private function validated(Request $request): array
    {
        // Older forms send an "all" option along with the addresses
        $request->merge(['emails' => array_values(array_filter((array) $request->input('emails', []), fn ($e) => $e !== 'all'))]);

        return $request->validate([
            'group_name' => 'required|string|max:191',
            'group_desc' => 'required|string|max:255',
            'emails'     => 'required|array|min:1',
            'emails.*'   => 'email',
        ], ['emails.required' => __('Add at least one contact.')], [
            'group_name' => __('name'),
            'group_desc' => __('description'),
        ]);
    }

    private function fill(Group $group, array $data): Group
    {
        $group->group_name = ucfirst($data['group_name']);
        $group->group_desc = ucfirst($data['group_desc']);
        // array_values: always store a JSON list, never an object
        $group->emails     = json_encode(array_values(array_unique(array_filter($data['emails'], fn ($e) => $e !== 'all'))));

        return $group;
    }
}
