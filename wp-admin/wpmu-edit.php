<?php
require_once('admin.php');

do_action( "wpmuadminedit", "" );

$_POST[ 'id' ] = intval( $_POST[ 'id' ] );
$_GET[ 'id' ] = intval( $_GET[ 'id' ] );
$id = $_POST[ 'id' ];

switch( $_GET[ 'action' ] ) {
	case "siteoptions":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}

		update_site_option( "WPLANG", $_POST[ 'WPLANG' ] );
		update_site_option( "illegal_names", split( ' ', $_POST[ 'illegal_names' ] ) );
		if( $_POST[ 'limited_email_domains' ] != '' ) {
			update_site_option( "limited_email_domains", split( ' ', $_POST[ 'limited_email_domains' ] ) );
		} else {
			update_site_option( "limited_email_domains", '' );
		}
		update_site_option( "menu_items", $_POST[ 'menu_items' ] );
		update_site_option( "blog_upload_space", $_POST[ 'blog_upload_space' ] );
		update_site_option( "upload_filetypes", $_POST[ 'upload_filetypes' ] );
		update_site_option( "site_name", $_POST[ 'site_name' ] );
		update_site_option( "first_post", $_POST[ 'first_post' ] );
		update_site_option( "welcome_email", $_POST[ 'welcome_email' ] );
		update_site_option( "fileupload_maxk", $_POST[ 'fileupload_maxk' ] );
		$site_admins = explode( ' ', $_POST['site_admins'] );
		if ( is_array( $site_admins ) )
			update_site_option( 'site_admins' , $site_admins );
		wpmu_admin_do_redirect( "wpmu-options.php" );
		exit;
	break;
	case "searchcategories":
		$search = wp_specialchars( $_POST[ 'search' ] );
		$query = "SELECT cat_name FROM " . $wpdb->sitecategories . " WHERE cat_name LIKE '%" . $search . "%' limit 0,10";
		$cats = $wpdb->get_results( $query );
		if( is_array( $cats ) ) {
			print "<ul>";
			while( list( $key, $val ) = each( $cats ) ) 
			{ 
				print "<li>{$val->cat_name}</li>";
			}
			print "</ul>";
		}
		exit;
	break;
	case "searchusers":
		$search = wp_specialchars( $_POST[ 'search' ] );
		$query = "SELECT " . $wpdb->users . ".ID, " . $wpdb->users . ".user_login FROM " . $wpdb->users . " WHERE user_login LIKE '" . $search . "%' limit 0,10";
		$users = $wpdb->get_results( $query );
		if( is_array( $users ) ) {
			print "<ul>";
			while( list( $key, $val ) = each( $users ) ) 
			{ 
				print "<li>{$val->user_login}</li>";
			}
			print "</ul>";
		} else {
			print "No Users Found";
		}
		exit;
	break;
	case "updatefeeds":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}

		update_site_option( "customizefeed1", $_POST[ 'customizefeed1' ] );
		update_site_option( "customizefeed2", $_POST[ 'customizefeed2' ] );
		update_site_option( "dashboardfeed1", $_POST[ 'dashboardfeed1' ] );
		update_site_option( "dashboardfeed2", $_POST[ 'dashboardfeed2' ] );
		update_site_option( "dashboardfeed1name", $_POST[ 'dashboardfeed1name' ] );
		update_site_option( "dashboardfeed2name", $_POST[ 'dashboardfeed2name' ] );
		wpmu_admin_do_redirect( "wpmu-feeds.php" );
	break;
	case "updateblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		$options_table_name = $wpmuBaseTablePrefix . $id ."_options";

		// themes
		if( is_array( $_POST[ 'theme' ] ) ) {
			$allowed_themes = $_POST[ 'theme' ];
			$_POST[ 'option' ][ 'allowed_themes' ] = $_POST[ 'theme' ];
		} else {
			$_POST[ 'option' ][ 'allowed_themes' ] = '';
		}
		if( is_array( $_POST[ 'option' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
				update_blog_option( $id, $key, $val );
			}
		}
		// update blogs table
		$query = "UPDATE $wpdb->blogs
				SET    domain       = '".$_POST[ 'blog' ][ 'domain' ]."',
				path         = '".$_POST[ 'blog' ][ 'path' ]."',
				registered   = '".$_POST[ 'blog' ][ 'registered' ]."',
				public       = '".$_POST[ 'blog' ][ 'public' ]."',
				archived     = '".$_POST[ 'blog' ][ 'archived' ]."',
				mature       = '".$_POST[ 'blog' ][ 'mature' ]."',
				deleted      = '".$_POST[ 'blog' ][ 'deleted' ]."',
				spam         = '".$_POST[ 'blog' ][ 'spam' ]."' 
			WHERE  blog_id = '$id'";
		$result = $wpdb->query( $query );
		update_blog_status( $id, 'spam', $_POST[ 'blog' ][ 'spam' ] );
		// user roles
		if( is_array( $_POST[ 'role' ] ) == true ) {
			$newroles = $_POST[ 'role' ];
			reset( $newroles );
			while( list( $userid, $role ) = each( $newroles ) ) { 
				$role_len = strlen( $role );
				$existing_role = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$userid'  AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities'" );
				if( false == $existing_role ) {
					$wpdb->query( "INSERT INTO " . $wpdb->usermeta . "( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '$userid', '" . $wpmuBaseTablePrefix . $id . "_capabilities', 'a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}')" );
				} elseif( $existing_role != "a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}" ) {
					$wpdb->query( "UPDATE $wpdb->usermeta SET meta_value = 'a:1:{s:" . strlen( $role ) . ":\"" . $role . "\";b:1;}' WHERE user_id = '$userid'  AND meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities'" );
				}

			}
		}

		// remove user
		if( is_array( $_POST[ 'blogusers' ] ) ) {
			reset( $_POST[ 'blogusers' ] );
			while( list( $key, $val ) = each( $_POST[ 'blogusers' ] ) ) { 
				$wpdb->query( "DELETE FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities' AND user_id = '" . $key . "'" );
			}
		}


		// add user?
		if( $_POST[ 'newuser' ] != '' ) {
			$newuser = $_POST[ 'newuser' ];
			$userid = $wpdb->get_var( "SELECT ID FROM " . $wpdb->users . " WHERE user_login = '$newuser'" );
			if( $userid ) {
				$user = $wpdb->get_var( "SELECT user_id FROM " . $wpdb->usermeta . " WHERE user_id='$userid' AND meta_key='wp_" . $id . "_capabilities'" );
				if( $user == false )
					$wpdb->query( "INSERT INTO " . $wpdb->usermeta . "( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '$userid', '" . $wpmuBaseTablePrefix . $id . "_capabilities', 'a:1:{s:" . strlen( $_POST[ 'new_role' ] ) . ":\"" . $_POST[ 'new_role' ] . "\";b:1;}')" );
			}
		}
		wpmu_admin_do_redirect( "wpmu-blogs.php?action=editblog&id=".$id );
	break;
	case "deleteblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		$id = $_GET[ 'id' ];
		if( $id != '0' && $id != '1' )
			wpmu_delete_blog( $id, true );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
	case "allblogs":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		if( is_array( $_POST[ 'allblogs' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'allblogs' ] ) ) {
				if( $val != '0' && $val != '1' ) {
					if( $_POST[ 'blogfunction' ] == 'delete' ) {
						wpmu_delete_blog( $val, true );
					} elseif( $_POST[ 'blogfunction' ] == 'spam' ) {
						update_blog_status( $val, "spam", '1' );
					}
				}
			}
		}

		if( isset( $_POST[ 'redirect' ] ) ) {
			wpmu_admin_do_redirect( $_POST[ 'redirect' ] );
		} else {
			wpmu_admin_do_redirect( "wpmu-blogs.php" );
		}
	break;
	case "activateblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		update_archived( $_GET[ 'id' ], '0' );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
	case "deactivateblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		do_action( "deactivate_blog", $_GET[ 'id' ] );
		update_archived( $_GET[ 'id' ], '1' );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
	case "unspamblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		update_blog_status( $_GET[ 'id' ], "spam", '0' );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
	case "spamblog":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		do_action( "make_spam_blog", $_GET[ 'id' ] );
		update_blog_status( $_GET[ 'id' ], "spam", '1' );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
	case "mature":
		update_blog_status( $_GET[ 'id' ], 'mature', '1' );
		do_action( 'mature_blog', $_GET[ 'id' ] );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
	case "unmature":
		update_blog_status( $_GET[ 'id' ], 'mature', '0' );
		do_action( 'unmature_blog', $_GET[ 'id' ] );
		wpmu_admin_do_redirect( "wpmu-blogs.php" );
	break;
    	case "updateuser":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
		unset( $_POST[ 'option' ][ 'ID' ] );
		if( is_array( $_POST[ 'option' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
				$query = "UPDATE ".$wpdb->users." SET ".$key." = '".$val."' WHERE  ID  = '".$id."'";
				$wpdb->query( $query );
			}
		}
		if( is_array( $_POST[ 'meta' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'meta' ] ) ) { 
				$query = "UPDATE ".$wpdb->usermeta." SET meta_key = '".$_POST[ 'metaname' ][ $key ]."', meta_value = '".$val."' WHERE  umeta_id  = '".$key."'";
				$wpdb->query( $query );
			}
		}
		if( is_array( $_POST[ 'metadelete' ] ) ) {
			while( list( $key, $val ) = each( $_POST[ 'metadelete' ] ) ) { 
				$query = "DELETE FROM ".$wpdb->usermeta." WHERE  umeta_id  = '".$key."'";
				$wpdb->query( $query );
			}
		}
		wpmu_admin_do_redirect( "wpmu-users.php?action=edit&id=".$id );
	break;
    	case "updatethemes":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
    		if( is_array( $_POST[ 'theme' ] ) ) {
			$themes = array_flip( array_keys( get_themes() ) );
			reset( $themes );
			while( list( $key, $val ) = each( $themes ) ) 
			{
				if( $_POST[ 'theme' ][ addslashes( $key ) ] == 'enabled' )
					$allowed_themes[ $key ] = true;
			}
			update_site_option( 'allowed_themes', $allowed_themes );
		}
		wpmu_admin_do_redirect( "wpmu-themes.php" );
	break;
	default:
		wpmu_admin_do_redirect( "wpmu-admin.php" );
	break;
}
?>