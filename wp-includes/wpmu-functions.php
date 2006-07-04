<?PHP
/*
	Helper functions for WPMU
*/


function wpmu_update_blogs_date() {
	global $wpdb;

	$wpdb->query( "UPDATE {$wpdb->blogs} SET last_updated = NOW() WHERE  blog_id = '{$wpdb->blogid}'" );
	refresh_blog_details( $wpdb->blogid );
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
	$spaceAllowed = get_site_option( "blog_upload_space" );
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

if( defined( "WP_INSTALLING" ) == false ) {
	header( "X-totalblogs: " . get_blog_count() );
	header( "X-rootblog: http://" . $current_site->domain . $current_site->path );
	header( "X-created-on: " . $current_blog->registered );

	if( empty( $WPMU_date ) == false ) 
		header( "X-wpmu-date: $WPMU_date" );
}


function get_blogaddress_by_id( $blog_id ) {
	global $hostname, $domain, $base, $wpdb;

	$bloginfo = get_blog_details( $blog_id, false ); // only get bare details!

	if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
		return "http://" . $bloginfo->domain . $bloginfo->path;
	} else {
		return get_blogaddress_by_domain($bloginfo->domain, $bloginfo->path);
	}
}

function get_blogaddress_by_name( $blogname ) {
	global $hostname, $domain, $base, $wpdb;

	if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
		if( $blogname == 'main' )
			$blogname = 'www';
		return "http://".$blogname.".".$domain.$base;
	} else {
		return "http://".$hostname.$base.$blogname;
	}
}

