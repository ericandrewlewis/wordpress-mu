<?php
function fix_upload_details( $uploads ) {

	$uploads[ 'url' ] = str_replace( UPLOADS, "files", $uploads[ 'url' ] );
	return $uploads;
}
add_filter( "upload_dir", "fix_upload_details" );

function upload_is_user_over_quota( $ret ) {
	global $wpdb;
	
	// Default space allowed is 10 MB 
	$spaceAllowed = get_site_option("blog_upload_space" );
	if( $spaceAllowed == false )
		$spaceAllowed = 10;
	
	$dirName = ABSPATH."wp-content/blogs.dir/" . $wpdb->blogid . "/files/";
	
  	$dir  = dir($dirName);
   	$size = 0;

	while($file = $dir->read()) {
		if ($file != '.' && $file != '..') {
			if (is_dir($file)) {
	           $size += dirsize($dirName . '/' . $file);
	       } else {
	           $size += filesize($dirName . '/' . $file);
	       }
	   }
	}
	$dir->close();
	$size = $size / 1024 / 1024;
	
	if( intval( $spaceAllowed ) < intval( $size ) ) {
		// No space left
		$ret = "You don't have any more space. Delete some files to upload more.";
	}
	return $ret;
}
add_filter( "pre_upload_error", "upload_is_user_over_quota" );
add_filter( "check_uploaded_file", "upload_is_user_over_quota" );

function upload_is_file_too_big( $ret ) {
	if( $_FILES[ 'image' ][ 'size' ] > get_site_option( 'fileupload_maxk', 1500 ) )
		$ret = "This file is too big. Files must be less than " . get_site_option( 'fileupload_maxk', 1500 ) . "Kb in size.";

	return $ret;
}
add_filter( "check_uploaded_file", "upload_is_file_too_big" );
?>
