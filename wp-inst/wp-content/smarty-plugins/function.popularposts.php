<?php

/* $Id: function.popularposts.php,v 1.2 2005/01/09 11:54:18 donncha Exp $ */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.popularposts.php
 * Type:     function
 * Name:     popularposts
 * Purpose:  outputs an image from the images/ directory.
 * -------------------------------------------------------------
 */

function getlinks( $wpblog )
{
    global $wpdb, $site;
    $s = strlen( $site );
    $sql = "SELECT visitURL, sum( visitTimes ) as c
	    FROM `referer_visitLog`
	    WHERE blogID = '".$wpblog."' 
	    AND   visitURL LIKE '%/'
	    GROUP BY visitURL
	    ORDER BY c DESC  LIMIT 0 , 30";
    $results = $wpdb->get_results($sql);
    if( $results )
    {
        reset( $results );
        while( list( $key, $t ) = each( $results ) )
        {
            if( substr( $t->visitURL, -9 ) == 'index.php' )
                $t->visitURL = substr( $t->visitURL, 0, -9 );
            $hits[ $t->visitURL ] += $t->c;
        }
        arsort( $hits );
        $hits = array_flip( $hits );
        reset( $hits );
        while( list( $key, $val ) = each( $hits ) ) 
        { 
            if( substr( $val, -1 ) == '/' )
            {
                $post_name = substr( $val, 0, -1 );
                $post_name = substr( $post_name, strrpos( $post_name, '/' ) + 1 );
                $sql = "SELECT post_title 
                        FROM   ".$wpdb->posts."
                        WHERE  post_name = '".$post_name."'";
                $results = $wpdb->get_results($sql);
                if( $results )
                {   
                    $links[ $key ] = array( "url" => $val, "title" => stripslashes( $results[0]->post_title ) );
                }
            }
        }
    }
    else
    {
        $links = false;
    }

    return $links;
}

function smarty_function_popularposts($params, &$smarty)
{
    global $wpblog;
    extract( $params );

    if( @include_once( "Cache/Function.php" ) )
    {
        $cache = new Cache_Function( 'file', array('cache_dir' => ABSPATH . "/wp-content/smarty-cache", 'filename_prefix' => 'popularposts_cache_' ), 3600 ); 
	$links = $cache->call( "getlinks", $wpblog );
    }
    else
    {
	$links = getlinks( $wpblog, $site );
    }

    $smarty->assign( "pposts", $links );
}
?>
