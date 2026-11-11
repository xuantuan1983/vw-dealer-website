<?php get_header(); the_post(); ?>
<section class="page-head"><div class="container"><?php vwt_breadcrumb(); ?><h1><?php the_title(); ?></h1></div></section>
<section class="section"><div class="container container--narrow"><div class="entry-content"><?php the_content(); ?></div></div></section>
<?php get_footer();
