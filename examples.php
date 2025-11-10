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

// ============================================
// VIDEO CONTROL BUTTON EXAMPLES (NEW)
// ============================================

// Ví dụ 1: Button cơ bản phát từ giây 30
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="30" label="Xem từ 0:30"]' );

// Ví dụ 2: Button phát đoạn từ 30s đến 60s
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="30" end="60" label="Xem đoạn 0:30-1:00"]' );

// Ví dụ 3: Button với style khác nhau
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" style="primary" label="Primary"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="30" style="secondary" label="Secondary"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="60" style="outline" label="Outline"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="90" style="link" label="Link Style"]' );

// Ví dụ 4: Button với kích thước khác nhau
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" size="small" label="Small"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" size="medium" label="Medium"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" size="large" label="Large"]' );

// Ví dụ 5: Button với icon
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" icon="play" label="Play"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="30" icon="forward" label="Skip"]' );
echo do_shortcode( '[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" icon="replay" label="Replay"]' );

// Ví dụ 6: Video timeline với nhiều button
?>
<div class="video-timeline">
	<h3>Nội dung video</h3>
	<?php echo do_shortcode( '[vn_youtube id="COURSE_VIDEO_ID"]' ); ?>
	
	<div class="timeline-buttons" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
		<?php 
		echo do_shortcode( '[vn_youtube_button video_id="COURSE_VIDEO_ID" start="0" end="120" label="Giới thiệu (2 phút)" style="primary"]' );
		echo do_shortcode( '[vn_youtube_button video_id="COURSE_VIDEO_ID" start="120" end="300" label="Phần 1 (3 phút)" style="secondary"]' );
		echo do_shortcode( '[vn_youtube_button video_id="COURSE_VIDEO_ID" start="300" end="600" label="Phần 2 (5 phút)" style="secondary"]' );
		echo do_shortcode( '[vn_youtube_button video_id="COURSE_VIDEO_ID" start="600" label="Kết luận" style="outline"]' );
		?>
	</div>
</div>

<?php
// Ví dụ 7: Dynamic button generation từ custom fields
function display_video_with_timeline( $video_id, $timeline_items ) {
	// Display video
	echo do_shortcode( '[vn_youtube id="' . esc_attr( $video_id ) . '"]' );
	
	// Display timeline buttons
	if ( ! empty( $timeline_items ) && is_array( $timeline_items ) ) {
		echo '<div class="video-timeline-buttons" style="margin-top: 20px;">';
		
		foreach ( $timeline_items as $item ) {
			$shortcode = sprintf(
				'[vn_youtube_button video_id="%s" start="%d" end="%d" label="%s" style="%s"]',
				esc_attr( $video_id ),
				absint( $item['start'] ),
				absint( $item['end'] ),
				esc_attr( $item['label'] ),
				esc_attr( $item['style'] ?? 'secondary' )
			);
			
			echo do_shortcode( $shortcode );
		}
		
		echo '</div>';
	}
}

// Sử dụng function trên
$timeline = array(
	array(
		'start' => 0,
		'end'   => 60,
		'label' => 'Introduction',
		'style' => 'primary',
	),
	array(
		'start' => 60,
		'end'   => 180,
		'label' => 'Main Content',
		'style' => 'secondary',
	),
	array(
		'start' => 180,
		'end'   => 0, // 0 = play to end
		'label' => 'Conclusion',
		'style' => 'outline',
	),
);

display_video_with_timeline( 'dQw4w9WgXcQ', $timeline );

// Ví dụ 8: Integration với ACF
if ( function_exists( 'get_field' ) ) {
	$video_id = get_field( 'youtube_video_id' );
	$highlights = get_field( 'video_highlights' ); // Repeater field
	
	if ( $video_id ) {
		echo do_shortcode( '[vn_youtube id="' . esc_attr( $video_id ) . '"]' );
		
		if ( $highlights ) {
			echo '<div class="video-highlights" style="margin-top: 20px;">';
			echo '<h4>Xem các phần nổi bật:</h4>';
			
			foreach ( $highlights as $highlight ) {
				$shortcode = sprintf(
					'[vn_youtube_button video_id="%s" start="%d" end="%d" label="%s"]',
					esc_attr( $video_id ),
					absint( $highlight['start_time'] ),
					absint( $highlight['end_time'] ),
					esc_attr( $highlight['label'] )
				);
				
				echo do_shortcode( $shortcode );
			}
			
			echo '</div>';
		}
	}
}

