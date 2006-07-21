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

    print "<h2>" . __('Edit Blog') . "</h2>";
    print "<a href='http://{$details[ 'domain' ]}/'>{$details[ 'domain' ]}</a>";
    ?>
    <form name="form1" method="post" action="wpmu-edit.php?action=updateblog"> 
    <input type="hidden" name="id" value="<?php echo $_GET[ 'id' ] ?>" /> 
    <table><td valign='top'>
    <div class="wrap">
    <table width="100%" border='0' cellspacing="2" cellpadding="5" class="editform"> 
	<tr valign="top"> 
	<th scope="row"><?php _e('URL') ?></th> 
	<td>http://<input name="blog[domain]" type="text" id="domain" value="<?php echo $details[ 'domain' ] ?>" size="33" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e('Path') ?></th> 
	<td><input name="blog[path]" type="text" id="path" value="<?php echo $details[ 'path' ] ?>" size="40" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e('Registered') ?></th> 
	<td><input name="blog[registered]" type="text" id="blog_registered" value="<?php echo $details[ 'registered' ] ?>" size="40" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e('Last Updated') ?></th> 
	<td><input name="blog[last_updated]" type="text" id="blog_last_updated" value="<?php echo $details[ 'last_updated' ] ?>" size="40" /></td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e('Public') ?></th> 
	<td><input type='radio' name='blog[public]' value='1' <?php if( $details[ 'public' ] == '1' ) echo " checked"?>> <?php _e('Yes') ?>&nbsp;&nbsp;
	    <input type='radio' name='blog[public]' value='0' <?php if( $details[ 'public' ] == '0' ) echo " checked"?>> <?php _e('No') ?> &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e( 'Archived' ); ?></th> 
	<td><input type='radio' name='blog[archived]' value='1' <?php if( $details[ 'archived' ] == '1' ) echo " checked"?>> <?php _e('Yes') ?>&nbsp;&nbsp;
	    <input type='radio' name='blog[archived]' value='0' <?php if( $details[ 'archived' ] == '0' ) echo " checked"?>> <?php _e('No') ?> &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e( 'Mature' ); ?></th> 
	<td><input type='radio' name='blog[mature]' value='1' <?php if( $details[ 'mature' ] == '1' ) echo " checked"?>> <?php _e('Yes') ?>&nbsp;&nbsp;
	    <input type='radio' name='blog[mature]' value='0' <?php if( $details[ 'mature' ] == '0' ) echo " checked"?>> <?php _e('No') ?> &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e( 'Spam' ); ?></th> 
	<td><input type='radio' name='blog[spam]' value='1' <?php if( $details[ 'spam' ] == '1' ) echo " checked"?>> <?php _e('Yes') ?>&nbsp;&nbsp;
	    <input type='radio' name='blog[spam]' value='0' <?php if( $details[ 'spam' ] == '0' ) echo " checked"?>> <?php _e('No') ?> &nbsp;&nbsp;
	    </td> 
	</tr> 
	<tr valign="top"> 
	<th scope="row"><?php _e( 'Deleted' ); ?></th> 
	<td><input type='radio' name='blog[deleted]' value='1' <?php if( $details[ 'deleted' ] == '1' ) echo " checked"?>> <?php _e('Yes') ?>&nbsp;&nbsp;
	    <input type='radio' name='blog[deleted]' value='0' <?php if( $details[ 'deleted' ] == '0' ) echo " checked"?>> <?php _e('No') ?> &nbsp;&nbsp;
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
	print "<div class='wrap'><h3>" . __('Blog Themes') . "</h3>";
	print '<table width="100%" border="0" cellspacing="2" cellpadding="5" class="editform">';
	print '<tr><th>' . __('Theme') . '</th><th>' . __('Enable') . '</th></tr>';
	print $out;
	print "</table></div>";
    }
    $blogusers = get_users_of_blog( $_GET[ 'id' ] );
    print '<div class="wrap"><h3>' . __('Blog Users') . '</h3>';
    if( is_array( $blogusers ) ) {
	    print '<table width="100%"><caption>' . __('Current Users') . '</caption>';
	    print "<tr><th>" . __('User') . "</th><th>" . __('Role') . "</th><th>" . __('Remove') . "</th><th></th></tr>";
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
			    print '<td><input title="' . __('Click to remove user') . '" type="checkbox" name="blogusers[' . $val->user_id . ']"></td>';
		    } else {
			    print "<td><b>" . __ ('N/A') . "</b></td><td><b>" . __('N/A') . "</b></td>";
		    }
		    print '<td><a href="user-edit.php?user_id=' . $val->user_id . '">' . __('Edit') . "</td></tr>";
	    }
	    print "</table>";
    }
    print "<h3>" . __('Add a new user') . "</h3>";
    ?>
