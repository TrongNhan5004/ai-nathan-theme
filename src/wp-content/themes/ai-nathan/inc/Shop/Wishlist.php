<?php

namespace AiNathan\Shop;

use AiNathan\Admin\ThemeOptions;
use AiNathan\Core\ViteAssets;

defined('ABSPATH') || exit;

/**
 * Standalone wishlist page backed by the browser wishlist used by product cards.
 */
class Wishlist
{
    private const PAGE_OPTION = 'ai_nathan_wishlist_page_id';
    private const PAGE_SLUG   = 'wishlist';

    public static function register(): void
    {
        add_action('init', [self::class, 'registerRenderers']);
        add_action('init', [self::class, 'ensurePage'], 20);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function registerRenderers(): void
    {
        add_shortcode('ai_nathan_wishlist', [self::class, 'renderApp']);
        add_shortcode('ai_nathan_wishlist_link', [self::class, 'renderHeaderLink']);
    }

    /**
     * Create the page once for existing installations as well as new activations.
     */
    public static function ensurePage(): void
    {
        if (!ThemeOptions::isWishlistEnabled() || wp_installing()) {
            return;
        }

        $page_id = absint(get_option(self::PAGE_OPTION, 0));
        $page_status = $page_id > 0 ? get_post_status($page_id) : false;
        if ($page_status !== false && $page_status !== 'trash') {
            return;
        }

        $existing = get_page_by_path(self::PAGE_SLUG, OBJECT, 'page');
        if ($existing instanceof \WP_Post && $existing->post_status !== 'trash') {
            update_option(self::PAGE_OPTION, $existing->ID);
            return;
        }

        $page_id = wp_insert_post([
            'post_title'   => __('Wishlist', 'ai-nathan'),
            'post_name'    => self::PAGE_SLUG,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '',
        ], true);

        if (!is_wp_error($page_id)) {
            update_option(self::PAGE_OPTION, (int) $page_id);
        }
    }

    public static function getPageUrl(): string
    {
        $page_id = absint(get_option(self::PAGE_OPTION, 0));
        if ($page_id > 0) {
            $url = get_permalink($page_id);
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        $page = get_page_by_path(self::PAGE_SLUG, OBJECT, 'page');
        if ($page instanceof \WP_Post) {
            return (string) get_permalink($page->ID);
        }

        return home_url('/' . self::PAGE_SLUG . '/');
    }

    public static function enqueue(): void
    {
        if (!ThemeOptions::isWishlistEnabled() || !self::isWishlistPage()) {
            return;
        }

        ViteAssets::enqueue(
            'ai-nathan-wishlist',
            'src/wp-content/themes/ai-nathan/src/js/frontend/wishlist.jsx'
        );
    }

    public static function renderApp(): string
    {
        if (!ThemeOptions::isWishlistEnabled()) {
            return '<div class="az-wishlist__empty"><p>'
                . esc_html__('The wishlist is currently unavailable.', 'ai-nathan')
                . '</p></div>';
        }

        $config = [
            'restUrl' => esc_url_raw(rest_url('ai-nathan/v1')),
            'shopUrl' => esc_url_raw(wc_get_page_permalink('shop')),
            'i18n'    => [
                'loading'      => __('Loading your wishlist…', 'ai-nathan'),
                'emptyTitle'   => __('Your wishlist is empty', 'ai-nathan'),
                'emptyMessage' => __('Save products you love and they will appear here.', 'ai-nathan'),
                'shopButton'   => __('Browse products', 'ai-nathan'),
                'error'        => __('We could not load your wishlist. Please try again.', 'ai-nathan'),
                'retry'        => __('Try again', 'ai-nathan'),
                'clear'        => __('Clear wishlist', 'ai-nathan'),
                'clearTitle'    => __('Clear your wishlist?', 'ai-nathan'),
                'clearMessage'  => __('This will remove every saved product. This action cannot be undone.', 'ai-nathan'),
                'removeTitle'   => __('Remove from wishlist?', 'ai-nathan'),
                'removeMessage' => __('Remove “%s” from your wishlist?', 'ai-nathan'),
                'removeLabel'   => __('Remove %s from wishlist', 'ai-nathan'),
                'cancel'        => __('Cancel', 'ai-nathan'),
                'confirmClear'  => __('Clear wishlist', 'ai-nathan'),
                'confirmRemove' => __('Remove product', 'ai-nathan'),
                'item'          => __('item saved', 'ai-nathan'),
                'items'        => __('items saved', 'ai-nathan'),
            ],
        ];

        return sprintf(
            '<div id="ai-nathan-wishlist" class="az-wishlist" data-config="%s"></div>',
            esc_attr(wp_json_encode($config))
        );
    }

    public static function renderHeaderLink(): string
    {
        if (!ThemeOptions::isWishlistEnabled()) {
            return '';
        }

        return sprintf(
            '<a class="az-header-wishlist" href="%1$s" aria-label="%2$s">'
                . '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
                . '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'
                . '</svg>'
                . '<span class="screen-reader-text">%2$s</span>'
                . '<span class="az-header-wishlist__count" data-wishlist-count hidden>0</span>'
                . '</a>',
            esc_url(self::getPageUrl()),
            esc_attr__('Wishlist', 'ai-nathan')
        );
    }

    private static function isWishlistPage(): bool
    {
        $page_id = absint(get_option(self::PAGE_OPTION, 0));

        return is_page(self::PAGE_SLUG) || ($page_id > 0 && is_page($page_id));
    }
}
