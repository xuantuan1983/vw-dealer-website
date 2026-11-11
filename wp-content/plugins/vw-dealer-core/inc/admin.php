<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_menu_page( 'VW Dealer', 'VW Dealer', 'manage_options', 'vw-dealer', 'vwd_admin_page', 'dashicons-admin-generic', 3 );
	add_submenu_page( 'vw-dealer', 'Setup & dữ liệu', 'Setup & dữ liệu', 'manage_options', 'vw-dealer', 'vwd_admin_page' );
	add_submenu_page( 'vw-dealer', 'Thông tin đại lý', 'Thông tin đại lý', 'manage_options', 'vw-dealer-settings', 'vwd_settings_page' );
} );

/** Xử lý các action POST. */
add_action( 'admin_init', function () {
	if ( empty( $_POST['vwd_action'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	check_admin_referer( 'vwd_admin' );
	$action = sanitize_key( $_POST['vwd_action'] );
	$msg    = '';

	switch ( $action ) {
		case 'reset':
			$r   = vwd_reset_content_step();
			$msg = sprintf( 'Đã xóa %d mục. Còn lại: %d.', $r['deleted'], $r['remaining'] );
			if ( $r['remaining'] > 0 ) {
				$msg .= ' Bấm "Xóa dữ liệu cũ" lần nữa để tiếp tục.';
			} else {
				$msg .= ' Hoàn tất xóa dữ liệu cũ.';
			}
			break;
		case 'import':
			$r   = vwd_import_all();
			$msg = sprintf( 'Đã nhập %d dòng xe, %d bài viết, %d trang và menu. Hàng đợi ảnh: %d ảnh – bấm "Tải ảnh" để tải về Media.', $r['cars'], $r['news'], $r['pages'], $r['images'] );
			break;
		case 'images':
			$r   = vwd_image_queue_step();
			$msg = sprintf( 'Đã tải %d ảnh, còn %d ảnh.', $r['done'], $r['remaining'] );
			if ( $r['errors'] ) {
				$msg .= ' Lỗi: ' . implode( '; ', array_slice( $r['errors'], 0, 5 ) );
			}
			if ( $r['remaining'] > 0 ) {
				$msg .= ' Bấm "Tải ảnh" lần nữa (hoặc bật tự động).';
			}
			break;
		case 'pull':
			update_option( 'vwd_github_repo', sanitize_text_field( wp_unslash( $_POST['repo'] ?? VWD_GITHUB_REPO ) ) );
			update_option( 'vwd_github_branch', sanitize_text_field( wp_unslash( $_POST['branch'] ?? VWD_GITHUB_BRANCH ) ) );
			$r = vwd_github_pull();
			if ( is_wp_error( $r ) ) {
				$msg = 'Lỗi kéo code: ' . $r->get_error_message();
			} else {
				$msg = sprintf( 'Đã cập nhật từ GitHub – theme: %s, plugin: %s.', $r['theme'] ? 'OK' : 'không có', $r['plugin'] ? 'OK' : 'không có' );
			}
			break;
		case 'settings':
			$in   = isset( $_POST['vwd'] ) ? (array) wp_unslash( $_POST['vwd'] ) : array();
			$opts = array();
			foreach ( vwd_defaults() as $k => $v ) {
				$opts[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
			}
			update_option( 'vwd_settings', $opts );
			if ( ! empty( $opts['dealer_name'] ) ) {
				update_option( 'blogname', $opts['dealer_name'] );
			}
			$msg = 'Đã lưu thông tin đại lý.';
			break;
	}
	set_transient( 'vwd_admin_msg', $msg, 60 );
	$page = 'settings' === $action ? 'vw-dealer-settings' : 'vw-dealer';
	wp_safe_redirect( admin_url( 'admin.php?page=' . $page . ( 'images' === $action && ! empty( $_POST['auto'] ) && $r['remaining'] > 0 ? '&auto=1' : '' ) ) );
	exit;
} );

function vwd_admin_notice() {
	$m = get_transient( 'vwd_admin_msg' );
	if ( $m ) {
		delete_transient( 'vwd_admin_msg' );
		echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( $m ) . '</p></div>';
	}
}

function vwd_admin_page() {
	global $wpdb;
	$types = vwd_reset_post_types();
	$in    = "'" . implode( "','", array_map( 'esc_sql', $types ) ) . "'";
	$old   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ({$in})" );
	$cars  = wp_count_posts( 'vw_car' )->publish ?? 0;
	$queue = count( get_option( 'vwd_image_queue', array() ) );
	$auto  = ! empty( $_GET['auto'] ) && $queue > 0;
	?>
	<div class="wrap">
		<h1>VW Dealer – Setup & dữ liệu</h1>
		<?php vwd_admin_notice(); ?>
		<style>.vwd-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:16px}.vwd-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px}.vwd-card h2{margin-top:0}.vwd-card .num{font-size:28px;font-weight:700;color:#1d4e9b}</style>
		<div class="vwd-cards">
			<div class="vwd-card">
				<h2>1. Xóa dữ liệu cũ</h2>
				<p>Xóa vĩnh viễn bài viết, trang, media, menu, bình luận và các post type cũ (<?php echo esc_html( implode( ', ', $types ) ); ?>). <strong>Giữ nguyên LearnPress, người dùng, plugin.</strong></p>
				<p class="num"><?php echo esc_html( $old ); ?> mục còn lại</p>
				<form method="post" onsubmit="return confirm('Xóa vĩnh viễn toàn bộ nội dung cũ? Không thể khôi phục!');">
					<?php wp_nonce_field( 'vwd_admin' ); ?>
					<input type="hidden" name="vwd_action" value="reset">
					<button class="button button-secondary" <?php disabled( 0 === $old ); ?>>Xóa dữ liệu cũ</button>
				</form>
			</div>
			<div class="vwd-card">
				<h2>2. Nhập dữ liệu demo</h2>
				<p>Tạo 11 dòng xe Volkswagen, bài viết/khuyến mãi, các trang (Trang chủ, Giới thiệu, Dịch vụ, Bảng giá, Trả góp, Lái thử, Tin tức, Liên hệ…), menu và kích hoạt theme <code>vw-dealer</code>.</p>
				<p class="num"><?php echo esc_html( $cars ); ?> dòng xe hiện có</p>
				<form method="post">
					<?php wp_nonce_field( 'vwd_admin' ); ?>
					<input type="hidden" name="vwd_action" value="import">
					<button class="button button-primary">Nhập dữ liệu demo</button>
				</form>
			</div>
			<div class="vwd-card">
				<h2>3. Tải ảnh về Media</h2>
				<p>Tải ảnh xe/tin tức từ nguồn (<?php echo esc_html( vwd_source_base() ); ?>) vào thư viện Media và gán cho từng dòng xe. Mỗi lần chạy ~25 giây; bật "tự động" để chạy liên tục.</p>
				<p class="num"><?php echo esc_html( $queue ); ?> ảnh trong hàng đợi</p>
				<form method="post" id="vwd-images-form">
					<?php wp_nonce_field( 'vwd_admin' ); ?>
					<input type="hidden" name="vwd_action" value="images">
					<label><input type="checkbox" name="auto" value="1" <?php checked( $auto ); ?>> Tự động chạy đến khi xong</label><br><br>
					<button class="button button-primary" <?php disabled( 0 === $queue ); ?>>Tải ảnh</button>
				</form>
				<?php if ( $auto ) : ?>
					<script>setTimeout(function(){document.getElementById('vwd-images-form').submit();},800);</script>
					<p><em>Đang tự động tải… trang sẽ tự tải lại.</em></p>
				<?php endif; ?>
			</div>
			<div class="vwd-card">
				<h2>4. Cập nhật từ GitHub</h2>
				<p>Tải zip nhánh mới nhất từ repo public và ghi đè theme <code>vw-dealer</code> + plugin <code>vw-dealer-core</code>. Lần cập nhật gần nhất: <?php echo esc_html( get_option( 'vwd_last_pull', '—' ) ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'vwd_admin' ); ?>
					<input type="hidden" name="vwd_action" value="pull">
					<p><label>Repo (owner/repo)<br><input type="text" name="repo" class="regular-text" value="<?php echo esc_attr( get_option( 'vwd_github_repo', VWD_GITHUB_REPO ) ); ?>"></label></p>
					<p><label>Nhánh<br><input type="text" name="branch" class="regular-text" value="<?php echo esc_attr( get_option( 'vwd_github_branch', VWD_GITHUB_BRANCH ) ); ?>"></label></p>
					<button class="button button-secondary">Kéo code mới nhất</button>
				</form>
			</div>
		</div>
		<p style="margin-top:20px"><em>Theme hiện tại: <?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?> · Plugin v<?php echo esc_html( VWD_VERSION ); ?></em></p>
	</div>
	<?php
}

function vwd_settings_page() {
	$labels = array(
		'dealer_name' => 'Tên đại lý', 'tagline' => 'Slogan', 'hotline' => 'Hotline', 'zalo' => 'Link Zalo', 'email' => 'Email hiển thị',
		'address' => 'Địa chỉ showroom', 'hours' => 'Giờ mở cửa', 'map_url' => 'Link Google Maps', 'lead_email' => 'Email nhận đăng ký',
		'facebook' => 'Facebook', 'youtube' => 'YouTube',
	);
	?>
	<div class="wrap">
		<h1>Thông tin đại lý</h1>
		<?php vwd_admin_notice(); ?>
		<form method="post">
			<?php wp_nonce_field( 'vwd_admin' ); ?>
			<input type="hidden" name="vwd_action" value="settings">
			<table class="form-table">
				<?php foreach ( $labels as $k => $label ) : ?>
					<tr><th><label for="vwd_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><input type="text" class="regular-text" id="vwd_<?php echo esc_attr( $k ); ?>" name="vwd[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( vwd_get( $k ) ); ?>"></td></tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button( 'Lưu thông tin' ); ?>
		</form>
	</div>
	<?php
}
