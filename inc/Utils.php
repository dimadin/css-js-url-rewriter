<?php
/**
 * \dimadin\WP\Plugin\CSSJSURLRewriter\Utils class.
 *
 * @package CSSJSURLRewriter
 * @since 2.0.0
 */

namespace dimadin\WP\Plugin\CSSJSURLRewriter;

use Exception;

/**
 * Class with various utility methods.
 *
 * @since 2.0.0
 */
class Utils {
	/**
	 * Get initial store data.
	 *
	 * @since 2.0.0
	 *
	 * @return array $data Initial store data.
	 */
	public static function init_stored_data() {
		$data = [
			'db_version' => CSSJSURLREWRITER_VERSION,
			'active'     => [],
			'inactive'   => [],
			'queue'      => [],
		];

		return $data;
	}

	/**
	 * Get name of the first directory in the path.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Path to get from.
	 * @return string Directory name.
	 */
	public static function get_dir_from_path( $path ) {
		$parts = explode( '/', ltrim( $path, '/' ) );

		return $parts[0];
	}

	/**
	 * Get basename path of the CSS JS URL Rewriter's main file.
	 *
	 * @since 2.0.0
	 *
	 * @return string Basename path of the CSS JS URL Rewriter's main file.
	 */
	public static function get_cjur_plugin_basename() {
		$plugins_path = 'css-js-url-rewriter/css-js-url-rewriter.php';

		/**
		 * Filter basename path of the CSS JS URL Rewriter's main file.
		 *
		 * @since 2.0.0
		 *
		 * @param string $plugins_path Standard basename path of the CSS JS URL Rewriter's main file.
		 */
		return apply_filters( 'cjur_plugin_basename', $plugins_path );
	}

	/**
	 * Remove prefix from relative path if prefix exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $path Relative path that might have prefix.
	 * @return string Path that doesn't have prefix.
	 */
	public static function sanitize_relative_path( $path ) {
		return preg_replace( '/#(SITE|CONTENT)#/', '', $path );
	}

	/**
	 * Get prefix for use in relative path.
	 *
	 * For most sites, this prefix is empty, but if site
	 * uses non-standard content directory it changes based
	 * on requested type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type Type of path where prefix is prepend.
	 * @return string Prefix for relative path type.
	 */
	public static function get_relative_path_prefix( $type ) {
		$site_url    = static::get_root_url( 'site' );
		$content_url = static::get_root_url( 'content' );

		// If these URLs have default structures they are the same so there is no prefix.
		if ( $content_url === $site_url ) {
			return '';
		} elseif ( 'content' === $type ) {
			return '#CONTENT#';
		} else {
			return '#SITE#';
		}
	}

	/**
	 * Get root URL of WordPress directory or content directory.
	 *
	 * For most sites, this URL is always the same, but if site
	 * uses non-standard content directory this URL is different
	 * based on requested type.
	 *
	 * @since 2.0.0
	 *
	 * @staticvar string $site_url
	 * @staticvar string $content_url
	 *
	 * @param string $type Type of content that requests root URL.
	 * @return string Root URL.
	 */
	public static function get_root_url( $type = 'site' ) {
		// Setting default URLs should happen only once.
		static $site_url = null, $content_url = null;

		if ( empty( $site_url ) ) {
			$site_url = site_url();
		}

		if ( empty( $content_url ) ) {
			$content_url = content_url();
		}

		// If these URLs have default structures root URL is always the same.
		if ( $content_url === $site_url . '/wp-content' ) {
			return $site_url;
		} elseif ( 'content' === $type ) {
			return $content_url;
		} else {
			return $site_url;
		}
	}

	/**
	 * Get network root URL of WordPress directory or content directory.
	 *
	 * For most sites, this URL is always the same, but if site
	 * uses non-standard content directory this URL is different
	 * based on requested type.
	 *
	 * @since 2.0.0
	 *
	 * @staticvar string $site_url
	 * @staticvar string $content_url
	 *
	 * @throws Exception If current site is not main site of the network.
	 *
	 * @param string $type Type of content that requests network root URL. Default 'site'.
	 * @return string Root URL.
	 */
	public static function get_network_root_url( $type = 'site' ) {
		// Setting default URLs should happen only once.
		static $site_url = null, $content_url = null;

		if ( empty( $site_url ) ) {
			// Check if it is active.
			$site_url = Paths::get_instance()->get_network_url( 'site' );

			// Otherwise, get raw and store it.
			if ( ! $site_url ) {
				// Remove trailing slash for consistency with site_url().
				$site_url = rtrim( network_site_url(), '/' );
				Queue::add( $site_url, $site_url, 'network_site_url', 'network_site_url' );
			}
		}

		if ( empty( $content_url ) ) {
			// Check if it is active.
			$content_url = Paths::get_instance()->get_network_url( 'content' );

			// Otherwise, get raw and store it.
			if ( ! $content_url ) {
				if ( is_main_site() ) {
					$content_url = content_url();

					Queue::add( $content_url, $content_url, 'network_content_url', 'network_content_url' );
				} else {
					throw new Exception( 'Current site is not main site of the network.' );
				}
			}
		}

		// If these URLs have default structures root URL is always the same.
		if ( $content_url === $site_url . '/wp-content' ) {
			return $site_url;
		} elseif ( 'content' === $type ) {
			return $content_url;
		} else {
			return $site_url;
		}
	}

