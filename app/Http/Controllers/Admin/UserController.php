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

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminPermissions;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Admin staff accounts: role 3 Administrator, role 4 Manager. The super admin
 * (role 1) and customers (role 2) are never listed or changed here, and
 * nobody can change their own account from this page (use My account).
 * Staff can only grant permissions they have themselves.
 */
class UserController extends Controller
{
    private const STAFF_ROLES = [3, 4];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function users(Request $request)
    {
        $me      = $request->user();
        $search  = trim((string) $request->query('search', ''));
        $perPage = max(1, (int) $request->query('per_page', 10));

        $query = User::whereIn('role_id', self::STAFF_ROLES)
            ->where('id', '!=', $me->id);

        // Filter staff by name or email
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Paginate latest staff accounts
        $staff = $query->latest()->paginate($perPage)->withQueryString()->through(fn(User $user) => [
            'id'          => $user->user_id,
            'name'        => $user->name,
            'email'       => $user->email,
            'avatar'      => asset($user->profile_image ?: 'profile.png'),
            'role'        => (int) $user->role_id,
            'active'      => (int) $user->status === 1,
            'permissions' => collect(AdminPermissions::keys())->filter(fn($key) => AdminPermissions::for($user)[$key] ?? false)->values(),
            'hideRevenue' => AdminPermissions::hidesRevenue($user),
            'createdAt'   => $user->created_at?->toIso8601String(),
        ]);

        $granted = AdminPermissions::for($me);

        return Inertia::render('settings/staff', [
            'staff'            => $staff,
            'groups'           => collect(AdminPermissions::GROUPS)->map(fn($items, $group) => [
                'title' => __($group),
                'items' => collect($items)->map(fn($label, $key) => [
                    'key'       => $key,
                    'label'     => __($label),
                    'grantable' => $this->isSuperAdmin($me) || ($granted[$key] ?? false),
                    'revenue'   => in_array($key, AdminPermissions::REVENUE_AREAS, true),
                ])->values(),
            ])->values(),
            'canLoginAs'       => $this->isSuperAdmin($me),
            'canToggleRevenue' => ! AdminPermissions::hidesRevenue($me),
            'open'             => $request->query('edit') ?: ($request->boolean('new') ? 'new' : null),
            'search'           => $search,
        ]);
    }

    // Old sub-pages now open the dialog on the list page
    public function createUser()
    {
        return redirect()->route('admin.users', ['new' => 1]);
    }

    // View user
    public function viewUser($id)
    {
        return redirect()->route('admin.users', ['edit' => $id]);
    }

    // Edit user
    public function editUser($id)
    {
        return redirect()->route('admin.users', ['edit' => $id]);
    }

    // Save user
    public function saveUser(Request $request)
    {
        $data = $this->validated($request);

        $user                    = new User();
        $user->user_id           = uniqid();
        $user->role_id           = $data['role'];
        $user->name              = $data['name'];
        $user->email             = $data['email'];
        $user->email_verified_at = now();
        $user->password          = Hash::make($data['password']);
        $user->permissions       = json_encode($this->permissions($request, null));
        $user->status            = 1;
        $user->save();

        return back()->with('success', __(':name can now sign in to the admin panel.', ['name' => $user->name]));
    }

    // Update user
    public function updateUser(Request $request)
    {
        $user = $this->staff($request, (string) $request->input('user_id'));
        $data = $this->validated($request, $user);

        $user->role_id     = $data['role'];
        $user->name        = $data['name'];
        $user->email       = $data['email'];
        $user->permissions = json_encode($this->permissions($request, $user));
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', __(':name saved.', ['name' => $user->name]));
    }

    // Update user status
    public function updateUserStatus(Request $request)
    {
        $user = $this->staff($request, (string) $request->input('id'));
        $user->forceFill(['status' => (int) $user->status === 1 ? 0 : 1])->save();

        return back()->with('success', (int) $user->status === 1
            ? __(':name can sign in again.', ['name' => $user->name])
            : __(':name can no longer sign in.', ['name' => $user->name]));
    }

    // Delete user
    public function deleteUser(Request $request)
    {
        try {
            $user = $this->staff($request, (string) $request->input('id'));

            if (!$user) {
                return back()->with('failed', __('User not found.'));
            }

            $name = $user->name;

            // Begin transaction to ensure safe deletion
            DB::beginTransaction();

            // This triggers the booted deleting event in App\User.php
            $user->delete();

            // Commit transaction if no errors occurred
            DB::commit();

            return back()->with('success', __(':name removed.', ['name' => $name]));
        } catch (\Throwable $th) {
            // Rollback all database operations if an error occurs
            DB::rollBack();

            // Log error details for reference
            // Log::error('User deletion error: ' . $th->getMessage());

            // Return error message and prevent deletion
            return back()->with('failed', __('Failed to delete user. Reason: :error', [
                'error' => $th->getMessage()
            ]));
        }
    }

    /** Super admin only: open the admin panel as another staff member. */
    public function authAsUser(Request $request, $id)
    {
        abort_unless($this->isSuperAdmin($request->user()), 403);

        $user = $this->staff($request, (string) $id);
        if ((int) $user->status !== 1) {
            return back()->with('failed', __(':name is deactivated.', ['name' => $user->name]));
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Force a full browser reload so <meta name="csrf-token"> gets refreshed
        return Inertia::location(route('admin.dashboard'));
    }

    /** A staff account other than the signed-in one, or 404. */
    private function staff(Request $request, string $userId): User
    {
        return User::where('user_id', $userId)
            ->whereIn('role_id', self::STAFF_ROLES)
            ->where('id', '!=', $request->user()->id)
            ->firstOrFail();
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'role'     => ['required', Rule::in(self::STAFF_ROLES)],
            'name'     => 'required|string|min:2|max:191',
            'email'    => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:191'],
        ], [
            'email.unique' => __('Another account already uses this email.'),
        ]);
    }

    /**
     * Requested permissions, limited to what the signed-in user may grant.
     * Permissions they can't grant keep their current value on edits.
     * Staff with revenue hidden always save hide_revenue as on.
     */
    private function permissions(Request $request, ?User $user): array
    {
        $me        = $request->user();
        $granted   = AdminPermissions::for($me);
        $requested = (array) $request->input('permissions', []);
        $current   = $user ? AdminPermissions::for($user) : [];

        return collect(AdminPermissions::keys())->mapWithKeys(function ($key) use ($me, $granted, $requested, $current) {
            $canGrant = $this->isSuperAdmin($me) || ($granted[$key] ?? false);

            return [$key => $canGrant ? (int) in_array($key, $requested, true) : (int) ($current[$key] ?? false)];
        })->put(AdminPermissions::HIDE_REVENUE, AdminPermissions::hidesRevenue($me) ? 1 : (int) $request->boolean('hide_revenue'))->all();
    }

    private function isSuperAdmin(?User $user): bool
    {
        return $user && (int) $user->role_id === 1;
    }
}
