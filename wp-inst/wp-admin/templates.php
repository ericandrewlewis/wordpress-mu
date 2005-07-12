<?php

/* $Id: templates.php,v 1.8 2005/03/12 20:18:49 donncha Exp $ */

require_once('admin.php');
$title = __("Template &amp; file editing");
$parent_file = 'edit.php';


if( isset( $_POST[ 'file' ] ) )
    $file = $_POST[ 'file' ];

if( isset( $_GET[ 'file' ] ) )
    $file = $_GET[ 'file' ];

if ($file=="") 
{
    $file = "index.html";
}

$file = str_replace( '..', '', $file );

if (substr($file,0,1) == "/")
    $file = ".".$file;

$file = stripslashes($file);
if (':' == substr($file,1,1))
    die ('Sorry, can&#8217;t call files with their real path.');

if( $wpblog != '' )
{
    $templateDir = "../wp-content/blogs/".$wpblog."/templates/";
    $filename = $templateDir . "$file";
}
else
{
    $templateDir = "../templates/";
    $filename = "templates/$file";
}
if( $backup != '' && $backup >= 0 && $backup <= 5 && is_file( $filename . "." . $backup ) )
{
    $filename .= "." . $backup;
}

switch($_POST[ 'action' ]) {

case 'update':

	if ($user_level < 5) {
		die(__('<p>You have do not have sufficient permissions to edit templates for this blog.</p>'));
	}

	$newcontent = stripslashes($_POST['newcontent']);
        $f = fopen( $filename, "r" );
        $content = fread( $f, filesize( $filename ) );
        fclose( $f );
        if( $content != $newcontent )
        {
            for( $t = 4; $t >= 1; $t -- )
            {
                if( is_file( $filename . "." . $t ) )
                {
                    rename( $filename . "." . $t, $filename . "." . ( $t + 1 ) );
                }
            }
            rename( $filename, $filename . ".1" );

            $f = fopen( $filename, "w+" );
            fwrite($f,$newcontent);
            fclose($f);

	    $wpsmarty->cache_dir = "../" . $wpsmarty->cache_dir;
	    $wpsmarty->clear_all_cache();

            header( "Location: templates.php?file=$file&a=te" );
        }
        else
        {
            header( "Location: templates.php?file=$file" );
        }

	exit();

break;

default:

	require_once('admin-header.php');

	if ($user_level <= 3) {
		die('<p>You have no right to edit the template for this blog.<br>Ask for a promotion to your <a href="mailto:$admin_email">blog admin</a>. :)</p>');
	}

	if (!is_file($filename))
		$error = 1;

	if (!$error) {
		$f = fopen($filename, 'r');
		$content = fread($f, filesize($filename));
		$content = htmlspecialchars($content);
	}

	?>
 <div class="wrap"> 
  <?php
	echo "<h2>Editing <strong>$file</strong></h2>";
	if( $backup != '' )
		echo ", backup <strong>$backup</strong>";
        echo " $warning";
	if ('te' == $a)
		echo "<em>File edited successfully.</em>";
	
	if (!$error) {
	?> 
  <form name="template" action="templates.php" method="post"> 
     <textarea cols="80" rows="20" style="width:100%; font-family: 'Courier New', Courier, monopace; font-size:small;" name="newcontent" tabindex="1"><?php echo $content ?></textarea> 
     <input type="hidden" name="action" value="update" /> 
     <input type="hidden" name="file" value="<?php echo $file ?>" /> 
     <br /> 
     <?php
		if (is_writeable($filename)) {
			echo "<input type=\"submit\" name=\"submit\" class=\"search\" value=\"update template !\" tabindex=\"2\" />";
		} else {
			echo "<input type=\"button\" name=\"oops\" class=\"search\" value=\"(you cannot update that file/template: must make it writable, e.g. CHMOD 666)\" tabindex=\"2\" />";
		}
		?> 
   </form> 
  <?php
	} else {
		echo '<p>Oops, no such file exists! Double check the name and try again, merci.</p>';
	}
	?> 
</div> 
<div class="wrap"> 
  <p>To edit a file, type its name here:</p> 
  <form name="file" action="templates.php" method="get"> 
    <input type="text" name="file" /> 
    <input type="submit" name="submit"  class="search" value="go" /> 
  </form> 
  <p>Note: of course, you can also edit the files/templates in your text editor of choice and upload them. This online editor is only meant to be used when you don't have access to a text editor or FTP client.</p> 
	<b>You can also edit the following files.</b><br />
	<br />
        <?php
        $templates = array ( 
                     "Main Page"       => "index.html",
		     "Site Style Sheet" => "wp-layout.css",
                     "Posts"        => "post.html",
                     "Comments"      => "comments.html",
                     "Old Template" => "index.tpl",
                     "Old Style Sheet" => "site.css"
		     );
        print "<table>";
        print "<tr><th>File</th><th colspan='5'>Backups</th><th>Notes</th></tr>";
        $notes = false;
        reset( $templates );
        foreach( $templates as $templateName => $templateFilename )
        {
            print "<tr><td><a href='templates.php?file=$templateFilename'><nobr>$templateName</nobr></a></td>";
            for( $t = 1; $t <= 5 ; $t ++ )
            {
                if( is_file( $templateDir . $templateFilename . "." . $t ) )
                {
                    print "<td><a href='templates.php?file=".$templateFilename."&backup=".$t."'>".$t."</a></td>";
                }
                else
                {
                    print "<td>" . $t . "</td>";
                }
            }
            if( $notes == false )
            {
                print "<td rowspan=10 valign='top' style='padding:10px'>";
                $notes = true;
                print "When you edit a file a backup is made of the old file.<br> Up to 5 backups are made before the oldest is lost. It's a FIFO queue so the newest backup is always <em>1</em> and the oldest being <em>5</em>. <br />
                    If the backup of a particular file exists it will be hyperlinked and clicking on that link will allow you to edit that file. Updating that backup template will restore the backup when saved and overwrite the template.<br />
                    (*) You probably don't want to edit the XML files unless you're absolutely sure you know what you're doing!<br />";
                    print "</td>";
            }
            print "</tr>\n";
        }
        print "</table> </div> ";

break;
}

include("admin-footer.php");
?> 
