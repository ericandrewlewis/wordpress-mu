<?PHP
/*
	Helper functions for WPMU
*/


function wpmu_update_blogs_date() {
    global $wpdb;

    $query = "UPDATE ".$wpdb->blogs."
              SET    last_updated = NOW()
	      WHERE  blog_id = '".$wpdb->blogid."'";
    $wpdb->query( $query );
}
add_action('comment_post', 'wpmu_update_blogs_date');
add_action('delete_post', 'wpmu_update_blogs_date');
add_action('delete_comment', 'wpmu_update_blogs_date');
add_action('private_to_published', 'wpmu_update_blogs_date');
add_action('publish_phone', 'wpmu_update_blogs_date');
add_action('publish_post', 'wpmu_update_blogs_date');
add_action('trackback_post', 'wpmu_update_blogs_date');
add_action('wp_set_comment_status', 'wpmu_update_blogs_date');

/*
  Determines if the available space defined by the admin has been exceeded by the user
*/
/**
 * Returns how much space is available (also shows a picture) for the current client blog, retrieving the value from the master blog 'main' option table
 *
 * @param string $action
 * @return string
 */
function wpmu_checkAvailableSpace($action) {
	// Using the action.
	// Set the action to 'not-writable' to block the upload
	global $wpblog, $blog_id;
	
	// Default space allowed is 10 MB 
	$spaceAllowed = get_site_option("blog_upload_space" );
	if( $spaceAllowed == false )
		$spaceAllowed = 10;
	
	$dirName = ABSPATH."wp-content/blogs.dir/".$blog_id."/files/";
	
  	$dir  = dir($dirName);
   	$size = 0;

	while($file = $dir->read()) {
		if ($file != '.' && $file != '..') {
			if (is_dir($file)) {
	           $size += dirsize($dirName . '/' . $file);
	       } else {
	           $size += filesize($dirName . '/' . $file);
	       }
	   }
	}
	$dir->close();
	$size = $size / 1024 / 1024;
	
	?>	
	<table align="center" width="20%" cellpadding="0" cellspacing="0">
	<tr>
	<td>Space Available (<?php printf( "%2.2f", ( ($spaceAllowed-$size) ) ) ?><i>MB)</i></td>
	</tr>
	<tr>
	<td bgcolor="<?php echo ((($size/$spaceAllowed)*100)<70)?"Green":"Red"; ?>">&nbsp;</td><td bgcolor="Black" width="<?php echo (($size/$spaceAllowed)*100); ?>%"></td>
	</tr>
	</table>
	<?
	
	if (($spaceAllowed-$size)>0) {
		return $action;
	} else {
		// No space left
		return 'not-writable';	
	}
}
add_filter('fileupload_init','wpmu_checkAvailableSpace');

