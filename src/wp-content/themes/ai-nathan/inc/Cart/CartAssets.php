<?php

namespace AiNathan\Cart;

defined('ABSPATH') || exit;

/**
 * Enqueue cart React app on WooCommerce cart page.
 */
class CartAssets
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * Enqueue cart assets.
     */
    public static function enqueue(): void
    {
        if (!is_cart() && !is_page('cart')) {
            return;
        }

        \AiNathan\Core\ViteAssets::enqueue(
            'ai-nathan-cart',
            'src/wp-content/themes/ai-nathan/src/js/frontend/cart/index.jsx'
        );
    }
}
