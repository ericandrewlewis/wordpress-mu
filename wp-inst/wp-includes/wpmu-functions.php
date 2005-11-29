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
	Space Available (<?php printf( "%2.2f", ( ($spaceAllowed-$size) ) ) ?><i>MB)</i>
	<?php
	
	if (($spaceAllowed-$size)>0) {
		return $action;
	} else {
		// No space left
		return 'not-writable';	
	}
}
add_filter('fileupload_init','wpmu_checkAvailableSpace');

function createBlog( $domain, $path, $username, $weblog_title, $admin_email, $source = 'regpage', $site_id = 1 ) {
    global $wpdb, $table_prefix, $wp_queries, $wpmuBaseTablePrefix, $current_site, $wp_roles, $new_user_id, $new_blog_id;

	$domain       = addslashes( $domain );
	$weblog_title = addslashes( $weblog_title );
	$admin_email  = addslashes( $admin_email );
	$username     = addslashes( $username );	

    if( empty($path) )
	    $path = '/';
	
    $limited_email_domains = get_site_option( 'limited_email_domains' );
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
    if( $wpdb->get_var( "SELECT ID FROM   ".$wpdb->users." WHERE user_login = '".$username."'" ) == true )
	return "error: username used";

    // Check if the username has been used already. We should return an error message.
    if( $wpdb->get_var( "SELECT ID FROM   ".$wpdb->users." WHERE user_email = '".$admin_email."'" ) == true )
	return "error: email used";
    if( strpos( " " . $username, "_" ) != false )
	    return "error: username must not contain _";

    $errmsg = false ;
    $errmsg = apply_filters( "createBlog_check", $errmsg );
    if( $errmsg != false ) 
	    return "error: $errmsg";

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
    $tmp[ 'options' ]         = $wpdb->options;
    $tmp[ 'postmeta' ]       = $wpdb->postmeta;
    $tmptable_prefix         = $table_prefix;
    $tmprolekey              = $wp_roles->role_key;

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
    $wp_roles->role_key     = $table_prefix . 'user_roles';
    wp_cache_flush();

    @mkdir( ABSPATH . "wp-content/blogs.dir/".$blog_id, 0777 );
    @mkdir( ABSPATH . "wp-content/blogs.dir/".$blog_id."/files", 0777 );

    include_once( ABSPATH . 'wp-admin/upgrade-functions.php');
    $wpdb->hide_errors();
    $installed = $wpdb->get_results("SELECT * FROM $wpdb->posts");
    if ($installed) die(__('<h1>Already Installed</h1><p>You appear to have already installed WordPress. To reinstall please clear your old database tables first.</p>') . '</body></html>');
    flush();

    if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
	$url = "http://".$domain.$path;
    } else {
	    if( $domain != $_SERVER[ 'HTTP_HOST' ] ) {
		    $blogname = substr( $domain, 0, strpos( $domain, '.' ) );
		    if( $blogname != 'www.' ) {
			    $url = 'http://' . substr( $domain, strpos( $domain, '.' ) + 1 ) . $path . $blogname . '/';
		    } else { // we're installing the main blog
			    $url = 'http://' . substr( $domain, strpos( $domain, '.' ) + 1 ) . $path;
		    }
	    } else { // we're installing the main blog
		    $url = 'http://' . $domain . $path;
	    }
    }

    // Set everything up
    make_db_current_silent();
    populate_options();

    // fix url.
    update_option('siteurl', $url);
    update_option('home', $url);
    update_option('fileupload_url', $url . "files" );

    $wpdb->query("UPDATE $wpdb->options SET option_value = '".$weblog_title."' WHERE option_name = 'blogname'");
    $wpdb->query("UPDATE $wpdb->options SET option_value = '".$admin_email."' WHERE option_name = 'admin_email'");

    // Default category
    $wpdb->query("INSERT INTO $wpdb->categories (cat_ID, cat_name, category_nicename) VALUES ('0', '".addslashes(__('Uncategorized'))."', '".sanitize_title(__('Uncategorized'))."')");

    // First post
    $now = date('Y-m-d H:i:s');
    $now_gmt = gmdate('Y-m-d H:i:s');

    // Set up admin user
    $random_password = substr(md5(uniqid(microtime())), 0, 6);
	$GLOBALS['random_password'] = $random_password;
    $wpdb->query("INSERT INTO $wpdb->users (ID, user_login, user_pass, user_email, user_url, user_registered, display_name) VALUES ( NULL, '".$username."', MD5('$random_password'), '$admin_email', '$url', '$now_gmt', '$username' )");
    $userID = $wpdb->insert_id;
    $new_user_id = $userID;
    $new_blog_id = $blog_id;
    $metavalues = array( 
		'user_nickname' => addslashes($username), 
		$table_prefix . 'user_level' => 10, 
		'source_domain' => $domain, 
		'primary_blog' => $blog_id,
		$table_prefix . 'capabilities' => serialize(array('administrator' => true)),
		'source' => $source 
	);
	$metavalues = apply_filters('newblog_metavalues', $metavalues);
	foreach ( $metavalues as $key => $val ) {
		if ( empty( $val ) ) // No more annoying empty values bloating the usermeta table
			continue;
		$wpdb->query( "INSERT INTO $wpdb->usermeta ( `user_id` , `meta_key` , `meta_value` ) VALUES ( '$userID', '$key' , '$val')" );
	}

	// Now drop in some default links
	$wpdb->query("INSERT INTO $wpdb->linkcategories (cat_id, cat_name) VALUES (1, '".addslashes(__('Blogroll'))."')");
	$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_owner, link_rss) VALUES ('http://wordpress.com/', 'WordPress.com', 1, '$userID', 'http://wordpress.com/feed/');");
	$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_owner, link_rss) VALUES ('http://wordpress.org/', 'WordPress.org', 1, '$userID', 'http://wordpress.org/development/feed/');");

	$invitee_id = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$_POST[ 'u' ]}_invited_by' AND user_id = '0'" );
	if( $invitee_id ) {
		$invitee_user_login = $wpdb->get_row( "SELECT user_login, user_email FROM {$wpdb->users} WHERE ID = '$invitee_id'" );
		$invitee_blog = $wpdb->get_row( "SELECT blog_id, meta_value from {$wpdb->blogs}, {$wpdb->usermeta} WHERE user_id = '$invitee_id' AND meta_key = 'source_domain' AND {$wpdb->usermeta}.meta_value = {$wpdb->blogs}.domain" );
		if( $invitee_blog )
			$invitee_siteurl = $wpdb->get_var( "SELECT option_value FROM {$wpmuBaseTablePrefix}{$invitee_blog->blog_id}_options WHERE option_name = 'siteurl'" );
	}

	if( $invitee_siteurl && $invitee_user_login )
		$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_owner, link_rss) VALUES ('{$invitee_siteurl}', '" . ucfirst( $invitee_user_login->user_login ) . "', 1, '$userID', '');");

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
    $first_post = stripslashes( $first_post );

    $wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_name, post_modified, post_modified_gmt) VALUES ('".$userID."', '$now', '$now_gmt', '".addslashes($first_post)."', '".addslashes(__('Hello world!'))."', '0', '".addslashes(__('hello-world'))."', '$now', '$now_gmt')");
    $wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_name, post_modified, post_modified_gmt, post_status) VALUES ('".$userID."', '$now', '$now_gmt', '".addslashes(__('This is an example of a WordPress page, you could edit this to put information about yourself or your site so readers know where you are coming from. You can create as many pages like this one or sub-pages as you like and manage all of your content inside of WordPress.'))."', '".addslashes(__('About'))."', '0', '".addslashes(__('about'))."', '$now', '$now_gmt', 'static')");

    $wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (1, 1, 1)" );
    $wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (2, 2, 1)" );

    // Default comment
    $wpdb->query("INSERT INTO $wpdb->comments (comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content) VALUES ('1', '".addslashes(__('Mr WordPress'))."', '', 'http://" . $current_site->domain . $current_site->path . "', '127.0.0.1', '$now', '$now_gmt', '".addslashes(__('Hi, this is a comment.<br />To delete a comment, just log in, and view the posts\' comments, there you will have the option to edit or delete them.'))."')");


    // remove all perms except for the login user.
    $wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id != '".$userID."' AND meta_key = '".$table_prefix."user_level'" );
    $wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id != '".$userID."' AND meta_key = '".$table_prefix."capabilities'" );
    if( $userID != 1 )
	    $wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id = '".$userID."' AND meta_key = '" . $wpmuBaseTablePrefix . "1_capabilities'" );

    do_action( "wpmu_new_blog", $blog_id, $userID );

    $welcome_email = apply_filters( "update_welcome_email", $welcome_email );
    $message_headers = 'From: ' . stripslashes($weblog_title) . ' <wordpress@' . $_SERVER[ 'SERVER_NAME' ] . '>';
    $message = $welcome_email;
    if( empty( $current_site->site_name ) )
	    $current_site->site_name = "WordPress MU";
    @mail($admin_email, __('New ' . $current_site->site_name . ' Blog').": ".stripslashes( $weblog_title ), $message, $message_headers);

    // restore wpdb variables
    reset( $tmp );
    while( list( $key, $val ) = each( $tmp ) ) 
    { 
	$wpdb->$key = $val;
    }
    $table_prefix = $tmptable_prefix;
    $wp_roles->role_key = $tmprolekey;

    $wpdb->show_errors();
    wp_cache_flush();

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

	$stats[ 'blogs' ] = get_blog_count();

	$count_ts = get_site_option( "get_user_count_ts" );
	if( time() - $count_ts > 3600 ) {
		$count = $wpdb->get_var( "SELECT count(*) as c FROM {$wpdb->users}" );
		update_site_option( "user_count", $count );
		update_site_option( "user_count_ts", time() );
	} else {
		$count = get_site_option( "user_count" );
	}
	$stats[ 'users' ] = $count;

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

