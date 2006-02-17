<?php
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
if( get_site_option( "check_reg_for_invite" ) == 'yes' ) {
	add_action('newblogheader', 'invites_check_user_hash');
}

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
	global $wpdb, $wpmuBaseTablePrefix, $url, $weblog_title;
	if( isset( $_POST[ 'u' ] ) ) {
		$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = 'invite' AND meta_value = '".$_POST[ 'u' ]."'" );
		$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$_POST[ 'u' ]}_to_email'" );
		$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$_POST[ 'u' ]}_to_name'" );

		$add_to_blogroll = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$_POST[ 'u' ]}_add_to_blogroll'" );
		if( $add_to_blogroll ) {
			$userdetails = @unserialize( $add_to_blogroll );
			if( is_array( $userdetails ) ) {
				$wpdb->query("INSERT INTO {$wpmuBaseTablePrefix}{$userdetails[ 'blogid' ]}_links (link_url, link_name, link_category, link_owner) VALUES('" . addslashes( $url ) . "','" . addslashes( $weblog_title ) . "', '1', '" . intval( $userdetails[ 'userid' ] ) . "' )" );
			}
			$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$_POST[ 'u' ]}_add_to_blogroll'" );
		}


		$id = $wpdb->get_var( "SELECT ID FROM ".$wpdb->users." WHERE user_login = '" . $_POST[ 'weblog_id' ] . "'" );

		if( $id ) {
			$wpdb->query( "UPDATE ".$wpdb->usermeta." SET user_id = '".$id."', meta_key = 'invited_by' WHERE meta_key = '".$_POST[ 'u' ]."_invited_by'" );
			$wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '{$id}', 'invites_left' , '" . get_site_option( "invites_per_user" ) . "' )" );
			$wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '{$id}', 'invite_hash' , '{$_POST[ 'u' ]}' )" );
		}
	}
}
add_action('newblogfinished', 'invites_cleanup_db');

/* admin functions: 
   Configure invites: sig, number per user, default message
 */

add_action('admin_menu', 'invites_admin_menu');

function invites_admin_menu() {
	$pfile = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
	if ( is_site_admin() )
		add_submenu_page('wpmu-admin.php', 'Invites', 'Invites', 0, $pfile, 'invites_admin_content');
}

add_action('admin_footer', 'timed_invites');

function timed_invites() {
	global $wpdb, $current_user;

	$chance = mt_rand( 0, 20 );
	if( $chance == '5' ) {
		$invites_add_days = get_site_option( "invites_add_days", 7 );
		if( $invites_add_days != 0 ) {
			$days_registered = $wpdb->get_var( "SELECT TO_DAYS( NOW() ) - TO_DAYS( user_registered ) FROM $wpdb->users WHERE ID = '" . get_current_user_id() . "'" );
			if( $days_registered % get_site_option( "invites_add_days", 7 ) == 0 ) {
				$invite_day = get_user_option( "invite_day" );
				if( $invite_day != $days_registered ) {
					$invites_left = get_usermeta( $current_user->id, "invites_left" );
					if( $invites_left < get_site_option( "invites_per_user" ) ) {
						update_usermeta( get_current_user_id(), "invites_left", ($invites_left + get_site_option( "invites_add_number", 1 ) ) );
					}
					update_usermeta( get_current_user_id(), "invite_day", $days_registered );
				}
			}
		}
	}
}

add_action('admin_footer', 'expire_old_invites');

function expire_old_invites() {
	global $wpdb;

	$chance = mt_rand( 0, 100 );
	if( $chance == '5' ) {
		$mutex = $wpdb->get_var( "SELECT meta_value FROM ".$wpdb->usermeta." WHERE meta_key = 'invite_mutex'" );
		if( $mutex == false ) {
			$wpdb->query( "INSERT INTO ".$wpdb->usermeta." ( `umeta_id` , `user_id` , `meta_key` , `meta_value` ) VALUES ( NULL, '0', 'invite_mutex' , '1' )" );
			$invites = $wpdb->get_results( "SELECT * FROM {$wpdb->usermeta} WHERE meta_key like '%_invite_timestamp' AND ( TO_DAYS( NOW() ) - TO_DAYS( FROM_UNIXTIME( meta_value ) ) ) >= " . intval( get_site_option( 'invite_time_limit', 31 ) ) );
			if( is_array( $invites ) ) {
				while( list( $key, $val ) = each( $invites ) ) { 
					$email_md5 = substr( $val->meta_key, 0, strpos( $val->meta_key, "_invite_timestamp" ) );
					delete_invite( $email_md5 );
					$uid = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$email_md5}_invited_by'" );
					if( $uid ) {
						$invites_left = get_usermeta( $uid, "invites_left" );
						if( $invites_left < get_site_option( "invites_per_user" ) )
							update_usermeta( $uid, "invites_left", $invites_left++ );
					}
				}
			} 
			$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = 'invite_mutex'" );
		} else {
			if( $mutex == '5' ) {
				$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = 'invite_mutex'" );
			} else {
				$wpdb->query( "UPDATE ".$wpdb->usermeta." SET meta_value = ".($mutex+1)." WHERE meta_key = 'invite_mutex'" );
			}
		}
	}
	
}

