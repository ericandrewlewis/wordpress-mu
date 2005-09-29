<?php
require_once('admin.php');

$title = __('Edit User');
$parent_file = 'profile.php';	
$submenu_file = 'users.php';

$wpvarstoreset = array('action', 'redirect', 'profile', 'user_id');
for ($i=0; $i<count($wpvarstoreset); $i += 1) {
	$wpvar = $wpvarstoreset[$i];
	if (!isset($$wpvar)) {
		if (empty($_POST["$wpvar"])) {
			if (empty($_GET["$wpvar"])) {
				$$wpvar = '';
			} else {
				$$wpvar = $_GET["$wpvar"];
			}
		} else {
			$$wpvar = $_POST["$wpvar"];
		}
	}
}

$errors = array();

// Only allow site admins to edit every user.
if( is_site_admin() == false ) 
	if( false == $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta WHERE user_id = '$user_id' AND meta_key = '".$wpdb->prefix."capabilities'") ) $errors['head'] = __('You do not have permission to edit this user.');

switch ($action) {
case 'switchposts':

check_admin_referer();

/* TODO: Switch all posts from one user to another user */

break;

case 'update':

$errors = array();

if (!current_user_can('edit_users'))
	$errors['head'] = __('You do not have permission to edit this user.');
else
	$errors = edit_user($user_id);

if(count($errors) == 0) {
	if( is_site_admin() )
		update_usermeta( $user_id, 'invites_left', intval( $_POST[ 'invites_left' ] ) );
	header("Location: user-edit.php?user_id=$user_id&updated=true");
	exit;
}

default:
include ('admin-header.php');

$profileuser = new WP_User($user_id);
$profiledata = $profileuser->data;

if (!current_user_can('edit_users')) $errors['head'] = __('You do not have permission to edit this user.');
?>

<?php if ( isset($_GET['updated']) ) : ?>
<div id="message" class="updated fade">
	<p><strong><?php _e('User updated.') ?></strong></p>
</div>
<?php endif; ?>
<?php if ( count($errors) != 0 ) : ?>
<div class="error">
	<ul>
	<?php
	foreach($errors as $error) echo "<li>$error</li>";
	?>
	</ul>
</div>
<?php endif; ?>

<div class="wrap">
<h2><?php _e('Edit User'); ?></h2>

<form name="profile" id="your-profile" action="user-edit.php" method="post">
<p>
<input type="hidden" name="from" value="profile" />
<input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />
</p>

<fieldset>
<legend><?php _e('Name'); ?></legend>
<p><label><?php _e('Username: (no editing)'); ?><br />
<input type="text" name="user_login" value="<?php echo $profiledata->user_login; ?>" disabled="disabled" />
</label></p>
<?php if( is_site_admin() ) {?>
	<p><label><?php _e('Invites Left:') ?><br />
	<input type="text" name="invites_left" id="invites_left" value="<?php echo get_usermeta( $user_id, 'invites_left' ) ?>" /></label></p>
	<?php
} // is_site_admin
?>
<p><label><?php _e('First name:') ?><br />
<input type="text" name="first_name" value="<?php echo $profiledata->first_name ?>" /></label></p>

<p><label><?php _e('Last name:') ?><br />
<input type="text" name="last_name"  value="<?php echo $profiledata->last_name ?>" /></label></p>

<p><label><?php _e('Nickname:') ?><br />
<input type="text" name="nickname" value="<?php echo $profiledata->nickname ?>" /></label></p>

</p><label><?php _e('Display name publicly as:') ?> <br />
<select name="display_name">
<option value="<?php echo $profiledata->display_name; ?>"><?php echo $profiledata->display_name; ?></option>
<option value="<?php echo $profiledata->nickname ?>"><?php echo $profiledata->nickname ?></option>
<option value="<?php echo $profiledata->user_login ?>"><?php echo $profiledata->user_login ?></option>
<?php if ( !empty( $profiledata->first_name ) ) : ?>
<option value="<?php echo $profiledata->first_name ?>"><?php echo $profiledata->first_name ?></option>
<?php endif; ?>
<?php if ( !empty( $profiledata->last_name ) ) : ?>
<option value="<?php echo $profiledata->last_name ?>"><?php echo $profiledata->last_name ?></option>
<?php endif; ?>
<?php if ( !empty( $profiledata->first_name ) && !empty( $profiledata->last_name ) ) : ?>
<option value="<?php echo $profiledata->first_name." ".$profiledata->last_name ?>"><?php echo $profiledata->first_name." ".$profiledata->last_name ?></option>
<option value="<?php echo $profiledata->last_name." ".$profiledata->first_name ?>"><?php echo $profiledata->last_name." ".$profiledata->first_name ?></option>
<?php endif; ?>
</select></label></p>
</fieldset>

<fieldset>
<legend><?php _e('Contact Info'); ?></legend>

<p><label><?php _e('E-mail: (required)') ?><br />
<input type="text" name="email" value="<?php echo $profiledata->user_email ?>" /></label></p>

<p><label><?php _e('Website:') ?><br />
<input type="text" name="url" value="<?php echo $profiledata->user_url ?>" />
</label></p>

<p><label><?php _e('AIM:') ?><br />
<input type="text" name="aim" value="<?php echo $profiledata->aim ?>" />
</label></p>

<p><label><?php _e('Yahoo IM:') ?><br />
<input type="text" name="yim" value="<?php echo $profiledata->yim ?>" />
</label></p>

<p><label><?php _e('Jabber / Google Talk:') ?>
<input type="text" name="jabber" value="<?php echo $profiledata->jabber ?>" /></label>
</p>
</fieldset>
<br clear="all" />
<fieldset>
<legend><?php _e('About the user'); ?></legend>
<p class="desc"><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></p>
<p><textarea name="description" rows="5" cols="30"><?php echo $profiledata->description ?></textarea></p>
</fieldset>

<?php
$show_password_fields = apply_filters('show_password_fields', true);
if ( $show_password_fields ) :
?>
<fieldset>
<legend><?php _e("Update User's Password"); ?></legend>
<p class="desc"><?php _e("If you would like to change the user's password type a new one twice below. Otherwise leave this blank."); ?></p>
<p><label><?php _e('New Password:'); ?><br />
<input type="password" name="pass1" size="16" value="" />
</label></p>
<p><label><?php _e('Type it one more time:'); ?><br />
<input type="password" name="pass2" size="16" value="" />
</label></p>
</fieldset>
<?php endif; ?>

<?php do_action('edit_user_profile'); ?>

<br clear="all" />
  <table width="99%"  border="0" cellspacing="2" cellpadding="3" class="editform">
    <?php
    if(count($profileuser->caps) > count($profileuser->roles)):
    ?>
    <tr>
      <th scope="row"><?php _e('Additional Capabilities:') ?></th>
      <td><?php 
			$output = '';
			foreach($profileuser->caps as $cap => $value) {
				if(!$wp_roles->is_role($cap)) {
					if($output != '') $output .= ', ';
					$output .= $value ? $cap : "Denied: {$cap}";
				}
			}
			echo $output;
			?></td>
    </tr>
    <?php
    endif;
    ?>
  </table>
<p class="submit">
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id; ?>" />
    <input type="submit" value="<?php _e('Update User &raquo;') ?>" name="submit" />
 </p>
</form>
</div>
<?php
$invites_list = get_usermeta( intval( $_GET[ 'user_id' ] ), "invites_list" );
if( $invites_list != '' )
{
	if( strlen( $invites_list ) > 3 ) {
		?><div class="wrap">
		<h3>Invited Users</h3>
		<table><?php
		$invites = explode( " ", $invites_list );
		reset( $invites );
		while( list( $key, $val ) = each( $invites ) ) { 
			if( $val != "" ) {
				$id = $wpdb->get_row( "SELECT ID FROM {$wpdb->users} WHERE user_email = '$val'" );
				if( $id ) {
					$invited_user_id = $id->ID;
				} else {
					$invited_user_id = $wpdb->get_var( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'invite_hash' AND meta_value = '" . md5( $val ) . "'" );
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
					print "<tr><td>$val</td><td>$invited_user_login</td><td><em>Invite Not Used Yet</em></td></tr>";
				}
			}
		}
		?></table></div><?php
	}
}
break;
}

include('admin-footer.php');
?>