function get_user_details( $username ) {
	global $wpdb;

	return $wpdb->get_row( "SELECT * FROM $wpdb->users WHERE user_login = '$username'" );
}

function get_blog_details( $id ) {
	global $wpdb, $wpmuBaseTablePrefix;
	$cache = wpmu_get_cache( $id, "blog-details" );
	if( is_array( $cache ) && ( time() - $cache[ 'time' ] ) < 300 ) { // cache for 300 seconds
		$row = $cache[ 'value' ];
	} else {
		$row = $wpdb->get_row( "SELECT * FROM $wpdb->blogs WHERE blog_id = '$id'" );
		$name = $wpdb->get_row( "SELECT * FROM {$wpmuBaseTablePrefix}{$id}_options WHERE option_name = 'blogname'" );
		$row->blogname = $name->option_value;
		$row->siteurl = $wpdb->get_var( "SELECT option_value FROM {$wpmuBaseTablePrefix}{$id}_options WHERE option_name = 'siteurl'" );
		wpmu_update_cache( $id, $row, "blog-details" );
	}

	return $row;
}

function get_current_user_id() {
	global $current_user;
	return $current_user->data->ID;
}

function is_site_admin( $user_login = false ) {
	global $wpdb, $current_user;

	if ( !$current_user && !$user_login )
		return false;

	if ( $user_login )
		$user_login = sanitize_user( $user_login );
	else 
		$user_login = $current_user->data->user_login;

	$site_admins = get_site_option( 'site_admins', array('admin') );
	if( in_array( $user_login, $site_admins ) )
		return true;

	return false;
}

