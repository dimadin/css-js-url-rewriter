<?php 
/**
 * CSS JS URL Rewriter Admin Class
 *
 * Load CSS JS URL Rewriter plugin admin area.
 * 
 * @package    CSS_JS_URL_Rewriter
 * @subpackage Admin
 */

/**
 * Load CSS JS URL Rewriter plugin admin area.
 *
 * @since 1.0
 */
class CSS_JS_URL_Rewriter_Admin {
	/**
	 * Add main method to appropriate hook.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function __construct() {
		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Initialize CSS_JS_URL_Rewriter_Admin object.
	 *
	 * @since 1.0
	 * @access public
	 *
	 * @return CSS_JS_URL_Rewriter_Admin $instance Instance of CSS_JS_URL_Rewriter_Admin class.
	 */
	public static function &get_instance() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new CSS_JS_URL_Rewriter_Admin;
		}

		return $instance;
	}

	/**
	 * Register settings field.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function register_settings() {
		// Load translations
		CSS_JS_URL_Rewriter::get_instance()->load_textdomain();

		add_settings_field( 'css_js_url_rewriter_cdn_url', __( 'CDN URL', 'css-js-url-rewriter' ), array( $this, 'render_cdn_url' ), 'general' );

		register_setting( 'general', 'css_js_url_rewriter_cdn_url', 'esc_url_raw' );
	}

	/**
	 * Display CDN URL settings field.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function render_cdn_url() {
		// Load translations
		CSS_JS_URL_Rewriter::get_instance()->load_textdomain();

		// Get hostname of current site
		$hostname = parse_url( site_url(), PHP_URL_HOST );

		// Get current CDN URL
		$cdn_url = get_option( 'css_js_url_rewriter_cdn_url' );

		?>
		<label for="css_js_url_rewriter_cdn_url">
			<input type="text" id="css_js_url_rewriter_cdn_url" class="regular-text ltr" name="css_js_url_rewriter_cdn_url" value="<?php echo esc_attr( $cdn_url ); ?>" />
		</label>
		<br />
		<span class="description"><?php echo sprintf( __( 'Base URL of CDN. For example <code>https://cdn.example.com</code>, <code>https://example.com/%s</code> etc.', 'css-js-url-rewriter' ), $hostname ); ?></span>
		<?php
	}
}
