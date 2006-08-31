<?php
require_once('./admin.php');

$title = __('General Options');
$parent_file = 'options-general.php';

include('./admin-header.php');
?>
 
<div class="wrap">
<h2><?php _e('General Options') ?></h2>
<form method="post" action="options.php"> 
<?php wp_nonce_field('update-options') ?>
<table class="optiontable"> 
<tr valign="top"> 
<th scope="row"><?php _e('Weblog title:') ?></th> 
<td><input name="blogname" type="text" id="blogname" value="<?php form_option('blogname'); ?>" size="40" /></td> 
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Tagline:') ?></th> 
<td><input name="blogdescription" type="text" id="blogdescription" style="width: 95%" value="<?php form_option('blogdescription'); ?>" size="45" />
<br />
<?php _e('In a few words, explain what this weblog is about.') ?></td> 
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('Search Engines:') ?> </th> 
<td><label><input type="checkbox" name="blog_public" value="1" <?php checked('1', $current_blog->public); ?> /> <?php _e('I would like my blog to appear in search engines like Google and Technorati, and in public listings around WordPress.com.'); ?></label> (<a href="http://wordpress.com/blog/2006/01/29/a-little-privacy/">more</a>)
</td> 
</tr>
<tr valign="top"> 
<th scope="row"><?php _e('Membership:') ?></th> 
<td> <label for="comment_registration">
<input name="comment_registration" type="checkbox" id="comment_registration" value="1" <?php checked('1', get_option('comment_registration')); ?> /> 
<?php _e('Users must be registered and logged in to comment') ?>
</label>
</td> 
</tr> 
<tr valign="top"> 
<th scope="row"><?php _e('E-mail address:') ?> </th> 
<td><input name="new_admin_email" type="text" id="new_admin_email" value="<?php form_option('admin_email'); ?>" size="40" class="code" />
<br />
<p><?php _e('This address is used only for admin purposes.') ?> If you change this we will send you an email at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong></p>
</td> 
</tr>

<?php
$lang_files = glob( ABSPATH . WPINC . "/languages/*" );
$lang = get_option( "WPLANG" );
if( $lang == false ) {
	$lang = get_site_option( "WPLANG" );
	add_option( "WPLANG", $lang );
}

if( is_array( $lang_files ) && count($lang_files) > 1 ) {
	?>
		<tr valign="top"> 
		<th width="33%" scope="row"><?php _e('Language:') ?></th> 
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
<fieldset class="options"> 
<legend><?php _e('Date and Time') ?></legend> 
<table class="optiontable"> 
<tr> 
<th scope="row"><?php _e('<abbr title="Coordinated Universal Time">UTC</abbr> time is:') ?> </th> 
<td><code><?php echo gmdate('Y-m-d g:i:s a'); ?></code></td> 
</tr>
<tr>
<th scope="row"><?php _e('Times in the weblog should differ by:') ?> </th>
<td><input name="gmt_offset" type="text" id="gmt_offset" size="2" value="<?php form_option('gmt_offset'); ?>" /> 
<?php _e('hours') ?> </td>
</tr>
<tr>
<th scope="row"><?php _e('Default date format:') ?></th>
<td><input name="date_format" type="text" id="date_format" size="30" value="<?php form_option('date_format'); ?>" /><br />
<?php _e('Output:') ?> <strong><?php echo mysql2date(get_option('date_format'), current_time('mysql')); ?></strong></td>
</tr>
<tr>
<th scope="row"><?php _e('Default time format:') ?></th>
<td><input name="time_format" type="text" id="time_format" size="30" value="<?php form_option('time_format'); ?>" /><br />
<?php _e('Output:') ?> <strong><?php echo gmdate(get_option('time_format'), current_time('timestamp')); ?></strong></td>
</tr> 
<tr>
<th scope="row">&nbsp;</th>
<td><?php _e('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date formatting</a>. Save option to update sample output.') ?> </td>
</tr>
<tr>
<th scope="row"><?php _e('Weeks in the calendar should start on:') ?></th>
<td><select name="start_of_week" id="start_of_week">
<?php
for ($day_index = 0; $day_index <= 6; $day_index++) :
	$selected = (get_option('start_of_week') == $day_index) ? 'selected="selected"' : '';
	echo "\n\t<option value='$day_index' $selected>" . $wp_locale->get_weekday($day_index) . '</option>';
endfor;
?>
</select></td>
</tr>
</table>
</fieldset> 

<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" />
<input type="hidden" name="action" value="update" /> 
<input type="hidden" name="page_options" value="blogname,blogdescription,new_admin_email,users_can_register,gmt_offset,date_format,time_format,start_of_week,comment_registration,WPLANG,language,blog_public" /> 
</p>
</form>

</div> 

<?php include('./admin-footer.php') ?>
