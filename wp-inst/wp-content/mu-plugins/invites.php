<?php

$u = '';
if( $_POST[ 'u' ] ) {
    $u = $_POST[ 'u' ];
} elseif( $_GET[ 'u' ] ) {
    $u = $_GET[ 'u' ];
}
$u = $wpdb->escape( $u );

function invites_check_user_hash() {
    global $wpdb, $u;
    if( $u == '' ) {
	header( "Location: ".get_settings( "siteurl" ) );
	die( );
    } else {
	$query = "SELECT meta_value
	          FROM   ".$wpdb->usermeta."
		  WHERE  user_id    = '0'
		  AND    meta_key   = '".invite."'
		  AND    meta_value = '".$u."'";
	$userhash = $wpdb->get_var( $query );
	if( $userhash == false ) {
	    header( "Location: ".get_settings( "siteurl" ) );
	    die();
	}
    }
}
add_action('newblogheader', 'invites_check_user_hash');

function invites_admin_send_email() {
    global $wpdb;
    if( $_GET[ 'action' ] == 'invite' ) {
	if( is_email( $_POST[ 'email' ] ) ) {
	    $email = $wpdb->escape( $_POST[ 'email' ] );
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` )
		      VALUES ( NULL, '0', 'invite' , '".md5( $email )."')";
	    $wpdb->query( $query );
	    $query = "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` )
		      VALUES ( NULL, '0', '".md5( strtolower( $email ) )."_invited_by' , 'admin')";
	    $wpdb->query( $query );
	    $msg =
"You have been invited to open a free WordPress weblog.

To accept this invitation and register for your weblog, visit
";
	    $msg .= get_settings( "siteurl" ) . "/invite/".md5( $email );
	    $msg .= 
"

This invitation can only be used to set up one weblog.

Regards,
The WordPress Team

(If clicking the URLs in this message does not work, copy and paste them
into the address bar of your browser).
";
	    mail( $_POST[ 'email' ], "Your WordPress.com Invitation", $msg, "From: WordPress.com <donotreply@".get_settings( "siteurl" ).">" );
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
add_action('wpmuadmindefaultpage', 'invites_admin_send_form');

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

	$query = "SELECT ID
	          FROM   ".$wpdb->users."
		  WHERE  user_login = '".$wpdb->escape( $_POST[ 'weblog_id' ] )."'";
	$id = $wpdb->get_var( $query );

	if( $id ) {
	    $query = "UPDATE ".$wpdb->usermeta."
	              SET    user_id = '".$id."'
		      WHERE  meta_key = '".$_POST[ 'u' ]."_invited_by'";
	    $wpdb->query( $query );
	}
    }
}
add_action('newblogfinished', 'invites_cleanup_db');
?>
