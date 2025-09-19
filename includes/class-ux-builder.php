<?php
/**
 * UX Builder Integration for Flatsome Theme
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_YouTube_Embed_UX_Builder
 */
class VN_YouTube_Embed_UX_Builder {

	/**
	 * Instance of this class
	 *
	 * @var VN_YouTube_Embed_UX_Builder
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return VN_YouTube_Embed_UX_Builder
	 */
	public static function get_instance(): VN_YouTube_Embed_UX_Builder {
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
		add_action( 'ux_builder_setup', array( $this, 'register_ux_element' ) );
		add_action( 'init', array( $this, 'register_ux_element' ), 20 ); // Fallback hook
		add_action( 'after_setup_theme', array( $this, 'register_ux_element' ), 20 ); // Another fallback
		add_shortcode( 'vn_youtube_ux', array( $this, 'render_ux_shortcode' ) );
		
		// Debug hook to check if UX Builder is available
		add_action( 'admin_notices', array( $this, 'debug_ux_builder' ) );
	}

	/**
	 * Register UX Builder element
	 */
	public function register_ux_element(): void {
		// Check if UX Builder functions exist.
		if ( ! function_exists( 'add_ux_builder_shortcode' ) ) {
			return;
		}

		add_ux_builder_shortcode(
			'vn_youtube_ux',
			array(
				'name'      => __( 'VN YouTube Embed', 'vn-youtube-embed' ),
				'category'  => __( 'Content', 'vn-youtube-embed' ),
				'priority'  => 1,
				'wrap'      => false,
				'options'   => array(
					'video_id'          => array(
						'type'        => 'textfield',
						'heading'     => __( 'Video ID or URL', 'vn-youtube-embed' ),
						'description' => __( 'Enter YouTube video ID or full URL. Example: dQw4w9WgXcQ', 'vn-youtube-embed' ),
						'default'     => '',
					),
					'thumbnail_quality' => array(
						'type'    => 'select',
						'heading' => __( 'Thumbnail Quality', 'vn-youtube-embed' ),
						'default' => 'maxresdefault',
						'options' => array(
							'maxresdefault' => __( 'Maximum Quality (1920x1080)', 'vn-youtube-embed' ),
							'sddefault'     => __( 'Standard Quality (640x480)', 'vn-youtube-embed' ),
							'hqdefault'     => __( 'High Quality (480x360)', 'vn-youtube-embed' ),
							'mqdefault'     => __( 'Medium Quality (320x180)', 'vn-youtube-embed' ),
						),
					),
					'custom_thumbnail'  => array(
						'type'        => 'image',
						'heading'     => __( 'Custom Thumbnail', 'vn-youtube-embed' ),
						'description' => __( 'Upload custom thumbnail image (optional)', 'vn-youtube-embed' ),
						'default'     => '',
					),
					'width'             => array(
						'type'        => 'textfield',
						'heading'     => __( 'Width', 'vn-youtube-embed' ),
						'description' => __( 'Container width (e.g., 100%, 640px)', 'vn-youtube-embed' ),
						'default'     => '100%',
					),
					'height'            => array(
						'type'        => 'textfield',
						'heading'     => __( 'Height', 'vn-youtube-embed' ),
						'description' => __( 'Container height (optional)', 'vn-youtube-embed' ),
						'default'     => '',
					),
					'autoplay'          => array(
						'type'    => 'checkbox',
						'heading' => __( 'Autoplay', 'vn-youtube-embed' ),
						'default' => 'true',
					),
					'lazy_load'         => array(
						'type'    => 'checkbox',
						'heading' => __( 'Lazy Loading', 'vn-youtube-embed' ),
						'default' => 'true',
					),
					'lightbox'          => array(
						'type'    => 'checkbox',
						'heading' => __( 'Open in Lightbox', 'vn-youtube-embed' ),
						'description' => __( 'Open the video in a fullscreen lightbox overlay when clicked', 'vn-youtube-embed' ),
						'default' => 'false',
					),
					'css_class'         => array(
						'type'        => 'textfield',
						'heading'     => __( 'CSS Class', 'vn-youtube-embed' ),
						'description' => __( 'Additional CSS classes', 'vn-youtube-embed' ),
						'default'     => '',
					),
				),
				'thumbnail' => function_exists( 'flatsome_ux_builder_thumbnail' )
					? flatsome_ux_builder_thumbnail( 'ux_video' )
					: '',
			)
		);
	}

	/**
	 * Debug UX Builder availability
	 */
	public function debug_ux_builder(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'plugins_page_vn-youtube-embed' !== $screen->id ) {
			return;
		}

		$ux_builder_exists = function_exists( 'add_ux_builder_shortcode' );
		$flatsome_active = $this->is_flatsome_theme_active();

		if ( ! $ux_builder_exists && $flatsome_active ) {
			echo '<div class="notice notice-warning"><p>';
			echo esc_html__( 'VN YouTube Embed: Flatsome theme detected but UX Builder functions not available. Make sure Flatsome theme is properly activated.', 'vn-youtube-embed' );
			echo '</p></div>';
		} elseif ( $ux_builder_exists ) {
			echo '<div class="notice notice-success"><p>';
			echo esc_html__( 'VN YouTube Embed: UX Builder integration is active! You can find "VN YouTube Embed" element in UX Builder.', 'vn-youtube-embed' );
			echo '</p></div>';
		}
	}

	/**
	 * Check if Flatsome theme is active
	 *
	 * @return bool
	 */
	private function is_flatsome_theme_active(): bool {
		$theme    = wp_get_theme();
		$template = $theme->get_template();
		$parent   = $theme->parent();

		// Check current theme.
		if ( 'Flatsome' === $theme->get( 'Name' ) || 'flatsome' === $template ) {
			return true;
		}

		// Check parent theme (for child themes).
		if ( $parent && ( 'Flatsome' === $parent->get( 'Name' ) || 'flatsome' === $parent->get_template() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Render UX Builder shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_ux_shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'video_id'          => '',
				'thumbnail_quality' => 'maxresdefault',
				'custom_thumbnail'  => '',
				'width'             => '100%',
				'height'            => '',
				'autoplay'          => 'true',
				'lazy_load'         => 'true',
				'lightbox'          => 'false',
				'css_class'         => '',
			),
			$atts,
			'vn_youtube_ux'
		);

		// Convert UX Builder attributes to standard shortcode format.
		$shortcode_atts = array(
			'id'        => $atts['video_id'],
			'quality'   => $atts['thumbnail_quality'],
			'width'     => $atts['width'],
			'height'    => $atts['height'],
			'autoplay'  => 'true' === $atts['autoplay'],
			'lazy_load' => 'true' === $atts['lazy_load'],
			'lightbox'  => isset( $atts['lightbox'] ) ? ( 'true' === $atts['lightbox'] ) : false,
			'class'     => $atts['css_class'],
		);

		// Handle custom thumbnail.
		if ( ! empty( $atts['custom_thumbnail'] ) ) {
			if ( is_numeric( $atts['custom_thumbnail'] ) ) {
				// It's an attachment ID.
				$image_url = wp_get_attachment_url( intval( $atts['custom_thumbnail'] ) );
				if ( $image_url ) {
					$shortcode_atts['custom_thumbnail'] = $image_url;
				}
			} else {
				// It's already a URL.
				$shortcode_atts['custom_thumbnail'] = $atts['custom_thumbnail'];
			}
		}

		// Use the main shortcode handler.
		$shortcode_instance = VN_YouTube_Embed_Shortcode::get_instance();
		return $shortcode_instance->render_shortcode( $shortcode_atts );
	}
}
