<?php
require_once('admin.php');

$title = __('Invites');
$parent_file = 'edit.php';

if( $_POST[ 'action' ] == 'send' ) {
    $invites_left = get_usermeta( $user_ID, 'invites_left' );
    if( $invites_left != false ) {
	if( $_POST[ 'email' ] != '' && is_email( $_POST[ 'email' ] ) ) {
	    $msg     = get_site_option( "invites_default_message" );
	    $subject = get_site_option( "invites_default_subject" );
	    $from    = $cache_userdata[ $user_ID ]->user_email;

	    $msg = str_replace( "FIRSTNAME", $_POST[ 'fname' ], $msg );
	    $msg = str_replace( "LASTNAME", $_POST[ 'lname' ], $msg );
	    $msg = str_replace( "PERSONALMESSAGE", $_POST[ 'personalmessage' ], $msg );
	    $msg = str_replace( "\\r\\n", "\n", stripslashes( str_replace( "REGURL", "http://" . $current_site->domain . "/invite/".md5( $_POST[ 'email' ] ), $msg ) ) );

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

	    $email = $wpdb->escape( $_POST[ 'email' ] );
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', 'invite' , '".md5( strtolower( $email ) )."')";
	    $wpdb->query( $query );
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_invited_by' , '$user_ID')";
	    $wpdb->query( $query );
	    mail( $_POST[ 'email' ], $subject, $msg, "From: $from" );
	    if( $user_ID != get_site_option( "admin_user_id" ) ) {
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
    $_POST[ 'personalmessage' ] = "I've been using WordPress and thought you might like to try it out.  Here's an invitation to create an account.";
}

include('admin-header.php');
if (isset($_GET['result'] ) && $_GET['result'] == 'sent' ) {
    ?><div class="updated"><p><strong><?php echo sprintf( __("Invite Sent to %s."), $wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
} elseif (isset($_GET['result'] ) && $_GET['result'] == 'notsent' ) {
    ?><div class="updated"><p><strong><?php echo sprintf( __("Invite Not Sent to %s."), $wpdb->escape( $_GET[ 'to' ] ) ) ?></strong></p></div><?php
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
<?php include("admin-footer.php") ?>
