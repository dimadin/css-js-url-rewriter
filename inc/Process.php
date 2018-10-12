<?php
/**
 * \dimadin\WP\Plugin\CSSJSURLRewriter\Process class.
 *
 * @package CSSJSURLRewriter
 * @since 2.0.0
 */

namespace dimadin\WP\Plugin\CSSJSURLRewriter;

use dimadin\WP\Plugin\CSSJSURLRewriter\Singleton;
use dimadin\WP\Plugin\CSSJSURLRewriter\Utils;
use dimadin\WP\Plugin\CSSJSURLRewriter\Rewrite;
use Exception;

/**
 * Class that processes current path to get remote one.
 *
 * @since 2.0.0
 */
class Process {
	use Singleton;

	/**
	 * Type of dependency.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $type;

	/**
	 * Handle by which dependency is registered in WordPress.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $handle;

	/**
	 * Final URL of dependency before rewrite.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $src;

	/**
	 * Path relative to the WP root.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $origin_path;

	/**
	 * Content of local path.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $origin_content;

	/**
	 * Status of the path after processing.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $status;

	/**
	 * TTL of path.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	public $ttl;

	/**
	 * Subresource integrity hash of the path.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $subresource_integrity;

	/**
	 * Process path.
	 *
	 * @since 2.0.0
	 *
	 * @throws Exception If processing isn't successful.
	 */
	public function execute() {
		$relative_path = Utils::sanitize_relative_path( $this->origin_path );

		$extension = strtolower( pathinfo( wp_parse_url( $relative_path, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

		// Check if path is dynamic file.
		if ( empty( $extension ) || 'php' === $extension ) {
			throw new Exception( 'Path is dynamic file.' );
		}

		$remote_content = Utils::get_remote_content( Rewrite::get_cdn_url( Rewrite::get_full_original_url( $this->origin_path ) ) );
		$origin_content = $this->get_origin_content();

		if ( $remote_content !== $origin_content ) {
			throw new Exception( 'Remote file is not the same as local file.' );
		}

		$this->status = 'active';

		/**
		 * Filter TTL of active path that rewrites to CDN path.
		 *
		 * Note that path might be cached as active for up to 12 hours after
		 * expiration. Garbage collector is scheduled to run twice daily,
		 * though it can be run before.
		 *
		 * @since 2.0.0
		 *
		 * @param int     $ttl  TTL of active path in seconds. Default 604800 (one week).
		 * @param Process $this Current instance of class.
		 */
		$ttl = apply_filters( 'cjur_active_path_ttl', WEEK_IN_SECONDS, $this );

		$this->ttl = time() + $ttl;
		$this->add_subresource_integrity( $remote_content );
	}

	/**
	 * Add subresource integrity hash to instance's property.
	 *
	 * @since 2.0.0
	 *
	 * @param string $remote_content Content that should be hashed.
	 */
	public function add_subresource_integrity( $remote_content ) {
		/**
		 * Filter whether to hash content for current path.
		 *
		 * Passing a false value to the filter will effectively short-circuit hashing.
		 *
		 * @since 2.0.0
		 *
		 * @param bool    $to_hash Value to return. Any value other than false
		 *                         will short-circuit hashing.
		 * @param Process $this    Current instance of class.
		 */
		$process = apply_filters( 'cjur_add_subresource_integrity', true, $this );

		if ( ! $process ) {
			return;
		}

		$this->subresource_integrity = Utils::get_subresource_integrity( $remote_content );
	}

	/**
	 * Get content of local path.
	 *
	 * @since 2.0.0
	 *
	 * @throws Exception If retrieving content isn't successful.
	 *
	 * @return string Content of local path.
	 */
	public function get_origin_content() {
		if ( ! empty( $this->origin_content ) ) {
			return $this->origin_content;
		}

		$this->origin_content = Utils::get_remote_content( $this->src );

		return $this->origin_content;
	}
}
