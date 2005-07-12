<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     wp_ozh_click_topclicks
 * Purpose:  Prints list of top links
 * -------------------------------------------------------------
 */
function smarty_function_wp_ozh_click_topclicks($params, &$smarty)
{
    extract($params);

    if( function_exists( wp_ozh_click_topclicks ) )
    {
        wp_ozh_click_topclicks( $limit, $trim, $pattern );
    }
}

/* vim: set expandtab: */


?>
