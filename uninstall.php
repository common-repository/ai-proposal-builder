<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
// Delete pages
$page_id = get_option('bitcraftx_aipb_page_id');
if ($page_id) {
    wp_delete_post( $page_id, true );
}

// Delete custom post types
unregister_post_type('bitcraftx_aipb_cta');
unregister_post_type('bitcraftx_aipb_testimonial');
// Unregister the post type and taxonomy
unregister_post_type('bitcraftx_aipb_portfolio');
unregister_taxonomy('bitcraftx_aipb_portfolio_category');