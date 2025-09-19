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