// ============================================
// HREF URL CONTROL EXAMPLES (RECOMMENDED)
// ============================================
?>

<h2>Sử dụng Href URL để Điều Khiển Video (Không cần Data Attributes)</h2>

<p><strong>Cách tốt nhất cho Flatsome và theme bị giới hạn!</strong></p>

<!-- Example 1: Link với href parameters -->
<a href="#play?video=dQw4w9WgXcQ&start=30&end=60" class="button">
	Xem đoạn 0:30 - 1:00
</a>

<!-- Example 2: Query string format -->
<a href="?video=dQw4w9WgXcQ&start=45" class="my-link">
	Xem từ 0:45
</a>

<!-- Example 3: #youtube anchor format -->
<a href="#youtube?video=VIDEO_ID&start=0&end=120" class="btn btn-primary">
	Xem phần đầu
</a>

<!-- Example 4: Flatsome button shortcode -->
<?php
echo do_shortcode( '[button text="Watch Demo" link="#play?video=dQw4w9WgXcQ&start=30&end=60" color="primary"]' );
?>

<!-- Example 5: Flatsome featured box -->
<?php
echo do_shortcode( '[featured_box title="Video Demo" link="?video=VIDEO_ID&start=45"]' );
?>

<!-- Example 6: Navigation menu với video timeline -->
<nav class="video-chapters">
	<a href="?video=COURSE_ID&start=0&end=300" class="chapter-link">1. Introduction (0:00-5:00)</a>
	<a href="?video=COURSE_ID&start=300&end=600" class="chapter-link">2. Main Content (5:00-10:00)</a>
	<a href="?video=COURSE_ID&start=600&end=900" class="chapter-link">3. Advanced (10:00-15:00)</a>
	<a href="?video=COURSE_ID&start=900" class="chapter-link">4. Conclusion (15:00-End)</a>
</nav>

<!-- Example 7: Grid layout với links -->
<div class="row">
	<div class="col-md-4">
		<div class="feature-card">
			<h4>Feature 1</h4>
			<a href="#play?video=VIDEO_ID&start=0&end=60" class="btn-outline">Watch</a>
		</div>
	</div>
	<div class="col-md-4">
		<div class="feature-card">
			<h4>Feature 2</h4>
			<a href="#play?video=VIDEO_ID&start=60&end=120" class="btn-outline">Watch</a>
		</div>
	</div>
	<div class="col-md-4">
		<div class="feature-card">
			<h4>Feature 3</h4>
			<a href="#play?video=VIDEO_ID&start=120" class="btn-outline">Watch</a>
		</div>
	</div>
</div>

<!-- Example 8: Điều khiển player cụ thể -->
<?php echo do_shortcode( '[vn_youtube id="dQw4w9WgXcQ"]' ); ?>

<div class="player-controls">
	<a href="#vn-youtube-dQw4w9WgXcQ-1234?start=0&end=30" class="control-btn">Intro</a>
	<a href="#vn-youtube-dQw4w9WgXcQ-1234?start=30&end=90" class="control-btn">Main</a>
	<a href="#vn-youtube-dQw4w9WgXcQ-1234?start=90" class="control-btn">End</a>
</div>

<!-- Example 9: WordPress menu items -->
<?php
// Trong WordPress admin > Appearance > Menus
// Thêm Custom Link với URL: #play?video=VIDEO_ID&start=30
?>

<!-- Example 10: Table of contents -->
<div class="toc">
	<h3>Video Table of Contents</h3>
	<ol>
		<li><a href="?video=VIDEO_ID&start=0&end=180">Introduction (0:00 - 3:00)</a></li>
		<li><a href="?video=VIDEO_ID&start=180&end=480">Setup Guide (3:00 - 8:00)</a></li>
		<li><a href="?video=VIDEO_ID&start=480&end=900">Advanced Features (8:00 - 15:00)</a></li>
		<li><a href="?video=VIDEO_ID&start=900">Q&A Session (15:00 - End)</a></li>
	</ol>
