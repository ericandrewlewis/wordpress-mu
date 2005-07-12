<?php

/* $Id: template-chooser.php,v 1.6 2005/02/24 13:46:48 donncha Exp $ */


$title = "Choose a Template";
$parent_file = 'edit.php';
require_once('admin.php');

$b2varstoreset = array('action','standalone','redirect','profile','error','warning','a','file', 'template', 'date' );
for ($i=0; $i<count($b2varstoreset); $i += 1) {
	$b2var = $b2varstoreset[$i];
	if (!isset($$b2var)) {
		if (empty($HTTP_POST_VARS["$b2var"])) {
			if (empty($HTTP_GET_VARS["$b2var"])) {
				$$b2var = '';
			} else {
				$$b2var = $HTTP_GET_VARS["$b2var"];
			}
		} else {
			$$b2var = $HTTP_POST_VARS["$b2var"];
		}
	}
}
require_once("admin-header.php");
print '<div class="wrap"> ';
switch( $action )
{
    case "activate":
        if ($dir = @opendir("../wp-content/sitetemplates/$template/templates/")) {
            while ($file = readdir($dir)) 
            {
                if( $file != '.' && $file != '..' )
                {
                    copy( "../wp-content/sitetemplates/$template/templates/$file", "../wp-content/blogs/$wpblog/templates/$file" );
                }
            }  
            closedir($dir);
            // modify css file.
            $cssfile = '';
            $fp = fopen( "../wp-content/blogs/$wpblog/templates/wp-layout.css", "r" );
            while (!feof ($fp)) 
            {
                $cssfile .= fgets($fp, 4096);
            }
            fclose ($fp);
	    $cssfile = str_replace( "BASE/", "BASE", $cssfile );
            $cssfile = str_replace( "BASE", $base, $cssfile );
            $fp = fopen( "../wp-content/blogs/".$wpblog."/templates/wp-layout.css", "w" );
            fwrite( $fp, $cssfile );
            fclose( $fp );
        }
        $wpsmarty->cache_dir = "../" . $wpsmarty->cache_dir;
        $wpsmarty->clear_all_cache();
        print "<div class='updated'>Theme $file activated.</div><br>";
    break;
    case "backup":
    $today = date( "YmdHis" );
    if ($dir = @opendir("../wp-content/blogs/".$wpblog."/templates/")) 
    {
        while ($file = readdir($dir)) 
        {
            if( substr( $file, 0 , 6 ) != 'backup' && $file != '.' && $file != '..' )
            {
                $backupFiles[] = $file;
            }
            if( substr( $file, 0 , 6 ) == 'backup' )
            {
                $numBackups[ substr( $file, 0 , 21 ) ] = true;
            }
        }
        closedir( $dir );

        if( is_array( $backupFiles ) )
        {
            if( count( $numBackups ) < 5 )
            {
                while( list( $key, $val ) = each( $backupFiles ) ) 
                { 
                    copy( "../wp-content/blogs/".$wpblog."/templates/".$val, "../wp-content/blogs/".$wpblog."/templates/backup$today-$val" );
                } 
                print "<div class='updated'>Created Backup: $today</div><br>";
            }
            else
            {
                print "<div class='updated'>Maximum number of backups already made. You must delete one backup to save another.</div><br>";
            }
        }
    }
    break;
    case "delete":
    if ($dir = @opendir("../wp-content/blogs/$wpblog/templates/")) 
    {
        $deletedFiles = false;
        while ($file = readdir($dir)) 
        {
            if( substr( $file, 0 , 20 ) == 'backup'.$date )
            {
                $deletedFiles = true;
                unlink( "../wp-content/blogs/$wpblog/templates/$file" );
            }
        }
        if( $deletedFiles )
        {
            print "<div class='updated'>Deleted backup: $date</div><br>";
        }
    }
    break;
    case "restore":
    if ($dir = @opendir("../wp-content/blogs/$wpblog/templates/")) 
    {
        while ($file = readdir($dir)) 
        {
            if( substr( $file, 0 , 20 ) == 'backup'.$date )
            {
                $restoreFiles[] = $file;
            }
        }
        closedir( $dir );

        if( is_array( $restoreFiles ) )
        {
            while( list( $key, $val ) = each( $restoreFiles ) ) 
            { 
                copy( "../wp-content/blogs/$wpblog/templates/$val", "../wp-content/blogs/$wpblog/templates/" . substr( $val, 21 ) );
            } 
            print "<div class='updated'>Restored backup: $date </div><br>";
        }
    }
    break;
    case "view":
        if( is_file( "../wp-content/blogs/$wpblog/templates/backup".$date."-index.html" ) )
        {
            print "index.html backup from $date<br>";
            print "<form><textarea style='font-size: 10px' rows=20 cols=80 readonly>";
            readfile( "../wp-content/blogs/$wpblog/templates/backup".$date."-index.html" );
            print "</textarea></form>";
        }
    break;
    default:
    break;
}
print "<script LANGUAGE='JavaScript'>
<!--
function confirmSubmit(msg)
{
    return confirm('Are you sure you want to '+msg);
}
// -->
</script>";
$siteurl = get_settings( 'siteurl' );
$examplesdir = "../wp-content/sitetemplates";
if ($dir = @opendir( $examplesdir )) 
{
    $c = "col0";
    $main = "<table class='templatelist'>\n";
    $msg = "use this template?";
    while ($file = readdir($dir)) 
    {
        if( $file != '.' && $file != '..' && $file != '.htaccess' && is_dir( "../wp-content/sitetemplates/".$file ) )
        {
            if( file_exists( "../wp-content/sitetemplates/" . $file . "/index.php" ) )
            {
                include( "../wp-content/sitetemplates/".$file . "/index.php" );
                $main.= "<tr><td><a href='".$base."wp-inst/wp-content/sitetemplates/".$file."/screenshot.gif'><img style='border: 1px dashed #000' border=0 src='".$base."wp-inst/wp-content/sitetemplates/". $file ."/screenshot-sm.gif'></a></td><td>";
                $main .= "$title - $when<br>";
                $main .= "<a href='$url'>$author</a><br>";
                $main .= $description;
                $main .= "<br /><a onclick=\"return confirmSubmit('$msg')\" href='template-chooser.php?action=activate&template=$file'>Use This Theme</a>";
                $main .= "</td></tr>\n";
            }
        }
    }  
    $main .= "</table>";
    closedir($dir);
    print $main;
    print "<p style='padding: 5px'><b>Warning!</b> By clicking on <em style='color: #00f'>Use This Theme</em> you will overwrite your current template. Backup your existing template if you want to use it in the future!</p>";
}
print "<a href='template-chooser.php?action=backup'>Backup</a> existing template? You can make a total of 5 backups.<br>";
if ($dir = @opendir("../wp-content/blogs/$wpblog/templates/")) 
{
    while ($file = readdir($dir)) 
    {
        if( substr( $file, 0 , 6 ) == 'backup' )
        {
            $backups[ substr( $file, 6, 14 ) ] = "backup";
        }
    }
    if( is_array( $backups ) )
    {
        print "Backups:<br>\n";
        print "<table>";
        while( list( $key, $val ) = each( $backups ) ) 
        { 
            print "<tr><td><b>$key</b>: </td><td><a onclick=\"return confirmSubmit('restore this backup?')\" href='template-chooser.php?action=restore&date=$key'>Restore</a></td><td><a onclick=\"return confirmSubmit('delete this backup?')\" href='template-chooser.php?action=delete&date=$key'>Delete</a></td><td><a href='template-chooser.php?action=view&date=$key'>View index.html</a></td></tr>\n"; 
        }
        print "</table>";
    }
}
print "</div>";
include("admin-footer.php");
?>
