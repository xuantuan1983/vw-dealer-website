<?php
/**
 * VW Dealer theme – functions.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VWT_VERSION', '1.0.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 60, 'width' => 240, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_image_size( 'vw-card', 640, 400, true );
	add_image_size( 'vw-hero', 1600, 800, false );
	register_nav_menus( array(
		'primary' => 'Menu chính',
		'footer'  => 'Menu footer',
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'vw-fonts', 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'vw-main', get_template_directory_uri() . '/assets/css/main.css', array(), VWT_VERSION );
	wp_enqueue_script( 'vw-main', get_template_directory_uri() . '/assets/js/main.js', array(), VWT_VERSION, true );
	wp_localize_script( 'vw-main', 'VWD', array( 'hotline' => function_exists( 'vwd_hotline_raw' ) ? vwd_hotline_raw() : '' ) );
} );

/**
 * Fallback khi plugin VW Dealer Core chưa bật – để theme không lỗi.
 */
if ( ! function_exists( 'vwd_get' ) ) {
	function vwd_get( $key, $default = '' ) {
		$d = array( 'dealer_name' => get_bloginfo( 'name' ), 'hotline' => '0907 658 787', 'address' => 'TP. Hồ Chí Minh', 'hours' => '8:00 – 18:00', 'email' => get_option( 'admin_email' ), 'zalo' => '#', 'tagline' => get_bloginfo( 'description' ), 'map_url' => '' );
		return isset( $d[ $key ] ) ? $d[ $key ] : $default;
	}
	function vwd_hotline_raw() { return preg_replace( '/[^0-9+]/', '', vwd_get( 'hotline' ) ); }
	function vwd_price( $n, $s = ' ₫' ) { return $n > 0 ? number_format( (float) $n, 0, ',', '.' ) . $s : 'Liên hệ'; }
	function vwd_price_short( $n ) { return vwd_price( $n ); }
	function vwd_car_meta( $id, $k, $d = '' ) { return $d; }
	function vwd_get_cars( $a = array() ) { return array(); }
	function vwd_car_image( $id, $s = 'large' ) { return get_the_post_thumbnail_url( $id, $s ) ?: get_template_directory_uri() . '/assets/img/placeholder-car.svg'; }
	function vwd_car_gallery( $id, $s = 'large' ) { return array(); }
	function vwd_page_url( $slug ) { return home_url( '/' . $slug . '/' ); }
	function vwd_render_form( $a = array() ) { echo '<p>Vui lòng kích hoạt plugin <strong>VW Dealer Core</strong>.</p>'; }
}

/** Excerpt ngắn gọn. */
add_filter( 'excerpt_length', function () { return 22; } );
add_filter( 'excerpt_more', function () { return '…'; } );

/** Body class theo template. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-home';
	}
	return $classes;
} );

/** Breadcrumb đơn giản. */
function vwt_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}
	echo '<nav class="breadcrumb" aria-label="breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">Trang chủ</a>';
	if ( is_singular( 'vw_car' ) ) {
		echo ' › <a href="' . esc_url( vwd_page_url( 'cac-dong-xe' ) ) . '">Các dòng xe</a> › <span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'post' ) ) {
		$c = get_the_category();
		if ( $c ) {
			echo ' › <a href="' . esc_url( get_category_link( $c[0] ) ) . '">' . esc_html( $c[0]->name ) . '</a>';
		}
		echo ' › <span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		echo ' › <span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_category() ) {
		echo ' › <span>' . esc_html( single_cat_title( '', false ) ) . '</span>';
	} elseif ( is_home() ) {
		echo ' › <span>Tin tức</span>';
	} elseif ( is_search() ) {
		echo ' › <span>Tìm kiếm</span>';
	} elseif ( is_post_type_archive( 'vw_car' ) ) {
		echo ' › <span>Các dòng xe</span>';
	}
	echo '</nav>';
}