function get_site_option( $option, $default = false ) {
	global $wpdb;

	$value = wp_cache_get($option, 'site-options');

	if ( false === $value ) {
		$cache = wpmu_get_cache( $option, "site_options" );
		if( is_array( $cache ) && ( time() - $cache[ 'time' ] ) < 300 ) { // cache for 300 seconds
			$value = $cache[ 'value' ];
		} else {
			$value = $wpdb->get_var("SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = '$option' AND site_id = '$wpdb->siteid'");
		}
		if ( $value ) {
			wpmu_update_cache( $option, $value, "site_options" );
			wp_cache_set($option, $value, 'site-options');
		} else {
			if ( $default )
				return $default;
			return false;
		}
	}

	$value = stripslashes( $value );
	@ $kellogs = unserialize($value);
	if ( $kellogs !== FALSE )
		return $kellogs;
	else
		return $value;
}

function add_site_option( $key, $value ) {
	global $wpdb;

	if ( get_site_option( $key ) ) // If we already have it
		return false;

	if ( is_array($value) || is_object($value) )
		$value = serialize($value);
	$value = $wpdb->escape( $value );
	$wpdb->query( "INSERT INTO $wpdb->sitemeta ( site_id , meta_key , meta_value ) VALUES ( '$wpdb->siteid', '$key', '$value')" );
	return $wpdb->insert_id;
}

