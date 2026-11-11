<?php
/**
 * Trang chi tiết dòng xe.
 */
get_header();
the_post();
$id         = get_the_ID();
$from       = (float) vwd_car_meta( $id, 'price_from', 0 );
$promo      = (float) vwd_car_meta( $id, 'price_promo', 0 );
$variants   = vwd_car_meta( $id, 'variants', array() );
$highlights = vwd_car_meta( $id, 'highlights', array() );
$specs      = vwd_car_meta( $id, 'specs', array() );
$faq        = vwd_car_meta( $id, 'faq', array() );
$gallery    = vwd_car_gallery( $id );
$video      = vwd_car_meta( $id, 'video' );
$short      = vwd_car_meta( $id, 'short_name', get_the_title() );
?>
<section class="car-hero">
	<div class="container">
		<?php vwt_breadcrumb(); ?>
		<div class="car-hero__grid">
			<div class="car-hero__media">
				<img src="<?php echo esc_url( vwd_car_image( $id, 'vw-hero' ) ); ?>" alt="<?php the_title_attribute(); ?>">
			</div>
			<div class="car-hero__info">
				<?php if ( vwd_car_meta( $id, 'badge' ) ) : ?><span class="badge"><?php echo esc_html( vwd_car_meta( $id, 'badge' ) ); ?></span><?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<p class="car-hero__summary"><?php echo esc_html( vwd_car_meta( $id, 'summary', get_the_excerpt() ) ); ?></p>
				<ul class="car-hero__facts">
					<li><small>Kiểu xe</small><strong><?php echo esc_html( vwd_car_meta( $id, 'type' ) ); ?> · <?php echo esc_html( vwd_car_meta( $id, 'seats' ) ); ?> chỗ</strong></li>
					<li><small>Động cơ</small><strong><?php echo esc_html( vwd_car_meta( $id, 'engine' ) ); ?></strong></li>
					<li><small>Công suất</small><strong><?php echo esc_html( vwd_car_meta( $id, 'power' ) ); ?></strong></li>
					<li><small>Dẫn động</small><strong><?php echo esc_html( vwd_car_meta( $id, 'drive' ) ); ?></strong></li>
				</ul>
				<div class="car-hero__price">
					<?php if ( $promo > 0 && $promo < $from ) : ?>
						<span class="price-old"><?php echo esc_html( vwd_price( $from ) ); ?></span>
						<span class="price-now"><?php echo esc_html( vwd_price( $promo ) ); ?></span>
					<?php else : ?>
						<span class="price-label">Giá từ</span><span class="price-now"><?php echo esc_html( vwd_price( $from ) ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( vwd_car_meta( $id, 'promo_note' ) ) : ?><p class="promo-note">🎁 <?php echo esc_html( vwd_car_meta( $id, 'promo_note' ) ); ?></p><?php endif; ?>
				<div class="car-hero__actions">
					<a class="btn btn--primary" href="#dang-ky">Nhận báo giá</a>
					<a class="btn btn--outline" href="<?php echo esc_url( add_query_arg( 'car', rawurlencode( get_the_title() ), vwd_page_url( 'dang-ky-lai-thu' ) ) ); ?>">Đăng ký lái thử</a>
					<a class="btn btn--outline" href="<?php echo esc_url( add_query_arg( 'gia', (int) ( $promo > 0 ? $promo : $from ), vwd_page_url( 'mua-xe-tra-gop' ) ) ); ?>">Tính trả góp</a>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="container car-layout">
	<div class="car-main">
		<?php if ( $variants ) : ?>
			<section class="car-section" id="phien-ban">
				<h2>Phiên bản & giá niêm yết</h2>
				<table class="table table--price">
					<thead><tr><th>Phiên bản</th><th>Giá niêm yết</th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $variants as $v ) : ?>
						<tr>
							<td><?php echo esc_html( $v['name'] ); ?></td>
							<td class="price-now"><?php echo esc_html( vwd_price( $v['price'] ?? 0 ) ); ?></td>
							<td><a class="btn btn--sm btn--outline" href="<?php echo esc_url( add_query_arg( 'gia', (int) ( $v['price'] ?? 0 ), vwd_page_url( 'mua-xe-tra-gop' ) ) ); ?>">Trả góp</a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p class="section__note">Giá đã bao gồm VAT, chưa bao gồm lệ phí trước bạ, biển số, bảo hiểm. Giá có thể thay đổi theo thời điểm.</p>
			</section>
		<?php endif; ?>

		<?php if ( $highlights ) : ?>
			<section class="car-section" id="noi-bat">
				<h2>Điểm nổi bật <?php echo esc_html( $short ); ?></h2>
				<ul class="highlights">
					<?php foreach ( $highlights as $h ) : ?><li><?php echo vwt_icon( 'check' ); ?><span><?php echo esc_html( $h ); ?></span></li><?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( get_the_content() ) : ?>
			<section class="car-section entry-content"><?php the_content(); ?></section>
		<?php endif; ?>

		<?php if ( $gallery ) : ?>
			<section class="car-section" id="hinh-anh">
				<h2>Thư viện hình ảnh</h2>
				<div class="gallery" data-lightbox>
					<?php foreach ( $gallery as $g ) : ?>
						<a href="<?php echo esc_url( $g ); ?>" class="gallery__item"><img src="<?php echo esc_url( $g ); ?>" alt="<?php echo esc_attr( $short ); ?>" loading="lazy"></a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $video ) : ?>
			<section class="car-section"><h2>Xem xe qua video</h2><div class="video"><iframe src="<?php echo esc_url( $video ); ?>" allowfullscreen loading="lazy" title="<?php the_title_attribute(); ?>"></iframe></div></section>
		<?php endif; ?>

		<?php if ( $specs ) : ?>
			<section class="car-section" id="thong-so">
				<h2>Thông số kỹ thuật</h2>
				<?php foreach ( $specs as $group ) : ?>
					<h3><?php echo esc_html( $group['title'] ?? '' ); ?></h3>
					<div class="table-wrap">
						<table class="table table--specs">
							<?php foreach ( (array) ( $group['rows'] ?? array() ) as $ri => $row ) : ?>
								<tr><?php foreach ( (array) $row as $ci => $cell ) : ?><?php echo 0 === $ri || 0 === $ci ? '<th>' . esc_html( $cell ) . '</th>' : '<td>' . esc_html( $cell ) . '</td>'; ?><?php endforeach; ?></tr>
							<?php endforeach; ?>
						</table>
					</div>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( $faq ) : ?>
			<section class="car-section" id="faq">
				<h2>Câu hỏi thường gặp về <?php echo esc_html( $short ); ?></h2>
				<div class="accordion">
					<?php foreach ( $faq as $qa ) : ?>
						<details><summary><?php echo esc_html( $qa[0] ?? '' ); ?></summary><p><?php echo esc_html( $qa[1] ?? '' ); ?></p></details>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	</div>

	<aside class="car-side">
		<div class="sticky">
			<div class="card" id="dang-ky">
				<?php vwd_render_form( array( 'kind' => 'Báo giá', 'title' => 'Nhận báo giá ' . $short, 'car' => get_the_title(), 'button' => 'Nhận báo giá ngay' ) ); ?>
			</div>
			<div class="card card--hotline">
				<small>Hotline tư vấn</small>
				<a href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>"><?php echo esc_html( vwd_get( 'hotline' ) ); ?></a>
			</div>
		</div>
	</aside>
</div>

<?php
$others = vwd_get_cars( array( 'post__not_in' => array( $id ), 'posts_per_page' => 3, 'orderby' => 'rand' ) );
if ( $others ) :
	?>
	<section class="section section--alt">
		<div class="container">
			<div class="section__head"><h2 class="section__title">Dòng xe khác</h2></div>
			<div class="cars-grid">
				<?php foreach ( $others as $o ) : ?><div class="cars-grid__item"><?php vwt_car_card( $o ); ?></div><?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_footer();
