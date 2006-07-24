<?php
// don't ever call this file directly!
if( strpos( $_SERVER["REQUEST_URI"], 'index-install.php' ) ) {
	header( "Location: index.php" );
	die();
}
if( $_SERVER[ 'HTTP_HOST' ] == 'localhost' ) {
	die( "<h2>Warning!</h2> Installing to http://localhost/ is not supported. Please use <a href='http://localhost.localdomain/'>http://localhost.localdomain/</a> instead." );
}
define('WP_INSTALLING', true);

function printheader() {
    print '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
        <title>WordPress &rsaquo; Installation</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style media="screen" type="text/css">
        <!--
        html {
                background: #eee;
        }
        body {
                background: #fff;
                color: #000;
                font-family: Georgia, "Times New Roman", Times, serif;
                margin-left: 20%;
                margin-right: 20%;
                padding: .2em 2em;
        }
        
        h1, h2 {
                color: #006;
                font-size: 18px;
                font-weight: lighter;
        }
        
        p, li, dt {
                line-height: 140%;
                padding-bottom: 2px;
        }

        ul, ol {
                padding: 5px 5px 5px 20px;
        }
        #logo {
                margin-bottom: 2em;
        }
.step a, .step input {
        font-size: 2em;
}
.step, th {
        text-align: right;
}
#footer {
text-align: center; border-top: 1px solid #ccc; padding-top: 1em; font-style: italic;
}
.fakelink {
    color: #00a;
    text-decoration: underline;
}
        -->
        </style>
</head>
<body>

<h1><img src="wp-includes/images/wordpress-mu.png" alt="WordPress MU" /></h1>
';
}

function check_writeable_dir( $dir, $ret ) {
    if( is_writeable( $dir  ) == false ) {
        print $dir." : <b style='color: #f55'>FAILED</b><br />Quick Fix: <code>chmod 777 $dir</code><br />";
        return false;
    } else {
        if( $ret == true ) {
            return true;
        } else {
            return false;
        }
    }
}

function filestats( $err ) {
    print "<h1>Server Summary</h1>";
    print "<p>If you post a message to the MU support forum at <a target='_blank' href='http://mu.wordpress.org/forums/'>http://mu.wordpress.org/forums/</a> then copy and paste the following information into your message:</p>";

    print "<blockquote style='background: #eee; border: 1px solid #333; padding: 5px;'>";
    print "<br /><strong>ERROR: $err</strong></br >";
    clearstatcache();
    $files = array( "htaccess.dist", ".htaccess" );
    while( list( $key, $val ) = each( $files ) ) { 
	$stats = @stat( $val );
	if( $stats ) {
	    print "<h2>$val</h2>";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;uid/gid: " . $stats[ 'uid' ] . "/" . $stats[ 'gid' ] . "<br />\n";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;size: " . $stats[ 'size' ] . "<br />";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;perms: " . substr( sprintf('%o', fileperms( $val ) ), -4 ) . "<br />";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;readable: ";
	    print is_readable( $val ) == true ? "yes" : "no";
	    print "<br />";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;writeable: ";
	    print is_writeable( $val ) == true ? "yes" : "no";
	    print "<br />";
	    
	} elseif( file_exists( $val ) == false ) {
	    print "<h2>$val</h2>";
	    print "&nbsp;&nbsp;&nbsp;&nbsp;FILE NOT FOUND: $val<br>";
	}
    }
    print "</blockquote>";

}

