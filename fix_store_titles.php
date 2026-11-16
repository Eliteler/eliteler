<?php
$files = glob(__DIR__ . '/resources/views/templates/store/*/*.blade.php');

$count = 0;
foreach ($files as $file) {
    if (!is_file($file)) continue;

    $content = file_get_contents($file);

    // Pattern to look for the subtitle2 injected logic in store templates:
    // @if(!empty($business_card_details->subtitle2)){{ $business_card_details->subtitle2 }} <br>@endif{{ $business_card_details->sub_title }}

    $pattern = '/@if\(\!empty\(\$business_card_details->subtitle2\)\)\{\{\s*\$business_card_details->subtitle2\s*\}\}\s*<br>\s*@endif\{\{\s*\$business_card_details->sub_title\s*\}\}/s';
    
    // Also consider variations with spaces
    $pattern2 = '/@if\(!empty\(\$business_card_details->subtitle2\)\)\s*\{\{\s*\$business_card_details->subtitle2\s*\}\}\s*<br>\s*@endif\s*\{\{\s*\$business_card_details->sub_title\s*\}\}/s';

    $replacement = "@if(!empty(\$business_card_details->title2))\n{{ \$business_card_details->title2 }} <br>\n@endif\n@if(!empty(\$business_card_details->subtitle2))\n{{ \$business_card_details->subtitle2 }} <br>\n@endif\n{{ \$business_card_details->sub_title }}";

    $newContent = preg_replace($pattern2, $replacement, $content);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated store template: " . basename(dirname($file)) . "/" . basename($file) . "\n";
        $count++;
    }
}
echo "Total store templates processed: $count\n";
