<?php
/**
 * Template Name: Đăng ký lái thử
 */
get_header();
the_post();
$car = isset( $_GET['car'] ) ? sanitize_text_field( wp_unslash( $_GET['car'] ) ) : '';
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Trải nghiệm xe Volkswagen tại showroom hoặc tận nhà – hoàn toàn miễn phí.</p></div></section>
<section class="section">
	<div class="container page-layout page-layout--form">
		<div class="page-main">
			<div class="card" id="dang-ky"><?php vwd_render_form( array( 'kind' => 'Lái thử', 'title' => 'Thông tin đăng ký', 'car' => $car, 'button' => 'Đăng ký lái thử' ) ); ?></div>
		</div>
		<aside class="page-side">
			<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php else : ?>
			<div class="entry-content">
				<h2>Quy trình lái thử</h2>
				<ol><li>Gửi thông tin đăng ký.</li><li>Chuyên viên liên hệ xác nhận thời gian, địa điểm trong 30 phút.</li><li>Trải nghiệm xe cùng chuyên viên, giải đáp mọi thắc mắc.</li><li>Nhận báo giá & ưu đãi dành riêng cho khách lái thử.</li></ol>
				<h2>Lưu ý</h2><ul><li>Mang theo giấy phép lái xe hạng B trở lên.</li><li>Lái thử tận nhà áp dụng trong bán kính 20 km từ showroom.</li></ul>
			</div>
			<?php endif; ?>
		</aside>
	</div>
</section>
<?php get_footer();
