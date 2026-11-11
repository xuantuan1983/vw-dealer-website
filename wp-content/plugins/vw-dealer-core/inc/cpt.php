<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vwd_register_post_types() {
	register_post_type( 'vw_car', array(
		'labels'       => array(
			'name'          => 'Dòng xe',
			'singular_name' => 'Dòng xe',
			'add_new'       => 'Thêm dòng xe',
			'add_new_item'  => 'Thêm dòng xe mới',
			'edit_item'     => 'Sửa dòng xe',
			'all_items'     => 'Tất cả dòng xe',
			'menu_name'     => 'Dòng xe VW',
		),
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'dong-xe', 'with_front' => false ),
		'menu_icon'    => 'dashicons-car',
		'menu_position' => 5,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest' => true,
	) );

	register_taxonomy( 'vw_car_type', 'vw_car', array(
		'labels'       => array( 'name' => 'Kiểu xe', 'singular_name' => 'Kiểu xe' ),
		'hierarchical' => true,
		'public'       => true,
		'rewrite'      => array( 'slug' => 'kieu-xe' ),
		'show_in_rest' => true,
	) );

	register_post_type( 'vw_lead', array(
		'labels'       => array(
			'name'          => 'Khách đăng ký',
			'singular_name' => 'Khách đăng ký',
			'menu_name'     => 'Khách đăng ký',
			'all_items'     => 'Tất cả đăng ký',
		),
		'public'       => false,
		'show_ui'      => true,
		'menu_icon'    => 'dashicons-id-alt',
		'menu_position' => 6,
		'supports'     => array( 'title' ),
		'capabilities' => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'vwd_register_post_types' );

/** Cột tuỳ chỉnh cho danh sách Dòng xe. */
add_filter( 'manage_vw_car_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['vwd_price'] = 'Giá từ';
			$new['vwd_type']  = 'Kiểu xe';
		}
	}
	return $new;
} );
add_action( 'manage_vw_car_posts_custom_column', function ( $col, $post_id ) {
	if ( 'vwd_price' === $col ) {
		echo esc_html( vwd_price( vwd_car_meta( $post_id, 'price_from', 0 ) ) );
	}
	if ( 'vwd_type' === $col ) {
		echo esc_html( vwd_car_meta( $post_id, 'type' ) );
	}
}, 10, 2 );

/** Cột cho Khách đăng ký. */
add_filter( 'manage_vw_lead_posts_columns', function ( $cols ) {
	return array(
		'cb'        => $cols['cb'],
		'title'     => 'Họ tên',
		'vwd_phone' => 'Điện thoại',
		'vwd_car'   => 'Quan tâm',
		'vwd_kind'  => 'Loại',
		'date'      => 'Ngày',
	);
} );
add_action( 'manage_vw_lead_posts_custom_column', function ( $col, $post_id ) {
	$map = array( 'vwd_phone' => '_vwd_phone', 'vwd_car' => '_vwd_car', 'vwd_kind' => '_vwd_kind' );
	if ( isset( $map[ $col ] ) ) {
		echo esc_html( get_post_meta( $post_id, $map[ $col ], true ) );
	}
}, 10, 2 );
