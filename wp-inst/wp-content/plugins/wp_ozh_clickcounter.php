<?php
/*
Plugin Name: Click Counter
Plugin URI: http://frenchfragfactory.net/ozh/archives/2004/09/17/click-counter-plugin-for-wordpress/
Description: Adds a click counter to links in your posts (<a href="../wp-content/plugins/wp_ozh_clickcounter.php">quick readme & manual</a>)
Version: 1.0
Author: Ozh
Author URI: http://planetOzh.com
*/
// script called directly (or something global badly misconfigured :)
if (!function_exists("get_settings")) {wp_ozh_click_readme();die;}


/*************************************
 *      OPTIONAL EDIT BELOW          *
 *               ~~                  *
 *************************************/

// when specified "default value" it means it can be overridden on a per-link basis in each post 

// Core variables

$wp_ozh_click['table'] = 'wp_linkclicks';
		/* name of the table where stats will be stored. Look at the bottom or at plugin page to learn how to create this table		 */
$wp_ozh_click['file'] = get_settings('siteurl') . "/go.php" ;
		/* name of the click counter php file (provided with the plugin archive). A good place for it is your blog root.		 */


// Basic features
		
$wp_ozh_click['do_posts'] = 1;
		/* 0 or 1, or true / false
		 * Add or not a link counter to links in your posts		 */

$wp_ozh_click['do_comments'] = 1;
		/* 0 or 1, or true / false
		 * Add or not a link counter to links in comments		 */
		 
		/* There is a quick editing needed if you also want to add a counter
		 * to commenters' website when specified, see plugin page. */

$wp_ozh_click['track_all_links'] = 1 ;
		/* 0 : adds a counter only when you add count="value" in your links html tag
		 * 1 : adds a counter to all external links in your posts (no need to add counter="1" if you plan to track them all)
		 * To keep track of internal links if set to 1, put absolute path (http://yourblog/link) instead of relative (/link) 		 */

$wp_ozh_click['in_title'] = 0 ;
		/* 1 or 0 (or true / false)
		 * add number of hits in link title tag : <a href="http://site.com" title="X hits">site</a>		 */

$wp_ozh_click['in_plain'] = 0 ;
		/* 1 or 0 (or true / false)
		 * add number of hits in plain text : <a href="http://site.com">site</a> <span class="hitcounter">(XX hits)</span>		 */

$wp_ozh_click['0click'] = 'No Click';
		/* default text for zero clicks.		 */

$wp_ozh_click['1click'] = 'One hit';
		/* default text for one click		 */
		
$wp_ozh_click['clicks'] = '%% hits';
		/* default text for several clicks, where %% will be replaced by a number		 */
		
$wp_ozh_click['method'] = 2 ;
		/* 1, 2 or 3 
		 * There are 3 link 'href' modification modes available
		 * Each has advantages and drawbacks
		 * All validate any Doctype up to xhtml 1.1
		 *
		 * From input <a href="http://site.com">, each methods gives the following html :
		 *
		 * Method 1 :
   	 * ^^^^^^^^^^
   	 * <a href="http://site.com" onclick="window.location='/go.php?http://site.com'; return false">
   	 * Cool : status bar shows real link without further trick.
   	 * Less cool : doesnt work with "open link in new window"
   	 *
   	 * Method 2 :
   	 * ^^^^^^^^^^
   	 * <a href="/yourblog/go.php?http://site.com">
   	 * Cool : works with "open in new window" and doesn't require Javascript enabled
   	 * Less cool : shows ugly link "/blog/go.php?http://site.com" in status bar
   	 *
   	 * Method 3 :
   	 * ^^^^^^^^^^
   	 * like method 2 but also modify status bar to hide the "yoursite.com/blog/go.php?" part
   	 * with an onmouseover="javascript:window.status='http://site.com'; return false"
   	 * Cool : status bar shows real link.
   	 * Less cool : adds a few bytes of html. But who cares :)
   	 *
   	 * I'd suggest you use preferably method 3, or at least method 2. Method 1 is really less
   	 * accurate since it doesn't keep track of links opened in a new window		 */


