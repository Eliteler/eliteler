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

use App\AiCreditsPlan;
use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiCreditsPlanController extends Controller
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

    // AI Credits Plans
    public function index()
    {
        // check modern_dashboard_enabled
        if (Schema::hasTable('modern_dashboard_settings')) {
            $is_modern_dashboard_enabled = DB::table('modern_dashboard_settings')->first()->modern_dashboard_enabled;
            if (plugin_active('ModernDashboard') && $is_modern_dashboard_enabled == 1) {
                if (session()->has('success') || session()->has('failed')) {
                    session()->reflash();
                }
                return redirect()->route('user.dashboard.ai-credits');
            }
        }

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Get aicredits plans
        $aiCreditsPlans = AiCreditsPlan::where('status', 'active')->get();

        return view('user.pages.ai-credits-plans.index', compact('settings', 'config', 'aiCreditsPlans'));
    }
}
