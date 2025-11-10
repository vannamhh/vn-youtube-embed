# VN YouTube Embed Plugin

Tối ưu hiệu suất WordPress bằng cách thay thế iframe YouTube bằng thumbnail tải nhanh, chỉ load video khi người dùng click. Hỗ trợ cache thumbnail và tích hợp UX Builder.

## 🚀 Tính năng chính

### ⚡ Tối ưu hiệu suất
- **Lazy Loading**: Video chỉ load khi người dùng click
- **Cache thumbnail**: Lưu thumbnail tại local để tránh tải lại
- **Tự động chọn chất lượng cao nhất**: Tự động lấy thumbnail chất lượng tốt nhất có sẵn
- **Tránh trùng lặp**: Kiểm tra và tránh tải thumbnail trùng lặp

### 🎨 Giao diện đẹp
- **Play button tùy chỉnh**: Thiết kế giống YouTube chính thức
- **Responsive**: Tự động điều chỉnh theo kích thước màn hình
- **Hover effects**: Hiệu ứng mượt mà khi di chuột
- **Dark mode**: Hỗ trợ chế độ tối

### 🔧 Dễ sử dụng
- **Shortcode đơn giản**: `[vn_youtube id="VIDEO_ID"]`
- **Tích hợp UX Builder**: Dành cho theme Flatsome
- **Admin panel**: Cài đặt chi tiết và quản lý cache
- **Tương thích**: Hoạt động với mọi theme WordPress

## 📋 Yêu cầu hệ thống

- WordPress 5.0+
- PHP 7.4+
- Theme hỗ trợ jQuery (hầu hết các theme)

## 🛠️ Cài đặt

1. Upload thư mục plugin vào `/wp-content/plugins/`
2. Kích hoạt plugin trong WordPress Admin
3. Vào **Settings > YouTube Embed** để cấu hình

## 📝 Cách sử dụng

### Shortcode cơ bản
```
[vn_youtube id="dQw4w9WgXcQ"]
```

### Với thumbnail tùy chỉnh
```
[vn_youtube id="dQw4w9WgXcQ" custom_thumbnail="https://example.com/thumb.jpg"]
```

### Với kích thước tùy chỉnh
```
[vn_youtube id="dQw4w9WgXcQ" width="640" height="360"]
```

### Tất cả tùy chọn
```
[vn_youtube 
    id="dQw4w9WgXcQ" 
    quality="maxresdefault"
    width="100%" 
    height=""
    autoplay="true"
    lazy_load="true"
    custom_thumbnail=""
    class="my-custom-class"
]
```

### New: Lightbox

- Global toggle in Settings to enable lightbox.
- Per-shortcode attribute: `lightbox="true|false"` (overrides global).
- When opening in lightbox, video starts playing immediately if autoplay is enabled.

### Với UX Builder (Flatsome)
Nếu bạn sử dụng theme Flatsome, plugin sẽ tự động thêm element "VN YouTube Embed" vào UX Builder.

## 🎮 Video Control Buttons (NEW)

Plugin hiện hỗ trợ tạo các button tùy chỉnh để điều khiển phát đoạn video YouTube với thời điểm bắt đầu và kết thúc xác định.

### Shortcode button cơ bản

```
[vn_youtube_button video_id="dQw4w9WgXcQ" start="30" end="60" label="Xem đoạn 0:30-1:00"]
```

### Điều khiển player cụ thể

```
<!-- Player -->
[vn_youtube id="dQw4w9WgXcQ"]

<!-- Button điều khiển player trên -->
[vn_youtube_button target="vn-youtube-dQw4w9WgXcQ-1234" start="10" end="30" label="Phát từ 0:10"]
```

### Button với styles khác nhau

```
<!-- Primary style (mặc định - đỏ YouTube) -->
[vn_youtube_button video_id="VIDEO_ID" start="0" style="primary" label="Bắt đầu"]

<!-- Secondary style (xám đen) -->
[vn_youtube_button video_id="VIDEO_ID" start="30" style="secondary" label="Từ giây 30"]

<!-- Outline style (viền đỏ) -->
[vn_youtube_button video_id="VIDEO_ID" start="60" end="90" style="outline" label="1:00 - 1:30"]

<!-- Link style (dạng text link) -->
[vn_youtube_button video_id="VIDEO_ID" start="120" style="link" label="Phần 2"]
```

### Button với kích thước khác nhau

```
<!-- Small -->
[vn_youtube_button video_id="VIDEO_ID" start="0" size="small" label="Nhỏ"]

<!-- Medium (mặc định) -->
[vn_youtube_button video_id="VIDEO_ID" start="0" size="medium" label="Trung bình"]

<!-- Large -->
[vn_youtube_button video_id="VIDEO_ID" start="0" size="large" label="Lớn"]
```

