<?php
// Turn register globals off
function unregister_GLOBALS() {
	if ( !ini_get('register_globals') )
		return;

	if ( isset($_REQUEST['GLOBALS']) )
		die('GLOBALS overwrite attempt detected');

	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix');
	
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ( $input as $k => $v ) 
		if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) )
			unset($GLOBALS[$k]);
}

unregister_GLOBALS(); 

$HTTP_USER_AGENT = getenv('HTTP_USER_AGENT');
unset( $wp_filter, $cache_userdata, $cache_lastcommentmodified, $cache_lastpostdate, $cache_settings, $category_cache, $cache_categories );

if ( ! isset($blog_id) )
	$blog_id = 0;

// Fix for IIS, which doesn't set REQUEST_URI
if ( empty( $_SERVER['REQUEST_URI'] ) ) {
	$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME']; // Does this work under CGI?
	
	// Append the query string if it exists and isn't null
	if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
	$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

// Fix for Dreamhost and other PHP as CGI hosts
if ( strstr( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) )
	unset($_SERVER['PATH_INFO']);

// Fix empty PHP_SELF
$PHP_SELF = $_SERVER['PHP_SELF'];
if ( empty($PHP_SELF) )
	$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);

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
$wpdb->sitecategories	= $table_prefix . 'sitecategories';


// find out what tables to use from $wpblog
$wpdb->hide_errors();

$domain = addslashes($_SERVER['HTTP_HOST']);
if( substr( $domain, 0, 4 ) == 'www.' )
	$domain = substr( $domain, 4 );
$domain = preg_replace('/:.*$/', '', $domain); // Strip ports

function is_installed() {
    global $wpdb, $domain, $base;

    if( defined( "WP_INSTALLING" ) == false ) {
	    $check = $wpdb->get_results( "SELECT * FROM $wpdb->site" );
	    if( $check == false ) {
		    $msg = '<strong>Database Tables Missing.</strong><br /> The table <em>' . DB_NAME . '::' . $wpdb->site . '</em> is missing. Delete the .htaccess file and run the installer again!<br />';
	    } else {
		    $msg = '<strong>Could Not Find Blog!</strong><br />';
		    $msg .= "Searched for <em>" . $domain . $base . "</em> in " . DB_NAME . "::" . $wpdb->blogs . " table. Is that right?<br />";
	    }
	    $msg .= "Please check that your database contains the following tables:<ul>
		    <li> $wpdb->blogs </li>
		    <li> $wpdb->users </li>
		    <li> $wpdb->usermeta </li>
		    <li> $wpdb->site </li>
		    <li> $wpdb->sitemeta </li>
		    <li> $wpdb->sitecategories </li>
		    </ul>";
	    $msg .= "If you suspect a problem please report it to <a href='http://mu.wordpress.org/forums/'>support forums</a>!";
	    die( "<h1>Fatal Error</h1> " . $msg );
    }
}
if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
	$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$base'");
} else {
	if( $base == '/' ) {
		$wpblog = substr( $_SERVER[ 'REQUEST_URI' ], 1 );
	} else {
		$wpblog = str_replace( $base, '', $_SERVER[ 'REQUEST_URI' ] );
	}
	if( strpos( $wpblog, '/' ) )
		$wpblog = substr( $wpblog, 0, strpos( $wpblog, '/' ) );
	if( $wpblog == '' || file_exists( ABSPATH . $wpblog ) || is_dir( ABSPATH . $wpblog ) ) {
		$searchdomain = $domain;
	} else {
		$searchdomain = $wpblog . "." . $domain;
	}
	$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '{$searchdomain}' AND path = '$base'");
}
if( $current_blog == false ) {
    is_installed();
}
$blog_id   = $current_blog->blog_id;
$public = $current_blog->public;
$site_id   = $current_blog->site_id;
if( $site_id == 0 )
	$site_id = 1;

$current_site = $wpdb->get_row("SELECT * FROM $wpdb->site WHERE id='$site_id'");
if( $current_site == false ) {
    is_installed();
}
$current_site->site_name = $wpdb->get_var( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = '$site_id' AND meta_key = 'site_name'" );
if( $current_site->site_name == false ) {
	$current_site->site_name = ucfirst( $current_site->domain );
	include( ABSPATH . "wp-admin/wpmu-upgrade.inc.php" );
}

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
	$check = $wpdb->get_results( "SELECT * FROM $wpdb->site" );
	if( $check == false ) {
	    $msg = ': DB Tables Missing';
	} else {
	    $msg = '';
	}
	die( "No Blog by that name on this system." . $msg );
    }
}

