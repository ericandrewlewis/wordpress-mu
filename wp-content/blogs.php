<?php
define( "BLOGDEFINITION", true );
require_once( "../wp-config.php" );

if ( !function_exists('wp_check_filetype') ) :
function wp_check_filetype($filename, $mimes = null) {
	// Accepted MIME types are set here as PCRE unless provided.
	$mimes = is_array($mimes) ? $mimes : array (
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'bmp' => 'image/bmp',
		'tif|tiff' => 'image/tiff',
		'ico' => 'image/x-icon',
		'asf|asx|wax|wmv|wmx' => 'video/asf',
		'avi' => 'video/avi',
		'mov|qt' => 'video/quicktime',
		'mpeg|mpg|mpe' => 'video/mpeg',
		'txt|c|cc|h' => 'text/plain',
		'rtx' => 'text/richtext',
		'css' => 'text/css',
		'htm|html' => 'text/html',
		'mp3|mp4' => 'audio/mpeg',
		'ra|ram' => 'audio/x-realaudio',
		'wav' => 'audio/wav',
		'ogg' => 'audio/ogg',
		'mid|midi' => 'audio/midi',
		'wma' => 'audio/wma',
		'rtf' => 'application/rtf',
		'js' => 'application/javascript',
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
		'wri' => 'application/vnd.ms-write',
		'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
		'mdb' => 'application/vnd.ms-access',
		'mpp' => 'application/vnd.ms-project',
		'swf' => 'application/x-shockwave-flash',
		'class' => 'application/java',
		'tar' => 'application/x-tar',
		'zip' => 'application/zip',
		'gz|gzip' => 'application/x-gzip',
		'exe' => 'application/x-msdownload'
	);

	$type = false;
	$ext = false;

	foreach ($mimes as $ext_preg => $mime_match) {
		$ext_preg = '!\.(' . $ext_preg . ')$!i';
		if ( preg_match($ext_preg, $filename, $ext_matches) ) {
			$type = $mime_match;
			$ext = $ext_matches[1];
			break;
		}
	}

	return compact('ext', 'type');
}
endif;

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
	$mime = wp_check_filetype( $_SERVER[ 'REQUEST_URI' ] );
	if( $mime[ 'type' ] != false ) {
		$mimetype = $mime[ 'type' ];
	} else {
		$ext = substr( $_SERVER[ 'REQUEST_URI' ], strrpos( $_SERVER[ 'REQUEST_URI' ], '.' ) + 1 );
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
