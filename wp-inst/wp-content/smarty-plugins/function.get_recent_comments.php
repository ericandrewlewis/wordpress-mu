<?php

/* $Id: function.get_recent_comments.php,v 1.1 2005/03/11 16:05:26 donncha Exp $ */

/*
   Smarty plugin
   -------------------------------------------------------------
   File:     function.get_hashcash_special_code.hp
   Type:     function
   Name:     get_hashcash_special_code
   Purpose:  print hascash code.
   -------------------------------------------------------------

   $Id: function.get_recent_comments.php,v 1.1 2005/03/11 16:05:26 donncha Exp $
*/

function smarty_function_get_recent_comments($params, &$smarty)
{
    $no_comments = 5;
    $comment_lenth = 5;
    extract( $params );

    return get_recent_comments( $no_comments, $comment_lenth );
}
?>
