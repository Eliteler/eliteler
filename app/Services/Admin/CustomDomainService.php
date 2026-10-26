<?php

namespace App\Services\Admin;

use App\BusinessCard;
use App\CustomDomainRequest;
use App\EmailTemplate;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Custom domain requests (custom_domain_requests.transfer_status):
 * 0 pending, 1 approved, -1 rejected. The approved domain of a card is
 * stored in business_cards.custom_domain, which is what the site routes on.
 */
class CustomDomainService
{
    public const PENDING = 0;

    public const APPROVED = 1;

    public const REJECTED = -1;

    private const TEMPLATES = [
        self::PENDING  => '584922675202',
        self::APPROVED => '584922675203',
        self::REJECTED => '584922675204',
    ];

    private const ZAPIER_STATUS = [
        self::PENDING  => 'Processing',
        self::APPROVED => 'Approved',
        self::REJECTED => 'Rejected',
    ];

    /**
     * Move a request to a new status, update the card's live domain, then
     * notify the customer (email + Zapier).
     *
     * @return bool whether the customer email was sent (or not needed)
     */
    public function setStatus(CustomDomainRequest $domainRequest, int $status): bool
    {
        $card = BusinessCard::where('card_id', $domainRequest->card_id)->first();

        DB::transaction(function () use ($domainRequest, $status, $card) {
            if ($status === self::APPROVED) {
                // Only one approved domain per card
                CustomDomainRequest::where('card_id', $domainRequest->card_id)
                    ->where('id', '!=', $domainRequest->id)
                    ->update(['transfer_status' => self::REJECTED]);

                $card?->forceFill(['custom_domain' => $domainRequest->current_domain])->save();
            } elseif ($card && $card->custom_domain === $domainRequest->current_domain) {
                // The live domain is no longer approved: stop serving the card on it
                $card->forceFill(['custom_domain' => null])->save();
            }

            $domainRequest->transfer_status = $status;
            $domainRequest->save();
        });

        $customer = User::where('user_id', $domainRequest->user_id)->first();

        $this->dispatchZapier($domainRequest, $status, $customer, $card);

        return $this->emailCustomer($domainRequest, $status, $customer, $card);
    }

    private function emailCustomer(CustomDomainRequest $domainRequest, int $status, ?User $customer, ?BusinessCard $card): bool
    {
        $template = EmailTemplate::where('email_template_id', self::TEMPLATES[$status])->first();

        if (! $customer?->email || ! $template || (int) $template->is_enabled !== 1) {
            return true;
        }

        try {
            Mail::to($customer->email)->send(new \App\Mail\AppointmentMail([
                'status'         => '',
                'emailSubject'   => $template->email_template_subject,
                'emailContent'   => $template->email_template_content,
                'vcardName'      => $card?->title,
                'previousDomain' => $domainRequest->previous_domain,
                'currentDomain'  => $domainRequest->current_domain,
            ]));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function dispatchZapier(CustomDomainRequest $domainRequest, int $status, ?User $customer, ?BusinessCard $card): void
    {
        if (! function_exists('zapier_dispatch')) {
            return;
        }

        zapier_dispatch('custom_domain', [
            'request_id'      => $domainRequest->custom_domain_request_id,
            'user_name'       => $customer->name ?? 'Customer',
            'user_email'      => $customer->email ?? '',
            'card_name'       => $card->title ?? 'Business Card',
            'previous_domain' => $domainRequest->previous_domain ?? '',
            'current_domain'  => $domainRequest->current_domain ?? '',
            'status'          => self::ZAPIER_STATUS[$status],
            'requested_at'    => now()->toDateTimeString(),
        ]);
    }
}
