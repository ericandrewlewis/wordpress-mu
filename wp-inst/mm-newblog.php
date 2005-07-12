<?php
require('wp-config.php');

get_header();
?>
<div id="content" class="widecolumn">
<style type="text/css">
form { margin-top: 2em; }
td input {
	width: 90%;
	font-size: 24px;
}
.error {
	background-color: #FF6666;
}
</style>
<?php

if ($_POST) {

$username = $_POST['username'];
$email    = $_POST['email'   ];
$blogname = stripslashes($_POST['blogname']);

$username = sanitize_title($username);

$user_error = $email_error = $blog_error = false;

if ( empty( $username ) )
	$user_error = true;
if ( $wpdb->get_row("SELECT * FROM $wpdb->users WHERE user_login = '$username'") )
	$user_error = true;

if ( !is_email($email ) )
	$email_error = true;

if ( empty( $blogname ) )
	$blog_error = true;

if ( $user_error || $email_error || $blog_error ) {
?>
<h2>There was an error</h2>
<form method="post" action="">
<?php if ( !$user_error || !$email_error || !$blog_error ) : ?>
<p>This info was fine:</p>
<ul>
<?php if ( !$user_error ) : ?>
	<li><strong>Username:</strong> <?php echo $username; ?><input name="username" type="hidden" id="username" value="<?php echo $username; ?>" /></li>
<?php endif; ?>
<?php if ( !$blog_error ) : ?>
	<li><strong>Blog Name:</strong> <?php echo $blogname; ?><input name="blogname" type="hidden" id="blogname" value="<?php echo wp_specialchars($blogname, 1); ?>"  /></li>
<?php endif; ?>
<?php if ( !$email_error ) : ?>
	<li><strong>Email Address:</strong> 
		<?php echo $email; ?><input name="email" type="hidden" value="<?php echo wp_specialchars($email, 1); ?>" /></li>
<?php endif; ?>
</ul>
<?php endif; ?>

<p>There was a problem with the following, please correct it below and try again.</p>

	<table width="100%" >
<?php if ( $user_error ) : ?>
   	<tr class="error">
   		<th valign="top" scope="row">Username: </th>
   		<td>
  					<input name="username" type="text" id="username" value="<?php echo $username; ?>" maxlength="50" />
   			   <br />
   			   (This will also be your blog address. Letters and numbers only, please.) 
   			</td>
  		</tr>
<?php endif; ?>

<?php if ( $blog_error ) : ?>
   	<tr class="error">
   		<th valign="top" scope="row">Blog Name: </th>
   		<td><input name="blogname" type="text" id="blogname" value="<?php echo wp_specialchars($blogname, 1); ?>" />
  			<br>
  			(Don't worry, you can change it later.) </td>
  		</tr>
<?php endif; ?>

<?php if ( $email_error ) : ?>
   	<tr class="error">
   		<th valign="top" scope="row">Email Address: </th>
   		<td><p>
   			<input name="email" type="text" maxlength="200" value="<?php echo wp_specialchars($email, 1); ?>" />
   		   <br />
   		   (Did you type in your address wrong?)
   		</p>
  			</td>
  		</tr>
<?php endif; ?>
   	<tr>
   		<th scope="row">&nbsp;</th>
   		<td><input type="submit" class="submit" name="Submit" value="Try Again &raquo;" /></td>
  		</tr>
   	</table>
</form>
<?php
} else {

$main_host = $_SERVER['HTTP_HOST'];
$main_host = str_replace('www.', '', $main_host);

$base_path = dirname( $_SERVER['SCRIPT_NAME'] );	
$base_path = str_replace('wp-inst', '',$result);
if( strlen( $base_path > 1 ) && substr($base_path, -1 ) == '/')
	$base_path = substr($base_path, 0, -1);

define( 'WP_INSTALLING', true );
require_once('./wp-config.php');
$setup = true;

if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
	$host = $username . '.' . $main_host;
	$path = '/';
} else {
	$host = $main_host;
	$path = $base_path . '/' . $username;
}

$err = create_blog( $host, $path, $username, $blogname, $email );

?>
<h2>You've got a new blog</h2>
<h3>Your new address is <a href="http://<?php echo $host . $path; ?>"><?php echo $host . $path; ?></a></h3>
<p>You should receive an email with the login details shortly.</p>
<?php
} // if error
} else {
?>

<h2>Get your own blog</h2>

<form method="post" action="">
	<table width="100%" >
   	<tr>
   		<th valign="top" scope="row">Username: </th>
   		<td>
  					<input name="username" type="text" id="username" maxlength="50" />
   			   <br />
   			   (This will also be your blog address. Letters and numbers only, please.)
   			</td>
  		</tr>
   	<tr>
   		<th valign="top" scope="row">Blog Name: </th>
   		<td><input name="blogname" type="text" id="blogname">
  			<br>
  			(Don't worry, you can change it later.) </td>
  		</tr>
   	<tr>
   		<th valign="top" scope="row">Email Address: </th>
   		<td><p>
   			<input name="email" type="text" maxlength="200" />
   		   <br />
   		   (We'll send a password to this address, so double-check it.)
   		</p>
  			</td>
  		</tr>
   	<tr>
   		<th scope="row">&nbsp;</th>
   		<td><input type="submit" name="Submit" class="submit" value="Sign Up &raquo;" /></td>
  		</tr>
   	</table>
</form>
<?php 
} // if post
?>
</div>
<?php get_footer(); ?>