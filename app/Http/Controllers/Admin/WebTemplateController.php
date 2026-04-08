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
use App\Services\TemplateManager;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session as FacadesSession;
use ZipArchive;

class WebTemplateController extends Controller
{
    protected $templateManager;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
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

        // Load templates
        $this->templateManager->loadTemplates();

        // Get all templates
        $templates = $this->templateManager->getTemplates();

        // Return view
        return view('admin.pages.web-templates.index', compact('settings', 'config', 'templates'));
    }

    public function deleteTemplate(Request $request, $templateName)
    {
        // check this template is active
        $web_template = getConfigData('web_template');
        if ($web_template == $templateName) {
            return redirect()->back()->with('failed', trans('You cannot delete the active template.'));
        }

        // Delete template
        if ($this->templateManager->deleteTemplate($templateName)) {
            return redirect()->back()->with('success', trans('Deleted!'));
        }

        // return back
        return redirect()->back()->with('failed', trans('Template not found or could not be deleted.'));
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zip_file' => 'required|mimes:zip|max:' . env("SIZE_LIMIT") . '',
        ]);

        if ($validator->fails()) {
            $limit = env("SIZE_LIMIT");
            FacadesSession::flash('failed', trans('Please upload a valid zip file. File size should be less than :limit Kb Or Increase the upload size limit in settings Panel!', ['limit' => $limit]));
            return response()->json(['message' => trans('Template Installation failed!')]);
        }

        $zipFile = $request->file('zip_file');

        // if zip file found
        if (! $zipFile) {
            FacadesSession::flash('failed', trans('Installation failed. File not found!'));
            return response()->json(['message' => trans('Template Installation failed!')]);
        }

        $zipName  = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);
        // Store zip file at storage folder
        $zipPath = storage_path('./app/templates/' . uniqid() . '.zip'); 
        file_put_contents($zipPath, $zipFile->get());

        $zip = new ZipArchive;
        $out = $zip->open($zipPath);

        if ($out !== true) {
            // remove zip file
            unlink($zipPath);
            FacadesSession::flash('failed', trans('Installation failed. File is corrupted!'));
            return response()->json(['message' => trans('Template Installation failed!')]);
        }

        $count = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file = $zip->getNameIndex($i);

            // Allow both root-level and folder-prefixed paths
            if (preg_match('#(^|.*/)(Views/)#i', $file)) $count++;
            if (preg_match('#(^|.*/)(Controllers/)#i', $file)) $count++;
            if (preg_match('#(^|.*/)routes\.php$#i', $file)) $count++;
            if (preg_match('#(^|.*/)(template|plugin)\.json$#i', $file)) $count++;
        }

        if ($count < 4) {
            // Remove zip file
            unlink($zipPath);
            FacadesSession::flash('failed', trans('Installation failed. Some files are missing!'));
            return response()->json(['message' => trans('Template Installation failed!')]);
        }

        $extractPath = base_path('templates');
        $zip->extractTo($extractPath);
        $zip->close();
        unlink($zipPath);

        FacadesSession::flash('success', trans('Template installation success!'));
        return response()->json(['message' => trans('Template installation success!')]);
    }

    // activate template
    public function activateTemplate($template_id) {
        // Update
        DB::table('config')->where('config_key', 'web_template')->update(['config_value' => $template_id]);

        // Return back
        return redirect()->back()->with('success', trans('Template activated!'));
    }
}
