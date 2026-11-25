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

namespace App\Services;

use App\PersonalizedLinkExclude;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Card / store links that can't be used because a page of the site already answers on
 * that path (login, user, admin, api, ...): a card with such a link would never be shown.
 * Database exclusions are always reserved. The router also covers newly registered routes.
 */
class ReservedLinks
{
    public const MESSAGE = 'This link is reserved. Please choose another one!';

    /** Database exclusions and first path segments of registered site routes. */
    public static function all(): array
    {
        return PersonalizedLinkExclude::query()->pluck('keyword')
            ->merge(self::routeSegments())
            ->unique()
            ->values()
            ->all();
    }

    public static function contains(?string $link): bool
    {
        // Laravel's generated URLs can include literal path, query, and fragment separators.
        $link = trim((string) $link);
        $path = rawurldecode(substr($link, 0, strcspn($link, '?#')));
        $segment = strtolower(explode('/', ltrim($path, '/'), 2)[0]);

        return $segment !== '' && (
            PersonalizedLinkExclude::query()->where('keyword', $segment)->value('keyword') === $segment
            || self::routeSegments()->containsStrict($segment)
        );
    }

    private static function routeSegments(): Collection
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => ! $route->getDomain())
            ->map(fn ($route) => strtolower(explode('/', trim($route->uri(), '/'), 2)[0]))
            ->filter(fn ($segment) => $segment !== '' && ! str_contains($segment, '{'));
    }
}
