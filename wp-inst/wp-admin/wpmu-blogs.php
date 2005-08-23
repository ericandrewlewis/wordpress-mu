<?php
require_once('admin.php');

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if( is_site_admin() == false ) {
    die( __('<p>You do not have permission to access this page.</p>') );
}
if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}
print '<div class="wrap">';
switch( $_GET[ 'action' ] ) {
    case "editblog":
    $options_table_name = $wpmuBaseTablePrefix . $_GET[ 'id' ] ."_options";
    $query = "SELECT *
              FROM   ".$options_table_name."
	      WHERE  option_name NOT LIKE 'rss%'
	      AND    option_name NOT LIKE '%user_roles'";
    $options = $wpdb->get_results( $query, ARRAY_A );
    $query = "SELECT * 
	      FROM ".$wpdb->blogs." 
              WHERE blog_id = '".$_GET[ 'id' ]."'";
    $details = $wpdb->get_row( $query, ARRAY_A );
    $is_archived = get_settings( "is_archived" );
    if( $is_archived == '' )
	$is_archived = 'no';

    print "<h2>Edit Blog</h2>";
    ?>
    <form name="form1" method="post" action="wpmu-edit.php?action=updateblog"> 
    <input type="hidden" name="id" value="<?php echo $_GET[ 'id' ] ?>" /> 
    <table><td valign='top'>
    <table width="100%" border='0' cellspacing="2" cellpadding="5" class="editform"> 
	<tr valign="top"> 
	<th scope="row">URL</th> 
	<td>http://<input name="blog[domain]" type="text" id="domain" value="<?php echo $details[ 'domain' ] ?>" size="33" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Path</th> 
	<td><input name="blog[path]" type="text" id="path" value="<?php echo $details[ 'path' ] ?>" size="40" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Registered</th> 
	<td><input name="blog[registered]" type="text" id="blog_registered" value="<?php echo $details[ 'registered' ] ?>" size="40" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Last Updated</th> 
	<td><input name="blog[last_updated]" type="text" id="blog_last_updated" value="<?php echo $details[ 'last_updated' ] ?>" size="40" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Public</th> 
	<td><input type='radio' name='blog[is_public]' value='yes'<?php if( $details[ 'is_public' ] == 'yes' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='blog[is_public]' value='no'<?php if( $details[ 'is_public' ] == 'no' ) echo " checked"?>> No &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Archived</th> 
	<td><input type='radio' name='option[is_archived]' value='yes'<?php if( $is_archived == 'yes' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='option[is_archived]' value='no'<?php if( $is_archived == 'no' ) echo " checked"?>> No &nbsp;&nbsp;
	    </td> 
	</tr> 
    <tr><td colspan='2'>
    <br />
    <br />
    </td></tr>
    <?php
    while( list( $key, $val ) = each( $options ) ) { 
	$kellog = @unserialize( $val[ 'option_value' ] );
	if( is_array( $kellog ) ) {
		print '<tr valign="top"> 
		       <th scope="row">' . ucwords( str_replace( "_", " ", $val[ 'option_name' ] ) ) . '</th> 
		       <td>';
		print '<textarea rows="5" cols="40" disabled>';
		reset( $kellog );
		while( list( $key, $val ) = each( $kellog ) ) 
		{ 
		    if( is_array( $val ) ) {
			print "$key:\n";
			while( list( $k, $v ) = each( $val ) ) {
			    if( is_array( $v ) ) {
				print "    $k:\n";
				while( list( $k1, $v1 ) = each( $v ) ) {
				    print "      $k1 -> $v1\n"; 
				}
			    } else {
				if( $v1 != '' )
				    print "  $k1 -> $v1\n";
			    }
			}
		    } else {
			if( $val != '' )
			    print "$key -> $val\n";
		    }
		}
		print '</textarea></td></tr>';
	} else {
	    	?>
		<tr valign="top"> 
		<th scope="row"><?php echo ucwords( str_replace( "_", " ", $val[ 'option_name' ] ) ) ?></th> 
		<td><input name="option[<?php echo $val[ 'option_name' ] ?>]" type="text" id="<?php echo $val[ 'option_name' ] ?>" value="<?php echo $val[ 'option_value' ] ?>" size="40" /></td> 
		</tr> 
		<?php
	}
    }
    ?>
    </table>
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
    </p>
    </td>
    <td valign='top'>
    <?php
    $themes = get_themes();
    $query = "SELECT option_value
              FROM   ".$options_table_name."
	      WHERE  option_name = 'allowed_themes'";
    $blog_allowed_themes = $wpdb->get_var( $query );
    if( $blog_allowed_themes != false )
	$blog_allowed_themes = unserialize( $blog_allowed_themes );
    $allowed_themes = get_site_option( "allowed_themes" );
    if( $allowed_themes == false ) {
	$allowed_themes = array_keys( $themes );
    }
    $out = '';
    while( list( $key, $val ) = each( $themes ) ) { 
	if( isset( $allowed_themes[ $key ] ) == false ) {
	    if( isset( $blog_allowed_themes[ $key ] ) == true ) {
		$checked = 'checked ';
	    } else {
		$checked = '';
	    }

	    $out .= '
		<tr valign="top"> 
		<th title="' . htmlspecialchars( $val[ "Description" ] ) . '" scope="row">'.$key.'</th> 
		<td><input name="theme['.$key.']" type="checkbox" id="'.$key.'" value="on" '.$checked.'/></td> 
		</tr> ';
	}
    }
    if( $out != '' ) {
	print "<h3>Blog Themes</h3>";
	print '<table width="100%" border="0" cellspacing="2" cellpadding="5" class="editform">';
	print '<tr><th>Theme</th><th>Enable</th></tr>';
	print $out;
	print "</table>";
    }
    $blogusers = get_users_of_blog( $_GET[ 'id' ] );
    print "<h3>Blog Users</h3>";
    if( is_array( $blogusers ) ) {
	    print "<table width='100%'><caption>Current Users</caption>";
	    print "<tr><th>User</th><th>Role</th><th>Remove</th><th></th></tr>";
	    reset( $blogusers );
	    while( list( $key, $val ) = each( $blogusers ) ) 
	    { 
		    $t = @unserialize( $val->meta_value );
		    if( is_array( $t ) ) {
			    reset( $t );
			    $existing_role = key( $t );
		    }
		    print "<tr><td>" . $val->user_login . "</td>";
		    if( $val->user_id != $current_user->data->ID ) {
			    ?>
			    <td><select name="role[<?php echo $val->user_id ?>]" id="new_role"><?php 
				    foreach($wp_roles->role_names as $role => $name) {
					    $selected = '';
					    if( $role == $existing_role )
						    $selected = 'selected="selected"';
					    echo "<option {$selected} value=\"{$role}\">{$name}</option>";
				    }
			    ?></select></td> <?php
			    print "<td><input title='Click to remove user' type='checkbox' name='blogusers[" . $val->user_id . "]'></td>";
		    } else {
			    print "<td><b>N/A</b></td><td><b>N/A</b></td>";
		    }
		    print "<td><a href='user-edit.php?user_id=" . $val->user_id . "'>Edit</td></tr>";
	    }
	    print "</table>";
    }
    print "<h3>Add a new user</h3>";
    ?>
<p>As you type WordPress will offer you a choice of usernames.<br /> Click them to select and hit <em>Update Options</em> to add the user.</p>
<table>
<tr><th scope="row">User&nbsp;Login: </th><td><input type="text" name="newuser" id="newuser"></td><td><a href="javascript:doSearch();">Search</a></td></tr>
<tr><td></td><td colspan='2'><div style='display:none; height: 60px; width: 100px; overflow: auto; border: 1px solid #ccc; background: #eee; margin: 5px; padding: 5px;' id="searchresults"><?php _e( 'Search Results' ) ?></div></td> </tr>
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
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
</p> 
<?php AJAX_search_box( "wpmu-edit.php?action=searchusers&search=", "newuser", "searchresults" ); ?>

    </td>
    </table>
    <?php
    break;
    default:
	$query = "SELECT * 
	          FROM ".$wpdb->blogs." 
		  WHERE site_id = '".$wpdb->siteid."'";
	if( $_GET[ 's' ] != '' ) {
	    $query = "SELECT * 
	              FROM ".$wpdb->blogs.", ".$wpdb->site." 
		      WHERE site_id = '".$wpdb->siteid."'
		      AND   ".$wpdb->blogs.".site_id = ".$wpdb->site.".id
		      AND   blogname like '%".$_GET[ 's' ]."%'";
	}
	$blog_list = $wpdb->get_results( $query, ARRAY_A );
?>
<h2>Blogs</h2>
<form name="searchform" action="wpmu-blogs.php" method="get" style="float: left; width: 16em; margin-right: 3em;"> 
  <fieldset> 
  <legend><?php _e('Search Blogs&hellip;') ?></legend> 
  <input type='hidden' name='action' value='blogs'>
  <input type="text" name="s" value="<?php if (isset($_GET[ 's' ])) echo wp_specialchars($_GET[ 's' ], 1); ?>" size="17" /> 
  <input type="submit" name="submit" value="<?php _e('Search') ?>"  /> 
  </fieldset>
</form>

<br style="clear:both;" />

<?php

// define the columns to display, the syntax is 'internal name' => 'display name'
$posts_columns = array(
  'id'           => __('ID'),
  'blogname'     => __('Blog Name'),
  'last_updated' => __('Last Updated'),
  'registered'   => __('Registered'),
  'users'        => __('Users')
);
$posts_columns = apply_filters('manage_posts_columns', $posts_columns);

// you can not edit these at the moment
$posts_columns['control_view']      = '';
$posts_columns['control_edit']      = '';
$posts_columns['control_backend']   = '';
$posts_columns['control_deactivate']    = '';
$posts_columns['control_delete']    = '';

?>

<table width="100%" cellpadding="3" cellspacing="3"> 
	<tr>

<?php foreach($posts_columns as $column_display_name) { ?>
	<th scope="col"><?php echo $column_display_name; ?></th>
<?php } ?>

	</tr>
<?php
if ($blog_list) {
$bgcolor = '';
foreach ($blog_list as $blog) { 
$class = ('alternate' == $class) ? '' : 'alternate';
?> 
	<tr class='<?php echo $class; ?>'>

<?php

foreach($posts_columns as $column_name=>$column_display_name) {

	switch($column_name) {
	
	case 'id':
		?>
		<th scope="row"><?php echo $blog[ 'blog_id' ] ?></th>
		<?php
		break;

	case 'blogname':
		?>
		<td valign='top'><?php echo str_replace( '.' . $current_site->domain, '', $blog[ 'domain' ] ) ?>
		</td>
		<?php
		break;

	case 'last_updated':
		?>
		<td valign='top'><?php echo $blog[ 'last_updated' ] == '0000-00-00 00:00:00' ? "Never" : $blog[ 'last_updated' ] ?></td>
		<?php
		break;

	case 'registered':
		?>
		<td valign='top'><?php echo $blog[ 'registered' ] ?></td>
		<?php
		break;

	case 'users':
		?>
		<td valign='top'><?php $blogusers = get_users_of_blog( $blog[ 'blog_id' ] ); if( is_array( $blogusers ) ) while( list( $key, $val ) = each( $blogusers ) ) { print '<a href="user-edit.php?user_id=' . $val->user_id . '">' . $val->user_login . '</a><BR>'; }  ?></td>
		<?php
		break;

	case 'control_view':
		?>
		<td valign='top'><a href="http://<?php echo $blog[ 'domain' ]; ?>" rel="permalink" class="edit"><?php _e('View'); ?></a></td>
		<?php
		break;

	case 'control_edit':
		?>
		<td valign='top'><?php echo "<a href='wpmu-blogs.php?action=editblog&amp;id=".$blog[ 'blog_id' ]."' class='edit'>" . __('Edit') . "</a>"; ?></td>
		<?php
		break;

	case 'control_backend':
		?>
		<td valign='top'><?php echo "<a href='http://" . $blog[ 'domain' ] . $current_site->path . "wp-admin/' class='edit'>" . __('Backend') . "</a>"; ?></td>
		<?php
		break;

	case 'control_deactivate':
		?>
		<td valign='top'><?php echo "<a href='wpmu-edit.php?action=deactivateblog&amp;id=".$blog[ 'blog_id' ]."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to deactivate this blog?\\n  \'OK\' to delete, \'Cancel\' to stop.") ) . "')\">" . __('Deactivate') . "</a>"; ?></td>
		<?php
		break;

	case 'control_delete':
		?>
		<td valign='top'><?php echo "<a href='wpmu-edit.php?action=deleteblog&amp;id=".$blog[ 'blog_id' ]."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to delete this blog?\\n  \'OK\' to delete, \'Cancel\' to stop.") ) . "')\">" . __('Delete') . "</a>"; ?></td>
		<?php
		break;

	default:
		?>
		<td valign='top'><?php do_action('manage_posts_custom_column', $column_name, $id); ?></td>
		<?php
		break;
	}
}
?>
	</tr> 
<?php
}
print "</table>";
} else {
?>
  <tr style='background-color: <?php echo $bgcolor; ?>'> 
    <td colspan="8"><?php _e('No blogs found.') ?></td> 
  </tr> 
<?php
} // end if ($blogs)

break;
} // end switch( $action )
?> 

<div class="navigation">
<div class="alignleft"><?php //next_posts_link(__('&laquo; Previous Entries')) ?></div>
<div class="alignright"><?php //previous_posts_link(__('Next Entries &raquo;')) ?></div>
</div>

</div>
<?php include('admin-footer.php'); ?>
