<?php

/* $Id: go.php,v 1.3 2005/03/10 09:02:49 donncha Exp $ */

require('./wp-blog-header.php');
// IMPORTANT :
// if you don't put this file in your blog root (where your index.php is)
// then modify the path to wp-blog-header.php above

header('Expires: Mon, 23 Mar 1972 07:00:00 GMT'); header('Cache-Control: no-cache, must-revalidate'); header('Pragma: no-cache');

if ($_SERVER["QUERY_STRING"]) {
	$url = $_SERVER["QUERY_STRING"];
	$url = wp_ozh_click_getrealpath("$url");
	if( $_SERVER["HTTP_USER_AGENT"] != "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)" && $_SERVER["HTTP_USER_AGENT"] != "Googlebot/2.1 (+http://www.google.com/bot.html)" )
	{
	    $test= wp_ozh_click_increment($url);
	}
	
	
	if ($is_IIS) {
		header("Refresh: 0;url=$url");
	} else {
	    if( $_SERVER["HTTP_USER_AGENT"] != "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)" )
	    {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
	    }
	    print "<html><head><title>Redirecting to $url</title></head><body>Redirecting to <a href='$url'>$url</a></body></html>";
	    exit;
	}

} else {
	echo "Hmmm ? ";
	echo "<a href=\"" . get_settings('siteurl') . "\">Back to Blog</a> !";
}
?>
