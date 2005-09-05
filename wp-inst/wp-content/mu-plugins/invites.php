<?php
return; // disable by default.
if( substr( $_SERVER[ 'PHP_SELF' ], -14 ) == 'wpmu-admin.php' || substr( $_SERVER[ 'PHP_SELF' ], -11 ) == 'invites.php' ) {
	if( false == get_site_option( "invites_default_message" ) ) {
		$msg = 
"Dear FIRSTNAME LASTNAME,
---------------------------------------------
PERSONALMESSAGE
---------------------------------------------
You have been invited to open a free WordPress weblog.

To accept this invitation and register for your weblog, visit
REGURL
Your visitor pass is: VISITORPASS

This invitation can only be used to set up one weblog.

Regards,
The WordPress Team

(If clicking the URLs in this message does not work, copy and paste them
into the address bar of your browser).";
		update_site_option( "invites_default_message", $msg );
	}

	if( false == get_site_option( "invites_default_subject" ) ) {
		$subject = "FIRSTNAME, USERNAME has invited you to use WordPress";
		update_site_option( "invites_default_subject", $subject );
	}

}

$u = $wpdb->escape( $_REQUEST['u'] );

function invites_check_user_hash() {
    global $wpdb, $u;
    if( $u == '' ) {
	header( "Location: ".get_option( "siteurl" ) );
	die( );
    } else {
	$query = "SELECT meta_value FROM ".$wpdb->usermeta." WHERE user_id = '0' AND meta_key = 'invite' AND meta_value = '".$u."'";
	$userhash = $wpdb->get_results( $query, ARRAY_A );

	if( $userhash == false ) {
	    header( "Location: ".get_option( "siteurl" ) );
	    die();
	}
    }
}
add_action('newblogheader', 'invites_check_user_hash');

function invites_admin_send_email() {
    global $wpdb;
    $msg = get_site_option( "invites_default_message" );
    if( $msg == '' ) {
	$msg = "Dear FIRSTNAME LASTNAME,
---------------------------------------------
PERSONALMESSAGE
---------------------------------------------
You have been invited to open a free WordPress weblog.

To accept this invitation and register for your weblog, visit
REGURL
This invitation can only be used to set up one weblog.

Regards,
The WordPress Team

(If clicking the URLs in this message does not work, copy and paste them
into the address bar of your browser).";
	update_site_option( "invites_default_message", $msg );
    }
    if( $_GET[ 'action' ] == 'invite' ) {
	if( is_email( $_POST[ 'email' ] ) ) {
	    $email = $_POST[ 'email' ];
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` )
		      VALUES ( NULL, '0', 'invite' , '".md5( $email )."')";
	    $wpdb->query( $query );
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` )
		      VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_invited_by' , 'admin')";
	    $wpdb->query( $query );
	    $msg = str_replace( "REGURL", get_option( "siteurl" ) . "/invite/".md5( $email ), $msg );
	    mail( $_POST[ 'email' ], "Your " . $current_site->site_name . " Invitation", $msg, "From: " . $current_site->site_name . " <donotreply@".get_option( "siteurl" ).">" );
	    header( "Location: wpmu-admin.php?result=invitesent" );
	    die();
	} else {
	    header( "Location: wpmu-admin.php?result=invitenotsent" );
	    die();
	}
    }
}
add_action('wpmuadminedit', 'invites_admin_send_email');

function invites_admin_send_form() {
    print "<h2>Invites</h2>";
    ?>
    <p>Invite a new user to use this site!</p>
    <form action='wpmu-edit.php?action=invite' method='POST'>
    Email: <input type='text' value='' name='email' size='40'><br />
    <input type='submit' value='Send Invite'>
    </form>
    <?php
    // must also list stats on current invites and drill down into specifics.
}
# add_action('wpmuadmindefaultpage', 'invites_admin_send_form');

