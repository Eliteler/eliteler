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

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        $from_address = config('mail.from.address');
        $from_name = config('mail.from.name');
 
        if (isset($this->details['cardId'])) {
            $vcard = \App\BusinessCard::where('card_id', $this->details['cardId'])->first();
            if ($vcard) {
                if (!empty($vcard->email_from_name)) {
                    $from_name = $vcard->email_from_name;
                }
                if (!empty($vcard->email_from_address)) {
                    $from_address = $vcard->email_from_address;
                }
            }
        }
 
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($from_address, $from_name),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($from_address, $from_name)
            ],
            subject: __(strtr($this->details['emailSubject'], [
                ':appname' => config('app.name'),
                ':hyperlink' => $hyperlink ?? '',
                ':vcardname' => $details['vcardName'] ?? '',
                ':vcardurl' => $details['vcardUrl'] ?? '',
                ':appointmentdate' => $details['appointmentDate'] ?? '',
                ':appointmenttime' => $details['appointmentTime'] ?? '',
                ':googlecalendarurl' => $details['googleCalendarUrl'] ?? '',
                '%3Agooglecalendarurl' => $details['googleCalendarUrl'] ?? '',
                ':status' => $details['status'] ?? '',
                ':customername' => $details['customerName'] ?? '',
                ':previousdomain' => $details['previousDomain'] ?? '',
                ':currentdomain' => $details['currentDomain'] ?? '',
                ':receivername' => $details['receiverName'] ?? '',
                ':receiveremail' => $details['receiverEmail'] ?? '',
                ':receiverphone' => $details['receiverPhone'] ?? '',
                ':receivermessage' => $details['receiverMessage'] ?? '',
                ':planname' => $details['planName'] ?? '',
                ':plancode' => $details['planCode'] ?? '',
                ':planprice' => $details['planPrice'] ?? '',
                ':expirydate' => $details['expiryDate'] ?? '',
                ':registeredname' => $details['registeredName'] ?? '',
                ':registeredemail' => $details['registeredEmail'] ?? '',
                ':orderid' => $details['orderid'] ?? '',
                ':cardname' => $details['cardname'] ?? '',
                ':cardprice' => $details['cardprice'] ?? '',
                ':paymentstatus' => $details['paymentstatus'] ?? '',
                ':deliverystatus' => $details['deliverystatus'] ?? '',
                ':quantity' => $details['quantity'] ?? '',
                ':trackingnumber' => $details['trackingnumber'] ?? '',
                ':courierpartner' => $details['courierpartner'] ?? '',
                ':orderpageurl' => $details['orderpageurl'] ?? '',
                '%3Aorderpageurl' => $details['orderpageurl'] ?? '',
                ':totalprice' => $details['totalprice'] ?? '',
                ':supportemail' => $details['supportemail'] ?? '',
                ':supportphone' => $details['supportphone'] ?? '',
                ':customeremail' => $details['customeremail'] ?? '',
                ':actionlink' => $details['actionlink'] ?? '',
                '%3Aactionlink' => $details['actionlink'] ?? '',
                ':checkindate' => $details['checkindate'] ?? '',
                ':checkintime' => $details['checkintime'] ?? '',
                ':checkoutdate' => $details['checkoutdate'] ?? '',
                ':checkouttime' => $details['checkouttime'] ?? '',
                ':servicebookingpageurl' => $details['servicebookingpageurl'] ?? '',
                '%3Aservicebookingpageurl' => $details['servicebookingpageurl'] ?? '',
                '%3Ahyperlink' => $details['hyperlink'] ?? '',
            ])),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment',
        );
    }

    /**
     * Get the attachments for the message.
     * 
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