// Link title features
// The plugin is able to retrieve a remote file title (from it's <title> html tag)

$wp_ozh_click['get_title'] = 0;
		/* 1 or 0 (or true / false)
		 * Get remote page title the first time a user clicks a link to store it along with hits in the table
		 * Will slow down a bit the first clicker (1 or 2 seconds, time for your website to retrieve the distant page)
		 * Titles stored are used for example when printing top clicked links
		 * !! Note : uses fopen(), check your host has enabled this !!           */

$wp_ozh_click['get_title_forcerefresh'] = 50;
		/* Refresh remote page title every XX clicks ?
		 * Set to 0 if you don't want to check & refresh titles every XX clicks
		 * (the higher traffic - then clicks - you get, the higher you should set this
		 * To be honest this is really a gadget - almost totally useless :)
		 * Examples : 50 for Joe's blog, 3000 for Slashdottish blog          */

$wp_ozh_click['extensions'] = array (
		"ace", "arj", "bin", "bz2", "dat", "deb", "gz", "hqx", "pak", "pk3", "rar", "rpm", "sea", "sit", "tar", "wsz", "zip",
		"aif", "aiff", "au", "mid", "mod", "mp3", "ogg", "ram", "rm", "wav",
		"ani", "bmp", "dwg", "eps", "eps2", "gif", "ico", "jpeg", "jpg", "png", "psd", "psp", "qt", "svg", "swf", "tga", "tiff", "wmf", "xcf",
		"avi", "mov", "mpeg", "mpg",
		"c", "class", "h", "java ", "jar", "js",
		"bat", "chm", "cur", "dll", "exe", "hlp", "inf", "ocx", "pps", "ppt", "reg", "scr", "xls",
		"css", "conf", "doc", "ini", "pdf", "rtf", "ttf", "txt"
);		/* Most common non html file extensions
		 * These are files that have no <title> html tag, so their link title will be $document.$ext		 */

// Top links function features

$wp_ozh_click['top_limit'] = 5;
		/* default number of top links to be displayed by wp_ozh_click_topclicks()		 */

$wp_ozh_click['top_pattern'] = '<li><a href="%%link_url%%" title="%%link_title%%">%%link_title_trim%%</a>: %%link_clicks%%</li>';
		/* default pattern used to display top links
       * Any %%tag%% where "tag" can be : link_id, link_url, link_clicks, link_date, link_title, link_title_trim (shortened, see below)
		 * Example : '%%link_title%% (%%link_url%%) = %%link_clicks%%'		 */

$wp_ozh_click['trim'] = 15;
		/* default maximum length of link titles
		 * When printing top links titles, trim long link titles output to XX characters (0 not to trim)		 */




/*************************************
 *        DO NOT EDIT BELOW          *
 *               ~~                  *
 *************************************/

//**************************************************************************************************************************


// inputs a URL, returns an integer (number of clicks for the URL)
function wp_ozh_click_getcount2 ($wpblog, $url = "") {
	global $wpdb, $wp_ozh_click;
	$url = str_replace("&amp;", "&", $url);
        $url = wp_ozh_click_getrealpath($url);
	return $wpdb->get_var("SELECT link_clicks FROM $wp_ozh_click[table] WHERE blogID='$wpblog' AND link_url='$url'");
}

function wp_ozh_click_getcount ($url = "") {
    global $wpblog;

    if( @include_once( "Cache/Function.php" ) )
    {
        $cache = new Cache_Function( 'file', array('cache_dir' => ABSPATH . "/wp-content/smarty-cache", 'filename_prefix' => 'wp_ozh_click_getcount_cache_' ), 600 ); 
	$count = $cache->call( "wp_ozh_click_getcount2", $wpblog, $url );
    }
    else
    {
	$count = wp_ozh_click_getcount2( $wpblog, $url );
    }

    return $count;
}