</div>

<?php
// Example 11: Dynamic generation với ACF
if ( function_exists( 'get_field' ) ) {
	$video_id = get_field( 'youtube_video_id' );
	$chapters = get_field( 'video_chapters' ); // Repeater
	
	if ( $video_id && $chapters ) {
		echo '<div class="video-chapters-list">';
		echo '<h3>Video Chapters</h3>';
		
		foreach ( $chapters as $chapter ) {
			$href = sprintf(
				'?video=%s&start=%d&end=%d',
				esc_attr( $video_id ),
				absint( $chapter['start_time'] ),
				absint( $chapter['end_time'] )
			);
			
			printf(
				'<a href="%s" class="chapter-item">%s</a>',
				esc_attr( $href ),
				esc_html( $chapter['title'] )
			);
		}
		
		echo '</div>';
	}
}
?>

<hr>

<h2>Sử dụng Data Attributes (Phương án thay thế)</h2>

<!-- Example 1: Button HTML thuần -->
<button data-vn-youtube-control="dQw4w9WgXcQ" data-start="30" data-end="60">
	Xem đoạn 0:30 - 1:00
</button>

<!-- Example 2: Link -->
<a href="#" data-vn-youtube-control="dQw4w9WgXcQ" data-start="45">
	Xem từ 0:45
</a>

<!-- Example 3: Bootstrap button đã styling -->
<button class="btn btn-primary btn-lg" 
		data-vn-youtube-control="dQw4w9WgXcQ" 
		data-start="30" 
		data-end="90">
	<i class="fas fa-play"></i> Xem Demo (30s)
</button>

<!-- Example 4: Navigation menu -->
<nav>
	<ul>
		<li><a href="#" data-vn-youtube-control="VIDEO_ID" data-start="0" data-end="120">Phần 1</a></li>
		<li><a href="#" data-vn-youtube-control="VIDEO_ID" data-start="120" data-end="300">Phần 2</a></li>
		<li><a href="#" data-vn-youtube-control="VIDEO_ID" data-start="300">Phần 3</a></li>
	</ul>
</nav>

<!-- Example 5: Card/Box với styling từ theme -->
<div class="feature-box vn-youtube-control" 
	 data-vn-youtube-control="dQw4w9WgXcQ" 
	 data-start="15" 
	 data-end="45">
	<div class="box-icon">
		<i class="icon-video"></i>
	</div>
	<div class="box-content">
		<h3>Tính năng A</h3>
		<p>Click để xem video demo</p>
	</div>
</div>

<!-- Example 6: Timeline HTML -->
<div class="timeline">
	<div class="timeline-item" data-vn-youtube-control="COURSE_VIDEO" data-start="0" data-end="300">
		<span class="timeline-time">00:00 - 05:00</span>
		<h4>Giới thiệu khóa học</h4>
	</div>
	<div class="timeline-item" data-vn-youtube-control="COURSE_VIDEO" data-start="300" data-end="900">
		<span class="timeline-time">05:00 - 15:00</span>
		<h4>Bài học 1</h4>
	</div>
	<div class="timeline-item" data-vn-youtube-control="COURSE_VIDEO" data-start="900">
		<span class="timeline-time">15:00 - Hết</span>
		<h4>Bài học 2</h4>
	</div>
</div>

<!-- Example 7: Table of contents -->
<div class="table-of-contents">
	<h3>Nội dung video</h3>
	<ol>
		<li>
			<a href="#" class="no-icon" data-vn-youtube-control="VIDEO_ID" data-start="0" data-end="180">
				Introduction (0:00 - 3:00)
			</a>
		</li>
		<li>
			<a href="#" class="no-icon" data-vn-youtube-control="VIDEO_ID" data-start="180" data-end="480">
				Main Content (3:00 - 8:00)
			</a>
		</li>
		<li>
			<a href="#" class="no-icon" data-vn-youtube-control="VIDEO_ID" data-start="480">
				Conclusion (8:00 - End)
			</a>
		</li>
	</ol>
</div>

