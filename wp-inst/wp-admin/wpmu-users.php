<?php
require_once('admin.php');

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if( $wpblog != 'main' || $user_level < 10) {
    die( __('<p>You do not have permission to access this page.</p>') );
}
if (isset($_GET['updated'])) {
    ?><div class="updated"><p><strong><?php _e('Options saved.') ?></strong></p></div><?php
}

print '<div class="wrap">';
switch( $_GET[ 'action' ] ) {
    case "edit":
    print "<h2>Edit User</h2>";
    $options_table_name = $wpmuBaseTablePrefix . $_GET[ 'id' ] ."_options";
    $query = "SELECT *
              FROM   ".$wpdb->users."
	      WHERE  ID = '".$_GET[ 'id' ]."'";
    $userdetails = $wpdb->get_results( $query, ARRAY_A );
    $query = "SELECT *
              FROM   ".$wpdb->usermeta."
	      WHERE  user_id = '".$_GET[ 'id' ]."'";
    $usermetadetails= $wpdb->get_results( $query, ARRAY_A );
    ?>
    <table><td valign='top'>
    <form name="form1" method="post" action="wpmu-edit.php?action=updateuser"> 
    <input type="hidden" name="id" value="<?php echo $_GET[ 'id' ] ?>" /> 
    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
    <?php
    unset( $userdetails[0][ 'ID' ] );
    while( list( $key, $val ) = each( $userdetails[0] ) ) { 
    ?>
	<tr valign="top"> 
	<th width="33%" scope="row"><?php echo ucwords( str_replace( "_", " ", $key ) ) ?></th> 
	<td><input name="option[<?php echo $key ?>]" type="text" id="<?php echo $val ?>" value="<?php echo $val ?>" size="40" /></td> 
	</tr> 
    <?php
    }
    ?>
    </table>
    </td><td valign='top'>
    <table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
	<tr><th style='text-align: left'>Name</th><th style='text-align: left'>Value</th><th style='text-align: left'>Delete</th></tr>
    <?php
    while( list( $key, $val ) = each( $usermetadetails ) ) { 
	if( substr( $val[ 'meta_key' ], -12 ) == 'capabilities' )
	    return;
    ?>
	<tr valign="top"> 
	<th width="33%" scope="row"><input name="metaname[<?php echo $val[ 'umeta_id' ] ?>]" type="text" id="<?php echo $val[ 'meta_key' ] ?>" value="<?php echo $val[ 'meta_key' ] ?>"></th> 
	<td><input name="meta[<?php echo $val[ 'umeta_id' ] ?>]" type="text" id="<?php echo $val[ 'meta_value' ] ?>" value="<?php echo addslashes( $val[ 'meta_value' ] ) ?>" size="40" /></td> 
	<td><input type='checkbox' name='metadelete[<?php echo $val[ 'umeta_id' ] ?>]'></td>
	</tr> 
    <?php
    }
    ?>
    </table>
    </td></table>

    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update User') ?> &raquo;" />
    </p>
    <?php
    break;
    default:
	$query = "SELECT * 
	          FROM ".$wpdb->users;
	if( $_GET[ 's' ] != '' ) {
	    $query .= " WHERE user_login LIKE '%".$_GET[ 's' ]."%'";
	}
	$user_list = $wpdb->get_results( $query, ARRAY_A );
?>
<h2>Users</h2>
<form name="searchform" action="wpmu-admin.php" method="get" style="float: left; width: 16em; margin-right: 3em;"> 
  <fieldset> 
  <legend><?php _e('Search Users&hellip;') ?></legend> 
  <input type='hidden' name='action' value='users'>
  <input type="text" name="s" value="<?php if (isset($_GET[ 's' ])) echo wp_specialchars($_GET[ 's' ], 1); ?>" size="17" /> 
  <input type="submit" name="submit" value="<?php _e('Search') ?>"  /> 
  </fieldset>
</form>

<br style="clear:both;" />

<?php

// define the columns to display, the syntax is 'internal name' => 'display name'
$posts_columns = array(
  'ID'              => __('ID'),
  'user_login'      => __('Login'),
  'user_email'      => __('Email'),
  'display_name'    => __('Name'),
  'user_registered' => __('Registered'),
  'blogs'           => __('Blogs')
);
$posts_columns = apply_filters('manage_posts_columns', $posts_columns);

// you can not edit these at the moment
$posts_columns['control_edit']   = '';
$posts_columns['control_delete'] = '';

?>

<table width="100%" cellpadding="3" cellspacing="3"> 
	<tr>

<?php foreach($posts_columns as $column_display_name) { ?>
	<th scope="col"><?php echo $column_display_name; ?></th>
<?php } ?>

	</tr>
<?php
if ($user_list) {
$bgcolor = '';
foreach ($user_list as $user) { 
$class = ('alternate' == $class) ? '' : 'alternate';
?> 
	<tr class='<?php echo $class; ?>'>

<?php

foreach($posts_columns as $column_name=>$column_display_name) {

	switch($column_name) {
	
	case 'ID':
		?>
		<th scope="row"><?php echo $user[ 'ID' ] ?></th>
		<?php
		break;

	case 'user_login':
		?>
		<td><?php echo $user[ 'user_login' ] ?>
		</td>
		<?php
		break;

	case 'display_name':
		?>
		<td><?php echo $user[ 'display_name' ] ?></td>
		<?php
		break;

	case 'user_email':
		?>
		<td><?php echo $user[ 'user_email' ] ?></td>
		<?php
		break;

	case 'user_registered':
		?>
		<td><?php echo $user[ 'user_registered' ] ?></td>
		<?php
		break;

	case 'blogs':
		?>
		<td><?php $blogs = get_blogs_of_user( $user[ 'ID' ] ); if( is_array( $blogs ) ) while( list( $key, $val ) = each( $blogs ) ) { print '<a href="wpmu-blogs.php?action=editblog&id=' . $val->userblog_id . '">' . str_replace( '.' . $current_site->domain, '', $val->domain ) . '</a><BR>'; } ?></td>
		<?php
		break;

	case 'control_edit':
		?>
		<td><?php echo "<a href='user-edit.php?user_id=".$user[ 'ID' ]."' class='edit'>" . __('Edit') . "</a>"; ?></td>
		<?php
		break;

	case 'control_delete':
		?>
		<td><?php echo "<a href='wpmu-users.php?action=delete&amp;id=".$user[ 'ID' ]."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to delete this user?\\n  \'OK\' to delete, \'Cancel\' to stop.") ) . "')\">" . __('Delete') . "</a>"; ?></td>
		<?php
		break;

	default:
		?>
		<td><?php do_action('manage_posts_custom_column', $column_name, $id); ?></td>
		<?php
		break;
	}
}
?>
	</tr> 
<?php
}
} else {
?>
  <tr style='background-color: <?php echo $bgcolor; ?>'> 
    <td colspan="8"><?php _e('No users found.') ?></td> 
  </tr> 
<?php
} // end if ($users)
?> 
</table> 

<div class="navigation">
<div class="alignleft"><?php //next_posts_link(__('&laquo; Previous Entries')) ?></div>
<div class="alignright"><?php //previous_posts_link(__('Next Entries &raquo;')) ?></div>
</div>
<?php
}

?>
</div>
<?php include('admin-footer.php'); ?>
