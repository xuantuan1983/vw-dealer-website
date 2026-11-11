<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Xử lý form đăng ký (lái thử / báo giá / tư vấn / trả góp).
 * Form POST tới admin-post.php?action=vwd_lead với nonce.
 */
add_action( 'admin_post_nopriv_vwd_lead', 'vwd_handle_lead' );
add_action( 'admin_post_vwd_lead', 'vwd_handle_lead' );

function vwd_handle_lead() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if ( ! isset( $_POST['vwd_nonce'] ) || ! wp_verify_nonce( $_POST['vwd_nonce'], 'vwd_lead' ) ) {
		wp_safe_redirect( add_query_arg( 'vwd', 'error', $back ) );
		exit;
	}
	// Honeypot chống spam.
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'vwd', 'ok', $back ) );
		exit;
	}

	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$phone = preg_replace( '/[^0-9+ .]/', '', wp_unslash( $_POST['phone'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$car   = sanitize_text_field( wp_unslash( $_POST['car'] ?? '' ) );
	$kind  = sanitize_text_field( wp_unslash( $_POST['kind'] ?? 'Tư vấn' ) );
	$msg   = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( '' === $name || strlen( $phone ) < 9 ) {
		wp_safe_redirect( add_query_arg( 'vwd', 'invalid', $back ) );
		exit;
	}

	$id = wp_insert_post( array(
		'post_type'   => 'vw_lead',
		'post_status' => 'publish',
		'post_title'  => $name,
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_vwd_phone', $phone );
		update_post_meta( $id, '_vwd_email', $email );
		update_post_meta( $id, '_vwd_car', $car );
		update_post_meta( $id, '_vwd_kind', $kind );
		update_post_meta( $id, '_vwd_message', $msg );
		update_post_meta( $id, '_vwd_page', esc_url_raw( $back ) );

		$to = vwd_get( 'lead_email', get_option( 'admin_email' ) );
		if ( is_email( $to ) ) {
			$body = "Khách hàng: {$name}\nĐiện thoại: {$phone}\nEmail: {$email}\nDòng xe: {$car}\nLoại: {$kind}\nGhi chú: {$msg}\nTrang: {$back}";
			wp_mail( $to, "[{$kind}] {$name} – {$phone}", $body );
		}
	}

	wp_safe_redirect( add_query_arg( 'vwd', 'ok', $back ) );
	exit;
}

/**
 * Shortcode [vwd_form kind="Lái thử" title="Đăng ký lái thử"]
 */
add_shortcode( 'vwd_form', function ( $atts ) {
	$atts = shortcode_atts( array( 'kind' => 'Tư vấn', 'title' => '', 'car' => '', 'button' => 'Gửi đăng ký', 'compact' => '0' ), $atts );
	ob_start();
	vwd_render_form( $atts );
	return ob_get_clean();
} );

function vwd_render_form( $args = array() ) {
	$args = wp_parse_args( $args, array( 'kind' => 'Tư vấn', 'title' => '', 'car' => '', 'button' => 'Gửi đăng ký', 'compact' => '0' ) );
	$cars = vwd_get_cars();
	?>
	<form class="vwd-form <?php echo '1' === (string) $args['compact'] ? 'vwd-form--compact' : ''; ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php if ( $args['title'] ) : ?><h3 class="vwd-form__title"><?php echo esc_html( $args['title'] ); ?></h3><?php endif; ?>
		<input type="hidden" name="action" value="vwd_lead">
		<input type="hidden" name="kind" value="<?php echo esc_attr( $args['kind'] ); ?>">
		<?php wp_nonce_field( 'vwd_lead', 'vwd_nonce' ); ?>
		<input type="text" name="website" class="vwd-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
		<div class="vwd-form__row"><input type="text" name="name" placeholder="Họ và tên *" required></div>
		<div class="vwd-form__row"><input type="tel" name="phone" placeholder="Số điện thoại *" required pattern="[0-9+ .]{9,15}"></div>
		<?php if ( '1' !== (string) $args['compact'] ) : ?>
		<div class="vwd-form__row"><input type="email" name="email" placeholder="Email"></div>
		<?php endif; ?>
		<div class="vwd-form__row">
			<select name="car">
				<option value="">— Chọn dòng xe quan tâm —</option>
				<?php foreach ( $cars as $c ) : ?>
					<option value="<?php echo esc_attr( $c->post_title ); ?>" <?php selected( $args['car'], $c->post_title ); ?>><?php echo esc_html( $c->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php if ( '1' !== (string) $args['compact'] ) : ?>
		<div class="vwd-form__row"><textarea name="message" rows="3" placeholder="Ghi chú (thời gian lái thử, phiên bản, màu xe…)"></textarea></div>
		<?php endif; ?>
		<button type="submit" class="btn btn--primary btn--block"><?php echo esc_html( $args['button'] ); ?></button>
		<p class="vwd-form__note">Thông tin của bạn được bảo mật và chỉ dùng để tư vấn.</p>
	</form>
	<?php
}

/** Thông báo sau khi gửi form. */
add_action( 'wp_footer', function () {
	if ( empty( $_GET['vwd'] ) ) {
		return;
	}
	$map = array(
		'ok'      => array( 'ok', 'Cảm ơn bạn! Chuyên viên sẽ liên hệ trong thời gian sớm nhất.' ),
		'invalid' => array( 'err', 'Vui lòng nhập họ tên và số điện thoại hợp lệ.' ),
		'error'   => array( 'err', 'Có lỗi xảy ra, vui lòng thử lại.' ),
	);
	$k = sanitize_key( $_GET['vwd'] );
	if ( isset( $map[ $k ] ) ) {
		echo '<div class="vwd-toast vwd-toast--' . esc_attr( $map[ $k ][0] ) . '">' . esc_html( $map[ $k ][1] ) . '</div>';
	}
} );
