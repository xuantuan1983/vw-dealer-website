<?php
/**
 * Template Name: Mua xe trả góp
 */
get_header();
the_post();
$cars = vwd_get_cars();
$gia  = isset( $_GET['gia'] ) ? (int) $_GET['gia'] : 0;
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1><p>Hỗ trợ vay đến 80% giá trị xe, thời hạn đến 8 năm, lãi suất ưu đãi từ các ngân hàng đối tác.</p></div></section>
<section class="section">
	<div class="container page-layout">
		<div class="page-main">
			<div class="card calc" id="calc">
				<h2>Công cụ tính trả góp</h2>
				<div class="calc__grid">
					<label>Dòng xe / phiên bản
						<select id="calc-car">
							<option value="<?php echo (int) $gia; ?>"><?php echo $gia ? 'Giá đã chọn: ' . esc_html( vwd_price( $gia ) ) : '— Chọn xe —'; ?></option>
							<?php foreach ( $cars as $c ) : $vs = vwd_car_meta( $c->ID, 'variants', array() ); foreach ( $vs as $v ) : ?>
								<option value="<?php echo (int) ( $v['price'] ?? 0 ); ?>"><?php echo esc_html( $v['name'] ); ?> – <?php echo esc_html( vwd_price( $v['price'] ?? 0 ) ); ?></option>
							<?php endforeach; endforeach; ?>
						</select>
					</label>
					<label>Giá xe (VNĐ)<input type="number" id="calc-price" value="<?php echo (int) $gia; ?>" step="1000000" min="0"></label>
					<label>Tỷ lệ vay (%)<input type="range" id="calc-ratio" min="10" max="80" value="70"><output id="calc-ratio-out">70%</output></label>
					<label>Thời hạn vay (năm)<input type="range" id="calc-years" min="1" max="8" value="5"><output id="calc-years-out">5 năm</output></label>
					<label>Lãi suất (%/năm)<input type="number" id="calc-rate" value="8.5" step="0.1" min="0"></label>
				</div>
				<div class="calc__result">
					<div><small>Số tiền vay</small><strong id="r-loan">0 ₫</strong></div>
					<div><small>Trả trước</small><strong id="r-down">0 ₫</strong></div>
					<div><small>Trả hàng tháng (tháng đầu)</small><strong id="r-month" class="price-now">0 ₫</strong></div>
					<div><small>Tổng lãi dự kiến</small><strong id="r-interest">0 ₫</strong></div>
				</div>
				<p class="section__note">Tính theo dư nợ giảm dần, mang tính tham khảo. Liên hệ để nhận phương án vay chính xác từ ngân hàng.</p>
			</div>
			<?php if ( get_the_content() ) : ?><div class="entry-content"><?php the_content(); ?></div><?php else : ?>
			<div class="entry-content">
				<h2>Điều kiện & hồ sơ vay</h2>
				<ul><li>Công dân Việt Nam 20–65 tuổi, có thu nhập ổn định.</li><li>CCCD, sổ hộ khẩu/xác nhận cư trú, giấy đăng ký kết hôn (nếu có).</li><li>Chứng minh thu nhập: sao kê lương, hợp đồng lao động hoặc giấy phép kinh doanh.</li><li>Hồ sơ mua xe: hợp đồng mua bán, biên lai đặt cọc.</li></ul>
				<h2>Quy trình</h2>
				<ol><li>Chọn xe và phương án vay cùng chuyên viên.</li><li>Nộp hồ sơ – ngân hàng thẩm định trong 1–2 ngày.</li><li>Ký hợp đồng tín dụng, giải ngân.</li><li>Nhận xe và giấy tờ.</li></ol>
			</div>
			<?php endif; ?>
		</div>
		<aside class="page-side"><div class="sticky"><div class="card"><?php vwd_render_form( array( 'kind' => 'Trả góp', 'title' => 'Tư vấn mua xe trả góp', 'button' => 'Nhận tư vấn vay' ) ); ?></div></div></aside>
	</div>
</section>
<?php get_footer();