function get_blogaddress_by_domain( $domain, $path ){
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
		} else { // main blog
			$url = 'http://' . $domain . $path;
		}
	}

	return $url;
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
		$query = "SELECT id FROM ".$wpdb->site." WHERE domain = '".$sitedomain."' AND path = '".$path."'";
		$site_id = $wpdb->get_var( $query );
	}
	if( $site_id != false ) {
		$query = "SELECT ID, user_login, user_pass FROM ".$wpdb->users.", ".$wpdb->sitemeta." WHERE meta_key = 'admin_user_id' AND ".$wpdb->users.".ID = ".$wpdb->sitemeta.".meta_value AND ".$wpdb->sitemeta.".site_id = '".$site_id."'";
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

function get_blog_details( $id, $all = true ) {
	global $wpdb, $wpmuBaseTablePrefix;

	$details = wp_cache_get( $id, 'blog-details' );

	if ( $details )
		return unserialize( $details );

	$details = $wpdb->get_row( "SELECT * FROM $wpdb->blogs WHERE blog_id = '$id' /* get_blog_details */" );

	if ( !$details )
		return false;

	if( $all == true ) {
		$details->blogname   = get_blog_option($id, 'blogname');
		$details->siteurl    = get_blog_option($id, 'siteurl');
		$details->post_count = get_blog_option($id, 'post_count');

		wp_cache_set( $id, serialize( $details ), 'blog-details' );

		$key = md5( $details->domain . $details->path );
		wp_cache_set( $key, serialize( $details ), 'blog-lookup' );
	}

	return $details;
}

function refresh_blog_details( $id ) {
	global $wpdb, $wpmuBaseTablePrefix;
	
	$details = get_blog_details( $id );
	wp_cache_delete( $id , 'blog-details' );

	$key = md5( $details->domain . $details->path );
	wp_cache_delete( $key , 'blog-lookup' );

	//return $details;
}

function get_current_user_id() {
	global $current_user;
	return $current_user->ID;
}

function is_site_admin( $user_login = false ) {
	global $wpdb, $current_user;

	if ( !$current_user && !$user_login )
		return false;

	if ( $user_login )
		$user_login = sanitize_user( $user_login );
	else 
		$user_login = $current_user->user_login;

	$site_admins = get_site_option( 'site_admins', array('admin') );
	if( in_array( $user_login, $site_admins ) )
		return true;

	return false;
}

function get_site_option( $option, $default = false, $use_cache = true ) {
	global $wpdb;

	if( $use_cache == true ) {
		$value = wp_cache_get($option, 'site-options');
	} else {
		$value = false;
	}

	if ( false === $value ) {
		$value = $wpdb->get_var("SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = '$option' AND site_id = '$wpdb->siteid'");
		if ( ! is_null($value) ) {
			wp_cache_set($option, $value, 'site-options');
		} elseif ( $default ) {
			return $default;
		} else {
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
	
	$exists = $wpdb->get_var("SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = '$key' AND site_id = '$wpdb->siteid'");

	if ( null !== $exists ) // If we already have it
		return false;

	if ( is_array($value) || is_object($value) )
		$value = serialize($value);
	$value = $wpdb->escape( $value );
	wp_cache_delete($key, 'site-options');
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

	if ( get_site_option( $key, false, false ) == false )
		add_site_option( $key, $value );

	$wpdb->query( "UPDATE $wpdb->sitemeta SET meta_value = '".$wpdb->escape( $value )."' WHERE meta_key = '$key'" );
	wp_cache_delete( $key, 'site-options' );
}

function get_blog_option( $id, $key, $default='na' ) {
	global $wpdb, $wpmuBaseTablePrefix, $blog_id, $switched;

	$current_blog_id = $blog_id;
	$current_options_table = $wpdb->options;
	$wpdb->options = $wpmuBaseTablePrefix . $id . "_options";
	$blog_id = $id;
	if ($id != $current_blog_id)
		$switched = true;
	$opt = get_option( $key );
	$switched = false;
	$blog_id = $current_blog_id;
	$wpdb->options = $current_options_table;

	return $opt;
}

function add_blog_option( $id, $key, $value ) {
	global $wpdb, $wpmuBaseTablePrefix, $blog_id;

	$current_blog_id = $blog_id;
	$current_options_table = $wpdb->options;
	$wpdb->options = $wpmuBaseTablePrefix . $id . "_options";
	$blog_id = $id;
	$opt = add_option( $key, $value );
	$blog_id = $current_blog_id;
	$wpdb->options = $current_options_table;
}


function update_blog_option( $id, $key, $value ) {
	global $wpdb, $wpmuBaseTablePrefix, $blog_id;

	$current_blog_id = $blog_id;
	$current_options_table = $wpdb->options;
	$wpdb->options = $wpmuBaseTablePrefix . $id . "_options";
	$blog_id = $id;
	$opt = update_option( $key, $value );
	$blog_id = $current_blog_id;
	$wpdb->options = $current_options_table;
	refresh_blog_details( $id );
}

function switch_to_blog( $new_blog ) {
	global $tmpoldblogdetails, $wpdb, $wpmuBaseTablePrefix, $table_prefix, $cache_settings, $category_cache, $cache_categories, $post_cache, $wp_object_cache, $blog_id, $switched, $wp_roles;

	// backup
	$tmpoldblogdetails[ 'blogid' ]         = $wpdb->blogid;
	$tmpoldblogdetails[ 'posts' ]          = $wpdb->posts;
	$tmpoldblogdetails[ 'categories' ]     = $wpdb->categories;
	$tmpoldblogdetails[ 'post2cat' ]       = $wpdb->post2cat;
	$tmpoldblogdetails[ 'comments' ]       = $wpdb->comments;
	$tmpoldblogdetails[ 'links' ]          = $wpdb->links;
	$tmpoldblogdetails[ 'link2cat' ]       = $wpdb->link2cat;
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
	$wpdb->prefix			= $table_prefix;
	$wpdb->blogid           = $new_blog;
	$wpdb->posts            = $table_prefix . 'posts';
	$wpdb->categories       = $table_prefix . 'categories';
	$wpdb->post2cat         = $table_prefix . 'post2cat';
	$wpdb->comments         = $table_prefix . 'comments';
	$wpdb->links            = $table_prefix . 'links';
	$wpdb->link2cat         = $table_prefix . 'link2cat';
	$wpdb->linkcategories   = $table_prefix . 'linkcategories';
	$wpdb->options          = $table_prefix . 'options';
	$wpdb->postmeta         = $table_prefix . 'postmeta';
	$blog_id = $new_blog;

	$cache_settings = array();
	unset( $cache_settings );
	unset( $category_cache );
	unset( $cache_categories );
	unset( $post_cache );
	//unset( $wp_object_cache );
	//$wp_object_cache = new WP_Object_Cache();
	//$wp_object_cache->cache_enabled = false;
	wp_cache_flush();
	wp_cache_close();
	if( is_object( $wp_roles ) ) {
		$wpdb->hide_errors();
		$wp_roles->_init();
		$wpdb->show_errors();
	}
	wp_cache_init();

	do_action('switch_blog', $blog_id, $tmpoldblogdetails[ 'blog_id' ]);

	$switched = true;
}

function restore_current_blog() {
	global $table_prefix, $tmpoldblogdetails, $wpdb, $wpmuBaseTablePrefix, $cache_settings, $category_cache, $cache_categories, $post_cache, $wp_object_cache, $blog_id, $switched, $wp_roles;
	// backup
	$wpdb->blogid = $tmpoldblogdetails[ 'blogid' ];
	$wpdb->posts = $tmpoldblogdetails[ 'posts' ];
	$wpdb->categories = $tmpoldblogdetails[ 'categories' ];
	$wpdb->post2cat = $tmpoldblogdetails[ 'post2cat' ];
	$wpdb->comments = $tmpoldblogdetails[ 'comments' ];
	$wpdb->links = $tmpoldblogdetails[ 'links' ];
	$wpdb->link2cat = $tmpoldblogdetails[ 'link2cat' ];
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
	$prev_blog_id = $blog_id;
	$blog_id = $tmpoldblogdetails[ 'blog_id' ];
	unset( $tmpoldblogdetails );
	wp_cache_flush();
	wp_cache_close();
	if( is_object( $wp_roles ) ) {
		$wpdb->hide_errors();
		$wp_roles->_init();
		$wpdb->show_errors();
	}
	wp_cache_init();

	do_action('switch_blog', $blog_id, $prev_blog_id);

	$switched = false;
}

function get_users_of_blog( $id = '' ) {
	global $wpdb, $wpmuBaseTablePrefix;
	if ( empty($id) )
		$id = $wpdb->blogid;
	$users = $wpdb->get_results( "SELECT user_id, user_login, user_email, meta_value FROM $wpdb->users, $wpdb->usermeta WHERE " . $wpdb->users . ".ID = " . $wpdb->usermeta . ".user_id AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities' ORDER BY {$wpdb->usermeta}.user_id" );
	return $users;
}

function get_blogs_of_user( $id ) {
	global $wpdb, $wpmuBaseTablePrefix;
	
	$user = get_userdata( $id );
	$blogs = array();

	$i = 0;
	foreach ( $user as $key => $value ) {
		if ( strstr( $key, '_capabilities') && strstr( $key, 'wp_') ) {
			preg_match('/wp_(\d+)_capabilities/', $key, $match);
			$blog = get_blog_details( $match[1] );
			if ( $blog && isset( $blog->domain ) ) {
				$blogs[$match[1]]->userblog_id = $match[1];
				$blogs[$match[1]]->domain      = $blog->domain;
				$blogs[$match[1]]->path        = $blog->path;
			}
		}
	}

	return $blogs;
}

function is_archived( $id ) {
	return get_blog_status($id, 'archived');
}

function update_archived( $id, $archived ) {
	update_blog_status($id, 'archived', $archived);

	return $archived;
}

function update_blog_status( $id, $pref, $value ) {
	global $wpdb;

	$wpdb->query( "UPDATE {$wpdb->blogs} SET {$pref} = '{$value}', last_updated = NOW() WHERE blog_id = '$id'" );

	refresh_blog_details($id);
	
	if( $pref == 'spam' ) {
		if( $value == 1 ) {
			do_action( "make_spam_blog", $id );
		} else {
			do_action( "make_ham_blog", $id );
		}
	}

	return $value;
}

function get_blog_status( $id, $pref ) {
	global $wpdb;

	$details = get_blog_details( $id );
	if( $details ) {
		return $details->$pref;
	}

	return $wpdb->get_var( "SELECT $pref FROM {$wpdb->blogs} WHERE blog_id = '$id'" );
}

function get_last_updated( $display = false ) {
	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = '$wpdb->siteid' AND last_updated != '0000-00-00 00:00:00' ORDER BY last_updated DESC limit 0,40", ARRAY_A );
	if( is_array( $blogs ) ) {
		while( list( $key, $details ) = each( $blogs ) ) { 
			if( get_blog_status( $details[ 'blog_id' ], 'archived' ) == '1' )
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
				$blog_list[ $details[ 'blog_id' ] ][ 'postcount' ] = $wpdb->get_var( "SELECT count(*) FROM " . $wpmuBaseTablePrefix . $details[ 'blog_id' ] . "_posts WHERE post_status='publish' AND post_type='post'" );
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

/*
	if( $id == 0 )
		$id = $wpdb->siteid;
	
	$count_ts = get_site_option( "blog_count_ts" );
	if( time() - $count_ts > 86400 ) {
		$count = $wpdb->get_var( "SELECT count(*) as c FROM $wpdb->blogs WHERE site_id = '$id' AND spam='0' AND deleted='0' and archived='0'" );
		update_site_option( "blog_count", $count );
		update_site_option( "blog_count_ts", time() );
	} else {
		
	} 
*/

	$count = get_site_option( "blog_count" );

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
	global $wpdb;

	$switch = false;

	if ( empty($blog_id) )
		$blog_id = $wpdb->blogid;

	if ( $blog_id != $wpdb->blogid ) {
		$switch = true;
		switch_to_blog($blog_id);	
	}

	$user = new WP_User($user_id);

	if ( empty($user) )
		return new WP_Error('user_does_not_exist', __('That user does not exist.'));

	if ( !get_usermeta($user_id, 'primary_blog') ) {
		update_usermeta($user_id, 'primary_blog', $blog_id);
		$details = get_blog_details($blog_id);
		update_usermeta($user_id, 'source_domain', $details->domain);
	}

	if ( empty($user->user_url) ) {
		$userdata = array();
		$userdata['ID'] = $user->id;
		$userdata['user_url'] = get_blogaddress_by_id($blog_id);
		wp_update_user($userdata);
	}

	$user->set_role($role);

	do_action('add_user_to_blog', $user_id, $role, $blog_id);

	if ( $switch )
		restore_current_blog();
}
 
function remove_user_from_blog($user_id, $blog_id = '') {
	global $wpdb;
	if ( empty($blog_id) )	
		$blog_id = $wpdb->blogid;

	$blog_id = (int) $blog_id;

	if ( $blog_id != $wpdb->blogid ) {
		$switch = true;
		switch_to_blog($blog_id);	
	}

	$user_id = (int) $user_id;

	do_action('remove_user_from_blog', $user_id, $blog_id);

	// If being removed from the primary blog, set a new primary if the user is assigned
	// to multiple blogs.
	$primary_blog = get_usermeta($user_id, 'primary_blog');
	if ( $primary_blog == $blog_id ) {
		$new_id = '';
		$new_domain = '';
		$blogs = get_blogs_of_user($user_id);
		if ( count($blogs) > 1 ) {		
			foreach ( $blogs as $blog ) {
				if ( $blog->userblog_id == $blog_id )
					continue;
				 $new_id = $blog->userblog_id;
				 $new_domain = $blog->domain;
				 break;
			}
		}
		update_usermeta($user_id, 'primary_blog', $new_id);
		update_usermeta($user_id, 'source_domain', $new_domain);
	}

	wp_revoke_user($user_id);

	$blogs = get_blogs_of_user($user_id);
	if ( count($blogs) == 0 ) {
		update_usermeta($user_id, 'primary_blog', '');
		update_usermeta($user_id, 'source_domain', '');
	}

	if ( $switch )
		restore_current_blog();
}

function create_empty_blog( $domain, $path, $weblog_title, $site_id = 1 ) {
	global $wpdb, $table_prefix, $wp_queries, $wpmuBaseTablePrefix, $current_site;

	$domain       = addslashes( $domain );
	$weblog_title = addslashes( $weblog_title );

	if( empty($path) )
		$path = '/';

	// Check if the domain has been used already. We should return an error message.
	if ( domain_exists($domain, $path, $site_id) )
		return 'error: Blog URL already taken.';

	// Need to backup wpdb table names, and create a new wp_blogs entry for new blog.
	// Need to get blog_id from wp_blogs, and create new table names.
	// Must restore table names at the end of function.

	if ( ! $blog_id = insert_blog($domain, $path, $site_id) )
		return "error: problem creating blog entry";

	switch_to_blog($blog_id);

	install_blog($blog_id);

	restore_current_blog();

	return true;
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

// wpmu admin functions

function wpmu_admin_do_redirect( $url = '' ) {
	$url = wpmu_admin_redirect_add_updated_param( $url );
	if( isset( $_GET[ 'redirect' ] ) ) {
		if( substr( $_GET[ 'redirect' ], 0, 2 ) == 's_' ) {
			$url .= "&action=blogs&s=". wp_specialchars( substr( $_GET[ 'redirect' ], 2 ) );
		}
	} elseif( isset( $_POST[ 'redirect' ] ) ) {
		$url = wpmu_admin_redirect_add_updated_param( $_POST[ 'redirect' ] );
	}
	header( "Location: {$url}" );
	die();
}
function wpmu_admin_redirect_add_updated_param( $url = '' ) {
	if( strpos( $url, 'updated=true' ) === false ) {
		if( strpos( $url, '?' ) === false ) {
			return $url . '?updated=true';
		} else {
			return $url . '&updated=true';
		}
		return $url;
	}
}

function wpmu_admin_redirect_url() {
	if( isset( $_GET[ 's' ] ) ) {
		return "s_".$_GET[ 's' ];
	}
}

function is_blog_user() {
	global $current_user, $wpdb, $wpmuBaseTablePrefix;

	$cap_key = $wpmuBaseTablePrefix . $wpdb->blogid . '_capabilities';

	if ( is_array($current_user->$cap_key) && in_array(1, $current_user->$cap_key) )
		return true;

	return false;
}

function validate_email( $email, $check_domain = true) {
    if (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
        '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
        '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email))
    {
        if ($check_domain && function_exists('checkdnsrr')) {
            list (, $domain)  = explode('@', $email);
        
            if (checkdnsrr($domain.'.', 'MX') || checkdnsrr($domain.'.', 'A')) {
                return true;
            }
            return false;
        }
        return true;
    }
    return false;
}

function wpmu_validate_user_signup($user_name, $user_email) {
	global $wpdb;

	$errors = new WP_Error();

	$user_name = sanitize_title($user_name);

	if ( empty( $user_name ) )
	   	$errors->add('user_name', __("Please enter a username"));

	preg_match( "/[a-zA-Z0-9]+/", $user_name, $maybe );

	if( $user_name != $maybe[0] ) {
	    $errors->add('user_name', __("Only letters and numbers allowed"));
	}

	$illegal_names = get_site_option( "illegal_names" );
	if( is_array( $illegal_names ) == false ) {
		$illegal_names = array(  "www", "web", "root", "admin", "main", "invite", "administrator" );
		add_site_option( "illegal_names", $illegal_names );
	}
	if( in_array( $user_name, $illegal_names ) == true ) {
	    $errors->add('user_name',  __("That username is not allowed"));
	}

	if( strlen( $user_name ) < 4 ) {
	    $errors->add('user_name',  __("Username must be at least 4 characters"));
	}

	if ( strpos( " " . $user_name, "_" ) != false )
		$errors->add('user_name', __("Sorry, usernames may not contain the character '_'!"));

	// all numeric?
	preg_match( '/[0-9]*/', $user_name, $match );
	if ( $match[0] == $user_name )
		$errors->add('user_name', __("Sorry, usernames must have letters too!"));
		
	if ( !is_email( $user_email ) )
	    $errors->add('user_email', __("Please enter a correct email address"));

	if ( !validate_email( $user_email ) )
		$errors->add('user_email', __("Please check your email address."));

	$limited_email_domains = get_site_option( 'limited_email_domains' );
	if ( is_array( $limited_email_domains ) && empty( $limited_email_domains ) == false ) {
		$emaildomain = substr( $user_email, 1 + strpos( $user_email, '@' ) );
		if( in_array( $emaildomain, $limited_email_domains ) == false ) {
			$errors->add('user_email', __("Sorry, that email address is not allowed!"));
		}
	}

	// Check if the username has been used already.
	if ( username_exists($user_name) )
		$errors->add('user_name', __("Sorry, that username already exists!"));

	// Check if the email address has been used already.
	if ( email_exists($user_email) )
		$errors->add('user_email', __("Sorry, that email address is already used!"));

	// Has someone already signed up for this username?
	$signup = $wpdb->get_row("SELECT * FROM $wpdb->signups WHERE user_login = '$user_name'");
	if ( $signup != null ) {
		$registered_at =  mysql2date('U', $signup->registered);
		$now = current_time( 'timestamp', true );
		$diff = $now - $registered_at;
		// If registered more than two days ago, cancel registration and let this signup go through.
		if ( $diff > 172800 ) {
			$wpdb->query("DELETE FROM $wpdb->signups WHERE user_login = '$user_name'");
		} else {
			$errors->add('user_name', __("That username is currently reserved but may be available in a couple of days."));
		}
	}

	$signup = $wpdb->get_row("SELECT * FROM $wpdb->signups WHERE user_email = '$user_email'");
	if ( $signup != null ) {
		$registered_at =  mysql2date('U', $signup->registered);
		$now = current_time( 'timestamp', true );
		$diff = $now - $registered_at;
		// If registered more than two days ago, cancel registration and let this signup go through.
		if ( $diff > 172800 ) {
			$wpdb->query("DELETE FROM $wpdb->signups WHERE user_email = '$user_email'");
		} else {
			$errors->add('user_email', __("That email address has already been used. Please check your inbox for an activation email. It will become available in a couple of days if you do nothing."));
		}
	}

	$result = array('user_name' => $user_name, 'user_email' => $user_email,	'errors' => $errors);

	return apply_filters('wpmu_validate_user_signup', $result);
}

function wpmu_validate_blog_signup($blog_id, $blog_title, $user = '') {
	global $wpdb, $domain, $base;

	$errors = new WP_Error();
	$illegal_names = get_site_option( "illegal_names" );
	if( $illegal_names == false ) {
	    $illegal_names = array( "www", "web", "root", "admin", "main", "invite", "administrator" );
	    add_site_option( "illegal_names", $illegal_names );
	}

	$blog_id = sanitize_title($blog_id);

	if ( empty( $blog_id ) )
	    $errors->add('blog_id', __("Please enter a blog name"));

	preg_match( "/[a-zA-Z0-9]+/", $blog_id, $maybe );
	if( $blog_id != $maybe[0] ) {
	    $errors->add('blog_id', __("Only letters and numbers allowed"));
	}
	if( in_array( $blog_id, $illegal_names ) == true ) {
	    $errors->add('blog_id',  __("That name is not allowed"));
	}
	if( strlen( $blog_id ) < 4 ) {
	    $errors->add('blog_id',  __("Blog name must be at least 4 characters"));
	}

	if ( strpos( " " . $blog_id, "_" ) != false )
		$errors->add('blog_id', __("Sorry, blog names may not contain the character '_'!"));

	// all numeric?
	preg_match( '/[0-9]*/', $blog_id, $match );
	if ( $match[0] == $blog_id )
		$errors->add('blog_id', __("Sorry, blog names must have letters too!"));

	$blog_id = apply_filters( "newblog_id", $blog_id );

	$blog_title = stripslashes(  $blog_title );

	if ( empty( $blog_title ) )
	    $errors->add('blog_title', __("Please enter a blog title"));

	// Check if the domain/path has been used already.
	if( constant( "VHOST" ) == 'yes' ) {
		$mydomain = "$blog_id.$domain";
		$path = $base;
	} else {
		$mydomain = "$domain";
		$path = $base.$blog_id.'/';
	}
	if ( domain_exists($mydomain, $path) )
		$errors->add('blog_id', __("Sorry, that blog already exists!"));

	if ( username_exists($blog_id) ) {
		if  ( !is_object($user) && ( $user->user_login != $blog_id ) )
			$errors->add('blog_id', __("Sorry, that blog is reserved!"));
	}

	// Has someone already signed up for this domain?
	// TODO: Check email too?
	$signup = $wpdb->get_row("SELECT * FROM $wpdb->signups WHERE domain = '$mydomain' AND path = '$path'");
	if ( ! empty($signup) ) {
		$registered_at =  mysql2date('U', $signup->registered);
		$now = current_time( 'timestamp', true );
		$diff = $now - $registered_at;
		// If registered more than two days ago, cancel registration and let this signup go through.
		if ( $diff > 172800 ) {
			$wpdb->query("DELETE FROM $wpdb->signups WHERE domain = '$mydomain' AND path = '$path'");
		} else {
			$errors->add('blog_id', __("That blog is currently reserved but may be available in a couple days."));
		}
	}

	$result = array('domain' => $mydomain, 'path' => $path, 'blog_id' => $blog_id, 'blog_title' => $blog_title,
				'errors' => $errors);

	return apply_filters('wpmu_validate_blog_signup', $result);
}

// Record signup information for future activation. wpmu_validate_signup() should be run
// on the inputs before calling wpmu_signup().
function wpmu_signup_blog($domain, $path, $title, $user, $user_email, $meta = '') {
	global $wpdb;

	$key = substr( md5( time() . rand() . $domain ), 0, 16 );
	$registered = current_time('mysql', true);
	$meta = serialize($meta);
	$domain = $wpdb->escape($domain);
	$path = $wpdb->escape($path);
	$title = $wpdb->escape($title);
	$wpdb->query( "INSERT INTO $wpdb->signups ( domain, path, title, user_login, user_email, registered, activation_key, meta )
					VALUES ( '$domain', '$path', '$title', '$user', '$user_email', '$registered', '$key', '$meta' )" );

	wpmu_signup_blog_notification($domain, $path, $title, $user, $user_email, $key, $meta);
}

function wpmu_signup_user($user, $user_email, $meta = '') {
	global $wpdb;

	$key = substr( md5( time() . rand() . $user_email ), 0, 16 );
	$registered = current_time('mysql', true);
	$meta = serialize($meta);
	$wpdb->query( "INSERT INTO $wpdb->signups ( domain, path, title, user_login, user_email, registered, activation_key, meta )
					VALUES ( '', '', '', '$user', '$user_email', '$registered', '$key', '$meta' )" );

	wpmu_signup_user_notification($user, $user_email, $key, $meta);
}

// Notify user of signup success.
function wpmu_signup_blog_notification($domain, $path, $title, $user, $user_email, $key, $meta = '') {
	global $current_site;
	// Send email with activation link.
	if( constant( "VHOST" ) == 'no' ) {
		$activate_url = "http://" . $current_site->domain . $current_site->path . "wp-activate.php?key=$key";
	} else {
		$activate_url = "http://{$domain}{$path}wp-activate.php?key=$key";
	}
	$message_headers = 'From: ' . stripslashes($title) . ' <support@' . $_SERVER[ 'SERVER_NAME' ] . '>';
	$message = sprintf(__("To activate your blog, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login.\n\nAfter you activate, you can visit your blog here:\n\n%s"), $activate_url, "http://{$domain}{$path}");
	// TODO: Don't hard code activation link.
	$subject = sprintf(__('Activate %s'), $domain.$path);
	wp_mail($user_email, $subject, $message, $message_headers);
}

function wpmu_signup_user_notification($user, $user_email, $key, $meta = '') {
	// Send email with activation link.
	$message_headers = 'From: ' . stripslashes($user) . ' <support@' . $_SERVER[ 'SERVER_NAME' ] . '>';
	$message = sprintf(__("To activate your user, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login.\n\n"), "http://{$_SERVER[ 'SERVER_NAME' ]}/wp-activate.php?key=$key" );
	// TODO: Don't hard code activation link.
	$subject = sprintf(__('Activate %s'), $user);
	wp_mail($user_email, $subject, $message, $message_headers);
}

function wpmu_activate_signup($key) {
	global $wpdb;

	$result = array();
	$signup = $wpdb->get_row("SELECT * FROM $wpdb->signups WHERE activation_key = '$key'");

	if ( empty($signup) )
		return new WP_Error('invalid_key', __('Invalid activation key.'));

	if ( $signup->active )
		return new WP_Error('already_active', __('The blog is already active.'));

	$user_login = $wpdb->escape($signup->user_login);
	$user_email = $wpdb->escape($signup->user_email);
	$password = generate_random_password();

	$user_id = username_exists($user_login);

	if ( ! $user_id )
		$user_id = wpmu_create_user($user_login, $password, $user_email);

	if ( ! $user_id )
		return new WP_Error('create_user', __('Could not create user'));

	$now = current_time('mysql', true);

	if ( empty($signup->domain) ) {
		$wpdb->query("UPDATE $wpdb->signups SET active = '1', activated = '$now' WHERE activation_key = '$key'");
		wpmu_welcome_user_notification($user_id, $password, $meta);
		do_action('wpmu_activate_user', $user_id, $password, $meta);
		return array('user_id' => $user_id, 'password' => $password, 'meta' => $meta);
	}

	$meta = unserialize($signup->meta);	
	$blog_id = wpmu_create_blog($signup->domain, $signup->path, $signup->title, $user_id, $meta);

	// TODO: What to do if we create a user but cannot create a blog?
	if ( is_wp_error($blog_id) )
		return $blog_id;

	$wpdb->query("UPDATE $wpdb->signups SET active = '1', activated = '$now' WHERE activation_key = '$key'");

	wpmu_welcome_notification($blog_id, $user_id, $password, $signup->title, $meta);

	do_action('wpmu_activate_blog', $blog_id, $user_id, $password, $signup->title, $meta);

	return array('blog_id' => $blog_id, 'user_id' => $user_id, 'password' => $password, 'title' => $signup->title, 'meta' => $meta);
}

function generate_random_password() {
	$random_password = substr(md5(uniqid(microtime())), 0, 6);
	$random_password = apply_filters('random_password', $random_password);
	return $random_password;
}

function wpmu_create_user( $user_name, $password, $email) {
	if ( username_exists($user_name) )
		return false;
	
	// Check if the email address has been used already.
	if ( email_exists($email) )
		return false;


	$user_id = wp_create_user( $user_name, $password, $email );
	$user = new WP_User($user_id);
	// Newly created users have no roles or caps until they are added to a blog.
	update_user_option($user_id, 'capabilities', '');
	update_user_option($user_id, 'user_level', '');

	do_action( 'wpmu_new_user', $user_id );
	
	return $user_id;
}

function wpmu_create_blog($domain, $path, $title, $user_id, $meta = '', $site_id = 1) {
	$domain = addslashes( $domain );
	$title = addslashes( $title );
	$user_id = (int) $user_id;

	if( empty($path) )
		$path = '/';

	// Check if the domain has been used already. We should return an error message.
	if ( domain_exists($domain, $path, $site_id) )
		return new WP_Error('blog_taken', __('Blog already exists.'));

	// Need to backup wpdb table names, and create a new wp_blogs entry for new blog.
	// Need to get blog_id from wp_blogs, and create new table names.
	// Must restore table names at the end of function.

	if ( ! $blog_id = insert_blog($domain, $path, $site_id) )
		return new WP_Error('insert_blog', __('Could not create blog.'));

	//define( "WP_INSTALLING", true );
	switch_to_blog($blog_id);

	install_blog($blog_id, $title);

	install_blog_defaults($blog_id, $user_id);

	add_user_to_blog($blog_id, $user_id, 'administrator');

	restore_current_blog();

	if ( is_array($meta) ) foreach ($meta as $key => $value) {
		update_blog_status( $blog_id, $key, $value );
		update_blog_option( $blog_id, $key, $value );
	}

	do_action( 'wpmu_new_blog', $blog_id, $user_id );

	return $blog_id;
}

function domain_exists($domain, $path, $site_id = 1) {
	global $wpdb;
	return $wpdb->get_var("SELECT blog_id FROM $wpdb->blogs WHERE domain = '$domain' AND path = '$path' AND site_id = '$site_id'" );
}

function insert_blog($domain, $path, $site_id) {
	global $wpdb;
	$path = trailingslashit( $path );
	$query = "INSERT INTO $wpdb->blogs ( blog_id, site_id, domain, path, registered ) VALUES ( NULL, '$site_id', '$domain', '$path', NOW( ))";
	$result = $wpdb->query( $query );
	if ( ! $result )
		return false;
		
	$id = $wpdb->insert_id;
	refresh_blog_details($id);
	return $id;
}

// Install an empty blog.  wpdb should already be switched.
function install_blog($blog_id, $blog_title = '') {
	global $wpdb, $table_prefix, $wp_roles;
	$wpdb->hide_errors();

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php');
	$installed = $wpdb->get_results("SELECT * FROM $wpdb->posts");
	if ($installed) die(__('<h1>Already Installed</h1><p>You appear to have already installed WordPress. To reinstall please clear your old database tables first.</p>') . '</body></html>');

	$url = get_blogaddress_by_id($blog_id);
	error_log("install_blog - ID: $blog_id  URL: $url Title: $blog_title ", 0);

	// Set everything up
	make_db_current_silent();
	populate_options();
	populate_roles();
	$wp_roles->_init();
	// fix url.
	update_option('siteurl', $url);
	update_option('home', $url);
	update_option('fileupload_url', $url . "files" );
	update_option('blogname', $blog_title);

	$wpdb->query("UPDATE $wpdb->options SET option_value = '".$blog_title."' WHERE option_name = 'blogname'");
	$wpdb->query("UPDATE $wpdb->options SET option_value = '' WHERE option_name = 'admin_email'");

	// Default category
	$wpdb->query("INSERT INTO $wpdb->categories (cat_ID, cat_name, category_nicename, category_count) VALUES ('0', '".addslashes(__('Uncategorized'))."', '".sanitize_title(__('Uncategorized'))."', 1)");
	$blogroll_id = $wpdb->get_var( "SELECT cat_ID FROM {$wpdb->sitecategories} WHERE category_nicename = 'blogroll'" );
	if( $blogroll_id == null ) {
		$wpdb->query( "INSERT INTO " . $wpdb->sitecategories . " VALUES (0, 'Blogroll', 'blogroll', '')" );
		$blogroll_id = $wpdb->insert_id;
	}
	$wpdb->query("INSERT INTO $wpdb->categories (cat_ID, cat_name, category_nicename, link_count) VALUES ('{$blogroll_id}', '".addslashes(__('Blogroll'))."', '".sanitize_title(__('Blogroll'))."', 2)");
	$wpdb->query("INSERT INTO $wpdb->link2cat (link_id, category_id) VALUES (1, $blogroll_id)");
	$wpdb->query("INSERT INTO $wpdb->link2cat (link_id, category_id) VALUES (2, $blogroll_id)");

	// remove all perms
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '".$table_prefix."user_level'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '".$table_prefix."capabilities'" );

	$wpdb->show_errors();	
}

function install_blog_defaults($blog_id, $user_id) {
	global $wpdb, $wp_rewrite, $current_site, $table_prefix, $wpmuBaseTablePrefix;

	$wpdb->hide_errors();

	// Default links
	$wpdb->query("INSERT INTO $wpdb->linkcategories (cat_id, cat_name) VALUES (1, '".addslashes(__('Blogroll'))."')");
	$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_owner, link_rss) VALUES ('http://wordpress.com/', 'WordPress.com', 1, '$user_id', 'http://wordpress.com/feed/');");
	$wpdb->query("INSERT INTO $wpdb->links (link_url, link_name, link_category, link_owner, link_rss) VALUES ('http://wordpress.org/', 'WordPress.org', 1, '$user_id', 'http://wordpress.org/development/feed/');");

	// First post
	$now = date('Y-m-d H:i:s');
	$now_gmt = gmdate('Y-m-d H:i:s');
	$first_post = get_site_option( 'first_post' );
	if( $first_post == false )
		$first_post = stripslashes( __( 'Welcome to <a href="SITE_URL">SITE_NAME</a>. This is your first post. Edit or delete it, then start blogging!' ) );

	$first_post = str_replace( "SITE_URL", "http://" . $current_site->domain . $current_site->path, $first_post );
	$first_post = str_replace( "SITE_NAME", $current_site->site_name, $first_post );
	$first_post = stripslashes( $first_post );

	$wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_name, post_modified, post_modified_gmt, comment_count) VALUES ('".$user_id."', '$now', '$now_gmt', '".addslashes($first_post)."', '".addslashes(__('Hello world!'))."', '0', '".addslashes(__('hello-world'))."', '$now', '$now_gmt', '1')");
	$wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (1, 1, 1)" );
	update_option( "post_count", 1 );

	// First page	
	$wpdb->query("INSERT INTO $wpdb->posts (post_author, post_date, post_date_gmt, post_content, post_excerpt, post_title, post_category, post_name, post_modified, post_modified_gmt, post_status, post_type, to_ping, pinged, post_content_filtered) VALUES ('$user_id', '$now', '$now_gmt', '".$wpdb->escape(__('This is an example of a WordPress page, you could edit this to put information about yourself or your site so readers know where you are coming from. You can create as many pages like this one or sub-pages as you like and manage all of your content inside of WordPress.'))."', '', '".$wpdb->escape(__('About'))."', '0', '".$wpdb->escape(__('about'))."', '$now', '$now_gmt', 'publish', 'page', '', '', '')");
	$wpdb->query( "INSERT INTO $wpdb->post2cat (`rel_id`, `post_id`, `category_id`) VALUES (2, 2, 1)" );
	// Flush rules to pick up the new page.
	$wp_rewrite->init();
	$wp_rewrite->flush_rules();

	// Default comment
	$wpdb->query("INSERT INTO $wpdb->comments (comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content) VALUES ('1', '".addslashes(__('Mr WordPress'))."', '', 'http://" . $current_site->domain . $current_site->path . "', '127.0.0.1', '$now', '$now_gmt', '".addslashes(__('Hi, this is a comment.<br />To delete a comment, just log in, and view the posts\' comments, there you will have the option to edit or delete them.'))."')");

	$user = new WP_User($user_id);
	$wpdb->query("UPDATE $wpdb->options SET option_value = '$user->user_email' WHERE option_name = 'admin_email'");

	// Remove all perms except for the login user.
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id != '".$user_id."' AND meta_key = '".$table_prefix."user_level'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id != '".$user_id."' AND meta_key = '".$table_prefix."capabilities'" );
	// Delete any caps that snuck into the previously active blog. (Hardcoded to blog 1 for now.) TODO: Get previous_blog_id.
	if ( $user_id != 1 )
		$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE  user_id = '".$user_id."' AND meta_key = '" . $wpmuBaseTablePrefix . "1_capabilities'" );

	$wpdb->show_errors();
}

function wpmu_welcome_notification($blog_id, $user_id, $password, $title, $meta = '') {
	global $current_site;

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

	$url = get_blogaddress_by_id($blog_id);
	$user = new WP_User($user_id);

	$welcome_email = str_replace( "SITE_NAME", $current_site->site_name, $welcome_email );
	$welcome_email = str_replace( "BLOG_URL", $url, $welcome_email );
	$welcome_email = str_replace( "USERNAME", $user->user_login, $welcome_email );
	$welcome_email = str_replace( "PASSWORD", $password, $welcome_email );

	$welcome_email = apply_filters( "update_welcome_email", $welcome_email, $blog_id, $user_id, $password, $title, $meta);
	$message_headers = 'From: ' . $title . ' <support@' . $_SERVER[ 'SERVER_NAME' ] . '>';
	$message = $welcome_email;
	if( empty( $current_site->site_name ) )
		$current_site->site_name = "WordPress MU";
	$subject = sprintf(__('New %s Blog: %s'), $current_site->site_name, $title);
	wp_mail($user->user_email, $subject, $message, $message_headers);	
}

function wpmu_welcome_user_notification($user_id, $password, $meta = '') {
	global $current_site;

	$welcome_email = __( "Dear User,
		
Your new account is setup.

You can log in with the following information:
Username: USERNAME
Password: PASSWORD

Thanks!

--The WordPress Team
SITE_NAME" );

	$user = new WP_User($user_id);

	$welcome_email = apply_filters( "update_welcome_user_email", $welcome_email, $user_id, $password, $meta);
	$welcome_email = str_replace( "SITE_NAME", $current_site->site_name, $welcome_email );
	$welcome_email = str_replace( "USERNAME", $user->user_login, $welcome_email );
	$welcome_email = str_replace( "PASSWORD", $password, $welcome_email );

	$message_headers = 'From: ' . $title . ' <support@' . $_SERVER[ 'SERVER_NAME' ] . '>';
	$message = $welcome_email;
	if( empty( $current_site->site_name ) )
		$current_site->site_name = "WordPress MU";
	$subject = sprintf(__('New %s User: %s'), $current_site->site_name, $user->user_login);
	wp_mail($user->user_email, $subject, $message, $message_headers);	
}

?>
