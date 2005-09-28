<?php
function fix_upload_details( $uploads ) {

	$uploads[ 'url' ] = str_replace( UPLOADS, "files", $uploads[ 'url' ] );
	return $uploads;
}
add_filter( "upload_dir", "fix_upload_details" );
?>
