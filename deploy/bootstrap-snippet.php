<?php
/**
 * VW Dealer – Bootstrap (dán vào plugin Code Snippets, chạy "Only run once" hoặc chạy trong Admin).
 *
 * Tác dụng: tải zip nhánh main của repo GitHub public, cài theme wp-content/themes/vw-dealer
 * và plugin wp-content/plugins/vw-dealer-core, rồi kích hoạt plugin.
 * Sau khi chạy xong, vào menu "VW Dealer" trong Admin để xóa dữ liệu cũ / nhập demo / tải ảnh.
 * Các lần cập nhật sau dùng nút "Kéo code mới nhất" trong VW Dealer → Setup (không cần snippet này).
 */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || empty( $_GET['vwd_bootstrap'] ) ) {
		return;
	}
	$repo   = 'xuantuan1983/vw-dealer-website'; // owner/repo
	$branch = 'main';

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	WP_Filesystem();
	global $wp_filesystem;
	set_time_limit( 300 );

	$url = "https://codeload.github.com/{$repo}/zip/refs/heads/{$branch}";
	$tmp = download_url( $url, 120 );
	if ( is_wp_error( $tmp ) ) {
		wp_die( 'Không tải được zip: ' . esc_html( $tmp->get_error_message() ) );
	}
	$work = trailingslashit( get_temp_dir() ) . 'vwd-boot-' . wp_generate_password( 6, false );
	wp_mkdir_p( $work );
	$r = unzip_file( $tmp, $work );
	@unlink( $tmp );
	if ( is_wp_error( $r ) ) {
		wp_die( 'Không giải nén được: ' . esc_html( $r->get_error_message() ) );
	}
	$dirs = glob( $work . '/*', GLOB_ONLYDIR );
	$root = $dirs ? $dirs[0] : '';
	$log  = array();
	foreach ( array(
		'theme'  => array( $root . '/wp-content/themes/vw-dealer', get_theme_root() . '/vw-dealer' ),
		'plugin' => array( $root . '/wp-content/plugins/vw-dealer-core', WP_PLUGIN_DIR . '/vw-dealer-core' ),
	) as $k => $p ) {
		if ( is_dir( $p[0] ) ) {
			if ( is_dir( $p[1] ) ) {
				$wp_filesystem->delete( $p[1], true );
			}
			wp_mkdir_p( $p[1] );
			$c     = copy_dir( $p[0], $p[1] );
			$log[] = $k . ': ' . ( is_wp_error( $c ) ? $c->get_error_message() : 'OK' );
		} else {
			$log[] = $k . ': không có trong zip';
		}
	}
	$wp_filesystem->delete( $work, true );

	$act = activate_plugin( 'vw-dealer-core/vw-dealer-core.php' );
	$log[] = 'activate plugin: ' . ( is_wp_error( $act ) ? $act->get_error_message() : 'OK' );

	wp_die( '<h1>VW Dealer bootstrap</h1><pre>' . esc_html( implode( "\n", $log ) ) . '</pre><p><a href="' . esc_url( admin_url( 'admin.php?page=vw-dealer' ) ) . '">→ Mở VW Dealer Setup</a></p>', 'VW Dealer', array( 'response' => 200 ) );
} );
