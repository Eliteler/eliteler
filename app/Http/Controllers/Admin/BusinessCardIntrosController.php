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

use App\BusinessCardIntro;
use App\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BusinessCardIntrosController extends Controller
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

    // Intros
    public function index(Request $request)
    {
        // status
        $status = $request->query('status');

        // check status
        $intros = BusinessCardIntro::getIntros($status);

        // settings
        $settings = Setting::active()->first();

        // return view
        return view('admin.pages.intros.index', compact('intros', 'settings'));
    }

    // Edit intro
    public function edit(Request $request, $id)
    {
        // get intro
        $intro = BusinessCardIntro::getIntro($id);

        if (!$intro) {
            return redirect()->route('admin.business-card-intros')->with('failed', trans('Not Found!'));
        }

        // settings
        $settings = Setting::active()->first();

        // return view
        return view('admin.pages.intros.edit', compact('intro', 'settings'));
    }

    // Update intro
    public function update(Request $request, $id)
    {
        // Validate
        $validated = Validator::make($request->all(), [
            'intro_name' => 'required|min:3',
            'intro_thumbnail' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:' . env('SIZE_LIMIT'),
        ]);

        if ($validated->fails()) {
            return back()->with('failed', $validated->errors()->first());
        }

        // Get intro
        $intro = BusinessCardIntro::getIntro($id);

        // Check intro exists
        if (!$intro) {
            return back()->with('failed', 'Not Found!');
        }

        // Update name
        $intro->intro_name = $request->intro_name;

        // Handle file upload
        if ($request->hasFile('intro_thumbnail')) {
            $file = $request->file('intro_thumbnail');
            $fileName = 'intro-' . uniqid() . '.' . $file->getClientOriginalExtension();

            Storage::disk('intros')->put($fileName, file_get_contents($file));

            $intro->intro_thumbnail = $fileName;
        }

        $intro->save();

        // Return success
        return redirect()->route('admin.business-card-intros')
            ->with('success', 'Updated!');
    }

    // Update intro status
    public function status(Request $request, $id)
    {
        // Get intro
        $intro = BusinessCardIntro::getIntro($id);

        // Check intro exists
        if (!$intro) {
            return back()->with('failed', 'Not Found!');
        }

        // Update status
        $intro->update([
            'status' => $intro->status == '0' ? 1 : 0,
        ]);

        // Return success
        return redirect()->route('admin.business-card-intros')
            ->with('success', 'Updated!');
    }
}
