<?php

/**
 * The CSS JS URL Rewriter Plugin
 *
 * Code used when the plugin is deleted.
 *
 * @package    CSS_JS_URL_Rewriter
 * @subpackage Unistall
 */

/* Exit if accessed directly or not in unistall */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

/*
 * Remove options on uninstallation of plugin.
 *
 * @since 1.0
 */
delete_option( 'css_js_url_rewriter_cdn_url' );
