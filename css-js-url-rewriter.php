<?php

/**
 * The CSS JS URL Rewriter Plugin
 *
 * Rewrite URLs of CSS and JavaScript files to use CDN.
 *
 * @package    CSS_JS_URL_Rewriter
 * @subpackage Main
 */

/**
 * Plugin Name: CSS JS URL Rewriter
 * Plugin URI:  http://blog.milandinic.com/wordpress/plugins/css-js-url-rewriter/
 * Description: Rewrite URLs of CSS and JavaScript files to use CDN.
 * Author:      Milan Dinić
 * Author URI:  http://blog.milandinic.com/
 * Version:     0.1-beta-1
 * Text Domain: css-js-url-rewriter
 * Domain Path: /languages/
 * License:     GPL
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 */
add_action( 'plugins_loaded', array( 'CSS_JS_URL_Rewriter', 'get_instance' ), 10 );

/**
 * CSS JS URL Rewriter main class.
 *
 * Rewrite URLs of CSS and JavaScript files to use CDN.
 *
 * @since 1.0
 */
class CSS_JS_URL_Rewriter {
	/**
	 * URL base of remote site.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @var string
	 */
	public $cdn_url;

	/**
	 * URL base of local site.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @var string
	 */
	public $site_url;

	/**
	 * Set class properties and add main methods to appropriate hooks.
	 * 
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		// Setup plugin
		add_action( 'init',                              array( $this, 'init'           )        );

		// Include and load admin class
		add_action( 'admin_menu',                        array( $this, 'admin_menu'     ), 1     );

		// Register plugins action links filter
		add_filter( 'plugin_action_links',               array( $this, 'action_links'   ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'action_links'   ), 10, 2 );
	}

	/**
	 * Initialize CSS_JS_URL_Rewriter object.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return CSS_JS_URL_Rewriter $instance Instance of CSS_JS_URL_Rewriter class.
	 */
	public static function &get_instance() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new CSS_JS_URL_Rewriter;
		}

		return $instance;
	}

	/**
	 * Set class properties and add methods to appropriate hooks.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function init() {
		// Get CDN URL from options
		$this->cdn_url = get_option( 'css_js_url_rewriter_cdn_url' );

		// If there is no CDN URL, don't proceed
		if ( ! $this->cdn_url ) {
			return;
		}

		// Remove last trailing slash from CDN URL
		$this->cdn_url = rtrim( $this->cdn_url, '/' );

		// Set site URL witout trailing slash
		$this->site_url = site_url();

		// Register hooks
		add_filter( 'script_loader_src', array( $this, 'replace' ), 10, 2 );
		add_filter( 'style_loader_src',  array( $this, 'replace' ), 10, 2 );
	}

	/**
	 * Load admin class.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function admin_menu() {
		require_once dirname( __FILE__ ) . '/inc/class-css-js-url-rewriter-admin.php';
		CSS_JS_URL_Rewriter_Admin::get_instance();
	}

	/**
	 * Add action links to plugins page.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param array  $links       Existing plugin's action links.
	 * @param string $plugin_file Path to the plugin file.
	 * @return array $links New plugin's action links.
	 */
	public function action_links( $links, $plugin_file ) {
		// Set basename
		$basename = plugin_basename( __FILE__ );

		// Check if it is for this plugin
		if ( $basename != $plugin_file ) {
			return $links;
		}

		// Load translations
		$this->load_textdomain();

		// Add new links
		$links['donate']   = '<a href="http://blog.milandinic.com/donate/">' . __( 'Donate', 'css-js-url-rewriter' ) . '</a>';
		$links['wpdev']    = '<a href="http://blog.milandinic.com/wordpress/custom-development/">' . __( 'WordPress Developer', 'css-js-url-rewriter' ) . '</a>';
		$links['premiums'] = '<strong><a href="https://shop.milandinic.com/">' . __( 'Premium WordPress Plugins', 'css-js-url-rewriter' ) . '</a></strong>';

		return $links;
	}

	/**
	 * Replace URL for local file to remote one.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @param string $src    Item loader source path.
	 * @param string $handle Item handle.
	 * @return string $src Updated source path.
	 */
	public function replace( $src, $handle ) {
		$src = str_replace( $this->site_url, $this->cdn_url, $src );

		return $src;
	}

	/**
	 * Load textdomain for internationalization.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function load_textdomain() {
		// If translation isn't loaded, load it
		if ( ! is_textdomain_loaded( 'css-js-url-rewriter' ) ) {
			load_plugin_textdomain( 'css-js-url-rewriter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}
	}
}
