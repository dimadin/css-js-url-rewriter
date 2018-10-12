<?php
/**
 * \dimadin\WP\Plugin\CSSJSURLRewriter\Process class.
 *
 * @package CSSJSURLRewriter
 * @since 2.0.0
 */

namespace dimadin\WP\Plugin\CSSJSURLRewriter;

/**
 * Load CSS JS URL Rewriter plugin admin area.
 *
 * @since 2.0.0
 */
class Admin {
	/**
	 * Add a field for customizing CDN Base URL to a settings page.
	 *
	 * @since 2.0.0
	 */
	public static function add_settings() {
		if ( ! is_multisite() ) {
			add_settings_field(
				'css_js_url_rewriter_cdn_url',
				__( 'CDN URL', 'css-js-url-rewriter' ),
				[ __CLASS__, 'render_field' ],
				'general'
			);
		}
	}

	/**
	 * Add a field for customizing CDN Base URL to a network settings page.
	 *
	 * @since 2.0.0
	 */
	public static function add_network_settings() {
		add_settings_section(
			'cjur-network-settings-section',
			__( 'CSS JS URL Rewriter', 'css-js-url-rewriter' ),
			'__return_false',
			'cjur-network-settings-page'
		);

		add_settings_field(
			'css_js_url_rewriter_cdn_url',
			__( 'CDN URL', 'css-js-url-rewriter' ),
			[ __CLASS__, 'render_field' ],
			'cjur-network-settings-page',
			'cjur-network-settings-section'
		);
	}

	/**
	 * Save CDN Base URL on network settings page submission.
	 *
	 * @since 2.0.0
	 */
	public static function save_network_settings() {
		if ( ! current_user_can( 'manage_network_options' ) ) {
			return false;
		}

		if ( false === check_admin_referer( 'cjur-network-settings', 'cjur-network-settings-nonce' ) ) {
			return false;
		}

		$cdn_url = '';

		if ( isset( $_POST['css_js_url_rewriter_cdn_url'] ) ) {
			$cdn_url = esc_url_raw( wp_unslash( $_POST['css_js_url_rewriter_cdn_url'] ) );
		}

		if ( $cdn_url ) {
			update_site_option( 'css_js_url_rewriter_cdn_url', $cdn_url );
		} else {
			delete_site_option( 'css_js_url_rewriter_cdn_url' );
		}
	}

	/**
	 * Display field for customizing CDN Base URL on network a settings page.
	 *
	 * @since 2.0.0
	 */
	public static function display_network_settings() {
		wp_nonce_field( 'cjur-network-settings', 'cjur-network-settings-nonce' );

		do_settings_sections( 'cjur-network-settings-page' );
	}

	/**
	 * Display CDN Base URL settings field.
	 *
	 * @since 2.0.0
	 */
	public static function render_field() {
		// Get hostname of current site.
		$hostname = wp_parse_url( site_url(), PHP_URL_HOST );

		// Get current CDN URL.
		$cdn_url = get_site_option( 'css_js_url_rewriter_cdn_url' );

		?>
		<label for="css_js_url_rewriter_cdn_url">
			<input type="text" id="css_js_url_rewriter_cdn_url" class="regular-text ltr" name="css_js_url_rewriter_cdn_url" value="<?php echo esc_attr( $cdn_url ); ?>" />
		</label>
		<br />
		<span class="description">
			<?php
			/* translators: 1: Hostname of current site, 2: Hostname of current site */
			echo sprintf( __( 'Hostname of CDN. That hostname must point to hostname of site, %1$s. Example of CDN hostname: <code>https://cdn.%2$s</code>.', 'css-js-url-rewriter' ), $hostname, $hostname ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</span>
		<?php
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $actions     An array of plugin action links.
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @return array $actions
	 */
	public static function plugin_action_link( $actions, $plugin_file, $plugin_data ) {
		if ( array_key_exists( 'TextDomain', $plugin_data ) && 'css-js-url-rewriter' === $plugin_data['TextDomain'] ) {
			$url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'options-general.php' );

			$actions['settings'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'css-js-url-rewriter' ) . '</a>';
		}

		return $actions;
	}
}
