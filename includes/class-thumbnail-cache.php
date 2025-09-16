<?php
/**
 * Thumbnail Cache Management
 *
 * @package VN_YouTube_Embed
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VN_YouTube_Embed_Thumbnail_Cache
 */
class VN_YouTube_Embed_Thumbnail_Cache {

	/**
	 * Instance of this class
	 *
	 * @var VN_YouTube_Embed_Thumbnail_Cache
	 */
	private static $instance = null;

	/**
	 * Cache directory path
	 *
	 * @var string
	 */
	private $cache_dir;

	/**
	 * Cache directory URL
	 *
	 * @var string
	 */
	private $cache_url;

	/**
	 * Get instance
	 *
	 * @return VN_YouTube_Embed_Thumbnail_Cache
	 */
	public static function get_instance(): VN_YouTube_Embed_Thumbnail_Cache {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_cache_directories();
		$this->init_hooks();
	}

	/**
	 * Initialize cache directories
	 */
	private function init_cache_directories(): void {
		$upload_dir      = wp_upload_dir();
		$this->cache_dir = $upload_dir['basedir'] . '/vn-youtube-thumbnails';
		$this->cache_url = $upload_dir['baseurl'] . '/vn-youtube-thumbnails';

		// Create cache directory if it doesn't exist.
		if ( ! file_exists( $this->cache_dir ) ) {
			wp_mkdir_p( $this->cache_dir );
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks(): void {
		add_action( 'vn_youtube_embed_cleanup', array( $this, 'cleanup_old_thumbnails' ) );
	}

	/**
	 * Get thumbnail URL for YouTube video
	 *
	 * @param string $video_id YouTube video ID.
	 * @param string $quality Thumbnail quality.
	 * @return string Thumbnail URL.
	 */
	public function get_thumbnail_url( string $video_id, string $quality = 'maxresdefault' ): string {
		$cached_file = $this->get_cached_thumbnail_path( $video_id, $quality );

		// Return cached thumbnail if exists and not expired.
		if ( $this->is_cache_valid( $cached_file ) ) {
			return $this->get_cached_thumbnail_url( $video_id, $quality );
		}

		// Download and cache thumbnail.
		$remote_url = $this->get_remote_thumbnail_url( $video_id, $quality );
		$cached_url = $this->cache_thumbnail( $video_id, $quality, $remote_url );

		return $cached_url ? $cached_url : $remote_url;
	}

	/**
	 * Get remote thumbnail URL from YouTube
	 *
	 * @param string $video_id YouTube video ID.
	 * @param string $quality Thumbnail quality.
	 * @return string Remote thumbnail URL.
	 */
	private function get_remote_thumbnail_url( string $video_id, string $quality ): string {
		$quality_urls = array(
			'maxresdefault' => "https://i.ytimg.com/vi/{$video_id}/maxresdefault.jpg",
			'sddefault'     => "https://i.ytimg.com/vi/{$video_id}/sddefault.jpg",
			'hqdefault'     => "https://i.ytimg.com/vi/{$video_id}/hqdefault.jpg",
			'mqdefault'     => "https://i.ytimg.com/vi/{$video_id}/mqdefault.jpg",
			'default'       => "https://i.ytimg.com/vi/{$video_id}/default.jpg",
		);

		return $quality_urls[ $quality ] ?? $quality_urls['maxresdefault'];
	}

	/**
	 * Cache thumbnail locally
	 *
	 * @param string $video_id YouTube video ID.
	 * @param string $quality Thumbnail quality.
	 * @param string $remote_url Remote thumbnail URL.
	 * @return string|false Cached thumbnail URL or false on failure.
	 */
	private function cache_thumbnail( string $video_id, string $quality, string $remote_url ) {
		$response = wp_remote_get( $remote_url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$image_data = wp_remote_retrieve_body( $response );
		if ( empty( $image_data ) ) {
			return false;
		}

		$cached_file = $this->get_cached_thumbnail_path( $video_id, $quality );

		// Use WP filesystem.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( $wp_filesystem && $wp_filesystem->put_contents( $cached_file, $image_data, FS_CHMOD_FILE ) ) {
			return $this->get_cached_thumbnail_url( $video_id, $quality );
		}

		return false;
	}

	/**
	 * Get cached thumbnail file path
	 *
	 * @param string $video_id YouTube video ID.
	 * @param string $quality Thumbnail quality.
	 * @return string File path.
	 */
	private function get_cached_thumbnail_path( string $video_id, string $quality ): string {
		return $this->cache_dir . "/{$video_id}_{$quality}.jpg";
	}

	/**
	 * Get cached thumbnail URL
	 *
	 * @param string $video_id YouTube video ID.
	 * @param string $quality Thumbnail quality.
	 * @return string URL.
	 */
	private function get_cached_thumbnail_url( string $video_id, string $quality ): string {
		return $this->cache_url . "/{$video_id}_{$quality}.jpg";
	}

	/**
	 * Check if cached file is valid
	 *
	 * @param string $file_path File path.
	 * @return bool True if valid.
	 */
	private function is_cache_valid( string $file_path ): bool {
		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		$options        = get_option( 'vn_youtube_embed_options', array() );
		$cache_duration = $options['cache_duration'] ?? 30;
		$file_time      = filemtime( $file_path );

		return ( time() - $file_time ) < ( $cache_duration * DAY_IN_SECONDS );
	}

	/**
	 * Cleanup old thumbnails
	 */
	public function cleanup_old_thumbnails(): void {
		$options        = get_option( 'vn_youtube_embed_options', array() );
		$cache_duration = $options['cache_duration'] ?? 30;
		$cutoff_time    = time() - ( $cache_duration * DAY_IN_SECONDS );

		$files = glob( $this->cache_dir . '/*.jpg' );
		if ( ! $files ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( filemtime( $file ) < $cutoff_time ) {
				wp_delete_file( $file );
			}
		}
	}

	/**
	 * Clear all cached thumbnails
	 */
	public function clear_cache(): void {
		$files = glob( $this->cache_dir . '/*.jpg' );
		if ( ! $files ) {
			return;
		}

		foreach ( $files as $file ) {
			wp_delete_file( $file );
		}
	}

	/**
	 * Get cache statistics
	 *
	 * @return array Cache statistics.
	 */
	public function get_cache_stats(): array {
		$files = glob( $this->cache_dir . '/*.jpg' );
		$count = $files ? count( $files ) : 0;
		$size  = 0;

		if ( $files ) {
			foreach ( $files as $file ) {
				$size += filesize( $file );
			}
		}

		return array(
			'count' => $count,
			'size'  => $size,
		);
	}
}
