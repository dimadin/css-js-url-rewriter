<?php
/**
 * \dimadin\WP\Plugin\CSSJSURLRewriter\Singleton trait.
 *
 * @package CSSJSURLRewriter
 * @since 2.0.0
 */

namespace dimadin\WP\Plugin\CSSJSURLRewriter;

/**
 * Singleton pattern.
 *
 * @link http://www.sitepoint.com/using-traits-in-php-5-4/
 */
trait Singleton {
	/**
	 * Instantiate called class.
	 *
	 * @since 2.0.0
	 *
	 * @staticvar bool|object $instance
	 *
	 * @return object $instance Instance of called class.
	 */
	public static function get_instance() {
		static $instance = false;

		if ( false === $instance ) {
			$instance = new static();
		}

		return $instance;
	}
}