function createBlog( $domain, $path, $username, $weblog_title, $admin_email, $site_id = 1 ) {
    global $wpdb, $table_prefix, $wp_queries, $wpmuBaseTablePrefix, $current_site;

	$domain       = addslashes( $domain );
	$weblog_title = addslashes( $weblog_title );
	$admin_email  = addslashes( $admin_email );
	$username     = addslashes( $username );	

    if( empty($path) )
	    $path = '/';
	
    $limited_email_domains = get_site_settings( 'limited_email_domains' );
    if( is_array( $limited_email_domains ) && empty( $limited_email_domains ) == false ) {
	    $emaildomain = substr( $admin_email, 1 + strpos( $admin_email, '@' ) );
	    if( in_array( $emaildomain, $limited_email_domains ) == false ) {
		    return "error: email domain not allowed";
	    }
    }
    // Check if the domain has been used already. We should return an error message.
    if( $wpdb->get_var("SELECT blog_id FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$path'" ) )
	return 'error: Blog URL already taken.';

    // Check if the username has been used already. We should return an error message.
    if( $wpdb->get_var( "SELECT ID FROM   ".$wpdb->users." WHERE  user_login = '".$username."'" ) == true )
	return "error: username used";

    // Need to backup wpdb table names, and create a new wp_blogs entry for new blog.
    // Need to get blog_id from wp_blogs, and create new table names.
    // Must restore table names at the end of function.

    $wpdb->hide_errors();

    $query = "SELECT blog_id
	      FROM   ".$wpdb->blogs."
	      WHERE  site_id = '".$site_id."'
	      AND    domain  = '".$domain."'
	      AND    path    = '".$path."'";
    $blog_id = $wpdb->get_var( $query );
    if( $blog_id != false ) {
	return "error: blogname used";
    }
    $query = "INSERT INTO $wpdb->blogs ( blog_id, site_id, domain, path, registered ) VALUES ( NULL, '$site_id', '$domain', '$path', NOW( ))";
    if( $wpdb->query( $query ) == false ) {
	return "error: problem creating blog entry";
    }
    $blog_id = $wpdb->insert_id;

    // backup
    $tmp[ 'siteid' ]         = $wpdb->siteid;
    $tmp[ 'blogid' ]         = $wpdb->blogid;
    $tmp[ 'posts' ]          = $wpdb->posts;
    $tmp[ 'categories' ]     = $wpdb->categories;
    $tmp[ 'post2cat' ]       = $wpdb->post2cat;
    $tmp[ 'comments' ]       = $wpdb->comments;
    $tmp[ 'links' ]          = $wpdb->links;
    $tmp[ 'linkcategories' ] = $wpdb->linkcategories;
    $tmp[ 'option' ]         = $wpdb->option;
    $tmp[ 'postmeta' ]       = $wpdb->postmeta;
    $tmptable_prefix         = $table_prefix;

    // fix the new prefix.
    $table_prefix = $wpmuBaseTablePrefix . $blog_id . "_";
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

    @mkdir( ABSPATH . "wp-content/blogs.dir/".$blog_id, 0777 );
    @mkdir( ABSPATH . "wp-content/blogs.dir/".$blog_id."/files", 0777 );

    require_once( ABSPATH . 'wp-admin/upgrade-functions.php');
    $wpdb->hide_errors();
    $installed = $wpdb->get_results("SELECT * FROM $wpdb->posts");
    if ($installed) die(__('<h1>Already Installed</h1><p>You appear to have already installed WordPress. To reinstall please clear your old database tables first.</p>') . '</body></html>');
    flush();

    if( $path == '/' ) {
	    $slash = '';
    } else {
	    $slash = $path;
    }
    if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
	$url = "http://".$domain.$path.$slash;
    } else {
	if( $blogname == 'main' ) {
	    $url = "http://".$domain.$path.$slash;
	} else {
	    $url = "http://".$domain.$path.$blogname.$slash;
	}
    }

    // Set everything up
    make_db_current_silent();
    populate_options();

    // fix url.
    update_option('siteurl', $url);

    $wpdb->query("UPDATE $wpdb->options SET option_value = '".$weblog_title."' WHERE option_name = 'blogname'");
    $wpdb->query("UPDATE $wpdb->options SET option_value = '".$admin_email."' WHERE option_name = 'admin_email'");

    // Now drop in some default links
    $wpdb->query("INSERT INTO $wpdb->linkcategories (cat_id, cat_name) VALUES (1, '".addslashes(__('Blogroll'))."')");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://blog.carthik.net/index.php', 'Carthik', 1, 'http://blog.carthik.net/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://blogs.linux.ie/xeer/', 'Donncha', 1, 'http://blogs.linux.ie/xeer/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://zengun.org/weblog/', 'Michel', 1, 'http://zengun.org/weblog/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://boren.nu/', 'Ryan', 1, 'http://boren.nu/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://photomatt.net/', 'Matt', 1, 'http://xml.photomatt.net/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://zed1.com/journalized/', 'Mike', 1, 'http://zed1.com/journalized/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://www.alexking.org/', 'Alex', 1, 'http://www.alexking.org/blog/wp-rss2.php');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://dougal.gunters.org/', 'Dougal', 1, 'http://dougal.gunters.org/feed/');");
    $wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_rss) VALUES ('http://www.wordpress.com/', 'WordPress', 1, 'http://www.wordpress.com/feed/');");

    // Default category
    $wpdb->query("INSERT INTO $wpdb->categories (cat_ID, cat_name, category_nicename) VALUES ('0', '".addslashes(__('Uncategorized'))."', '".sanitize_title(__('Uncategorized'))."')");

    // Set up admin user
    $random_password = substr(md5(uniqid(microtime())), 0, 6);
    $wpdb->query("INSERT INTO $wpdb->users (ID, user_login, user_pass, user_email, user_registered, display_name) VALUES ( NULL, '".$username."', MD5('$random_password'), '".$admin_email."', NOW(), 'Administrator' )");
    $userID = $wpdb->insert_id;
    $metavalues = array( "user_nickname" 		=> addslashes(__('Administrator')), 
                         $table_prefix . "user_level" 	=> 10, 
			 "source_domain" 		=> $domain, 
			 "{$table_prefix}capabilities" 	=> serialize(array('administrator' => true)) );
    reset( $metavalues );
    while( list( $key, $val ) = each ( $metavalues ) )
    {
	$query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '".$userID."', '".$key."' , '".$val."')";
	$wpdb->query( $query );
    }

    // First post
    $now = date('Y-m-d H:i:s');
    $now_gmt = gmdate('Y-m-d H:i:s');

    $first_post = get_site_option( 'first_post' );
    if( $first_post == false )
		$first_post = stripslashes( __( 'Welcome to <a href="SITE_URL">SITE_NAME</a>. This is your first post. Edit or delete it, then start blogging!' ) );
    $welcome_email = stripslashes( get_site_option( 'welcome_email' ) );
    if( $welcome_email == false ) 
		$welcome_email = stripslashes( __( "Dear User,
		
Your new SITE_NAME blog has been successfully set up at:
BLOG_URL

You can log in to the administrator account with the following information:
Username: USERNAME
Password: PASSWORD
Login Here: BLOG_URLwp-login.php

We hope you enjoy your new weblog.
Thanks!

--The WordPress Team
SITE_NAME" ) );


    $welcome_email = str_replace( "SITE_NAME", $current_site->site_name, $welcome_email );
    $welcome_email = str_replace( "BLOG_URL", $url, $welcome_email );
    $welcome_email = str_replace( "USERNAME", $username, $welcome_email );
    $welcome_email = str_replace( "PASSWORD", $random_password, $welcome_email );

    $first_post = str_replace( "SITE_URL", "http://" . $current_site->domain . $current_site->path, $first_post );
    $first_post = str_replace( "SITE_NAME", $current_site->site_name, $first_post );
    $wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_name, post_modified, post_modified_gmt) VALUES ('".$userID."', '$now', '$now_gmt', '".addslashes($first_post)."', '".addslashes(__('Hello world!'))."', '0', '".addslashes(__('hello-world'))."', '$now', '$now_gmt')");
    $wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_name, post_modified, post_modified_gmt, post_status) VALUES ('".$userID."', '$now', '$now_gmt', '".addslashes(__('This is an example of a WordPress page, you could edit this to put information about yourself or your site so readers know where you are coming from. You can create as many pages like this one or sub-pages as you like and manage all of your content inside of WordPress.'))."', '".addslashes(__('About'))."', '0', '".addslashes(__('about'))."', '$now', '$now_gmt', 'static')");

    $wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (1, 1, 1)" );
    $wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (2, 2, 1)" );

    // Default comment
    $wpdb->query("INSERT INTO $wpdb->comments (comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content) VALUES ('1', '".addslashes(__('Mr WordPress'))."', '', 'http://" . $current_site->domain . $current_site->path . "', '127.0.0.1', '$now', '$now_gmt', '".addslashes(__('Hi, this is a comment.<br />To delete a comment, just log in, and view the posts\' comments, there you will have the option to edit or delete them.'))."')");

    $message_headers = 'From: ' . stripslashes($weblog_title) . ' <wordpress@' . $_SERVER[ 'SERVER_NAME' ] . '>';
    $message = $welcome_email;
    @mail($admin_email, __('New ' . $current_site->site_name . ' Blog').": ".stripslashes( $weblog_title ), $message, $message_headers);

    // remove all perms except for the login user.
    $wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id != '".$userID."' AND meta_key = '".$table_prefix."user_level'" );
    $wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id != '".$userID."' AND meta_key = '".$table_prefix."capabilities'" );
    if( $userID != 1 )
	    $wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id = '".$userID."' AND meta_key = '" . $wpmuBaseTablePrefix . "1_capabilities'" );

    // restore wpdb variables
    reset( $tmp );
    while( list( $key, $val ) = each( $tmp ) ) 
    { 
	$wpdb->$key = $val;
    }
    $table_prefix = $tmptable_prefix;

    $wpdb->show_errors();

    return "ok";
}