function update_site_option( $key, $value ) {
	global $wpdb;

	if ( $value == get_site_option( $key ) )
	 	return;
	
	if ( is_array($value) || is_object($value) )
		$value = serialize($value);
	$value = $wpdb->escape( $value );

	if ( !get_site_option( $key ) )
		add_site_option( $key, $value );

	$wpdb->query( "UPDATE $wpdb->sitemeta SET meta_value = '".$wpdb->escape( $value )."' WHERE meta_key = '$key'" );
	wpmu_update_cache( $key, $value, "site_options" );
}

function get_blog_option( $blog_id, $key, $default='na' ) {
	global $wpdb, $wpmuBaseTablePrefix;
	$cache = wpmu_get_cache( $blog_id."-".$key, "get_blog_option" );
	if( is_array( $cache ) && ( time() - $cache[ 'time' ] ) < 30 ) { 
		$opt = $cache[ 'value' ];
	} else {
		$option = $wpdb->get_row( "SELECT option_value FROM {$wpmuBaseTablePrefix}{$blog_id}_options WHERE option_name = '$key'" );
		if( $option == false ) {
			if( $default != 'na' ) {
				$opt = $default;
			} else {
				$opt = false;
			}
		} else {
			@ $kellogs = unserialize($option->option_value);
			if ($kellogs !== FALSE) {
				$option_value = $kellogs;
			} else {
				$option_value = $option->option_value;
			}
			$opt = $option_value;
		}
		wpmu_update_cache( $blog_id."-".$key, $opt, "get_blog_option" );
	}

	return $opt;
}

function add_blog_option( $blog_id, $key, $value ) {
    global $wpdb, $wpmuBaseTablePrefix;

    if( $value != get_blog_option( $blog_id, $key ) ) {
	if ( is_array($value) || is_object($value) )
	    $value = serialize($value);
	$query = "SELECT option_value FROM {$wpmuBaseTablePrefix}{$blog_id}_options WHERE option_name = '$key'";
       if( $wpdb->get_row( $query ) == false ) {
	       $wpdb->query( "INSERT INTO {$wpmuBaseTablePrefix}{$blog_id}_options ( `option_id` , `blog_id` , `option_name` , `option_can_override` , `option_type` , `option_value` , `option_width` , `option_height` , `option_description` , `option_admin_level` , `autoload` ) VALUES ( NULL, '0', '{$key}', 'Y', '1', '{$value}', '20', '8', '', '10', 'yes')" );
       } else {
	       update_blog_option( $blog_id, $key, $value );
       }
    }
}


function update_blog_option( $blog_id, $key, $value ) {
    global $wpdb, $wpmuBaseTablePrefix;

    if( $value != get_blog_option( $blog_id, $key ) ) {
	if ( is_array($value) || is_object($value) )
	    $value = serialize($value);

	$value = trim($value); // I can't think of any situation we wouldn't want to trim
	$query = "SELECT option_name, option_value FROM {$wpmuBaseTablePrefix}{$blog_id}_options WHERE option_name = '$key'";
       if( $wpdb->get_row( $query ) == false ) {
	   add_blog_option( $blog_id, $key, $value );
       } else {
	   $wpdb->query( "UPDATE {$wpmuBaseTablePrefix}{$blog_id}_options SET option_value = '".$wpdb->escape( $value )."' WHERE option_name = '".$key."'" );
       }
    }
}

