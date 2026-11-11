<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ---------- Tiện ích chung ----------
 */
function vwd_data_file( $name ) {
	$f = VWD_DIR . 'data/' . $name;
	if ( ! file_exists( $f ) ) {
		return array();
	}
	$d = json_decode( file_get_contents( $f ), true );
	return is_array( $d ) ? $d : array();
}

function vwd_source_base() {
	$site = vwd_data_file( 'site.json' );
	return ! empty( $site['source_base'] ) ? rtrim( $site['source_base'], '/' ) : '';
}

/** Ngân sách thời gian cho mỗi request (giây). */
function vwd_time_budget() {
	return (int) apply_filters( 'vwd_time_budget', 25 );
}

/**
 * ---------- 1. XÓA DỮ LIỆU CŨ ----------
 * Xóa vĩnh viễn post của các post type trong danh sách (giữ LearnPress & user).
 * Trả về số mục còn lại (0 = xong).
 */
function vwd_reset_post_types() {
	$types = array( 'post', 'page', 'attachment', 'xe_listing', 'xe_model', 'dealer', 'elementor_library', 'e-floating-buttons', 'product', 'wp_block', 'wp_navigation', 'wpum_directory', 'nav_menu_item', 'vw_car', 'vw_lead' );
	return apply_filters( 'vwd_reset_post_types', $types );
}

function vwd_reset_content_step() {
	global $wpdb;
	$start = microtime( true );
	$types = vwd_reset_post_types();
	$in    = "'" . implode( "','", array_map( 'esc_sql', $types ) ) . "'";

	$deleted = 0;
	while ( microtime( true ) - $start < vwd_time_budget() ) {
		$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ({$in}) ORDER BY ID DESC LIMIT 40" );
		if ( empty( $ids ) ) {
			break;
		}
		foreach ( $ids as $id ) {
			$type = get_post_type( $id );
			if ( 'attachment' === $type ) {
				wp_delete_attachment( $id, true );
			} else {
				wp_delete_post( $id, true );
			}
			$deleted++;
		}
	}

	$remaining = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ({$in})" );

	if ( 0 === $remaining ) {
		// Bình luận, menu, term của blog.
		$wpdb->query( "DELETE FROM {$wpdb->comments}" );
		$wpdb->query( "DELETE FROM {$wpdb->commentmeta}" );
		foreach ( wp_get_nav_menus() as $menu ) {
			wp_delete_nav_menu( $menu->term_id );
		}
		foreach ( array( 'category', 'post_tag' ) as $tax ) {
			$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
			foreach ( (array) $terms as $t ) {
				if ( 'category' === $tax && (int) get_option( 'default_category' ) === (int) $t->term_id ) {
					continue;
				}
				wp_delete_term( $t->term_id, $tax );
			}
		}
		delete_option( 'page_on_front' );
		delete_option( 'page_for_posts' );
		update_option( 'show_on_front', 'posts' );
		update_option( 'vwd_reset_done', current_time( 'mysql' ) );
	}

	return array( 'deleted' => $deleted, 'remaining' => $remaining );
}

/**
 * ---------- 2. TẢI ẢNH ----------
 * Tải ảnh từ URL vào Media, nhớ nguồn để không tải trùng.
 */
function vwd_sideload( $url, $post_id = 0, $title = '' ) {
	$url = trim( $url );
	if ( '' === $url ) {
		return 0;
	}
	if ( 0 !== strpos( $url, 'http' ) ) {
		$url = vwd_source_base() . '/' . ltrim( $url, '/' );
	}
	global $wpdb;
	$exist = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_vwd_source' AND meta_value=%s LIMIT 1", $url ) );
	if ( $exist && get_post( $exist ) ) {
		return (int) $exist;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}
	$name = basename( parse_url( $url, PHP_URL_PATH ) );
	if ( $title ) {
		$ext  = pathinfo( $name, PATHINFO_EXTENSION );
		$name = sanitize_title( $title ) . '-' . substr( md5( $url ), 0, 6 ) . '.' . $ext;
	}
	$file = array( 'name' => $name, 'tmp_name' => $tmp );
	$id   = media_handle_sideload( $file, $post_id, $title );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		return $id;
	}
	update_post_meta( $id, '_vwd_source', $url );
	return (int) $id;
}

