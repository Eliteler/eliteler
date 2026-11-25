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

namespace App\Support\SmartProducts;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product type labels, chip link types and the default Smart Products catalogue.
 */
class SmartProductCatalog
{
    public const GENERIC_TYPE = 'generic';

    /** Original type codes and labels imported when the type table is initialized. */
    public const LEGACY_TYPES = [
        'nfc_card'  => 'NFC Card',
        'nfc_stand' => 'NFC Stand',
        'nfc_tag'   => 'NFC Tag',
        'wristband' => 'Wristband',
        'keychain'  => 'Keychain',
        'sticker'   => 'Sticker',
        'other'     => 'Other',
    ];

    /** link_type => label. Where a chip sends people when it is tapped. */
    public const LINK_TYPES = [
        'vcard' => 'Business card',
        'store' => 'WhatsApp store',
        'url'   => 'Custom URL',
    ];

    /** @return Collection<string, string> */
    public static function typeLabels(): Collection
    {
        return self::typeNames()->map(fn (string $name) => __($name));
    }

    public static function typeCode(?string $code): string
    {
        $code = $code ?: 'nfc_card';

        return self::typeNames()->has($code) ? $code : self::GENERIC_TYPE;
    }

    public static function typeLabel(?string $code): string
    {
        return __(self::typeNames()->get(self::typeCode($code), 'Generic'));
    }

    /** @return Collection<string, string> */
    private static function typeNames(): Collection
    {
        return once(function () {
            try {
                if (! Schema::hasTable('smart_product_types')) {
                    return collect(self::LEGACY_TYPES);
                }

                return DB::table('smart_product_types')->orderBy('name')->pluck('name', 'code');
            } catch (\Throwable) {
                return collect(self::LEGACY_TYPES);
            }
        });
    }

