<?php
/**
 * \dimadin\WP\Plugin\CSSJSURLRewriter\Clean class.
 *
 * @package CSSJSURLRewriter
 * @since 2.0.0
 */

namespace dimadin\WP\Plugin\CSSJSURLRewriter;

use dimadin\WP\Plugin\CSSJSURLRewriter\Lock;
use dimadin\WP\Plugin\CSSJSURLRewriter\Utils;

/**
 * Class for working with storage for data.
 *
 * @since 2.0.0
 */
class Store {
	/**
	 * Delete stored data.
	 *
	 * @since 2.0.0
	 */
	public static function delete() {
		$func = Utils::get_store_callback( 'delete' );

		$func( 'css_js_url_rewriter_data' );
	}

	/**
	 * Get stored data.
	 *
	 * @since 2.0.0
	 */
	public static function get() {
		$func = Utils::get_store_callback( 'get' );

		return $func( 'css_js_url_rewriter_data' );
	}

	/**
	 * Update stored data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings New data to store.
	 */
	public static function update( $settings ) {
		if ( ! Lock::is_globally() ) {
			$func = Utils::get_store_callback( 'update' );

			$func( 'css_js_url_rewriter_data', $settings );
		}
	}
}
