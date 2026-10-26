{{--
    Smart parser for mixed scripts and styles.
    Preserves existing <script>/<style> blocks and wraps loose code automatically.
--}}
@php
    $customCss = trim($business_card_details->custom_css ?? '');
    $customJs  = trim($business_card_details->custom_js ?? '');
    $themeCss  = trim($business_card_details->theme_css ?? '');
    $themeJs   = trim($business_card_details->theme_js ?? '');

    // Function to parse and format mixed JavaScript
    $processSmartJs = function($code) {
        if (empty($code)) return '';

        // Strip harmful functions that trigger XSS attacks
        $blocked = [
            '/alert\s*\([^)]*\);?/i',
            '/confirm\s*\([^)]*\);?/i',
            '/prompt\s*\([^)]*\);?/i',
            '/document\.cookie/i',
        ];
        $code = preg_replace($blocked, '', $code);

        // Regex pattern to match complete tags: script, noscript, comments, and void tags
        $tagPattern = '/<!--[\s\S]*?-->|<script\b[^>]*>[\s\S]*?<\/script>|<noscript\b[^>]*>[\s\S]*?<\/noscript>|<(meta|link|img)\b[^>]*\/?>/i';

        // Extract all existing tag blocks
        preg_match_all($tagPattern, $code, $matches);
        $taggedBlocks = $matches[0];

        // Isolate remaining loose JS code that is not wrapped in any tag
        $looseJs = trim(preg_replace($tagPattern, '', $code));

        // Combine existing tagged blocks
        $output = implode("\n", $taggedBlocks);

        // If loose code exists, wrap it inside its own <script> block
        if (!empty($looseJs)) {
            $output .= "\n<script>\n" . $looseJs . "\n</script>";
        }

        return trim($output);
    };

    // Function to parse and format mixed CSS
    $processSmartCss = function($code) {
        if (empty($code)) return '';

        // Regex pattern to match existing <style> blocks
        $tagPattern = '/<style\b[^>]*>[\s\S]*?<\/style>/i';

        // Extract all existing style blocks
        preg_match_all($tagPattern, $code, $matches);
        $styledBlocks = $matches[0];

        // Isolate loose CSS that has no <style> tag
        $looseCss = trim(preg_replace($tagPattern, '', $code));

        $output = implode("\n", $styledBlocks);

        // If loose CSS exists, wrap it inside its own <style> block
        if (!empty($looseCss)) {
            $output .= "\n<style>\n" . $looseCss . "\n</style>";
        }

        return trim($output);
    };
@endphp

{{-- Render Custom CSS --}}
{!! $processSmartCss($customCss) !!}

{{-- Render Theme CSS --}}
{!! $processSmartCss($themeCss) !!}

{{-- Render Custom JS (Handles TikTok, Analytics and loose JS properly) --}}
{!! $processSmartJs($customJs) !!}

{{-- Render Theme JS --}}
{!! $processSmartJs($themeJs) !!}