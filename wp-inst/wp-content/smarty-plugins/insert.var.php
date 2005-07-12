<?php

function smarty_insert_var( $params, $smarty )
{
    global $REMOTE_ADDR;

    extract( $params );

    if( $REMOTE_ADDR == '217.75.13.250' )
        error_log( "$var: ".$$var."\n", 3, "/tmp/err.txt" );
    if( $var )
    {
        global $$var;
        return $$var;
    }
    else
    {
        return "";
    }
}
?>
