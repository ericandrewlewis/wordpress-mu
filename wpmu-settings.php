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

#$sites = $wpdb->get_results( "SELECT * FROM $wpdb->site" );
#if( count( $sites ) == 1 ) {
	#$current_site = $sites[0];
#}
	
if( isset( $current_site ) == false ) {
	$path = substr( $_SERVER[ 'REQUEST_URI' ], 0, 1 + strpos( $_SERVER[ 'REQUEST_URI' ], '/', 1 ) );
	if( constant( 'VHOST' ) == 'yes' ) {
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='$path'" );
		if( $current_site == null ) {
			$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='/'" );
			if( $current_site == null ) {
				$sitedomain = substr( $domain, 1 + strpos( $domain, '.' ) );
				$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$sitedomain' AND path='$path'" );
				if( $current_site == null ) {
					$path = '/';
					$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$sitedomain' AND path='$path'" );
					if( $current_site == null && defined( "WP_INSTALLING" ) == false )
						die( "No WPMU site defined on this host." );
				}
			} else {
				$path = '/';
			}
		}
	} else {
		$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='$path'" );
		if( $current_site == null ) {
			$path = '/';
			$current_site = $wpdb->get_row( "SELECT * FROM $wpdb->site WHERE domain = '$domain' AND path='$path'" );
			if( $current_site == null && defined( "WP_INSTALLING" ) == false )
				die( "No WPMU site defined on this host." );
		}
	}
}


if( constant( 'VHOST' ) == 'yes' ) {
	$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain'");
	if( $current_blog != null ) {
		$current_site = $wpdb->get_row("SELECT * FROM $wpdb->site WHERE id='{$current_blog->site_id}'");
	} else {
		$blogname = substr( $domain, 0, strpos( $domain, '.' ) );
	}
} else {
	$blogname = htmlspecialchars( substr( $_SERVER[ 'REQUEST_URI' ], strlen( $path ) ) );
	if( strpos( $blogname, '/' ) )
		$blogname = substr( $blogname, 0, strpos( $blogname, '/' ) );
	if( strpos( $blogname, '?' ) )
		$blogname = substr( $blogname, 0, strpos( $blogname, '?' ) );
	if( $blogname == '' || $blogname == 'blog' || $blogname == 'wp-admin' || $blogname == 'wp-includes' || $blogname == 'files' || $blogname == 'feed' || is_file( $blogname ) ) {
		$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$path'");
	} else {
		$current_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE domain = '$domain' AND path = '{$path}{$blogname}/'");
	}
}

if( defined( "WP_INSTALLING" ) == false ) {
	if( $current_site && $current_blog == null ) {
		header( "Location: http://{$current_site->domain}{$current_site->path}wp-signup.php?new=" . urlencode( $blogname ) );
		die();
	}
	if( $current_blog == false || $current_site == false )
		is_installed();
}

$blog_id = $current_blog->blog_id;
$public  = $current_blog->public;
$site_id = $current_blog->site_id;

if( $site_id == 0 )
	$site_id = 1;

$current_site->site_name = $wpdb->get_var( "SELECT meta_value FROM $wpdb->sitemeta WHERE site_id = '$site_id' AND meta_key = 'site_name'" );
if( $current_site->site_name == null )
	$current_site->site_name = ucfirst( $current_site->domain );

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
