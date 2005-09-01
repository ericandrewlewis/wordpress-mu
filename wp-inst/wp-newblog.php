<?php
require('wp-config.php');

do_action( "newblogheader", "" );
include( "wp-newblog.inc.php" );

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

function displayInitialForm( $weblog_id = '', $weblog_title = '', $admin_email = '', $admin_login = '', $form = '', $errormsg = '' ) {
    print "<h2>Get your own blog</h2>";
    if( is_array( $errormsg ) ) {
	print "<p>There was a problem, please correct the form below and try again.</p>";
    }
    print '
	<form name="setupform" id="setupform" method="post" action="wp-newblog.php">
	<input type="hidden" name="stage" value="1">
	<table border="0" width="100%">';
    if( isset( $errormsg[ 'weblog_id' ] ) == true ) {
	print '<tr class="error">';
    } else {
	print '<tr>';
    }
    print '
	<th valign="top">Username:</th>
	<td><input name="weblog_id" type="text" id="weblog_id" value="'.$weblog_id.'" maxlength="50" 
/><br />(Your address will be username.wordpress.com. Letters and numbers only, 
please!)</td>
	</tr>';
    if( $errormsg[ 'weblog_title' ] != '' ) {
	print '<tr class="error">';
    } else {
	print '<tr>';
    }
    print '
	<th valign="top">Blog Title:</th>
	<td><input name="weblog_title" type="text" id="weblog_title" value="'.wp_specialchars( $weblog_title, 1 ).'" /><br /> (Don\'t worry, you can change it later.)</td>
	</tr>';
    if( $errormsg[ 'admin_email' ] != '' ) {
	print '<tr class="error">';
    } else {
	print '<tr>';
    }
    print '
	<th valign="top">Email&nbsp;Address:</th>
	<td><input name="admin_email" type="text" id="admin_email" value="'.wp_specialchars( $admin_email, 1 ).'" maxlength="200" /><br /> (We\'ll send a password to this address, so double-check it.)</td>
	</tr>';
    if( $form == 'adminform' ) {
	print '
	<tr class="error">
	<th colspan="2">You have tried to setup a new domain. Please enter the administrator password for this site.</th>
	</tr>
	<tr>
	<th>Admin Password:</th>
	<td><input name="admin_pw" type="password" id="admin_pw" value="" /></td>
	</tr>
	';
    }
    print '
   	<tr>
   		<th scope="row">&nbsp;</th>
   		<td><input id="submit" type="submit" name="Submit" class="submit" value="Sign Up &raquo;" /></td>
  		</tr>';
    do_action( "newblogform", "" );
    print '
	</table>
	</form>';
}

function displaySecondForm() {
    global $url;
    print "<h2>You've got a new blog!</h2>";
    print "<h3>Your new address is <a href='".$url."'>".$url."</a></h3>
	<p>You should receive an email with the login details shortly.</p>";
    print "<p>Visit: <a href='$url'>$url</a><br>";
    print "Login: <a href='".$url."wp-login.php'>".$url."wp-login.php</a></p>";
    do_action( "newblogfinished", "" );
}

/*
   Determines the directory path - using the current script
 */
function determineDirPath() {
    global $_SERVER;

    $result = dirname( $_SERVER["SCRIPT_NAME"] );	
    $result = str_replace("wp-inst","",$result);

    return $result;
}

switch( $_POST[ 'stage' ] )
{
    case "1":
	$illegal_names = get_site_option( "illegal_names" );
	if( $illegal_names == false ) {
	    $illegal_names = array( "www", "web", "root", "admin", "main", "invite", "administrator" );
	    add_site_option( "illegal_names", $illegal_names );
	}

	$newBlogID = sanitize_title($_POST['weblog_id']);
	if( in_array( $newBlogID, $illegal_names ) == true ) {
	    $errormsg[ 'weblog_id' ] = true;
	}
	$weblog_title = stripslashes(  $_POST[ 'weblog_title' ] );
	$admin_email = $_POST[ 'admin_email' ];

	if( is_email( $admin_email ) == false )
	    $errormsg[ 'admin_email' ] = true;

	if( empty( $newBlogID ) )
	    $errormsg[ 'weblog_id' ] = true;

	if( empty( $weblog_title ) )
	    $errormsg[ 'weblog_title' ] = true;

	$newBlogID = apply_filters( "newblog_id", $newBlogID );

	if( is_array( $errormsg ) ) {
	    displayInitialForm( $_POST[ 'weblog_id' ], $weblog_title, $_POST[ 'admin_email' ], $_POST[ 'admin_login' ], 'userform', $errormsg );
	} elseif( isset($newBlogID) && ($newBlogID !='' )) {
	    $scriptBaseName = determineDirPath();
	    $serverName = $_SERVER[ 'SERVER_NAME' ];
	    define( "WP_INSTALLING", true );
	    require_once('./wp-config.php');
	    // check if "main" being installed. ask for admin pw if not defined..
	    $setup = true;
	    if( $newBlogID == 'main' && isset( $_POST[ 'admin_pw' ] ) == false ) {
		displayInitialForm( $_POST[ 'weblog_id' ], $weblog_title, $_POST[ 'admin_email' ], $_POST[ 'admin_login' ], 'adminform' );
		$setup = false;
	    } elseif( $newBlogID == 'main' && isset( $_POST[ 'admin_pw' ] ) == true ) {
		$admin_id = $wpdb->get_var( "SELECT ID FROM   ".$wpdb->users." WHERE  user_pass = '".md5( $_POST[ 'admin_pw' ] )."' AND    user_login = 'admin'" );
		if( $admin_id != 1 ) {
		    displayInitialForm( $_POST[ 'weblog_id' ], $_POST[ 'weblog_title' ], $_POST[ 'admin_email' ], $_POST[ 'admin_login' ], 'adminform' );
		    $setup = false;
		}
	    }


	    if( substr( $domain, 0, 4 ) == 'www.' )
		$domain = substr( $domain, 4 );
	    if( $setup == true ) {
		if( defined( "VHOST" ) && constant( "VHOST" ) == 'yes' ) {
		    if( $newBlogID == 'main' ) {
			$url = 'http://www'.$domain.$scriptBaseName;
		    } else {
			$url = 'http://'.$newBlogID.".".$domain.$scriptBaseName;
		    }
		} else {
		    $url = 'http://'.$serverName.$scriptBaseName.$newBlogID."/";
		}
		$err = createBlog( $newBlogID.".".$domain, $scriptBaseName, $newBlogID, $weblog_title, $admin_email );
		if( $err == 'ok' ) {
		    displaySecondForm();
		    $email = get_site_option( "admin_email" );
		    $msg = "A new blog has been created on " . $current_site->domain . $current_site->path . "\n";
		    $msg .= "Address: http://" . $newBlogID . "." . $domain . $scriptBaseName . "\n";
		    $msg .= "Title: $weblog_title\n";
		    $msg .= "Email: $admin_email\n";
		    $msg .= "Backend: http://" . $newBlogID . "." . $domain . $scriptBaseName . "wp-login.php (You can login with your admin u/p)\n";
		    $subject = "New Blog on: http://" . $current_site->domain . $current_site->path;
		    mail( $email, $subject, $msg, "From: WordPress <" . $admin_email . ">" );
		} else {
		    if( $err == 'error: username used' ) {
			$errormsg[ 'weblog_id' ] = "Sorry, that blog already exists!";
		    } elseif( $err == 'error: email domain not allowed' ) {
			$errormsg[ 'admin_email' ] = "Sorry, that email address is not allowed!";
		    } else {
			$errormsg[ 'weblog_id' ] = "Sorry, that blog already exists!";
		    }
		    displayInitialForm( $_POST[ 'weblog_id' ], $weblog_title, $_POST[ 'admin_email' ], $_POST[ 'admin_login' ], 'userform', $errormsg );
		}
	    }
	} else {
	    $errormsg[ 'weblog_id' ] = "Sorry, your blog ID may only contain the characters a-z, A-Z, or 0-9!";
	    displayInitialForm( $_POST[ 'weblog_id' ], $weblog_title, $_POST[ 'admin_email' ], $_POST[ 'admin_login' ], 'userform', $errormsg );
	}
        break;
    default:
	if( $_GET[ 'u' ] ) {
		$email = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$_GET[ 'u' ]}_to_email' AND user_id = '0'" );
		$name = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$_GET[ 'u' ]}_to_name' AND user_id = '0'" );
		$user_login = '';
		if( $name != false ) {
			$user_login = strtolower( $name );
			if( $wpdb->get_var( "SELECT user_login FROM {$wpdb->users} WHERE user_login = '{$user_login}'" ) != false ) {
				$count = 1;
				while( $wpdb->get_var( "SELECT user_login FROM {$wpdb->users} WHERE user_login = '{$user_login}{$count}'" ) != false ) {
					$count ++;
				}
				$user_login = $user_login . $count;
			}
			$weblog_title = $name."'s Blog";
		} else {
			$weblog_title = '';
		}
	}
	displayInitialForm( $user_login, $weblog_title, $email );
	break;
}
?>
</div>
<?php get_footer(); ?>
