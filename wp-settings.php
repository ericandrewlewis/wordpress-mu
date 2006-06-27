<?php
if( defined( 'ABSPATH' ) == false )
	die();

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

$wpdb->blogs		= $table_prefix . 'blogs';
$wpdb->users		= $table_prefix . 'users';
$wpdb->usermeta		= $table_prefix . 'usermeta';
$wpdb->site		= $table_prefix . 'site';
$wpdb->sitemeta		= $table_prefix . 'sitemeta';
$wpdb->sitecategories	= $table_prefix . 'sitecategories';
$wpdb->signups		= $table_prefix . 'signups';

require_once ( ABSPATH . 'wpmu-settings.php' );
$wpdb->siteid           = $site_id;
$wpdb->blogid           = $blog_id;
$wpdb->posts            = $table_prefix . 'posts';
$wpdb->categories       = $table_prefix . 'categories';
$wpdb->post2cat         = $table_prefix . 'post2cat';
$wpdb->comments         = $table_prefix . 'comments';
$wpdb->link2cat         = $table_prefix . 'link2cat';
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
$tablelink2cat = $wpdb->link2cat;
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
require (ABSPATH . WPINC . '/plugin.php');
require (ABSPATH . WPINC . '/default-filters.php');
require_once (ABSPATH . WPINC . '/l10n.php');

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

require (ABSPATH . WPINC . '/formatting.php');
require (ABSPATH . WPINC . '/capabilities.php');
require (ABSPATH . WPINC . '/classes.php');
require (ABSPATH . WPINC . '/query.php');
require (ABSPATH . WPINC . '/theme.php');
require (ABSPATH . WPINC . '/user.php');
require (ABSPATH . WPINC . '/general-template.php');
require (ABSPATH . WPINC . '/link-template.php');
require (ABSPATH . WPINC . '/author-template.php');
require (ABSPATH . WPINC . '/post.php');
require (ABSPATH . WPINC . '/post-template.php');
require (ABSPATH . WPINC . '/category.php');
require (ABSPATH . WPINC . '/category-template.php');
require (ABSPATH . WPINC . '/comment.php');
require (ABSPATH . WPINC . '/comment-template.php');
require (ABSPATH . WPINC . '/rewrite.php');
require (ABSPATH . WPINC . '/feed.php');
require (ABSPATH . WPINC . '/bookmark.php');
require (ABSPATH . WPINC . '/bookmark-template.php');
require (ABSPATH . WPINC . '/kses.php');
require (ABSPATH . WPINC . '/cron.php');
require (ABSPATH . WPINC . '/version.php');
require (ABSPATH . WPINC . '/deprecated.php');
require (ABSPATH . WPINC . '/script-loader.php');

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

if ( '1' == $current_blog->deleted )
	graceful_fail('This user has elected to delete their account and the content is no longer available.');

if ( '2' == $current_blog->deleted )
		graceful_fail("This blog has not been activated yet. If you are having problems activating your blog, please contact <a href='mailto:support@{$current_site->domain}'>support@{$current_site->domain}</a>.");

if( $current_blog->archived == '1' )
    graceful_fail( 'This blog has been archived or suspended.' );

if( $current_blog->spam == '1' )
    graceful_fail( 'This blog has been archived or suspended.' );

if (!strstr($_SERVER['PHP_SELF'], 'install.php') && !strstr($_SERVER['PHP_SELF'], 'wp-admin/import')) :
    // Used to guarantee unique hash cookies
    $cookiehash = ''; // Remove in 1.4
	define('COOKIEHASH', ''); 
endif;

if ( !defined('USER_COOKIE') )
	define('USER_COOKIE', 'wordpressuser');
if ( !defined('PASS_COOKIE') )
	define('PASS_COOKIE', 'wordpresspass');
if ( !defined('COOKIEPATH') )
	define('COOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_settings('home') . '/' ) );
if ( !defined('SITECOOKIEPATH') )
	define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_settings('siteurl') . '/' ) );
if ( !defined('COOKIE_DOMAIN') )
	define('COOKIE_DOMAIN', false);

require (ABSPATH . WPINC . '/vars.php');

if ( get_settings('active_plugins') ) {
	$current_plugins = get_settings('active_plugins');
	if ( is_array($current_plugins) ) {
		foreach ($current_plugins as $plugin) {
			if ('' != $plugin && file_exists(ABSPATH . 'wp-content/plugins/' . $plugin))
				include_once(ABSPATH . 'wp-content/plugins/' . $plugin);
		}
	}
}

require (ABSPATH . WPINC . '/pluggable.php');

if ( defined('WP_CACHE') && function_exists('wp_cache_postload') )
	wp_cache_postload();

do_action('plugins_loaded');

// If already slashed, strip.
if ( get_magic_quotes_gpc() ) {
	$_GET    = stripslashes_deep($_GET   );
	$_POST   = stripslashes_deep($_POST  );
	$_COOKIE = stripslashes_deep($_COOKIE);
}

// Escape with wpdb.
$_GET    = add_magic_quotes($_GET   );
$_POST   = add_magic_quotes($_POST  );
$_COOKIE = add_magic_quotes($_COOKIE);
$_SERVER = add_magic_quotes($_SERVER);

do_action('sanitize_comment_cookies');

$wp_query   = new WP_Query();
$wp_rewrite = new WP_Rewrite();
$wp         = new WP();

define('TEMPLATEPATH', get_template_directory());

// Load the default text localization domain.
load_default_textdomain();

// Pull in locale data after loading text domain.
require_once(ABSPATH . WPINC . '/locale.php');

$wp_locale = new WP_Locale();

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