<?php
/** Danh sách dòng xe (archive) */
get_header();
$cars = vwd_get_cars();
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1>Các dòng xe Volkswagen</h1><p>Chọn dòng xe phù hợp với bạn – SUV, MPV hay Hatchback.</p></div></section>
<section class="section">
	<div class="container">
		<div class="cars-grid">
			<?php foreach ( $cars as $c ) : ?><div class="cars-grid__item"><?php vwt_car_card( $c ); ?></div><?php endforeach; ?>
		</div>
	</div>
</section>
<?php get_footer();