function invites_admin_result() {
    switch( $_GET[ 'result' ] ) {
	case "invitesent":
	    ?><div class="updated"><p><strong><?php _e('Invite Sent.') ?></strong></p></div><?php
	    break;
	case "invitenotsent":
	    ?><div class="updated"><p><strong><?php _e('Invite Not Sent.') ?></strong></p></div><?php
	    break;
    }
}
add_action('wpmuadminresult', 'invites_admin_result');

function invites_add_field() {
    global $u;

    echo "<input type='hidden' name='u' value='".$u."'>\n";
}
add_action('newblogform', 'invites_add_field');

function invites_cleanup_db( $val ) {
    global $wpdb;
    if( isset( $_POST[ 'u' ] ) ) {
	$query = "DELETE FROM ".$wpdb->usermeta."
                  WHERE       meta_key = 'invite'
	          AND         meta_value = '".$_POST[ 'u' ]."'";
	$wpdb->query( $query );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$_POST[ 'u' ]}_to_email'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$_POST[ 'u' ]}_to_name'" );

	$id = $wpdb->get_var( "SELECT ID FROM ".$wpdb->users." WHERE user_login = '" . $_POST[ 'weblog_id' ] . "'" );

	if( $id ) {
	    $query = "UPDATE ".$wpdb->usermeta."
	              SET    user_id = '".$id."'
		      WHERE  meta_key = '".$_POST[ 'u' ]."_invited_by'";
	    $wpdb->query( $query );
	}
    }
}
add_action('newblogfinished', 'invites_cleanup_db');

/* admin functions: 
   Configure invites: sig, number per user, default message
 */

if( is_site_admin() ) {
	add_action('admin_menu', 'admin_menu');
	add_action('admin_footer', 'admin_footer');
}

function admin_menu() {
	$pfile = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
	/*
	$invites_left = get_option( "invites_left" );
	if( $invites_left == '' ) {
	    $invites_left = 5;
	    update_site_option( "invites_per_user", $invites_left );
	    update_option( "invites_left", $invites_left );
	}
	*/

	add_submenu_page('wpmu-admin.php', 'Invites', 'Invites', 0, $pfile, 'invites_admin_content');
}

function admin_footer() {
}

function invites_admin_content() {
    global $wpdb;

    if( is_site_admin() == false ) {
	    return;
    }

    switch( $_GET[ 'action' ] ) {
	case "updateinvitedefaults":
	update_site_option( "invites_per_user", $_GET[ 'invites_per_user' ] );
	update_site_option( "invites_default_message", $_GET[ 'invites_default_message' ] );
	update_site_option( "invites_default_subject", $_GET[ 'invites_default_subject' ] );
	break;
	case "":
	break;
	default:
	break;
    }
    $invites_per_user = get_site_option( "invites_per_user" );
    if( $invites_per_user == '' ) {
	$invites_per_user = 5;
	update_site_option( "invites_per_user", $invites_per_user );
    }
    ?>
	<div class='wrap'>
	<h2>Invite Options</h2>
	<form method='GET'>
	<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
	<input type='hidden' name='action' value='updateinvitedefaults'>
	<table>
	<tr><td valign='top'>Invites Per User:</td><td><input type='text' size='2' maxlength='2' name='invites_per_user' value='<?php echo $invites_per_user ?>'></td></tr>
	<tr><td valign='top'>Default Subject:</td><td><input type='text' size='70' maxlength='90' name='invites_default_subject' value='<?php echo get_site_option( "invites_default_subject" ) ?>'></td></tr>
	<tr><td valign='top'>Default Message:</td><td><textarea rows="9" cols="70" name="invites_default_message" tabindex="5" id="defaultmessage"><?php echo str_replace( "\\r\\n", "\n", stripslashes( get_site_option( 'invites_default_message' ) ) ) ?></textarea></td></tr>
	<tr><td valign='top' colspan='2'><input type='submit'></td></tr>
	</table>
	</form>
	</div>
	<?php
}
?>
