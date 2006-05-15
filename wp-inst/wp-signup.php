<?php
require ('wp-config.php');
require_once( ABSPATH . WPINC . '/registration-functions.php');

do_action("signup_header");

get_header();
?>
<div id="content" class="widecolumn">
<style type="text/css">
form { margin-top: 2em; }
#submit, #blog_title, #user_email {
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
function show_signup_form($blog_id = '', $blog_title = '', $user_email = '', $errors = '') {
	global $current_user, $current_site, $wpdb;

	if ( ! is_wp_error($errors) )
		$errors = new WP_Error();

	echo '<h2>' . __('Get your own WordPress MU blog in seconds') . '</h2>';

	if ( $errors->get_error_code() ) {
		print "<p>There was a problem, please correct the form below and try again.</p>";
	}
?>

<form name="setupform" id="setupform" method="post" action="">
<input type="hidden" name="stage" value="1">
<table border="0" width="100%" cellpadding="9">
<?php
	// Blog name/Username
	if ( $errors->get_error_message('blog_id') || $errors->get_error_message('user_name') ) {
		print '<tr class="error">';
	} else {
		print '<tr>';
	}
	if ( !is_user_logged_in() )
		echo '<th valign="top">' . __('Username:') . '</th><td>';
	else
		echo '<th valign="top">' . __('Blog Domain:') . '</th><td>';

	if ( $errmsg = $errors->get_error_message('user_name') ) {
?><p><strong><?php echo $errmsg ?></strong></p><?php
	} else if ( $errmsg = $errors->get_error_message('blog_id') ) {
?><p><strong><?php echo $errmsg ?></strong></p><?php
	}
	if ( constant( 'VHOST' ) == 'yes' )
		echo '<input name="blog_id" type="text" id="blog_id" value="'.$blog_id.'" maxlength="50" style="width:40%; text-align: right; font-size: 30px;" /><span style="font-size: 30px">' .  $current_site->domain . '</span><br />';
	else
		echo '<span style="font-size: 30px">' .  $current_site->domain . '/</span><input name="blog_id" type="text" id="blog_id" value="'.$blog_id.'" maxlength="50" style="width:40%; font-size: 30px;" /><br />';

	if ( !is_user_logged_in() ) print 'At least 4 letters and numbers only, please. It cannot be changed so choose carefully!)</td> </tr>';

	// Blog Title
	if ( $errors->get_error_message('blog_title')) {
		print '<tr class="error">';
	} else {
		print '<tr>';
	}
?><th valign="top" width="120">Blog Title:</th><td><?php

	if ( $errmsg = $errors->get_error_message('blog_title') ) {
?><p><strong><?php echo $errmsg ?></strong></p><?php
	}
	print '<input name="blog_title" type="text" id="blog_title" value="'.wp_specialchars($blog_title, 1).'" /></td>
		</tr>';

	// User Email
	// Don't show email field if user is logged in.
	if ( !is_user_logged_in() ) {
		if ( $errors->get_error_message('user_email') ) {
			print '<tr class="error">';
		} else {
			print '<tr>';
		}
?><th valign="top">Email&nbsp;Address:</th><td><?php

		if ( $errmsg = $errors->get_error_message('user_email') ) {
?><p><strong><?php echo $errmsg ?></strong></p><?php
		}
		print '
		<input name="user_email" type="text" id="user_email" value="'.wp_specialchars($user_email, 1).'" maxlength="200" /><br /> (We&#8217;ll send your password to this address, so double-check it.)</td>
		</tr>';
	}

	if ( $errmsg = $errors->get_error_message('generic') )
		print '<tr class="error"> <th colspan="2">'.$errmsg.'</th> </tr>';
?>
<tr>
<th scope="row"  valign="top">&nbsp;</th>
<td><input id="submit" type="submit" name="Submit" class="submit" value="Sign Up &raquo;" /></td>
</tr>
<?php
	do_action('signup_form');
	print '
	</table>
	</form>';
}

function show_signup_confirm_form($domain, $path, $blog_title, $user_name, $user_email, $meta) {
?>
<h2><?php printf(__('%s Is Yours'), $domain) ?></h2>
<p><?php _e('But, before you can start using your blog, <strong>you must activate it</strong>.') ?></p>
<p><?php printf(__('Check your inbox at <strong>%1$s</strong> and click the link given.  '),  $user_email) ?></p>
<p><?php _e('If you do not activate your blog within two days, you will have to sign up again.'); ?></p>
<?php
	do_action('signup_finished');
}

function show_create_confirm_form($domain, $path, $blog_title, $user_name, $user_email, $meta) {
?>
<h2><?php printf(__('%s Is Yours'), $domain) ?></h2>
<p><?php printf(__('%1$s is your new blog.  <a href="%2$s">Login</a> as "%3$s" using your existing password.'), $domain, "http://${domain}${path}wp-login.php", $user_name) ?></p>
<?php
	do_action('signup_finished');
}

function validate_signup_form() {
	global $current_user;
	if ( is_user_logged_in() )
		$result = wpmu_validate_signup($_POST['blog_id'], $_POST['blog_title'], $current_user, $current_user->user_email);	
	else
		$result = wpmu_validate_signup($_POST['blog_id'], $_POST['blog_title'], $_POST['blog_id'], $_POST['user_email']);
	extract($result);

	if ( empty($errors) )
		$errors = new WP_Error();
	
	if ( $errors->get_error_code() ) {
		show_signup_form($blog_id, $blog_title, $user_email, $errors);
		return;
	}
	$errors = new WP_Error();

	if (empty ($blog_id)) {
		$errors->add('blog_id', __("Sorry, your blog domain may only contain the characters a-z or 0-9!"));
		show_signup_form($blog_id, $blog_title, $user_email, $errors);
		return;
	}

	$public = (int) $_POST['blog_public'];
	$meta = array ();

	if ( is_user_logged_in() ) {
		wpmu_create_blog($domain, $path, $blog_title, $current_user->id, $meta);
		show_create_confirm_form($domain, $path, $blog_title, $current_user->user_login, $current_user->user_email, $meta);		
	} else {
		wpmu_signup($domain, $path, $blog_title, $blog_id, $user_email, $meta);
		show_signup_confirm_form($domain, $path, $blog_title, $blog_id, $user_email, $meta);
	}
}

switch ($_POST['stage']) {
	case "1" :
		validate_signup_form();
		break;
	default :
		$user_login = '';
		$blog_title = '';
		$email = '';
		if ($_GET['new'])
			$user_login = stripslashes($_GET['new']);

		show_signup_form($user_login, $blog_title, $email);
		break;
}
?>
</div>

<?php get_footer(); ?>