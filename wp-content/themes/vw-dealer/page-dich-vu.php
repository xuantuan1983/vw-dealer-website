<?php
/**
 * Template Name: Dịch vụ
 */
get_header();
the_post();
$services = array(
	array( 'shield', 'Bảo hành chính hãng', 'Bảo hành 3 năm hoặc 100.000 km cho xe mới, áp dụng tại toàn bộ hệ thống đại lý Volkswagen Việt Nam.' ),
	array( 'steering', 'Bảo dưỡng định kỳ', 'Gói bảo dưỡng theo chuẩn Volkswagen toàn cầu, kỹ thuật viên được đào tạo & chứng nhận, phụ tùng chính hãng.' ),
	array( 'headset', 'Cứu hộ 24/7', 'Hỗ trợ cứu hộ, kéo xe và tư vấn kỹ thuật 24/7 trên toàn quốc.' ),
	array( 'percent', 'Tài chính & bảo hiểm', 'Liên kết ngân hàng, hỗ trợ vay đến 80%, bảo hiểm vật chất với mức phí ưu đãi.' ),
	array( 'check', 'Đồng sơn & phụ kiện', 'Xưởng đồng sơn đạt chuẩn, phụ kiện chính hãng Volkswagen cá nhân hóa chiếc xe của bạn.' ),
	array( 'pin', 'Dịch vụ tận nơi', 'Nhận & giao xe bảo dưỡng tận nhà, lái thử tận nơi trong bán kính 20 km.' ),
);
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Dịch vụ 3S chính hãng – bán hàng, dịch vụ, phụ tùng.</p></div></section>
<section class="section">
	<div class="container">
		<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php endif; ?>
		<div class="services-grid">
			<?php foreach ( $services as $s ) : ?>
				<div class="service"><?php echo vwt_icon( $s[0] ); ?><h3><?php echo esc_html( $s[1] ); ?></h3><p><?php echo esc_html( $s[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<section class="cta-banner"><div class="container cta-banner__inner"><div><h2>Đặt lịch dịch vụ</h2><p>Gọi hotline <?php echo esc_html( vwd_get( 'hotline' ) ); ?> hoặc để lại thông tin, cố vấn dịch vụ sẽ liên hệ xác nhận.</p></div><a class="btn btn--light" href="<?php echo esc_url( vwd_page_url( 'lien-he' ) ); ?>">Liên hệ ngay</a></div></section>
<?php get_footer();
