<?php

namespace App\View\Composers;

use App\Support\Store\StorefrontContext;
use App\Support\Store\StoreTheme;
use Illuminate\View\View;

/**
 * Gives every store theme view (templates.stores.*) the store's addresses and the shared
 * storefront settings, so themes don't each work them out:
 *
 *   $storefront         App\Support\Store\StorefrontContext
 *   $storeUrl           store home (main domain, subdomain or custom domain)
 *   $productUrlPrefix   prefix for product pages ($productUrlPrefix . '/' . $product->id)
 *   $isCustomDomainOrSubdomain
 *   $isRtl              the page language is written right to left
 *   $storeTheme         App\Support\Store\StoreTheme: view folder, light/dark mode and colour of the theme
 */
class StorefrontComposer
{
    public function compose(View $view): void
    {
        $store = $view->getData()['business_card_details'] ?? null;

        if (! is_object($store) || empty($store->card_id)) {
            return;
        }

        $storefront = StorefrontContext::for($store);

        $view->with([
            'storefront'                => $storefront,
            'storeUrl'                  => $storefront->storeUrl,
            'productUrlPrefix'          => $storefront->productUrlPrefix,
            'isCustomDomainOrSubdomain' => $storefront->onOwnDomain,
            'isRtl'                     => in_array(app()->getLocale(), ['ar', 'ur', 'he'], true),
            'storeTheme'                => StoreTheme::resolve($store->theme_code ?? null),
        ]);
    }
}
