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

namespace App\Http\Controllers\User;

use App\Setting;
use App\Visitor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VisitorController extends Controller
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

    // Visitors
    public function index(Request $request, $id)
    {
        $settings = Setting::where('status', 1)->first();

        return view('user.pages.cards.visitors', compact('settings', 'id'));
    }

    // Visitors Data
    public function visitorsData(Request $request, $id)
    {
        $query = Visitor::where('card_id', $id)->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return $row->created_at->diffForHumans();
            })
            ->editColumn('platform', function ($row) {
                return str_replace('"', '', $row->platform);
            })
            ->make(true);
    }
}
