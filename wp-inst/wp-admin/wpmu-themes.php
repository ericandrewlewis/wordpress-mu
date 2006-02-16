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
?>

<form action='wpmu-edit.php?action=updatethemes' method='POST'>
<h3>Site Themes</h3>
<table border="0" cellspacing="5" cellpadding="5">
<caption>Disable themes site-wide. You can enable themes on a blog by blog basis.</caption>
<tr><th width="100">Disabled</th><th>Theme</th><th>Description</th></tr>
<?php
while( list( $key, $val ) = each( $themes ) ) {
	$i++;
	$enabled = '';
	$disabled = '';
	if( isset( $allowed_themes[ $key ] ) == true ) {
		$enabled = 'checked ';
	} else {
		$disabled = 'checked ';
	}
?>

<tr valign="top" style="<?php if ($i%2) echo 'background: #eee'; ?>">
<td>
<label><input name="theme[<?php echo $key ?>]" type="radio" id="<?php echo $key ?>" value="disabled" <?php echo $disabled ?>/> Yes</label>
&nbsp;&nbsp;&nbsp; 
<label><input name="theme[<?php echo $key ?>]" type="radio" id="<?php echo $key ?>" value="enabled" <?php echo $enabled ?>/> No</label>
</td>
<th scope="row" align="left"><?php echo $key ?></th> 
<td><?php echo $val[ 'Description' ] ?></td>
</tr> 
<?php
}
?>
</table>
<p class="submit">
<input type='submit' value='Update Themes &raquo;' />
</p>
</form>

</div>
<?php include('admin-footer.php'); ?>
