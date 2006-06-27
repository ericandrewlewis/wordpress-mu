<?php
require_once('admin.php');

switch( $_GET[ 'action' ] ) {
	case "delete":
		$id = intval( $_GET[ 'id' ] );
		if( $id != '0' && $id != '1' )
			wpmu_delete_user($id);
		wpmu_admin_do_redirect( "wpmu-users.php" );
		die();
	break;
	case "allusers":
		if( is_site_admin() == false ) {
			die( __('<p>You do not have permission to access this page.</p>') );
		}
	if( is_array( $_POST[ 'allusers' ] ) ) {
		while( list( $key, $val ) = each( $_POST[ 'allusers' ] ) ) {
			if( $val != '' && $val != '0' && $val != '1' ) {
				$user_details = get_userdata( $val );
				if( $_POST[ 'userfunction' ] == 'delete' ) {
					wpmu_delete_user($val);
				} elseif( $_POST[ 'userfunction' ] == 'spam' ) {
					$blogs = get_blogs_of_user( $val );
					if( is_array( $blogs ) ) {
						while( list( $key, $details ) = each( $blogs ) ) { 
							update_blog_status( $details->userblog_id, "spam", '1' );
							do_action( "make_spam_blog", $details->userblog_id );
						}
					}
				}
			}
		}
	}
	wpmu_admin_do_redirect( "wpmu-users.php" );
	break;
}

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
		if( isset( $_GET[ 'start' ] ) == false ) {
			$start = 0;
		} else {
			$start = intval( $_GET[ 'start' ] );
		}
		if( isset( $_GET[ 'num' ] ) == false ) {
			$num = 30;
		} else {
			$num = intval( $_GET[ 'num' ] );
		}

		$query = "SELECT * 
			FROM ".$wpdb->users;
		if( $_GET[ 's' ] != '' ) {
			$search = '%' . addslashes( $_GET['s'] ) . '%';
			$query .= " WHERE user_login LIKE '$search' OR user_email LIKE '$search'";
		}
		if( isset( $_GET[ 'sortby' ] ) == false ) {
			$_GET[ 'sortby' ] = 'ID';
		}
		if( $_GET[ 'sortby' ] == 'Email' ) {
			$query .= ' ORDER BY user_email ';
		} elseif( $_GET[ 'sortby' ] == 'ID' ) {
			$query .= ' ORDER BY ID ';
		} elseif( $_GET[ 'sortby' ] == 'Login' ) {
			$query .= ' ORDER BY user_login ';
		} elseif( $_GET[ 'sortby' ] == 'Name' ) {
			$query .= ' ORDER BY display_name ';
		} elseif( $_GET[ 'sortby' ] == 'Registered' ) {
			$query .= ' ORDER BY registered ';
		}
		if( $_GET[ 'order' ] == 'DESC' ) {
			$query .= "DESC";
		} else {
			$query .= "ASC";
		}
		$query .= " LIMIT " . intval( $start ) . ", " . intval( $num );
		$user_list = $wpdb->get_results( $query, ARRAY_A );
		if( count( $user_list ) < $num ) {
			$next = false;
		} else {
			$next = true;
		}
