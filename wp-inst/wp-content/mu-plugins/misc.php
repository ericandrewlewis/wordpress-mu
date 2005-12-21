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
	$type = substr( $_FILES[ 'image' ][ 'name' ], 1+strpos( $_FILES[ 'image' ][ 'name' ], '.' ) );
	$allowed_types = split( " ", get_site_option( "upload_filetypes" ) );
	if( in_array( $type, $allowed_types ) == false ) {
		$ret = "You cannot upload files of this type.<br />";
	} elseif( $_FILES[ 'image' ][ 'size' ] > ( 1024 * get_site_option( 'fileupload_maxk', 1500 ) ) ) {
		$ret = "This file is too big. Files must be less than " . get_site_option( 'fileupload_maxk', 1500 ) . "Kb in size.<br />";
	}

	return $ret;
}
add_filter( "check_uploaded_file", "upload_is_file_too_big" );

add_filter('the_title', 'wp_filter_kses');
?>