if( defined( "WP_INSTALLING" ) == false ) {
	header( "X-totalblogs: " . get_blog_count() );
	header( "X-rootblog: http://" . $current_site->domain . $current_site->path );
	header( "X-created-on: " . $current_blog->registered );

	if( empty( $WPMU_date ) == false ) 
		header( "X-wpmu-date: $WPMU_date" );
}


function get_blogaddress_by_id( $blog_id ) {
    global $hostname, $domain, $base, $wpdb;

    // not current blog
    $query = "SELECT *
	      FROM   ".$wpdb->blogs."
	      WHERE  blog_id = '".$blog_id."'";
    $bloginfo = $wpdb->get_results( $query, ARRAY_A );
    if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
	return "http://".$bloginfo[ 'blogname' ].".".$domain.$base;
    } else {
	return "http://".$hostname.$base.$bloginfo[ 'blogname' ];
    }
}

function get_blogaddress_by_name( $blogname ) {
    global $domain, $base, $wpdb;

    if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
	if( $blogname == 'main' )
	    $blogname = 'www';
	return "http://".$blogname.".".$domain.$base;
    } else {
	return "http://".$hostname.$base.$blogname;
    }
}

function get_sitestats() {
    global $wpdb, $basedomain, $base;

    $query = "SELECT count(*) as c
              FROM ".$wpdb->blogs." 
	      WHERE site_id = '".$wpdb->siteid."'";
    $blogs = $wpdb->get_var( $query );
    $stats[ 'blogs' ] = $blogs;

    $query = "SELECT count(*) as c
              FROM ".$wpdb->users;
    $users = $wpdb->get_var( $query );
    $stats[ 'users' ] = $users;

    return $stats;

}