// inputs a URL, returns text
function wp_ozh_click_getclicks ($url = '', $zeroclick = '',
                                 $oneclick = '', $lotsaclicks = '' ) {
	$result = wp_ozh_click_getcount ($url);
	$result = wp_ozh_click_labelize ($result, $zeroclick, $oneclick, $lotsaclicks);
	$wp_ozh_click['temp'] = "...".$url;
	return $result;
}

// inputs a number, returns text like "<number> hits"
function wp_ozh_click_labelize ($number = 0, $zeroclick = '',
                                 $oneclick = '', $lotsaclicks = '' ) {
	global $wp_ozh_click;
	if (!$zeroclick) $zeroclick = $wp_ozh_click['0click'];
	if (!$oneclick)  $oneclick = $wp_ozh_click['1click'];
	if (!$lotsaclicks) $lotsaclicks = $wp_ozh_click['clicks'];
	
	switch ($number) :
	case "":
		return $zeroclick;
		break;
	case 1:
		return $oneclick;
		break;
	default:
		return (str_replace ("%%", $number, $lotsaclicks));
	endswitch;
}


// parses string to detect and process pairs of tag="value"
function wp_ozh_click_parse ($html="", $all=0) {
	global $wp_ozh_click;
	
	preg_match_all ('/[^=]{1,}="[^"]+"/', $html, $wp_ozh_click['link']);
	foreach ($wp_ozh_click['link'][0] as $pair) {
	     list ($tag , $value) = explode ("=", $pair , 2);
	     $wp_ozh_click['link'][trim($tag)]=trim($value, '"');
   }
   unset ($wp_ozh_click['link'][0]);
   
   $wp_ozh_click['modify_href'] = 0;
   // do we want to display clicks ?
   if ( !isset($wp_ozh_click['link']['count']) || $wp_ozh_click['link']['count'] != "0" ) {
	   if (
	   ( ($all == 1) && (eregi("^[a-z]+://", $wp_ozh_click['link']['href'])) )
	   ||
	   ( isset($wp_ozh_click['link']['count'] ) )
	   ) {
	   	$wp_ozh_click['modify_href'] = 1;
	   }
	}

   if ($wp_ozh_click['modify_href']) {
   	if ( (!isset($wp_ozh_click['link']['count']) && $wp_ozh_click['track_all_links'] && $wp_ozh_click['in_title'] ) || ( $wp_ozh_click['in_title'] && $wp_ozh_click['link']['count']=="1" ) || stristr($wp_ozh_click['link']['count'],'title') ) {
			if (isset($wp_ozh_click['link']['title'])) {
				$wp_ozh_click['link']['title']= $wp_ozh_click['link']['title'] . " (" . wp_ozh_click_getclicks($wp_ozh_click['link']['href']) . ")";
			} else {
				$wp_ozh_click['link']['title']= "(" . wp_ozh_click_getclicks($wp_ozh_click['link']['href']) . ")";
			}
		}
		if ( (!isset($wp_ozh_click['link']['count']) && $wp_ozh_click['track_all_links'] && $wp_ozh_click['in_plain'] ) || ( $wp_ozh_click['in_plain'] && $wp_ozh_click['link']['count']=="1" ) || stristr($wp_ozh_click['link']['count'],'inline') ) {
			$wp_ozh_click['after'] = ' <span class="hitcounter">(' . wp_ozh_click_getclicks($wp_ozh_click['link']['href']) . ')</span>' ;
		}
		
		switch ($wp_ozh_click['method']) :
		case 1 :
			$wp_ozh_click['link']['onclick'] = "window.location='". $wp_ozh_click['file'] . "?" . $wp_ozh_click['link']['href'] . "'; return false";
			break;
		case 2 :
			$wp_ozh_click['link']['href'] = $wp_ozh_click['file'] . "?" . $wp_ozh_click['link']['href'] ;
			break;
		case 3 :
   		$wp_ozh_click['link']['onmouseover']="javascript:window.status='". $wp_ozh_click['link']['href'] ."'; return true;" ;
   		$wp_ozh_click['link']['onmouseout']="javascript:window.status=''; return true;" ;
			$wp_ozh_click['link']['href'] = $wp_ozh_click['file'] . "?" . $wp_ozh_click['link']['href'] ;
		endswitch;

		
		unset ($wp_ozh_click['link']['count']);
   } 
   
	$html='';
	foreach ($wp_ozh_click['link'] as $key => $value) {
	     $html .= $key . "=\"" . $value . "\" ";
   }
   $html=trim($html);
   return '<a '. $html .'>';
}

