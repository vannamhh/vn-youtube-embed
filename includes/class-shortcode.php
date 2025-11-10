<?php
/**
 * Shortcode Handler
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_YouTube_Embed_Shortcode
 */
class VN_YouTube_Embed_Shortcode {

	/**
	 * Instance of this class
	 *
	 * @var VN_YouTube_Embed_Shortcode
	 */
	private static $instance = null;

	/**
	 * Thumbnail cache instance
	 *
	 * @var VN_YouTube_Embed_Thumbnail_Cache
	 */
	private $thumbnail_cache;

	/**
	 * Get instance
	 *
	 * @return VN_YouTube_Embed_Shortcode
	 */
	public static function get_instance(): VN_YouTube_Embed_Shortcode {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->thumbnail_cache = VN_YouTube_Embed_Thumbnail_Cache::get_instance();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		add_shortcode( 'vn_youtube', array( $this, 'render_shortcode' ) );
		add_shortcode( 'youtube_embed', array( $this, 'render_shortcode' ) ); // Backward compatibility.
	}

	/**
	 * Render YouTube embed shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ): string {
		// Get plugin options early to allow dynamic defaults.
		$options          = get_option( 'vn_youtube_embed_options', array() );
		$default_lightbox = ! empty( $options['lightbox_enabled'] );

		$atts = shortcode_atts(
			array(
				'id'               => '',
				'videoid'          => '', // Backward compatibility.
				'width'            => '100%',
				'height'           => '',
				'thumbnail'        => '',
				'quality'          => 'maxresdefault',
				'autoplay'         => true,
				'lazy_load'        => true,
				'lightbox'         => $default_lightbox,
				'custom_thumbnail' => '',
				'class'            => '',
			),
			$atts,
			'vn_youtube'
		);

		// Use videoid for backward compatibility.
		if ( empty( $atts['id'] ) && ! empty( $atts['videoid'] ) ) {
			$atts['id'] = $atts['videoid'];
		}

		// Validate video ID.
		$video_id = $this->extract_video_id( $atts['id'] );
		if ( empty( $video_id ) ) {
			return '<p>' . __( 'Invalid YouTube video ID.', 'vn-youtube-embed' ) . '</p>';
		}

		// Normalize booleans possibly passed as strings.
		$atts['autoplay']  = filter_var( (string) $atts['autoplay'], FILTER_VALIDATE_BOOLEAN );
		$atts['lazy_load'] = filter_var( (string) $atts['lazy_load'], FILTER_VALIDATE_BOOLEAN );
		$atts['lightbox']  = filter_var( (string) $atts['lightbox'], FILTER_VALIDATE_BOOLEAN );

		// Use custom thumbnail if provided, otherwise get from cache.
		$thumbnail_url = ! empty( $atts['custom_thumbnail'] )
			? esc_url( $atts['custom_thumbnail'] )
			: $this->thumbnail_cache->get_thumbnail_url( $video_id, $atts['quality'] );

		// Prepare data attributes.
		$data_attrs = array(
			'data-video-id'     => esc_attr( $video_id ),
			'data-autoplay'     => $atts['autoplay'] ? '1' : '0',
			'data-thumbnail'    => esc_url( $thumbnail_url ),
			'data-custom-thumb' => ! empty( $atts['custom_thumbnail'] ) ? 'true' : 'false',
			'data-lightbox'     => $atts['lightbox'] ? '1' : '0',
		);

		// Build CSS classes.
		$css_classes = array( 'vn-youtube-player' );
		if ( ! empty( $atts['class'] ) ) {
			$css_classes[] = esc_attr( $atts['class'] );
		}
		if ( $atts['lazy_load'] ) {
			$css_classes[] = 'lazy-load-enabled';
		}
		if ( $atts['lightbox'] ) {
			$css_classes[] = 'use-lightbox';
		}

		// Build inline styles (width only) and CSS variables (height via ::before).
		$styles = array();

		$style_vars = array();
		if ( ! empty( $atts['width'] ) ) {
			$styles[] = 'width: ' . esc_attr( $atts['width'] );
		}
		if ( ! empty( $atts['height'] ) ) {
			$style_vars[] = '--vn-yt-height: ' . esc_attr( is_numeric( $atts['height'] ) ? ( (string) $atts['height'] . 'px' ) : (string) $atts['height'] );
			$css_classes[] = 'has-fixed-height';
		}

		$style_attr_parts = array();
		if ( ! empty( $styles ) ) {
			$style_attr_parts[] = implode( '; ', $styles );
		}
		if ( ! empty( $style_vars ) ) {
			$style_attr_parts[] = implode( '; ', $style_vars );
		}
		$computed_style_attr = implode( '; ', $style_attr_parts );

		// Generate unique ID for this instance.
		$unique_id = 'vn-youtube-' . $video_id . '-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div 
			id="<?php echo esc_attr( $unique_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>"
			<?php
			foreach ( $data_attrs as $k => $v ) {
				echo esc_attr( $k ) . '="' . esc_attr( $v ) . '" ';
			}
			?>
			<?php if ( ! empty( $computed_style_attr ) ) : ?>
				style="<?php echo esc_attr( $computed_style_attr ); ?>"
			<?php endif; ?>
		>
			<div class="vn-youtube-thumbnail">
				<img 
					src="<?php echo esc_url( $thumbnail_url ); ?>" 
					<?php
						/* translators: %s: YouTube video ID. */
						$alt = sprintf( __( 'Video thumbnail for %s', 'vn-youtube-embed' ), $video_id );
						echo 'alt="' . esc_attr( $alt ) . '"';
					?>
					class="<?php echo $atts['lazy_load'] ? 'lazy-load' : ''; ?>"
					loading="<?php echo $atts['lazy_load'] ? 'lazy' : 'eager'; ?>"
				>
				