function switch_to_blog( $new_blog ) {
    global $tmpoldblogdetails, $wpdb, $wpmuBaseTablePrefix, $cache_settings, $category_cache, $cache_categories, $post_cache, $wp_object_cache, $blog_id, $switched;

    // FIXME

    // backup
    $tmpoldblogdetails[ 'blogid' ]         = $wpdb->blogid;
    $tmpoldblogdetails[ 'posts' ]          = $wpdb->posts;
    $tmpoldblogdetails[ 'categories' ]     = $wpdb->categories;
    $tmpoldblogdetails[ 'post2cat' ]       = $wpdb->post2cat;
    $tmpoldblogdetails[ 'comments' ]       = $wpdb->comments;
    $tmpoldblogdetails[ 'links' ]          = $wpdb->links;
    $tmpoldblogdetails[ 'linkcategories' ] = $wpdb->linkcategories;
    $tmpoldblogdetails[ 'options' ]         = $wpdb->options;
    $tmpoldblogdetails[ 'postmeta' ]       = $wpdb->postmeta;
    $tmpoldblogdetails[ 'prefix' ]         = $wpdb->prefix;
    $tmpoldblogdetails[ 'cache_settings' ] = $cache_settings;
    $tmpoldblogdetails[ 'category_cache' ] = $category_cache;
    $tmpoldblogdetails[ 'cache_categories' ] = $cache_categories;
    $tmpoldblogdetails[ 'table_prefix' ] = $table_prefix;
    $tmpoldblogdetails[ 'post_cache' ]     = $post_cache;
    $tmpoldblogdetails[ 'wp_object_cache' ] = $wp_object_cache;
    $tmpoldblogdetails[ 'blog_id' ] = $blog_id;

    // fix the new prefix.
    $table_prefix = $wpmuBaseTablePrefix . $new_blog . "_";
    $wpdb->blogid           = $new_blog;
    $wpdb->posts            = $table_prefix . 'posts';
    $wpdb->categories       = $table_prefix . 'categories';
    $wpdb->post2cat         = $table_prefix . 'post2cat';
    $wpdb->comments         = $table_prefix . 'comments';
    $wpdb->links            = $table_prefix . 'links';
    $wpdb->linkcategories   = $table_prefix . 'linkcategories';
    $wpdb->options          = $table_prefix . 'options';
    $wpdb->postmeta         = $table_prefix . 'postmeta';
    $blog_id = $new_blog;

    $cache_settings = array();
    unset( $cache_settings );
    unset( $category_cache );
    unset( $cache_categories );
    unset( $post_cache );
    unset( $wp_object_cache );
    $wp_object_cache = new WP_Object_Cache();
    $wp_object_cache->cache_enabled = false;
    $switched = true;
}

function restore_current_blog() {
    global $table_prefix, $tmpoldblogdetails, $wpdb, $wpmuBaseTablePrefix, $cache_settings, $category_cache, $cache_categories, $post_cache, $wp_object_cache, $blog_id, $switched;
    // backup
    $wpdb->blogid = $tmpoldblogdetails[ 'blogid' ];
    $wpdb->posts = $tmpoldblogdetails[ 'posts' ];
    $wpdb->categories = $tmpoldblogdetails[ 'categories' ];
    $wpdb->post2cat = $tmpoldblogdetails[ 'post2cat' ];
    $wpdb->comments = $tmpoldblogdetails[ 'comments' ];
    $wpdb->links = $tmpoldblogdetails[ 'links' ];
    $wpdb->linkcategories = $tmpoldblogdetails[ 'linkcategories' ];
    $wpdb->options = $tmpoldblogdetails[ 'options' ];
    $wpdb->postmeta = $tmpoldblogdetails[ 'postmeta' ];
    $wpdb->prefix = $tmpoldblogdetails[ 'prefix' ];
    $cache_settings = $tmpoldblogdetails[ 'cache_settings' ];
    $category_cache = $tmpoldblogdetails[ 'category_cache' ];
    $cache_categories = $tmpoldblogdetails[ 'cache_categories' ];
    $table_prefix = $tmpoldblogdetails[ 'table_prefix' ];
    $post_cache      = $tmpoldblogdetails[ 'post_cache' ];
    $wp_object_cache = $tmpoldblogdetails[ 'wp_object_cache' ];
    $blog_id = $tmpoldblogdetails[ 'blog_id' ];
    unset( $tmpoldblogdetails );
    $wp_object_cache->cache_enabled = true;
    $switched = false;
}

function get_users_of_blog( $id ) {
    global $wpdb, $wpmuBaseTablePrefix;
    $users = $wpdb->get_results( "SELECT user_id, user_login, meta_value FROM $wpdb->users, $wpdb->usermeta WHERE " . $wpdb->users . ".ID = " . $wpdb->usermeta . ".user_id AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities' ORDER BY {$wpdb->usermeta}.user_id" );
    return $users;
}

function get_blogs_of_user( $id ) {
    global $wpdb, $wpmuBaseTablePrefix;
    $blogs = $wpdb->get_results( "SELECT domain, REPLACE( REPLACE( meta_key, '$wpmuBaseTablePrefix', '' ), '_capabilities', '' ) as userblog_id  FROM $wpdb->blogs, $wpdb->usermeta WHERE $wpdb->blogs.blog_id = REPLACE( REPLACE( $wpdb->usermeta.meta_key, '$wpmuBaseTablePrefix', '' ), '_capabilities', '' ) AND user_id = '$id' AND meta_key LIKE '%capabilities'" );

    return $blogs;
}

