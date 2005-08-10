<?php
/*
add_action('wp_footer', "blogs_header" );

function blogs_header() {
	global $current_site;
	print "<div style='position: absolute; top: 0px; width: 100%; background: #eee; border-bottom: 1px solid #333;'><table width='100%' border=0 cellspacing=0 cellpadding=0><td align='left'><a href='http://www.wordpress.com'>WordPress.com</a></td><td align='right'><a href='http://" . $current_site->domain . $current_site->path . "wp-newblog.php'>Get Your Own Blog</a></td></table></div>";
}
*/
function x_headers() {
	global $current_site, $current_blog, $WPMU_date;

	print "<meta name='X-totalblogs' content='" . get_blog_count() . "' />\n";
	print "<meta name='X-rootblog' content='http://" . $current_site->domain . $current_site->path. "' />\n";
	print "<meta name='X-created-on' content='" . $current_blog->registered . "' />\n";
	if( empty( $WPMU_date ) == false ) 
		print "<meta name='X-wpmu-date' content='" . $WPMU_date . "' />\n";


}
add_action('wp_head', "x_headers" );


?>