/**
 * Hàng đợi ảnh: mỗi phần tử = [post_id, url, role(featured|gallery), title]
 */
function vwd_image_queue_add( $post_id, $url, $role, $title = '' ) {
	$q   = get_option( 'vwd_image_queue', array() );
	$q[] = array( (int) $post_id, $url, $role, $title );
	update_option( 'vwd_image_queue', $q, false );
}

function vwd_image_queue_step() {
	$q     = get_option( 'vwd_image_queue', array() );
	$start = microtime( true );
	$done  = 0;
	$err   = array();
	while ( ! empty( $q ) && microtime( true ) - $start < vwd_time_budget() ) {
		list( $post_id, $url, $role, $title ) = array_shift( $q );
		$id = vwd_sideload( $url, $post_id, $title );
		if ( is_wp_error( $id ) ) {
			$err[] = $url . ' – ' . $id->get_error_message();
		} elseif ( $id ) {
			if ( 'featured' === $role ) {
				set_post_thumbnail( $post_id, $id );
			} else {
				$g = vwd_car_meta( $post_id, 'gallery', array() );
				$g = array_values( array_filter( (array) $g, function ( $v ) use ( $url ) {
					return $v !== $url; // thay URL bằng ID
				} ) );
				if ( ! in_array( $id, $g, true ) ) {
					$g[] = $id;
				}
				update_post_meta( $post_id, '_vwd_gallery', wp_json_encode( $g ) );
			}
			$done++;
		}
		update_option( 'vwd_image_queue', $q, false );
	}
	return array( 'done' => $done, 'remaining' => count( $q ), 'errors' => $err );
}

/**
 * ---------- 3. NHẬP DỮ LIỆU DEMO ----------
 */
function vwd_find_by_slug( $slug, $type ) {
	$p = get_page_by_path( $slug, OBJECT, $type );
	return $p ? $p->ID : 0;
}

function vwd_import_models() {
	$models = vwd_data_file( 'models.json' );
	$base   = vwd_source_base();
	$n      = 0;
	foreach ( $models as $i => $m ) {
		$id = vwd_find_by_slug( $m['slug'], 'vw_car' );
		$data = array(
			'post_type'    => 'vw_car',
			'post_status'  => 'publish',
			'post_title'   => $m['name'],
			'post_name'    => $m['slug'],
			'post_excerpt' => $m['summary'],
			'post_content' => '',
			'menu_order'   => $i,
		);
		if ( $id ) {
			$data['ID'] = $id;
			wp_update_post( $data );
		} else {
			$id = wp_insert_post( $data );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		foreach ( array( 'short_name', 'type', 'badge', 'seats', 'engine', 'power', 'drive', 'price_from', 'price_promo', 'promo_note', 'summary' ) as $k ) {
			update_post_meta( $id, '_vwd_' . $k, isset( $m[ $k ] ) ? $m[ $k ] : '' );
		}
		foreach ( array( 'highlights', 'variants', 'specs', 'faq' ) as $k ) {
			update_post_meta( $id, '_vwd_' . $k, wp_json_encode( isset( $m[ $k ] ) ? $m[ $k ] : array(), JSON_UNESCAPED_UNICODE ) );
		}
		update_post_meta( $id, '_vwd_hero_url', $base . $m['hero'] );
		// Gallery tạm là URL tuyệt đối, sẽ được thay bằng ID khi tải ảnh.
		$gallery = array();
		foreach ( (array) $m['gallery'] as $g ) {
			$gallery[] = $base . $g;
		}
		update_post_meta( $id, '_vwd_gallery', wp_json_encode( $gallery ) );
		wp_set_object_terms( $id, $m['type'], 'vw_car_type' );

		if ( ! has_post_thumbnail( $id ) ) {
			vwd_image_queue_add( $id, $base . $m['hero'], 'featured', $m['name'] );
		}
		foreach ( $gallery as $g ) {
			vwd_image_queue_add( $id, $g, 'gallery', $m['name'] );
		}
		$n++;
	}
	return $n;
}

function vwd_import_news() {
	$items = vwd_data_file( 'news.json' );
	$base  = vwd_source_base();
	$n     = 0;
	foreach ( $items as $i => $it ) {
		$cat = get_term_by( 'name', $it['category'], 'category' );
		if ( ! $cat ) {
			$r   = wp_insert_term( $it['category'], 'category' );
			$cat = is_wp_error( $r ) ? null : get_term( $r['term_id'], 'category' );
		}
		$id   = vwd_find_by_slug( $it['slug'], 'post' );
		$data = array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $it['title'],
			'post_name'     => $it['slug'],
			'post_excerpt'  => $it['excerpt'],
			'post_content'  => $it['content'],
			'post_date'     => date( 'Y-m-d H:i:s', strtotime( "-{$i} days" ) ),
			'post_category' => $cat ? array( $cat->term_id ) : array(),
		);
		if ( $id ) {
			$data['ID'] = $id;
			wp_update_post( $data );
		} else {
			$id = wp_insert_post( $data );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			if ( ! has_post_thumbnail( $id ) ) {
				vwd_image_queue_add( $id, $base . $it['image'], 'featured', $it['title'] );
			}
			$n++;
		}
	}
	return $n;
}

