<?php

namespace App\Support\Admin;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Staff roles (table `roles`): 3 Administrator and 4 Manager are built in,
 * anything else is a custom role created from Admin > Administrators and staff.
 * Role 1 (super admin) and 2 (customer) are never staff roles.
 * Permissions are still stored per account (users.permissions).
 */
class AdminRoles
{
    public const SUPER_ADMIN = 1;

    public const CUSTOMER = 2;

    public const BUILTIN = [3, 4];

    private static ?array $cache = null;

    /** @return array<int, array{id:int,name:string,slug:string,builtin:bool,users:int}> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $counts = DB::table('users')
            ->whereNotIn('role_id', [self::SUPER_ADMIN, self::CUSTOMER])
            ->selectRaw('role_id, COUNT(*) as c')
            ->groupBy('role_id')
            ->pluck('c', 'role_id');

        $rows = DB::table('roles')
            ->whereNotIn('role_id', [self::SUPER_ADMIN, self::CUSTOMER])
            ->orderBy('role_id')
            ->get();

        return self::$cache = $rows->map(fn ($row) => [
            'id'      => (int) $row->role_id,
            'name'    => in_array((int) $row->role_id, self::BUILTIN, true) ? __($row->role_name) : $row->role_name,
            'slug'    => $row->role_slug,
            'builtin' => in_array((int) $row->role_id, self::BUILTIN, true),
            'users'   => (int) ($counts[$row->role_id] ?? 0),
        ])->values()->all();
    }

    /** @return array<int, int> */
    public static function staffIds(): array
    {
        return array_map(fn ($role) => $role['id'], self::all());
    }

    /** Display name of a user's role. */
    public static function name(?User $user): string
    {
        if (! $user) {
            return '';
        }
        if ((int) $user->role_id === self::SUPER_ADMIN) {
            return __('Administrator');
        }
        foreach (self::all() as $role) {
            if ($role['id'] === (int) $user->role_id) {
                return $role['name'];
            }
        }

        return __('Administrator');
    }

    public static function create(string $name): int
    {
        $name = trim($name);
        self::assertNameFree($name);

        $id = DB::table('roles')->insertGetId([
            'role_name'  => $name,
            'role_slug'  => self::uniqueSlug($name),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'role_id');

        self::$cache = null;

        return (int) $id;
    }

    public static function rename(int $id, string $name): void
    {
        self::assertCustom($id);
        $name = trim($name);
        self::assertNameFree($name, $id);

        DB::table('roles')->where('role_id', $id)->update([
            'role_name'  => $name,
            'role_slug'  => self::uniqueSlug($name, $id),
            'updated_at' => now(),
        ]);

        self::$cache = null;
    }

    public static function delete(int $id): void
    {
        self::assertCustom($id);

        $inUse = DB::table('users')->where('role_id', $id)->count();
        if ($inUse > 0) {
            throw ValidationException::withMessages([
                'id' => __('This role is assigned to :count staff accounts and cannot be deleted.', ['count' => $inUse]),
            ]);
        }

        DB::table('roles')->where('role_id', $id)->delete();
        self::$cache = null;
    }

    private static function assertCustom(int $id): void
    {
        $exists = DB::table('roles')->where('role_id', $id)->exists();
        if (! $exists || in_array($id, [self::SUPER_ADMIN, self::CUSTOMER, ...self::BUILTIN], true)) {
            throw ValidationException::withMessages(['id' => __('Built-in roles cannot be changed.')]);
        }
    }

    private static function assertNameFree(string $name, ?int $except = null): void
    {
        $taken = DB::table('roles')
            ->whereRaw('LOWER(role_name) = ?', [mb_strtolower($name)])
            ->when($except, fn ($q) => $q->where('role_id', '!=', $except))
            ->exists();

        if ($taken || in_array(mb_strtolower($name), ['admin', 'user', 'administrator', 'manager'], true) && $except === null) {
            throw ValidationException::withMessages(['name' => __('Another role already uses this name.')]);
        }
    }

    private static function uniqueSlug(string $name, ?int $except = null): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $i = 2;
        while (DB::table('roles')->where('role_slug', $slug)->when($except, fn ($q) => $q->where('role_id', '!=', $except))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
