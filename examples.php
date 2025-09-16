<?php
/**
 * VN YouTube Embed - Usage Examples
 *
 * File này chỉ để tham khảo cách sử dụng plugin, không cần include vào theme.
 *
 * @package VN_YouTube_Embed
 */

// Ví dụ shortcode cơ bản
echo do_shortcode( '[vn_youtube id="YWeK3Q7qXaw"]' );

// Ví dụ với tất cả tùy chọn
echo do_shortcode(
	'
[vn_youtube 
    id="YWeK3Q7qXaw" 
    quality="maxresdefault"
    width="100%" 
    height=""
    autoplay="true"
    lazy_load="true"
    custom_thumbnail=""
    class="my-custom-class"
]
'
);

// Sử dụng trong PHP template
function display_youtube_video( $video_id, $options = array() ) {
	$default_options = array(
		'id'        => $video_id,
		'quality'   => 'maxresdefault',
		'width'     => '100%',
		'autoplay'  => true,
		'lazy_load' => true,
	);

	$options = wp_parse_args( $options, $default_options );

	$shortcode = '[vn_youtube';
	foreach ( $options as $key => $value ) {
		if ( is_bool( $value ) ) {
			$value = $value ? 'true' : 'false';
		}
		$shortcode .= ' ' . $key . '="' . esc_attr( $value ) . '"';
	}
	$shortcode .= ']';

	return do_shortcode( $shortcode );
}

// Sử dụng function trên
echo display_youtube_video(
	'dQw4w9WgXcQ',
	array(
		'width'    => '800px',
		'height'   => '450px',
		'autoplay' => false,
	)
);

// Hook để tùy chỉnh thumbnail URL
add_filter(
	'vn_youtube_embed_thumbnail_url',
	function ( $url, $video_id, $quality ) {
		// Có thể tùy chỉnh URL thumbnail tại đây
		// Ví dụ: sử dụng CDN khác

		return $url;
	},
	10,
	3
);

// Hook để tùy chỉnh tham số iframe
add_filter(
	'vn_youtube_embed_iframe_params',
	function ( $params, $video_id ) {
		// Thêm tham số tùy chỉnh
		$params['cc_load_policy'] = 1; // Hiển thị subtitle
		$params['hl']             = 'vi'; // Ngôn ngữ

		return $params;
	},
	10,
	2
);

// JavaScript API examples
?>
<script>
// Sử dụng JavaScript API
document.addEventListener('DOMContentLoaded', function() {
	// Load video theo ID container
	if (window.vnYouTubeEmbedInstance) {
		window.vnYouTubeEmbedInstance.loadVideoById('dQw4w9WgXcQ', 'my-container-id');
		
		// Lấy tất cả players
		const players = window.vnYouTubeEmbedInstance.getAllPlayers();
		console.log('Total players:', players.length);
		
		// Tìm player theo video ID
		const player = window.vnYouTubeEmbedInstance.getPlayerByVideoId('dQw4w9WgXcQ');
		if (player) {
			console.log('Found player for video:', player.videoId);
		}
	}
});

// Lắng nghe sự kiện khi video được load
$(document).on('vnYouTubeLoaded', '.vn-youtube-player', function(event, data) {
	console.log('Video loaded:', data.videoId);
	console.log('Autoplay:', data.autoplay);
});
</script>