// convert relative path ("/blog/dir/file" or "dir/this/file") into absolute (from blog's index.php)
function wp_ozh_click_getrealpath ($url = "") {
	$url = preg_replace ("/#.*$/",'',$url);

	if (!eregi("^[a-z]+://", $url)) {
		if (eregi("^/", $url)) {
			$url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
		} else {
			$url = get_settings('siteurl') . '/' . $url;
		}
	}
	return $url;
}


// increments field link_click for a given URL
function wp_ozh_click_increment ($url="") {
	global $wpdb, $wp_ozh_click, $wpblog;
	$url = wp_ozh_click_getrealpath($url);

	// if (!get_magic_quotes_gpc()) {$url = add_magic_quotes($url);}

	$result = $wpdb->get_var("SELECT link_clicks FROM $wp_ozh_click[table] WHERE blogID='$wpblog' AND link_url='$url'");
	
	if ($result) {
		$todo = 'link_clicks=(link_clicks + 1)';
		if (($wp_ozh_click['get_title_forcerefresh']) && (($result % $wp_ozh_click['get_title_forcerefresh']) == 0)) {
			$link_title=wp_ozh_click_gettitle($url);
			if ($link_title) {
				$todo .= ", link_title='$link_title'";
			}
		}
		$return = $wpdb->query("UPDATE $wp_ozh_click[table] SET $todo WHERE blogID='$wpblog' AND link_url='$url'");
	} else {
		$link_date = gmdate('Y-m-d H:i:s', (time() + (get_settings('gmt_offset') * 3600)));
		if ($wp_ozh_click['get_title']) {
			$link_title=wp_ozh_click_gettitle($url);
		} else {
			$link_title='';
		}
		$return = $wpdb->query("INSERT INTO $wp_ozh_click[table] (blogID, link_url, link_clicks, link_date, link_title) VALUES ('$wpblog', '$url', 1, '$link_date', '$link_title')");
	};
	return $return;
}


// prints most clicked links
function wp_ozh_click_topclicks ($limit = '', $trim = '', $pattern = '') {
	global $wpdb, $wp_ozh_click, $wpblog;
	if (!$limit) $limit = $wp_ozh_click['top_limit'];
	if (!$pattern) $pattern = $wp_ozh_click['top_pattern'];
	if (!$trim) $trim = $wp_ozh_click['trim'];
	
	$results = $wpdb->get_results("select * from $wp_ozh_click[table] WHERE blogID='$wpblog' ORDER BY link_clicks DESC LIMIT $limit");
	foreach ($results as $result) {
		$html = $pattern;
		$html = preg_replace ( "/%%link_url%%/i", $wp_ozh_click['file'] . "?" . "$result->link_url", $html);
		$html = preg_replace ( "/%%link_clicks%%/i", wp_ozh_click_labelize($result->link_clicks), $html);
		$html = preg_replace ( "/%%link_date%%/i", "$result->link_date", $html);
		if (!$result->link_title) {
			// prettyfies link_title for display : no "http://www." or trailing "/"
			$result->link_title = preg_replace("/((ht)*f*tp:\/\/)*(www\.)*/", "", $result->link_url);
			$result->link_title = preg_replace("/\/$/", "", $result->link_title);
		}
		if ($trim && (strlen($result->link_title) > $trim)) {
			$result->link_title_trim = substr($result->link_title, 0, $trim) . '&#8230';
		} else {
			$result->link_title_trim = $result->link_title;
		}
		$html = preg_replace ( "/%%link_title_trim%%/i", "$result->link_title_trim", $html);
		$html = preg_replace ( "/%%link_title%%/i", "$result->link_title", $html);
		echo $html . "\n";
	}
}


