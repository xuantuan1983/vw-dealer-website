<?php
/** Trang tin tức / archive / category */
get_header();
$title = 'Tin tức';
$desc  = 'Tin tức, đánh giá xe, bảng giá và ưu đãi Volkswagen mới nhất.';
if ( is_category() ) { $title = single_cat_title( '', false ); $desc = category_description() ? wp_strip_all_tags( category_description() ) : $desc; }
elseif ( is_tag() ) { $title = 'Thẻ: ' . single_tag_title( '', false ); }
elseif ( is_search() ) { $title = 'Kết quả tìm kiếm: ' . get_search_query(); $desc = ''; }
elseif ( is_home() && get_option( 'page_for_posts' ) ) { $title = get_the_title( get_option( 'page_for_posts' ) ); }
?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php echo esc_html( $title ); ?></h1><?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?></div></section>
<section class="section">
	<div class="container">
		<?php $cats = get_categories( array( 'hide_empty' => true ) ); if ( $cats && ! is_search() ) : ?>
			<div class="filter-tabs filter-tabs--links">
				<a class="<?php echo is_home() ? 'is-active' : ''; ?>" href="<?php echo esc_url( vwd_page_url( 'tin-tuc' ) ); ?>">Tất cả</a>
				<?php foreach ( $cats as $c ) : ?><a class="<?php echo is_category( $c->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?> <small><?php echo (int) $c->count; ?></small></a><?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<div class="posts-grid posts-grid--3">
				<?php while ( have_posts() ) : the_post(); if ( 'vw_car' === get_post_type() ) { echo '<div>'; vwt_car_card( get_post() ); echo '</div>'; } else { vwt_post_card( get_post() ); } endwhile; ?>
			</div>
			<div class="pagination"><?php the_posts_pagination( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?></div>
		<?php else : ?>
			<p>Chưa có bài viết nào.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer();
