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

    print "<h2>Edit Blog</h2>";
    print "<a href='http://{$details[ 'domain' ]}/'>{$details[ 'domain' ]}</a>";
    ?>
    <form name="form1" method="post" action="wpmu-edit.php?action=updateblog"> 
    <input type="hidden" name="id" value="<?php echo $_GET[ 'id' ] ?>" /> 
    <table><td valign='top'>
    <div class="wrap">
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
	<td><input type='radio' name='blog[public]' value='1' <?php if( $details[ 'public' ] == '1' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='blog[public]' value='0' <?php if( $details[ 'public' ] == '0' ) echo " checked"?>> No &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Archived</th> 
	<td><input type='radio' name='blog[archived]' value='1' <?php if( $details[ 'archived' ] == '1' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='blog[archived]' value='0' <?php if( $details[ 'archived' ] == '0' ) echo " checked"?>> No &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Mature</th> 
	<td><input type='radio' name='blog[mature]' value='1' <?php if( $details[ 'mature' ] == '1' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='blog[mature]' value='0' <?php if( $details[ 'mature' ] == '0' ) echo " checked"?>> No &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Spam</th> 
	<td><input type='radio' name='blog[spam]' value='1' <?php if( $details[ 'spam' ] == '1' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='blog[spam]' value='0' <?php if( $details[ 'spam' ] == '0' ) echo " checked"?>> No &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row">Deleted</th> 
	<td><input type='radio' name='blog[deleted]' value='1' <?php if( $details[ 'deleted' ] == '1' ) echo " checked"?>> Yes&nbsp;&nbsp;
	    <input type='radio' name='blog[deleted]' value='0' <?php if( $details[ 'deleted' ] == '0' ) echo " checked"?>> No &nbsp;&nbsp;
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
		<td><input name="option[<?php echo $val[ 'option_name' ] ?>]" type="text" id="<?php echo $val[ 'option_name' ] ?>" value="<?php echo stripslashes( $val[ 'option_value' ] ) ?>" size="40" /></td> 
		</tr> 
		<?php
	}
    }
    ?>
    </table>
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
    </p>
    </div>
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
	print "<div class='wrap'><h3>Blog Themes</h3>";
	print '<table width="100%" border="0" cellspacing="2" cellpadding="5" class="editform">';
	print '<tr><th>Theme</th><th>Enable</th></tr>';
	print $out;
	print "</table></div>";
    }
    $blogusers = get_users_of_blog( $_GET[ 'id' ] );
    print "<div class='wrap'><h3>Blog Users</h3>";
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
<?php autocomplete_css(); ?>
<p>As you type WordPress will offer you a choice of usernames.<br /> Click them to select and hit <em>Update Options</em> to add the user.</p>
<table>
<tr><th scope="row">User&nbsp;Login: </th><td><input type="text" name="newuser" id="newuser"></td></tr>
<tr><td></td><td><div id="searchresults" class="autocomplete"></div></td> </tr>
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
</div>
<div class='wrap'><strong>Misc Blog Actions</strong>
<p><?php do_action( "wpmueditblogaction", $_GET[ 'id' ] ); ?></p>
</div>
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
</p> 
<?php autocomplete_textbox( "wpmu-edit.php?action=searchusers&search=", "newuser", "searchresults" ); ?>

    </td>
    </table>
    <?php
    break;
    default:
		if( isset( $_GET[ 'start' ] ) == false ) {
			$start = 0;
		} else {
			$start = intval( $_GET[ 'start' ] );
		}
		if( isset( $_GET[ 'num' ] ) == false ) {
			$num = 60;
		} else {
			$num = intval( $_GET[ 'num' ] );
		}

		$query = "SELECT * 
			FROM ".$wpdb->blogs." 
			WHERE site_id = '".$wpdb->siteid."' ";
		if( $_GET[ 's' ] != '' ) {
			$query = "SELECT blog_id, {$wpdb->blogs}.domain, registered, last_updated
				FROM $wpdb->blogs, $wpdb->site 
				WHERE site_id = '$wpdb->siteid'
				AND   {$wpdb->blogs}.site_id = {$wpdb->site}.id
				AND   {$wpdb->blogs}.domain like '%". $_GET[ 's' ]."%'";
		} elseif( $_GET[ 'blog_id' ] != '' ) {
			$query = "SELECT * 
				FROM $wpdb->blogs 
				WHERE site_id = '$wpdb->siteid'
				AND   blog_id = '".intval($_GET[ 'blog_id' ])."'";
		} elseif( $_GET[ 'ip_address' ] != '' ) {
			$query = "SELECT * 
				FROM $wpdb->blogs, wp_registration_log
				WHERE site_id = '$wpdb->siteid'
				AND   {$wpdb->blogs}.blog_id = wp_registration_log.blog_id
				AND   wp_registration_log.IP LIKE ('%".$_GET[ 'ip_address' ]."%')";
		}
		if( isset( $_GET[ 'sortby' ] ) == false ) {
			$_GET[ 'sortby' ] = 'ID';
		}
		if( $_GET[ 'sortby' ] == 'Registered' ) {
			$query .= ' ORDER BY registered ';
		} elseif( $_GET[ 'sortby' ] == 'ID' ) {
			$query .= ' ORDER BY ' . $wpdb->blogs . '.blog_id ';
		} elseif( $_GET[ 'sortby' ] == 'Last Updated' ) {
			$query .= ' ORDER BY last_updated ';
		} elseif( $_GET[ 'sortby' ] == 'Blog Name' ) {
			$query .= ' ORDER BY domain ';
		}
		if( $_GET[ 'order' ] == 'DESC' ) {
			$query .= "DESC";
		} else {
			$query .= "ASC";
		}

		if ( $_GET[ 'ip_address' ] == '' )
			$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
		$blog_list = $wpdb->get_results( $query, ARRAY_A );
		if( count( $blog_list ) < $num ) {
			$next = false;
		} else {
			$next = true;
		}
?>
<script language="javascript">
<!--
var checkflag = "false";
function check_all_rows() {
	field = document.formlist;
	if (checkflag == "false") {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allblogs[]' )
				field[i].checked = true;}
		checkflag = "true";
		return "Uncheck All"; 
	} else {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allblogs[]' )
				field[i].checked = false; }
		checkflag = "false";
		return "Check All"; 
	}
}
//  -->
</script>

