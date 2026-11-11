</main>

<section class="newsletter">
	<div class="container newsletter__inner">
		<div>
			<h3>Đăng ký nhận khuyến mãi</h3>
			<p>Nhận thông tin ưu đãi, bảng giá và sự kiện lái thử mới nhất từ <?php echo esc_html( vwd_get( 'dealer_name' ) ); ?>.</p>
		</div>
		<form class="newsletter__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="vwd_lead">
			<input type="hidden" name="kind" value="Nhận khuyến mãi">
			<input type="hidden" name="name" value="Đăng ký nhận tin">
			<?php wp_nonce_field( 'vwd_lead', 'vwd_nonce' ); ?>
			<input type="text" name="website" class="vwd-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
			<input type="tel" name="phone" placeholder="Số điện thoại của bạn" required pattern="[0-9+ .]{9,15}">
			<button class="btn btn--primary" type="submit">Gửi thông tin</button>
		</form>
	</div>
</section>

<footer class="site-footer">
	<div class="container footer__grid">
		<div class="footer__col footer__brand">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-mark.svg' ); ?>" alt="Volkswagen" class="footer__logo">
			<h4><?php echo esc_html( vwd_get( 'dealer_name' ) ); ?></h4>
			<p><?php echo esc_html( vwd_get( 'tagline' ) ); ?>. Showroom 3S: bán hàng – dịch vụ – phụ tùng chính hãng, bảo hành toàn quốc.</p>
		</div>
		<div class="footer__col">
			<h4>Thông tin liên hệ</h4>
			<ul class="footer__contact">
				<li><?php echo vwt_icon( 'pin' ); ?><span><strong>Showroom:</strong> <?php echo esc_html( vwd_get( 'address' ) ); ?></span></li>
				<li><?php echo vwt_icon( 'phone' ); ?><span><strong>Hotline:</strong> <a href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>"><?php echo esc_html( vwd_get( 'hotline' ) ); ?></a> (Phòng kinh doanh)</span></li>
				<?php if ( vwd_get( 'email' ) ) : ?><li><?php echo vwt_icon( 'mail' ); ?><span><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( vwd_get( 'email' ) ); ?>"><?php echo esc_html( vwd_get( 'email' ) ); ?></a></span></li><?php endif; ?>
				<li><?php echo vwt_icon( 'clock' ); ?><span><strong>Giờ mở cửa:</strong> <?php echo esc_html( vwd_get( 'hours' ) ); ?></span></li>
			</ul>
		</div>
		<div class="footer__col">
			<h4>Liên kết</h4>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer__links', 'depth' => 1 ) );
			} else {
				echo '<ul class="footer__links"><li><a href="' . esc_url( vwd_page_url( 'gioi-thieu' ) ) . '">Giới thiệu</a></li><li><a href="' . esc_url( vwd_page_url( 'tin-tuc' ) ) . '">Tin tức</a></li><li><a href="' . esc_url( vwd_page_url( 'lien-he' ) ) . '">Liên hệ</a></li></ul>';
			}
			?>
		</div>
		<div class="footer__col">
			<h4>Các dòng xe</h4>
			<ul class="footer__links footer__links--cars">
				<?php foreach ( vwd_get_cars() as $c ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><?php echo esc_html( $c->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="footer__bottom">
		<div class="container">
			<p>Copyright <?php echo esc_html( date( 'Y' ) ); ?> © <?php echo esc_html( vwd_get( 'dealer_name' ) ); ?>. Website demo – giá và ưu đãi mang tính tham khảo.</p>
		</div>
	</div>
</footer>

<!-- Floating: gọi / Zalo / form đăng ký -->
<div class="float-contact">
	<a class="float-contact__btn float-contact__btn--call" href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>" aria-label="Gọi ngay"><?php echo vwt_icon( 'phone' ); ?><span>Gọi ngay</span></a>
	<a class="float-contact__btn float-contact__btn--zalo" href="<?php echo esc_url( vwd_get( 'zalo' ) ); ?>" target="_blank" rel="noopener" aria-label="Chat Zalo"><?php echo vwt_icon( 'zalo' ); ?><span>Zalo</span></a>
	<button class="float-contact__btn float-contact__btn--form" id="float-form-toggle" aria-expanded="false"><?php echo vwt_icon( 'steering' ); ?><span>Đăng ký tư vấn</span></button>
</div>
<div class="float-form" id="float-form" hidden>
	<button class="float-form__close" id="float-form-close" aria-label="Đóng">×</button>
	<?php vwd_render_form( array( 'kind' => 'Tư vấn', 'title' => 'Đăng ký tư vấn', 'button' => 'Gửi đăng ký', 'compact' => '1' ) ); ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
