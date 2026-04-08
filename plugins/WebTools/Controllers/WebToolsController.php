<?php
namespace Plugins\WebTools\Controllers;

use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class WebToolsController extends Controller
{
    // Enable/Disable Web Tools
    public function index()
    {
        // Queries
        $settings = Setting::active()->first();
        $config   = DB::table('config')->get();

        // Update Enable/Disable NFC Card Orders
        return view()->file(base_path('plugins/WebTools/Views/index.blade.php'), compact('settings', 'config'));
    }

    // Update Enable/Disable Web Tools
    public function update(Request $request)
    {
        // Check if the form is valid
        $enableWebtools = $request->enable_webtools == '1' ? 1 : 0;

        // Update the database
        DB::table('config')->where('config_key', 'enable_webtools')->update(['config_value' => $enableWebtools]);

        return redirect()->route('admin.plugin.webtools')->with('success', trans('Updated!'));
    }
}