// prints number of links tracked
function wp_ozh_click_linkcount ($display=1) {
	global $wpdb, $wp_ozh_click;
	if (!$wp_ozh_click['stats']) wp_ozh_click_getstats();
	if ($display)
		echo $wp_ozh_click['stats']->linkcount;
	return $wp_ozh_click['stats']->linkcount;
}


// prints total number of clicks
function wp_ozh_click_clickcount ($display=1) {
	global $wpdb, $wp_ozh_click;
	if (!$wp_ozh_click['stats']) wp_ozh_click_getstats();
	if ($display)
		echo $wp_ozh_click['stats']->clickcount;
	return $wp_ozh_click['stats']->clickcount;
	
}


// retrieves various stats
function wp_ozh_click_getstats () {
	global $wpdb, $wp_ozh_click, $wpblog;
	$wp_ozh_click['stats'] = $wpdb->get_row("SELECT count(*) AS linkcount, sum(link_clicks) AS clickcount FROM $wp_ozh_click[table] WHERE blogID='$wpblog'");
	//echo $wp_ozh_click['stats']->clickcount;
	//echo "<hr>";
	//echo $wp_ozh_click['stats']->linkcount;
	return $wp_ozh_click['stats'];
}

// return title of a (local or remote) webpage
function wp_ozh_click_gettitle ($url = "") {
	global $wp_ozh_click;
	eregi("/([^#\?\/]+)\.([a-z0-9]+)$", $url, $file);
	$ext = $file[2];
	$file = $file[1];
	$in_array = in_array($ext, $wp_ozh_click['extensions']);
	switch ($in_array):
	case true:
		return "$file.$ext";
		break;
	case false:
		if (function_exists('fopen')) {
			$fp = @fopen ($url, 'r'); 
			if( $fp ) {
			    while (! feof ($fp)){ 
				$webpage .= fgets ($fp, 1024); 
				if (stristr($webpage, '<title>' )){ 
				    break; 
				} 
			    } 
			    if (eregi("<title>(.*)</title>", $webpage, $out)) { 
				return addslashes($out[1]);
			    } else { 
				return "";
			    }
			} else {
			    return "$url";
			}
		} else {
			return "";
		}
		break;
	endswitch;
}


// readme & check install
function wp_ozh_click_readme() {
	echo '<html><head>
	<title>Click Counter Plugin for Wordpress - By Ozh</title>
	<link rel="stylesheet" href="../../wp-admin/wp-admin.css" type="text/css" />
	</head>
	<body>
	<div id="wphead" style="height: 4.5em">
	<h1 align="right">Click Counter Plugin - By Ozh</h1>
	</div>
	<div class="wrap">
	<h2>Thanks :)</h2>
	<p>Thank you for installing this plugin !</p>
	<h2>About this plugin</h2>
	<p>This plugin adds a "tracker" to links in your posts and your comments, so that when someone clicks on them, the link\'s hit counter increments. The number of hits can also be displayed in a variety of flavours. You can choose to add a hit counter to all links (default) or only to chosen links</p>
	<h2>2 steps installation and usage "out of the box"</h2>
	<ol><li><p>First, create a new table in your WordPress MySQL database, named wp_linkclicks, using for example PHPMyAdmin with the following query :</p>
	<pre class="updated">
