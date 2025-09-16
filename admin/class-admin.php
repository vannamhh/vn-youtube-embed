<?php
/**
 * Admin Panel Management
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_YouTube_Embed_Admin
 */
class VN_YouTube_Embed_Admin {

	/**
	 * Instance of this class
	 *
	 * @var VN_YouTube_Embed_Admin
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return VN_YouTube_Embed_Admin
	 */
	public static function get_instance(): VN_YouTube_Embed_Admin {
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wp_ajax_vn_clear_thumbnail_cache', array( $this, 'ajax_clear_cache' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu(): void {
		add_options_page(
			__( 'VN YouTube Embed Settings', 'vn-youtube-embed' ),
			__( 'YouTube Embed', 'vn-youtube-embed' ),
			'manage_options',
			'vn-youtube-embed',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Initialize settings
	 */
	public function init_settings(): void {
		register_setting(
			'vn_youtube_embed_options',
			'vn_youtube_embed_options',
			array( $this, 'sanitize_options' )
		);

		add_settings_section(
			'vn_youtube_embed_general',
			__( 'General Settings', 'vn-youtube-embed' ),
			array( $this, 'render_general_section' ),
			'vn-youtube-embed'
		);

		add_settings_field(
			'thumbnail_quality',
			__( 'Default Thumbnail Quality', 'vn-youtube-embed' ),
			array( $this, 'render_thumbnail_quality_field' ),
			'vn-youtube-embed',
			'vn_youtube_embed_general'
		);

		add_settings_field(
			'cache_duration',
			__( 'Cache Duration (days)', 'vn-youtube-embed' ),
			array( $this, 'render_cache_duration_field' ),
			'vn-youtube-embed',
			'vn_youtube_embed_general'
		);

		add_settings_field(
			'lazy_load',
			__( 'Enable Lazy Loading', 'vn-youtube-embed' ),
			array( $this, 'render_lazy_load_field' ),
			'vn-youtube-embed',
			'vn_youtube_embed_general'
		);

		add_settings_field(
			'autoplay',
			__( 'Enable Autoplay', 'vn-youtube-embed' ),
			array( $this, 'render_autoplay_field' ),
			'vn-youtube-embed',
			'vn_youtube_embed_general'
		);

		add_settings_field(
			'custom_play_button',
			__( 'Custom Play Button', 'vn-youtube-embed' ),
			array( $this, 'render_custom_play_button_field' ),
			'vn-youtube-embed',
			'vn_youtube_embed_general'
		);
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$cache = VN_YouTube_Embed_Thumbnail_Cache::get_instance();
		$stats = $cache->get_cache_stats();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'vn_youtube_embed_options' );
				do_settings_sections( 'vn-youtube-embed' );
				submit_button();
				?>
			</form>

			<div class="vn-youtube-cache-info">
				<h2><?php esc_html_e( 'Cache Information', 'vn-youtube-embed' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %1$d: number of cached files, %2$s: cache size */
						esc_html__( 'Cached thumbnails: %1$d files (%2$s)', 'vn-youtube-embed' ),
						$stats['count'],
						size_format( $stats['size'] )
					);
					?>
				</p>
				<button type="button" id="vn-clear-cache" class="button button-secondary">
					<?php esc_html_e( 'Clear Cache', 'vn-youtube-embed' ); ?>
				</button>
			</div>

			<div class="vn-youtube-usage">
				<h2><?php esc_html_e( 'Usage Examples', 'vn-youtube-embed' ); ?></h2>
				<h3><?php esc_html_e( 'Basic Usage', 'vn-youtube-embed' ); ?></h3>
				<code>[vn_youtube id="dQw4w9WgXcQ"]</code>
				
				<h3><?php esc_html_e( 'With Custom Thumbnail', 'vn-youtube-embed' ); ?></h3>
				<code>[vn_youtube id="dQw4w9WgXcQ" custom_thumbnail="https://example.com/thumb.jpg"]</code>
				
				<h3><?php esc_html_e( 'With Custom Dimensions', 'vn-youtube-embed' ); ?></h3>
				<code>[vn_youtube id="dQw4w9WgXcQ" width="640" height="360"]</code>
			</div>
		</div>
		<?php
	}

	/**
	 * Render general section
	 */
	public function render_general_section(): void {
		echo '<p>' . esc_html__( 'Configure the default settings for YouTube embeds.', 'vn-youtube-embed' ) . '</p>';
	}

	/**
	 * Render thumbnail quality field
	 */
	public function render_thumbnail_quality_field(): void {
		$options = get_option( 'vn_youtube_embed_options', array() );
		$value   = $options['thumbnail_quality'] ?? 'maxresdefault';
		?>
		<select name="vn_youtube_embed_options[thumbnail_quality]">
			<option value="maxresdefault" <?php selected( $value, 'maxresdefault' ); ?>>
				<?php esc_html_e( 'Maximum Quality (1920x1080)', 'vn-youtube-embed' ); ?>
			</option>
			<option value="sddefault" <?php selected( $value, 'sddefault' ); ?>>
				<?php esc_html_e( 'Standard Quality (640x480)', 'vn-youtube-embed' ); ?>
			</option>
			<option value="hqdefault" <?php selected( $value, 'hqdefault' ); ?>>
				<?php esc_html_e( 'High Quality (480x360)', 'vn-youtube-embed' ); ?>
			</option>
			<option value="mqdefault" <?php selected( $value, 'mqdefault' ); ?>>
				<?php esc_html_e( 'Medium Quality (320x180)', 'vn-youtube-embed' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Choose the default quality for cached thumbnails.', 'vn-youtube-embed' ); ?>
		</p>
		<?php
	}

	/**
	 * Render cache duration field
	 */
	public function render_cache_duration_field(): void {
		$options = get_option( 'vn_youtube_embed_options', array() );
		$value   = $options['cache_duration'] ?? 30;
		?>
		<input 
			type="number" 
			name="vn_youtube_embed_options[cache_duration]" 
			value="<?php echo esc_attr( $value ); ?>"
			min="1"
			max="365"
		>
		<p class="description">
			<?php esc_html_e( 'How long to cache thumbnails (1-365 days).', 'vn-youtube-embed' ); ?>
		</p>
		<?php
	}

	/**
	 * Render lazy load field
	 */
	public function render_lazy_load_field(): void {
		$options = get_option( 'vn_youtube_embed_options', array() );
		$value   = $options['lazy_load'] ?? true;
		?>
		<label>
			<input 
				type="checkbox" 
				name="vn_youtube_embed_options[lazy_load]" 
				value="1"
				<?php checked( $value ); ?>
			>
			<?php esc_html_e( 'Enable lazy loading for thumbnails', 'vn-youtube-embed' ); ?>
		</label>
		<?php
	}

	/**
	 * Render autoplay field
	 */
	public function render_autoplay_field(): void {
		$options = get_option( 'vn_youtube_embed_options', array() );
		$value   = $options['autoplay'] ?? true;
		?>
		<label>
			<input 
				type="checkbox" 
				name="vn_youtube_embed_options[autoplay]" 
				value="1"
				<?php checked( $value ); ?>
			>
			<?php esc_html_e( 'Enable autoplay when video is clicked', 'vn-youtube-embed' ); ?>
		</label>
		<?php
	}

	/**
	 * Render custom play button field
	 */
	public function render_custom_play_button_field(): void {
		$options = get_option( 'vn_youtube_embed_options', array() );
		$value   = $options['custom_play_button'] ?? true;
		?>
		<label>
			<input 
				type="checkbox" 
				name="vn_youtube_embed_options[custom_play_button]" 
				value="1"
				<?php checked( $value ); ?>
			>
			<?php esc_html_e( 'Use custom YouTube-style play button', 'vn-youtube-embed' ); ?>
		</label>
		<?php
	}

	/**
	 * Sanitize options
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized data.
	 */
	public function sanitize_options( array $input ): array {
		$sanitized = array();

		$sanitized['thumbnail_quality'] = in_array( 
			$input['thumbnail_quality'] ?? '', 
			array( 'maxresdefault', 'sddefault', 'hqdefault', 'mqdefault' ),
			true
		) ? $input['thumbnail_quality'] : 'maxresdefault';

		$sanitized['cache_duration'] = absint( $input['cache_duration'] ?? 30 );
		if ( $sanitized['cache_duration'] < 1 || $sanitized['cache_duration'] > 365 ) {
			$sanitized['cache_duration'] = 30;
		}

		$sanitized['lazy_load']          = ! empty( $input['lazy_load'] );
		$sanitized['autoplay']           = ! empty( $input['autoplay'] );
		$sanitized['custom_play_button'] = ! empty( $input['custom_play_button'] );

		return $sanitized;
	}

	/**
	 * AJAX handler for clearing cache
	 */
	public function ajax_clear_cache(): void {
		check_ajax_referer( 'vn_youtube_embed_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'vn-youtube-embed' ) );
		}

		$cache = VN_YouTube_Embed_Thumbnail_Cache::get_instance();
		$cache->clear_cache();

		wp_send_json_success( array(
			'message' => __( 'Cache cleared successfully.', 'vn-youtube-embed' ),
		) );
	}
}
