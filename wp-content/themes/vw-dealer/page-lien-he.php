<?php
/**
 * Template Name: Liên hệ
 */
get_header();
the_post();
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Chúng tôi luôn sẵn sàng hỗ trợ bạn.</p></div></section>
<section class="section">
	<div class="container page-layout">
		<div class="page-main">
			<div class="contact-grid">
				<div class="contact-item"><?php echo vwt_icon( 'pin' ); ?><h3>Showroom</h3><p><?php echo esc_html( vwd_get( 'address' ) ); ?></p><?php if ( vwd_get( 'map_url' ) ) : ?><a href="<?php echo esc_url( vwd_get( 'map_url' ) ); ?>" target="_blank" rel="noopener">Xem bản đồ</a><?php endif; ?></div>
				<div class="contact-item"><?php echo vwt_icon( 'phone' ); ?><h3>Hotline</h3><p><a href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>"><?php echo esc_html( vwd_get( 'hotline' ) ); ?></a></p></div>
				<div class="contact-item"><?php echo vwt_icon( 'mail' ); ?><h3>Email</h3><p><a href="mailto:<?php echo esc_attr( vwd_get( 'email' ) ); ?>"><?php echo esc_html( vwd_get( 'email' ) ); ?></a></p></div>
				<div class="contact-item"><?php echo vwt_icon( 'clock' ); ?><h3>Giờ mở cửa</h3><p><?php echo esc_html( vwd_get( 'hours' ) ); ?></p></div>
			</div>
			<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php endif; ?>
			<?php if ( vwd_get( 'map_url' ) ) : ?><p><a class="btn btn--outline" href="<?php echo esc_url( vwd_get( 'map_url' ) ); ?>" target="_blank" rel="noopener">Chỉ đường Google Maps</a></p><?php endif; ?>
		</div>
		<aside class="page-side"><div class="card"><?php vwd_render_form( array( 'kind' => 'Liên hệ', 'title' => 'Gửi yêu cầu tư vấn', 'button' => 'Gửi yêu cầu' ) ); ?></div></aside>
	</div>
</section>
<?php get_footer();
