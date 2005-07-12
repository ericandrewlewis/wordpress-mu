<?php

/* $Id: function.getcustomfield.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $ */

// custom fields hack by Mystis @ http://www.mystis.net/b2customfields.html
// commentary @ http://tidakada.com/board/viewtopic.php?t=2693
// Highly modified by Donncha O Caoimh, donncha@linux.ie

function smarty_function_getcustomfield($params, &$smarty)
{
    global $tableposts,$id, $site;

    extract( $params );

    $sql = "SELECT field_contents 
            FROM   b2customfieldsContents 
            WHERE  postID = '".$id."'
            AND    blog   = '".$site."'
            AND    field_name = '".$name."'";
    $result = mysql_query($sql);
    if( @mysql_num_rows( $result ) )
    {
        $row = mysql_fetch_array($result);
        $output = $row[ 'field_contents' ];
        $output = convert_bbcode(convert_smilies(stripslashes($output)));
        if( strstr( $output, ";;;" ) )
        {
            $output = split(";;;", $output );
        }
    }
    else
    {
        $output = '';
    }

    $smarty->assign( "customfield", $output );
}

?>
