<?php
require_once('admin.php');
require_once( ABSPATH . WPINC . '/registration-functions.php');

$title = __('Users');
$parent_file = 'profile.php';
	
$action = $_REQUEST['action'];
$update = '';

switch ($action) {

case 'promote':
	check_admin_referer();

	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t edit users.'));

 	$userids = $_POST['users'];
	$update = 'promote';
 	foreach($userids as $id) {
		// The new role of the current user must also have edit_users caps
		if($id == $current_user->id && !$wp_roles->role_objects[$_POST['new_role']]->has_cap('edit_users')) {
			$update = 'err_admin_role';
			continue;
		}

 		$user = new WP_User($id);
 		$user->set_role($_POST['new_role']);
 	}
		
	header('Location: users.php?update=' . $update);

break;

case 'dodelete':

	check_admin_referer();

	if ( empty($_POST['users']) ) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t delete users.'));

	$userids = $_POST['users'];
	
	$update = 'del';
 	foreach ($userids as $id) {
		if($id == $current_user->id) {
			$update = 'err_admin_del';
			continue;
		}
 		switch($_POST['delete_option']) {
		case 'delete':
			wp_delete_user($id);
			break;
		case 'reassign':
			wp_delete_user($id, $_POST['reassign_user']);
			break;
		}
	}

	header('Location: users.php?update=' . $update);

break;

case 'delete':

	check_admin_referer();

	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		$error['edit_users'] = __('You can&#8217;t delete users.');

	$userids = $_POST['users'];

	include ('admin-header.php');
?>
<form action="" method="post" name="updateusers" id="updateusers">
<div class="wrap">
<h2><?php _e('Delete Users'); ?></h2>
<p><?php _e('You have specified these users for deletion:'); ?></p>
<ul>
<?php
	$go_delete = false;
 	foreach ($userids as $id) {
 		$user = new WP_User($id);
		if ($id == $current_user->id) {
			echo "<li>" . sprintf(__('ID #%1s: %2s <strong>The current user will not be deleted.</strong>'), $id, $user->user_login) . "</li>\n";
		} else {
			echo "<li><input type=\"hidden\" name=\"users[]\" value=\"{$id}\" />" . sprintf(__('ID #%1s: %2s'), $id, $user->user_login) . "</li>\n";
			$go_delete = true;
		}
 	}
	$all_logins = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users, $wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id AND meta_key = '".$wpdb->prefix."capabilities'");
 	$user_dropdown = '<select name="reassign_user">';
 	foreach ($all_logins as $login) {
		if ( $login->ID == $current_user->id || !in_array($login->ID, $userids) ) {
 			$user_dropdown .= "<option value=\"{$login->ID}\">{$login->user_login}</option>";
 		}
 	}
 	$user_dropdown .= '</select>';
 	?>
 	</ul>
<?php if($go_delete) : ?>
 	<p><?php _e('What should be done with posts and links owned by this user?'); ?></p>
	<ul style="list-style:none;">
		<li><label><input type="radio" id="delete_option0" name="delete_option" value="delete" checked="checked" />
		<?php _e('Delete all posts and links.'); ?></label></li>
		<li><input type="radio" id="delete_option1" name="delete_option" value="reassign" />
		<?php echo '<label for="delete_option1">'.__('Attribute all posts and links to:')."</label> $user_dropdown"; ?></li>
	</ul>
	<input type="hidden" name="action" value="dodelete" />
	<p class="submit"><input type="submit" name="submit" value="<?php _e('Confirm Deletion'); ?>" /></p>
<?php else : ?>
	<p><?php _e('There are no valid users selected for deletion.'); ?></p>
<?php endif; ?>
</div>
</form>
<?php

break;

case 'adduser':
	die( "This function is disabled. Add a user from your community." );
	check_admin_referer();
	
	$errors = add_user();
	
	if(count($errors) == 0) {
		header('Location: users.php?update=add');
		die();
	}

case 'addexistinguser':
	check_admin_referer();
	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t edit users.'));

	$new_user_login = wp_specialchars(trim($_POST['newuser']));
	/* checking that username has been typed */
	if ($new_user_login != '' && $new_user_login != 'admin' ) {
		if ( username_exists( $new_user_login ) ) {
			$user_ID = $wpdb->get_var( "SELECT ID FROM $wpdb->users WHERE user_login = '$new_user_login'" );
			if( $wpdb->get_var( "SELECT user_id FROM {$wpdb->usermeta} WHERE user_id = '{$user_ID}' AND meta_key = '{$wpdb->prefix}capabilities'" ) == false ) {
				$user = new WP_User($user_ID);
				$user->set_role( $_POST[ 'new_role' ] );
				do_action( "added_existing_user", $user_ID );
				header('Location: users.php?update=add');
				die();
			}
		}
	}
	header('Location: users.php');
	die();
break;
default:
	if( is_array( $_POST[ 'new_roles' ] ) ) {
		check_admin_referer();

		if ( !current_user_can('edit_users') )
			die(__('You can&#8217;t edit users.'));

		while( list( $key, $val ) = each( $_POST[ 'new_roles' ] ) ) { 
			if( $val == 'inactive' ) {
				$wpdb->query( "DELETE FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpmuBaseTablePrefix . $wpdb->blogid . "_capabilities' AND user_id = '" . $key . "'" );
			} else {
				$user = new WP_User($key);
				$user->set_role( $val );
			}
		}
		header('Location: users.php?update=promote');
		die();
	}
	
	include ('admin-header.php');
	
	$userids = $wpdb->get_col("SELECT ID FROM $wpdb->users, $wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id AND meta_key = '".$wpdb->prefix."capabilities'");
	
	foreach($userids as $userid) {
		$tmp_user = new WP_User($userid);
		$roles = $tmp_user->roles;
		$role = array_shift($roles);
		$roleclasses[$role][$tmp_user->user_login] = $tmp_user;
	}	
	
	?>

	<?php 
	if (isset($_GET['update'])) : 
		switch($_GET['update']) {
		case 'del':
		?>
			<div id="message" class="updated fade"><p><?php _e('User deleted.'); ?></p></div>
		<?php
			break;
		case 'add':
		?>
			<div id="message" class="updated fade"><p><?php _e('New user created.'); ?></p></div>
		<?php
			break;
		case 'promote':
		?>
			<div id="message" class="updated fade"><p><?php _e('Changed roles.'); ?></p></div>
		<?php
			break;
		case 'err_admin_role':
		?>
			<div id="message" class="error"><p><?php _e("The current user's role must have user editing capabilities."); ?></p></div>
			<div id="message" class="updated fade"><p><?php _e('Other user roles have been changed.'); ?></p></div>
		<?php
			break;
		case 'err_admin_del':
		?>
			<div id="message" class="error"><p><?php _e("You can't delete the current user."); ?></p></div>
			<div id="message" class="updated fade"><p><?php _e('Other users have been deleted.'); ?></p></div>
		<?php
			break;
		}
	endif; 
	if ( isset($errors) ) : ?>
	<div class="error">
		<ul>
		<?php
		foreach($errors as $error) echo "<li>$error</li>";
		?>
		</ul>
	</div>
	<?php 
	endif;
	?>
	
<form action="" method="post" name="updateusers" id="updateusers">
<div class="wrap">
	<h2><?php _e('User List by Role'); ?></h2>
  <table cellpadding="3" cellspacing="3" width="100%">
	<?php
	foreach($roleclasses as $role => $roleclass) {
		ksort($roleclass);
		?>

	<tr>
	<th colspan="8" align="left">
  <h3><?php echo $wp_roles->role_names[$role]; ?></h3>
  </th></tr>

	<tr>
	<th><?php _e('ID') ?></th>
	<th><?php _e('Username') ?></th>
	<th><?php _e('Name') ?></th>
	<th><?php _e('E-mail') ?></th>
	<th><?php _e('Role') ?></th>
	<th><?php _e('Posts') ?></th>
	<th>&nbsp;</th>
	</tr>
	<?php
	$style = '';
	foreach ($roleclass as $user_object) {
		if( $user_object->ID != get_site_option( "admin_user_id" ) ) {
			$email = $user_object->user_email;
			$url = $user_object->user_url;
			$short_url = str_replace('http://', '', $url);
			$short_url = str_replace('www.', '', $short_url);
			if ('/' == substr($short_url, -1))
				$short_url = substr($short_url, 0, -1);
			if (strlen($short_url) > 35)
				$short_url =  substr($short_url, 0, 32).'...';
			$style = ('class="alternate"' == $style) ? '' : 'class="alternate"';
			$numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = '$user_object->ID' and post_status = 'publish'");
			if (0 < $numposts) $numposts = "<a href='edit.php?author=$user_object->ID' title='" . __('View posts') . "'>$numposts</a>";
			echo "
				<tr $style>
				<td><input type='checkbox' name='users[]' id='user_{$user_object->ID}' value='{$user_object->ID}' /> <label for='user_{$user_object->ID}'>{$user_object->ID}</label></td>
				<td><label for='user_{$user_object->ID}'><strong>$user_object->user_login</strong></label></td>
				<td><label for='user_{$user_object->ID}'>$user_object->first_name $user_object->last_name</label></td>
				<td><a href='mailto:$email' title='" . sprintf(__('e-mail: %s'), $email) . "'>$email</a></td>
				<td><select name='new_roles[{$user_object->ID}]' id='new_role'>";
			foreach($wp_roles->role_names as $roleid => $name) {
				$selected = '';
				if( $role == $roleid)
					$selected = 'selected="selected"';
				echo "<option {$selected} value=\"{$roleid}\">{$name}</option>";
			}
			echo "</select></td><td align='right'>$numposts</td><td>";
			if (is_site_admin())
				echo "<a href='user-edit.php?user_id=$user_object->ID' class='edit'>".__('Edit')."</a>";
			echo '</td></tr>';
		} else {
			echo "<tr class='alternate'><td><label for='user_{$user_object->ID}'>{$user_object->ID}</label></td><td><label for='user_{$user_object->ID}'><strong>$user_object->user_login</strong></label></td><td><label for='user_{$user_object->ID}'>$user_object->first_name $user_object->last_name</label></td><td colspan='4'><strong>Cannot Edit Site Administrator</strong></td></tr>";
		}
	}
	}
?>
  </table>


	<h2><?php _e('Update Users'); ?></h2>
<?php
$role_select = '<select name="new_role">';
foreach($wp_roles->role_names as $role => $name) {
	$role_select .= "<option value=\"{$role}\">{$name}</option>";
}
$role_select .= '</select>';
?>  
  <ul style="list-style:none;">
  	<li><input type="radio" name="action" id="action0" value="delete" /> <label for="action0"><?php _e('Delete checked users.'); ?></label></li>
  	<li><input type="radio" name="action" id="action1" value="promote" /> <?php echo '<label for="action1">'.__('Set the Role of checked users to:')."</label> $role_select"; ?></li>
  </ul>
	<p class="submit"><input type="submit" value="<?php _e('Update &raquo;'); ?>" /></p>
</div>
</form>

<div class="wrap">
<h2><?php _e('Add User From Community') ?></h2>
<form action="" method="post" name="adduser" id="adduser">
<input type='hidden' name='action' value='addexistinguser'>
<p>Type the username of another user to add them to your blog.</p>
<table>
<tr><th scope="row">User&nbsp;Login: </th><td><input type="text" name="newuser" id="newuser"></td></tr>
	<tr>
		<th scope="row"><?php _e('Role:') ?></th>
		<td><select name="new_role" id="new_role"><?php 
		foreach($wp_roles->role_names as $role => $name) {
			$selected = '';
			if( $role == 'subscriber' )
				$selected = 'selected="selected"';
			echo "<option {$selected} value=\"{$role}\">{$name}</option>";
		}
		?></select></td>
	</tr>
</table>
<br />

    </td>
    </table>
  <p class="submit">
    <input name="adduser" type="submit" id="adduser" value="<?php _e('Add User') ?> &raquo;" />
  </p>
  </form>
</div>

<?php
break;
}

include('admin-footer.php');
?>
