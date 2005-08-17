<?php
require_once('admin.php');

$title = __('Invites');
$parent_file = 'edit.php';

if( $_POST[ 'action' ] == 'send' ) {
    $invites_left = get_usermeta( $user_ID, 'invites_left' );
    if( $invites_left != false ) {
	if( $_POST[ 'email' ] != '' && is_email( $_POST[ 'email' ] ) ) {
	    $email = strtolower( $_POST[ 'email' ] );
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
	    $subject = str_replace( "USERNAME", ucfirst( $username ), $subject );

	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', 'invite' , '".md5( strtolower( $email ) )."')";
	    $wpdb->query( $query );
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_invited_by' , '$user_ID')";
	    $wpdb->query( $query );
	    mail( $_POST[ 'email' ], $subject, $msg, "From: $from" );
	    if( is_site_admin() == false ) {
		    $invites_left = $invites_left - 1;
		    update_usermeta( $user_ID, "invites_left", $invites_left );
	    }
	    header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=sent&to=" . urlencode(  $email ) );
	    exit;
	}
    } else {
	    header( "Location: ".get_settings( "siteurl" )."/wp-admin/invites.php?result=notsent&to=" . urlencode(  $email ) );
	    exit;
    }
} elseif( $_POST[ 'personalmessage' ] == '' ) {
    $_POST[ 'personalmessage' ] = "I've been using WordPress and thought you might 
like to try it out.  Here's an invitation to 
create an account.";
}

include('admin-header.php');
if (isset($_GET['result'] ) && $_GET['result'] == 'sent' ) {
    ?><div class="updated"><p><strong><?php echo sprintf( __("Invite Sent to %s."), $wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'notsent' ) {
    ?><div class="updated"><p><strong><?php echo sprintf( __("Invite Not Sent to %s."), $wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'alreadysent' ) {
    ?><div class="updated"><p><strong><?php echo sprintf( __("Invite Already Sent to %s."), $wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
}
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
        <td><textarea rows="5" cols="40" name="personalmessage" tabindex="5" id="defaultmessage"><?php echo stripslashes( $_POST[ 'personalmessage' ] ) ?></textarea></td> 
      </tr> 
    </table> 
    </fieldset> 
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Send Invite') ?> &raquo;" />
    </p>
  </form> 
</div>
<?php
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
				$row = $wpdb->get_row( "SELECT * FROM $wpdb->usermeta WHERE meta_key = '" . md5( $val ) . "_invited_by' AND meta_value = '" . $current_user->data->ID. "'" );
				$invited_user_id = $row->user_id;
				if( $invited_user_id != 0 ) {
					$invited_user_blog = $wpdb->get_var( "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$invited_user_id' AND meta_key='source_domain'" );
				} else {
					$invited_user_blog = '';
				}
				$invited_user_login = $wpdb->get_var( "SELECT user_login FROM $wpdb->users WHERE user_id = '$invited_user_id'" );
				if( $invited_user_blog != '' ) {
					print "<tr><td>$val</td><td>$invited_user_login</td><td><a href='http://{$invited_user_blog}'>http://$invited_user_blog</a></td></tr>";
				} else {
					print "<tr><td>$val</td><td>$invited_user_login</td><td><em>Invite Not Used Yet</em></td></tr>";
				}
			}
		}
		?></table></div><?php
	}
}
?>
<?php include("admin-footer.php") ?>
