<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wp_ozh_click_topclicks
 * Purpose:  Prints list of top links
 * -------------------------------------------------------------
 */
function smarty_function_wp_ozh_click_comment_author_link($params, &$smarty)
{
    extract($params);

    if( function_exists( wp_ozh_click_comment_author_link ) )
    {
        wp_ozh_click_comment_author_link();
    }
    else
    {
        comment_author_link();
    }
}

/* vim: set expandtab: */


?>
