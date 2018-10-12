<?php
/**
 * \dimadin\WP\Plugin\CSSJSURLRewriter\Main class.
 *
 * @package CSSJSURLRewriter
 * @since 2.0.0
 */

namespace dimadin\WP\Plugin\CSSJSURLRewriter;

use dimadin\WP\Plugin\CSSJSURLRewriter\Singleton;
use WP_CLI;
use WP_Temporary;

/**
 * Class with methods that initialize CSS JS URL Rewriter.
 *
 * This class hooks other parts of CSS JS URL Rewriter, and
 * other methods that are important for functioning
 * of CSS JS URL Rewriter.
 *
 * @since 2.0.0
 */
class Main {
	use Singleton;

	/**
	 * Constructor.
	 *
	 * This method is used to hook everything.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		static::hook();
	}

	/**
	 * Hook everything.
	 *
	 * @since 2.0.0
	 */
	public static function hook() {
		// phpcs:disable PEAR.Functions.FunctionCallSignature.SpaceBeforeCloseBracket, Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma, WordPress.Arrays.CommaAfterArrayItem.SpaceAfterComma, WordPress.Arrays.ArrayDeclarationSpacing.SpaceBeforeArrayCloser, Generic.Functions.FunctionCallArgumentSpacing.SpaceBeforeComma

		// Rewrite URLs of dependencies to use CDN.
		add_filter( 'script_loader_src',                              [ __NAMESPACE__ . '\Rewrite',      'script'                      ], -456, 2  );
		add_filter( 'style_loader_src',                               [ __NAMESPACE__ . '\Rewrite',      'style'                       ], -456, 2  );

		// Add subresource integrity attributes to rewritten dependencies.
		add_filter( 'script_loader_tag',                              [ __NAMESPACE__ . '\SRI',          'script'                      ], -567, 3  );
		add_filter( 'style_loader_tag',                               [ __NAMESPACE__ . '\SRI',          'style'                       ], -567, 3  );

		// Save current page queue and schedule processing of queued dependencies.
		add_action( 'shutdown',                                       [ __NAMESPACE__ . '\Queue',        'save'                        ], 1        );
		add_action( 'shutdown',                                       [ __NAMESPACE__ . '\Queue',        'schedule_processing'         ], 2        );

		// Remove expired dependencies from settings.
		add_action( 'wp_scheduled_delete',                            [ __NAMESPACE__ . '\Clean',        'expired'                     ], 2        );

		// Register settings field.
		add_action( 'init',                                           [ __NAMESPACE__ . '\Main',         'register_settings'           ], 2        );

		// Add CDN Base URL field to settings pages.
		add_action( 'admin_menu',                                     [ __NAMESPACE__ . '\Admin',        'add_settings'                ], 2        );
		add_action( 'network_admin_menu',                             [ __NAMESPACE__ . '\Admin',        'add_network_settings'        ], 2        );

		// Display CDN Base URL field on network settings page and handle submission.
		add_action( 'wpmu_options',                                   [ __NAMESPACE__ . '\Admin',        'display_network_settings'    ], 2        );
		add_action( 'update_wpmu_options',                            [ __NAMESPACE__ . '\Admin',        'save_network_settings'       ], 2        );

		// Delete everything from storage when CDN URL settings is changed.
		add_action( 'add_option_css_js_url_rewriter_cdn_url',         [ __NAMESPACE__ . '\Clean',        'all'                         ], 2        );
		add_action( 'update_option_css_js_url_rewriter_cdn_url',      [ __NAMESPACE__ . '\Clean',        'all'                         ], 2        );
		add_action( 'delete_option_css_js_url_rewriter_cdn_url',      [ __NAMESPACE__ . '\Clean',        'all'                         ], 2        );
		add_action( 'add_site_option_css_js_url_rewriter_cdn_url',    [ __NAMESPACE__ . '\Clean',        'all'                         ], 2        );
		add_action( 'update_site_option_css_js_url_rewriter_cdn_url', [ __NAMESPACE__ . '\Clean',        'all'                         ], 2        );
		add_action( 'delete_site_option_css_js_url_rewriter_cdn_url', [ __NAMESPACE__ . '\Clean',        'all'                         ], 2        );

		// Add WP-CLI commands.
		add_action( 'cli_init',                                       [ __NAMESPACE__ . '\Main',         'init_wp_cli'                 ], 2        );

		// Remove all dependencies with paths that belong to changed resources.
		add_action( 'upgrader_process_complete',                      [ __NAMESPACE__ . '\Clean',        'after_upgrade'               ], 2   , 2  );
		add_action( 'deactivated_plugin',                             [ __NAMESPACE__ . '\Clean',        'after_plugin_deactivation'   ], 2   , 2  );
		add_action( 'switch_theme',                                   [ __NAMESPACE__ . '\Clean',        'after_theme_switch'          ], 2   , 3  );

		// Show action links on the plugin screen.
		add_filter( 'plugin_action_links',                            [ __NAMESPACE__ . '\Admin',        'plugin_action_link'          ], 10  , 3  );
		add_filter( 'network_admin_plugin_action_links',              [ __NAMESPACE__ . '\Admin',        'plugin_action_link'          ], 10  , 3  );

		// Register listener for background processes from Backdrop library.
		add_action( 'admin_init', [ 'dimadin\WP\Library\Backdrop\Main',                                  'init'                        ], 2        );

		// Remove expired temporaries from database.
		add_action( 'admin_init', [ 'WP_Temporary',                                                      'clean'                       ], 2        );

		// phpcs:enable
	}

	/**
	 * Register settings field.
	 *
	 * @since 2.0.0
	 */
	public static function register_settings() {
		$option_group = is_multisite() ? 'cjur-network-settings-page' : 'general';

		register_setting( $option_group, 'css_js_url_rewriter_cdn_url', [
			'sanitize_callback' => 'esc_url_raw',
		] );
	}

	/**
	 * Add WP-CLI commands.
	 *
	 * @since 2.0.0
	 */
	public static function init_wp_cli() {
		WP_CLI::add_command( 'css-js-url-rewriter', __NAMESPACE__ . '\WPCLI' );

		// Add WP_Temporary command.
		WP_Temporary::init_wp_cli();
	}
}
