<?php
define( "WP_INSTALLING", true );
require ('wp-config.php');
require_once( ABSPATH . WPINC . '/registration.php');


do_action("activate_header");

get_header();
?>
<div id="content" class="widecolumn">
<style type="text/css">
form { margin-top: 2em; }
#submit, #key {
	width: 90%;
	font-size: 24px;
}
#language {
	margin-top: .5em;
}
.error {
	background-color: #f66;
}
</style>
<?php
	if ( empty($_GET['key']) && empty($_POST['key']) ) {
?>
<h2>Activation Key Required</h2>
<form name="activateform" id="activateform" method="post" action="/wp-activate.php">
<table border="0" width="100%" cellpadding="9">
<tr>
<th valign="top">Activation Key:</th>
<td><input name="key" type="text" id="key" value="" /></td>
</tr>
<tr>
<th scope="row"  valign="top">&nbsp;</th>
<td><input id="submit" type="submit" name="Submit" class="submit" value="Activate &raquo;" /></td>
</tr>
</table>
</form>
<?php
	} else {
		if ( ! empty($_GET['key']) )
			$key = $_GET['key'];
		else
			$key = $_POST['key'];
			
		$result = wpmu_activate_signup($key);
		if ( is_wp_error($result) ) {
			if ( 'already_active' == $result->get_error_code() )
				echo __('The blog is already active.  Please check your email inbox for your username, password, and login instructions.');
			else 
				echo $result->get_error_message();
		} else {
			extract($result);
			$url = get_blogaddress_by_id($blog_id);
			$user = new WP_User($user_id);
?>
<h2><?php _e('All set!'); ?></h2>
<table border="0" id="signup-welcome">
<tr>
<td width="50%" align="center">
<h3><?php _e('Username'); ?>:</h3>
<p><?php echo $user->user_login ?></p></td>
<td width="50%" align="center">
<h3><?php _e('Password'); ?>:</h3>
<p><?php echo $password; ?></p>
</td>
</tr>
</table>
<h3 class="view"><?php printf(__('<a href="%1$s">View your site</a> or <a href="%2$s">Login</a>'), $url, 'http://' . $_SERVER[ 'SERVER_NAME' ] . '/' ); ?></h3>
<?php
		}
	}
?>
</div>
<?php get_footer(); ?>
