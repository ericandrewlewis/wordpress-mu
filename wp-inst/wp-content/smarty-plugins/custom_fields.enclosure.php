<?php

/* $Id: custom_fields.enclosure.php,v 1.2 2004/10/14 14:15:50 donncha Exp $ */


function enclosure( &$smarty, $params, $custom_params )
{
    global $siteurl, $wpblog;

    while( list( $key, $val ) = each( $custom_params ) ) 
    { 
        $enclosure[] = split( "\n", $val );
    }
    $smarty->assign( "enclosure", $enclosure );
}

?>
