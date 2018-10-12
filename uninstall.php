<?php
/**
 * Uninstall procedure for CSS JS URL Rewriter.
 *
 * @package CSSJSURLRewriter
 * @subpackage Uninstall
 * @since 2.0.0
 */

// Exit if accessed directly or not on uninstall.
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * Remove options that could store data.
 *
 * @since 2.0.0
 */
delete_option( 'css_js_url_rewriter_data' );
delete_site_option( 'css_js_url_rewriter_data' );

/*
 * Remove options that could store CDN Base URL setting.
 *
 * @since 2.0.0
 */
delete_option( 'css_js_url_rewriter_cdn_url' );
delete_site_option( 'css_js_url_rewriter_cdn_url' );

/*
 * Try to load dependencies if WP_Temporary isn't loaded.
 */
if ( ! class_exists( 'WP_Temporary' ) && file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

/*
 * If WP_Temporary is loaded, clean temporaries used by CSS JS URL Rewriter.
 *
 * @since 2.0.0
 */
if ( class_exists( 'WP_Temporary' ) ) {
	WP_Temporary::delete( 'cjur_processing_queue' );
	WP_Temporary::delete_site( 'cjur_processing_queue' );

	WP_Temporary::clean();
}
