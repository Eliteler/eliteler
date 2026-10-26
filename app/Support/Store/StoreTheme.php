<?php

namespace App\Support\Store;

/**
 * Which view folder renders a store theme, and its colour.
 *
 * The 16 "modern store" theme codes (modern-store-{light|dark}-{colour}) share one folder,
 * templates/stores/modern-store; the light/dark mode and the colour come from the code.
 * Every other theme code is its own folder.
 *
 * Shared with every store view as $storeTheme by StorefrontComposer.
 */
class StoreTheme
{
    /** Theme-code colour => [Tabler colour class, hex for the browser's theme colour] */
    private const COLOURS = [
        'blue'   => ['blue', '#206bc4'],
        'gray'   => ['secondary', '#616876'],
        'green'  => ['green', '#2fb344'],
        'indigo' => ['indigo', '#4263eb'],
        'pink'   => ['pink', '#d6336c'],
        'purple' => ['purple', '#ae3ec9'],
        'red'    => ['red', '#d63939'],
        'yellow' => ['yellow', '#f59f00'],
    ];

    private function __construct(
        /** Folder under resources/views/templates/stores */
        public readonly string $view,
        /** "light" or "dark" (modern store); "light" for other themes */
        public readonly string $mode,
        /** Tabler colour class name, used as btn-{color}, text-{color}, bg-{color}… */
        public readonly string $color,
        /** The same colour as a hex value (meta theme-color) */
        public readonly string $hex,
    ) {
    }

    public static function resolve(?string $themeCode): self
    {
        $themeCode = (string) $themeCode;

        if (preg_match('/^modern-store-(light|dark)-([a-z]+)$/', $themeCode, $matches)) {
            [$color, $hex] = self::COLOURS[$matches[2]] ?? self::COLOURS['blue'];

            return new self('modern-store', $matches[1], $color, $hex);
        }

        return new self($themeCode, 'light', ...self::COLOURS['blue']);
    }

    public function isDark(): bool
    {
        return $this->mode === 'dark';
    }
}