    /**
     * Default products. Ids are fixed so seeding never inserts twice.
     *
     * @return array<int, array{id: string, name: string, type: string, link: string, logo: bool, image: string, short: string, description: string}>
     */
    public static function defaults(): array
    {
        return [
            [
                'id' => 'SP-PET-TAG', 'name' => 'Pet ID NFC Tag', 'type' => 'nfc_tag', 'link' => 'vcard', 'logo' => false,
                'image' => 'pet-id-tag.svg',
                'short' => 'Collar tag that shows your pet\'s profile and your contact details with one tap.',
                'description' => 'A durable NFC tag for your pet\'s collar. Anyone who finds your pet can tap it with a phone to see the pet\'s name, your phone number and address, and call you straight away. Update the details any time without replacing the tag.',
            ],
            [
                'id' => 'SP-GOOGLE-REVIEW-STAND', 'name' => 'Google Review NFC Stand', 'type' => 'nfc_stand', 'link' => 'url', 'logo' => true,
                'image' => 'google-review-stand.svg',
                'short' => 'Counter stand that opens your Google review page with a tap or scan.',
                'description' => 'Place it on your counter or table. Customers tap their phone or scan the QR code and land directly on your Google review form. More reviews, less effort. Change the link from your dashboard at any time.',
            ],
            [
                'id' => 'SP-EVENT-WRISTBAND', 'name' => 'Smart Event Wristband', 'type' => 'wristband', 'link' => 'vcard', 'logo' => true,
                'image' => 'event-wristband.svg',
                'short' => 'NFC wristband for events, check-ins and instant contact sharing.',
                'description' => 'A comfortable NFC wristband for events, conferences, gyms and resorts. Share your profile, event pass or any link with a single tap. Waterproof and reusable.',
            ],
            [
                'id' => 'SP-TIPPING-STAND', 'name' => 'Digital Tipping Stand', 'type' => 'nfc_stand', 'link' => 'url', 'logo' => true,
                'image' => 'tipping-stand.svg',
                'short' => 'Let customers tip instantly through your payment link.',
                'description' => 'A tap-to-tip stand for salons, cafés, delivery and service staff. Customers tap or scan to open your UPI, PayPal or other payment link and leave a tip in seconds.',
            ],
            [
                'id' => 'SP-EMERGENCY-KEYCHAIN', 'name' => 'Emergency Contact Keychain', 'type' => 'keychain', 'link' => 'vcard', 'logo' => false,
                'image' => 'emergency-keychain.svg',
                'short' => 'Keychain that shares emergency contacts and medical notes with a tap.',
                'description' => 'Keep your emergency contacts, blood group and important medical notes one tap away. Ideal for children, elderly family members, riders and travellers.',
            ],
            [
                'id' => 'SP-WARRANTY-CARD', 'name' => 'Digital Warranty Card', 'type' => 'nfc_card', 'link' => 'url', 'logo' => true,
                'image' => 'warranty-card.svg',
                'short' => 'Put a tap-to-open warranty and support page in every product box.',
                'description' => 'An NFC and QR card for your product packaging. Buyers tap it to see warranty terms, register the product or contact your support team. No more lost paper warranty cards.',
            ],
            [
                'id' => 'SP-MEMBERSHIP-CARD', 'name' => 'NFC Membership Card', 'type' => 'nfc_card', 'link' => 'vcard', 'logo' => true,
                'image' => 'membership-card.svg',
                'short' => 'Branded membership card for clubs, gyms and loyalty programs.',
                'description' => 'A premium NFC membership card with your branding. Members tap it to open their profile, benefits or your loyalty page.',
            ],
            [
                'id' => 'SP-WHATSAPP-ORDER-STAND', 'name' => 'WhatsApp Ordering Stand', 'type' => 'nfc_stand', 'link' => 'store', 'logo' => true,
                'image' => 'whatsapp-order-stand.svg',
                'short' => 'Customers tap to open your WhatsApp store and order.',
                'description' => 'A table or counter stand that opens your WhatsApp store. Customers browse your products and send their order on WhatsApp without downloading any app.',
            ],
            [
                'id' => 'SP-BOOKING-STAND', 'name' => 'Appointment Booking Stand', 'type' => 'nfc_stand', 'link' => 'vcard', 'logo' => true,
                'image' => 'booking-stand.svg',
                'short' => 'Tap to open your appointment booking page.',
                'description' => 'Perfect for clinics, salons and consultants. Customers tap or scan to open your business card and book an appointment in a free slot.',
            ],
            [
                'id' => 'SP-REVIEW-STAND', 'name' => 'Review Collection Stand', 'type' => 'nfc_stand', 'link' => 'url', 'logo' => true,
                'image' => 'review-stand.svg',
                'short' => 'Collect reviews on any platform: Google, Facebook, TripAdvisor and more.',
                'description' => 'Point this stand to any review site you choose. Customers tap or scan and leave a review while the experience is fresh.',
            ],
            [
                'id' => 'SP-MENU-STAND', 'name' => 'Smart Menu Stand', 'type' => 'nfc_stand', 'link' => 'store', 'logo' => true,
                'image' => 'menu-stand.svg',
                'short' => 'Contactless menu for restaurants and cafés.',
                'description' => 'Guests tap or scan to see your menu with photos and prices, and order on WhatsApp. Update the menu any time without reprinting.',
            ],
            [
                'id' => 'SP-SOCIAL-STAND', 'name' => 'Social Media Follow Stand', 'type' => 'nfc_stand', 'link' => 'url', 'logo' => true,
                'image' => 'social-stand.svg',
                'short' => 'Grow your followers with a tap-to-follow stand.',
                'description' => 'Send customers to your Instagram, Facebook, YouTube or any profile with one tap. Great for shops, cafés and events.',
            ],
            [
                'id' => 'SP-NFC-STICKER', 'name' => 'NFC Smart Sticker', 'type' => 'sticker', 'link' => 'url', 'logo' => false,
                'image' => 'nfc-sticker.svg',
                'short' => 'Small sticker that turns any surface into a tap point.',
                'description' => 'Stick it on a phone case, laptop, door or product. Link it to your business card, store or any URL and change it whenever you like.',
            ],
        ];
    }
}
