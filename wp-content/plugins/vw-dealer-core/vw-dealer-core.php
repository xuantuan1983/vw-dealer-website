<?php
/**
 * Plugin Name: VW Dealer Core
 * Plugin URI:  https://github.com/xuantuan1983/vw-dealer-website
 * Description: Post type "Dòng xe", form đăng ký lái thử/báo giá, trang Setup (xóa dữ liệu cũ, nhập dữ liệu demo, cập nhật từ GitHub) cho website đại lý Volkswagen.
 * Version:     1.0.0
 * Author:      Cao Xuân Tuấn
 * Text Domain: vw-dealer
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VWD_VERSION', '1.0.0' );
define( 'VWD_FILE', __FILE__ );
define( 'VWD_DIR', plugin_dir_path( __FILE__ ) );
define( 'VWD_URL', plugin_dir_url( __FILE__ ) );
define( 'VWD_GITHUB_REPO', 'xuantuan1983/vw-dealer-website' ); // owner/repo – đổi nếu repo khác
define( 'VWD_GITHUB_BRANCH', 'main' );

require_once VWD_DIR . 'inc/helpers.php';
require_once VWD_DIR . 'inc/cpt.php';
require_once VWD_DIR . 'inc/metabox.php';
require_once VWD_DIR . 'inc/leads.php';
require_once VWD_DIR . 'inc/importer.php';
require_once VWD_DIR . 'inc/github.php';
require_once VWD_DIR . 'inc/admin.php';

register_activation_hook( __FILE__, function () {
	vwd_register_post_types();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