### Button với icon

```
[vn_youtube_button video_id="VIDEO_ID" start="0" icon="play" label="Phát video"]
[vn_youtube_button video_id="VIDEO_ID" start="30" icon="forward" label="Tua đến 0:30"]
[vn_youtube_button video_id="VIDEO_ID" start="0" icon="replay" label="Xem lại"]
```

### Tất cả tùy chọn button

```
[vn_youtube_button 
    target=""              <!-- ID của player (vn-youtube-VIDEO_ID-XXXX) -->
    video_id=""            <!-- YouTube video ID (nếu không dùng target) -->
    start="0"              <!-- Thời điểm bắt đầu (giây) -->
    end=""                 <!-- Thời điểm kết thúc (giây, để trống = phát đến hết) -->
    label="Play Video"     <!-- Text hiển thị trên button -->
    icon="play"            <!-- Icon: play, pause, forward, backward, replay, stop -->
    class=""               <!-- CSS class tùy chỉnh -->
    style="primary"        <!-- Style: primary, secondary, outline, link -->
    size="medium"          <!-- Size: small, medium, large -->
    autoplay="true"        <!-- Tự động phát khi click -->
]
```

### Ví dụ thực tế

#### Case 1: Một video với nhiều button phát các đoạn khác nhau

```
<!-- Video player -->
[vn_youtube id="dQw4w9WgXcQ"]

<!-- Buttons điều khiển các đoạn -->
<div class="vn-ytb-button-group">
[vn_youtube_button video_id="dQw4w9WgXcQ" start="0" end="30" label="Phần mở đầu" style="primary"]
[vn_youtube_button video_id="dQw4w9WgXcQ" start="30" end="60" label="Phần 1" style="secondary"]
[vn_youtube_button video_id="dQw4w9WgXcQ" start="60" end="120" label="Phần 2" style="secondary"]
[vn_youtube_button video_id="dQw4w9WgXcQ" start="120" label="Phần kết" style="outline"]
</div>
```

#### Case 2: Button mở video trong lightbox

```
<!-- Button sẽ mở video trong lightbox và phát đoạn chỉ định -->
[vn_youtube_button video_id="dQw4w9WgXcQ" start="45" end="90" label="Xem demo" style="primary" size="large"]
```

#### Case 3: Playlist với timeline

```
<h3>Nội dung khóa học</h3>

[vn_youtube id="COURSE_VIDEO_ID"]

<ul class="course-timeline">
    <li>[vn_youtube_button video_id="COURSE_VIDEO_ID" start="0" end="300" label="1. Giới thiệu (5 phút)" style="link"]</li>
    <li>[vn_youtube_button video_id="COURSE_VIDEO_ID" start="300" end="900" label="2. Kiến thức cơ bản (10 phút)" style="link"]</li>
    <li>[vn_youtube_button video_id="COURSE_VIDEO_ID" start="900" end="1800" label="3. Thực hành (15 phút)" style="link"]</li>
    <li>[vn_youtube_button video_id="COURSE_VIDEO_ID" start="1800" label="4. Tổng kết" style="link"]</li>
</ul>
```

### Lưu ý về Video Control Buttons

- Button có thể điều khiển player có sẵn trên trang (dùng `target`) hoặc mở video mới trong lightbox (dùng `video_id`)
- Khi `end` không được chỉ định, video sẽ phát từ `start` đến hết
- Button tự động format thời gian trong label nếu không có label tùy chỉnh
- Hỗ trợ đầy đủ responsive và accessibility
- Tương thích với mọi theme WordPress

## 🔗 Sử dụng Link Href để Điều Khiển Video (Không cần Data Attributes)

**Cách tốt nhất cho Flatsome và các theme/builder bị giới hạn!**

Bạn chỉ cần thêm parameters vào **href URL** của link. Plugin sẽ tự động nhận diện và không thêm bất kỳ CSS nào.

### ⭐ Cú pháp Href (Recommended)

```html
<!-- Cách 1: Dùng # anchor -->
<a href="#play?video=VIDEO_ID&start=30&end=60">Xem đoạn 0:30-1:00</a>

<!-- Cách 2: Dùng query string -->
<a href="?video=VIDEO_ID&start=45">Xem từ 0:45</a>

<!-- Cách 3: Dùng #youtube anchor -->
<a href="#youtube?video=VIDEO_ID&start=0&end=120">Xem phần đầu</a>

<!-- Cách 4: Điều khiển player cụ thể -->
<a href="#vn-youtube-VIDEO_ID-1234?start=30">Phát từ 0:30</a>
```

