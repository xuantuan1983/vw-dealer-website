<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thông tin đại lý (lưu trong option vwd_settings, có giá trị mặc định từ data/site.json).
 */
function vwd_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = array(
			'dealer_name' => 'Volkswagen Sài Gòn',
			'tagline'     => 'Đại lý ủy quyền chính hãng Volkswagen',
			'hotline'     => '0907 658 787',
			'zalo'        => 'https://zalo.me/0907658787',
			'email'       => '',
			'address'     => 'TP. Hồ Chí Minh',
			'hours'       => '8:00 – 18:00, Thứ 2 – Chủ nhật',
			'map_url'     => '',
			'lead_email'  => get_option( 'admin_email' ),
			'facebook'    => '',
			'youtube'     => '',
		);
		$json = VWD_DIR . 'data/site.json';
		if ( file_exists( $json ) ) {
			$data = json_decode( file_get_contents( $json ), true );
			if ( is_array( $data ) ) {
				foreach ( $defaults as $k => $v ) {
					if ( ! empty( $data[ $k ] ) ) {
						$defaults[ $k ] = $data[ $k ];
					}
				}
			}
		}
	}
	return $defaults;
}

function vwd_get( $key, $default = '' ) {
	$opts = get_option( 'vwd_settings', array() );
	if ( isset( $opts[ $key ] ) && '' !== $opts[ $key ] ) {
		return $opts[ $key ];
	}
	$d = vwd_defaults();
	return isset( $d[ $key ] ) ? $d[ $key ] : $default;
}

function vwd_hotline_raw() {
	return preg_replace( '/[^0-9+]/', '', vwd_get( 'hotline' ) );
}

/** Định dạng giá: 1.299.000.000 ₫ */
function vwd_price( $n, $suffix = ' ₫' ) {
	$n = (float) $n;
	if ( $n <= 0 ) {
		return 'Liên hệ';
	}
	return number_format( $n, 0, ',', '.' ) . $suffix;
}

/** Giá rút gọn: 1,299 tỷ / 798 triệu */
function vwd_price_short( $n ) {
	$n = (float) $n;
	if ( $n <= 0 ) {
		return 'Liên hệ';
	}
	if ( $n >= 1e9 ) {
		return rtrim( rtrim( number_format( $n / 1e9, 3, ',', '.' ), '0' ), ',' ) . ' tỷ';
	}
	return number_format( $n / 1e6, 0, ',', '.' ) . ' triệu';
}

/** Lấy meta của xe, decode JSON nếu cần. */
function vwd_car_meta( $post_id, $key, $default = '' ) {
	$v = get_post_meta( $post_id, '_vwd_' . $key, true );
	if ( '' === $v || null === $v ) {
		return $default;
	}
	if ( in_array( $key, array( 'highlights', 'variants', 'specs', 'faq', 'gallery' ), true ) ) {
		if ( is_string( $v ) ) {
			$dec = json_decode( $v, true );
			return is_array( $dec ) ? $dec : $default;
		}
		return is_array( $v ) ? $v : $default;
	}
	return $v;
}

/** Danh sách xe (WP_Post[]), sắp theo menu_order rồi tên. */
function vwd_get_cars( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'post_type'      => 'vw_car',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'post_status'    => 'publish',
	) );
	return get_posts( $args );
}

/** URL ảnh đại diện xe (fallback: ảnh placeholder của theme). */
function vwd_car_image( $post_id, $size = 'large' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}
	$ext = vwd_car_meta( $post_id, 'hero_url' );
	if ( $ext ) {
		return $ext;
	}
	return get_template_directory_uri() . '/assets/img/placeholder-car.svg';
}

/** Gallery: mảng URL. */
function vwd_car_gallery( $post_id, $size = 'large' ) {
	$ids  = vwd_car_meta( $post_id, 'gallery', array() );
	$urls = array();
	foreach ( (array) $ids as $id ) {
		if ( is_numeric( $id ) ) {
			$u = wp_get_attachment_image_url( (int) $id, $size );
			if ( $u ) {
				$urls[] = $u;
			}
		} elseif ( is_string( $id ) && 0 === strpos( $id, 'http' ) ) {
			$urls[] = $id;
		}
	}
	return $urls;
}

/** Trang theo slug -> permalink (fallback home). */
function vwd_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
}
