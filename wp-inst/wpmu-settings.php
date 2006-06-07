<?php
if( defined( 'ABSPATH' ) == false )
	die();

$wpmuBaseTablePrefix = $table_prefix;

$domain = addslashes( $_SERVER['HTTP_HOST'] );
if( substr( $domain, 0, 4 ) == 'www.' )
	$domain = substr( $domain, 4 );
$domain = preg_replace('/:.*$/', '', $domain); // Strip ports

$path = preg_replace( '|([a-z0-9-]+.php.*)|', '', $_SERVER['REQUEST_URI'] );
$path = str_replace ( '/wp-admin/', '/', $path );
$path = preg_replace( '|(/[a-z0-9-]+?/).*|', '$1', $path );

$wpdb->hide_errors();

if( constant( 'VHOST' ) == 'yes' )
	$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain'");
else
	$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$path'");

if( $current_blog == false )
    is_installed();

$blog_id = $current_blog->blog_id;
$public  = $current_blog->public;
$site_id = $current_blog->site_id;

if( $site_id == 0 )
	$site_id = 1;

$current_site = $wpdb->get_row("SELECT * FROM $wpdb->site WHERE id='$site_id'");

if( $current_site == false )
    is_installed();

$current_site->site_name = $wpdb->get_var( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = '$site_id' AND meta_key = 'site_name'" );

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

$wpdb->show_errors();

if( '0' == $current_blog->public ) {
	// This just means the blog shouldn't show up in google, etc. Only to registered members
}

if( $current_blog->archived == '1' ) {
    die( 'This blog has been archived or suspended.' );
}

if( $current_blog->spam == '1' ) {
    die( 'This blog has been archived or suspended.' );
}

function is_installed() {
    global $wpdb, $domain, $path;
	$base = stripslashes( $base );
    if( defined( "WP_INSTALLING" ) == false ) {
	    $check = $wpdb->get_results( "SELECT * FROM $wpdb->site" );
	    if( $check == false ) {
		    $msg = '<strong>Database Tables Missing.</strong><br /> The table <em>' . DB_NAME . '::' . $wpdb->site . '</em> is missing. Delete the .htaccess file and run the installer again!<br />';
	    } else {
		    $msg = '<strong>Could Not Find Blog!</strong><br />';
		    $msg .= "Searched for <em>" . $domain . $path . "</em> in " . DB_NAME . "::" . $wpdb->blogs . " table. Is that right?<br />";
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

$table_prefix = $table_prefix . $blog_id . '_';

?>