### 📋 URL Parameters

| Parameter | Mô tả | Ví dụ |
|-----------|-------|-------|
| `video` hoặc `v` hoặc `id` | YouTube video ID (bắt buộc) | `video=dQw4w9WgXcQ` |
| `start` hoặc `time` hoặc `t` | Giây bắt đầu | `start=30` |
| `end` | Giây kết thúc | `end=60` |
| `target` hoặc `player` | ID player cụ thể | `target=vn-youtube-XXX` |
| `autoplay` | Tự động phát | `autoplay=true` |

### 💡 Ví dụ với Flatsome Shortcode

```
[button text="Xem Video" link="#play?video=dQw4w9WgXcQ&start=30&end=60"]

[ux_text_box text="Click here" link="?video=VIDEO_ID&start=0"]

[featured_box title="Demo" link="#youtube?video=VIDEO_ID&start=45"]
```

### 🎯 Ví dụ HTML thuần

```html
<!-- Button đã có class từ theme -->
<a href="#play?video=dQw4w9WgXcQ&start=30&end=60" class="button primary">
    Xem Demo
</a>

<!-- Link trong menu -->
<a href="?video=VIDEO_ID&start=0&end=120" class="nav-link">
    Phần 1
</a>

<!-- Card/Box với link -->
<div class="feature-box">
    <h3>Feature Title</h3>
    <a href="#youtube?video=VIDEO_ID&start=15&end=45" class="btn">
        Watch Demo
    </a>
</div>
```

### 📝 Ví dụ Timeline/Navigation

```html
<nav class="video-nav">
    <a href="?video=COURSE_ID&start=0&end=300">1. Introduction (0:00-5:00)</a>
    <a href="?video=COURSE_ID&start=300&end=600">2. Main Content (5:00-10:00)</a>
    <a href="?video=COURSE_ID&start=600&end=900">3. Advanced (10:00-15:00)</a>
    <a href="?video=COURSE_ID&start=900">4. Conclusion (15:00-End)</a>
</nav>
```

### ✅ Lợi ích của Href Method

✅ **Không cần data attributes** - chỉ dùng href thuần  
✅ **Không thêm CSS nào** - giữ 100% style từ theme  
✅ **Hoạt động với mọi builder** - Flatsome, Elementor, WPBakery  
✅ **Không can thiệp code** - chỉ thêm vào href  
✅ **SEO friendly** - vẫn là link HTML chuẩn  

---

## 🔗 (Phương án 2) Sử dụng Data Attributes

Nếu bạn có thể thêm data attributes, đây là cách thay thế:

### Cú pháp cơ bản

```html
<!-- Button HTML thuần -->
<button data-vn-youtube-control="VIDEO_ID" data-start="30" data-end="60">
    Xem đoạn 0:30 - 1:00
</button>

<!-- Link -->
<a href="#" data-vn-youtube-control="dQw4w9WgXcQ" data-start="45">
    Xem từ 0:45
</a>

<!-- Div hoặc bất kỳ element nào -->
<div class="my-custom-button" data-vn-youtube-control="VIDEO_ID" data-start="0" data-end="30">
    Click để xem giới thiệu
</div>
```

### Data Attributes hỗ trợ

| Attribute | Bắt buộc | Mô tả | Ví dụ |
|-----------|----------|-------|-------|
| `data-vn-youtube-control` | ✅ | YouTube video ID | `dQw4w9WgXcQ` |
| `data-start` | ⚪ | Giây bắt đầu (mặc định: 0) | `30` |
| `data-end` | ⚪ | Giây kết thúc (mặc định: đến hết) | `60` |
| `data-target-id` | ⚪ | ID player cụ thể để điều khiển | `vn-youtube-VIDEO_ID-1234` |
| `data-autoplay` | ⚪ | Tự động phát (mặc định: true) | `true` hoặc `false` |

### Aliases (tên thay thế)

Các attribute sau cũng được hỗ trợ:

- `data-video-id` thay cho `data-vn-youtube-control`
- `data-time-start` thay cho `data-start`
- `data-time-end` thay cho `data-end`
- `data-target` thay cho `data-target-id`

### Ví dụ thực tế

#### 1. Button Bootstrap đã styling sẵn

```html
<button class="btn btn-primary btn-lg" 
        data-vn-youtube-control="dQw4w9WgXcQ" 
        data-start="30" 
        data-end="90">
    <i class="fas fa-play"></i> Xem Demo (30s)
</button>
```

#### 2. Navigation menu items

