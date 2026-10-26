<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NFCGreetingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageContent;
    public $qrImage;

    /**
     * Create a new message instance.
     */
    public function __construct($messageContent, $qrImage)
    {
        $this->messageContent = $messageContent;
        $this->qrImage        = $qrImage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('NFC Greeting Mail'),
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('NFC Greeting Mail')
            ->view('emails.nfc-greeting');
    }
}
