<?php

/* $Id: function.lastposts.php,v 1.3 2005/01/09 11:54:18 donncha Exp $ */


/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     lastposts
 * Purpose:  Returns a list of the last posts to the blog
 * -------------------------------------------------------------
 */

function getposts( $wpblog, $posts )
{
    global $wpdb;

    $query = "SELECT ID, post_title
              FROM ".$wpdb->posts."
              WHERE unix_timestamp( post_date ) < unix_timestamp( NOW() )
              AND   post_status = 'publish'
              ORDER BY `post_date` DESC LIMIT 0, ".intval( $posts );
    $result = $wpdb->get_results( $query );
    if( $result )
    {   
        foreach( $result as $details )
        {
            $postdata[ $details->ID ] = stripslashes( strip_tags( $details->post_title ) );
        }

        return $postdata;
    }
    else
    {
        return false;
    }
}

function smarty_function_lastposts($params, &$smarty)
{

    global $wpblog;

    $posts = 10;
    extract($params);

    if( $posts > 40 )
        $posts = 40;

    if( @include_once( "Cache/Function.php" ) )
    {
        $cache = new Cache_Function( 'file', array('cache_dir' => ABSPATH . "/wp-content/smarty-cache", 'filename_prefix' => 'lastposts_cache_' ), 600 ); 
        $lastposts = $cache->call( "getposts", $wpblog, $posts );
    }
    else
    {
        $lastposts = getposts( $wpblog, $posts );
    }

    $smarty->assign( "lastposts", $lastposts );
}

/* vim: set expandtab: */

?>
