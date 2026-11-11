<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kéo mã nguồn từ GitHub (repo public) và cập nhật theme + plugin.
 * URL zip: https://codeload.github.com/{owner}/{repo}/zip/refs/heads/{branch}
 */
function vwd_github_zip_url( $repo = '', $branch = '' ) {
	$repo   = $repo ? $repo : get_option( 'vwd_github_repo', VWD_GITHUB_REPO );
	$branch = $branch ? $branch : get_option( 'vwd_github_branch', VWD_GITHUB_BRANCH );
	return sprintf( 'https://codeload.github.com/%s/zip/refs/heads/%s', $repo, $branch );
}

/**
 * @return array|WP_Error  ['theme'=>bool,'plugin'=>bool,'commit'=>string]
 */
function vwd_github_pull( $repo = '', $branch = '' ) {
	set_time_limit( 300 );
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;

	$url = vwd_github_zip_url( $repo, $branch );
	$tmp = download_url( $url, 120 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}

	$work = trailingslashit( get_temp_dir() ) . 'vwd-' . wp_generate_password( 8, false );
	wp_mkdir_p( $work );
	$r = unzip_file( $tmp, $work );
	@unlink( $tmp );
	if ( is_wp_error( $r ) ) {
		$wp_filesystem->delete( $work, true );
		return $r;
	}

	// Thư mục gốc trong zip: {repo}-{branch}/
	$dirs = glob( $work . '/*', GLOB_ONLYDIR );
	if ( empty( $dirs ) ) {
		$wp_filesystem->delete( $work, true );
		return new WP_Error( 'vwd_zip', 'Zip không có thư mục gốc.' );
	}
	$root   = $dirs[0];
	$result = array( 'theme' => false, 'plugin' => false );

	$copy = function ( $from, $to ) use ( $wp_filesystem ) {
		if ( ! is_dir( $from ) ) {
			return false;
		}
		if ( is_dir( $to ) ) {
			$wp_filesystem->delete( $to, true );
		}
		wp_mkdir_p( $to );
		$err = copy_dir( $from, $to );
		return ! is_wp_error( $err );
	};

	$result['theme']  = $copy( $root . '/wp-content/themes/vw-dealer', get_theme_root() . '/vw-dealer' );
	$result['plugin'] = $copy( $root . '/wp-content/plugins/vw-dealer-core', WP_PLUGIN_DIR . '/vw-dealer-core' );

	$wp_filesystem->delete( $work, true );

	if ( $result['theme'] && 'vw-dealer' !== get_stylesheet() ) {
		switch_theme( 'vw-dealer' );
	}
	update_option( 'vwd_last_pull', current_time( 'mysql' ) );
	return $result;
}
