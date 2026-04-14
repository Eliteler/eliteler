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

namespace Plugins\AiBuilder\Classes;

use App\BusinessCard;
use Illuminate\Support\Str;

class GenerateWithAI
{
    public function generate($image, $aibuilder_settings, $plan_details, $user_id)
    {
        // extracted business card data
        $business_card_data = extractBusinessCardData($image, $aibuilder_settings);

        // if data is null return false
        if (is_null($business_card_data)) {
            return [
                'success' => false,
                'message' => __('Failed to extract business card data.')
            ];
        }

        // company name
        $companyName = $business_card_data['company_name'] ?? 'Business Card';

        // sub title
        $subTitle = $business_card_data['tagline_or_slogan'] ?? 'Business Card';

        // get theme
        $themeId = detectTheme($companyName, $subTitle);

        // description
        $description = $business_card_data['description'] ?? null;

        // email
        if (!empty($business_card_data['email'])) {
            if (is_array($business_card_data['email'])) {
                $email = reset($business_card_data['email']);
            } else {
                $email = $business_card_data['email'];
            }

            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        } else {
            $email = null;
        }

        // business card id
        $card_id = uniqid();

        // Generate profile image using Gravatar
        $profileImage = generateLetterAvatar($companyName, 500);

        // Generate cover image using Gravatar
        $coverImage   = generateCoverImage($companyName ?? $subTitle);

        // construct slug
        if ($plan_details['personalized_link'] == 1) {
            // card slug
            $slug = Str::slug($companyName);

            // check if slug exists
            $is_slug_exists = BusinessCard::where('card_url', $slug)->where('card_status', '!=', 'deleted')->exists();

            // if yes regenerate slug
            if ($is_slug_exists) {
                // tagline
                $tagline = $subTitle;

                // if tagline empty generate random
                if (empty($tagline)) {
                    $tagline = Str::lower(Str::random(5));
                }

                // join slug and subtitle with dash
                $slug .= '-' . Str::slug($tagline);

                // check if slug exists
                $is_slug_exists = BusinessCard::where('card_url', $slug)->where('card_status', '!=', 'deleted')->exists();

                // if slug exists, add random string to slug
                if ($is_slug_exists) {
                    $slug .= '-' . Str::lower(Str::random(5));
                }
            }
        } else {
            $slug = $card_id;
        }

        // Save business card
        $card = new BusinessCard();
        $card->card_id = $card_id;
        $card->user_id = $user_id;
        $card->type = 'business';
        $card->theme_id = $themeId;
        $card->card_lang = 'EN';
        $card->cover_type = 'photo';
        $card->cover = $coverImage;
        $card->profile = $profileImage;
        $card->card_url = $slug;
        $card->card_type = 'vcard';
        $card->title = $companyName;
        $card->sub_title = $subTitle;
        $card->description = $description;
        $card->enquiry_email = $email;
        $card->appointment_receive_email = $email;
        $card->status = 1;
        $card->save();

        // Save social links
        saveSocialLinks($card_id, $business_card_data, $plan_details);

        // return response
        return [
            'success' => true,
            'url' => url($slug)
        ];
    }
}
