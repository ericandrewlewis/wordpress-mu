<?php

function wpmu_delete_blog($blog_id, $drop = false) {
	global $wpdb, $wpmuBaseTablePrefix;

	if ( $blog_id != $wpdb->blogid ) {
		$switch = true;
		switch_to_blog($blog_id);	
	}

	do_action('delete_blog', $blog_id, $drop);

	$users = get_users_of_blog($blog_id);

	// Remove users from this blog.
	if ( !empty($users) ) foreach ($users as $user) {
		remove_user_from_blog($user->user_id, $blog_id);
	}

	update_blog_status( $wpdb->blogid, 'deleted', 1 );

	if ( $drop ) {
		$drop_tables = array( $wpmuBaseTablePrefix . $blog_id . "_categories",
		$wpmuBaseTablePrefix . $blog_id . "_comments",
		$wpmuBaseTablePrefix . $blog_id . "_linkcategories",
		$wpmuBaseTablePrefix . $blog_id . "_links",
		$wpmuBaseTablePrefix . $blog_id . "_link2cat",
		$wpmuBaseTablePrefix . $blog_id . "_options",
		$wpmuBaseTablePrefix . $blog_id . "_post2cat",
		$wpmuBaseTablePrefix . $blog_id . "_postmeta",
		$wpmuBaseTablePrefix . $blog_id . "_posts",
		$wpmuBaseTablePrefix . $blog_id . "_referer_visitLog",
		$wpmuBaseTablePrefix . $blog_id . "_referer_blacklist" );
		reset( $drop_tables );

		foreach ($drop_tables as $drop_table)
			$wpdb->query( "DROP TABLE IF EXISTS $drop_table" );

		$wpdb->query( "DELETE FROM $wpdb->blogs WHERE blog_id = '$blog_id'" );
		$dir = constant( "ABSPATH" ) . "wp-content/blogs.dir/" . $blog_id ."/files/";
		$dir = rtrim($dir, DIRECTORY_SEPARATOR);
		$top_dir = $dir;
		$stack = array($dir);
		$index = 0;

		while ($index < count($stack)) {
			# Get indexed directory from stack
			$dir = $stack[$index];

			$dh = @ opendir($dir);
			if ($dh) {
				while (($file = @ readdir($dh)) !== false) {
					if ($file == '.' or $file == '..')
						continue;

					if (@ is_dir($dir . DIRECTORY_SEPARATOR . $file))
						$stack[] = $dir . DIRECTORY_SEPARATOR . $file;
					else if (@ is_file($dir . DIRECTORY_SEPARATOR . $file))
						@ unlink($dir . DIRECTORY_SEPARATOR . $file);
				}
			}
			$index++;
		}

		$stack = array_reverse($stack);  // Last added dirs are deepest
		foreach($stack as $dir) {
			if ( $dir != $top_dir)
			@ rmdir($dir);
		}
	}
	$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key='wp_{$blog_id}_autosave_draft_ids'");

	if ( $switch )
		restore_current_blog();
}

function update_blog_public($old_value, $value) {
	global $wpdb;
	$value = (int) $value;
	do_action('update_blog_public');
	update_blog_status( $wpdb->blogid, 'public', $value );
}

add_action('update_option_blog_public', 'update_blog_public', 10, 2);

function wpmu_delete_user($id) {
	global $wpdb;

	$id = (int) $id;
	$user = get_userdata($id);

	do_action('wpmu_delete_user', $id);

	$blogs = get_blogs_of_user($id);

	if ( ! empty($blogs) ) foreach ($blogs as $blog) {
		switch_to_blog($blog->userblog_id);
		remove_user_from_blog($id, $blog->userblog_id);

		$post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_author = $id");

		if ($post_ids) {
			foreach ($post_ids as $post_id)
				wp_delete_post($post_id);
		}

		// Clean links
		$wpdb->query("DELETE FROM $wpdb->links WHERE link_owner = $id");

		restore_current_blog();
	}

	$wpdb->query("DELETE FROM $wpdb->users WHERE ID = $id");
	$wpdb->query("DELETE FROM $wpdb->usermeta WHERE user_id = '$id'");

	wp_cache_delete($id, 'users');
	wp_cache_delete($user->user_login, 'userlogins');

	return true;
}