CREATE TABLE `wp_linkclicks` (
 `link_id` INT NOT NULL AUTO_INCREMENT ,
 `link_url` TEXT NOT NULL ,
 `link_clicks` INT NOT NULL ,
 `link_date` DATETIME NOT NULL ,
 `link_title` TEXT NOT NULL ,
 UNIQUE (
  `link_id` 
 )
);</pre></li>
	<li><p>Then put the plugin file in <strong><em>yourblog</em>/wp-content/plugins/</strong> and <strong>activate it</strong>
	from the <a href="../../wp-admin/plugins.php">admin interface</a>.</p></li></ol>
	<p>The plugin should now work silently without further working. Get back to blogging and posting link as usual :)</p>
	<h2>Optional Configuration</h2>
	<p>You will find detailed information about how to configure the plugin in its source itself:
	the section you can configure is well commented. You can do so from within the <a href="../../wp-admin/templates.php?file=wp-content/plugins/wp_ozh_clickcounter.php">admin interface</a> as well.
	<p>You can also find detailed information and examples of use
	at the <a href="http://frenchfragfactory.net/ozh/">plugin\'s homepage</a>.</p>
	<h2>Feedback & Disclaimer</h2>
	<p>I\'d appreciate your leaving a comment on the plugin page, to suggest any improvement, bug fix, or just to say if you like the plugin or not :)
	By the way, you\'ll find on <a href="http://frenchfragfactory.net/ozh/archives/category/wordpress/">my site</a> a few other plugins (<a href="http://frenchfragfactory.net/ozh/archives/2004/08/27/ip-to-nation-plugin/">IP to Nation plugin</a> in particular) you may find of interest.</p>
	<p>Any resemblance between this page and  a well-known admin interface is purely coincidental :-P</p>
	</div>
	<div id="footer"><p><a href="http://planetOzh.com/"><img src="http://frenchfragfactory.net/ozh/wp-images/btn_planetozh.png" border="0" alt="planetOzh.com" /></a><br />
	</div>
	</body></html>
	';
}

// the one that starts it all
function wp_ozh_click_modifyhrefs ($input) {
	$input = preg_replace_callback ("/<a ([^>]{1,})>(.+?<\/a>)/", "wp_ozh_click_do_posts", $input);
	// ** OMFG ** I finally understood what preg_replace_callback is ! :))
	return $input;
}

// callback function
function wp_ozh_click_do_posts($text) {
	global $wp_ozh_click;
	unset ($wp_ozh_click['after']);
	
	$before = wp_ozh_click_parse($text[1],$wp_ozh_click['track_all_links']);
	$text = $text[2];
	return $before.$text.$wp_ozh_click['after'];
}

function wp_ozh_click_comment_author_link() {
	global $comment;
	$url = apply_filters('comment_url', $comment->comment_author_url);
	$author = apply_filters('comment_author', $comment->comment_author);
	if (!$author) $author = 'Anonymous';

	if (empty($url)) :
		echo $author;
	else:
		echo wp_ozh_click_modifyhrefs("<a href=\"$url\" rel=\"external\">$author</a>");
	endif;
}


// Add per-post filtering:
if ($wp_ozh_click['do_posts'])
	add_filter('the_content', 'wp_ozh_click_modifyhrefs');
if ($wp_ozh_click['do_comments'])
	add_filter('comment_text', 'wp_ozh_click_modifyhrefs');


// And that's it.


/* Future enhancements ?
 	- handle malformed html tags like <a href=http://site.com count=1>site</a> (no "quotes")
*/


/*	Example post :
	Post this in your blog and watch how modifying the script configuration affects display of counters.

--8<-----8<-----[cut & paste]-
<strong>Tests with external links : href="http://external-link.com" </strong>
<a href="http://external-link.com" count="0">count="0"</a> never shows click count
<a href="http://external-link.com" count="1">count="1"</a> shows click count according to defaults as set in the plugin
<a href="http://external-link.com" count="title">count="title"</a> always shows a count in link title (mouseover)
<a href="http://external-link.com" count="inline">count="inline"</a> always shows a count next to title in plain text
<a href="http://external-link.com" count="inline title">count="inline title"</a> always shows both
<a href="http://external-link.com">count not specified</a> shows or not depending on defaults as set in the plugin (tracking all links or not)

<strong>Tests with internal links : href="/local_dir/file"</strong>
<a href="/local_dir/file" count="0">count="0"</a> never shows click count
<a href="/local_dir/file" count="1">count="1"</a> always shows click count, according to defaults
<a href="/local_dir/file" count="title">count="title"</a> always shows click count in link title
<a href="/local_dir/file" count="inline">count="inline"</a> always shows click count next to title in plain text
<a href="/local_dir/file" count="inline title">count="inline title"</a> always shows both
<a href="/local_dir/file">count not specified</a> never shows click count
--8<-----8<-----[cut & paste]-

*/

?>
