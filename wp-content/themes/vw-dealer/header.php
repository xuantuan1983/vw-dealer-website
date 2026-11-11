<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Bỏ qua đến nội dung</a>

<div class="topbar">
	<div class="container topbar__inner">
		<div class="topbar__left">
			<?php echo vwt_icon( 'pin' ); ?><span><?php echo esc_html( vwd_get( 'address' ) ); ?></span>
		</div>
		<div class="topbar__right">
			<a href="<?php echo esc_url( vwd_page_url( 'chinh-sach' ) ); ?>">Chính sách</a>
			<a href="<?php echo esc_url( vwd_page_url( 'khuyen-mai' ) ); ?>">Khuyến mãi</a>
			<a href="<?php echo esc_url( vwd_page_url( 'lien-he' ) ); ?>">Liên hệ</a>
			<a class="topbar__phone" href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>"><?php echo vwt_icon( 'phone' ); ?><?php echo esc_html( vwd_get( 'hotline' ) ); ?></a>
		</div>
	</div>
</div>

<header class="site-header" id="site-header">
	<div class="container site-header__inner">
		<div class="site-brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-mark.svg' ); ?>" alt="Volkswagen" class="site-logo__mark">
					<span class="site-logo__text"><?php echo esc_html( vwd_get( 'dealer_name' ) ); ?></span>
				<?php endif; ?>
			</a>
		</div>

		<nav class="main-nav" id="main-nav" aria-label="Menu chính">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'menu', 'depth' => 2 ) );
			} else {
				echo '<ul class="menu"><li><a href="' . esc_url( home_url( '/' ) ) . '">Trang chủ</a></li><li><a href="' . esc_url( get_post_type_archive_link( 'vw_car' ) ) . '">Các dòng xe</a></li><li><a href="' . esc_url( vwd_page_url( 'tin-tuc' ) ) . '">Tin tức</a></li></ul>';
			}
			?>
			<div class="main-nav__mobile-extra">
				<a class="btn btn--primary btn--block" href="<?php echo esc_url( vwd_page_url( 'dang-ky-lai-thu' ) ); ?>">Đăng ký lái thử</a>
				<a class="btn btn--outline btn--block" href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>"><?php echo esc_html( vwd_get( 'hotline' ) ); ?></a>
			</div>
		</nav>

		<div class="site-header__actions">
			<form class="header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" name="s" placeholder="Tìm kiếm…" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="Tìm kiếm">
				<button type="submit" aria-label="Tìm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></button>
			</form>
			<a class="header-hotline" href="tel:<?php echo esc_attr( vwd_hotline_raw() ); ?>">
				<?php echo vwt_icon( 'phone' ); ?>
				<span><small>Hotline 24/7</small><strong><?php echo esc_html( vwd_get( 'hotline' ) ); ?></strong></span>
			</a>
			<button class="nav-toggle" id="nav-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Mở menu"><span></span><span></span><span></span></button>
		</div>
	</div>
</header>

<main id="main" class="site-main">
