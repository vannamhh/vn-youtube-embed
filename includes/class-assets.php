<?php
/**
 * Assets Management
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_YouTube_Embed_Assets
 */
class VN_YouTube_Embed_Assets {

	/**
	 * Instance of this class
	 *
	 * @var VN_YouTube_Embed_Assets
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return VN_YouTube_Embed_Assets
	 */
	public static function get_instance(): VN_YouTube_Embed_Assets {
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_footer', array( $this, 'add_inline_script' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets(): void {
		// Enqueue CSS.
		wp_enqueue_style(
			'vn-youtube-embed',
			VN_YOUTUBE_EMBED_PLUGIN_URL . 'assets/css/style.css',
			array(),
			VN_YOUTUBE_EMBED_VERSION
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			'vn-youtube-embed',
			VN_YOUTUBE_EMBED_PLUGIN_URL . 'assets/js/youtube-embed.js',
			array( 'jquery' ),
			VN_YOUTUBE_EMBED_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'vn-youtube-embed',
			'vnYouTubeEmbed',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'vn_youtube_embed_nonce' ),
				'options' => get_option( 'vn_youtube_embed_options', array() ),
			)
		);
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		// Only load on plugin settings page.
		if ( 'settings_page_vn-youtube-embed' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'vn-youtube-embed-admin',
			VN_YOUTUBE_EMBED_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			VN_YOUTUBE_EMBED_VERSION
		);

		wp_enqueue_script(
			'vn-youtube-embed-admin',
			VN_YOUTUBE_EMBED_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			VN_YOUTUBE_EMBED_VERSION,
			true
		);
	}

	/**
	 * Add inline script for immediate functionality
	 */
	public function add_inline_script(): void {
		// Only add if there are YouTube embeds on the page.
		if ( ! $this->has_youtube_embeds() ) {
			return;
		}

		?>
		<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof window.vnYouTubeEmbedInit === 'function') {
				window.vnYouTubeEmbedInit();
			}
		});
		</script>
		<?php
	}

	/**
	 * Check if page has YouTube embeds
	 *
	 * @return bool True if has embeds.
	 */
	private function has_youtube_embeds(): bool {
		global $post;
		
		if ( ! $post ) {
			return false;
		}

		// Check if shortcode exists in content.
		return has_shortcode( $post->post_content, 'vn_youtube' ) ||
			has_shortcode( $post->post_content, 'youtube_embed' );
	}
}
