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

use App\User;
use App\Group;
use App\Setting;
use App\Campaign;
use App\CampaignEmail;
use App\Mail\MarketingEmail;
use Illuminate\Http\Request;
use App\Services\MailgunService;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
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

    // All Campaigns
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Campaign::where('status', '!=', -1)->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('campaign_name', function ($row) {
                    return $row->campaign_name;
                })
                ->addColumn(('campaign_desc'), function ($row) {
                    return $row->campaign_desc;
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 0
                        ? '<span class="badge bg-red text-white text-white">' . __('Deactivate') . '</span>'
                        : '<span class="badge bg-green text-white text-white">' . __('Active') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    // Recampaign button
                    $recampaignButton = '<a href="' . route('admin.marketing.campaigns.recampaign') . '?id=' . $row->campaign_id . '" class="dropdown-item">' . __('Recampaign') . '</a>';

                    // Activate/Deactivate button
                    $activateDeactivate = $row->status == 0 ? __('Activate') : __('Deactivate');
                    $activateDeactivateFunction = $row->status == 0 ? 'activateCampaign' : 'deactivateCampaign';
                    $activateDeactivateButton = '<a class="dropdown-item" href="#" onclick="' . $activateDeactivateFunction . '(\'' . $row->campaign_id . '\'); return false;">' . $activateDeactivate . '</a>';

                    // Delete button
                    $deleteButton = '<a class="dropdown-item" href="#" onclick="deleteCampaign(\'' . $row->campaign_id . '\'); return false;">' . __('Delete') . '</a>';

                    return '
                        <a class="btn-action" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            ' . $recampaignButton . '
                            ' . $activateDeactivateButton . '
                            ' . $deleteButton . '
                        </div>
                    ';
                })
                ->rawColumns(['campaign_name', 'campaign_desc', 'status', 'action'])
                ->make(true);
        }

        // Get groups
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.marketing.campaigns.index', compact('settings', 'config'));
    }

    // Create Campaign
    public function createCampaign()
    {
        // Queries
        $groups = Group::where('status', 1)->get();
        $settings = Setting::where('status', 1)->first();

        return view('admin.pages.marketing.campaigns.create', compact('groups', 'settings'));
    }

    // Save Campaign
    public function saveCampaign(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'campaign_name' => 'required|string|min:3',
            'campaign_description' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Generate campaign id
        $campaignId = uniqid();

        // Save campaign
        $campaign = new Campaign;
        $campaign->campaign_id = $campaignId;
        $campaign->campaign_name = ucfirst($request->campaign_name);
        $campaign->campaign_desc = $request->campaign_description;
        $campaign->save();

        // Campaign emails
        $campaign_emails = new CampaignEmail;
        $campaign_emails->campaign_email_id = uniqid();
        $campaign_emails->campaign_id = $campaignId;
        $campaign_emails->group_id = $request->group;
        $campaign_emails->subject = $request->email_subject;
        $campaign_emails->body = $request->email_body;
        $campaign_emails->save();

        // Retrieve email configuration from config table
        $config = DB::table('config')->get();

        // Check if Mailgun config exists
        if ($config[57]->config_value == '' || $config[58]->config_value == '' || $config[59]->config_value == '') {
            return redirect()->route('admin.marketing.campaigns')
                ->with('failed', trans('Email configuration not found or incomplete'));
        }

        // Resolve Mailgun service
        $mailgun = app(\App\Services\MailgunService::class);

        // Get email addresses from group
        $emails = Group::where('group_id', $request->group)->first()->emails;
        $emails = json_decode($emails, true);

        $subject = $request->email_subject;
        $messageTemplate = $request->email_body;

        // Send email to each user
        try {
            foreach ($emails as $email) {
                // Replace #name with customer name
                $customer = User::where('email', $email)->first();
                $personalizedMessage = str_replace(
                    '#name',
                    $customer ? $customer->name : '',
                    $messageTemplate
                );

                // Send via Mailgun service
                $mailgun->sendEmail($email, $subject, $personalizedMessage);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.marketing.campaigns')
                ->with('failed', trans('Failed to send emails: ' . $e->getMessage()));
        }

        // Redirect
        return redirect()->route('admin.marketing.campaigns')->with('success', trans('Created!'));
    }

    // Recampaign
    public function recampaign(Request $request)
    {
        // Campaign details and CampaignEmail details in single query (use joins)
        $campaign_details = Campaign::where('campaigns.campaign_id', $request->query('id'))->join('campaign_emails', 'campaign_emails.campaign_id', '=', 'campaigns.campaign_id')->first();

        $groups = Group::where('status', 1)->get();
        $settings = Setting::where('status', 1)->first();

        if ($campaign_details == null) {
            return redirect()->route('admin.marketing.campaigns')->with('failed', trans('Not Found!'));
        }

        return view('admin.pages.marketing.campaigns.recampaign', compact('campaign_details', 'groups', 'settings'));
    }

    // Resend Campaign
    public function resendCampaign(Request $request, MailgunService $mailgun)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'campaign_name' => 'required|string|max:255',
            'campaign_description' => 'required|string|max:255',
            'group' => 'required|string|max:255',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Get campaign details
        $campaign_details = Campaign::where('campaign_id', $request->query('id'))->first();

        if (!$campaign_details) {
            return redirect()->route('admin.marketing.campaigns')->with('failed', trans('Not Found!'));
        }

        // Get emails from group
        $emails = Group::where('group_id', $request->group)->first()->emails;
        $emails = json_decode($emails, true);

        $subject = $request->email_subject;
        $messageTemplate = $request->email_body;

        try {
            foreach ($emails as $email) {
                // Personalize message
                $customer = User::where('email', $email)->first();
                $personalizedMessage = str_replace(
                    '#name',
                    $customer ? $customer->name : '',
                    $messageTemplate
                );

                // Send via Mailgun service
                $mailgun->sendEmail($email, $subject, $personalizedMessage);
            }

            // ✅ Update campaign details (before redirect)
            $campaign_details->campaign_name = ucfirst($request->campaign_name);
            $campaign_details->campaign_desc = ucfirst($request->campaign_description);
            $campaign_details->save();

            // ✅ Update campaign emails
            $campaign_emails = CampaignEmail::where('campaign_id', $campaign_details->campaign_id)->first();
            if ($campaign_emails) {
                $campaign_emails->group_id = $request->group;
                $campaign_emails->subject = $request->email_subject;
                $campaign_emails->body = clean($request->email_body);
                $campaign_emails->save();
            }

            return redirect()
                ->route('admin.marketing.campaigns')
                ->with('success', trans('Emails sent!'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.marketing.campaigns')
                ->with('failed', trans('Failed to send emails some emails are not valid!'));
        }
    }

    // Status Campaign
    public function statusCampaign(Request $request)
    {
        // Queries
        $campaign_details = Campaign::where('campaign_id', $request->query('id'))->first();

        if ($campaign_details == null) {
            return redirect()->route('admin.marketing.campaigns')->with('failed', trans('Not Found!'));
        } else {
            // Get status from Campaign
            $campaignStatus = Campaign::where('campaign_id', $request->query('id'))->first();

            // Check status
            if ($campaignStatus->status == 0) {
                $campaign_details->status = 1;
            } else {
                $campaign_details->status = 0;
            }
            $campaign_details->save();

            return redirect()->route('admin.marketing.campaigns')->with('success', trans('Updated!'));
        }
    }

    // Delete Campaign
    public function deleteCampaign(Request $request)
    {
        // Update status
        $campaign_details = Campaign::where('campaign_id', $request->query('id'))->first();
        $campaign_details->status = -1;
        $campaign_details->save();

        return redirect()->route('admin.marketing.campaigns')->with('success', trans('Removed!'));
    }
}