```html
<nav>
    <ul>
        <li><a href="#" data-vn-youtube-control="VIDEO_ID" data-start="0" data-end="120">Phần 1</a></li>
        <li><a href="#" data-vn-youtube-control="VIDEO_ID" data-start="120" data-end="300">Phần 2</a></li>
        <li><a href="#" data-vn-youtube-control="VIDEO_ID" data-start="300">Phần 3</a></li>
    </ul>
</nav>
```

#### 3. Card/Box đã có styling từ theme

```html
<div class="feature-box" data-vn-youtube-control="dQw4w9WgXcQ" data-start="15" data-end="45">
    <div class="box-icon">
        <i class="icon-video"></i>
    </div>
    <div class="box-content">
        <h3>Tính năng A</h3>
        <p>Click để xem video demo tính năng này</p>
    </div>
</div>
```

#### 4. Timeline với HTML list

```html
<div class="timeline">
    <div class="timeline-item" data-vn-youtube-control="COURSE_VIDEO" data-start="0" data-end="300">
        <span class="timeline-time">00:00 - 05:00</span>
        <h4>Giới thiệu khóa học</h4>
    </div>
    <div class="timeline-item" data-vn-youtube-control="COURSE_VIDEO" data-start="300" data-end="900">
        <span class="timeline-time">05:00 - 15:00</span>
        <h4>Bài học 1: Cơ bản</h4>
    </div>
    <div class="timeline-item" data-vn-youtube-control="COURSE_VIDEO" data-start="900">
        <span class="timeline-time">15:00 - Hết</span>
        <h4>Bài học 2: Nâng cao</h4>
    </div>
</div>
```

#### 5. Table of contents

```html
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
```

#### 6. Grid layout với Flatsome shortcode

```
[row]
[col span="4"]
<div class="custom-video-card" data-vn-youtube-control="VIDEO_ID" data-start="0" data-end="60">
    <h4>Feature 1</h4>
    <p>Click to watch</p>
</div>
[/col]
[col span="4"]
<div class="custom-video-card" data-vn-youtube-control="VIDEO_ID" data-start="60" data-end="120">
    <h4>Feature 2</h4>
    <p>Click to watch</p>
</div>
[/col]
[col span="4"]
<div class="custom-video-card" data-vn-youtube-control="VIDEO_ID" data-start="120">
    <h4>Feature 3</h4>
    <p>Click to watch</p>
</div>
[/col]
[/row]
```

#### 7. Điều khiển player cụ thể

```html
<!-- Video player -->
[vn_youtube id="dQw4w9WgXcQ"]

<!-- Button điều khiển player trên (không mở lightbox mới) -->
<button class="my-styled-btn" 
        data-target-id="vn-youtube-dQw4w9WgXcQ-1234" 
        data-start="30" 
        data-end="60">
    Phát đoạn 0:30 - 1:00
</button>
```

#### 8. Tích hợp với JavaScript frameworks

```html
<!-- Vue.js -->
<button @click.prevent 
        :data-vn-youtube-control="videoId" 
        :data-start="segment.start" 
        :data-end="segment.end">
    {{ segment.label }}
</button>

<!-- React (JSX) -->
<button 
    data-vn-youtube-control={videoId}
    data-start={30}
    data-end={60}
    onClick={(e) => e.preventDefault()}>
    Watch Segment
</button>

<!-- Alpine.js -->
<button x-data 
        data-vn-youtube-control="VIDEO_ID" 
        :data-start="currentTime"
        @click.prevent>
    Play from current time
</button>
```

### Lợi ích của Data Attributes

✅ **Dùng được với bất kỳ HTML element nào** đã code sẵn  
✅ **Không thêm CSS tự động** - giữ nguyên style từ theme  
✅ **Tương thích với page builders**  
✅ **Không cần học shortcode mới**  

## ⚙️ Cài đặt Plugin

### General Settings
- **Default Thumbnail Quality**: Chọn chất lượng thumbnail mặc định
- **Cache Duration**: Thời gian lưu cache (1-365 ngày)
- **Enable Lazy Loading**: Bật/tắt lazy loading
- **Enable Autoplay**: Tự động phát khi click
- **Custom Play Button**: Sử dụng nút play tùy chỉnh

### Cache Management
- Xem thống kê cache (số file, dung lượng)
- Xóa cache thủ công
- Tự động dọn dẹp cache theo lịch

## 🎯 Tham số Shortcode

| Tham số | Mặc định | Mô tả |
|---------|----------|-------|
| `id` | - | ID video YouTube (bắt buộc) |
| `quality` | maxresdefault | Chất lượng thumbnail |
| `width` | 100% | Chiều rộng container |
| `height` | - | Chiều cao container |
| `autoplay` | true | Tự động phát |
| `lazy_load` | true | Lazy loading |
| `custom_thumbnail` | - | URL thumbnail tùy chỉnh |
| `class` | - | CSS class thêm |

