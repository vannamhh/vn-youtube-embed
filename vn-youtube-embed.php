<?php
/**
 * Plugin Name: VN YouTube Embed
 * Plugin URI: https://wpmasterynow.com/
 * Description: Tối ưu hiệu suất WordPress bằng cách thay thế iframe YouTube bằng thumbnail tải nhanh, chỉ load video khi người dùng click. Hỗ trợ cache thumbnail và tích hợp UX Builder.
 * Version: 1.1.0
 * Author: VN
 * Author URI: https://wpmasterynow.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vn-youtube-embed
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Network: false
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'VN_YOUTUBE_EMBED_VERSION', '1.1.0' );
define( 'VN_YOUTUBE_EMBED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VN_YOUTUBE_EMBED_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'VN_YOUTUBE_EMBED_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main VN YouTube Embed Class
 */
final class VN_YouTube_Embed {

	/**
	 * Plugin instance
	 *
	 * @var VN_YouTube_Embed
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @return VN_YouTube_Embed
	 */
	public static function get_instance(): VN_YouTube_Embed {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		// Activation and deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize plugin
	 */
	public function init(): void {
		// Load required files.
		$this->load_dependencies();

		// Initialize components.
		$this->init_components();
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'vn-youtube-embed',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies(): void {
		require_once VN_YOUTUBE_EMBED_PLUGIN_PATH . 'includes/class-thumbnail-cache.php';
		require_once VN_YOUTUBE_EMBED_PLUGIN_PATH . 'includes/class-shortcode.php';
		require_once VN_YOUTUBE_EMBED_PLUGIN_PATH . 'includes/class-assets.php';
		require_once VN_YOUTUBE_EMBED_PLUGIN_PATH . 'admin/class-admin.php';

		// Always load UX Builder integration - it will check internally if UX Builder is available.
		require_once VN_YOUTUBE_EMBED_PLUGIN_PATH . 'includes/class-ux-builder.php';
	}

	/**
	 * Initialize components
	 */
	private function init_components(): void {
		VN_YouTube_Embed_Thumbnail_Cache::get_instance();
		VN_YouTube_Embed_Shortcode::get_instance();
		VN_YouTube_Embed_Assets::get_instance();
		VN_YouTube_Embed_Admin::get_instance();

		// Always initialize UX Builder integration.
		VN_YouTube_Embed_UX_Builder::get_instance();
	}

	/**
	 * Check if Flatsome theme is active
	 *
	 * @return bool
	 */
	private function is_flatsome_active(): bool {
		$theme = wp_get_theme();
		$template = $theme->get_template();
		$parent = $theme->parent();
		
		// Check current theme
		if ( 'Flatsome' === $theme->get( 'Name' ) || 'flatsome' === $template ) {
			return true;
		}
		
		// Check parent theme (for child themes)
		if ( $parent && ( 'Flatsome' === $parent->get( 'Name' ) || 'flatsome' === $parent->get_template() ) ) {
			return true;
		}
		
		// Check if UX Builder functions exist (additional check)
		return function_exists( 'add_ux_builder_shortcode' );
	}

	/**
	 * Plugin activation
	 */
	public function activate(): void {
		// Create upload directory for thumbnails.
		$upload_dir    = wp_upload_dir();
		$thumbnail_dir = $upload_dir['basedir'] . '/vn-youtube-thumbnails';

		if ( ! file_exists( $thumbnail_dir ) ) {
			wp_mkdir_p( $thumbnail_dir );
		}

		// Set default options.
		$default_options = array(
			'thumbnail_quality'  => 'maxresdefault',
			'lazy_load'          => true,
			'cache_duration'     => 30,
			'autoplay'           => true,
			'custom_play_button' => true,
			'lightbox_enabled'   => false,
		);

		add_option( 'vn_youtube_embed_options', $default_options );

		// Schedule cleanup event.
		if ( ! wp_next_scheduled( 'vn_youtube_embed_cleanup' ) ) {
			wp_schedule_event( time(), 'weekly', 'vn_youtube_embed_cleanup' );
		}
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate(): void {
		// Clear scheduled events.
		wp_clear_scheduled_hook( 'vn_youtube_embed_cleanup' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}

/**
 * Initialize the plugin
 */
function vn_youtube_embed(): VN_YouTube_Embed {
	return VN_YouTube_Embed::get_instance();
}

// Start the plugin.
vn_youtube_embed();
