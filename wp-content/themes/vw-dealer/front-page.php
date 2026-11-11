<?php
/**
 * Trang chủ – cấu trúc tham khảo giaxevinfasthcm.com:
 * Hero slider → 4 ô CTA → Lưới dòng xe → Về chúng tôi → Công nghệ Đức → CTA lái thử → Tin tức → Video
 */
get_header();

$cars   = vwd_get_cars();
$by     = array();
foreach ( $cars as $c ) {
	$by[ $c->post_name ] = $c;
}
$site   = function_exists( 'vwd_data_file' ) ? vwd_data_file( 'site.json' ) : array();
$slides = ! empty( $site['hero_slides'] ) ? $site['hero_slides'] : array();
$ctas   = ! empty( $site['cta_boxes'] ) ? $site['cta_boxes'] : array();
$videos = ! empty( $site['videos'] ) ? $site['videos'] : array();
?>

<!-- HERO SLIDER -->
<section class="hero" id="hero">
	<div class="hero__track">
		<?php
		$i = 0;
		foreach ( $slides as $s ) :
			if ( empty( $by[ $s['car'] ] ) ) {
				continue;
			}
			$car = $by[ $s['car'] ];
			?>
			<div class="hero__slide <?php echo 0 === $i ? 'is-active' : ''; ?>">
				<div class="hero__bg" style="background-image:url('<?php echo esc_url( vwd_car_image( $car->ID, 'vw-hero' ) ); ?>')"></div>
				<div class="container hero__content">
					<span class="hero__eyebrow"><?php echo esc_html( vwd_car_meta( $car->ID, 'badge' ) ? 'Mới ra mắt' : 'Volkswagen' ); ?></span>
					<h1 class="hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<p class="hero__sub"><?php echo esc_html( $s['sub'] ); ?></p>
					<div class="hero__price">Giá từ <strong><?php echo esc_html( vwd_price( vwd_car_meta( $car->ID, 'price_from', 0 ) ) ); ?></strong></div>
					<div class="hero__actions">
						<a class="btn btn--primary" href="<?php echo esc_url( get_permalink( $car ) ); ?>">Khám phá <?php echo esc_html( vwd_car_meta( $car->ID, 'short_name', $car->post_title ) ); ?></a>
						<a class="btn btn--ghost" href="<?php echo esc_url( vwd_page_url( 'dang-ky-lai-thu' ) ); ?>">Đăng ký lái thử</a>
					</div>
				</div>
			</div>
			<?php
			$i++;
		endforeach;
		if ( 0 === $i ) :
			?>
			<div class="hero__slide is-active">
				<div class="container hero__content">
					<h1 class="hero__title"><?php echo esc_html( vwd_get( 'dealer_name' ) ); ?></h1>
					<p class="hero__sub">Vui lòng vào <strong>VW Dealer → Setup</strong> để nhập dữ liệu demo.</p>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( $i > 1 ) : ?>
		<button class="hero__arrow hero__arrow--prev" data-dir="-1" aria-label="Trước">‹</button>
		<button class="hero__arrow hero__arrow--next" data-dir="1" aria-label="Sau">›</button>
		<div class="hero__dots"><?php for ( $d = 0; $d < $i; $d++ ) : ?><button data-index="<?php echo (int) $d; ?>" class="<?php echo 0 === $d ? 'is-active' : ''; ?>" aria-label="Slide <?php echo (int) $d + 1; ?>"></button><?php endfor; ?></div>
	<?php endif; ?>
</section>

<!-- 4 Ô CTA -->
<section class="cta-boxes">
	<div class="container cta-boxes__grid">
		<?php foreach ( $ctas as $b ) : ?>
			<a class="cta-box" href="<?php echo esc_url( home_url( $b['link'] ) ); ?>">
				<?php echo vwt_icon( $b['icon'] ); ?>
				<h3><?php echo esc_html( $b['title'] ); ?></h3>
				<p><?php echo esc_html( $b['text'] ); ?></p>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<!-- LƯỚI DÒNG XE -->
<section class="section section--cars" id="dong-xe">
	<div class="container">
		<div class="section__head">
			<span class="section__eyebrow">Sản phẩm</span>
			<h2 class="section__title">Khám phá các dòng xe Volkswagen</h2>
			<p class="section__desc">Giá niêm yết đã bao gồm VAT. Giá ưu đãi áp dụng có điều kiện và tùy thời điểm theo chính sách của Volkswagen Việt Nam.</p>
		</div>
		<?php
		$types = array();
		foreach ( $cars as $c ) {
			$t = vwd_car_meta( $c->ID, 'type' );
			if ( $t ) {
				$types[ $t ] = isset( $types[ $t ] ) ? $types[ $t ] + 1 : 1;
			}
		}
		if ( $types ) :
			?>
			<div class="filter-tabs" data-filter-target="#cars-grid">
				<button class="is-active" data-filter="*">Tất cả <small><?php echo count( $cars ); ?></small></button>
				<?php foreach ( $types as $t => $n ) : ?>
					<button data-filter="<?php echo esc_attr( sanitize_title( $t ) ); ?>"><?php echo esc_html( $t ); ?> <small><?php echo (int) $n; ?></small></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="cars-grid" id="cars-grid">
			<?php foreach ( $cars as $c ) : ?>
				<div class="cars-grid__item" data-type="<?php echo esc_attr( sanitize_title( vwd_car_meta( $c->ID, 'type' ) ) ); ?>"><?php vwt_car_card( $c ); ?></div>
			<?php endforeach; ?>
		</div>
		<p class="section__note">* Giá khuyến mãi áp dụng có điều kiện và tùy thời điểm theo chính sách của Volkswagen Việt Nam.</p>
	</div>
