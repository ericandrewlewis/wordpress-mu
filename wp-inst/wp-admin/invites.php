<?php
require_once('admin.php');

$title = __('Invites');
$parent_file = 'edit.php';

if( $_POST[ 'action' ] == 'send' ) {
    $invites_left = get_usermeta( $user_ID, 'invites_left' );
    if( $_POST[ 'email' ] != '' && is_email( $_POST[ 'email' ] ) ) {
	    $email = strtolower( $_POST[ 'email' ] );
	    if( $invites_left != false || is_site_admin() == true ) {
		    $invites_list = get_usermeta( $current_user->data->ID, "invites_list" );
		    $pos = strpos( $invites_list, substr( $email, 1 ) );
		    if( $pos == true ) {
			    header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=alreadysent&to=" . urlencode(  $email ) );
			    exit;
		    }
		    $invites_list .= strtolower( $email ) . " ";
		    update_usermeta( $current_user->data->ID, "invites_list", $invites_list );

		    $msg     = get_site_option( "invites_default_message" );
		    $subject = get_site_option( "invites_default_subject" );
		    $from    = $cache_userdata[ $user_ID ]->user_email;

		    $visitor_pass = md5( $email );
		    $msg = str_replace( "FIRSTNAME", $_POST[ 'fname' ], $msg );
		    $msg = str_replace( "LASTNAME", $_POST[ 'lname' ], $msg );
		    $msg = str_replace( "PERSONALMESSAGE", $_POST[ 'personalmessage' ], $msg );
		    $msg = str_replace( "VISITORPASS", $visitor_pass, $msg );
		    $msg = str_replace( "\\r\\n", "\n", stripslashes( str_replace( "REGURL", "http://" . $current_site->domain . "/invite/" . $visitor_pass, $msg ) ) );

		    $subject = str_replace( "FIRSTNAME", $_POST[ 'fname' ], $subject );
		    if( $cache_userdata[ $user_ID ]->display_name != '' ) {
			    $username = $cache_userdata[ $user_ID ]->display_name;
		    } elseif( $cache_userdata[ $user_ID ]->first_name != '' ) {
			    $username = $cache_userdata[ $user_ID ]->first_name;
		    } elseif( $cache_userdata[ $user_ID ]->nickname != '' ) {
			    $username = $cache_userdata[ $user_ID ]->nickname;
		    } else {
			    $username = __( 'Someone' );
		    }
		    $msg = str_replace( "USERNAME", ucfirst( $username ), $msg );
		    $subject = str_replace( "USERNAME", ucfirst( $username ), $subject );

		    $wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', 'invite' , '".md5( strtolower( $email ) )."')" );
		    $wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_invited_by' , '$user_ID')" );
		    $wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_to_email' , '{$_POST[ 'email' ]}')" );
		    $wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_to_name' , '{$_POST[ 'fname' ]}')" );
		    $wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_invite_timestamp' , UNIX_TIMESTAMP())" );
		    if( $_POST[ 'add_blog_to_blogroll' ] == '1' ) {
			    $t = array( "blogid" => $wpdb->blogid, "userid" => get_current_user_id() );
			    $wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_add_to_blogroll' , '" . serialize( $t ) . "')" );
		    }

		    mail( $_POST[ 'email' ], $subject, $msg, "From: $from" );
		    if( is_site_admin() == false ) {
			    $invites_left = $invites_left - 1;
			    update_usermeta( $user_ID, "invites_left", $invites_left );
		    }
		    header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=sent&to=" . urlencode(  $email ) );
		    exit;
	    } else {
		    header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=notsent&to=" . urlencode(  $email ) );
		    exit;
	    }
    } else {
	    header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=completeform" );
	    exit;
    }
} elseif( $_GET[ 'action' ] == 'deleteinvite' ) {
	delete_invite( md5( $_GET[ 'inviteemail' ] ) );
	header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=deletedinvite" );
	exit;
}

