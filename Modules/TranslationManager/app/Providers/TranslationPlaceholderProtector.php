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

namespace Modules\TranslationManager\App\Providers;

/**
 * TranslationPlaceholderProtector
 * --------------------------------
 * Protects placeholders, HTML tags, numbers, URLs, and emails from being
 * mangled by third-party translation APIs (Google/LibreTranslate/Lingva),
 * by swapping them out for opaque, letters-only sentinel tokens before
 * translation and restoring the exact original substrings afterward.
 *
 * WHY LETTERS-ONLY SENTINELS:
 * Free MT engines frequently localize or reformat plain digits for
 * RTL / Indic / CJK target locales (e.g. turning "0" into an Arabic-Indic
 * or Devanagari digit, or spelling it out as a word). If the sentinel
 * token itself contained a digit (e.g. "##PH0##"), the same bug that
 * corrupts your real numbers would also corrupt the sentinel and make
 * restoration fail. Encoding the placeholder index in letters (a, b, ...,
 * z, aa, ab, ...) instead completely avoids this failure mode.
 *
 * USAGE:
 *   [$protectedText, $tokens] = TranslationPlaceholderProtector::protect($text);
 *   $translated = callYourTranslationProvider($protectedText);
 *   $final = TranslationPlaceholderProtector::restore($translated, $tokens);
 */
class TranslationPlaceholderProtector
{
    /**
     * Sentinel wrapper strings. Deliberately alphabetic only (no digits,
     * no punctuation beyond what regex needs) so translation engines have
     * no reason to reformat, translate, or reorder them.
     */
    private const OPEN = 'zzphzz';
    private const CLOSE = 'zzomzz';

    /**
     * Ordered list of regex patterns to protect. Order matters: more
     * specific / longer patterns (URLs, HTML tags, emails) are matched
     * before generic ones (bare numbers), so a URL containing digits
     * gets consumed whole by the "url" pattern first rather than having
     * its digits separately swallowed by the "number" pattern.
     *
     * @var array<string,string>
     */
    private static array $patterns = [
        // Full URLs (http/https or bare www.) - must run before "number"
        // so digits inside a URL don't get separately protected.
        'url' => '/(https?:\/\/[^\s<>"\']+|www\.[^\s<>"\']+)/i',

        // Bare domains with no scheme/www prefix, e.g. "yourdomain.com",
        // "example.org" used as sample text in documentation strings.
        // Restricted to a curated TLD whitelist to avoid false-positives
        // on ordinary sentence punctuation (e.g. "e.g." never matches
        // since "g" is not a listed TLD). Must run before "number" and
        // after "url"/"email" so it only catches the leftover bare form.
        'bare_domain' => '/\b(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+'
            . '(?:com|net|org|io|co|dev|app|info|biz|gov|edu|ai|me|us|uk|in)\b/i',

        // Email addresses.
        'email' => '/[a-zA-Z0-9_.+\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+/',

        // HTML tags, e.g. <a href="...">, </a>, <strong>, <br/>.
        'html_tag' => '/<\/?[a-zA-Z][a-zA-Z0-9]*(?:\s+[^<>]*)?\/?>/',

        // HTML entities, e.g. &nbsp; &amp; &#39;
        'html_entity' => '/&[a-zA-Z#][a-zA-Z0-9]*;/',

        // Underscore-wrapped constant-style placeholders, e.g. _MAX_,
        // _COUNT_, _TOTAL_. A different convention from :colon / {brace}
        // placeholders, commonly used in translation string templates.
        'underscore_placeholder' => '/_[A-Z][A-Z0-9]*_/',

        // Laravel-style colon placeholders, e.g. :status, :count, :name
        'laravel_colon_var' => '/:[a-zA-Z_][a-zA-Z0-9_]*/',

        // Curly brace placeholders, e.g. {name}, {email}
        'brace_placeholder' => '/\{[a-zA-Z0-9_.]+\}/',

        // printf-style placeholders, e.g. %s, %d, %1$s
        'printf_placeholder' => '/%(?:\d+\$)?[sdif%]/',

        // Bare numbers, including decimals/thousands separators,
        // e.g. 0, 5, 12.5, 1,000, 3.14
        'number' => '/\d+(?:[.,]\d+)*/',
    ];

