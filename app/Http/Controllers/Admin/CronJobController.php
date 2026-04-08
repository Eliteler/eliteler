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

use App\Setting;
use Carbon\Carbon;
use App\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class CronJobController extends Controller
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

    // Cron Jobs
    public function index()
    {
        // Queries
        $settings = Setting::first();
        $config = DB::table('config')->get();

        // Separate dates in array
        $config[60]->config_value = str_replace('[', '', $config[60]->config_value);
        $config[60]->config_value = str_replace(']', '', $config[60]->config_value);

        return view('admin.pages.cron-jobs.index', compact('settings', 'config'));
    }

    // Update cron jobs
    public function update(Request $request)
    {
        // Validate form
        $validator = Validator::make($request->all(), [
            'dates_in_array' => 'required',
        ]);

        // Check validation
        if ($validator->fails()) {
            return redirect()->route('admin.cron.jobs')->with('failed', trans('Please fill all the fields.'));
        }

        // dates_in_array in array (Like [10, 5, 3, 1])
        $dates_in_array = explode(',', $request->dates_in_array);
        $dates_in_array = array_map('intval', $dates_in_array);
        $dates_in_array = array_unique($dates_in_array);

        // Check $dates_in_array is min -30 to max 366
        foreach ($dates_in_array as $date) {
            if ($date < -30 || $date > 366) {
                return redirect()->route('admin.cron.jobs')->with('failed', trans('Please enter a valid number of dates.'));
            }
        }

        // Update config
        DB::table('config')->where('config_key', 'cronjob_dates_in_array')->update([
            'config_value' => $dates_in_array,
        ]);

        // Success message
        return redirect()->route('admin.cron.jobs')->with('success', trans('Updated!'));
    }

    // Set cronjob time
    public function setCronjobTime(Request $request)
    {
        // Validate form
        $validator = Validator::make(request()->all(), [
            'cron_hour' => 'required|integer|between:0,23',
        ]);

        // Check validation
        if ($validator->fails()) {
            return redirect()->route('admin.cron.jobs')->with('failed', trans('Please fill all the fields.'));
        }

        // Update config
        DB::table('config')->where('config_key', 'cron_hour')->update([
            'config_value' => $request->cron_hour,
        ]);

        // Success message
        return redirect()->route('admin.cron.jobs')->with('success', trans('Updated!'));
    }

    // Test reminder
    public function testReminder()
    {
        // Details
        $expiredEmailTemplateDetails = EmailTemplate::where('email_template_id', '584922675206')->first();
        $expiryEmailTemplateDetails  = EmailTemplate::where('email_template_id', '584922675207')->first();

        // Dummy plan validity
        $activePlanValidity = Carbon::now()->addDays(3);
        $expiredPlanValidity = Carbon::now()->subDays(3);

        //Dummy plan code, plan name, plan price
        $planCode = "123456789";
        $planName = "Test Plan";
        $planPrice = 100;

        $expiredPlanDetails = [
            'status'          => "",
            'emailSubject'    => $expiredEmailTemplateDetails->email_template_subject,
            'emailContent'    => $expiredEmailTemplateDetails->email_template_content,
            'registeredName'  => "Test User - " . Auth::user()->name,
            'registeredEmail' => Auth::user()->email,
            'expiryDate'      => Carbon::parse($expiredPlanValidity)->format('Y-m-d'),
            'planCode'        => $planCode,
            'planName'        => $planName,
            'planPrice'       => $planPrice,
        ];

        $expiryPlanDetails = [
            'status'          => "",
            'emailSubject'    => $expiryEmailTemplateDetails->email_template_subject,
            'emailContent'    => $expiryEmailTemplateDetails->email_template_content,
            'registeredName'  => "Test User - " . Auth::user()->name,
            'registeredEmail' => Auth::user()->email,
            'expiryDate'      => Carbon::parse($activePlanValidity)->format('Y-m-d'),
            'planCode'        => $planCode,
            'planName'        => $planName,
            'planPrice'       => $planPrice
        ];

        // Send email
        try {
            Mail::to(Auth::user()->email)->send(new \App\Mail\AppointmentMail($expiryPlanDetails));
            Mail::to(Auth::user()->email)->send(new \App\Mail\AppointmentMail($expiredPlanDetails));
        } catch (\Exception $e) {
            return redirect()->back()->with('failed', trans('Remainder emails have not been sent successfully.'));
            // $this->error("Failed to send email to {$user->email}: {$e->getMessage()}");
        }

        return redirect()->back()->with('success', trans('Reminder emails have been sent successfully.'));
    }

    // Run cronjob
    public function runCronjob()
    {
        try {
            // Run cronjob
            Artisan::call('schedule:run');
        } catch (\Exception $e) {
            return redirect()->back()->with('failed', trans('Cronjob has not been run successfully.'));
        }

        // Redirect
        return redirect()->route('admin.cron.jobs')->with('success', trans('Cronjob has been run successfully.'));
    }
}