function is_archived( $id ) {
    global $wpdb;
    $archived = $wpdb->get_var( "SELECT archived FROM {$wpdb->blogs} WHERE blog_id = '$id'" );

    return $archived;
}

function update_archived( $id, $archived ) {
    global $wpdb;
    $wpdb->query( "UPDATE {$wpdb->blogs} SET archived = '{$archived}' WHERE blog_id = '$id'" );

    return $archived;
}

function update_blog_status( $id, $pref, $value ) {
    global $wpdb;
    $wpdb->query( "UPDATE {$wpdb->blogs} SET {$pref} = '{$value}' WHERE blog_id = '$id'" );

    return $value;
}

function get_blog_status( $id, $pref ) {
    global $wpdb;
    return $wpdb->get_var( "SELECT $pref FROM {$wpdb->blogs} WHERE blog_id = '$id'" );
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
		if( is_archived( $details[ 'blog_id' ] ) == '1' )
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
	
	$count_ts = get_site_option( "blog_count_ts" );
	if( time() - $count_ts > 86400 ) {
		$count = $wpdb->get_var( "SELECT count(*) as c FROM $wpdb->blogs WHERE site_id = '$id'" );
		update_site_option( "blog_count", $count );
		update_site_option( "blog_count_ts", time() );
	} else {
		$count = get_site_option( "blog_count" );
	}

	return $count;
}

function get_blog_post( $blog_id, $post_id ) {
	global $wpdb, $wpmuBaseTablePrefix;
	
	$cache = wpmu_get_cache( $blog_id."-".$post_id, "get_blog_post" );
	if( is_array( $cache ) && ( time() - $cache[ 'time' ] ) < 10 ) { 
		$post = $cache[ 'value' ];
	} else {
		$post = $wpdb->get_row( "SELECT * FROM {$wpmuBaseTablePrefix}{$blog_id}_posts WHERE ID = '{$post_id}'" );
		wpmu_update_cache( $blog_id."-".$post_id, $post, "get_blog_post" );
	}

	return $post;

}

function add_user_to_blog( $blog_id, $user_id, $role ) {
	global $wpdb, $wpmuBaseTablePrefix;

	$wpdb->query( "INSERT INTO " . $wpdb->usermeta . "( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '$user_id', '" . $wpmuBaseTablePrefix . $blog_id . "_capabilities', 'a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}')" );

}


function get_blog_permalink( $blog_id, $post_id ) {
	global $wpdb, $cache_settings;
	
	$cache = wpmu_get_cache( $blog_id."-".$post_id, "permalink" );
	if( is_array( $cache ) && ( time() - $cache[ 'time' ] ) < 30 ) { // cache for 30 seconds
		$link = $cache[ 'value' ];
	} else {
		switch_to_blog( $blog_id );
		$link = get_permalink( $post_id );
		restore_current_blog();
		wpmu_update_cache( $blog_id."-".$post_id, $link, "permalink" );
	}
	return $link;
}
function wpmu_update_cache( $key, $value, $path ) {
	if( defined( "WPMU_CACHE_PATH" ) ) {
		@mkdir( CONSTANT( "WPMU_CACHE_PATH" ) . "/$path/", 0700 );
		@mkdir( CONSTANT( "WPMU_CACHE_PATH" ) . "/$path/temp/", 0700 );
		$cache_path = CONSTANT( "WPMU_CACHE_PATH" ) . "/$path/" . md5( $key );
		$tmpfname = tempnam( CONSTANT( "WPMU_CACHE_PATH" ) . "/$path/temp/", "tempname");
		$handle = fopen($tmpfname, "w");
		$cache = array( "value" => $value, "time" => time() );
		fwrite( $handle, serialize( $cache ) );
		fclose($handle);
		if( file_exists( $cache_path ) )
			@unlink( $cache_path );
		rename( $tmpfname, $cache_path );
	}
}

function wpmu_get_cache( $key, $path ) {
	if( defined( "WPMU_CACHE_PATH" ) ) {
		$cache_path = CONSTANT( "WPMU_CACHE_PATH" ) . "/$path/" . md5( $key );
		if ( @file_exists( $cache_path ) ) {
			$cache = unserialize( @file_get_contents( $cache_path ) );
			return $cache;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>