<!-- Example 8: Flatsome button shortcode với data attributes -->
<?php
echo do_shortcode(
	'[button text="Watch Video" color="primary" class="custom-video-btn" 
	data-vn-youtube-control="dQw4w9WgXcQ" data-start="30" data-end="60"]'
);
?>

<!-- Example 9: Điều khiển player cụ thể -->
<?php echo do_shortcode( '[vn_youtube id="dQw4w9WgXcQ"]' ); ?>

<div class="control-buttons" style="margin-top: 20px;">
	<button class="my-btn" data-target-id="vn-youtube-dQw4w9WgXcQ-1234" data-start="0" data-end="30">
		Intro
	</button>
	<button class="my-btn" data-target-id="vn-youtube-dQw4w9WgXcQ-1234" data-start="30" data-end="90">
		Main Part
	</button>
	<button class="my-btn" data-target-id="vn-youtube-dQw4w9WgXcQ-1234" data-start="90">
		Conclusion
	</button>
</div>

<!-- Example 10: Grid layout -->
<div class="row">
	<div class="col-md-4">
		<div class="video-card" data-vn-youtube-control="VIDEO_ID" data-start="0" data-end="60">
			<img src="thumb1.jpg" alt="Feature 1">
			<h4>Feature 1</h4>
		</div>
	</div>
	<div class="col-md-4">
		<div class="video-card" data-vn-youtube-control="VIDEO_ID" data-start="60" data-end="120">
			<img src="thumb2.jpg" alt="Feature 2">
			<h4>Feature 2</h4>
		</div>
	</div>
	<div class="col-md-4">
		<div class="video-card" data-vn-youtube-control="VIDEO_ID" data-start="120">
			<img src="thumb3.jpg" alt="Feature 3">
			<h4>Feature 3</h4>
		</div>
	</div>
</div>

<?php
// Example 11: Dynamic generation with data attributes
function generate_video_chapters( $video_id, $chapters ) {
	if ( empty( $chapters ) ) {
		return;
	}
	
	echo '<div class="video-chapters">';
	echo '<h3>Chapters</h3>';
	echo '<ul class="chapter-list">';
	
	foreach ( $chapters as $chapter ) {
		printf(
			'<li><a href="#" data-vn-youtube-control="%s" data-start="%d" data-end="%d">%s</a></li>',
			esc_attr( $video_id ),
			absint( $chapter['start'] ),
			absint( $chapter['end'] ),
			esc_html( $chapter['title'] )
		);
	}
	
	echo '</ul>';
	echo '</div>';
}

// Usage
$chapters = array(
	array(
		'title' => 'Chapter 1: Introduction',
		'start' => 0,
		'end'   => 120,
	),
	array(
		'title' => 'Chapter 2: Getting Started',
		'start' => 120,
		'end'   => 300,
	),
	array(
		'title' => 'Chapter 3: Advanced Topics',
		'start' => 300,
		'end'   => 0,
	),
);

generate_video_chapters( 'dQw4w9WgXcQ', $chapters );

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
		
		// NEW: Phát video segment programmatically
		window.vnYouTubeEmbedInstance.playSegment('vn-youtube-dQw4w9WgXcQ-1234', 30, 60);
		
		// NEW: Mở lightbox với video segment
		window.vnYouTubeEmbedInstance.openLightboxWithSegment('dQw4w9WgXcQ', 45, 90, true);
		
		// NEW: Lấy active iframes
		const activeIframes = window.vnYouTubeEmbedInstance.getActiveIframes();
		console.log('Active iframes:', activeIframes.size);
	}
});

// Lắng nghe sự kiện khi video được load
jQuery(document).on('vnYouTubeLoaded', '.vn-youtube-player', function(event, data) {
	console.log('Video loaded:', data.videoId);
	console.log('Autoplay:', data.autoplay);
});

// Custom button click handler
jQuery('#my-custom-button').on('click', function() {
	if (window.vnYouTubeEmbedInstance) {
		// Phát đoạn video từ 30s đến 90s
		window.vnYouTubeEmbedInstance.openLightboxWithSegment('dQw4w9WgXcQ', 30, 90, true);
	}
});
</script>
