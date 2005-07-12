<?php
$HTTP_HOST = getenv('HTTP_HOST');  /* domain name */
$REMOTE_ADDR = getenv('REMOTE_ADDR'); /* visitor's IP */
$HTTP_USER_AGENT = getenv('HTTP_USER_AGENT'); /* visitor's browser */

// Fix for IIS, which doesn't set REQUEST_URI
if (! isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	
	// Append the query string if it exists and isn't null
	if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

if ( !(phpversion() >= '4.1') )
	die( 'Your server is running PHP version ' . phpversion() . ' but WordPress requires at least 4.1' );

if ( !extension_loaded('mysql') )
	die( 'Your PHP installation appears to be missing the MySQL which is required for WordPress.' );

function timer_start() {
	global $timestart;
	$mtime = explode(' ', microtime() );
	$mtime = $mtime[1] + $mtime[0];
	$timestart = $mtime;
	return true;
}
timer_start();

// Change to E_ALL for development/debugging
error_reporting(E_ALL ^ E_NOTICE);

// For an advanced caching plugin to use, static because you would only want one
if ( defined('WP_CACHE') )
	require (ABSPATH . 'wp-content/advanced-cache.php');

define('WPINC', 'wp-includes');
require_once (ABSPATH . WPINC . '/wp-db.php');

$wpdb->blogs            = $table_prefix . 'blogs';
$wpdb->users            = $table_prefix . 'users';
$wpdb->usermeta         = $table_prefix . 'usermeta';
$wpdb->site             = $table_prefix . 'site';
$wpdb->sitemeta         = $table_prefix . 'sitemeta';

// find out what tables to use from $wpblog
$wpdb->hide_errors();

$domain = $_SERVER['HTTP_HOST'];

$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '/'");

$blog_id = $current_blog->blog_id;
$is_public = $current_blog->is_public;
if( $blog_id == false ) {
    // no blog found, are we installing? Check if the table exists.
    if ( defined('WP_INSTALLING') ) {
	$query = "SELECT blog_id FROM ".$wpdb->blogs." limit 0,1";
	$blog_id = $wpdb->get_var( $query );
	if( $blog_id == false ) {
	    // table doesn't exist. This is the first blog
	    $blog_id = 1;
	} else {
	    // table exists
	    // don't create record at this stage. we're obviously installing so it doesn't matter what the table vars below are like.
	    // default to using the "main" blog.
	    $blog_id = 1;
	}
    } else {
	die( "No Blog by that name on this system." );
    }
}

if( $is_public == 'no' ) {
    // need to put checks in here?
    // A hook?
    die( "This blog is private." );
}

$wpdb->show_errors();

$table_prefix = $table_prefix.$blog_id."_";

// Table names
$wpdb->siteid           = $site_id;
$wpdb->blogid           = $blog_id;
$wpdb->posts            = $table_prefix . 'posts';
$wpdb->categories       = $table_prefix . 'categories';
$wpdb->post2cat         = $table_prefix . 'post2cat';
$wpdb->comments         = $table_prefix . 'comments';
$wpdb->links            = $table_prefix . 'links';
$wpdb->linkcategories   = $table_prefix . 'linkcategories';
$wpdb->options          = $table_prefix . 'options';
$wpdb->postmeta         = $table_prefix . 'postmeta';
$wpdb->prefix           = $table_prefix;

if ( defined('CUSTOM_USER_TABLE') )
	$wpdb->users = CUSTOM_USER_TABLE;
if ( defined('CUSTOM_USER_META_TABLE') )
	$wpdb->usermeta = CUSTOM_USER_META_TABLE;

// We're going to need to keep this around for a few months even though we're not using it internally

$tableposts = $wpdb->posts;
$tableusers = $wpdb->users;
$tablecategories = $wpdb->categories;
$tablepost2cat = $wpdb->post2cat;
$tablecomments = $wpdb->comments;
$tablelinks = $wpdb->links;
$tablelinkcategories = $wpdb->linkcategories;
$tableoptions = $wpdb->options;
$tablepostmeta = $wpdb->postmeta;

require (ABSPATH . WPINC . '/functions.php');
require (ABSPATH . WPINC . '/default-filters.php');
require_once (ABSPATH . WPINC . '/wp-l10n.php');

$wpdb->hide_errors();
if ( !update_category_cache() && (!strstr($_SERVER['PHP_SELF'], 'install.php') && !defined('WP_INSTALLING')) ) {
	if ( strstr($_SERVER['PHP_SELF'], 'wp-admin') )
		$link = 'install.php';
	else
		$link = 'wp-admin/install.php';
	die(sprintf(__("It doesn't look like you've installed WP yet. Try running <a href='%s'>install.php</a>."), $link));
}
$wpdb->show_errors();

require (ABSPATH . WPINC . '/functions-formatting.php');
require (ABSPATH . WPINC . '/functions-post.php');
require (ABSPATH . WPINC . '/capabilities.php');
require (ABSPATH . WPINC . '/classes.php');
require (ABSPATH . WPINC . '/template-functions-general.php');
require (ABSPATH . WPINC . '/template-functions-links.php');
require (ABSPATH . WPINC . '/template-functions-author.php');
require (ABSPATH . WPINC . '/template-functions-post.php');
require (ABSPATH . WPINC . '/template-functions-category.php');
require (ABSPATH . WPINC . '/comment-functions.php');
require (ABSPATH . WPINC . '/feed-functions.php');
require (ABSPATH . WPINC . '/links.php');
require (ABSPATH . WPINC . '/kses.php');
require (ABSPATH . WPINC . '/version.php');

$is_archived = get_settings( "is_archived" );
if( $is_archived == 'yes' ) {
    die( "This blog has been archived or suspended temporarily. Please check back later." );
}

if (!strstr($_SERVER['PHP_SELF'], 'install.php') && !strstr($_SERVER['PHP_SELF'], 'wp-admin/import')) :
    // Used to guarantee unique hash cookies
    $cookiehash = md5(get_settings('siteurl')); // Remove in 1.4
	define('COOKIEHASH', $cookiehash); 
endif;

require (ABSPATH . WPINC . '/vars.php');

do_action('core_files_loaded');

// Check for hacks file if the option is enabled
if (get_settings('hack_file')) {
	if (file_exists(ABSPATH . '/my-hacks.php'))
		require(ABSPATH . '/my-hacks.php');
}

if ( get_settings('active_plugins') ) {
	$current_plugins = get_settings('active_plugins');
	if ( is_array($current_plugins) ) {
		foreach ($current_plugins as $plugin) {
			if ('' != $plugin && file_exists(ABSPATH . 'wp-content/plugins/' . $plugin))
				include_once(ABSPATH . 'wp-content/plugins/' . $plugin);
		}
	}
}

require (ABSPATH . WPINC . '/pluggable-functions.php');

if ( defined('WP_CACHE') && function_exists('wp_cache_postload') )
	wp_cache_postload();

do_action('plugins_loaded');

define('TEMPLATEPATH', get_template_directory());

// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(ABSPATH . WPINC . '/locale.php');

// If already slashed, strip.
if ( get_magic_quotes_gpc() ) {
	$_GET    = stripslashes_deep($_GET   );
	$_POST   = stripslashes_deep($_POST  );
	$_COOKIE = stripslashes_deep($_COOKIE);
	$_SERVER = stripslashes_deep($_SERVER);
}

// Escape with wpdb.
$_GET    = add_magic_quotes($_GET   );
$_POST   = add_magic_quotes($_POST  );
$_COOKIE = add_magic_quotes($_COOKIE);
$_SERVER = add_magic_quotes($_SERVER);

function shutdown_action_hook() {
	do_action('shutdown');
}
register_shutdown_function('shutdown_action_hook');

$wp_query = new WP_Query();
$wp_rewrite = new WP_Rewrite();
$wp = new WP();
$wp_roles = new WP_Roles();

// Everything is loaded and initialized.
do_action('init');
?>
