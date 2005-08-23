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
$themes = get_themes();
$allowed_themes = get_site_option( "allowed_themes" );
if( $allowed_themes == false ) {
	$allowed_themes = array_keys( $themes );
}

print "<br />";
print "<form action='wpmu-edit.php?action=updatethemes' method='POST'>";
print "<h3>Site Themes</h3>";
print '<table border="0" cellspacing="2" cellpadding="5" class="editform">';
print "<caption>Disable themes site-wide. You can enable themes on a blog by blog basis.</caption>";
print '<tr><th>Theme</th><th>Description</th><th>Disabled</th></tr>';
while( list( $key, $val ) = each( $themes ) ) { 
	$enabled = '';
	$disabled = '';
	if( isset( $allowed_themes[ $key ] ) == true ) {
		$enabled = 'checked ';
	} else {
		$disabled = 'checked ';
	}
	?>
		<tr valign="top"> 
		<th scope="row"><?php echo $key ?></th> 
		<td><?php echo $val[ 'Description' ] ?></td>
		<td>
		<input name="theme[<?php echo $key ?>]" type="radio" id="<?php echo $key ?>" value="disabled" <?php echo $disabled ?>/> Yes
		&nbsp;&nbsp;&nbsp; 
	<input name="theme[<?php echo $key ?>]" type="radio" id="<?php echo $key ?>" value="enabled" <?php echo $enabled ?>/> No
		</td> 
		</tr> 
		<?php
}
?>
</table>
<input type='submit' value='Update Themes'>
</form>

</div>
<?php include('admin-footer.php'); ?>