				<div class="vn-youtube-play-button">
					<?php echo wp_kses_post( $this->get_play_button_html() ); ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Extract video ID from various YouTube URL formats
	 *
	 * @param string $input YouTube URL or video ID.
	 * @return string Video ID or empty string if invalid.
	 */
	private function extract_video_id( string $input ): string {
		$input = trim( $input );

		// If it's already a video ID (11 characters).
		if ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $input ) ) {
			return $input;
		}

		// Try to parse URL components for more robust extraction (supports /shorts/).
		$parsed = wp_parse_url( $input );
		if ( false !== $parsed && isset( $parsed['host'] ) ) {
			$host = $parsed['host'];
			$path = $parsed['path'] ?? '';

			// youtu.be short links: https://youtu.be/VIDEO_ID.
			if ( false !== strpos( $host, 'youtu.be' ) ) {
				$id = ltrim( $path, '/' );
				if ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $id ) ) {
					return $id;
				}
			}

			// youtube.com variants.
			if ( false !== strpos( $host, 'youtube.com' ) || false !== strpos( $host, 'youtube-nocookie.com' ) ) {
				// /shorts/VIDEO_ID
				if ( preg_match( '#/shorts/([a-zA-Z0-9_-]{11})#', $path, $m ) ) {
					return $m[1];
				}

				// /embed/VIDEO_ID or /v/VIDEO_ID
				if ( preg_match( '#/(?:embed|v)/([a-zA-Z0-9_-]{11})#', $path, $m ) ) {
					return $m[1];
				}

				// watch?v=VIDEO_ID (query parameter).
				if ( isset( $parsed['query'] ) ) {
					parse_str( $parsed['query'], $query_vars );
					if ( ! empty( $query_vars['v'] ) && preg_match( '/^[a-zA-Z0-9_-]{11}$/', $query_vars['v'] ) ) {
						return $query_vars['v'];
					}
				}
			}
		}

		// Fallback: try a set of regex patterns (covers some edge cases).
		$patterns = array(
			'/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
			'/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
			'/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
			'/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $input, $matches ) ) {
				return $matches[1];
			}
		}

		return '';
	}

	/**
	 * Get play button HTML
	 *
	 * @return string Play button HTML.
	 */
	private function get_play_button_html(): string {
		$options = get_option( 'vn_youtube_embed_options', array() );

		if ( ! empty( $options['custom_play_button'] ) ) {
			return '
				<svg class="vn-youtube-play-icon" viewBox="0 0 68 48" width="68" height="48">
					<path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
					<path d="M 45,24 27,14 27,34" fill="#fff"></path>
				</svg>
			';
		}

		return '<button class="btn-icon circle is-xlarge"><i class="icon-play" aria-hidden="true"></i></button>';
	}

	/**
	 * Generate YouTube embed iframe
	 *
	 * @param string $video_id YouTube video ID.
	 * @param bool   $autoplay Whether to autoplay.
	 * @return string Iframe HTML.
	 */
	public function generate_iframe( string $video_id, bool $autoplay = true ): string {
		$params = array(
			'autoplay'       => $autoplay ? 1 : 0,
			'rel'            => 0,
			'showinfo'       => 0,
			'modestbranding' => 1,
		);

		$embed_url = add_query_arg( $params, "https://www.youtube.com/embed/{$video_id}" );

		return sprintf(
			'<iframe src="%s" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe>',
			esc_url( $embed_url )
		);
	}
}
