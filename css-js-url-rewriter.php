<?php
/**
 * Plugin Name: CSS JS URL Rewriter
 * Description: Rewrite URLs of CSS and JavaScript files to use CDN.
 * Author:      Milan Dinić
 * Author URI:  https://milandinic.com/
 * Version:     2.0.0-beta-1
 * Text Domain: css-js-url-rewriter
 * Domain Path: /languages/
 * Network: True
 *
 * @package CSSJSURLRewriter
 */

// Load dependencies.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

/**
 * Version of CSS JS URL Rewriter plugin.
 *
 * @since 2.0.0
 * @var string
 */
define( 'CSSJSURLREWRITER_VERSION', '2.0.0-beta-1' );

/*
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 */
add_action( 'plugins_loaded', [ 'dimadin\WP\Plugin\CSSJSURLRewriter\Main', 'get_instance' ], 10 );
