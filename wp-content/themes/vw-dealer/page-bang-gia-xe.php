<?php
/**
 * Template Name: Bảng giá xe
 */
get_header();
the_post();
$cars = vwd_get_cars();
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Cập nhật <?php echo esc_html( date_i18n( 'm/Y' ) ); ?> – giá niêm yết đã gồm VAT, chưa gồm lệ phí trước bạ, biển số, bảo hiểm.</p></div></section>
<section class="section">
	<div class="container page-layout">
		<div class="page-main">
			<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php endif; ?>
			<div class="table-wrap">
				<table class="table table--price table--full">
					<thead><tr><th>Dòng xe</th><th>Phiên bản</th><th>Giá niêm yết</th><th>Ưu đãi</th><th></th></tr></thead>
					<tbody>
					<?php foreach ( $cars as $c ) :
						$variants = vwd_car_meta( $c->ID, 'variants', array() );
						if ( ! $variants ) { $variants = array( array( 'name' => $c->post_title, 'price' => vwd_car_meta( $c->ID, 'price_from', 0 ) ) ); }
						$note = vwd_car_meta( $c->ID, 'promo_note' );
						$n = count( $variants );
						foreach ( $variants as $k => $v ) : ?>
						<tr>
							<?php if ( 0 === $k ) : ?><td rowspan="<?php echo (int) $n; ?>" class="table__car"><a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><img src="<?php echo esc_url( vwd_car_image( $c->ID, 'thumbnail' ) ); ?>" alt="" loading="lazy"><strong><?php echo esc_html( $c->post_title ); ?></strong></a></td><?php endif; ?>
							<td><?php echo esc_html( $v['name'] ); ?></td>
							<td class="price-now"><?php echo esc_html( vwd_price( $v['price'] ?? 0 ) ); ?></td>
							<?php if ( 0 === $k ) : ?><td rowspan="<?php echo (int) $n; ?>" class="table__promo"><?php echo $note ? esc_html( $note ) : '—'; ?></td><?php endif; ?>
							<td><a class="btn btn--sm btn--outline" href="<?php echo esc_url( add_query_arg( 'gia', (int) ( $v['price'] ?? 0 ), vwd_page_url( 'mua-xe-tra-gop' ) ) ); ?>">Trả góp</a></td>
						</tr>
					<?php endforeach; endforeach; ?>
					</tbody>
				</table>
			</div>
			<p class="section__note">* Giá ưu đãi áp dụng có điều kiện và tùy thời điểm theo chính sách của Volkswagen Việt Nam. Liên hệ để nhận giá lăn bánh chính xác.</p>
		</div>
		<aside class="page-side"><div class="sticky"><div class="card"><?php vwd_render_form( array( 'kind' => 'Báo giá', 'title' => 'Nhận báo giá lăn bánh', 'button' => 'Nhận báo giá' ) ); ?></div></div></aside>
	</div>
</section>
<?php get_footer();