<h2>Blogs</h2>
<form name="searchform" action="wpmu-blogs.php" method="get" style="float: left; width: 16em; margin-right: 3em;"> 
  <table><td>
  <fieldset> 
  <legend><?php _e('Search Blogs&hellip;') ?></legend> 
  <input type='hidden' name='action' value='blogs'>
  Name:&nbsp;<input type="text" name="s" value="<?php if (isset($_GET[ 's' ])) echo wp_specialchars($_GET[ 's' ], 1); ?>" size="17" /><br />
  Blog&nbsp;ID:&nbsp;<input type="text" name="blog_id" value="<?php if (isset($_GET[ 'blog_id' ])) echo wp_specialchars($_GET[ 'blog_id' ], 1); ?>" size="10" /><br />
  IP Address: <input type="text" name="ip_address" value="<?php if (isset($_GET[ 'ip_address' ])) echo wp_specialchars($_GET[ 'ip_address' ], 1); ?>" size="10" /><br />
  <input type="submit" name="submit" value="<?php _e('Search') ?>"  /> 
  </fieldset>
  <?php
  if( isset($_GET[ 's' ]) && $_GET[ 's' ] != '' ) {
	  ?><a href="/wp-admin/wpmu-users.php?action=users&s=<?php echo wp_specialchars($_GET[ 's' ], 1) ?>">Search Users: <?php echo wp_specialchars($_GET[ 's' ], 1) ?></a><?php
  }
  ?>
  </td><td>
  <fieldset> 
  <legend><?php _e('Blog Navigation') ?></legend> 
  <?php 

  $url2 = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ] . "&s=" . $_GET[ 's' ] . "&ip_address=" . $_GET[ 'ip_address' ];

  if( $start == 0 ) { 
	  echo 'Previous&nbsp;Blogs';
  } elseif( $start <= 30 ) { 
	  echo '<a href="wpmu-blogs.php?start=0&' . $url2 . ' ">Previous&nbsp;Blogs</a>';
  } else {
	  echo '<a href="wpmu-blogs.php?start=' . ( $start - $num ) . '&' . $url2 . '">Previous&nbsp;Blogs</a>';
  } 
  if ( $next ) {
	  echo '&nbsp;||&nbsp;<a href="wpmu-blogs.php?start=' . ( $start + $num ) . '&' . $url2 . '">Next&nbsp;Blogs</a>';
  } else {
	  echo '&nbsp;||&nbsp;Next&nbsp;Blogs';
  }
  ?>
  </fieldset>
  </td></table>
</form>

<br style="clear:both;" />

<?php

// define the columns to display, the syntax is 'internal name' => 'display name'
$posts_columns = array(
  'id'           => __('ID'),
  'blogname'     => __('Blog Name'),
  'last_updated' => __('Last Updated'),
  'registered'   => __('Registered'),
  'users'        => __('Users'),
  'plugins'      => __('Actions')
);
$posts_columns = apply_filters('manage_posts_columns', $posts_columns);

// you can not edit these at the moment
$posts_columns['control_view']      = '';
$posts_columns['control_edit']      = '';
$posts_columns['control_backend']   = '';
$posts_columns['control_deactivate']    = '';
$posts_columns['control_spam']    = '';
$posts_columns['control_delete']    = '';

?>

<form name='formlist' action='wpmu-edit.php?action=allblogs' method='POST'>
<input type=button value="Check All" onClick="this.value=check_all_rows()"> 
<table width="100%" cellpadding="3" cellspacing="3"> 
	<tr>

<?php foreach($posts_columns as $column_display_name) { ?>
	<th scope="col"><a href="wpmu-blogs.php?sortby=<?php echo urlencode( $column_display_name ) ?>&<?php if( $_GET[ 'sortby' ] == $column_display_name ) { if( $_GET[ 'order' ] == 'DESC' ) { echo "order=ASC&" ; } else { echo "order=DESC&"; } } ?>start=<?php echo $start ?>"><?php echo $column_display_name; ?></a></th>
<?php } ?>

	</tr>
