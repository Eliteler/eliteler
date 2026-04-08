<?php

// Extract business card data using AI
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\BusinessField;

function extractBusinessCardData($image, $aibuilder_settings)
{
    // provider
    $provider = $aibuilder_settings->provider ?? 'openai';

    // api keys
    $key1 = trim($aibuilder_settings->key_1 ?? '');
    $key2 = trim($aibuilder_settings->key_2 ?? '');

    // prompt
    $prompt = "
        Extract data from this business card and return ONLY valid JSON.

        Schema:
        {
            company_name: string | null,
            tagline_or_slogan: string | null,
            email: string[],
            contacts: string[],
            social_links: [{ icon: string | null, label: string | null, url: string }],
            full_address: string | null,
            description: string | null
        }

        Rules:
        1. JSON only. No explanations.
        2.Missing fields → null. arrays → [].

        Company name priority (STRICT):
        1. If a logo graphic exists with company text → company_name = logo text.
        2. If NO logo graphic exists and the largest text is a person name → company_name = that person name.
        3. Do NOT use website or email domain as company_name unless printed as a logo graphic.
        4. Website names or emails alone are NOT company_name.
        5. If company_name or person name if fully uppercase change to lowercase with first letter capitalized.

        Contacts:
        1. contacts = phone/mobile numbers only.
        2. No spaces, +, or symbols and dont skip any number.

        Email:
        1. email = plain email text.
        2. Do NOT include email inside social_links.

        Social links:
        1. Only real social platforms:
        [facebook, instagram, x-twitter, linkedin, youtube, url, pinterest, reddit,
        tiktok, threads, snapchat, wechat, telegram, tumblr, qq, discord, quora]
        2. url MUST be full usable URL.
        3. Convert handles to full URLs.

        Address:
        1. full_address = full postal address text.
        2. Do NOT add address in social_links.

        Label rule:
        1. label MUST be platform name derived from URL.

        Icon:
        1. Full FontAwesome class.
        2. fab for brands, fas for others.
        3. End with fa-md.

        Description:
        1. description = short description about business in 2 short sentences (max words = 50).

        If nothing readable → {}.
        ";

    // payload
    $payload = [
        'model' => $aibuilder_settings->model,
        'messages' => [
            ['role' => 'system', 'content' => 'Extract business card details and return valid JSON only.'],
            ['role' => 'user', 'content' => [
                ['type' => 'text', 'text' => $prompt],
                ['type' => 'image_url', 'image_url' => ['url' => 'data:image/jpeg;base64,' . $image]]
            ]]
        ],
    ];

    // Token parameter based on model
    if ($provider == 'openai') {
        if (str_contains($aibuilder_settings->model, 'gpt-5') || str_contains($aibuilder_settings->model, 'o1') || str_contains($aibuilder_settings->model, 'o3')) {
            $payload['max_completion_tokens'] = 500;
        } else {
            $payload['max_tokens'] = 500;
        }
    }

    try {
        // try first key   
        if ($provider == 'openai') {
            $client = OpenAI::client($key1);
            $response = $client->chat()->create($payload);
        }
    } catch (\Exception $e) {
        Log::error($e->getMessage());

        if (!$key2) {
            return null;
        }

        // try second key
        try {
            if ($provider == 'openai') {
                $client = OpenAI::client($key2);
                $response = $client->chat()->create($payload);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    $content = trim($response->choices[0]->message->content ?? '');
    $content = preg_replace('/^```json\s*/', '', $content);
    $content = preg_replace('/\s*```$/', '', $content);

    return json_decode($content, true) ?? [];
}

// Detect theme based on company name keywords
function detectTheme($companyName, $subTitle)
{
    // get text
    $text = strtolower(($companyName ?? '') . ' ' . ($subTitle ?? ''));

    // Keyword map
    $keywordMap = [
        "588969111125" => ['makeup', 'beauty', 'salon', 'cosmetic'],
        "588969111126" => ['chef', 'catering', 'food'],
        "588969111127" => ['software developer', 'it', 'tech'],
        "588969111128" => ['lawyer', 'advocate', 'legal'],
        "588969111129" => ['doctor', 'clinic', 'hospital', 'medical'],
        "588969111130" => ['spa', 'massage'],
        "588969111131" => ['interior', 'furniture'],
        "588969111133" => ['gym', 'fitness'],
        "588969111132" => ['architect', 'construction'],
        "588969111134" => ['yoga'],
        "588969111135" => ['taxi', 'cab'],
        "588969111136" => ['restaurant', 'hotel', 'dining'],
        "588969111137" => ['wedding'],
        "588969111139" => ['school', 'academy'],
        "588969111140" => ['youtube', 'creator'],
        "588969111144" => ['music', 'musician'],
        "588969111145" => ['photographer', 'studio'],
        "588969111146" => ['flower'],
        "588969111148" => ['travel', 'tour'],
        "588969111152" => ['yacht'],
        "588969111153" => ['resort'],
        "588969111160" => ['3d', 'modern']
    ];

    // loop keywords to find match
    foreach ($keywordMap as $themeId => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return $themeId;
            }
        }
    }

    // return default if no match
    return "588969111125";
}

// Generate Gravatar Letter Avatar
function generateLetterAvatar($companyName, $size = 500)
{
    // get first letter of company name
    $letter = strtoupper(substr(trim($companyName), 0, 1));
    // generate hash
    $hash = md5($companyName);
    // generate file path
    $filePath = "avatars/profile_{$hash}.png";

    // avoid duplicates
    if (Storage::disk('public')->exists($filePath)) {
        return "/storage/" . $filePath;
    }

    // Ensure folder exists
    if (!Storage::disk('public')->exists('avatars')) {
        Storage::disk('public')->makeDirectory('avatars');
    }

    // create image
    $image = imagecreatetruecolor($size, $size);

    // Background color (dynamic based on hash)
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));

    // fill image with color
    $bgColor = imagecolorallocate($image, $r, $g, $b);
    imagefill($image, 0, 0, $bgColor);

    // text color
    $textColor = imagecolorallocate($image, 255, 255, 255);

    // font path
    $fontFile = public_path('webfonts/Inter-Bold.ttf');

    // if font not found return null
    if (!file_exists($fontFile)) {
        return null;
    }

    // assign font size
    $fontSize = $size * 0.5;

    // Get text box size
    $bbox = imagettfbbox($fontSize, 0, $fontFile, $letter);
    $textWidth = $bbox[2] - $bbox[0];
    $textHeight = $bbox[1] - $bbox[7];

    $x = ($size - $textWidth) / 2;
    $y = ($size + $textHeight) / 2;

    // write letter to image
    imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $letter);
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    // save image
    Storage::disk('public')->put($filePath, $imageData);

    // return image path
    return "/storage/" . $filePath;
}

