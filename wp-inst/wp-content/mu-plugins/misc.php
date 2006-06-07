<?php
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
	
	$dirName = ABSPATH."wp-content/blogs.dir/" . $wpdb->blogid . "/files/";
	$size = get_dirsize($dirName) / 1024 / 1024;
	
	if( ($spaceAllowed-$size) < 0 ) { 
		return "Sorry, you have used your space allocation. Please delete some files to upload more files."; //No space left
	} else {
		return false;
	}
}
add_filter( "pre_upload_error", "upload_is_user_over_quota" );

function upload_is_file_too_big( $ret ) {
	if( $_FILES[ 'image' ][ 'size' ] > ( 1024 * get_site_option( 'fileupload_maxk', 1500 ) ) )
		$ret = "This file is too big. Files must be less than " . get_site_option( 'fileupload_maxk', 1500 ) . "Kb in size.<br />";

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

function update_pages_last_updated( $post_id ) {
	global $wpdb;
	$post_id = intval( $post_id );
	if( $wpdb->get_var( "SELECT post_type FROM {$wpdb->posts} WHERE post_status = 'publish' and ID = '$post_id'" ) == 'page' )
		update_option( "pages_last_updated", time() );
}
add_action( "save_post", "update_pages_last_updated" );
add_action( "comment_post", "update_pages_last_updated" );
add_action( "publish_post", "update_pages_last_updated" );
add_action('delete_post', 'update_pages_last_updated');
add_action('delete_comment', 'update_pages_last_updated');
add_action('private_to_published', 'update_pages_last_updated');
add_action('trackback_post', 'update_pages_last_updated');
add_action('wp_set_comment_status', 'update_pages_last_updated');


?>
