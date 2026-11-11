<?php get_header(); the_post(); $cat = get_the_category(); ?>
<section class="page-head page-head--post"><div class="container"><?php vwt_breadcrumb(); ?>
	<div class="post-meta"><?php if ( $cat ) : ?><a class="tag" href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>"><?php echo esc_html( $cat[0]->name ); ?></a><?php endif; ?> <time><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time></div>
	<h1><?php the_title(); ?></h1>
	<?php if ( has_excerpt() ) : ?><p class="post-lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
</div></section>
<section class="section">
	<div class="container page-layout">
		<article class="page-main">
			<?php if ( has_post_thumbnail() ) : ?><figure class="post-thumb"><?php the_post_thumbnail( 'large' ); ?></figure><?php endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
			<?php the_tags( '<p class="post-tags">Thẻ: ', ', ', '</p>' ); ?>
			<nav class="post-nav"><?php previous_post_link( '<div class="post-nav__prev">‹ %link</div>' ); next_post_link( '<div class="post-nav__next">%link ›</div>' ); ?></nav>
		</article>
		<aside class="page-side">
			<div class="sticky">
				<div class="card"><?php vwd_render_form( array( 'kind' => 'Tư vấn', 'title' => 'Đăng ký tư vấn', 'button' => 'Gửi đăng ký', 'compact' => '1' ) ); ?></div>
				<?php $recent = get_posts( array( 'posts_per_page' => 5, 'post__not_in' => array( get_the_ID() ) ) ); if ( $recent ) : ?>
				<div class="card"><h3>Bài viết mới</h3><ul class="recent-list"><?php foreach ( $recent as $r ) : ?><li><a href="<?php echo esc_url( get_permalink( $r ) ); ?>"><?php echo esc_html( get_the_title( $r ) ); ?></a><small><?php echo esc_html( get_the_date( 'd/m/Y', $r ) ); ?></small></li><?php endforeach; ?></ul></div>
				<?php endif; ?>
			</div>
		</aside>
	</div>
</section>
<?php get_footer();
