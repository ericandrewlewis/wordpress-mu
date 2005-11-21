<?php
require_once('admin.php');

$title = __('Writing Options');
$parent_file = 'options-general.php';

include('admin-header.php');
?>

<div class="wrap"> 
<h2><?php _e('Writing Options') ?></h2> 
<form method="post" action="options.php"> 
<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
<tr valign="top"> 
<th width="33%" scope="row"> <?php _e('Size of the post box:') ?></th> 
<td><input name="default_post_edit_rows" type="text" id="default_post_edit_rows" value="<?php form_option('default_post_edit_rows'); ?>" size="2" style="width: 1.5em; " /> 
<?php _e('lines') ?></td> 
</tr> 
<tr valign="top">
<th scope="row"><?php _e('Formatting:') ?></th>
<td>
<label for="use_smilies">
<input name="use_smilies" type="checkbox" id="use_smilies" value="1" <?php checked('1', get_settings('use_smilies')); ?> />
<?php _e('Convert emoticons like <code>:-)</code> and <code>:-P</code> to graphics on display') ?></label>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Default post category:') ?></th>
<td><select name="default_category" id="default_category">
<?php
$categories = $wpdb->get_results("SELECT * FROM $wpdb->categories ORDER BY cat_name");
foreach ($categories as $category) :
if ($category->cat_ID == get_settings('default_category')) $selected = " selected='selected'";
else $selected = '';
echo "\n\t<option value='$category->cat_ID' $selected>$category->cat_name</option>";
endforeach;
?>
</select></td>
</tr>
</table>



<p class="submit">
<input type="hidden" name="action" value="update" /> 
<input type="hidden" name="page_options" value="default_post_edit_rows,use_smilies,ping_sites,mailserver_url,mailserver_port,mailserver_login,mailserver_pass,default_category,default_email_category" /> 
<input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" /> 
</p>
</form> 
</div> 

<?php include('./admin-footer.php') ?>
