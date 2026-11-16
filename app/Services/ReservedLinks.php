<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

/**
 * Card / store links that can't be used because a page of the site already answers on
 * that path (login, user, admin, api, ...): a card with such a link would never be shown.
 * Built from the router, so new pages are covered automatically.
 */
class ReservedLinks
{
    public const MESSAGE = 'This link is reserved. Please choose another one!';

    private static ?array $reserved = null;

    /** First path segments of every registered route without a domain. */
    public static function all(): array
    {
        if (self::$reserved === null) {
            self::$reserved = collect(Route::getRoutes()->getRoutes())
                ->filter(fn ($route) => ! $route->getDomain())
                ->map(fn ($route) => strtolower(explode('/', trim($route->uri(), '/'))[0]))
                ->filter(fn ($segment) => $segment !== '' && ! str_contains($segment, '{'))
                ->unique()
                ->values()
                ->all();
        }

        return self::$reserved;
    }

    public static function contains(?string $link): bool
    {
        return in_array(strtolower(trim((string) $link)), self::all(), true);
    }
}
