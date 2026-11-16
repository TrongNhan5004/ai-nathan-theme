<?php

/**
 * Theme Autoloader & Bootstrap
 *
 * - PSR-4 autoloader for AiNathan\ namespace
 * - Registers all modules from inc/ subdirectories
 * - Procedural files in setup/ are loaded directly
 *
 * Namespace mapping:
 *   AiNathan\Core\*     → inc/Core/*.php      (framework: ViteAssets, Runtime, Features, L10n, ThemeSetup)
 *   AiNathan\Admin\*    → inc/Admin/*.php     (admin UI: Customizer, ThemeOptions, Typography)
 *   AiNathan\Api\*      → inc/Api/*.php       (REST endpoints)
 *   AiNathan\Hooks\*    → inc/Hooks/*.php     (action/filter glue)
 *   AiNathan\Account\*  → inc/Account/*.php   (My Account feature)
 *   AiNathan\Shop\*     → inc/Shop/*.php      (shop page)
 *   AiNathan\Cart\*     → inc/Cart/*.php      (cart page)
 *   AiNathan\Checkout\* → inc/Checkout/*.php  (checkout + order confirmation)
 *
 * Bootstrap order matters:
 *   1. ViteAssets first — everything else depends on it for asset enqueuing.
 *   2. Runtime second — attaches the frontend config to the ViteAssets handle.
 *   3. ThemeSetup third — registers post types, block categories, theme supports.
 *   4. Admin + feature modules — register hooks; order within a group is flexible.
 *   5. REST routes last — hooked onto rest_api_init so actual registration is deferred.
 */

defined('ABSPATH') || exit;

// PSR-4 Autoloader: AiNathan\ → inc/
spl_autoload_register(function (string $class): void {
    $prefix = 'AiNathan\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = AI_NATHAN_THEME_DIR . '/inc/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load procedural setup files (early boot)
foreach (glob(AI_NATHAN_THEME_DIR . '/inc/setup/*.php') as $file) {
    require_once $file;
}

// ---- Bootstrap: register all modules ----

// Core
// Features and L10n are passive registries with no hooks of their own — they are
// resolved on demand by Runtime, which publishes them to the frontend.
AiNathan\Core\ViteAssets::register();
AiNathan\Core\Runtime::register();
AiNathan\Core\ThemeSetup::register();
AiNathan\Core\Comments::register();

// Admin
AiNathan\Admin\Customizer::register();
AiNathan\Admin\ThemeOptions::register();
AiNathan\Admin\Typography::register();

// Hooks
AiNathan\Hooks\CacheInvalidation::register();

// API
add_action('rest_api_init', [AiNathan\Api\ProductFilterApi::class, 'register']);
add_action('rest_api_init', [AiNathan\Api\TypographyApi::class, 'register']);
add_action('rest_api_init', [AiNathan\Api\SearchApi::class, 'register']);

// Account
AiNathan\Account\AccountAssets::register();

// Product (single product page)
AiNathan\Product\ProductShortcode::register();
AiNathan\Product\ProductAssets::register();
AiNathan\Product\RelatedProductsShortcode::register();

// Shop
AiNathan\Shop\ShopAssets::register();
AiNathan\Shop\Wishlist::register();

// Cart
AiNathan\Cart\HeaderCartButton::register();
AiNathan\Cart\CartAssets::register();
AiNathan\Cart\MiniCart::register();

// Checkout
AiNathan\Checkout\CheckoutSettings::register();
AiNathan\Checkout\CheckoutShortcode::register();
AiNathan\Checkout\OrderConfirmationShortcode::register();
AiNathan\Checkout\CheckoutValidation::register();
AiNathan\Checkout\CheckoutAssets::register();
AiNathan\Checkout\ClassicCheckoutFields::register();
