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

namespace App\Http\Controllers\User;

use App\BusinessCard;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Setting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // My account
    public function account()
    {
        // Queries
        $account_details = User::where('user_id', auth()->user()->user_id)->where('status', 1)->first();
        $settings        = Setting::where('status', 1)->first();

        if ($account_details == null) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home-locale');
        }

        return view('user.pages.account.account', compact('account_details', 'settings'));
    }

    // Update account
    public function updateAccount(Request $request)
    {
        // Check profile image
        if ($request->profile_picture == null) {
            // Validate
            $validator = Validator::make($request->all(), [
                'name'  => 'required',
                'email' => 'required'
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->all()[0])->withInput();
            }

            // Check email address is already exists
            if (User::where('email', $request->email)->where('user_id', '!=', Auth::user()->user_id)->exists()) {
                return back()->with('failed', trans('Email address already exists. Please try with another email address.'))->withInput();
            }

            // Update
            User::where('user_id', Auth::user()->user_id)->update([
                'name'  => $request->name,
                'email' => $request->email,
                'country_code'  => $request->country_code ?? '',
                'mobile_number' => $request->mobile_number ?? '',
                'whatsapp_country_code' => $request->whatsapp_country_code ?? '',
                'whatsapp_number'       => $request->whatsapp_number ?? '',
            ]);

            return redirect()->route('user.account')->with('success', trans('Profile Updated Successfully!'));
        } else {
            // Validate
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|mimes:jpeg,png,jpg,gif,svg|max:' . env("SIZE_LIMIT"),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->all()[0])->withInput();
            }

            if ($request->hasFile('profile_picture') && $request->file('profile_picture')->isValid()) {
                $file = $request->file('profile_picture');
                $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];

                if (in_array(strtolower($file->getClientOriginalExtension()), $allowedExtensions)) {
                    // Delete old profile image from storage
                    StorageHelper::deleteFile(Auth::user()->profile_image);

                    // Upload new profile image and get relative path
                    $profile_picture = StorageHelper::uploadImage($file, 'profile-images');

                    // Update user profile image in database
                    User::where('user_id', Auth::user()->user_id)->update([
                        'profile_image' => $profile_picture,
                    ]);
                }
            }

            return redirect()->route('user.account')->with('success', trans('Updated!'));
        }
    }

    // Change password
    public function changePassword()
    {
        // Queries
        $account_details = User::where('user_id', Auth::user()->user_id)->where('status', 1)->first();
        $settings        = Setting::where('status', 1)->first();

        return view('user.pages.account.change-password', compact('account_details', 'settings'));
    }

    // Update password
    public function updatePassword(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), [
            'new_password'     => 'required',
            'confirm_password' => 'required',
        ]);

        // Check password and confirm password
        if ($request->new_password == $request->confirm_password) {
            // Update
            User::where('user_id', Auth::user()->user_id)->update([
                'password' => bcrypt($request->new_password),
            ]);

            if ($validator->fails()) {
                return back()->with('failed', $validator->messages()->all()[0])->withInput();
            }

            return redirect()->route('user.change.password')->with('success', trans('Updated!'));
        } else {
            return redirect()->route('user.change.password')->with('failed', trans('Confirm Password Mismatched.'));
        }
    }

    // Change theme
    public function changeTheme($id)
    {
        // Update Password
        User::where('id', auth()->user()->id)->update([
            'choosed_theme' => $id,
        ]);

        return redirect()->route('user.dashboard');
    }

    // Edit account
    public function settings()
    {
        // Queries
        $account_details = User::where('user_id', auth()->user()->user_id)->where('status', 1)->first();
        $settings        = Setting::where('status', 1)->first();

        return view('user.pages.account.settings', compact('account_details', 'settings'));
    }

    // update settings
    public function updateSettings(Request $request)
    {

        return redirect()->route('user.settings')->with('success', trans('Settings Updated Successfully!'));
    }

    // Delete Account
    public function deleteAccount(Request $request)
    {
        $user = User::where('user_id', auth()->user()->user_id)
            ->where('status', 1)
            ->first();

        if ($user) {

            // Update business card status
            BusinessCard::where('user_id', auth()->user()->user_id)->update([
                'card_status' => 'deleted',
                'status' => 0,
            ]);

            // Update 
            User::where('user_id', auth()->user()->user_id)->update([
                'email' => $user->email . 'deleted',
                'status' => -1,
            ]);

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home-locale')->with('success', trans('Account deleted successfully'));
        }

        return redirect()->route('home-locale')->with('failed', trans('Account not found'));
    }
}
