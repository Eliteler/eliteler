<?php
$files = array_merge(
    glob(__DIR__ . '/resources/views/templates/*.blade.php'),
    glob(__DIR__ . '/resources/views/templates/store/*/*.blade.php'),
    glob(__DIR__ . '/resources/views/templates/includes/vcard/modern/*.blade.php')
);

foreach ($files as $file) {
    if (!is_file($file)) continue;

    $content = file_get_contents($file);

    // Skip if not containing the specific replaced pattern
    if (strpos($content, '@if(!empty($business_card_details->title2))') === false) {
        continue;
    }

    echo "Processing $file...\n";

    // Regular Expression to find the replaced title tag
    // It looks for a tag containing @if(!empty($business_card_details->title2))
    $content = preg_replace_callback(
        '/<([a-z1-6]+)([^>]*)>\s*@if\(!empty\(\$business_card_details->title2\)\)\s*\{\{\s*\$business_card_details->title2\s*\}\}\s*<br>\s*@endif\s*\{\{\s*\$business_card_details->title\s*\}\}\s*<\/\1>/s',
        function ($matches) {
            $tag = $matches[1];
            $attributes = $matches[2];
            return "@if(!empty(\$business_card_details->title2))\n<{$tag}{$attributes} style=\"margin-bottom: 0px; padding-bottom: 0px;\">{{ \$business_card_details->title2 }}</{$tag}>\n@endif\n<{$tag}{$attributes}>{{ \$business_card_details->title }}</{$tag}>";
        },
        $content
    );

    // Now for subtitle
    $content = preg_replace_callback(
        '/<([a-z1-6]+)([^>]*)>\s*@if\(!empty\(\$business_card_details->subtitle2\)\)\s*\{\{\s*\$business_card_details->subtitle2\s*\}\}\s*<br>\s*@endif\s*\{\{\s*\$card_details->sub_title\s*\}\}\s*<\/\1>/s',
        function ($matches) {
            $tag = $matches[1];
            $attributes = $matches[2];
            return "@if(!empty(\$business_card_details->subtitle2))\n<{$tag}{$attributes} style=\"margin-bottom: 5px; padding-bottom: 0px;\">{{ \$business_card_details->subtitle2 }}</{$tag}>\n@endif\n<{$tag}{$attributes}>{{ \$card_details->sub_title }}</{$tag}>";
        },
        $content
    );
    
    // Store template subtitle logic uses $business_card_details instead of $card_details sometimes
    $content = preg_replace_callback(
        '/<([a-z1-6]+)([^>]*)>\s*@if\(!empty\(\$business_card_details->subtitle2\)\)\s*\{\{\s*\$business_card_details->subtitle2\s*\}\}\s*<br>\s*@endif\s*\{\{\s*\$business_card_details->sub_title\s*\}\}\s*<\/\1>/s',
        function ($matches) {
            $tag = $matches[1];
            $attributes = $matches[2];
            return "@if(!empty(\$business_card_details->subtitle2))\n<{$tag}{$attributes} style=\"margin-bottom: 5px; padding-bottom: 0px;\">{{ \$business_card_details->subtitle2 }}</{$tag}>\n@endif\n<{$tag}{$attributes}>{{ \$business_card_details->sub_title }}</{$tag}>";
        },
        $content
    );

    file_put_contents($file, $content);
}
echo "Done.\n";