## 🎮 Tham số Shortcode Button

| Tham số | Mặc định | Mô tả |
|---------|----------|-------|
| `target` | - | ID của player target (vn-youtube-VIDEO_ID-XXXX) |
| `video_id` | - | YouTube video ID (nếu không dùng target) |
| `start` | 0 | Thời điểm bắt đầu (giây) |
| `end` | - | Thời điểm kết thúc (giây, để trống = đến hết) |
| `label` | Auto | Text hiển thị trên button |
| `icon` | play | Icon class (play, pause, forward, etc) |
| `class` | - | CSS class tùy chỉnh |
| `style` | primary | Style: primary, secondary, outline, link |
| `size` | medium | Size: small, medium, large |
| `autoplay` | true | Tự động phát khi click |

### Chất lượng thumbnail
- `maxresdefault`: 1920x1080 (tốt nhất)
- `sddefault`: 640x480 (tiêu chuẩn)
- `hqdefault`: 480x360 (chất lượng cao)
- `mqdefault`: 320x180 (trung bình)

## 🔍 API & Hooks

### JavaScript API
```javascript
// Load video theo ID
window.vnYouTubeEmbedInstance.loadVideoById('VIDEO_ID', 'container-id');

// Lấy tất cả players
const players = window.vnYouTubeEmbedInstance.getAllPlayers();

// Tìm player theo video ID
const player = window.vnYouTubeEmbedInstance.getPlayerByVideoId('VIDEO_ID');

// Phát đoạn video với start và end time
window.vnYouTubeEmbedInstance.playSegment('vn-youtube-VIDEO_ID-1234', 30, 60);

// Mở video trong lightbox với segment
window.vnYouTubeEmbedInstance.openLightboxWithSegment('VIDEO_ID', 30, 60, true);

// Lấy danh sách iframe đang active
const activeIframes = window.vnYouTubeEmbedInstance.getActiveIframes();
```

### WordPress Hooks
```php
// Custom thumbnail URL
add_filter('vn_youtube_embed_thumbnail_url', function($url, $video_id, $quality) {
    // Your custom logic
    return $url;
}, 10, 3);

// Custom iframe parameters
add_filter('vn_youtube_embed_iframe_params', function($params, $video_id) {
    // Your custom parameters
    return $params;
}, 10, 2);
```

## 🐛 Troubleshooting

### Video không hiển thị
1. Kiểm tra Video ID có chính xác
2. Đảm bảo video không bị private/unlisted
3. Kiểm tra console browser có lỗi JavaScript

### Thumbnail không load
1. Kiểm tra kết nối internet
2. Xóa cache và thử lại
3. Thử chất lượng thumbnail thấp hơn

### UX Builder không hiển thị element
1. Đảm bảo đang dùng theme Flatsome
2. Kiểm tra plugin đã kích hoạt
3. Refresh UX Builder

## 🔄 Changelog

### 1.2.0
- **NEW**: Video Control Buttons - Tạo button tùy chỉnh để điều khiển phát đoạn video
- **NEW**: Href URL Control - Điều khiển video qua link href (không cần data attributes)
- Thêm shortcode `[vn_youtube_button]` với đầy đủ tùy chọn
- Hỗ trợ phát video từ thời điểm start đến end
- Hỗ trợ nhiều format URL: `#play?video=...`, `?video=...`, `#youtube?...`
- Không thêm CSS tự động - giữ 100% style từ theme
- Button styles: primary, secondary, outline, link (optional)
- Button sizes: small, medium, large (optional)
- Tích hợp icons và labels tùy chỉnh
- API JavaScript mở rộng cho video segment control
- Tương thích hoàn hảo với Flatsome và các page builder bị giới hạn

### 1.1.0
- Thêm hỗ trợ lightbox mode
- Cải thiện responsive design
- Tối ưu lazy loading

### 1.0.0
- Phiên bản đầu tiên
- Hỗ trợ shortcode cơ bản
- Cache thumbnail tự động
- Tích hợp UX Builder
- Admin panel đầy đủ

## 💡 Đóng góp

Nếu bạn muốn đóng góp cho plugin:

1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Tạo Pull Request

## 📞 Hỗ trợ

- Website: [https://wpmasterynow.com/](https://wpmasterynow.com/)
- Email: support@wpmasterynow.com

## 📄 Giấy phép

GPL v2 or later

---

**Developed with ❤️ by Van Nam**
