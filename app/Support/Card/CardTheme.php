<?php

namespace App\Support\Card;

/**
 * Which view folder renders a card theme, and its color.
 *
 * Base templates (template-1, template-2, template-3) each live in a single folder
 * under resources/views/templates/cards/{template}; the color is passed dynamically.
 * Existing database theme codes (e.g. template-1-blue, template-2-red) resolve to
 * the base view and their respective color.
 */
class CardTheme
{
    /** Supported card colors */
    public const VALID_COLORS = [
        'black',
        'blue',
        'gray',
        'green',
        'indigo',
        'pink',
        'purple',
        'red',
        'yellow',
    ];

    /** Hex representation of theme colors */
    public const HEX_COLORS = [
        'black'  => '#000000',
        'blue'   => '#2563eb',
        'gray'   => '#4b5563',
        'green'  => '#16a34a',
        'indigo' => '#4f46e5',
        'pink'   => '#db2777',
        'purple' => '#9333ea',
        'red'    => '#dc2626',
        'yellow' => '#ca8a04',
    ];

    public function __construct(
        /** Folder under resources/views/templates/cards */
        public readonly string $view,
        /** Color name, used for Tailwind classes (e.g. bg-{color}-500) */
        public readonly string $color,
        /** Hex representation of the color (for meta theme-color) */
        public readonly string $hex,
    ) {
    }

    public static function resolve(?string $themeCode): self
    {
        $themeCode = (string) $themeCode;

        // 1. Matches template-1, template-2, template-3 with optional -color (e.g. template-1-blue, template-2-red)
        if (preg_match('/^(template-[123])(?:-([a-z]+))?$/', $themeCode, $matches)) {
            $baseView = $matches[1];
            $rawColor = $matches[2] ?? 'blue';
            $color = in_array($rawColor, self::VALID_COLORS, true) ? $rawColor : 'blue';
            $hex = self::HEX_COLORS[$color] ?? '#2563eb';

            return new self($baseView, $color, $hex);
        }

        // 2. Matches personal with optional -color (e.g. personal-blue, personal-red)
        if (preg_match('/^personal(?:-([a-z]+))?$/', $themeCode, $matches)) {
            $rawColor = $matches[1] ?? 'blue';
            $color = in_array($rawColor, self::VALID_COLORS, true) ? $rawColor : 'blue';
            $hex = self::HEX_COLORS[$color] ?? '#2563eb';

            return new self('personal', $color, $hex);
        }

        // 3. Matches ultra-premium with optional -color (e.g. ultra-premium-black, ultra-premium-indigo)
        if (preg_match('/^ultra-premium(?:-(black|indigo|pink|red))?$/', $themeCode, $matches)) {
            $color = $matches[1] ?? 'black';
            $hex = self::HEX_COLORS[$color] ?? '#000000';

            return new self('ultra-premium', $color, $hex);
        }

        // 4. Matches premium-(black|indigo|pink|red) or premium (strictly excludes premium-1)
        if (preg_match('/^premium-(black|indigo|pink|red)$/', $themeCode, $matches) || $themeCode === 'premium') {
            $color = $matches[1] ?? 'black';
            $hex = self::HEX_COLORS[$color] ?? '#000000';

            return new self('premium', $color, $hex);
        }

        // 5. Matches (classic|metro|modern-vcard)-(dark|light)-(color)
        if (preg_match('/^(classic|metro|modern-vcard)-(dark|light)(?:-([a-z]+))?$/', $themeCode, $matches)) {
            $baseView = "{$matches[1]}-{$matches[2]}";
            $rawColor = $matches[3] ?? 'blue';
            $color = in_array($rawColor, self::VALID_COLORS, true) ? $rawColor : 'blue';
            $hex = self::HEX_COLORS[$color] ?? '#2563eb';

            return new self($baseView, $color, $hex);
        }

        // Fallback for custom or standalone codes (e.g. modern, premium-1, gym, doctor, developer)
        return new self($themeCode ?: 'template-1', 'blue', '#2563eb');
    }
}

