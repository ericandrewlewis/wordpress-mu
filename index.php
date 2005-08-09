<?php
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
        
        h1 {
                color: #006;
                font-size: 18px;
                font-weight: lighter;
        }
        
        h2 {
                font-size: 16px;
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

<div align="center"><img src="wp-inst/wp-images/wordpress-mu.png"></div><br>
Welcome to WordPress MU, the Multi User Weblog System built on WordPress.<br><br>
';
}

function check_writeable_dir( $dir, $ret )
{
    if( is_writeable( $dir  ) == false )
    {
        print $dir." : <b style='color: #f55'>FAILED</b><br>Quick Fix: <code>chmod 777 $dir</code><br>";
        return false;
    }
    else
    {
        if( $ret == true )
        {
            return true;
        }
        else
        {
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

function do_htaccess( $oldfilename, $newfilename, $realpath, $base, $url )
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
            $htaccess = str_replace( "REALPATH", $realpath, $htaccess );
            $htaccess = str_replace( "BASE", $base, $htaccess );
            $htaccess = str_replace( "HOST", $url, $htaccess );
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
	    print "<p><strong>There was a problem creating the .htaccess file in $realpath.</strong> </p>";
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
		<li> Replace the following text:<ul><li>REALPATH by '$realpath'</li><li>BASE by '$base'</li><li>HOST by '$url'</li></li>
		<li> Rename htaccess.dist to .htaccess and upload it back to the same directory.</li></ul>";
	    die( "Installation Aborted!" );
    }
}

function checkdirs() {
    $ret = true;
    $ret = check_writeable_dir( dirname(__FILE__), $ret );
    $ret = check_writeable_dir( dirname(__FILE__) . "/wp-inst", $ret );
    $ret = check_writeable_dir( dirname(__FILE__) . "/wp-inst/wp-content/", $ret );

    if( $ret == false )
    {
        print "<h2>Warning!</h2>";
        print "<div style='border: 1px solid #ccc'>";
        print "<p style='font-weight: bold; padding-left: 10px'>One or more of the above directories must be made writeable by the webserver.<br>";
        print "Please <code>chmod 777 <q>directory-name</q></code> or <code>chown</code> that directory to the user the web server runs as (usually nobody, apache, or www-data)<br>";
        print "Refresh this page when you're done!<br></p>";
        print "</div>";
    }

    return $ret;
}

function step1() {
    print "<h1>Welcome to WPMU</h1>";
    print "<p>Please make sure mod_rewrite is installed as it will be activated at the end of this install.</p><p>If mod_rewrite is disabled ask your administrator to enable that module, or look at the <a href='http://httpd.apache.org/docs/mod/mod_rewrite.html'>Apache documentation</a> or <a href='http://www.google.com/search?q=apache+mod_rewrite'>elsewhere</a> for help setting it up.</p>";
    if( checkdirs() == false ) {
	return false;
    }

    // Create default template cache dirs
    @mkdir( dirname(__FILE__) . "/wp-inst/wp-content/smarty-cache" , 0777 );
    @mkdir( dirname(__FILE__) . "/wp-inst/wp-content/smarty-templates_c" , 0777 );

    // Create Blogs living area.
    @mkdir( dirname(__FILE__) . "/wp-inst/wp-content/blogs.dir", 0777 );


    $url = "http://".$_SERVER["SERVER_NAME"] . dirname( $_SERVER[ "SCRIPT_NAME" ] );
    if( substr( $url, -1 ) == '/' )
        $url = substr( $url, 0, -1 );
    $base = dirname( $_SERVER["SCRIPT_NAME"] );
    if( $base == "/")
    {
           $base = "";
    } 
    $realpath = dirname(__FILE__);

    if( is_file( dirname(__FILE__) . "./wp-inst/wpmu-settings.php" ) == false )
    {
        $configfile = '';
        $fp = fopen( "./wp-inst/wpmu-settings.php.dist", "r" );
        if( $fp )
        {
            while( !feof( $fp ) )
            {
                $configfile .= fgets( $fp, 4096 );
            }
            fclose( $fp );
        }
        $configfile = str_replace( "BASE", $base."/", $configfile );
        $fp = fopen( "./wp-inst/wpmu-settings.php", "w" );
        fwrite( $fp, $configfile );
        fclose( $fp );
    }
    return true;
}

function printstep1form( $dbname = 'wordpress', $uname = 'username', $pwd = 'password', $dbhost = 'localhost', $prefix = 'wp_' ) {
    print "
    <form method='post' action='index.php'> 
    <input type='hidden' name='action' value='step2'>
    <h1>Virtual Server Support</h1>
    <p>Each blog on your site will have their own hostname or 'sub domain'. Your blog addresses will appear like <span class='fakelink'>http://joesblog.example.com/</span> instead of <span class='fakelink'>http://www.example.com/joesblog/</span> but you need to do a few more things to Apache and your DNS settings before it'll work.</p>
    <p>Apache will have to have a <q>wildcard</q> alias configured in the virtual server definition of your server. You'll have to add a wildcard DNS record for your domain too. That's usually as easy as adding a <q>*</q> hostname in your online dns software.</p>
    <p>More: <ul><li> <a href='http://codewalkers.com/archives/general_admin/234.html'>Sub-domain catch-all with Apache</a> via <a href='http://www.google.com/search?q=apache+wildcard+alias'>Google Search: apache wildcard alias</a></li><li> <a href='http://photomatt.net/2003/10/10/wildcard-dns-and-sub-domains/'>Wildcard dns and sub domains</a> via <a href='http://www.google.com/search?q=dns+wildcard+sub+domain'>Google Search: dns wildcard sub domain</a></li><li><a href='http://mu.wordpress.org/forums/topic/126#post-677'>mu forums: how to setup vhosts</a></li></ul></p>
    <br />
    <h1>Database</h1>
<p>We need some information on the database. You will need to know the following items before proceeding.</p> 
<ol> 
  <li>Database name</li> 
  <li>Database username</li> 
  <li>Database password</li> 
  <li>Database host</li> 
  <li>Table prefix (if you want to run more than one WordPress in a single database) </li>
</ol> 
<p><strong>If for any reason this automatic file creation doesn't work, don't worry. All this does is fill in the database information to a configuration file. You may also simply open <code>wp-config-sample.php</code> in a text editor, fill in your information, and save it as <code>wp-config.php</code>. </strong></p>

  <p>Below you should enter your database connection details. If you're not sure about these, contact your host. </p>
  <table> 
    <tr> 
      <th scope='row'>Database Name</th> 
      <td><input name='dbname' type='text' size='45' value='".$dbname."' /></td> 
      <td>The name of the database you want to run WP in. </td> 
    </tr> 
    <tr> 
      <th scope='row'>User Name</th> 
      <td><input name='uname' type='text' size='45' value='".$uname."' /></td> 
      <td>Your MySQL username</td> 
    </tr> 
    <tr> 
      <th scope='row'>Password</th> 
      <td><input name='pwd' type='text' size='45' value='".$pwd."' /></td> 
      <td>...and MySQL password.</td> 
    </tr> 
    <tr> 
      <th scope='row'>Database Host</th> 
      <td><input name='dbhost' type='text' size='45' value='".$dbhost."' /></td> 
      <td>99% chance you won't need to change this value.</td> 
    </tr>
    <tr>
      <th scope='row'>Table Prefix</th>
      <td><input name='prefix' type='text' id='prefix' value='".$prefix."' size='45' /></td>
      <td>If you want to run multiple WordPress installations in a single database, change this.</td>
    </tr> 
  </table> 
  <input name='submit' type='submit' value='Submit' /> 
</form> ";
}

function step2() {
    $dbname  = $_POST['dbname'];
    $uname   = $_POST['uname'];
    $passwrd = $_POST['pwd'];
    $dbhost  = $_POST['dbhost'];
    $vhost   = $_POST['vhost' ]; 
    $prefix  = $_POST['prefix'];
    if (empty($prefix)) $prefix = 'wp_';

    // Test the db connection.
    define('DB_NAME', $dbname);
    define('DB_USER', $uname);
    define('DB_PASSWORD', $passwrd);
    define('DB_HOST', $dbhost);

    if (!file_exists('wp-inst/wp-config-sample.php'))
	die('Sorry, I need a wp-config-sample.php file to work from. Please re-upload this file from your WordPress installation.');

    $configFile = file('wp-inst/wp-config-sample.php');
    // We'll fail here if the values are no good.
    require_once('wp-inst/wp-includes/wp-db.php');
    printheader();

    print "Creating Database Config File: ";
    
    $handle = fopen('wp-inst/wp-config.php', 'w');

    foreach ($configFile as $line_num => $line) {
	switch (substr($line,0,16)) {
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
	    case "define('VHOST', ":
		fwrite($handle, str_replace("VHOSTSETTING", 'yes', $line));
	    break;
	    case '$table_prefix  =':
	    fwrite($handle, str_replace('wp_', $prefix, $line));
	    break;
	    default:
	    fwrite($handle, $line);
	}
    }
    fclose($handle);
    chmod('wp-inst/wp-config.php', 0666);
    print "<b style='color: #00aa00; font-weight: bold'>DONE</b><br />";
}

function printuserdetailsform( $weblog_title = 'My new Blog', $username = '', $email = '' ) {
    print " 
	<form method='post' action='index.php'> 
	<input type='hidden' name='action' value='step3'>
	<p>To finish setting up your blog, please fill in the folling form and click <q>Submit</q>.</p>
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
	<input name='submit' type='submit' value='Submit' /> 
	</form> 
	<br />
	You will be sent an email with your password and login links and details.";
}

function step3() {
    global $wpdb;
    $base = dirname( $_SERVER["SCRIPT_NAME"] );
    $domain = $_SERVER[ 'HTTP_HOST' ];
    if( substr( $domain, 0, 4 ) == 'www.' )
	$domain = substr( $domain, 4 );

    $email = $wpdb->escape( $_POST[ 'email' ] );
    $weblog_title = $wpdb->escape( $_POST[ 'weblog_title' ] );

    // set up site tables
    $query = "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'admin_email', '".$email."')";
    $wpdb->query( $query );
    $query = "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, 1, 'admin_user_id', '1')";
    $wpdb->query( $query );
    $wpdb->query( "INSERT INTO ".$wpdb->site." ( id, domain, path ) VALUES ( NULL, '$domain', '$base' )" );
    $wpdb->query( "INSERT INTO " . $wpdb->sitecategories . " VALUES (1, 'Uncategorized', 'uncategorized', '')" );

    $res = createBlog( $domain, $base, 'admin', $weblog_title, $email );
    if( $res == 'ok' ) {
	if( $base == '/' ) {
		$url = "http://".$_SERVER["HTTP_HOST"] . '/';
	} else {
		$url = "http://".$_SERVER["HTTP_HOST"] . $base . '/';
	}
	$realpath = dirname(__FILE__);
	do_htaccess( "htaccess.dist", ".htaccess", $realpath, $base, $url );
	do_htaccess( "wp-inst/htaccess.dist", "wp-inst/.htaccess", $realpath, $base, $url );

	$illegal_names = array( "www", "web", "root", "admin", "main", "invite", "administrator" );
	add_site_settings( "illegal_names", $illegal_names );

	print "<p>Well Done! Your blog has been set up and you have been sent details of your login and password in an email.</p>";
	print "<p>You may view your new blog by visiting <a href='".$url."'>".$url."</a>!</p>";
    } else {
	if( $res == 'error: problem creating blog entry' ) {
	    print "The <q>main</q> blog has already been created. Edit your blogs table and delete the entry for this domain!";
	} elseif( $res == 'error: username used' ) {
	    print "The username you chose is already in use, please select another one.";
	}
	print "<br>result: $res<br>";
	printuserdetailsform( $_POST[ 'weblog_title' ], $_POST[ 'username' ], $_POST[ 'email' ] );
    }
}

switch( $_POST[ 'action' ] ) {
    case "step2":
	// get blog username
	// create wp-inst/wp-config.php 
	step2();
	printuserdetailsform();
    break;
    case "step3":
	// call createBlog();
	// create .htaccess
	// print login info and links.
	require_once('./wp-inst/wp-config.php');
        require_once('./wp-inst/wp-admin/upgrade-functions.php');
	make_db_current_silent();
	populate_options();
        printheader();
	step3();
    break;
    default:
        // check that directories are writeable.
        // create wp-inst/wpmu-settings.php
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
<a href="http://mu.wordpress.org/">WPMU</a> | <a href="http://mu.wordpress.org/forums/">Support Forums</a>
</div>
</body>
</html>