function do_htaccess( $oldfilename, $newfilename, $base, $url )
{
    // remove ending slash from $base and $url
    $htaccess = '';
    if( substr($base, -1 ) == '/') {
	$base = substr($base, 0, -1);
    }

    if( substr($url, -1 ) == '/') {
	$url = substr($url, 0, -1);
    }
    $err = '';
    if( is_file( $oldfilename ) ) {
        $fp = @fopen( $oldfilename, "r" );
        if( $fp ) {
            while( !feof( $fp ) )
            {
                $htaccess .= fgets( $fp, 4096 );
            }
            fclose( $fp );
            $htaccess = str_replace( "BASE", $base, $htaccess );
	    if( touch( $newfilename ) ) {
		    $fp = fopen( $newfilename, "w" );
		    if( $fp ) {
			    fwrite( $fp, $htaccess );
			    fclose( $fp );
		    } else {
			    $err = "could not open $newfilename for writing";
		    }
	    } else {
		    $err = "could not open $newfilename for writing";
	    }
        } else {
	    $err = "could not open $oldfilename for reading";
	}
    } else {
	$err = "$oldfilename not found";
    }

    if( $err != '' ) {
	    print "<h1>Warning!</h1>";
	    print "<p><strong>There was a problem creating the .htaccess file.</strong> </p>";
	    print "<p style='color: #900'>Error: ";
	    if( $err == "could not open $newfilename for writing" ) {
		print "Could Not Write To $newfilename.";
	    } elseif( $err == "could not open $oldfilename for reading" ) {
		print "I could not read from $oldfilename. ";
	    } elseif( $err == "$oldfilename not found" ) {
		print "The file, $oldfilename, is missing.";
	    }
	    print "</p>";
	    filestats( $err );

	    print "<p>Please ensure that the webserver can write to this directory.</p>";
	    print "<p>If you use Cpanel then read <a href='http://mu.wordpress.org/forums/topic/99'>this post</a>. Cpanel creates files that I need to overwrite and you have to fix that.</p>";
	    print "<p>If all else fails then you'll have to create it by hand:";
	    print "<ul><li> Download htaccess.dist to your computer and open it in your favourite text editor.</li>
		<li> Replace the following text:<ul><li>BASE by '$base'</li><li>HOST by '$url'</li></li>
		<li> Rename htaccess.dist to .htaccess and upload it back to the same directory.</li></ul>";
	    die( "Installation Aborted!" );
    }
}

function checkdirs() {
    $ret = true;
    $ret = check_writeable_dir( dirname(__FILE__), $ret );
    $ret = check_writeable_dir( dirname(__FILE__) . "/wp-content/", $ret );

    if( $ret == false )
    {
        print "<h2>Warning!</h2>";
        print "<div style='border: 1px solid #ccc'>";
        print "<p style='font-weight: bold; padding-left: 10px'>One or more of the above directories must be made writeable by the webserver.<br>";
        print "Please <code>chmod 777 <q>directory-name</q></code> or <code>chown</code> that directory to the user the web server runs as (usually nobody, apache, or www-data)<br>";
        print "Refresh this page when you're done!<br></p>";
        print "</div>";
    }
    if( file_exists( "./.htaccess" ) && is_writeable( "./.htaccess" ) == false ) {
	    $ret = false;
	    print "<h2>Warning! .htaccess already exists.</h2>";
	    print "<div style='border: 1px solid #ccc'>";
	    print "<p style='font-weight: bold; padding-left: 10px'>A file with the name '.htaccess' already exists in this directory and I cannot write to it. Please ftp to the server and delete this file from this directory!<br />";
	    print "Offending file: " . realpath( '.htaccess' ) . "</p>";
	    print "</div>";
    }


    return $ret;
}

function step1() {
	print "<h2>Installing WP&micro;</h2>";
	$mod_rewrite_msg = "<p>If the <code>mod_rewrite</code> module is disabled ask your administrator to enable that module, or look at the <a href='http://httpd.apache.org/docs/mod/mod_rewrite.html'>Apache documentation</a> or <a href='http://www.google.com/search?q=apache+mod_rewrite'>elsewhere</a> for help setting it up.</p>";
    if( function_exists( "apache_get_modules" ) ) {
	    $modules = apache_get_modules();
		if( in_array( "mod_rewrite", $modules ) == false ) {
			echo "<p><strong>Warning!</strong> It looks like mod_rewrite is not installed.</p>" . $mod_rewrite_msg;
		}
	} else {
		?><p>Please make sure <code>mod_rewrite</code> is installed as it will be activated at the end of this install.</p><?php
			echo $mod_rewrite_msg;
    }
    if( checkdirs() == false ) {
	return false;
    }

    // Create Blogs living area.
    @mkdir( dirname(__FILE__) . "/wp-content/blogs.dir", 0777 );


    $url = stripslashes( "http://".$_SERVER["SERVER_NAME"] . dirname( $_SERVER[ "SCRIPT_NAME" ] ) );
    if( substr( $url, -1 ) == '/' )
        $url = substr( $url, 0, -1 );
    $base = stripslashes( dirname( $_SERVER["SCRIPT_NAME"] ) );
    if( $base != "/")
    {
           $base .= "/";
    } 
    $realpath = dirname(__FILE__);

    return true;
}

