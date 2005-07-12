<?php 
/* 
 * Smarty plugin 
 * ------------------------------------------------------------- 
 * Type: modifier 
 * Name: highlight 
 * Version: 0.5 
 * Date: 2003-03-27
 * Author: Pavel Prishivalko, aloner#telephone.ru
 * Purpose: Highlight search term in text
 * Install: Drop into the plugin directory
 *
 * Extended To 0.5 By: Alexey Kulikov <alex@pvl.at>
 * Strips Tags for nice output, allows multiple term for highlight
 * Modified and simplified to high light b2 searches by Donncha O Caoimh
 * Added google search highlight using code from http://www.textism.com/tools/google_hilite/
 * ------------------------------------------------------------- 
 */ 

function smarty_modifier_highlightsearch($text, $start_tag='<b style="color: #000; background-color: #ff0;">', $end_tag='</b>') 
{ 
    global $smarty, $p, $s, $_SERVER, $HTTP_REFERER;

    $orig = $text;

    if( $s != '' )
    {
        $b = preg_quote($s);

        if (!preg_match('/<.+>/',$text)) {

            // If there are no tags in the text, we'll just do a simple search and replace
            $text = preg_replace('/(\b'.$b.'\b)/i',$start_tag.'$1'.$end_tag,$text);

        } else {

            // If there are tags, we need to stay outside them
            $text = preg_replace('/(?<=>)([^<]+)?(\b'.$b.'\b)/i','$1'.$start_tag.'$2'.$end_tag,$text);

        }
    }
    else
    {
        $ref = urldecode($_SERVER[ 'HTTP_REFERER' ]);
        // let's see if the referrer is google
        if (preg_match('/^http:\/\/w?w?w?\.?google.*/i',$ref)) 
        {
            // if so, tweezer out the search query
            $query = preg_replace('/^.*q=([^&]+)&?.*$/i','$1',$ref);

            // scrub away nasty quote marks
            $query = preg_replace('/\'|"/','',$query);

            // chop the search terms into an array
            $query_array = preg_split ("/[\s,\+\.]+/",$query);

            // loop through the search terms
            foreach($query_array as $b)
            {
                if (!preg_match('/<.+>/',$text)) {

                    // If there are no tags in the text, we'll just do a simple search and replace
                    $text = preg_replace('/(\b'.$b.'\b)/i',$start_tag.'$1'.$end_tag,$text);

                } else {

                    // If there are tags, we need to stay outside them
                    $text = preg_replace('/(?<=>)([^<]+)?(\b'.$b.'\b)/i','$1'.$start_tag.'$2'.$end_tag,$text);

                }
            }
        }
    }
    if( $text != $orig )
        if( $p != '' )
            $smarty->clear_cache( "post.tpl", $p.$p );

    return $text;
} 
?>