function get_admin_users_for_domain( $sitedomain = '', $path = '' ) {
    global $domain, $base, $basedomain, $wpdb, $wpmuBaseTablePrefix;
    if( $sitedomain == '' ) {
	$sitedomain = $basedomain;
	$path = $base;
	$site_id = $wpdb->siteid;
    } else {
	$query = "SELECT id
                  FROM   ".$wpdb->site."
                  WHERE  domain = '".$sitedomain."'
	          AND    path = '".$path."'";
        $site_id = $wpdb->get_var( $query );
    }
    if( $site_id != false ) {
	$query = "SELECT ID, user_login, user_pass
	          FROM   ".$wpdb->users.", ".$wpdb->sitemeta."
		  WHERE  meta_key = 'admin_user_id'
		  AND    ".$wpdb->users.".ID = ".$wpdb->sitemeta.".meta_value
		  AND    ".$wpdb->sitemeta.".site_id = '".$site_id."'";
	$details = $wpdb->get_results( $query, ARRAY_A );
    } else {
	$details = false;
    }

    return $details;
}

function is_site_admin( $user_id ) {
    global $wpdb, $current_user;

    if( $wpdb->get_var( "SELECT site_id FROM $wpdb->sitemeta WHERE site_id = '$wpdb->siteid' AND meta_key = 'admin_user_id' AND meta_value = '$user_id'" ) == false ) {
	return false;
    } else {
	return true;
    }
}

function get_site_settings( $option, $default='na' ) {
    global $wpdb;

    $query = "SELECT meta_value 
              FROM   $wpdb->sitemeta 
	      WHERE  meta_key = '$option'
	      AND    site_id = '".$wpdb->siteid."'";
    $option = $wpdb->get_row( $query );
    if( $option == false ) {
	    if( $default != 'na' ) {
		    return $default;
	    } else {
		    return false;
	    }
    } else {
	    @ $kellogs = unserialize($option->meta_value);
	    if ($kellogs !== FALSE) {
		    $meta_value = $kellogs;
	    } else {
		    $meta_value = $option->meta_value;
	    }
	    return $meta_value;
    }
}

function get_site_option( $option, $default='na' ) {
	return get_site_settings( $option, $default );
}

function add_site_settings( $key, $value ) {
    global $wpdb;
    if( $value != get_site_settings( $key ) ) {
	if ( is_array($value) || is_object($value) )
	    $value = serialize($value);
	$query = "SELECT meta_value 
                  FROM   ".$wpdb->sitemeta." 
	          WHERE  meta_key = '$key'
	          AND    site_id = '".$wpdb->siteid."'";
       if( $wpdb->get_row( $query ) == false ) {
	   $query = "INSERT INTO $wpdb->sitemeta ( meta_id , site_id , meta_key , meta_value )
	             VALUES ( NULL, '".$wpdb->siteid."', '".$key."', '".$wpdb->escape( $value )."')";
	   $wpdb->query( $query );
       }
    }
}

function add_site_option( $key, $value ) {
	return add_site_settings( $key, $value );
}