if( $_POST[ 'personalmessage' ] == '' ) {
    if( $current_site->site_name != '' ) {
	    $site_name = $current_site;
    } else {
	    $site_name = get_settings( 'blogname' );
    }
    $_POST[ 'personalmessage' ] = sprintf( __( "I've been using %s and thought you might 
like to try it out.  Here's an invitation to 
create an account." ), $site_name ) ;
}

include('admin-header.php');
if (isset($_GET['result'] ) && $_GET['result'] == 'sent' ) {
    ?><div id="sent" class="updated fade"><p><strong><?php echo sprintf( __("Invite Sent to %s."), 
$wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'notsent' ) {
    ?><div id="sent" class="updated fade"><p><strong><?php echo sprintf( __("Invite Not Sent to %s."), 
$wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'alreadysent' ) {
    ?><div id="sent" class="updated fade"><p><strong><?php echo sprintf( __("Invite Already Sent to 
%s."), 
$wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'completeform' ) {
    ?><div id="sent" class="updated fade"><p><strong><?php _e("Please complete the form.") ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'deletedinvite' ) {
    ?><div id="sent" class="updated fade"><p><strong><?php _e("Invite Deleted.") ?></strong></p></div><?php
}
if( $invites_left != false || is_site_admin() == true ) {
?>
 
<div class="wrap"> 
  <h2><?php _e('Invites') ?></h2> 
  <form name="form1" method="post" action="invites.php"> 
    <input type="hidden" name="action" value="send" /> 
    <fieldset class="options"> 
    <legend><?php _e('Send Invite To') ?></legend> 
    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
      <tr valign="top"> 
        <th width="33%" scope="row"><?php _e('First Name:') ?></th> 
        <td><input name="fname" type="text" id="fname" value="<?php echo stripslashes( $_POST[ 'fname' ] ) ?>" size="40" /></td> 
      </tr> 
      <tr valign="top"> 
        <th width="33%" scope="row"><?php _e('Last Name:') ?></th> 
        <td><input name="lname" type="text" id="lname" value="<?php echo stripslashes( $_POST[ 'lname' ] ) ?>" size="40" /></td> 
      </tr> 
      <tr valign="top"> 
        <th width="33%" scope="row"><?php _e('Email:') ?></th> 
        <td><input name="email" type="text" id="email" value="<?php echo stripslashes( $_POST[ 'email' ] ) ?>" size="40" /></td> 
      </tr> 
      <tr valign="top"> 
        <th width="33%" scope="row"><?php _e('Personal Message:') ?></th> 
        <td><textarea rows="5" cols="60" name="personalmessage" tabindex="5" id="defaultmessage"><?php echo stripslashes( $_POST[ 'personalmessage' ] ) ?></textarea></td> 
      </tr> 
      <tr valign="top"> 
        <th width="33%" scope="row"></th> 
        <td><label><input type='checkbox' name='add_blog_to_blogroll' value='1' /> <?php _e('Add to my blogroll after signup') ?></label></td> 
      </tr> 
    </table> 
    </fieldset> 
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Send Invite') ?> &raquo;" />
    </p>
  </form> 
</div>
<?php
} else { // check for invites/is_site_admin()
	?>
	<div class="wrap"> 
	<p>Sorry, you have used all your invites!</p>
	</div>
	<?php
}

$invites_list = get_usermeta( $current_user->data->ID, "invites_list" );
if( $invites_list != '' )
{
	if( strlen( $invites_list ) > 3 ) {
		?><div class="wrap">
		<h3>Already Invited</h3>
		<table><?php
		$invites = explode( " ", $invites_list );
		reset( $invites );
		while( list( $key, $val ) = each( $invites ) ) { 
			if( $val != "" ) {
				$id = $wpdb->get_row( "SELECT ID FROM {$wpdb->users} WHERE user_email = '$val'" );
				if( $id ) {
					$invited_user_id = $id->ID;
				} else {
					$invited_user_id = 0;
				}

				if( $invited_user_id != 0 ) {
					$invited_user_blog = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$invited_user_id' AND meta_key='source_domain'" );
				} else {
					$invited_user_blog = '';
				}
				$invited_user_login = $wpdb->get_var( "SELECT user_login FROM $wpdb->users WHERE ID = '$invited_user_id'" );
				if( $invited_user_blog != '' ) {
					print "<tr><td>$val</td><td>$invited_user_login</td><td><a href='http://{$invited_user_blog}'>http://$invited_user_blog</a></td></tr>";
				} else {
					$invited_time = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '" . md5( $val ) . "_invite_timestamp'" );
					if( $invited_time ) {
						$days_left = 7 - intval( ( time() - $invited_time ) / 86400 );
						print "<tr><td>$val</td><td>$invited_user_login</td><td><em>Invite Not Used Yet</em> ($days_left days left)";
						if ( function_exists('delete_invite') )
							print " (<a href='?action=deleteinvite&inviteemail=" . urlencode( $val ) . "'>Delete</a>)";
						print "</td></tr>";
					} else {
						print "<tr><td>$val</td><td>$invited_user_login</td><td><em>Invite Not Used Yet</em>";
						if ( function_exists('delete_invite') )
							print " (<a href='?action=deleteinvite&inviteemail=" . urlencode( $val ) . "'>Delete</a>)";
						print "</td></tr>";
					}
				}
			}
		}
		?></table></div><?php
	}
}
?>
<?php include("admin-footer.php") ?>
