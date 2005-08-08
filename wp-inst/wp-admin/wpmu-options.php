<?php
require_once('admin.php');
$title = __('Site Options');
$parent_file = 'wpmu-admin.php';

include('admin-header.php');

if( $wpblog != 'main' || $user_level < 10) {
    die( __('<p>You do not have permission to access this page.</p>') );
}

if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}

?>
<div class="wrap"> 
	<h2><?php _e('Site Options') ?></h2> 
	<form name="form1" method="post" action="wpmu-edit.php?action=siteoptions"> 
	<fieldset class="options">
		<legend><?php _e('Site Wide Settings <em>(These settings may be overridden by blog owners)</em>') ?></legend> 
		<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<?php
		$lang_files = glob( ABSPATH . WPINC . "/languages/*" );
		$lang = get_site_option( "WPLANG" );
		if( is_array( $lang_files ) ) {
			?>
			<tr valign="top"> 
			<th width="33%" scope="row"><?php _e('Default Language:') ?></th> 
			<td><select name="WPLANG" id="WPLANG">
			<?php
			echo "<option value=''>Default</option>";
			while( list( $key, $val ) = each( $lang_files ) ) { 
				$l = basename( $val, ".mo" );
				echo "<option value='$l'";
				echo $lang == $l ? " selected" : "";
				echo "> $l</option>";
			}
			?>
				</select></td>
				</tr> 
				<?php
		} // languages
		?>
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Operational Settings <em>(These settings cannot be modified by blog owners)') ?></legend> 
		<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Banned Names:') ?></th> 
		<td><input name="illegal_names" type="text" id="illegal_names" style="width: 95%" value="<?php echo implode( " ", get_site_option('illegal_names') ); ?>" size="45" />
		<br />
		<?php _e('Users are not allowed to register these blogs. Separate names by spaces.') ?></td> 
		</tr> 
		<tr valign="top"> 
		<th scope="row"><?php _e('Limited Email Registrations:') ?></th> 
		<td><input name="limited_email_domains" type="text" id="limited_email_domains" style="width: 95%" value="<?php echo implode( " ", get_site_option('limited_email_domains') ); ?>" size="45" />
		<br />
		<?php _e('If you want to limit blog registrations to certain domains. Separate domains by spaces.') ?></td> 
		</tr> 
		</table>
	</fieldset>
	<p class="submit"> 
	<input type="submit" name="Submit" value="<?php _e('Update Options') ?>" /> 
	</p> 
	</form> 
</div>
<?php include('./admin-footer.php'); ?>
