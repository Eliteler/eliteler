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

use App\NfcCardOrder;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * To the site owner: a customer's Smart Product order now has every
 * required image asset (or a file changed after that). Sent by
 * App\Services\SmartProducts\OrderAssets::store().
 */
class SmartProductAssetsUploaded extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, array{key: string, name: string, resolution: string, required: bool, url: ?string}> $assets
     */
    public function __construct(
        public NfcCardOrder $order,
        public User $customer,
        public array $assets,
        public string $assetName,
        public bool $replaced,
        public bool $justCompleted,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->justCompleted
            ? __('Order :order: print files ready', ['order' => $this->order->nfc_card_order_id])
            : __('Order :order: print file updated', ['order' => $this->order->nfc_card_order_id]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.smart-product-assets', with: [
            'orderUrl' => route('admin.order.show', $this->order->nfc_card_order_id),
        ]);
    }
}
