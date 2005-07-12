<?php
require_once('admin.php');

$title = __('Miscellaneous Options');
$parent_file = 'options-general.php';

include('admin-header.php');

?>
 
<div class="wrap"> 
<h2><?php _e('Miscellaneous Options') ?></h2> 
<form name="miscoptions" method="post" action="options.php"> 
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="'hack_file','use_fileupload','fileupload_minlevel','use_geo_positions','use_linksupdate'" /> 
	<fieldset class="options">
	<legend>
	<input name="use_fileupload" type="checkbox" id="use_fileupload" value="1" <?php checked('1', get_settings('use_fileupload')); ?> />
	<label for="use_fileupload"><?php _e('Allow File Uploads') ?></label></legend>
	<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
	<tr>
	<th scope="row"><?php _e('Minimum level to upload:') ?></th>
	<td><select name="fileupload_minlevel" id="fileupload_minlevel">
	<?php
	for ($i = 1; $i < 11; $i++) {
	if ($i == get_settings('fileupload_minlevel')) $selected = " selected='selected'";
	else $selected = '';
	echo "\n\t<option value='$i' $selected>$i</option>";
	}
	?>
	</select></td>
	</tr>
	</table> 
	</fieldset>
	<p><input name="use_linksupdate" type="checkbox" id="use_linksupdate" value="1" <?php checked('1', get_settings('use_linksupdate')); ?> />
	<label for="use_linksupdate"><?php _e('Track Links&#8217; Update Times') ?></label></p>
	<p>
	<label><input type="checkbox" name="hack_file" value="1" <?php checked('1', get_settings('hack_file')); ?> /> <?php _e('Use legacy <code>my-hacks.php</code> file support') ?></label>
	</p>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
	</p>
</form> 
</div>

<?php include('./admin-footer.php'); ?>
