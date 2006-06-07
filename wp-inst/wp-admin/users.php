<?php
require_once('admin.php');
require_once( ABSPATH . WPINC . '/registration-functions.php');

$title = __('Users');
$parent_file = 'profile.php';

$action = $_REQUEST['action'];
$update = '';

switch ($action) {

case 'promote':
	check_admin_referer('bulk-users');

	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t edit users.'));

 	$userids = $_POST['users'];
	$update = 'promote';
 	foreach($userids as $id) {
 		if ( ! current_user_can('edit_user', $id) )
 			die(__('You can&#8217;t edit that user.'));
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
	die( "This function is disabled." );
	check_admin_referer('delete-users');

	if ( empty($_POST['users']) ) {
		header('Location: users.php');
	}

	if ( !current_user_can('delete_users') )
		die(__('You can&#8217;t delete users.'));

	$userids = $_POST['users'];

	$update = 'del';
 	foreach ($userids as $id) {
 		if ( ! current_user_can('delete_user', $id) )
 			die(__('You can&#8217;t delete that user.'));
 
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
	die( "This function is disabled." );
	check_admin_referer('bulk-users');

	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('delete_users') )
		$error = new WP_Error('edit_users', __('You can&#8217;t delete users.'));

	$userids = $_POST['users'];

	include ('admin-header.php');
?>
<form action="" method="post" name="updateusers" id="updateusers">
<?php wp_nonce_field('delete-users') ?>
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

case 'doremove':
	check_admin_referer();

	if ( empty($_POST['users']) ) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t remove users.'));

	$userids = $_POST['users'];

	$update = 'remove';
 	foreach ($userids as $id) {
		if ($id == $current_user->id) {
			$update = 'err_admin_remove';
			continue;
		}
		remove_user_from_blog($id);
	}

	header('Location: users.php?update=' . $update);

break;

case 'removeuser':

	check_admin_referer();

	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		$error = new WP_Error('edit_users', __('You can&#8217;t remove users.'));

	$userids = $_POST['users'];

	include ('admin-header.php');
?>
<form action="" method="post" name="updateusers" id="updateusers">
<div class="wrap">
<h2><?php _e('Remove Users from Blog'); ?></h2>
<p><?php _e('You have specified these users for removal:'); ?></p>
<ul>
<?php
	$go_remove = false;
 	foreach ($userids as $id) {
 		$user = new WP_User($id);
		if ($id == $current_user->id) {
			echo "<li>" . sprintf(__('ID #%1s: %2s <strong>The current user will not be removed.</strong>'), $id, $user->user_login) . "</li>\n";
		} else {
			echo "<li><input type=\"hidden\" name=\"users[]\" value=\"{$id}\" />" . sprintf(__('ID #%1s: %2s'), $id, $user->user_login) . "</li>\n";
			$go_remove = true;
		}
 	}
 	?>
<?php if($go_remove) : ?>
		<input type="hidden" name="action" value="doremove" />
		<p class="submit"><input type="submit" name="submit" value="<?php _e('Confirm Removal'); ?>" /></p>
<?php else : ?>
	<p><?php _e('There are no valid users selected for removal.'); ?></p>
<?php endif; ?>
</div>
</form>
<?php

break;

case 'adduser':
	die( "This function is disabled. Add a user from your community." );
	check_admin_referer('add-user');

	if ( ! current_user_can('create_users') )
		die(__('You can&#8217;t create users.'));

	$user_id = add_user();
	if ( is_wp_error( $user_id ) )
		$errors = $user_id;
	else {
		header('Location: users.php?update=add');
		die();
	}

case 'addexistinguser':
	check_admin_referer();
	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t edit users.'));

	$new_user_email = wp_specialchars(trim($_POST['newuser']));
	/* checking that username has been typed */
	if ( !empty($new_user_email) ) {
		if ( $user_id = email_exists( $new_user_email ) ) {
			if ( array_key_exists($blog_id, get_blogs_of_user($user_id)) ) {
				$location = 'users.php?update=add_existing';
			} else {
				add_user_to_blog('', $user_id, $_POST[ 'new_role' ]);
				do_action( "added_existing_user", $user_id );
				$location = 'users.php?update=add';
			}
			header("Location: $location");
			die();
		}
	}
	header('Location: users.php');
	die();
break;
default:
	wp_enqueue_script( 'admin-users' );

	include ('admin-header.php');

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t edit users.'));
	$userids = array();
	$users = get_users_of_blog();
	foreach ( $users as $user )
		$userids[] = $user->user_id;

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
		case 'remove':
		?>
			<div id="message" class="updated fade"><p><?php _e('User removed from this blog.'); ?></p></div>
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
		case 'err_admin_remove':
		?>
			<div id="message" class="error"><p><?php _e("You can't remove the current user."); ?></p></div>
			<div id="message" class="updated fade"><p><?php _e('Other users have been removed.'); ?></p></div>
		<?php
			break;
		case 'notactive':
		?>
			<div id="message" class="updated fade"><p><?php _e('User not added. User is deleted or not active.'); ?></p></div>
		<?php
			break;
		case 'add_existing':
		?>
			<div id="message" class="updated fade"><p><?php _e('User not added. User is already registered.'); ?></p></div>
		<?php
			break;
		}
	endif; 
	if ( is_wp_error( $errors ) ) : ?>
	<div class="error">
		<ul>
		<?php
			foreach ( $errors->get_error_messages() as $message )
				 echo "<li>$message</li>";
		?>
		</ul>
	</div>
	<?php 
	endif;
	?>

<form action="" method="post" name="updateusers" id="updateusers">
<?php wp_nonce_field('bulk-users') ?>
<div class="wrap">
	<h2><?php _e('User List by Role'); ?></h2>
<table class="widefat">
<?php
foreach($roleclasses as $role => $roleclass) {
	ksort($roleclass);
?>

<tr>
	<th colspan="8" align="left"><h3><?php echo $wp_roles->role_names[$role]; ?></h3></th>
</tr>
<thead>
<tr>
	<th style="text-align: left"><?php _e('ID') ?></th>
	<th style="text-align: left"><?php _e('Username') ?></th>
	<th style="text-align: left"><?php _e('Name') ?></th>
	<th style="text-align: left"><?php _e('E-mail') ?></th>
	<th style="text-align: left"><?php _e('Website') ?></th>
	<th><?php _e('Posts') ?></th>
	<th>&nbsp;</th>
</tr>
</thead>
<tbody id="role-<?php echo $role; ?>"><?php
$style = '';
foreach ($roleclass as $user_object) {
	$style = (' class="alternate"' == $style) ? '' : ' class="alternate"';
	echo "\n\t" . user_row( $user_object, $style );
}

?>

</tbody>
<?php
}
?>
</table>


	<h2><?php _e('Update Users'); ?></h2>
  <ul style="list-style:none;">
  	<li><input type="radio" name="action" id="action0" value="removeuser" /> <label for="action0"><?php _e('Remove checked users from blog.'); ?></label></li>
  	<li>
		<input type="radio" name="action" id="action1" value="promote" /> <label for="action1"><?php _e('Set the Role of checked users to:'); ?></label>
		<select name="new_role"><?php wp_dropdown_roles(); ?></select>
	</li>
  </ul>
	<p class="submit"><input type="submit" value="<?php _e('Update &raquo;'); ?>" /></p>
</div>
</form>

<div class="wrap">
<h2><?php _e('Add User From Community') ?></h2>
<form action="" method="post" name="adduser" id="adduser">
  <?php wp_nonce_field('add-user') ?>
<input type='hidden' name='action' value='addexistinguser'>
<p>Type the e-mail address of another user to add them to your blog.</p>
<table>
<tr><th scope="row">User&nbsp;E-Mail: </th><td><input type="text" name="newuser" id="newuser"></td></tr>
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
    <input name="adduser" type="submit" id="addusersub" value="<?php _e('Add User &raquo;') ?>" />
  </p>
  </form>
<div id="ajax-response"></div>
</div>
	<?php

break;
}

include('admin-footer.php');
?>
