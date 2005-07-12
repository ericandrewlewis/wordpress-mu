<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     assign
 * Purpose:  assign a value to a template variable
 * -------------------------------------------------------------
 */
function smarty_function_is_aside($params, &$smarty)
{
    extract($params);

    $category = get_the_category();
    //$wpsmarty->assign( "category", $category[0]->cat_name );
    $t = count( $category );
    $ret = false;
    if( $t > 1 )
    {
	for( $i = 0; $i < $t; $i++ )
	{
	    if( $category[$i]->cat_name == 'Asides' )
	    {
		$ret = true;
	    }
	}
    }
    else
    {
	if( $category[0]->cat_name == 'Asides' )
	{
	    $ret = true;
	}
    }

    $smarty->assign( "is_aside", $ret );
}

/* vim: set expandtab: */

?>