// Generate Cover Image
function generateCoverImage($companyName)
{
    // generate hash
    $hash = md5($companyName);

    // generate file path
    $filePath = "avatars/cover_{$hash}.jpg";

    // get disk
    $disk = Storage::disk('public');

    // check if file exists
    if ($disk->exists($filePath)) {
        return "/storage/" . $filePath;
    }

    // set image size
    $width = 850;
    $height = 430;

    // create image
    $image = imagecreatetruecolor($width, $height);

    // Background color
    $r = rand(30, 150);
    $g = rand(60, 180);
    $b = rand(90, 200);

    // fill image with color
    $bgColor = imagecolorallocate($image, $r, $g, $b);
    imagefill($image, 0, 0, $bgColor);

    // get text
    $text = strtoupper($companyName);

    // Text color
    $textColor = imagecolorallocate($image, 255, 255, 255);

    // Font path (make sure this file exists)
    $fontPath = public_path('webfonts/Inter-Bold.ttf');

    // if font not found return null
    if (!file_exists($fontPath)) {
        return null;
    }

    // Dynamic font size based on length
    $length = strlen($text);
    if ($length > 30) {
        $fontSize = 28;
    } elseif ($length > 20) {
        $fontSize = 36;
    } else {
        $fontSize = 48;
    }

    // Calculate text box
    $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
    $textWidth  = $bbox[2] - $bbox[0];
    $textHeight = $bbox[1] - $bbox[7];

    $x = ($width - $textWidth) / 2;
    $y = ($height + $textHeight) / 2;

    // write text to image
    imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
    ob_start();
    imagejpeg($image, null, 90);
    $imageData = ob_get_clean();
    imagedestroy($image);

    // check if avatars folder exists
    if (!$disk->exists('avatars')) {
        $disk->makeDirectory('avatars');
    }

    // save image
    $disk->put($filePath, $imageData);

    // return image path
    return "/storage/" . $filePath;
}

