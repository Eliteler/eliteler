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
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BusinessCardIntrosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Intros
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['active', 'disabled'], true) ? $request->query('status') : 'all';

        // Business cards using each intro (business_cards.intro_screen holds the intro id)
        $usage = DB::table('business_cards')
            ->whereNotNull('intro_screen')
            ->where('card_status', '!=', 'deleted')
            ->selectRaw('intro_screen, COUNT(*) as total')
            ->groupBy('intro_screen')
            ->pluck('total', 'intro_screen');

        $intros = BusinessCardIntro::getIntros($status)->map(fn (BusinessCardIntro $intro) => [
            'id'        => $intro->business_card_intro_id,
            'name'      => $intro->intro_name,
            'thumbnail' => $intro->intro_thumbnail ? asset('img/intros/' . $intro->intro_thumbnail) : null,
            'active'    => (string) $intro->status === '1',
            'usage'     => (int) ($usage[$intro->business_card_intro_id] ?? 0),
        ]);

        return Inertia::render('themes/intros', [
            'intros'      => $intros,
            'filters'     => ['status' => $status],
            'maxUploadKb' => (int) env('SIZE_LIMIT', 2048),
        ]);
    }

    // Update intro
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'intro_name'      => 'required|string|min:3|max:191',
            'intro_thumbnail' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:' . env('SIZE_LIMIT', 2048),
        ]);

        $intro = BusinessCardIntro::getIntro($id);

        if (! $intro) {
            return back()->with('failed', __('Not Found!'));
        }

        $intro->intro_name = $validated['intro_name'];

        if ($request->hasFile('intro_thumbnail')) {
            if (! empty($intro->intro_thumbnail)) {
                StorageHelper::deleteFile($intro->intro_thumbnail, 'intros');
            }

            $intro->intro_thumbnail = StorageHelper::uploadImage(
                $request->file('intro_thumbnail'),
                '',
                'intro-' . uniqid(),
                'intros'
            );
        }

        $intro->save();

        return back()->with('success', __('Intro updated.'));
    }

    // Enable / disable intro
    public function status($id)
    {
        $intro = BusinessCardIntro::getIntro($id);

        if (! $intro) {
            return back()->with('failed', __('Not Found!'));
        }

        $enable = (string) $intro->status === '0';
        $intro->update(['status' => $enable ? 1 : 0]);

        return back()->with('success', $enable ? __('Intro enabled.') : __('Intro disabled.'));
    }
}
