<?php

namespace App\Services;

use Mailgun\Mailgun;
use Illuminate\Support\Facades\DB;

class MailgunService
{
    protected $mg;
    protected $domain;
    protected $from;

    public function __construct()
    {
        // Get Mailgun credentials from config table
        $mailgunApiKey = DB::table('config')->where('config_key', 'mailgun_smtp_password')->value('config_value');
        $mailFrom = DB::table('config')->where('config_key', 'mailgun_from_address')->value('config_value');
        $mailgunRegion = DB::table('config')->where('config_key', 'mailgun_region')->value('config_value');

        // Check region
        $mailgunRegionUrl = 'https://api.mailgun.net';
        if ($mailgunRegion == 'eu') {
            $mailgunRegionUrl = 'https://api.eu.mailgun.net';
        }

        // Create Mailgun client
        $this->mg = Mailgun::create(
            $mailgunApiKey ?? '',
            $mailgunRegionUrl
        );

        // Set domain
        $this->domain = env('MAIN_DOMAIN');

        // Build from address
        $this->from = env('MAIL_FROM_NAME', config('app.name')) . ' <' . ($mailFrom ?? env('MAIL_FROM_ADDRESS')) . '>';
    }

    /**
     * Send an email via Mailgun
     */
    public function sendEmail($to, $subject, $html, $from = null)
    {
        return $this->mg->messages()->send($this->domain, [
            'from'    => $from ?? $this->from,
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html, // use 'text' if plain text only
        ]);
    }
}