function printstep1form( $dbname = 'wordpress', $uname = 'username', $pwd = 'password', $dbhost = 'localhost', $prefix = 'wp_' ) {
	$weblog_title = 'My new WPMU Blog';
	$email = '';
	$hostname = str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
    print "
    <form method='post' action='index.php'> 
    <input type='hidden' name='action' value='step2'>
    <h2>Blog Addresses</h2>
	<p>Please choose whether you would like blogs for the MU install to use sub-domains or sub-directories. You can not change this later. We recommend sub-domains.</p>
	<p><label><input type='radio' name='vhost' value='yes' /> Sub-domains (like <code>blog1.example.com</code>)</label><br />
	<label><input type='radio' name='vhost' value='no' /> Sub-directories (like <code>example.com/blog1</code></label></p>
	
    <h2>Database</h2>

  <p>Below you should enter your database connection details. If you're not sure about these, contact your host. </p>
  <table cellpadding='5'> 
    <tr> 
      <th scope='row' width='33%'>Database Name</th> 
      <td><input name='dbname' type='text' size='45' value='$dbname' /></td>  
    </tr> 
    <tr> 
      <th scope='row'>User Name</th> 
      <td><input name='uname' type='text' size='45' value='$uname' /></td> 
    </tr> 
    <tr> 
      <th scope='row'>Password</th> 
      <td><input name='pwd' type='text' size='45' value='$pwd' /></td> 
    </tr> 
    <tr> 
      <th scope='row'>Database Host</th> 
      <td><input name='dbhost' type='text' size='45' value='$dbhost' /></td> 
    </tr>
  </table> 
  <h2>Server Address</h2>
  <p><label>What is the Internet address of your site? You should enter the shortest address possible. For example, use <em>example.com</em> instead of <em>www.example.com</em> but if you are going to use an address like <em>blogs.example.com</em> then enter that unaltered in the box below.<br /><b>Server Address:</b> <input type='text' name='basedomain' value='{$hostname}'></label></p>
  <h2>Blog Details</h2>
  <table width='100%'> 
  <tr> 
  <th scope='row'>Weblog&nbsp;Title</th> 
  <td><input name='weblog_title' type='text' size='45' value='".$weblog_title."' /></td> 
  <td>What would you like to call your weblog? </td> 
  </tr> 
  <tr> 
  <th scope='row'>Email</th> 
  <td><input name='email' type='text' size='45' value='".$email."' /></td> 
  <td>Your email address.</td> 
  </tr> 
  </table> 
  <p class='submit'><input name='submit' type='submit' value='Submit' /> </p>
</form> ";
}

function step2() {
	global $wpdb, $table_prefix, $base, $blog_id;
    $dbname  = $_POST['dbname'];
    $uname   = $_POST['uname'];
    $passwrd = $_POST['pwd'];
    $dbhost  = $_POST['dbhost'];
    $vhost   = $_POST['vhost' ]; 
    $prefix  = 'wp_';
    $base = stripslashes( dirname( $_SERVER["SCRIPT_NAME"] ) );
    if( $base != "/") {
           $base .= "/";
    } 

    // Test the db connection.
    define('DB_NAME', $dbname);
    define('DB_USER', $uname);
    define('DB_PASSWORD', $passwrd);
    define('DB_HOST', $dbhost);

    if (!file_exists('wp-config-sample.php'))
	die('Sorry, I need a wp-config-sample.php file to work from. Please re-upload this file from your WordPress installation.');

    $configFile = file('wp-config-sample.php');
    // We'll fail here if the values are no good.
    require_once('wp-includes/wp-db.php');
    printheader();

    print "Creating Database Config File: ";
    
    $handle = fopen('wp-config.php', 'w');

    foreach ($configFile as $line_num => $line) {
	switch (trim( substr($line,0,16) )) {
	    case "define('DB_NAME'":
		fwrite($handle, str_replace("wordpress", $dbname, $line));
	    break;
	    case "define('DB_USER'":
		fwrite($handle, str_replace("'username'", "'$uname'", $line));
	    break;
	    case "define('DB_PASSW":
		fwrite($handle, str_replace("'password'", "'$passwrd'", $line));
	    break;
	    case "define('DB_HOST'":
		fwrite($handle, str_replace("localhost", $dbhost, $line));
	    break;
	    case "define('VHOST',":
		fwrite($handle, str_replace("VHOSTSETTING", $vhost, $line));
	    break;
	    case '$table_prefix  =':
	    fwrite($handle, str_replace('wp_', $prefix, $line));
	    break;
	    case '$base = \'BASE\';':
	    fwrite($handle, str_replace('BASE', $base, $line));
	    break;
	    default:
	    fwrite($handle, $line);
	    break;
	}
    }
    fclose($handle);
    chmod('wp-config.php', 0666);
    print "<b style='color: #00aa00; font-weight: bold'>DONE</b><br />";
}

function printuserdetailsform( $weblog_title = 'My new Blog', $username = '', $email = '' ) {
    $hostname = str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
    print " 
	<form method='post' action='index.php'> 
	<input type='hidden' name='action' value='step3'>
	<p>To finish setting up your blog, please fill in the following form and click <q>Submit</q>.</p>
	<input name='submit' type='submit' value='Submit' /> 
	</form> 
	<br />
	You will be sent an email with your password and login links and details.";
}

function step3() {
    global $wpdb, $current_site;
    $base = stripslashes( dirname( $_SERVER["SCRIPT_NAME"] ) );
    if( $base != "/") {
           $base .= "/";
    } 
    $domain =   $wpdb->escape( $_POST[ 'basedomain' ] );
    if( substr( $domain, 0, 4 ) == 'www.' )
	$domain = substr( $domain, 4 );

    $email = $wpdb->escape( $_POST[ 'email' ] );
    $weblog_title = $wpdb->escape( $_POST[ 'weblog_title' ] );

    // set up site tables
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'admin_email', '".$email."')" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'admin_user_id', '1')" );
    $wpdb->query( "INSERT INTO ".$wpdb->site." ( id, domain, path ) VALUES ( NULL, '$domain', '$base' )" );
    $wpdb->query( "INSERT INTO " . $wpdb->sitecategories . " VALUES (1, 'Uncategorized', 'uncategorized', NOW())" );
    $wpdb->query( "INSERT INTO " . $wpdb->sitecategories . " VALUES (2, 'Blogroll', 'blogroll', NOW())" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'upload_filetypes', 'jpg jpeg png gif mp3 mov avi wmv midi mid pdf' )" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'blog_upload_space', '10' )" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'fileupload_maxk', '1500' )" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'site_admins', '" . serialize( array( 'admin' ) ) . "' )" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'allowed_themes', '" . serialize( array( 'WordPress Classic', 'WordPress Default' ) ) . "' )" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'illegal_names', '" . serialize( array(  "www", "web", "root", "admin", "main", "invite", "administrator" ) ) . "' )" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'welcome_email', 'Dear User,