	/**
	 * Get CDN Base URL setting value.
	 *
	 * @since 2.0.0
	 *
	 * @staticvar string $cdn_url
	 *
	 * @throws Exception If CDN base URL was not set.
	 *
	 * @return string $cdn_url
	 */
	public static function get_cdn_base_url() {
		// Setting base CDN URLs should happen only once.
		static $cdn_url = null;

		if ( ! empty( $cdn_url ) ) {
			return $cdn_url;
		}

		$cdn_url = get_site_option( 'css_js_url_rewriter_cdn_url' );

		if ( ! $cdn_url ) {
			throw new Exception( 'CDN base URL was not set.' );
		}

		$cdn_url = rtrim( $cdn_url, '/' );

		return $cdn_url;
	}

	/**
	 * Get array of default directories.
	 *
	 * @since 2.0.0
	 *
	 * @return array Array of default directories.
	 */
	public static function get_default_dirs() {
		// Always include default admin and include directories.
		$defaults = [ '/wp-admin/', '/wp-includes/' ];

		// Include directories set by dependencies classes if parent directory not already included.
		return array_unique( array_merge( $defaults, (array) wp_scripts()->default_dirs, (array) wp_styles()->default_dirs ) );
	}

	/**
	 * Get name of callback for manipulating store data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $base Type of manipulation.
	 * @return string The callback to be run for manipulating store data.
	 */
	public static function get_store_callback( $base ) {
		$function  = is_multisite() ? $base . '_site' : $base;
		$function .= '_option';

		/**
		 * Filter name of callback for manipulating store data.
		 *
		 * @since 2.0.0
		 *
		 * @param string $function The callback to be run for manipulating store data.
		 * @param string $base     Type of manipulation.
		 */
		return apply_filters( 'cjur_store_callback', $function, $base );
	}

	/**
	 * Get name of \WP_Temporary method for manipulating temporary data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $base Type of manipulation.
	 * @return string The \WP_Temporary method to be run for manipulating temporary data.
	 */
	public static function get_temporaries_method( $base ) {
		$method = is_multisite() ? $base . '_site' : $base;

		/**
		 * Filter name of \WP_Temporary method for manipulating temporary data.
		 *
		 * @since 2.0.0
		 *
		 * @param string $method The \WP_Temporary method to be run for manipulating temporary data.
		 * @param string $base   Type of manipulation.
		 */
		return apply_filters( 'cjur_temporaries_method', $method, $base );
	}

	/**
	 * Generate and return subresource integrity hash.
	 *
	 * @since 2.0.0
	 *
	 * @link https://github.com/Elhebert/laravel-sri/blob/03640cb670d3af1908af91c6c87b46a29ca3e37f/src/Sri.php#L22
	 *
	 * @param string $content Content that should be hashed.
	 * @return string Subresource integrity hash.
	 */
	public static function get_subresource_integrity( $content ) {
		return 'sha384-' . base64_encode( hash( 'sha384', $content, true ) );
	}

	/**
	 * Get body of URL.
	 *
	 * @since 2.0.0
	 *
	 * @throws Exception If retrieving content isn't successful.
	 *
	 * @param string $url URL to get content for.
	 * @return string Content of URL.
	 */
	public static function get_remote_content( $url ) {
		$request = wp_safe_remote_get( $url, [
			'timeout'    => MINUTE_IN_SECONDS / 2,
			'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . ' CSSJSURLRewriter/' . CSSJSURLREWRITER_VERSION,
		] );

		if ( is_wp_error( $request ) ) {
			throw new Exception( 'Something wrong happened during request.' );
		}

		if ( 200 != wp_remote_retrieve_response_code( $request ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			throw new Exception( 'Requested remote URL does not exist.' );
		}

		return wp_remote_retrieve_body( $request );
	}

	/**
	 * Verify that settings required for CSS JS URL Rewriter to work are saved.
	 *
	 * This check if CDN base URL is set, and for multisite, if
	 * network content URL is cached. This is important because it
	 * can be cached only when requested on main site of network.
	 *
	 * @since 2.0.0
	 *
	 * @throws Exception If settings were not set.
	 */
	public static function verify_settings() {
		// Verify that CDN's base URL is set.
		static::get_cdn_base_url();

		// For multisite, verify that content URL is cached.
		if ( is_multisite() ) {
			static::get_network_root_url( 'content' );
		}
	}
}
