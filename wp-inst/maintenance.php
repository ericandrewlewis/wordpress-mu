<?php
/*
Call this file in a secure manner. You could set up a cron job to do it:
0 2,7,12,16,20 * * * (/usr/bin/lynx --dump http://localhost/maintenance.php) 2> /dev/null

You don't want others deleting your cache!
*/
die( 'You must delete line 2 of '.$_SERVER[ 'PHP_SELF' ].' for it to work!' ); // delete this line when you've secured this file.

if( $_SERVER[ 'REMOTE_ADDR' ] == '127.0.0.1' )
{
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*0" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*1" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*2" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*3" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*4" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*5" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*6" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*7" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*8" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*9" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*a" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*b" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*c" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*d" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*e" );
    exec( "rm -f ./wp-content/smarty-cache/function_cache/*f" );
}
?>