if( '0' == $current_blog->public ) {
	// This just means the blog shouldn't show up in google, etc. Only to registered members
}

$wpdb->show_errors();

$table_prefix = $table_prefix . $blog_id . '_';

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

if ( file_exists(ABSPATH . 'wp-content/object-cache.php') )
	require (ABSPATH . 'wp-content/object-cache.php');
else
	require (ABSPATH . WPINC . '/cache.php');

// To disable persistant caching, add the below line to your wp-config.php file, uncommented of course.
// define('DISABLE_CACHE', true);

wp_cache_init();

if( defined( "BLOGDEFINITION" ) && constant( "BLOGDEFINITION" ) == true )
	return;

define( "UPLOADS", "wp-content/blogs.dir/{$wpdb->blogid}/files" );

require (ABSPATH . WPINC . '/functions.php');
require (ABSPATH . WPINC . '/default-filters.php');
require_once (ABSPATH . WPINC . '/wp-l10n.php');

$wpdb->hide_errors();
$db_check = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl'");
if ( !$db_check && (!strstr($_SERVER['PHP_SELF'], 'install.php') && !defined('WP_INSTALLING')) ) {
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

require_once( ABSPATH . WPINC . '/wpmu-functions.php' );

if( defined( "WP_INSTALLING" ) == false ) {
	$current_site->site_name = get_site_option('site_name');
}

if( $current_site->site_name == false ) {
	$current_site->site_name = ucfirst( $current_site->domain );
}

if( defined( "WP_INSTALLING" ) == false ) {
	$locale = get_option( "WPLANG" );
	if( $locale == false )
		$locale = get_site_option( "WPLANG" );
}

$wpdb->hide_errors();
$plugins = glob( ABSPATH . 'wp-content/mu-plugins/*.php' );
if( is_array( $plugins ) ) {
	foreach ( $plugins as $plugin ) {
		if( is_file( $plugin ) )
			include_once( $plugin );
	}
}
$wpdb->show_errors();

$is_archived = get_settings( "is_archived" );
if( $is_archived == 'yes' ) {
	update_archived( $wpdb->blogid, 1 );
	die( "This blog has been archived or suspended temporarily. Please check back later." );
}

if( $current_blog->archived == '1' ) {
    die( 'This blog has been archived or suspended.' );
}

if( $current_blog->spam == '1' ) {
    die( 'This blog has been archived or suspended.' );
}


if (!strstr($_SERVER['PHP_SELF'], 'install.php') && !strstr($_SERVER['PHP_SELF'], 'wp-admin/import')) :
    // Used to guarantee unique hash cookies
    $cookiehash = ''; // Remove in 1.4
	define('COOKIEHASH', ''); 
endif;

if ( !defined('USER_COOKIE') )
	define('USER_COOKIE', 'wordpressuser_'. COOKIEHASH);
if ( !defined('PASS_COOKIE') )
	define('PASS_COOKIE', 'wordpresspass_'. COOKIEHASH);
if ( !defined('COOKIEPATH') )
	define('COOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_settings('home') . '/' ) );
if ( !defined('SITECOOKIEPATH') )
	define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_settings('siteurl') . '/' ) );
if ( !defined('COOKIE_DOMAIN') )
	define('COOKIE_DOMAIN', false);

require (ABSPATH . WPINC . '/vars.php');

do_action('core_files_loaded');

/*
// Check for hacks file if the option is enabled
if (get_settings('hack_file')) {
	if (file_exists(ABSPATH . '/my-hacks.php'))
		require(ABSPATH . '/my-hacks.php');
}
*/

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

$wp_query   = new WP_Query();
$wp_rewrite = new WP_Rewrite();
$wp         = new WP();

define('TEMPLATEPATH', get_template_directory());

// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(ABSPATH . WPINC . '/locale.php');

// Load functions for active theme.
if ( file_exists(TEMPLATEPATH . "/functions.php") )
	include(TEMPLATEPATH . "/functions.php");

function shutdown_action_hook() {
	do_action('shutdown');
	wp_cache_close();
}
register_shutdown_function('shutdown_action_hook');

// Everything is loaded and initialized.
do_action('init');

?>
