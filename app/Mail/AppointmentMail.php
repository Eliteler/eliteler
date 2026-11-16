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

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(strtr($this->details['emailSubject'], [
                ':appname' => config('app.name'),
                ':hyperlink' => $this->details['hyperlink'] ?? '',
                ':vcardname' => $this->details['vcardName'] ?? '',
                ':vcardurl' => $this->details['vcardUrl'] ?? '',
                ':appointmentdate' => $this->details['appointmentDate'] ?? '',
                ':appointmenttime' => $this->details['appointmentTime'] ?? '',
                ':googlecalendarurl' => $this->details['googleCalendarUrl'] ?? '',
                '%3Agooglecalendarurl' => $this->details['googleCalendarUrl'] ?? '',
                ':status' => $this->details['status'] ?? '',
                ':customername' => $this->details['customerName'] ?? '',
                ':previousdomain' => $this->details['previousDomain'] ?? '',
                ':currentdomain' => $this->details['currentDomain'] ?? '',
                ':receivername' => $this->details['receiverName'] ?? '',
                ':receiveremail' => $this->details['receiverEmail'] ?? '',
                ':receiverphone' => $this->details['receiverPhone'] ?? '',
                ':receivermessage' => $this->details['receiverMessage'] ?? '',
                ':planname' => $this->details['planName'] ?? '',
                ':plancode' => $this->details['planCode'] ?? '',
                ':planprice' => $this->details['planPrice'] ?? '',
                ':expirydate' => $this->details['expiryDate'] ?? '',
                ':registeredname' => $this->details['registeredName'] ?? '',
                ':registeredemail' => $this->details['registeredEmail'] ?? '',
                ':orderid' => $this->details['orderid'] ?? '',
                ':cardname' => $this->details['cardname'] ?? '',
                ':cardprice' => $this->details['cardprice'] ?? '',
                ':paymentstatus' => $this->details['paymentstatus'] ?? '',
                ':deliverystatus' => $this->details['deliverystatus'] ?? '',
                ':quantity' => $this->details['quantity'] ?? '',
                ':trackingnumber' => $this->details['trackingnumber'] ?? '',
                ':courierpartner' => $this->details['courierpartner'] ?? '',
                ':orderpageurl' => $this->details['orderpageurl'] ?? '',
                '%3Aorderpageurl' => $this->details['orderpageurl'] ?? '',
                ':totalprice' => $this->details['totalprice'] ?? '',
                ':supportemail' => $this->details['supportemail'] ?? '',
                ':supportphone' => $this->details['supportphone'] ?? '',
                ':customeremail' => $this->details['customeremail'] ?? '',
                ':actionlink' => $this->details['actionlink'] ?? '',
                '%3Aactionlink' => $this->details['actionlink'] ?? '',
                ':checkindate' => $this->details['checkindate'] ?? '',
                ':checkintime' => $this->details['checkintime'] ?? '',
                ':checkoutdate' => $this->details['checkoutdate'] ?? '',
                ':checkouttime' => $this->details['checkouttime'] ?? '',
                ':servicebookingpageurl' => $this->details['servicebookingpageurl'] ?? '',
                '%3Aservicebookingpageurl' => $this->details['servicebookingpageurl'] ?? '',
                '%3Ahyperlink' => $this->details['hyperlink'] ?? '',
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
