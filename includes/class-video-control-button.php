<?php
/**
 * Video Control Button Handler
 * 
 * Provides custom buttons to control YouTube video playback with specified start and end times.
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_YouTube_Embed_Video_Control_Button
 */
class VN_YouTube_Embed_Video_Control_Button {

	/**
	 * Instance of this class
	 *
	 * @var VN_YouTube_Embed_Video_Control_Button
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return VN_YouTube_Embed_Video_Control_Button
	 */
	public static function get_instance(): VN_YouTube_Embed_Video_Control_Button {
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
		add_shortcode( 'vn_youtube_button', array( $this, 'render_control_button' ) );
		add_shortcode( 'youtube_control_button', array( $this, 'render_control_button' ) ); // Alternative name.
	}

	/**
	 * Render control button shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_control_button( $atts ): string {
		$atts = shortcode_atts(
			array(
				'target'    => '',           // ID của player target (vn-youtube-VIDEO_ID-XXXX).
				'video_id'  => '',           // YouTube video ID (nếu không dùng target).
				'start'     => '0',          // Thời điểm bắt đầu (giây).
				'end'       => '',           // Thời điểm kết thúc (giây, để trống = phát đến hết).
				'label'     => '',           // Text hiển thị trên button.
				'icon'      => 'play',       // Icon class (play, pause, forward, etc).
				'class'     => '',           // Custom CSS classes.
				'style'     => 'primary',    // Button style: primary, secondary, outline, link.
				'size'      => 'medium',     // Button size: small, medium, large.
				'autoplay'  => 'true',       // Tự động phát khi click.
			),
			$atts,
			'vn_youtube_button'
		);

		// Validate required parameters.
		if ( empty( $atts['target'] ) && empty( $atts['video_id'] ) ) {
			return '<p class="vn-youtube-button-error">' . __( 'Error: Please specify target or video_id.', 'vn-youtube-embed' ) . '</p>';
		}

		// Sanitize inputs.
		$target_id  = ! empty( $atts['target'] ) ? sanitize_text_field( $atts['target'] ) : '';
		$video_id   = ! empty( $atts['video_id'] ) ? sanitize_text_field( $atts['video_id'] ) : '';
		$start_time = absint( $atts['start'] );
		$end_time   = ! empty( $atts['end'] ) ? absint( $atts['end'] ) : 0;
		$label      = ! empty( $atts['label'] ) ? sanitize_text_field( $atts['label'] ) : $this->get_default_label( $start_time, $end_time );
		$icon       = sanitize_text_field( $atts['icon'] );
		$style      = sanitize_text_field( $atts['style'] );
		$size       = sanitize_text_field( $atts['size'] );
		$autoplay   = filter_var( $atts['autoplay'], FILTER_VALIDATE_BOOLEAN );

		// Build CSS classes.
		$css_classes = array(
			'vn-youtube-control-button',
			'vn-ytb-btn',
			'vn-ytb-btn-' . $style,
			'vn-ytb-btn-' . $size,
		);

		if ( ! empty( $atts['class'] ) ) {
			$css_classes[] = sanitize_html_class( $atts['class'] );
		}

		// Build data attributes.
		$data_attrs = array(
			'data-target-id' => $target_id,
			'data-video-id'  => $video_id,
			'data-start'     => $start_time,
			'data-end'       => $end_time,
			'data-autoplay'  => $autoplay ? '1' : '0',
		);

		// Generate unique ID for this button.
		$button_id = 'vn-ytb-btn-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<button 
			id="<?php echo esc_attr( $button_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>"
			type="button"
			<?php
			foreach ( $data_attrs as $k => $v ) {
				echo esc_attr( $k ) . '="' . esc_attr( $v ) . '" ';
			}
			?>
			aria-label="<?php echo esc_attr( $label ); ?>"
		>
			<?php if ( ! empty( $icon ) ) : ?>
				<span class="vn-ytb-btn-icon icon-<?php echo esc_attr( $icon ); ?>" aria-hidden="true"></span>
			<?php endif; ?>
			<span class="vn-ytb-btn-label"><?php echo esc_html( $label ); ?></span>
		</button>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get default label based on time range
	 *
	 * @param int $start Start time in seconds.
	 * @param int $end   End time in seconds.
	 * @return string Default label.
	 */
	private function get_default_label( int $start, int $end ): string {
		if ( $end > 0 ) {
			/* translators: 1: start time, 2: end time. */
			return sprintf(
				__( 'Play %1$s - %2$s', 'vn-youtube-embed' ),
				$this->format_time( $start ),
				$this->format_time( $end )
			);
		}

		if ( $start > 0 ) {
			/* translators: %s: start time. */
			return sprintf(
				__( 'Play from %s', 'vn-youtube-embed' ),
				$this->format_time( $start )
			);
		}

		return __( 'Play Video', 'vn-youtube-embed' );
	}

	/**
	 * Format time in seconds to readable format (MM:SS or HH:MM:SS)
	 *
	 * @param int $seconds Time in seconds.
	 * @return string Formatted time string.
	 */
	private function format_time( int $seconds ): string {
		if ( $seconds < 0 ) {
			return '0:00';
		}

		$hours   = floor( $seconds / 3600 );
		$minutes = floor( ( $seconds % 3600 ) / 60 );
		$secs    = $seconds % 60;

		if ( $hours > 0 ) {
			return sprintf( '%d:%02d:%02d', $hours, $minutes, $secs );
		}

		return sprintf( '%d:%02d', $minutes, $secs );
	}

	/**
	 * Get available button styles
	 *
	 * @return array Available styles.
	 */
	public function get_available_styles(): array {
		return array(
			'primary'   => __( 'Primary', 'vn-youtube-embed' ),
			'secondary' => __( 'Secondary', 'vn-youtube-embed' ),
			'outline'   => __( 'Outline', 'vn-youtube-embed' ),
			'link'      => __( 'Link', 'vn-youtube-embed' ),
		);
	}

	/**
	 * Get available button sizes
	 *
	 * @return array Available sizes.
	 */
	public function get_available_sizes(): array {
		return array(
			'small'  => __( 'Small', 'vn-youtube-embed' ),
			'medium' => __( 'Medium', 'vn-youtube-embed' ),
			'large'  => __( 'Large', 'vn-youtube-embed' ),
		);
	}
}