/** Thẻ xe dùng chung. */
function vwt_car_card( $post ) {
	$id    = $post->ID;
	$from  = (float) vwd_car_meta( $id, 'price_from', 0 );
	$promo = (float) vwd_car_meta( $id, 'price_promo', 0 );
	$badge = vwd_car_meta( $id, 'badge' );
	$type  = vwd_car_meta( $id, 'type' );
	$seats = vwd_car_meta( $id, 'seats' );
	?>
	<article class="car-card">
		<a class="car-card__img" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
			<?php if ( $badge ) : ?><span class="car-card__badge"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
			<img src="<?php echo esc_url( vwd_car_image( $id, 'vw-card' ) ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>" loading="lazy">
		</a>
		<div class="car-card__body">
			<div class="car-card__meta"><?php echo esc_html( $type ); ?><?php echo $seats ? ' · ' . esc_html( $seats ) . ' chỗ' : ''; ?></div>
			<h3 class="car-card__title"><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a></h3>
			<p class="car-card__desc"><?php echo esc_html( wp_trim_words( vwd_car_meta( $id, 'summary', $post->post_excerpt ), 18 ) ); ?></p>
			<div class="car-card__price">
				<?php if ( $promo > 0 && $promo < $from ) : ?>
					<span class="price-old"><?php echo esc_html( vwd_price( $from ) ); ?></span>
					<span class="price-now"><?php echo esc_html( vwd_price( $promo ) ); ?></span>
				<?php else : ?>
					<span class="price-label">Giá từ</span>
					<span class="price-now"><?php echo esc_html( vwd_price( $from ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="car-card__actions">
				<a class="btn btn--outline" href="<?php echo esc_url( get_permalink( $id ) ); ?>">Xem chi tiết</a>
				<a class="btn btn--primary" href="<?php echo esc_url( add_query_arg( 'car', rawurlencode( $post->post_title ), vwd_page_url( 'dang-ky-lai-thu' ) ) ); ?>#dang-ky">Báo giá</a>
			</div>
		</div>
	</article>
	<?php
}

/** Thẻ bài viết dùng chung. */
function vwt_post_card( $post, $show_excerpt = true ) {
	$cat = get_the_category( $post->ID );
	?>
	<article class="post-card">
		<a class="post-card__img" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<?php echo get_the_post_thumbnail( $post, 'vw-card', array( 'loading' => 'lazy' ) ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/placeholder-car.svg' ); ?>" alt="">
			<?php endif; ?>
		</a>
		<div class="post-card__body">
			<div class="post-card__meta"><?php if ( $cat ) : ?><span class="tag"><?php echo esc_html( $cat[0]->name ); ?></span><?php endif; ?> <time><?php echo esc_html( get_the_date( 'd/m/Y', $post ) ); ?></time></div>
			<h3 class="post-card__title"><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<?php if ( $show_excerpt ) : ?><p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 20 ) ); ?></p><?php endif; ?>
		</div>
	</article>
	<?php
}

/** Icon SVG inline. */
function vwt_icon( $name ) {
	$icons = array(
		'steering' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M3.5 10.5h5M15.5 10.5h5M12 15v6"/></svg>',
		'shield'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg>',
		'percent'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 19L19 5"/><circle cx="7" cy="7" r="2.5"/><circle cx="17" cy="17" r="2.5"/></svg>',
		'headset'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 13v-2a8 8 0 0116 0v2"/><rect x="3" y="12" width="4" height="6" rx="1.5"/><rect x="17" y="12" width="4" height="6" rx="1.5"/><path d="M19 18v1a2 2 0 01-2 2h-3"/></svg>',
		'phone'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg>',
		'pin'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s7-6.5 7-12a7 7 0 10-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>',
		'clock'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
		'mail'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>',
		'check'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12l5 5L20 7"/></svg>',
		'zalo'     => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 2 5.9 2 10.7c0 2.6 1.3 4.9 3.4 6.5L4.5 21l4.2-2.1c1 .3 2.2.5 3.3.5 5.5 0 10-3.9 10-8.7S17.5 2 12 2zm-4 11.5H5.5v-1.1l2-2.9H5.6v-1h2.5v1.1l-2 2.9H8v1zm3 0H9.8V8.5H11v5zm4.6 0h-1.1v-.4c-.3.3-.7.5-1.2.5-1.1 0-1.9-.9-1.9-2s.8-2 1.9-2c.5 0 .9.2 1.2.5v-.4h1.1v3.8zm3.9 0h-1.1v-.4c-.3.3-.7.5-1.2.5-1.1 0-1.9-.9-1.9-2s.8-2 1.9-2c.5 0 .9.2 1.2.5v-.4h1.1v3.8z"/></svg>',
	);
	return isset( $icons[ $name ] ) ? '<span class="icon icon--' . esc_attr( $name ) . '">' . $icons[ $name ] . '</span>' : '';
}

/** Số bài viết trên trang tin tức. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_home() || $q->is_category() ) ) {
		$q->set( 'posts_per_page', 9 );
	}
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'vw_car' ) ) {
		$q->set( 'posts_per_page', -1 );
		$q->set( 'orderby', 'menu_order title' );
		$q->set( 'order', 'ASC' );
	}
} );
