<?php
/**
 * Template Name: Các dòng xe
 */
get_header();
the_post();
$cars  = vwd_get_cars();
$types = array();
foreach ( $cars as $c ) { $t = vwd_car_meta( $c->ID, 'type' ); if ( $t ) { $types[ $t ] = ( $types[ $t ] ?? 0 ) + 1; } }
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Giá niêm yết đã bao gồm VAT. Liên hệ hotline <?php echo esc_html( vwd_get( 'hotline' ) ); ?> để nhận ưu đãi tháng này.</p></div></section>
<section class="section">
	<div class="container">
		<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php endif; ?>
		<div class="filter-tabs" data-filter-target="#cars-grid">
			<button class="is-active" data-filter="*">Tất cả <small><?php echo count( $cars ); ?></small></button>
			<?php foreach ( $types as $t => $n ) : ?><button data-filter="<?php echo esc_attr( sanitize_title( $t ) ); ?>"><?php echo esc_html( $t ); ?> <small><?php echo (int) $n; ?></small></button><?php endforeach; ?>
		</div>
		<div class="cars-grid" id="cars-grid">
			<?php foreach ( $cars as $c ) : ?><div class="cars-grid__item" data-type="<?php echo esc_attr( sanitize_title( vwd_car_meta( $c->ID, 'type' ) ) ); ?>"><?php vwt_car_card( $c ); ?></div><?php endforeach; ?>
		</div>
	</div>
</section>
<?php get_footer();
