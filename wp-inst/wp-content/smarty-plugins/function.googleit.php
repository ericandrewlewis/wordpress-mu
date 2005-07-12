<?php

/* $Id: function.googleit.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $ */

/*
   Smarty plugin
   -------------------------------------------------------------
   File:     function.googleit.php
   Type:     function
   Name:     googleit
   Purpose:  Return google search for string.
   -------------------------------------------------------------

   COPYRIGHT Donncha O Caoimh.

   $Id: function.googleit.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $
*/

function smarty_function_googleit($params, &$smarty)
{
    extract( $params );
    if( $search != '' )
    {
        $search1 = str_replace( " ", "+", $search );
        echo '<a href="http://www.google.com/search?q=' . strip_tags( $search1 ).'" title="Search for '. strip_tags( $search ).'">';
        if( $link != '' )
        {
            echo $link;
        }
        else
        {
            echo "Search";
        }
        echo '</a>';
    }
    
}
?>