    /**
     * Replace every protected substring in $text with a sentinel token.
     *
     * @param string $text The raw source string (a single translatable value).
     * @return array{0:string,1:array<int,string>} A tuple of
     *         [protectedText, tokens] where tokens[$i] is the original
     *         substring that sentinel index $i stands for.
     */
    public static function protect(string $text): array
    {
        $tokens = [];

        foreach (self::$patterns as $pattern) {
            $text = preg_replace_callback(
                $pattern,
                function (array $matches) use (&$tokens): string {
                    $tokens[] = $matches[0];
                    $index = count($tokens) - 1;
                    return self::OPEN . self::indexToLetters($index) . self::CLOSE;
                },
                $text
            );
        }

        return [$text, $tokens];
    }

    /**
     * Restore original substrings into a translated string, replacing
     * every sentinel token with its corresponding original value.
     *
     * @param string $translatedText The MT provider's output, still
     *        containing sentinel tokens in place of protected substrings.
     * @param array<int,string> $tokens The tokens array returned by protect().
     * @return string The final string with placeholders/HTML/numbers/etc.
     *         restored to their exact original form.
     */
    public static function restore(string $translatedText, array $tokens): string
    {
        if (empty($tokens)) {
            return $translatedText;
        }

        // Some MT engines insert a stray space just inside/outside the
        // sentinel wrapper (e.g. "zzphzz a zzomzz"); normalize the most
        // common variants before the strict match below. Case-insensitive
        // since some providers uppercase/lowercase the entire response,
        // sentinel wrapper included.
        $normalized = preg_replace(
            ['/' . preg_quote(self::OPEN, '/') . '\s+/i', '/\s+' . preg_quote(self::CLOSE, '/') . '/i'],
            [self::OPEN, self::CLOSE],
            $translatedText
        );

        $pattern = '/' . preg_quote(self::OPEN, '/') . '([a-zA-Z]+?)' . preg_quote(self::CLOSE, '/') . '/i';

        $restored = preg_replace_callback(
            $pattern,
            function (array $matches) use ($tokens): string {
                $index = self::lettersToIndex(strtolower($matches[1]));
                return $tokens[$index] ?? $matches[0];
            },
            $normalized
        );

        return $restored ?? $translatedText;
    }

    /**
     * Determine whether a string has any content worth sending to a
     * translation API at all.
     *
     * @param string $text The raw source string.
     * @return bool False for empty/whitespace-only strings, or strings
     *         made up entirely of protected substrings (e.g. a bare
     *         ":status" or a lone URL) - these can be copied through
     *         untouched, saving an API call.
     */
    public static function hasTranslatableContent(string $text): bool
    {
        if (trim($text) === '') {
            return false;
        }

        [$protectedText] = self::protect($text);

        $pattern = '/' . preg_quote(self::OPEN, '/') . '[a-zA-Z]+?' . preg_quote(self::CLOSE, '/') . '/';
        $remainder = preg_replace($pattern, '', $protectedText);

        return trim((string) $remainder) !== '';
    }

    /**
     * Encode a zero-based integer index as a letters-only string, using
     * a bijective base-26 scheme so every index maps to a unique,
     * digit-free label: 0 -> "a", 1 -> "b", ..., 25 -> "z", 26 -> "aa",
     * 27 -> "ab", and so on indefinitely.
     *
     * @param int $index Zero-based placeholder index.
     * @return string The letters-only encoding.
     */
    private static function indexToLetters(int $index): string
    {
        $letters = '';
        $n = $index + 1; // shift to 1-based so there is no empty-string case

        while ($n > 0) {
            $n--;
            $letters = chr(97 + ($n % 26)) . $letters;
            $n = intdiv($n, 26);
        }

        return $letters;
    }

    /**
     * Decode a letters-only label (produced by indexToLetters) back into
     * its zero-based integer index.
     *
     * @param string $letters Lowercase letters-only label, e.g. "a", "ab".
     * @return int The zero-based placeholder index.
     */
    private static function lettersToIndex(string $letters): int
    {
        $index = 0;

        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord($char) - 97 + 1);
        }

        return $index - 1;
    }
}