function delete_invite( $uid ) {
	global $wpdb;

	$email = $wpdb->get_var( "SELECT meta_value FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_to_email'" );
	if( $email ) {
		$invited_by = $wpdb->get_var( "SELECT meta_value FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_invited_by'" );
		if( $invited_by ) {
			$invites_list = get_usermeta( $invited_by, "invites_list" );
			if( $invites_list ) {
				$invites_list = str_replace( $email . " ", "", $invites_list );
				update_usermeta( $invited_by, "invites_list", $invites_list );
			}
			$invites_left = get_usermeta( $invited_by, "invites_left" );
			update_usermeta( $invited_by, "invites_left", $invites_left + 1 );
		}
	}
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = 'invite' AND meta_value = '$uid'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_to_email'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_to_name'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_add_to_blogroll'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_invited_by'" );
	$wpdb->query( "DELETE FROM ".$wpdb->usermeta." WHERE meta_key = '{$uid}_invite_timestamp'" );
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
	update_site_option( "invites_add_number", intval( $_GET[ 'invites_add_number' ] ) );
	update_site_option( "invites_add_days", intval( $_GET[ 'invites_add_days' ] ) );
	update_site_option( "invite_time_limit", intval( $_GET[ 'invite_time_limit' ] ) );
	if( $_GET[ 'check_reg_for_invite' ] == 'yes' ) {
		update_site_option( "check_reg_for_invite", 'yes' );
	} else {
		update_site_option( "check_reg_for_invite", 'no' );
	}
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php

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
	<fieldset class="options">
	<form method='GET'>
	<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
	<input type='hidden' name='action' value='updateinvitedefaults'>
	<table class='editform'>
	<tr><th scope='row' valign='top'>Invites Per User:</td><td><input type='text' size='2' maxlength='2' name='invites_per_user' value='<?php echo $invites_per_user ?>'></td></tr>
	<tr><th scope='row' valign='top'>Number of Invites To Add: </td><td><input type='text' size='2' maxlength='2' name='invites_add_number' value='<?php echo get_site_option( "invites_add_number", 1 ) ?>'> (This number of invites will be added to each user every X days.)</td></tr>
	<tr><th scope='row' valign='top'>Add Invites Every</td><td valign='top'><input type='text' size='2' maxlength='2' name='invites_add_days' value='<?php echo get_site_option( "invites_add_days", 7 ) ?>'> <strong>Days</strong> (0 to disable)</td></tr>
	<tr><th scope='row' valign='top'>Invites Expire After</td><td valign='top'><input type='text' size='2' maxlength='2' name='invite_time_limit' value='<?php echo get_site_option( "invite_time_limit", 31 ) ?>'> <strong>Days</strong></td></tr>
	<tr><th scope='row' valign='top'>Default Subject:</td><td><input type='text' size='70' maxlength='90' name='invites_default_subject' value='<?php echo get_site_option( "invites_default_subject" ) ?>'></td></tr>
	<tr><th scope='row' valign='top'>Default Message:</td><td><textarea rows="9" cols="70" name="invites_default_message" tabindex="5" id="defaultmessage"><?php echo str_replace( "\\r\\n", "\n", stripslashes( get_site_option( 'invites_default_message' ) ) ) ?></textarea></td></tr>
	<tr><th scope='row' valign='top'>Registration - check for invite: </td><td><input type='checkbox' name='check_reg_for_invite' value='yes'<?php if( get_site_option( 'check_reg_for_invite' ) == 'yes' ) echo " checked"; ?>></td></tr>
	<tr><td valign='top' colspan='2'><input type='submit'></td></tr>
	</table>
	</form>
	</fieldset>
	</div>
	<div class='wrap'>
	<h2>Invite Stats</h2>
	<ul>
	<li> Free Invites: <?php echo $wpdb->get_var( "SELECT sum( meta_value ) FROM $wpdb->usermeta WHERE meta_key = 'invites_left'" ); ?></li>
	<li> <?php echo $wpdb->get_var( "SELECT count(*) FROM $wpdb->usermeta WHERE meta_key LIKE '%invited_by'" ) ?> Invites sent, of which <?php echo $wpdb->get_var( "SELECT count(*) FROM $wpdb->usermeta WHERE meta_key='invite'" ); ?> are pending and have not been used yet.</li>
	<li> Invites Per User:<ul>
	<?php $invite_groups = $wpdb->get_results( "SELECT count(*) as c, meta_value  FROM {$wpdb->usermeta} WHERE `meta_key` = 'invites_left' group by meta_value", ARRAY_A );
	while( list( $key, $val ) = each( $invite_groups ) ) 
	{ 
		print "<li> {$val[ 'c' ]} users have {$val[ 'meta_value' ]} invites.</li>";
	} 
	?>
	</ul></li>
	</ul>
	</div>
	<?php
}
if( get_usermeta( get_current_user_id(), 'invites_left' ) )
	add_action('admin_head', 'invites_link' );

function invites_link() {
	?>
<script type="text/javascript">
function invites_link() {
	inviteslink = document.createElement('a');
	inviteslink.id = 'inviteslink';
	inviteslink.innerHTML = 'Invites';
	inviteslink.href = 'invites.php';
	var userinfo = document.getElementById( 'footer' );
	userinfo.appendChild(inviteslink);
	var inviteslinkForm = document.getElementById('inviteslinkform');
}

// addLoadEvent from admin-header
addLoadEvent( invites_link );

</script>
<style type="text/css">
#inviteslink {
	position: absolute;
	top: 2.8em;
	right: 10em;
	display: block;
	padding: .3em .8em;
	background: #6da6d1;
	color: #fff;
	cursor: pointer;
}
</style>

<?php
}
?>