<?php autocomplete_css(); ?>
<p><?php _e('As you type WordPress will offer you a choice of usernames.<br /> Click them to select and hit <em>Update Options</em> to add the user.') ?></p>
<table>
<tr><th scope="row"><?php _e('User&nbsp;Login:') ?> </th><td><input type="text" name="newuser" id="newuser"></td></tr>
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
<div class='wrap'><strong><?php _e('Misc Blog Actions') ?></strong>
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
		return "<?php _e('Uncheck All') ?>"; 
	} else {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allblogs[]' )
				field[i].checked = false; }
		checkflag = "false";
		return "<?php _e('Check All') ?>"; 
	}
}

function confirm_action( msg ) {
	return confirm( msg );
}
//  -->
</script>

<h2><?php _e ('Blogs') ?></h2>
<form name="searchform" action="wpmu-blogs.php" method="get" style="float: left; width: 16em; margin-right: 3em;"> 
  <table><td>
  <fieldset> 
  <legend><?php _e('Search Blogs&hellip;') ?></legend> 
  <input type='hidden' name='action' value='blogs'>
  <?php _e('Name:') ?>&nbsp;<input type="text" name="s" value="<?php if (isset($_GET[ 's' ])) echo wp_specialchars($_GET[ 's' ], 1); ?>" size="17" /><br />
  <?php _e('Blog&nbsp;ID:') ?>&nbsp;<input type="text" name="blog_id" value="<?php if (isset($_GET[ 'blog_id' ])) echo wp_specialchars($_GET[ 'blog_id' ], 1); ?>" size="10" /><br />
  <?php _e('IP Address:') ?> <input type="text" name="ip_address" value="<?php if (isset($_GET[ 'ip_address' ])) echo wp_specialchars($_GET[ 'ip_address' ], 1); ?>" size="10" /><br />
  <input type="submit" name="submit" value="<?php _e('Search') ?>"  /> 
  </fieldset>
  <?php
  if( isset($_GET[ 's' ]) && $_GET[ 's' ] != '' ) {
	  ?><a href="/wp-admin/wpmu-users.php?action=users&s=<?php echo wp_specialchars($_GET[ 's' ], 1) ?>"><?php _e('Search Users:') ?> <?php echo wp_specialchars($_GET[ 's' ], 1) ?></a><?php
  }
  ?>
  </td><td>
  <fieldset> 
  <legend><?php _e('Blog Navigation') ?></legend> 
  <?php 

  $url2 = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ] . "&s=" . $_GET[ 's' ] . "&ip_address=" . $_GET[ 'ip_address' ];

  if( $start == 0 ) { 
	  _e('Previous&nbsp;Blogs');
  } elseif( $start <= 30 ) { 
	  echo '<a href="wpmu-blogs.php?start=0&' . $url2 . ' ">' . __('Previous&nbsp;Blogs') . '</a>';
  } else {
	  echo '<a href="wpmu-blogs.php?start=' . ( $start - $num ) . '&' . $url2 . '">' . __('Previous&nbsp;Blogs') . '</a>';
  } 
  if ( $next ) {
	  echo '&nbsp;||&nbsp;<a href="wpmu-blogs.php?start=' . ( $start + $num ) . '&' . $url2 . '">' . __('Next&nbsp;Blogs') . '</a>';
  } else {
	  echo '&nbsp;||&nbsp;' . __('Next&nbsp;Blogs');
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
<input type=button value="<?php _e('Check All') ?>" onClick="this.value=check_all_rows()"> 
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
		<td valign='top'><label for='<?php echo $blog[ 'blog_id' ] ?>'><?php if( constant( "VHOST" ) == 'yes' ) { echo str_replace( '.' . $current_site->domain, '', $blog[ 'domain' ] ); } else { echo $blog[ 'path' ]; } ?></label>
		</td>
		<?php
		break;

	case 'last_updated':
		?>
		<td valign='top'><?php echo $blog[ 'last_updated' ] == '0000-00-00 00:00:00' ? __("Never") : $blog[ 'last_updated' ] ?></td>
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
		<td valign='top'><a href="http://<?php echo $blog[ 'domain' ]. $blog[ 'path' ]; ?>" rel="permalink" class="edit"><?php _e('View'); ?></a></td>
		<?php
		break;

	case 'control_edit':
		?>
		<td valign='top'><?php echo "<a href='wpmu-blogs.php?action=editblog&amp;id=".$blog[ 'blog_id' ]."' class='edit'>" . __('Edit') . "</a>"; ?></td>
		<?php
		break;

	case 'control_backend':
		?>
		<td valign='top'><?php echo "<a href='http://" . $blog[ 'domain' ] . $blog[ 'path' ] . "wp-admin/' class='edit'>" . __('Backend') . "</a>"; ?></td>
		<?php
		break;

	case 'control_spam':
		if( get_blog_status( $blog[ 'blog_id' ], "spam" ) == '1' ) {
			?>
			<td valign='top'><form action='wpmu-edit.php?action=unspamblog' method='POST' onSubmit='return confirm_action( "<?php _e("You are about to mark this blog as not spam.") ?>" )'>
			<?php wp_nonce_field( "unspamblog" ); ?>
			<input type='hidden' name='id' value='<?php echo $blog[ 'blog_id' ] ?>'><input type='submit' value='<?php _e('Not Spam') ?>'></form></td>
			<?php
		} else {
			?>
			<td valign='top'><form action='wpmu-edit.php?action=spamblog' method='POST' onSubmit='return confirm_action( "<?php _e("You are about to mark this blog as spam.") ?>" )'>
			<?php wp_nonce_field( "spamblog" ); ?>
			<input type='hidden' name='id' value='<?php echo $blog[ 'blog_id' ] ?>'><input type='submit' value='<?php _e('Spam') ?>'></form></td>
			<?php
		}
		break;

	case 'control_deactivate':
		if( is_archived( $blog[ 'blog_id' ] ) == '1' ) {
			?>
			<td valign='top'><form action='wpmu-edit.php?action=activateblog' method='POST' onSubmit='return confirm_action( "<?php _e("You are about to activate this blog.") ?>" )'>
			<?php wp_nonce_field( "activateblog" ); ?>
			<input type='hidden' name='id' value='<?php echo $blog[ 'blog_id' ] ?>'><input type='submit' value='<?php _e('Activate') ?>'></form></td>
			<?php
		} else {
			?>
			<td valign='top'><form action='wpmu-edit.php?action=deactivateblog' method='POST' onSubmit='return confirm_action( "<?php _e("You are about to deactivate this blog.") ?>" )'>
			<?php wp_nonce_field( "deactivateblog" ); ?>
			<input type='hidden' name='id' value='<?php echo $blog[ 'blog_id' ] ?>'><input type='submit' value='<?php _e('Deactivate') ?>'></form></td>
			<?php
		}
		break;

	case 'control_delete':
		?>
		<td valign='top'><form action='wpmu-edit.php?action=deleteblog' method='POST' onSubmit='return confirm_action( "<?php _e("You are about to completely delete this blog, its database tables and uploaded files.") ?>" )'>
		<?php wp_nonce_field( "deleteblog" ); ?>
		<input type='hidden' name='id' value='<?php echo $blog[ 'blog_id' ] ?>'><input type='submit' value='<?php _e('Delete') ?>'></form></td>
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
<input type=button value="<?php _e('Check All') ?>" onClick="this.value=check_all_rows()"> 
<p><?php _e('Selected Blogs:') ?><ul>
<li><input type='radio' name='blogfunction' id='delete' value='delete'> <label for='delete'><?php _e('Delete') ?></label></li>
<li><input type='radio' name='blogfunction' id='spam' value='spam'> <label for='spam'><?php _e('Mark as Spam') ?></label></li>
<?php wp_nonce_field( "allblogs" ); ?>
</ul>
<input type='hidden' name='redirect' value='<?php echo $_SERVER[ 'REQUEST_URI' ] ?>'>
<input type='submit' value='<?php _e('Apply Changes') ?>'></p>
</form>

</div>
<div class="wrap">
<h2><?php _e('Add Blog') ?></h2>
<form name="addform" method="post" action="wpmu-edit.php?action=addblog">
<?php wp_nonce_field('add-blog') ?>
<table>
<tr><th scope='row'><?php _e('Blog Address') ?></th><td><?php
if( constant( "VHOST" ) == 'yes' ) {
	?><input name="blog[domain]" type="text" title="<?php _e('Domain') ?>"/>.<?php echo $current_site->domain;?></td></tr><?php
} else {
	echo $current_site->domain . $current_site->path ?><input name="blog[domain]" type="text" title="<?php _e('Domain') ?>"/></td></tr><?php
} ?>
<tr><th scope='row'><?php _e('Blog Title') ?></th><td><input name="blog[title]" type="text" title="<?php _e('Title') ?>"/></td></tr>
<tr><th scope='row'><?php _e('Admin Email') ?></th><td><input name="blog[email]" type="text" title="<?php _e('Email') ?>"/></td></tr>
<tr><td colspan='2'><?php _e('A new user will be created if the above email address is not in the database.') ?></td></tr>
</table>
<input type="submit" name="go" value="<?php _e('Add Blog') ?>" />
</form>
</div>
<?php

break;
} // end switch( $action )
?> 

</div>
<?php include('admin-footer.php'); ?>
