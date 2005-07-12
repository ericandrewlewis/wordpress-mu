<?php

/* $Id: function.photoblog.php,v 1.4 2004/11/30 00:50:56 donncha Exp $ */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.photoblog.php
 * Type:     function
 * Name:     photoblog
 * Purpose:  outputs an image from the images/ directory.
 * -------------------------------------------------------------
 */
function get_post_id( $when )
{
    global $wpdb;
    $query = "SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key='photoblog' AND meta_value='".$when."'";
    $post_id = $wpdb->get_var( $query );
    return $post_id;

}
function smarty_function_photoblog($params, &$smarty)
{
    global $id, $wpblog, $siteurl, $wpdb;

    $start = "<div align='center'>";
    $msg = "<b>Pic of the Day</b><br>";
    $randmsg = "<b>Random Pic</b><br>";
    $end = "</div>";
    $class = "pictureborder";
    $display = 'yes';

    extract( $params );
    if( isset( $when ) == false )
    {
        $when = date( "Ymd" );
    }

    if( is_file( ABSPATH."wp-content/blogs/".$wpblog."/images/photoblog-".$when.".jpg" ) )
    {
        $post_id = get_post_id( $when );
        if( $display == 'yes' )
        {
            echo $start;
            echo $msg;
            echo "<img class='$class' src='".$siteurl."/images/photoblog-".$when.".jpg'>";
            if( $post_id )
            {
                $t = $id;
                $id = $post_id;
                print "<br /><a href='".get_permalink( $post_id )."'>";
                print comments_number( 'View Comments', 'Comments (1)', $more='Comments (%)');
                print "</a>";
                $id = $t;
            }
            echo $end;
        }
        else
        {
            return array( "image" => $siteurl."/images/photoblog-".$when.".jpg" );
        }
    }
    else
    {
        $dir = "wp-content/blogs/".$wpblog."/images/";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if( strpos( $file, "hotoblog-" ) == 1 )
                    {
                        $files[] = $file;
                    }
                }
                closedir($dh);
                $pic = $files[ rand( 0, count( $files ) - 1 ) ];
                if( $pic != '' )
                {
                    $when = substr( $pic, 10, 8 );
                    $post_id = get_post_id( $when );
                    if( $display == 'yes' )
                    {
                        echo $start;
                        echo $randmsg;
                        echo "<img class='$class' src='".$siteurl."/images/".$pic."'>";
                        if( $post_id )
                        {
                            $t = $id;
                            $id = $post_id;
                            print "<br /><a href='".get_permalink( $post_id )."'>";
                            print comments_number( 'View Comments', 'Comments (1)', $more='Comments (%)');
                            print "</a>";
                            $id = $t;
                        }
                        echo $end;
                    }
                }
                
            }
        }
    }
}
?>
