<?php
/**
 * Template Name: Giới thiệu
 */
get_header();
the_post();
$cars = vwd_get_cars( array( 'posts_per_page' => 1, 'post_name__in' => array( 'vw-touareg' ) ) );
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p><?php echo esc_html( vwd_get( 'tagline' ) ); ?></p></div></section>
<section class="section">
	<div class="container split">
		<div class="split__media"><img src="<?php echo esc_url( $cars ? vwd_car_image( $cars[0]->ID ) : get_template_directory_uri() . '/assets/img/placeholder-car.svg' ); ?>" alt="<?php echo esc_attr( vwd_get( 'dealer_name' ) ); ?>"></div>
		<div class="split__content entry-content"><?php the_content(); ?></div>
	</div>
</section>
<section class="section section--alt">
	<div class="container stats">
		<div class="stat"><strong>11+</strong><span>Dòng xe đang phân phối</span></div>
		<div class="stat"><strong>3 năm</strong><span>Bảo hành chính hãng</span></div>
		<div class="stat"><strong>24/7</strong><span>Hỗ trợ cứu hộ</span></div>
		<div class="stat"><strong>80%</strong><span>Hỗ trợ vay tối đa</span></div>
	</div>
</section>
<?php get_footer();
