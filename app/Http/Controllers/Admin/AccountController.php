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

namespace App\Http\Controllers\Admin;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/** The signed-in admin's own profile and password (one page). */
class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function account(Request $request)
    {
        $user = $request->user();

        return Inertia::render('account/index', [
            'account' => [
                'name'      => $user->name,
                'email'     => $user->email,
                'avatar'    => asset($user->profile_image ?: 'profile.png'),
                'hasAvatar' => (bool) $user->profile_image,
                'role'      => (int) $user->role_id === 4 ? __('Manager') : __('Administrator'),
                'joinedAt'  => $user->created_at?->toIso8601String(),
            ],
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    // Old sub-pages now live on the account page
    public function editAccount()
    {
        return redirect()->route('admin.account');
    }

    public function changePassword()
    {
        return redirect()->route('admin.account');
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'            => 'required|string|min:3|max:191',
            'email'           => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_picture' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:' . env('SIZE_LIMIT', 2048),
        ], [
            'email.unique' => __('Another account already uses this email.'),
        ], [
            'profile_picture' => __('photo'),
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if ($request->hasFile('profile_picture')) {
            StorageHelper::deleteFile($user->profile_image);
            $user->profile_image = StorageHelper::uploadImage($request->file('profile_picture'), 'profile-images', 'IMG-' . Str::random(12) . '-' . time());
        }

        $user->save();

        return back()->with('success', __('Profile updated.'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'new_password.different' => __('The new password must be different from the current one.'),
        ], [
            'current_password' => __('current password'),
            'new_password'     => __('new password'),
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => __('The current password is incorrect.')]);
        }

        $user->password = bcrypt($request->input('new_password'));
        $user->save();

        return back()->with('success', __('Password changed.'));
    }

    // Light/dark preference; also used by the customer panel
    public function changeTheme($id)
    {
        User::where('id', auth()->user()->id)->update([
            'choosed_theme' => $id,
        ]);

        return redirect()->back();
    }
}