<?php
if ($blog_list) {
	$bgcolor = '';
	$status_list = array( "archived" => "#fee", "spam" => "#faa", "deleted" => "#f55" );
	foreach ($blog_list as $blog) { 
		$class = ('alternate' == $class) ? '' : 'alternate';
		reset( $status_list );
		$bgcolour = "";
		while( list( $status, $col ) = each( $status_list ) ) {
			if( get_blog_status( $blog[ 'blog_id' ], $status ) == 1 ) {
				$bgcolour = "style='background: $col'";
			}
		}
		print "<tr $bgcolour class='$class'>";

foreach($posts_columns as $column_name=>$column_display_name) {

	switch($column_name) {
	
	case 'id':
		?>
		<th scope="row"><input type='checkbox' id='<?php echo $blog[ 'blog_id' ] ?>' name='allblogs[]' value='<?php echo $blog[ 'blog_id' ] ?>'> <label for='<?php echo $blog[ 'blog_id' ] ?>'><?php echo $blog[ 'blog_id' ] ?></label></th>
		<?php
		break;

	case 'blogname':
		?>
		<td valign='top'><label for='<?php echo $blog[ 'blog_id' ] ?>'><?php echo str_replace( '.' . $current_site->domain, '', $blog[ 'domain' ] ) ?></label>
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
		<td valign='top'><?php $blogusers = get_users_of_blog( $blog[ 'blog_id' ] ); if( is_array( $blogusers ) ) while( list( $key, $val ) = each( $blogusers ) ) { print '<a href="user-edit.php?user_id=' . $val->user_id . '">' . $val->user_login . '</a> ('.$val->user_email.')<BR>'; }  ?></td>
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

	case 'control_spam':
		if( get_blog_status( $blog[ 'blog_id' ], "spam" ) == '1' ) {
			?>
				<td valign='top'><?php echo "<a href='wpmu-edit.php?action=unspamblog&amp;id=".$blog[ 'blog_id' ]."' class='edit' onclick=\"return confirm('" . sprintf(__("You are about to mark this blog as not spam?\\n  \'OK\' to activate, \'Cancel\' to stop.") ) . "')\">" . __('Not Spam') . "</a>"; ?></td>
			<?php
		} else {
			?>
				<td valign='top'><?php echo "<a href='wpmu-edit.php?action=spamblog&amp;id=".$blog[ 'blog_id' ]."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to mark this blog as spam?\\n  \'OK\' to continue, \'Cancel\' to stop.") ) . "')\">" . __('Spam') . "</a>"; ?></td>
			<?php
		}
		break;

	case 'control_deactivate':
		if( is_archived( $blog[ 'blog_id' ] ) == '1' ) {
			?>
				<td valign='top'><?php echo "<a href='wpmu-edit.php?action=activateblog&amp;id=".$blog[ 'blog_id' ]."' class='edit' onclick=\"return confirm('" . sprintf(__("You are about to activate this blog?\\n  \'OK\' to activate, \'Cancel\' to stop.") ) . "')\">" . __('Activate') . "</a>"; ?></td>
			<?php
		} else {
			?>
				<td valign='top'><?php echo "<a href='wpmu-edit.php?action=deactivateblog&amp;id=".$blog[ 'blog_id' ]."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to deactivate this blog?\\n  \'OK\' to deactivate, \'Cancel\' to stop.") ) . "')\">" . __('Deactivate') . "</a>"; ?></td>
			<?php
		}
		break;

	case 'control_delete':
		?>
		<td valign='top'><?php echo "<a href='wpmu-edit.php?action=deleteblog&amp;id=".$blog[ 'blog_id' ]."&amp;redirect=".wpmu_admin_redirect_url()."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to delete this blog?\\n  \'OK\' to delete, \'Cancel\' to stop.") ) . "')\">" . __('Delete') . "</a>"; ?></td>
		<?php
		break;

	case 'plugins':
		?>
		<td valign='top'><?php do_action( "wpmublogsaction", $blog[ 'blog_id' ] ); ?></td>
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
} else {
?>
  <tr style='background-color: <?php echo $bgcolor; ?>'> 
    <td colspan="8"><?php _e('No blogs found.') ?></td> 
  </tr> 
<?php
} // end if ($blogs)
?>
</table>
<input type=button value="Check All" onClick="this.value=check_all_rows()"> 
<p>Selected Blogs:<ul>
<li><input type='radio' name='blogfunction' id='delete' value='delete'> <label for='delete'>Delete</label></li>
<li><input type='radio' name='blogfunction' id='spam' value='spam'> <label for='spam'>Mark as Spam</label></li>
</ul>
<input type='hidden' name='redirect' value='<?php echo $_SERVER[ 'REQUEST_URI' ] ?>'>
<input type='submit' value='Apply Changes'></p>
</form>
<?php

break;
} // end switch( $action )
?> 

</div>
<?php include('admin-footer.php'); ?>