function update_site_settings( $key, $value ) {
    global $wpdb;
    if( $value != get_site_settings( $key ) ) {
	if ( is_array($value) || is_object($value) )
	    $value = serialize($value);

	$value = trim($value); // I can't think of any situation we wouldn't want to trim
	$query = "SELECT meta_key, meta_value 
                  FROM   ".$wpdb->sitemeta." 
	          WHERE  meta_key = '$key'
	          AND    site_id = '".$wpdb->siteid."'";
       if( $wpdb->get_row( $query ) == false ) {
	   add_site_option( $key, $value );
       } else {
	   $query = "UPDATE ".$wpdb->sitemeta."
	             SET    meta_value = '".$wpdb->escape( $value )."'
		     WHERE  meta_key   = '".$key."'";
	   $wpdb->query( $query );
       }
    }
}

function update_site_option( $key, $value ) {
	return update_site_settings( $key, $value );
}

function switch_to_blogid( $blog_id ) {
    global $tmpoldblogdetails, $wpdb, $wpmuBaseTablePrefix, $cache_settings;

    // FIXME

    // backup
    $tmpoldblogdetails[ 'blogid' ]         = $wpdb->blogid;
    $tmpoldblogdetails[ 'posts' ]          = $wpdb->posts;
    $tmpoldblogdetails[ 'categories' ]     = $wpdb->categories;
    $tmpoldblogdetails[ 'post2cat' ]       = $wpdb->post2cat;
    $tmpoldblogdetails[ 'comments' ]       = $wpdb->comments;
    $tmpoldblogdetails[ 'links' ]          = $wpdb->links;
    $tmpoldblogdetails[ 'linkcategories' ] = $wpdb->linkcategories;
    $tmpoldblogdetails[ 'option' ]         = $wpdb->option;
    $tmpoldblogdetails[ 'postmeta' ]       = $wpdb->postmeta;
    $tmpoldblogdetails[ 'prefix' ]         = $wpdb->prefix;

    // fix the new prefix.
    $table_prefix = $wpmuBaseTablePrefix . $blog_id . "_";
    $wpdb->blogid           = $blog_id;
    $wpdb->posts            = $table_prefix . 'posts';
    $wpdb->categories       = $table_prefix . 'categories';
    $wpdb->post2cat         = $table_prefix . 'post2cat';
    $wpdb->comments         = $table_prefix . 'comments';
    $wpdb->links            = $table_prefix . 'links';
    $wpdb->linkcategories   = $table_prefix . 'linkcategories';
    $wpdb->options          = $table_prefix . 'options';
    $wpdb->postmeta         = $table_prefix . 'postmeta';

    unset( $cache_settings );
}

function restore_current_blogid() {
    global $tmpoldblogdetails, $wpdb;
    // backup
    $wpdb->blogid = $tmpoldblogdetails[ 'blogid' ];
    $wpdb->posts = $tmpoldblogdetails[ 'posts' ];
    $wpdb->categories = $tmpoldblogdetails[ 'categories' ];
    $wpdb->post2cat = $tmpoldblogdetails[ 'post2cat' ];
    $wpdb->comments = $tmpoldblogdetails[ 'comments' ];
    $wpdb->links = $tmpoldblogdetails[ 'links' ];
    $wpdb->linkcategories = $tmpoldblogdetails[ 'linkcategories' ];
    $wpdb->option = $tmpoldblogdetails[ 'option' ];
    $wpdb->postmeta = $tmpoldblogdetails[ 'postmeta' ];
    $wpdb->prefix = $tmpoldblogdetails[ 'prefix' ];
}

function get_users_of_blog( $id ) {
    global $wpdb, $wpmuBaseTablePrefix;
    $users = $wpdb->get_results( "SELECT user_id, user_login FROM $wpdb->users, $wpdb->usermeta WHERE " . $wpdb->users . ".ID = " . $wpdb->usermeta . ".user_id AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities'" );
    return $users;
}

function get_blogs_of_user( $id ) {
    global $wpdb, $wpmuBaseTablePrefix;
    $blogs = $wpdb->get_results( "SELECT domain, REPLACE( REPLACE( meta_key, '$wpmuBaseTablePrefix', '' ), '_capabilities', '' ) as userblog_id  FROM $wpdb->blogs, $wpdb->usermeta WHERE $wpdb->blogs.blog_id = REPLACE( REPLACE( $wpdb->usermeta.meta_key, '$wpmuBaseTablePrefix', '' ), '_capabilities', '' ) AND user_id = '$id' AND meta_key LIKE '%capabilities'" );

    return $blogs;
}

