<?php
define( "BLOGDEFINITION", true );
require_once( "../wp-config.php" );

// Referrer protection
if( $_SERVER["HTTP_REFERER"] ) {
	if( strpos( $_SERVER["HTTP_REFERER"], $current_blog->domain ) == false ) {
		// do something against hot linking sites!
	}
}
$file = $_GET[ 'file' ];
$file = ABSPATH . "wp-content/blogs.dir/" . $blog_id . '/files/' . $file;

if( is_file( $file ) ) {
	$etag = md5( $file . filemtime( $file ) );
	$lastModified = date( "D, j M Y H:i:s ", filemtime( $file ) ) . "GMT";
	#$headers = apache_request_headers();
	// get mime type
	$ext = substr( $_SERVER[ 'REQUEST_URI' ], strrpos( $_SERVER[ 'REQUEST_URI' ], '.' ) + 1 );
	$ext_list = array( "jpg" => "image/jpeg", "mp3" => "audio/mpeg", "mov" => "video/quicktime" );
	if( $ext_list[ $ext ] ) {
		$mimetype = $ext_list[ $ext ];
	} else {
		$mimetype = "image/$ext";
	}

	// from http://blog.rd2inc.com/archives/2005/03/24/making-dynamic-php-pages-cacheable/
	if( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] == '"' . $etag . '"' || $lastModified == $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
		// They already have an up to date copy so tell them 
		header('HTTP/1.1 304 Not Modified'); 
		header('Cache-Control: private'); 
		header('Content-Type: $mimetype'); 
		header('ETag: "'.$etag.'"'); 
	} else {
		header("Content-type: $mimetype" );
		header( "Last-Modified: " . $lastModified );
		header( 'Accept-Ranges: bytes' );
		header( "Content-Length: " . filesize( $file ) );
		header( 'ETag: "' . $etag . '"' );
		readfile( $file );
	}
} else {
	// 404
	header("HTTP/1.1 404 Not Found");
	print "<html><head><title>Error 404! File Not Found!</title></head>";
	print "<body>";
	print "<h1>File Not Found!</h1>";
	print "</body></html>";
}
?>
