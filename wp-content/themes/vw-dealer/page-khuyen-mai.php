<?php
/**
 * Template Name: Khuyến mãi
 */
get_header();
the_post();
$cat   = get_term_by( 'name', 'Khuyến mãi', 'category' );
$posts = $cat ? get_posts( array( 'category' => $cat->term_id, 'posts_per_page' => 12 ) ) : array();
$cars  = vwd_get_cars();
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Ưu đãi đang áp dụng tại <?php echo esc_html( vwd_get( 'dealer_name' ) ); ?>.</p></div></section>
<section class="section">
	<div class="container">
		<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php endif; ?>
		<?php if ( $posts ) : ?>
			<div class="posts-grid posts-grid--3"><?php foreach ( $posts as $p ) { vwt_post_card( $p ); } ?></div>
		<?php endif; ?>
		<h2 class="section__title" style="margin-top:40px">Ưu đãi theo dòng xe</h2>
		<div class="table-wrap"><table class="table table--full"><thead><tr><th>Dòng xe</th><th>Giá niêm yết</th><th>Giá ưu đãi</th><th>Ghi chú</th></tr></thead><tbody>
		<?php foreach ( $cars as $c ) : $from = (float) vwd_car_meta( $c->ID, 'price_from', 0 ); $promo = (float) vwd_car_meta( $c->ID, 'price_promo', 0 ); $note = vwd_car_meta( $c->ID, 'promo_note' ); if ( ! $promo && ! $note ) { continue; } ?>
			<tr><td><a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><?php echo esc_html( $c->post_title ); ?></a></td><td><?php echo esc_html( vwd_price( $from ) ); ?></td><td class="price-now"><?php echo $promo ? esc_html( vwd_price( $promo ) ) : '—'; ?></td><td><?php echo esc_html( $note ); ?></td></tr>
		<?php endforeach; ?>
		</tbody></table></div>
	</div>
</section>
<?php get_footer();
