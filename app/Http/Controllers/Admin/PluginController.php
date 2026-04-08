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
use App\Services\PluginManager;
use App\Setting;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session as FacadesSession;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class PluginController extends Controller
{
    protected $pluginManager;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Index
    public function index()
    {
        // Queries
        $settings = Setting::active()->first();
        $config   = DB::table('config')->get();

        // Load plugins
        $this->pluginManager->loadPlugins();

        // Get all plugins
        $plugins = $this->pluginManager->getPlugins();

        return view('admin.pages.plugins.index', compact('settings', 'config', 'plugins'));
    }

    public function deletePlugin($pluginName)
    {
        // check this is payment plugin
        if (Schema::hasTable('recurring_payment_gateways')) {
            $recurring_payment_gateways = DB::table('recurring_payment_gateways')->where('plugin_id', $pluginName)->first();

            // check active payments
            if ($recurring_payment_gateways) {
                $active_payments = Transaction::where('payment_gateway_name', $recurring_payment_gateways->payment_gateway_name)->where('payment_status', 'SUCCESS')->count();

                // check active payments
                if ($active_payments > 0) {
                    return redirect()->back()->with('failed', trans('Plugin cannot be deleted. There are active payments using this plugin.'));
                }
            }
        }

        if ($this->pluginManager->deletePlugin($pluginName)) {
            return redirect()->back()->with('success', trans('Deleted!'));
        }

        return redirect()->back()->with('failed', trans('Plugin not found or could not be deleted.'));
    }

    public function upload(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'zip_file' => 'required|mimes:zip|max:' . env("SIZE_LIMIT") . '',
        ]);

        if ($validator->fails()) {
            $limit = env("SIZE_LIMIT");

            FacadesSession::flash('failed', trans('Please upload a valid zip file. File size should be less than :limit Kb Or Increase the upload size limit in settings Panel!', ['limit' => $limit]));
            return response()->json(['message' => trans('Plugin Installation failed!')]);
        }

        $zipFile = $request->file('zip_file');

        // if zip file found
        if (! $zipFile) {
            FacadesSession::flash('failed', trans('Installation failed. File not found!'));
            return response()->json(['message' => trans('Plugin Installation failed!')]);
        }

        $zipName  = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);
        $download = uniqid();
        // Store zip file at storage folder
        $zipPath = storage_path('./app/plugins/' . $download . '.zip');
        file_put_contents($zipPath, $zipFile->get());

        $zip = new ZipArchive;
        $out = $zip->open($zipPath);

        if ($out !== true) {
            // remove zip file
            unlink($zipPath);
            FacadesSession::flash('failed', trans('Installation failed. File is corrupted!'));
            return response()->json(['message' => trans('Plugin Installation failed!')]);
        }

        // Check zip file
        $fileStrictValidationCount = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);

            // Views/index.blade.php
            if (preg_match('#(^|.*/)Views/index\.blade\.php$#i', $fileName)) {
                $fileStrictValidationCount++;
            }

            // routes.php
            if (preg_match('#(^|.*/)routes\.php$#i', $fileName)) {
                $fileStrictValidationCount++;
            }

            // plugin.json or template.json
            if (preg_match('#(^|.*/)(plugin|template)\.json$#i', $fileName)) {
                $fileStrictValidationCount++;
            }

            // Controllers/ folder
            if (preg_match('#(^|.*/)Controllers/$#i', $fileName)) {
                $fileStrictValidationCount++;
            }

            // Views/ folder
            if (preg_match('#(^|.*/)Views/$#i', $fileName)) {
                $fileStrictValidationCount++;
            }
        }

        if ($fileStrictValidationCount < 5) {
            // Remove zip file
            unlink($zipPath);
            FacadesSession::flash('failed', trans('Installation failed. Some files are missing!'));
            return response()->json(['message' => trans('Plugin Installation failed!')]);
        }

        $extractPath = base_path('plugins'); // Extract to plugins directory
        $zip->extractTo($extractPath);
        $zip->close();
        unlink($zipPath);

        FacadesSession::flash('success', trans('Plugin installation success!'));
        return response()->json(['message' => trans('Plugin installation success!')]);
    }
}
