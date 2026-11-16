<?php
$files = array_merge(
    glob(__DIR__ . '/resources/views/templates/*.blade.php'),
    glob(__DIR__ . '/resources/views/templates/store/*/*.blade.php'),
    glob(__DIR__ . '/resources/views/templates/includes/vcard/modern/*.blade.php')
);

$count = 0;
foreach ($files as $file) {
    if (!is_file($file)) continue;

    $originalContent = file_get_contents($file);
    $content = $originalContent;

    // Pattern to match title2 block, title block, intermediate logic, subtitle2 block
    // Using {0,300} to allow up to 300 characters between title and subtitle2 blocks.
    $pattern = '/(@if\(\!empty\(\$business_card_details->title2\)\)(?:.|\n)*?@endif\s*)(<([a-z1-6]+)(?:.|\n)*?\{\{\s*\$business_card_details->title\s*\}\}(?:.|\n)*?<\/\3>\s*)(.{0,300}?)(@if\(\!empty\(\$business_card_details->subtitle2\)\)(?:.|\n)*?@endif\s*)/is';
    
    // First, verify if pattern exists
    if (preg_match($pattern, $content)) {
        $content = preg_replace_callback($pattern, function($matches) {
            $title2Block = $matches[1];
            $titleBlock = $matches[2];
            $intermediate = $matches[4]; // might be empty or some whitespaces or comments
            $subtitle2Block = $matches[5];
            
            // Reorder: title2 -> subtitle2 -> title -> intermediate
            return $title2Block . $subtitle2Block . $titleBlock . $intermediate; // Added newline for safety
        }, $content);
        
        if ($originalContent !== $content) {
            file_put_contents($file, $content);
            echo "Reordered in: " . basename($file) . "\n";
            $count++;
        }
    }
}
echo "Total templates processed: $count\n";
