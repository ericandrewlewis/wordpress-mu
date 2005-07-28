<?php
require_once('admin.php');

$title = __('Reading Options');
$parent_file = 'options-general.php';

include('admin-header.php');
?>

<div class="wrap"> 
<h2><?php _e('Reading Options') ?></h2> 
<form name="form1" method="post" action="options.php"> 
	<input type="hidden" name="action" value="update" /> 
	<input type="hidden" name="page_options" value="'posts_per_page','what_to_show' " /> 
	<fieldset class="options"> 
	<legend><?php _e('Blog Pages') ?></legend> 
	<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr valign="top"> 
		<th width="33%" scope="row"><?php _e('Show at most:') ?></th> 
		<td>
		<input name="posts_per_page" type="text" id="posts_per_page" value="<?php form_option('posts_per_page'); ?>" size="3" /> 
		<select name="what_to_show" id="what_to_show" > 
			<option value="days" <?php selected('days', get_settings('what_to_show')); ?>><?php _e('days') ?></option> 
			<option value="posts" <?php selected('posts', get_settings('what_to_show')); ?>><?php _e('posts') ?></option> 
		</select>
		</td> 
		</tr> 
	</table> 
	</fieldset> 

	<p class="submit"> 
		<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" /> 
	</p> 
</form> 
</div> 
<?php include('./admin-footer.php'); ?>
