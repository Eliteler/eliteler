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

use App\Campaign;
use App\CampaignEmail;
use App\Group;
use App\Http\Controllers\Controller;
use App\Services\MailgunService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Email campaigns sent through Mailgun to a contact group.
 * campaigns.status -1 = deleted. "#name" in the body becomes the customer's name.
 */
class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $campaigns = Campaign::where('campaigns.status', '!=', -1)
            ->leftJoin('campaign_emails', 'campaign_emails.campaign_id', '=', 'campaigns.campaign_id')
            ->leftJoin('groups', 'groups.group_id', '=', 'campaign_emails.group_id')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('campaigns.campaign_name', 'like', "%{$search}%")->orWhere('campaign_emails.subject', 'like', "%{$search}%")))
            ->orderByDesc('campaigns.updated_at')
            ->paginate(20, ['campaigns.campaign_id', 'campaigns.campaign_name', 'campaigns.campaign_desc', 'campaigns.updated_at', 'campaign_emails.subject', 'groups.group_name', 'groups.emails'])
            ->withQueryString()
            ->through(fn ($row) => [
                'id'          => $row->campaign_id,
                'name'        => $row->campaign_name,
                'description' => $row->campaign_desc,
                'subject'     => $row->subject,
                'group'       => $row->group_name,
                'recipients'  => count(json_decode($row->emails ?? '[]', true) ?: []),
                'sentAt'      => $row->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('marketing/campaigns', [
            'campaigns'  => $campaigns,
            'search'     => $search,
            'configured' => $this->configured(),
        ]);
    }

    public function createCampaign()
    {
        return $this->form(null);
    }

    public function recampaign(Request $request)
    {
        $campaign = Campaign::where('campaigns.campaign_id', (string) $request->query('id'))
            ->join('campaign_emails', 'campaign_emails.campaign_id', '=', 'campaigns.campaign_id')
            ->first(['campaigns.campaign_id', 'campaign_name', 'campaign_desc', 'group_id', 'subject', 'body']);

        if (! $campaign) {
            return redirect()->route('admin.marketing.campaigns')->with('failed', __('Not Found!'));
        }

        return $this->form($campaign);
    }

    public function saveCampaign(Request $request, MailgunService $mailgun)
    {
        $data = $this->validated($request);

        $campaign                = new Campaign();
        $campaign->campaign_id   = uniqid();
        $campaign->campaign_name = ucfirst($data['campaign_name']);
        $campaign->campaign_desc = $data['campaign_description'];
        $campaign->save();

        $email                    = new CampaignEmail();
        $email->campaign_email_id = uniqid();
        $email->campaign_id       = $campaign->campaign_id;
        $email->group_id          = $data['group'];
        $email->subject           = $data['email_subject'];
        $email->body              = $data['email_body'];
        $email->save();

        return $this->send($mailgun, $data);
    }

    public function resendCampaign(Request $request, MailgunService $mailgun)
    {
        $campaign = Campaign::where('campaign_id', (string) $request->query('id'))->firstOrFail();
        $data     = $this->validated($request);

        $campaign->campaign_name = ucfirst($data['campaign_name']);
        $campaign->campaign_desc = $data['campaign_description'];
        $campaign->updated_at    = now();
        $campaign->save();

        $email = CampaignEmail::where('campaign_id', $campaign->campaign_id)->first() ?? new CampaignEmail(['campaign_id' => $campaign->campaign_id]);
        if (! $email->exists) {
            $email->campaign_email_id = uniqid();
            $email->campaign_id       = $campaign->campaign_id;
        }
        $email->group_id = $data['group'];
        $email->subject  = $data['email_subject'];
        $email->body     = $data['email_body'];
        $email->save();

        return $this->send($mailgun, $data);
    }

    public function deleteCampaign(Request $request)
    {
        Campaign::where('campaign_id', (string) $request->input('id'))->update(['status' => -1]);

        return back()->with('success', __('Campaign deleted.'));
    }

    // Kept for old links; campaigns have no on/off state in the UI
    public function statusCampaign(Request $request)
    {
        $campaign = Campaign::where('campaign_id', (string) $request->input('id'))->firstOrFail();
        $campaign->status = (int) $campaign->status === 1 ? 0 : 1;
        $campaign->save();

        return back();
    }

    private function form($campaign)
    {
        return Inertia::render('marketing/campaign-form', [
            'campaign'   => $campaign ? [
                'id'          => $campaign->campaign_id,
                'name'        => $campaign->campaign_name,
                'description' => $campaign->campaign_desc,
                'group'       => $campaign->group_id,
                'subject'     => $campaign->subject,
                'body'        => $campaign->body,
            ] : null,
            'groups'     => Group::where('status', 1)->orderBy('group_name')->get(['group_id', 'group_name', 'emails'])->map(fn ($g) => [
                'id'      => $g->group_id,
                'name'    => $g->group_name,
                'members' => count(json_decode($g->emails ?? '[]', true) ?: []),
            ]),
            'configured' => $this->configured(),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'campaign_name'        => 'required|string|min:3|max:191',
            'campaign_description' => 'required|string|max:255',
            'group'                => ['required', Rule::exists('groups', 'group_id')->where('status', 1)],
            'email_subject'        => 'required|string|max:255',
            'email_body'           => 'required|string',
        ], [], [
            'campaign_name'        => __('name'),
            'campaign_description' => __('description'),
            'group'                => __('contact group'),
            'email_subject'        => __('subject'),
            'email_body'           => __('message'),
        ]);
    }

    private function configured(): bool
    {
        $config = DB::table('config')->whereIn('config_key', ['mailgun_smtp_password', 'mailgun_from_address'])->pluck('config_value', 'config_key');

        return ! empty($config['mailgun_smtp_password']) && ! empty($config['mailgun_from_address']);
    }

    /** Send to every member of the group; keeps going when one address fails. */
    private function send(MailgunService $mailgun, array $data)
    {
        if (! $this->configured()) {
            return redirect()->route('admin.marketing.campaigns')->with('failed', __('Campaign saved, but Mailgun isn’t set up yet, so nothing was sent.'));
        }

        $emails = array_values(array_unique(json_decode(Group::where('group_id', $data['group'])->value('emails') ?? '[]', true) ?: []));
        $names  = User::whereIn('email', $emails)->pluck('name', 'email');
        $sent   = 0;
        $failed = [];

        foreach ($emails as $email) {
            try {
                $mailgun->sendEmail($email, $data['email_subject'], str_replace('#name', e($names[$email] ?? ''), $data['email_body']));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                $failed[] = $email;
            }
        }

        return redirect()->route('admin.marketing.campaigns')->with(
            $failed ? 'failed' : 'success',
            $failed
                ? __('Sent to :sent of :total contacts. Failed: :emails', ['sent' => $sent, 'total' => count($emails), 'emails' => implode(', ', array_slice($failed, 0, 5))])
                : __('Campaign sent to :count contacts.', ['count' => $sent])
        );
    }
}