function is_archived( $id ) {
    global $wpdb, $wpmuBaseTablePrefix;
    $is_archived = $wpdb->get_var( "SELECT option_value FROM " . $wpmuBaseTablePrefix . $id . "_options WHERE option_name = 'is_archived'" );
    if( $is_archived == false )
	$is_archived = 'no';

    return $is_archived;
}

function get_last_updated( $display = false ) {
    global $wpdb;
    $blogs = $wpdb->get_results( "SELECT blog_id domain, path FROM $wpdb->blogs WHERE site_id = '$wpdb->siteid' AND last_updated != '0000-00-00 00:00:00' ORDER BY last_updated DESC limit 0,40", ARRAY_A );
    if( is_array( $blogs ) ) {
	while( list( $key, $details ) = each( $blogs ) ) { 
	    if( is_archived( $details[ 'blog_id' ] ) == 'yes' )
		unset( $blogs[ $key ] );
	}
    }

    return $blogs;
}

function get_most_active_blogs( $num = 10, $display = true ) {
    global $wpdb;
    $most_active = get_site_option( "most_active" );
    $update = false;
    if( is_array( $most_active ) ) {
	if( ( $most_active[ 'time' ] + 60 ) < time() ) { // cache for 60 seconds.
	    $update = true;
	}
    } else {
	$update = true;
    }

    if( $update == true ) {
	unset( $most_active );
	$blogs = get_blog_list( 0, 'all', false ); // $blog_id -> $details
	if( is_array( $blogs ) ) {
	    reset( $blogs );
	    while( list( $key, $details ) = each( $blogs ) ) { 
		$most_active[ $details[ 'blog_id' ] ] = $details[ 'postcount' ];
		$blog_list[ $details[ 'blog_id' ] ] = $details; // array_slice() removes keys!!
	    }
	    arsort( $most_active );
	    reset( $most_active );
	    while( list( $key, $details ) = each( $most_active ) ) { 
		$t[ $key ] = $blog_list[ $key ];
	    }
	    unset( $most_active );
	    $most_active = $t;
	}
	update_site_option( "most_active", $most_active );
    }

    if( $display == true ) {
	    if( is_array( $most_active ) ) {
		    reset( $most_active );
		    while( list( $key, $details ) = each( $most_active ) ) { 
			    $url = "http://" . $details[ 'domain' ] . $details[ 'path' ];
			    print "<li>" . $details[ 'postcount' ] . " <a href='$url'>$url</a></li>";
		    }
	    }
    }

    return array_slice( $most_active, 0, $num );
}

function get_blog_list( $start = 0, $num = 10, $display = true ) {
    global $wpdb, $wpmuBaseTablePrefix;

    $blogs = get_site_option( "blog_list" );
    $update = false;
    if( is_array( $blogs ) ) {
	if( ( $blogs[ 'time' ] + 60 ) < time() ) { // cache for 60 seconds.
	    $update = true;
	}
    } else {
	$update = true;
    }

    if( $update == true ) {
	unset( $blogs );
	$blogs = $wpdb->get_results( "SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = '$wpdb->siteid' ORDER BY registered DESC", ARRAY_A );
	if( is_array( $blogs ) ) {
	    while( list( $key, $details ) = each( $blogs ) ) { 
		if( is_archived( $details[ 'blog_id' ] ) == 'yes' )
		    unset( $blogs[ $key ] );

		$blog_list[ $details[ 'blog_id' ] ] = $details;
		$blog_list[ $details[ 'blog_id' ] ][ 'postcount' ] = $wpdb->get_var( "SELECT count(*) FROM " . $wpmuBaseTablePrefix . $details[ 'blog_id' ] . "_posts WHERE post_status='publish'" );
	    }
	    unset( $blogs );
	    $blogs = $blog_list;
	}
	update_site_option( "blog_list", $blogs );
    }

    if( $num == 'all' ) {
	    return array_slice( $blogs, $start, count( $blogs ) );
    } else {
	    return array_slice( $blogs, $start, $num );
    }
}

function get_blog_count( $id = 0 ) {
	global $wpdb;
	if( $id == 0 )
		$id = $wpdb->siteid;
	
	$count = $wpdb->get_var( "SELECT count(*) as c FROM $wpdb->blogs WHERE site_id = '$id'" );

	return $count;
}
?>
