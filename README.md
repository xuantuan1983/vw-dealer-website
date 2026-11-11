# VW Dealer Website – caoxuantuan.net (demo)

Website tin tức & giới thiệu sản phẩm cho đại lý Volkswagen, xây trên WordPress.
Cấu trúc trang tham khảo giaxevinfasthcm.com; hình ảnh xe lấy từ volkswagentanthuan.vn.

## Thành phần

```
wp-content/
├── themes/vw-dealer/            # Theme tùy biến (không phụ thuộc Elementor/Astra)
│   ├── front-page.php           # Trang chủ: hero slider → 4 ô CTA → lưới dòng xe → giới thiệu → CTA → tin tức → video
│   ├── single-vw_car.php        # Chi tiết dòng xe: giá, phiên bản, điểm nổi bật, gallery, thông số, FAQ, form báo giá
│   ├── page-*.php               # Template: Các dòng xe, Bảng giá, Trả góp (máy tính), Lái thử, Dịch vụ, Giới thiệu, Khuyến mãi, Liên hệ
│   └── assets/css|js|img
└── plugins/vw-dealer-core/      # Post type "Dòng xe" (vw_car), "Khách đăng ký" (vw_lead), form, trang Setup
    ├── inc/importer.php         # Xóa dữ liệu cũ, nhập demo, hàng đợi tải ảnh về Media
    ├── inc/github.php           # Kéo zip từ GitHub và cập nhật theme/plugin
    └── data/*.json              # models.json (11 dòng xe), news.json, site.json (thông tin đại lý, hero, CTA)
deploy/bootstrap-snippet.php     # Snippet cài đặt lần đầu (Code Snippets)
```

## Triển khai lần đầu (hosting không có SSH/FTP)

1. Push repo này lên GitHub (public) – nhánh `main`.
2. Trong WP Admin → **Snippets → Add New**, dán nội dung `deploy/bootstrap-snippet.php` (bỏ dòng `<?php`), lưu & kích hoạt.
3. Mở `https://caoxuantuan.net/wp-admin/?vwd_bootstrap=1` → theme + plugin được tải từ GitHub và kích hoạt.
4. Vào **VW Dealer → Setup & dữ liệu**:
   1. *Xóa dữ liệu cũ* (giữ LearnPress/người dùng) – bấm lại nếu còn mục.
   2. *Nhập dữ liệu demo* – tạo dòng xe, bài viết, trang, menu, kích hoạt theme.
   3. *Tải ảnh* – bật "tự động" để tải toàn bộ ảnh về Media.
5. **VW Dealer → Thông tin đại lý**: sửa tên, hotline, địa chỉ, email nhận đăng ký.
6. Giao diện → Tùy biến → Logo website: tải logo đại lý (theme không kèm logo Volkswagen).

## Cập nhật sau này

Sửa code → `git push` → WP Admin → **VW Dealer → Setup → Kéo code mới nhất**.

## Phát triển cục bộ

Sao chép `wp-content/` vào một WordPress local (LocalWP/XAMPP), kích hoạt plugin **VW Dealer Core** và theme **VW Dealer**, chạy Setup → Nhập dữ liệu demo.

## Lưu ý

- Giá xe / ưu đãi trong `data/models.json` chỉ mang tính demo.
- Ảnh được tải từ `source_base` trong `site.json` khi chạy "Tải ảnh"; các ảnh đã tải được nhớ qua meta `_vwd_source` nên không tải trùng.
- Form đăng ký lưu vào **Khách đăng ký** và gửi email tới địa chỉ cấu hình.
