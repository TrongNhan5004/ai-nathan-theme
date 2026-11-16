<?php

namespace AiNathan\Core;

defined('ABSPATH') || exit;

/**
 * Frontend string table.
 *
 * Text rendered by JS used to live inside the bundle, which made it
 * untranslatable and un-overridable per client. Every such string is declared
 * here instead, passed to the browser by Runtime and read through azText().
 *
 * Keys are "namespace.name". Placeholders are %s and are substituted in JS, so
 * a translation may move the placeholder but must keep it.
 *
 * Filter:
 *   ai_nathan_i18n_strings (array) — add or replace strings from a child theme.
 */
class L10n
{
    /**
     * @return array<string, string>
     */
    public static function strings(): array
    {
        $strings = [
            // Search typeahead
            'search.no_results'      => __('No results for “%s”', 'ai-nathan'),
            'search.group_products'  => __('Products', 'ai-nathan'),
            'search.group_posts'     => __('Blog Posts', 'ai-nathan'),
            'search.view_all'        => __('View all results for %s', 'ai-nathan'),
            'search.badge_sale'      => __('Sale', 'ai-nathan'),
            'search.badge_oos'       => __('Out of stock', 'ai-nathan'),
            'search.sku'             => __('SKU: %s', 'ai-nathan'),

            // Add to cart
            'cart.adding'            => __('Adding…', 'ai-nathan'),
            'cart.added'             => __('Added!', 'ai-nathan'),
            'cart.add_success'       => __('Product added to cart', 'ai-nathan'),
            'cart.add_success_many'  => __('%s products added to cart', 'ai-nathan'),
            'cart.add_failed'        => __('Failed to add to cart', 'ai-nathan'),
            'cart.select_options'    => __('Please select all options before adding to cart.', 'ai-nathan'),
            'cart.grouped_empty'     => __('Choose a quantity for at least one item.', 'ai-nathan'),

            // Mini cart.
            //
            // The item counter is two separate strings rather than _n(): the
            // count is only known in the browser, so PHP cannot pick the form.
            // Locales with more than two plural forms need the
            // ai_nathan_i18n_strings filter.
            'minicart.title'          => __('Cart', 'ai-nathan'),
            'minicart.items_one'      => __('%s item in cart', 'ai-nathan'),
            'minicart.items_many'     => __('%s items in cart', 'ai-nathan'),
            'minicart.empty'          => __('Your cart is empty', 'ai-nathan'),
            'minicart.keep_shopping'  => __('Continue shopping', 'ai-nathan'),
            'minicart.subtotal'       => __('Subtotal', 'ai-nathan'),
            'minicart.view_cart'      => __('View cart', 'ai-nathan'),
            'minicart.checkout'       => __('Checkout', 'ai-nathan'),
            'minicart.remove'         => __('Remove %s from cart', 'ai-nathan'),
            'minicart.increase'       => __('Increase quantity of %s', 'ai-nathan'),
            'minicart.decrease'       => __('Decrease quantity of %s', 'ai-nathan'),
            'minicart.quantity'       => __('Quantity of %s', 'ai-nathan'),
            'minicart.update_failed'  => __('Could not update the cart', 'ai-nathan'),
            'minicart.ship_progress'  => __('Spend %s more to get free shipping', 'ai-nathan'),
            'minicart.ship_reached'   => __('You have earned free shipping', 'ai-nathan'),
            'minicart.cross_sell'     => __('You may also like', 'ai-nathan'),

            // Shared
            'common.close'           => __('Close', 'ai-nathan'),
        ];

        /**
         * Filter the frontend string table.
         *
         * @param array<string, string> $strings
         */
        $strings = (array) apply_filters('ai_nathan_i18n_strings', $strings);

        return array_map('strval', $strings);
    }
}