?>
<h2>Users</h2>
<form name="searchform" action="wpmu-users.php" method="get" style="float: left; width: 16em; margin-right: 3em;"> 
  <table><td>
  <fieldset> 
  <legend><?php _e('Search Users&hellip;') ?></legend> 
  <input type='hidden' name='action' value='users'>
  <input type="text" name="s" value="<?php if (isset($_GET[ 's' ])) echo wp_specialchars($_GET[ 's' ], 1); ?>" size="17" /> 
  <input type="submit" name="submit" value="<?php _e('Search') ?>"  /> 
  </fieldset>
  <?php
  if( isset($_GET[ 's' ]) && $_GET[ 's' ] != '' ) {
	  ?><a href="/wp-admin/wpmu-blogs.php?action=blogs&s=<?php echo wp_specialchars($_GET[ 's' ], 1) ?>">Search Blogs: <?php echo wp_specialchars($_GET[ 's' ], 1) ?></a><?php
  }
  ?>
  </td><td>
  <fieldset> 
  <legend><?php _e('User Navigation') ?></legend> 
  <?php 

  $url2 = "order=" . $_GET[ 'order' ] . "&sortby=" . $_GET[ 'sortby' ] . "&s=" .$_GET[ 's' ];

  if( $start == 0 ) { 
	  echo 'Previous&nbsp;Users';
  } elseif( $start <= 30 ) { 
	  echo '<a href="wpmu-users.php?start=0' . $url2 . '">Previous&nbsp;Users</a>';
  } else {
	  echo '<a href="wpmu-users.php?start=' . ( $start - $num ) . '&' . $url2 . '">Previous&nbsp;Users</a>';
  } 
  if ( $next ) {
	  echo '&nbsp;||&nbsp;<a href="wpmu-users.php?start=' . ( $start + $num ) . '&' . $url2 . '">Next&nbsp;Users</a>';
  } else {
	  echo '&nbsp;||&nbsp;Next&nbsp;Users';
  }
  ?>
  </fieldset>
  </td></table>
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
<script language="javascript">
<!--
var checkflag = "false";
function check_all_rows() {
	field = document.formlist;
	if (checkflag == "false") {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allusers[]' )
				field[i].checked = true;}
		checkflag = "true";
		return "Uncheck All"; 
	} else {
		for (i = 0; i < field.length; i++) {
			if( field[i].name == 'allusers[]' )
				field[i].checked = false; }
		checkflag = "false";
		return "Check All"; 
	}
}
//  -->
</script>

<form name='formlist' action='wpmu-users.php?action=allusers' method='POST'>
<input type=button value="Check All" onClick="this.value=check_all_rows()"> 
<table width="100%" cellpadding="3" cellspacing="3"> 
	<tr>

<?php foreach($posts_columns as $column_display_name) { ?>
	<th scope="col"><?php if( $column_display_name == 'Blogs' ) { echo "Blogs"; } else { ?><a href="wpmu-users.php?sortby=<?php echo urlencode( $column_display_name ) ?>&<?php if( $_GET[ 'sortby' ] == $column_display_name ) { if( $_GET[ 'order' ] == 'DESC' ) { echo "order=ASC&" ; } else { echo "order=DESC&"; } } ?>start=<?php echo $start ?>"><?php echo $column_display_name; ?></a></th><?php } ?>
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
		<th scope="row"><input type='checkbox' id='<?php echo $user[ 'ID' ] ?>' name='allusers[]' value='<?php echo $user[ 'ID' ] ?>'> <label for='<?php echo $user[ 'ID' ] ?>'><?php echo $user[ 'ID' ] ?></label></th>
		<?php
		break;

	case 'user_login':
		?>
		<td><label for='<?php echo $user[ 'ID' ] ?>'><?php echo $user[ 'user_login' ] ?></label>
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
		$blogs = get_blogs_of_user( $user[ 'ID' ] );
		?>
		<td><?php if( is_array( $blogs ) ) 
				while( list( $key, $val ) = each( $blogs ) ) { 
					print '<a href="wpmu-blogs.php?action=editblog&id=' . $val->userblog_id . '">' . str_replace( '.' . $current_site->domain, '', $val->domain ) . '</a> (<a '; 
					if( get_blog_status( $val->userblog_id, 'spam' ) == 1 )
						print 'style="background-color: #f66" ';
					print 'target="_new" href="http://'.$val->domain . $val->path.'">View</a>)<BR>'; 
				} ?></td>
		<?php
		break;

	case 'control_edit':
		?>
		<td><?php echo "<a href='user-edit.php?user_id=".$user[ 'ID' ]."' class='edit'>" . __('Edit') . "</a>"; ?></td>
		<?php
		break;

	case 'control_delete':
		?>
		<td><?php echo "<a href='wpmu-users.php?action=delete&amp;id=".$user[ 'ID' ]."&amp;redirect=".wpmu_admin_redirect_url()."' class='delete' onclick=\"return confirm('" . sprintf(__("You are about to delete this user?\\n  \'OK\' to delete, \'Cancel\' to stop.") ) . "')\">" . __('Delete') . "</a>"; ?></td>
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
<p>Selected Users:<ul>
<li><input type='radio' name='userfunction' id='delete' value='delete'> <label for='delete'>Delete</label></li>
<li><input type='radio' name='userfunction' id='spam' value='spam'> <label for='spam'>Mark as Spammers</label></li>
</ul>
<input type='submit' value='Apply Changes'></p>
</form>

<?php
}

?>
</div>
<?php include('admin-footer.php'); ?>