function vwd_pages_spec() {
	$name = vwd_get( 'dealer_name' );
	return array(
		'trang-chu'        => array( 'title' => 'Trang chủ', 'template' => '', 'content' => '' ),
		'gioi-thieu'       => array( 'title' => 'Giới thiệu', 'template' => 'page-gioi-thieu.php', 'content' => "<p><strong>{$name}</strong> là đại lý ủy quyền chính hãng Volkswagen tại TP. Hồ Chí Minh, cung cấp đầy đủ dịch vụ 3S: bán hàng – dịch vụ – phụ tùng chính hãng.</p><p>Với đội ngũ tư vấn giàu kinh nghiệm và xưởng dịch vụ đạt chuẩn Volkswagen toàn cầu, chúng tôi cam kết mang đến trải nghiệm sở hữu xe Đức trọn vẹn: giá minh bạch, giao xe đúng hẹn, bảo hành 3 năm hoặc 100.000 km và chăm sóc khách hàng trọn đời.</p><h2>Giá trị cốt lõi</h2><ul><li><strong>Uy tín</strong> – Báo giá rõ ràng, không phát sinh chi phí ẩn.</li><li><strong>Chuyên nghiệp</strong> – Kỹ thuật viên được Volkswagen đào tạo và chứng nhận.</li><li><strong>Đồng hành</strong> – Hỗ trợ cứu hộ 24/7, nhắc lịch bảo dưỡng định kỳ.</li></ul>" ),
		'dich-vu'          => array( 'title' => 'Dịch vụ', 'template' => 'page-dich-vu.php', 'content' => '' ),
		'cac-dong-xe'      => array( 'title' => 'Các dòng xe', 'template' => 'page-cac-dong-xe.php', 'content' => '' ),
		'bang-gia-xe'      => array( 'title' => 'Bảng giá xe Volkswagen', 'template' => 'page-bang-gia-xe.php', 'content' => '' ),
		'mua-xe-tra-gop'   => array( 'title' => 'Mua xe trả góp', 'template' => 'page-tra-gop.php', 'content' => '' ),
		'dang-ky-lai-thu'  => array( 'title' => 'Đăng ký lái thử', 'template' => 'page-lai-thu.php', 'content' => '' ),
		'tin-tuc'          => array( 'title' => 'Tin tức', 'template' => '', 'content' => '' ),
		'khuyen-mai'       => array( 'title' => 'Khuyến mãi', 'template' => 'page-khuyen-mai.php', 'content' => '' ),
		'chinh-sach'       => array( 'title' => 'Chính sách', 'template' => '', 'content' => "<h2>Chính sách bảo hành</h2><p>Xe mới được bảo hành 3 năm hoặc 100.000 km (tùy điều kiện nào đến trước) tại toàn bộ hệ thống đại lý Volkswagen Việt Nam.</p><h2>Chính sách bảo mật</h2><p>Thông tin khách hàng gửi qua website chỉ được dùng để tư vấn bán hàng và chăm sóc khách hàng, không chia sẻ cho bên thứ ba.</p><h2>Miễn trừ trách nhiệm</h2><p>Giá bán và chương trình ưu đãi mang tính tham khảo, có thể thay đổi theo chính sách của Volkswagen Việt Nam mà không báo trước. Đây là website demo.</p>" ),
		'lien-he'          => array( 'title' => 'Liên hệ', 'template' => 'page-lien-he.php', 'content' => '' ),
	);
}

