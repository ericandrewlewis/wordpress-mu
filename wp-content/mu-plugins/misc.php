<?php

if ( !function_exists('graceful_fail') ) :
function graceful_fail( $message ) {
	die('
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Error!</title>
<style type="text/css">
img {
	border: 0;
}
body {
line-height: 1.6em; font-family: Georgia, serif; width: 390px; margin: auto;
text-align: center;
}
.message {
	font-size: 22px;
	width: 350px;
	margin: auto;
}
</style>
</head>
<body>
<p class="message">' . $message . '</p>
</body>
</html>
	');
}
endif;

function fix_upload_details( $uploads ) {
	$uploads[ 'url' ] = str_replace( UPLOADS, "files", $uploads[ 'url' ] );
	return $uploads;
}
add_filter( "upload_dir", "fix_upload_details" );

function get_dirsize($directory) {
	$size = 0;
	if(substr($directory,-1) == '/') $directory = substr($directory,0,-1);
	if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) return false;
	if($handle = opendir($directory)) {
		while(($file = readdir($handle)) !== false) {
			$path = $directory.'/'.$file;
			if($file != '.' && $file != '..') {
				if(is_file($path)) {
					$size += filesize($path);
				} elseif(is_dir($path)) {
					$handlesize = get_dirsize($path);
					if($handlesize >= 0) {
						$size += $handlesize;
					} else {
						return false;
					}
				}
			}
		}
		closedir($handle);
	}
	return $size;
}

function upload_is_user_over_quota( $ret ) {
	global $wpdb;
	
	// Default space allowed is 10 MB 
	$spaceAllowed = get_site_option("blog_upload_space");
	if(empty($spaceAllowed) || !is_numeric($spaceAllowed)) $spaceAllowed = 10;
	
	$dirName = constant( "ABSPATH" ) . constant( "UPLOADS" );
	$size = get_dirsize($dirName) / 1024 / 1024;
	
	if( ($spaceAllowed-$size) < 0 ) { 
		return __("Sorry, you have used your space allocation. Please delete some files to upload more files."); //No space left
	} else {
		return false;
	}
}
add_filter( "pre_upload_error", "upload_is_user_over_quota" );

// Use wporg wp_upload_dir() filter
function filter_upload_dir_size( $uploads ) { 
	if( upload_is_user_over_quota( 1 ) ) {
		$uploads[ 'error' ] = __('Sorry, you have used your upload quota.');
	}

	return $uploads;
}
add_filter( 'upload_dir', 'filter_upload_dir_size' );

function upload_is_file_too_big( $ret ) {
	if( $_FILES[ 'image' ][ 'size' ] > ( 1024 * get_site_option( 'fileupload_maxk', 1500 ) ) ) {
		$file_maxk = get_site_option( 'fileupload_maxk', 1500 );
		$ret = sprintf(__('This file is too big. Files must be less than %1$s Kb in size.<br />'), $file_maxk);
	}
	return $ret;
}
add_filter( "check_uploaded_file", "upload_is_file_too_big" );

function check_upload_mimes($mimes) {
	$site_exts = explode( " ", get_site_option( "upload_filetypes" ) );
	foreach ( $site_exts as $ext )
		foreach ( $mimes as $ext_pattern => $mime )
			if ( preg_match("/$ext_pattern/", $ext) )
				$site_mimes[$ext_pattern] = $mime;
	return $site_mimes;
}
add_filter('upload_mimes', 'check_upload_mimes');

add_filter('the_title', 'wp_filter_kses');
function update_posts_count( $post_id ) {
	global $wpdb;
	$post_id = intval( $post_id );
	$c = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->posts} WHERE post_status = 'publish' and post_type='post'" );
	update_option( "post_count", $c );
}
add_action( "publish_post", "update_posts_count" );

function wpmu_log_new_registrations( $blog_id, $user_id ) {
	global $wpdb;
	$user = new WP_User($user_id);
	$email = $wpdb->escape($user->user_email);
	$IP = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR'] );
	$wpdb->query( "INSERT INTO {$wpdb->registration_log} ( email , IP , blog_id, date_registered ) VALUES ( '{$email}', '{$IP}', '{$blog_id}', NOW( ))" );
}

add_action( "wpmu_new_blog" ,"wpmu_log_new_registrations", 10, 2 );

function scriptaculous_admin_loader() {
	        wp_enqueue_script('scriptaculous');
}
add_action( 'admin_print_scripts', 'scriptaculous_admin_loader' );

function fix_import_form_size( $size ) {
	if( upload_is_user_over_quota() == false )
		return 0;
	$dirName = constant( "ABSPATH" ) . constant( "UPLOADS" );
	$dirsize = get_dirsize($dirName) / 1024;
	if( $size > $dirsize ) {
		return $dirsize;
	} else {
		return $size;
	}
}
add_filter( 'import_upload_size_limit', 'fix_import_form_size' );

?>