function wpmu_get_blog_allowedthemes( $blog_id = 0 ) {
	$themes = get_themes();
	if( $blog_id == 0 )
		$blog_allowed_themes = get_option( "allowedthemes" );
	else 
		$blog_allowed_themes = get_blog_option( $blog_id, "allowedthemes" );
	if( !is_array( $blog_allowed_themes ) || empty( $blog_allowed_themes ) ) { // convert old allowed_themes to new allowedthemes
		if( $blog_id == 0 )
			$blog_allowed_themes = get_option( "allowed_themes" );
		else 
			$blog_allowed_themes = get_blog_option( $blog_id, "allowed_themes" );
		if( is_array( $blog_allowed_themes ) ) {
			foreach( $themes as $key => $theme ) {
				$theme_key = wp_specialchars( $theme[ 'Stylesheet' ] );
				if( isset( $blog_allowed_themes[ $key ] ) == true ) {
					$blog_allowedthemes[ $theme_key ] = 1;
				}
			}
			$blog_allowed_themes = $blog_allowedthemes;
			if( $blog_id == 0 ) {
				add_option( "allowedthemes", $blog_allowed_themes );
				delete_option( "allowed_themes" );
			} else {
				add_blog_option( $blog_id, "allowedthemes", $blog_allowed_themes );
				delete_blog_option( $blog_id, "allowed_themes" );
			}
		}
	}
	return $blog_allowed_themes;
}

function update_option_new_admin_email($old_value, $value) {
	if ( $value == get_option( 'admin_email' ) || !is_email( $value ) )
		return;

	$hash = md5( $value.time().mt_rand() );
	$newadminemail = array( 
		"hash" => $hash,
		"newemail" => $value
	);
	update_option( 'adminhash', $newadminemail );
	
	$content = __("Dear user,\n\n
You recently requested to have the administration email address on 
your blog changed.\n
If this is correct, please click on the following link to change it:\n
###ADMIN_URL###\n\n
You can safely ignore and delete this email if you do not want to take this action.\n\n
This email has been sent to ###EMAIL###\n\n
Regards,\n
The Webmaster");
	
	$content = str_replace('###ADMIN_URL###', get_option( "siteurl" ).'/wp-admin/options.php?adminhash='.$hash, $content);
	$content = str_replace('###EMAIL###', $value, $content);
	
	wp_mail( $value, sprintf(__('[%s] New Admin Email Address'), get_option('blogname')), $content );
}			

add_action('update_option_new_admin_email', 'update_option_new_admin_email', 10, 2);

function get_site_allowed_themes() {
	$themes = get_themes();
	$allowed_themes = get_site_option( 'allowedthemes' );
	if( !is_array( $allowed_themes ) || empty( $allowed_themes ) ) {
		$allowed_themes = get_site_option( "allowed_themes" ); // convert old allowed_themes format
		if( !is_array( $allowed_themes ) ) {
			$allowed_themes = array();
		} else {
			foreach( $themes as $key => $theme ) {
				$theme_key = wp_specialchars( $theme[ 'Stylesheet' ] );
				if( isset( $allowed_themes[ $key ] ) == true ) {
					$allowedthemes[ $theme_key ] = 1;
				}
			}
			$allowed_themes = $allowedthemes;
		}
	}

	return $allowed_themes;
}

function get_space_allowed() {
	$spaceAllowed = get_option("blog_upload_space");
	if( $spaceAllowed == false ) 
		$spaceAllowed = get_site_option("blog_upload_space");
	if(empty($spaceAllowed) || !is_numeric($spaceAllowed)) $spaceAllowed = 50;

	return $spaceAllowed;
}

function display_space_usage() {
		$space = get_space_allowed();
		$percentused = ( intval( get_dirsize( constant( "ABSPATH" ) . constant( "UPLOADS" ) )/1024/1024 ) / $space ) * 100;

		if( $space > 1000 ) {
			$space = number_format( $space / 1024 );
			$space .= "GB";
		} else {
			$space .= "MB";
		}
	?>
	<strong>Used: <?php echo number_format( $percentused ) ?>% of <?php echo $space ?></strong>
	<?php
}

?>