function vwd_import_pages() {
	$ids = array();
	foreach ( vwd_pages_spec() as $slug => $p ) {
		$id   = vwd_find_by_slug( $slug, 'page' );
		$data = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $slug,
			'post_content' => $p['content'],
		);
		if ( $id ) {
			$data['ID'] = $id;
			wp_update_post( $data );
		} else {
			$id = wp_insert_post( $data );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			if ( $p['template'] ) {
				update_post_meta( $id, '_wp_page_template', $p['template'] );
			} else {
				delete_post_meta( $id, '_wp_page_template' );
			}
			$ids[ $slug ] = $id;
		}
	}
	if ( ! empty( $ids['trang-chu'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['trang-chu'] );
	}
	if ( ! empty( $ids['tin-tuc'] ) ) {
		update_option( 'page_for_posts', $ids['tin-tuc'] );
	}
	return $ids;
}

function vwd_import_menus( $page_ids ) {
	$menu_name = 'Menu chính';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}
	$menu_id = wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}
	$add_page = function ( $slug, $parent = 0, $title = '' ) use ( $menu_id, $page_ids ) {
		if ( empty( $page_ids[ $slug ] ) ) {
			return 0;
		}
		return wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $title ? $title : get_the_title( $page_ids[ $slug ] ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_ids[ $slug ],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent,
		) );
	};
	$add_page( 'trang-chu' );
	$add_page( 'gioi-thieu' );
	$cars_item = $add_page( 'cac-dong-xe' );
	foreach ( vwd_get_cars() as $car ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'     => $car->post_title,
			'menu-item-object'    => 'vw_car',
			'menu-item-object-id' => $car->ID,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $cars_item,
		) );
	}
	$add_page( 'dich-vu' );
	$add_page( 'bang-gia-xe', 0, 'Bảng giá xe' );
	$add_page( 'mua-xe-tra-gop' );
	$add_page( 'dang-ky-lai-thu' );
	$add_page( 'tin-tuc' );

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;

	// Footer menu
	$fm = wp_get_nav_menu_object( 'Menu footer' );
	if ( $fm ) {
		wp_delete_nav_menu( $fm->term_id );
	}
	$fid = wp_create_nav_menu( 'Menu footer' );
	if ( ! is_wp_error( $fid ) ) {
		foreach ( array( 'gioi-thieu', 'tin-tuc', 'khuyen-mai', 'dang-ky-lai-thu', 'bang-gia-xe', 'chinh-sach', 'lien-he' ) as $s ) {
			if ( ! empty( $page_ids[ $s ] ) ) {
				wp_update_nav_menu_item( $fid, 0, array(
					'menu-item-title'     => get_the_title( $page_ids[ $s ] ),
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_ids[ $s ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
		}
		$locations['footer'] = $fid;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/** Chạy toàn bộ import (trừ ảnh – ảnh dùng hàng đợi). */
function vwd_import_all() {
	set_time_limit( 300 );
	delete_option( 'vwd_image_queue' );

	$site = vwd_data_file( 'site.json' );
	if ( ! empty( $site['dealer_name'] ) ) {
		update_option( 'blogname', $site['dealer_name'] );
		update_option( 'blogdescription', ! empty( $site['tagline'] ) ? $site['tagline'] : '' );
	}
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'timezone_string', 'Asia/Ho_Chi_Minh' );
	update_option( 'date_format', 'd/m/Y' );

	// Kích hoạt theme nếu có.
	if ( wp_get_theme( 'vw-dealer' )->exists() && 'vw-dealer' !== get_stylesheet() ) {
		switch_theme( 'vw-dealer' );
	}

	$cars  = vwd_import_models();
	$news  = vwd_import_news();
	$pages = vwd_import_pages();
	vwd_import_menus( $pages );
	flush_rewrite_rules();

	update_option( 'vwd_import_done', current_time( 'mysql' ) );
	return array( 'cars' => $cars, 'news' => $news, 'pages' => count( $pages ), 'images' => count( get_option( 'vwd_image_queue', array() ) ) );
}
