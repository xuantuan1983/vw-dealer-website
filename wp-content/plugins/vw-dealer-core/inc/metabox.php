<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox "Thông tin dòng xe" – các trường đơn giản + các trường JSON (textarea).
 */
function vwd_car_fields() {
	return array(
		'short_name'  => array( 'label' => 'Tên ngắn (Golf, Tiguan…)', 'type' => 'text' ),
		'type'        => array( 'label' => 'Kiểu xe (SUV/MPV/Hatchback)', 'type' => 'text' ),
		'badge'       => array( 'label' => 'Nhãn (Mới, Hot…)', 'type' => 'text' ),
		'seats'       => array( 'label' => 'Số chỗ', 'type' => 'number' ),
		'engine'      => array( 'label' => 'Động cơ', 'type' => 'text' ),
		'power'       => array( 'label' => 'Công suất', 'type' => 'text' ),
		'drive'       => array( 'label' => 'Dẫn động', 'type' => 'text' ),
		'price_from'  => array( 'label' => 'Giá niêm yết từ (VNĐ)', 'type' => 'number' ),
		'price_promo' => array( 'label' => 'Giá ưu đãi (VNĐ, 0 = không có)', 'type' => 'number' ),
		'promo_note'  => array( 'label' => 'Ghi chú ưu đãi', 'type' => 'text' ),
		'summary'     => array( 'label' => 'Mô tả ngắn', 'type' => 'textarea' ),
		'hero_url'    => array( 'label' => 'URL ảnh hero (nếu không dùng ảnh đại diện)', 'type' => 'text' ),
		'video'       => array( 'label' => 'YouTube embed URL', 'type' => 'text' ),
		'highlights'  => array( 'label' => 'Điểm nổi bật (JSON mảng chuỗi)', 'type' => 'json' ),
		'variants'    => array( 'label' => 'Phiên bản & giá (JSON [{"name","price"}])', 'type' => 'json' ),
		'specs'       => array( 'label' => 'Thông số (JSON [{"title","rows":[[...]]}])', 'type' => 'json' ),
		'faq'         => array( 'label' => 'FAQ (JSON [["Câu hỏi","Trả lời"]])', 'type' => 'json' ),
		'gallery'     => array( 'label' => 'Gallery (JSON mảng ID attachment hoặc URL)', 'type' => 'json' ),
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'vwd_car_box', 'Thông tin dòng xe', 'vwd_car_box_render', 'vw_car', 'normal', 'high' );
	add_meta_box( 'vwd_lead_box', 'Thông tin đăng ký', 'vwd_lead_box_render', 'vw_lead', 'normal', 'high' );
} );

function vwd_car_box_render( $post ) {
	wp_nonce_field( 'vwd_car_save', 'vwd_car_nonce' );
	echo '<style>.vwd-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 20px}.vwd-grid label{display:block;font-weight:600;margin:6px 0 3px}.vwd-grid input[type=text],.vwd-grid input[type=number],.vwd-grid textarea{width:100%}.vwd-grid .full{grid-column:1/-1}.vwd-grid textarea{font-family:monospace;font-size:12px}</style>';
	echo '<div class="vwd-grid">';
	foreach ( vwd_car_fields() as $key => $f ) {
		$val  = get_post_meta( $post->ID, '_vwd_' . $key, true );
		$full = in_array( $f['type'], array( 'textarea', 'json' ), true ) ? ' full' : '';
		echo '<div class="' . esc_attr( trim( $full ) ) . '"><label for="vwd_' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label>';
		if ( 'textarea' === $f['type'] ) {
			echo '<textarea id="vwd_' . esc_attr( $key ) . '" name="vwd[' . esc_attr( $key ) . ']" rows="3">' . esc_textarea( $val ) . '</textarea>';
		} elseif ( 'json' === $f['type'] ) {
			if ( is_array( $val ) ) {
				$val = wp_json_encode( $val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			}
			echo '<textarea id="vwd_' . esc_attr( $key ) . '" name="vwd[' . esc_attr( $key ) . ']" rows="6">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="' . esc_attr( $f['type'] ) . '" id="vwd_' . esc_attr( $key ) . '" name="vwd[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '">';
		}
		echo '</div>';
	}
	echo '</div>';
}

add_action( 'save_post_vw_car', function ( $post_id ) {
	if ( ! isset( $_POST['vwd_car_nonce'] ) || ! wp_verify_nonce( $_POST['vwd_car_nonce'], 'vwd_car_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = isset( $_POST['vwd'] ) && is_array( $_POST['vwd'] ) ? wp_unslash( $_POST['vwd'] ) : array();
	foreach ( vwd_car_fields() as $key => $f ) {
		$v = isset( $in[ $key ] ) ? $in[ $key ] : '';
		if ( 'json' === $f['type'] ) {
			$dec = json_decode( $v, true );
			$v   = is_array( $dec ) ? wp_json_encode( $dec, JSON_UNESCAPED_UNICODE ) : '';
		} elseif ( 'number' === $f['type'] ) {
			$v = is_numeric( $v ) ? $v + 0 : '';
		} elseif ( 'textarea' === $f['type'] ) {
			$v = sanitize_textarea_field( $v );
		} else {
			$v = sanitize_text_field( $v );
		}
		update_post_meta( $post_id, '_vwd_' . $key, $v );
	}
} );

function vwd_lead_box_render( $post ) {
	$fields = array( 'phone' => 'Điện thoại', 'email' => 'Email', 'car' => 'Dòng xe quan tâm', 'kind' => 'Loại đăng ký', 'message' => 'Ghi chú', 'page' => 'Trang gửi' );
	echo '<table class="form-table">';
	foreach ( $fields as $k => $label ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td>' . esc_html( get_post_meta( $post->ID, '_vwd_' . $k, true ) ) . '</td></tr>';
	}
	echo '</table>';
}
