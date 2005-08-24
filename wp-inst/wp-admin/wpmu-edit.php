<?php
require_once('admin.php');

if( is_site_admin() == false ) {
    die( __('<p>You do not have permission to access this page.</p>') );
}

do_action( "wpmuadminedit", "" );

$id = $_POST[ 'id' ];
switch( $_GET[ 'action' ] ) {
	case "siteoptions":
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
		update_site_option( "super_users", $_POST[ 'super_users' ] );
		header( "Location: wpmu-options.php?updated=true" );
		exit;
	break;
	case "searchcategories":
		$search = $_GET[ 'search' ];
		$id = $_GET[ 'id' ];
		$query = "SELECT cat_name FROM " . $wpdb->sitecategories . " WHERE cat_name LIKE '%" . $search . "%' limit 0,10";
		$cats = $wpdb->get_results( $query );
		if( is_array( $cats ) ) {
			print "<table cellpadding=2 cellspacing=0 border=0>";
			print "<tr><td style='padding: 5px; background: #dfe8f1' >ESC to cancel</td></tr>";
			while( list( $key, $val ) = each( $cats ) ) 
			{ 
				print '<tr><td><span onclick="javascript:return update_AJAX_search_box(\'' . $val->cat_name . '\');"><a>' . $val->cat_name . '</a></span></td></tr>';
			}
			print "</table>";
		}
		exit;
	break;
	case "searchusers":
		$search = $_GET[ 'search' ];
		$id = $_GET[ 'id' ];
		$query = "SELECT " . $wpdb->users . ".ID, " . $wpdb->users . ".user_login FROM " . $wpdb->users . ", " . $wpdb->usermeta . " WHERE " . $wpdb->users . ".ID = " . $wpdb->usermeta . ".user_id AND " . $wpdb->usermeta . ".meta_key = '" . $wpmuBaseTablePrefix . $id . "_capabilities'";
		$query = "SELECT " . $wpdb->users . ".ID, " . $wpdb->users . ".user_login FROM " . $wpdb->users . " WHERE user_login LIKE '%" . $search . "%' limit 0,10";
		$users = $wpdb->get_results( $query );
		if( is_array( $users ) ) {
			while( list( $key, $val ) = each( $users ) ) 
			{ 
				print '<span onclick="javascript:return update_AJAX_search_box(\'' . $val->user_login . '\');"><a>' . $val->user_login . '</a></span><br>';
			}
		} else {
			print "No Users Found";
		}
		exit;
	break;
	case "updatefeeds":
		update_site_option( "customizefeed1", $_POST[ 'customizefeed1' ] );
		update_site_option( "customizefeed2", $_POST[ 'customizefeed2' ] );
		update_site_option( "dashboardfeed1", $_POST[ 'dashboardfeed1' ] );
		update_site_option( "dashboardfeed2", $_POST[ 'dashboardfeed2' ] );
		update_site_option( "dashboardfeed1name", $_POST[ 'dashboardfeed1name' ] );
		update_site_option( "dashboardfeed2name", $_POST[ 'dashboardfeed2name' ] );
		header( "Location: wpmu-feeds.php?updated=true" );
	break;
    case "updateblog":
    $options_table_name = $wpmuBaseTablePrefix . $id ."_options";

    // themes
    if( is_array( $_POST[ 'theme' ] ) ) {
	$allowed_themes = $_POST[ 'theme' ];
	$_POST[ 'option' ][ 'allowed_themes' ] = $_POST[ 'theme' ];
    }
    if( is_array( $_POST[ 'option' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
	    if ( is_array($val) || is_object($val) )
		$val = serialize($val);

	    $query = "SELECT option_id, option_value
	              FROM   ".$options_table_name."
		      WHERE  option_name  = '".$key."'";
	    $opts = $wpdb->get_row( $query, ARRAY_A );
	    $optvalue = $opts[ 'option_value' ];
	    $option_id = $opts[ 'option_id' ];
	    if( $opts == false ) {
		$query = "INSERT INTO ".$options_table_name." ( `option_id` , `blog_id` , `option_name` , `option_can_override` , `option_type` , `option_value` , `option_width` , `option_height` , `option_description` , `option_admin_level` , `autoload` )
		          VALUES ( NULL, '0', '".$key."', 'Y', '1', '".$val."', '20', '8', '', '1', 'yes')";
	        $wpdb->query( $query );
	    } elseif( $optvalue != $val ) {
		$query = "UPDATE ".$options_table_name."
    	                  SET    option_value = '".$val."'
		          WHERE  option_name  = '".$key."'";
	        $wpdb->query( $query );
	    }
	}
    }

    // update blogs table
    if( $_POST[ 'blog' ][ 'domain' ] != $current_site->domain ) {
	$query = "UPDATE ".$wpdb->blogs."
                  SET    domain       = '".$_POST[ 'blog' ][ 'domain' ]."',
	                 path         = '".$_POST[ 'blog' ][ 'path' ]."',
	                 registered   = '".$_POST[ 'blog' ][ 'registered' ]."',
		         last_updated = '".$_POST[ 'blog' ][ 'last_updated' ]."',
		         is_public    = '".$_POST[ 'blog' ][ 'is_public' ]."'
	          WHERE  blog_id = '".$id."'";
        $wpdb->query( $query );
    }

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
    header( "Location: wpmu-blogs.php?action=editblog&id=".$id."&updated=true" );
    break;
    case "deleteblog":
	$drop_tables = array( $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_categories",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_comments",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_linkcategories",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_links",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_options",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_post2cat",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_postmeta",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_posts",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_referer_visitLog",
			      $wpmuBaseTablePrefix . $_GET[ 'id' ] . "_referer_blacklist" );
	reset( $drop_tables );
	while( list( $key, $val ) = each( $drop_tables  ) ) 
	{ 
		$wpdb->query( "DROP TABLE $val" );
	}
	$wpdb->query( "DELETE FROM ".$wpdb->blogs." WHERE blog_id = '".$_GET[ 'id' ]."'" );
	header( "Location: wpmu-blogs.php?updated=true" );
    break;
    case "deactivateblog":
	$query = "UPDATE ".$wpdb->blogs."
	          SET    is_public = 'archived'
	          WHERE  blog_id = '".$_GET[ 'id' ]."'";
        $wpdb->query( $query );
	header( "Location: wpmu-blogs.php?updated=true" );
    break;
    case "updateuser":
    unset( $_POST[ 'option' ][ 'ID' ] );
    if( is_array( $_POST[ 'option' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
    	$query = "UPDATE ".$wpdb->users."
    	          SET    ".$key." = '".$val."'
    	          WHERE  ID  = '".$id."'";
    	$wpdb->query( $query );
        }
    }
    if( is_array( $_POST[ 'meta' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'meta' ] ) ) { 
    	$query = "UPDATE ".$wpdb->usermeta."
    	          SET    meta_key = '".$_POST[ 'metaname' ][ $key ]."',
    		         meta_value = '".$val."'
    	          WHERE  umeta_id  = '".$key."'";
    	$wpdb->query( $query );
        }
    }
    if( is_array( $_POST[ 'metadelete' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'metadelete' ] ) ) { 
	    $query = "DELETE FROM ".$wpdb->usermeta."
	              WHERE  umeta_id  = '".$key."'";
	    $wpdb->query( $query );
        }
    }
    header( "Location: wpmu-users.php?action=edit&id=".$id."&updated=true" );
    break;
    case "updatethemes":
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
    header( "Location: wpmu-themes.php?updated=true" );
    break;
    default:
    header( "Location: wpmu-admin.php" );
    break;
}
?>