</section>

<!-- VỀ CHÚNG TÔI -->
<section class="section section--split">
	<div class="container split">
		<div class="split__media">
			<img src="<?php echo esc_url( ! empty( $by['vw-touareg'] ) ? vwd_car_image( $by['vw-touareg']->ID ) : get_template_directory_uri() . '/assets/img/placeholder-car.svg' ); ?>" alt="Showroom <?php echo esc_attr( vwd_get( 'dealer_name' ) ); ?>" loading="lazy">
		</div>
		<div class="split__content">
			<span class="section__eyebrow">Về chúng tôi</span>
			<h2 class="section__title"><?php echo esc_html( vwd_get( 'dealer_name' ) ); ?> – đồng hành trên mọi hành trình</h2>
			<p>Đại lý ủy quyền chính hãng Volkswagen với đầy đủ dịch vụ 3S: trưng bày & bán hàng, xưởng dịch vụ đạt chuẩn toàn cầu, phụ tùng chính hãng. Đội ngũ tư vấn tận tâm, minh bạch về giá và chương trình ưu đãi.</p>
			<ul class="check-list">
				<li><?php echo vwt_icon( 'check' ); ?>Bảo hành 3 năm hoặc 100.000 km trên toàn quốc</li>
				<li><?php echo vwt_icon( 'check' ); ?>Giao xe đúng hẹn, hỗ trợ đăng ký – đăng kiểm trọn gói</li>
				<li><?php echo vwt_icon( 'check' ); ?>Cứu hộ 24/7 và nhắc lịch bảo dưỡng định kỳ</li>
			</ul>
			<a class="btn btn--primary" href="<?php echo esc_url( vwd_page_url( 'lien-he' ) ); ?>">Liên hệ tư vấn</a>
		</div>
	</div>
</section>

<!-- CÔNG NGHỆ ĐỨC -->
<section class="section section--split section--alt">
	<div class="container split split--reverse">
		<div class="split__media">
			<img src="<?php echo esc_url( ! empty( $by['vw-id-era-9x'] ) ? vwd_car_image( $by['vw-id-era-9x']->ID ) : get_template_directory_uri() . '/assets/img/placeholder-car.svg' ); ?>" alt="Volkswagen ID. ERA 9X" loading="lazy">
		</div>
		<div class="split__content">
			<span class="section__eyebrow">Công nghệ</span>
			<h2 class="section__title">Kỹ thuật Đức – vì một tương lai di chuyển bền vững</h2>
			<p>Từ động cơ TSI tiết kiệm nhiên liệu, hệ dẫn động 4MOTION, đến nền tảng EREV trên ID. ERA 9X với phạm vi tổng 1.651 km – Volkswagen mang công nghệ Đức đến mọi hành trình của bạn.</p>
			<a class="btn btn--primary" href="<?php echo esc_url( ! empty( $by['vw-id-era-9x'] ) ? get_permalink( $by['vw-id-era-9x'] ) : vwd_page_url( 'cac-dong-xe' ) ); ?>">Khám phá ID. ERA 9X</a>
		</div>
	</div>
</section>

<!-- CTA LÁI THỬ -->
<section class="cta-banner">
	<div class="container cta-banner__inner">
		<div>
			<h2>Volkswagen – Khát vọng vươn xa</h2>
			<p>Đặt lịch lái thử tại showroom hoặc tận nhà, chuyên viên sẽ liên hệ xác nhận trong 30 phút.</p>
		</div>
		<a class="btn btn--light" href="<?php echo esc_url( vwd_page_url( 'dang-ky-lai-thu' ) ); ?>">Đăng ký lái thử</a>
	</div>
</section>

<!-- TIN TỨC -->
<?php
$news = get_posts( array( 'posts_per_page' => 6, 'post_status' => 'publish' ) );
if ( $news ) :
	?>
	<section class="section section--news">
		<div class="container">
			<div class="section__head section__head--row">
				<div>
					<span class="section__eyebrow">Tin tức & sự kiện</span>
					<h2 class="section__title">Tin tức mới nhất</h2>
				</div>
				<a class="btn btn--outline" href="<?php echo esc_url( vwd_page_url( 'tin-tuc' ) ); ?>">Xem tất cả</a>
			</div>
			<div class="posts-grid posts-grid--3">
				<?php foreach ( $news as $p ) { vwt_post_card( $p ); } ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- VIDEO -->
<?php
$videos = array_filter( $videos, function ( $v ) { return false === strpos( $v, 'VIDEO_ID' ); } );
if ( $videos ) :
	?>
	<section class="section section--videos section--alt">
		<div class="container">
			<div class="section__head"><span class="section__eyebrow">Video</span><h2 class="section__title">Video hữu ích</h2></div>
			<div class="videos-grid">
				<?php foreach ( $videos as $v ) : ?>
					<div class="video"><iframe src="<?php echo esc_url( $v ); ?>" loading="lazy" allowfullscreen title="Video Volkswagen"></iframe></div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_footer();
