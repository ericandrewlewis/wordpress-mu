<?php

/* $Id: custom_fields.photoblog.php,v 1.3 2004/11/10 10:27:29 donncha Exp $ */


function photoblog( &$smarty, $params, $custom_params )
{
    global $siteurl, $wpblog;

    $smarty->assign( "photoblog", $siteurl."/images/photoblog-".$custom_params[0].".jpg" );

}

?>
