<?php
require_once('admin.php');

$title = __('Options');
$this_file = 'options.php';
$parent_file = 'options-general.php';

wp_reset_vars(array('action'));

if ( !current_user_can('manage_options') )
	wp_die(__('Cheatin&#8217; uh?'));

if( $_GET[ 'adminhash' ] ) {
	$new_admin_details = get_option( 'new_admin_email' );
	if( is_array( $new_admin_details ) && $new_admin_details[ 'hash' ] == $_GET[ 'adminhash' ] && $new_admin_details[ 'newemail' ] != '' ) {
		update_option( "admin_email", $new_admin_details[ 'newemail' ] );
		delete_option( "new_admin_email" );
	}
	wp_redirect( get_option( "siteurl" ) . "/wp-admin/options-general.php?updated=true" );
	exit;
}

switch($action) {

case 'update':
	$any_changed = 0;

	check_admin_referer('update-options');

	if (!$_POST['page_options']) {
		foreach ($_POST as $key => $value) {
			$options[] = $key;
		}
	} else {
		$options = explode(',', stripslashes($_POST['page_options']));
	}

	// Save for later.
	$old_siteurl = get_settings('siteurl');
	$old_home = get_settings('home');

	// HACK
	// Options that if not there have 0 value but need to be something like "closed"
	$nonbools = array('default_ping_status', 'default_comment_status');
	if ($options) {
		foreach ($options as $option) {
			$option = trim($option);
			$value = trim(stripslashes($_POST[$option]));
				if( in_array($option, $nonbools) && ( $value == '0' || $value == '') )
				$value = 'closed';

			if( $option == 'blogdescription' || $option == 'blogname' )
				$value = wp_filter_post_kses( $value );
			
			if( $option == 'posts_per_page' && $value == '' )
				$value = 10;

			if( $option == 'new_admin_email' && $value != get_option( 'admin_email' ) && is_email( $val ) ) {
				$hash = md5( $value.time().mt_rand() );
				$newadminemail = array( 
						"hash" => $hash,
						"newemail" => $value
						);
				update_option( "new_admin_email", $newadminemail );
				wp_mail( $value, "[ " . get_option( 'blogname' ) . " ] New Admin Email Address", "Dear User,

You recently requested to have the administration email address on 
your blog changed. 
If this is correct, please click on the following link to change it:
" . get_option( "siteurl" ) . "/wp-admin/options.php?adminhash={$hash}

You can safely ignore and delete this email if you do not want to
take this action.

This email has been sent to '{$email}'
" );
			} elseif (update_option($option, $value) ) {
				$any_changed++;
			}

			if ( 'lang_id' == $option ) {
				$value = (int) $value;
				update_blog_status( $wpdb->blogid, 'lang_id', $value );
				$any_changed++;
			}
			if ( 'blog_public' == $option ) {
				$value = (int) $value;
				update_blog_status( $wpdb->blogid, 'public', $value );
				$any_changed++;
			}
		}
	}
    
	if ($any_changed) {
			// If siteurl or home changed, reset cookies.
			if ( get_settings('siteurl') != $old_siteurl || get_settings('home') != $old_home ) {
				// If home changed, write rewrite rules to new location.
				$wp_rewrite->flush_rules();
				// Clear cookies for old paths.
				wp_clearcookie();
				// Set cookies for new paths.
				wp_setcookie($user_login, $user_pass_md5, true, get_settings('home'), get_settings('siteurl'));
			}

			//$message = sprintf(__('%d setting(s) saved... '), $any_changed);
    }
    
	$referred = remove_query_arg('updated' , wp_get_referer());
	$goback = add_query_arg('updated', 'true', wp_get_referer());
	$goback = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $goback);
	wp_redirect($goback);
    break;

default:
if (!is_site_admin())
	die('Not admin');

	include('admin-header.php'); ?>

<div class="wrap">
  <h2><?php _e('All options'); ?></h2>
  <form name="form" action="options.php" method="post">
  <?php wp_nonce_field('update-options') ?>
  <input type="hidden" name="action" value="update" />
  <table width="98%">
<?php
$options = $wpdb->get_results("SELECT * FROM $wpdb->options ORDER BY option_name");

foreach ($options as $option) :
	$value = wp_specialchars($option->option_value, 'single');
	echo "
<tr>
	<th scope='row'><label for='$option->option_name'>$option->option_name</label></th>
	<td><input type='text' name='$option->option_name' id='$option->option_name' size='30' value='" . $value . "' /></td>
	<td>$option->option_description</td>
</tr>";
endforeach;
?>
  </table>
<p class="submit"><input type="submit" name="Update" value="<?php _e('Update Settings &raquo;') ?>" /></p>
  </form>
</div>


<?php
break;
} // end switch

include('admin-footer.php');
?>
