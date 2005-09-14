<?php
require_once('admin.php');
require_once( ABSPATH . WPINC . '/registration-functions.php');

$title = __('Users');
$parent_file = 'profile.php';
	
$action = $_REQUEST['action'];

switch ($action) {

case 'promote':
	check_admin_referer();

	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t edit users.'));

	$userids = $_POST['users'];
	foreach($userids as $id) {
		$user = new WP_User($id);
		$user->set_role($_POST['new_role']);
	}
		
	header('Location: users.php?update=promote');

break;

case 'dodelete':

	check_admin_referer();

	if( is_site_admin() == false ) {
		header('Location: users.php?update=del');
		exit;
	}
	if (empty($_POST['users'])) {
		header('Location: users.php');
	}

	if ( !current_user_can('edit_users') )
		die(__('You can&#8217;t delete users.'));

	$userids = $_POST['users'];
	
	foreach($userids as $id) {
		switch($_POST['delete_option']) {
		case 'delete':
			wp_delete_user($id);
			break;
		case 'reassign':
			wp_delete_user($id, $_POST['reassign_user']);
			break;
		}
	}

	header('Location: users.php?update=del');

break;

case 'delete':

	if( is_site_admin() == false ) {
		header('Location: users.php');
		exit;
	}

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
	foreach($userids as $id) {
		$user = new WP_User($id);
		echo "<li><input type=\"hidden\" name=\"users[]\" value=\"{$id}\" />";
		echo "{$id}: {$user->data->user_login}</li>\n";
	}
	$all_logins = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users ORDER BY user_login");
	$user_dropdown = '<select name="reassign_user">';
	foreach($all_logins as $login) {
		if(!in_array($login->ID, $userids)) {
			$user_dropdown .= "<option value=\"{$login->ID}\">{$login->user_login}</option>";
		}
	}
	$user_dropdown .= '</select>';
	?>
	</ul>
	<p><?php _e('What should be done with posts and links owned by this user?'); ?></p>
	<ul style="list-style:none;">
		<li><label><input type="radio" id="delete_option0" name="delete_option" value="delete" checked="checked" />
		<?php _e('Delete all posts and links.'); ?></label></li>
		<li><input type="radio" id="delete_option1" name="delete_option" value="reassign" />
		<?php echo sprintf(__('<label for="delete_option1">Attribute all posts and links to:</label> %s'), $user_dropdown); ?></li>
	</ul>
	<input type="hidden" name="action" value="dodelete" />
	<p class="submit"><input type="submit" name="submit" value="<?php _e('Confirm Deletion'); ?>" /></p>
</div>
</form>
<?php

break;

case 'addexistinguser':
	check_admin_referer();
	$new_user_login = wp_specialchars(trim($_POST['newuser']));
	/* checking that username has been typed */
	if ($new_user_login == '')
		$errors['user_login'] = __('<strong>ERROR</strong>: Please enter a username.');
	if ( username_exists( $new_user_login ) ) {
		$user_ID = $wpdb->get_var( "SELECT ID FROM $wpdb->users WHERE user_login = '$new_user_login'" );
		$user = new WP_User($user_ID);
		$user->set_role( $_POST[ 'new_role' ] );
	}
	header('Location: users.php?update=add');
	die();
break;

case 'adduser':
	exit;
	check_admin_referer();
	
	$errors = add_user();
	
	if(count($errors) == 0) {
		header('Location: users.php?update=add');
		die();
	}

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
	
	if( is_site_admin() ) {
	    $userids = $wpdb->get_col("SELECT ID FROM $wpdb->users");
	} else {
	    $userids = $wpdb->get_col("SELECT ID FROM $wpdb->users, $wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id AND meta_key = '".$wpdb->prefix."capabilities'");
	}
	
	foreach($userids as $userid) {
		$tmp_user = new WP_User($userid);
		$roles = $tmp_user->roles;
		$role = $roles[0];
		$roleclasses[$role][$tmp_user->data->user_login] = $tmp_user;
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
		$user_data = &$user_object->data;
		if( $user_data->ID != get_site_option( "admin_user_id" ) ) {
			$email = $user_data->user_email;
			$url = $user_data->user_url;
			$short_url = str_replace('http://', '', $url);
			$short_url = str_replace('www.', '', $short_url);
			if ('/' == substr($short_url, -1))
				$short_url = substr($short_url, 0, -1);
			if (strlen($short_url) > 35)
				$short_url =  substr($short_url, 0, 32).'...';
			$style = ('class="alternate"' == $style) ? '' : 'class="alternate"';
			$numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = '$user_data->ID' and post_status = 'publish'");
			if (0 < $numposts) $numposts = "<a href='edit.php?author=$user_data->ID' title='" . __('View posts') . "'>$numposts</a>";
			echo "
				<tr $style>
				<td><input type='checkbox' name='users[]' id='user_{$user_data->ID}' value='{$user_data->ID}' /> <label for='user_{$user_data->ID}'>{$user_data->ID}</label></td>
				<td><label for='user_{$user_data->ID}'><strong>$user_data->user_login</strong></label></td>
				<td><label for='user_{$user_data->ID}'>$user_data->first_name $user_data->last_name</label></td>
				<td><a href='mailto:$email' title='" . sprintf(__('e-mail: %s'), $email) . "'>$email</a></td>";
			    ?>
			    <td><select name="new_roles[<?php echo $user_data->ID ?>]" id="new_role"><?php 
				    foreach($wp_roles->role_names as $roleid => $name) {
					    $selected = '';
					    if( $role == $roleid)
						    $selected = 'selected="selected"';
					    echo "<option {$selected} value=\"{$roleid}\">{$name}</option>";
				    }
			    ?></select></td> <?php
			echo "<td align='right'>$numposts</td>";
			echo '<td>';
			if (is_site_admin())
				echo "<a href='user-edit.php?user_id=$user_data->ID' class='edit'>".__('Edit')."</a>";
			echo '</td>';
			echo '</tr>';
		} else {
			echo "<tr class='alternate'><td><label for='user_{$user_data->ID}'>{$user_data->ID}</label></td><td><label for='user_{$user_data->ID}'><strong>$user_data->user_login</strong></label></td><td><label for='user_{$user_data->ID}'>$user_data->first_name $user_data->last_name</label></td><td colspan='4'><strong>Cannot Edit Site Administrator</strong></td></tr>";
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
	<?php if( is_site_admin() ) { ?>
  	<li><input type="radio" name="action" id="action0" value="delete" /> <label for="action0"><?php _e('Delete checked users.'); ?></label></li>
	<?php } ?>
  	<li><input type="radio" name="action" id="action1" value="promote" /> <?php echo sprintf(__('<label for="action1">Set the Role of checked users to:</label> %s'), $role_select); ?></li>
  </ul>
	<p class="submit"><input type="submit" value="<?php _e('Update &raquo;'); ?>" /></p>
</div>
</form>

<div class="wrap">
<h2><?php _e('Add User From Community') ?></h2>
<form action="" method="post" name="adduser" id="adduser">
<input type='hidden' name='action' value='addexistinguser'>
<p>As you type WordPress will offer you a choice of usernames.<br /> Click one to select and hit <em>Add User</em> to add the user.</p>
<table>
<tr><th scope="row">User&nbsp;Login: </th><td><input type="text" name="newuser" id="newuser"></td><td><a href="javascript:doSearch();">Search</a></td></tr>
<tr><td></td><td colspan='2'><div style='display:none; height: 60px; width: 200px; overflow: auto; border: 1px solid #ccc; background: #eee; margin: 5px; padding: 5px;' id="searchresults"><?php _e( 'Search Results' ) ?></div></td> </tr>
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
<?php AJAX_search_box( "users.php?action=searchusers&search=", "newuser", "searchresults" ); ?>

    </td>
    </table>
  <p class="submit">
    <input name="adduser" type="submit" id="adduser" value="<?php _e('Add User') ?> &raquo;" />
  </p>
  </form>
</div>

<?php /*
<div class="wrap">
<h2><?php _e('Add New User') ?></h2>
<?php printf(__('<p>Users can <a href="%s/wp-register.php">register themselves</a> or you can manually create users here.</p>'), get_settings('siteurl')); ?>
<form action="" method="post" name="adduser" id="adduser">
  <table class="editform" width="100%" cellspacing="2" cellpadding="5">
    <tr>
      <th scope="row" width="33%"><?php _e('Nickname') ?>
      <input name="action" type="hidden" id="action" value="adduser" /></th>
      <td width="66%"><input name="user_login" type="text" id="user_login" value="<?php echo $new_user_login; ?>" /></td>
    </tr>
    <tr>
      <th scope="row"><?php _e('First Name') ?> </th>
      <td><input name="firstname" type="text" id="firstname" value="<?php echo $new_user_firstname; ?>" /></td>
    </tr>
    <tr>
      <th scope="row"><?php _e('Last Name') ?> </th>
      <td><input name="lastname" type="text" id="lastname" value="<?php echo $new_user_lastname; ?>" /></td>
    </tr>
    <tr>
      <th scope="row"><?php _e('E-mail') ?></th>
      <td><input name="email" type="text" id="email" value="<?php echo $new_user_email; ?>" /></td>
    </tr>
    <tr>
      <th scope="row"><?php _e('Website') ?></th>
      <td><input name="uri" type="text" id="uri" value="<?php echo $new_user_uri; ?>" /></td>
    </tr>
<?php
$show_password_fields = apply_filters('show_password_fields', true);
if ( $show_password_fields ) :
?>
    <tr>
      <th scope="row"><?php _e('Password (twice)') ?> </th>
      <td><input name="pass1" type="password" id="pass1" />
      <br />
      <input name="pass2" type="password" id="pass2" /></td>
    </tr>
<?php endif; ?>
  </table>
  <p class="submit">
    <input name="adduser" type="submit" id="adduser" value="<?php _e('Add User') ?> &raquo;" />
  </p>
  </form>
</div>
*/ ?>
	<?php

break;
}

include('admin-footer.php');
?>
