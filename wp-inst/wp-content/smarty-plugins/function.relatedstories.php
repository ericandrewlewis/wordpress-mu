<?php

/* $Id: function.relatedstories.php,v 1.2 2005/01/09 11:54:18 donncha Exp $ */

/* 
   Originally by GamerZ, http://tidakada.com/board/viewtopic.php?t=1805&postdays=0&postorder=asc&start=0 
   Extensively modified by Donncha O Caoimh.

   Add {$relatedstories} to your post.tpl to enable it
*/

function relatedstories( $wpblog, $posts, $post )
{
    global $wpdb, $tableposts;

    $p = false;

    if( count( $posts ) == 1 )
    {
        if( $post->ID != "" )
        {
            $query = "SELECT post_title, post_content FROM $tableposts WHERE ID='".$post->ID."'";
            $p = $wpdb->get_row( $query );
            if( $p )
            {
                $titlesearch = $p->post_title;
                if( $titlesearch == '' )
                    $titlesearch = substr( str_replace( "'", "", strip_tags( $p->post_content ) ), 0, 20 );

                $howmanystories=5;

                $exclude = array ("a", "and", "are", "do", "it", "its", "is", "in", "i", "my", "the", "to", "with", "you", "this", "them" );

                $qry = "SELECT DISTINCT ID, DATE_FORMAT(post_date, '%e/%c/%y') as c, post_content, post_title FROM   $tableposts WHERE  (";

                $titlesearch = preg_replace('/, +/', '', $titlesearch);
                $titlesearch = strtolower( trim($titlesearch) );
                $words = explode (" ", $titlesearch);
                $words = array_diff($words,$exclude);

                reset( $words );
                foreach ($words as $word)
                {   
                    if( strlen( $word ) > 2 )
                    {
                        $word = addslashes( $word );
                        $qry .= "(post_content LIKE '% $word%' OR post_title LIKE '% $word%') OR ";
                        $usedWords .= stripslashes( $word ).", ";
                    }
                }
                $usedWords = substr( $usedWords, 0, -2 );
                $qry = substr ($qry, 0, strlen ($qry) - 4);
                $qry .= ") AND (ID != '".$post->ID."')  ORDER BY post_date DESC LIMIT $howmanystories";
                $p = $wpdb->get_results( $qry );
            }
        }
    }
    else
    {
    }
    return array( "p" => $p, "usedWords" => $usedWords );
}


function smarty_function_relatedstories( $params, &$smarty )
{
    global $wpblog, $wpdb, $post, $tableposts, $posts;

    extract( $params );
    if( @include_once( "Cache/Function.php" ) )
    {
        $cache = new Cache_Function( 'file', array('cache_dir' => ABSPATH . "/wp-content/smarty-cache", 'filename_prefix' => 'relatedstories_cache_' ), 3600 ); 
	$links = $cache->call( "relatedstories", $wpblog, $posts, $post );
    }
    else
    {
	$links = relatedstories( $wpblog, $posts, $post );
    }


    $smarty->assign( "relatedstories", $links[ 'p' ] );
    $smarty->assign( "relatedstoriesWords", $links[ 'usedWords' ] );
}

?>
