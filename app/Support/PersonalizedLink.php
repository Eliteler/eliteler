<?php

namespace App\Support;

class PersonalizedLink
{
    /** Normalize slugs submitted by the classic dashboard. */
    public static function normalize(string $link): string
    {
        $link = str_replace(['https://', 'http://', 'www.'], '', trim($link));
        $link = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $link) ?: '';

        return strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $link));
    }
}