// Format platform label
function formatPlatformLabel($label)
{
    $map = [
        'facebook'   => 'Facebook',
        'instagram'  => 'Instagram',
        'x-twitter'  => 'X',
        'twitter'    => 'X',
        'x'          => 'X',
        'linkedin'   => 'LinkedIn',
        'youtube'    => 'YouTube',
        'url'        => 'Website',
        'email'      => 'Email',
        'phone'      => 'Phone',
        'mobile'     => 'Mobile',
        'address'    => 'Location',
        'pinterest'  => 'Pinterest',
        'reddit'     => 'Reddit',
        'tiktok'     => 'TikTok',
        'threads'    => 'Threads',
        'snapchat'   => 'Snapchat',
        'wechat'     => 'WeChat',
        'telegram'   => 'Telegram',
        'tumblr'     => 'Tumblr',
        'qq'         => 'QQ',
        'discord'    => 'Discord',
        'quora'      => 'Quora',
    ];

    return $map[$label] ?? ucfirst(str_replace('-', ' ', $label));
}

// Save social links
function saveSocialLinks($cardId, $business_card_data, $plan_details)
{
    $links = $business_card_data['social_links'] ?? [];

    $maxLinks = $plan_details['no_of_links'] ?? 0;
    if ($maxLinks > 0) {
        $links = array_slice($links, 0, $maxLinks);
    }

    $position = 1;
    $savedUrls = [];

    try {
        // Save social links
        foreach ($links as $link) {

            $type  = strtolower($link['label'] ?? 'url');
            $icon  = $link['icon'] ?? 'fas fa-link fa-md';
            $label = $link['label'] ?? "Custom Text";
            $url   = trim($link['url'] ?? '');

            if (!$url || in_array($url, $savedUrls)) continue;
            $savedUrls[] = $url;

            if ($type === 'x' || $type === 'twitter' || $type === 'x-twitter') {
                if (str_contains(strtolower($url), 'twitter.com')) {
                    $url = str_ireplace('twitter.com', 'x.com', $url);
                }
            }

            $field = new BusinessField();
            $field->card_id  = $cardId;
            $field->icon     = $icon;
            $field->content  = $url;
            $field->position = $position++;

            if ($type === 'x' || $type === 'twitter' || $type === 'x-twitter') {
                $field->type = 'x-twitter';
                $field->icon = 'fab fa-x-twitter fa-md';
                $field->label = 'X';
            } elseif ($type === 'youtube') {
                $field->type = 'url';
                $field->icon = 'fab fa-youtube fa-md';
            } else {
                $field->type = $type;
            }

            $field->label = formatPlatformLabel($label);
            $field->save();
        }

        // Contacts
        foreach (array_filter($business_card_data['contacts'] ?? []) as $contact) {
            BusinessField::create([
                'card_id'  => $cardId,
                'type'     => 'phone',
                'icon'     => 'fas fa-phone fa-md',
                'label'    => 'Phone',
                'content'  => $contact,
                'position' => $position++,
            ]);
        }

        // Emails
        foreach (array_filter($business_card_data['email'] ?? []) as $email) {
            BusinessField::create([
                'card_id'  => $cardId,
                'type'     => 'email',
                'icon'     => 'fas fa-envelope fa-md',
                'label'    => 'Email',
                'content'  => $email,
                'position' => $position++,
            ]);
        }

        // Address
        if (!empty($business_card_data['full_address'])) {
            BusinessField::create([
                'card_id'  => $cardId,
                'type'     => 'address',
                'icon'     => 'fas fa-map-marker-alt fa-md',
                'label'    => 'Address',
                'content'  => $business_card_data['full_address'],
                'position' => $position++,
            ]);
        }
    } catch (\Exception $e) {
        Log::error($e->getMessage());
    }
}