Your new SITE_NAME blog has been successfully set up at:
BLOG_URL

You can log in to the administrator account with the following information:
 Username: USERNAME
 Password: PASSWORD
Login Here: BLOG_URLwp-login.php

We hope you enjoy your new weblog.
 Thanks!

--The Team @ SITE_NAME')" );
    $wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'first_post', 'Welcome to <a href=\"SITE_URL\">SITE_NAME</a>. This is your first post. Edit or delete it, then start blogging!' )" );

	$pass = substr( md5( rand() ), 5, 12 );
	$user_id = wpmu_create_user( 'admin', $pass, $email);

	$current_site->domain = $domain;
	$current_site->path = $base;
	$current_site->site_name = ucfirst( $domain );
	
	wpmu_create_blog( $domain, $base, $weblog_title, $user_id, array() );
	update_blog_option( 1, 'template', 'home');
	update_blog_option( 1, 'stylesheet', 'home');
	update_blog_option( 1, 'permalink_structure', '/blog/%year%/%monthnum%/%day%/%postname%/');
	update_blog_option( 1, 'rewrite_rules', '');
	$msg = "Your new WPMU site has been created at\nhttp://{$domain}{$base}\n\nLogin details:\nUsername: admin\nPassword: $pass\nLogin: http://{$domain}{$base}wp-login.php\n";
	wp_mail( $email, "Your new WPMU site is ready!", $msg, "From: wordpress@" . $_SERVER[ 'HTTP_HOST' ]  );
	print "<p>Congrats! Your <a href='http://{$domain}{$base}'>WPMU site</a> has been set up and you have been sent details of your login and password in an email.</p>";
}

switch( $_POST[ 'action' ] ) {
	case "step2":
		// get blog username
		// create wp-config.php 
		step2();
		// Install Blog!
		include_once('./wp-config.php');
		include_once('./wp-admin/upgrade-functions.php');
		make_db_current_silent();
		populate_options();
		do_htaccess( 'htaccess.dist', '.htaccess', $base, '');
		step3();
	break;
	case "step3":
		// call createBlog();
		// create .htaccess
		// print login info and links.
		require_once('./wp-config.php');
		require_once('./wp-admin/upgrade-functions.php');
		make_db_current_silent();
		populate_options();
		do_htaccess( 'htaccess.dist', '.htaccess', $base, '');
		printheader();
		step3();
	break;
	default:
		// check that directories are writeable.
		// create wpmu-settings.php
		// get db auth info.
		printheader();
		if( step1() ) {
			printstep1form();
		}
	break;
}
?>
<br /><br />
<div align='center'>
<a href="http://mu.wordpress.org/">WordPress &micro;</a> | <a href="http://mu.wordpress.org/forums/">Support Forums</a>
</div>
</body>
</html>
