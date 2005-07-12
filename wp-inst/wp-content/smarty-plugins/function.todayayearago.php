<?php

/* $Id: function.todayayearago.php,v 1.2 2005/01/09 11:54:18 donncha Exp $ */

/*
   Smarty plugin
   -------------------------------------------------------------
   File:     function.todayayearago.php
   Type:     function
   Name:     todayayearago
   Purpose:  Print links to posts a year ago.
   -------------------------------------------------------------

   COPYRIGHT Indigo Anand Meridian @ http://indiboi.com/
   Modified for Smarty plugin API by Donncha O Caoimh

   $Id: function.todayayearago.php,v 1.2 2005/01/09 11:54:18 donncha Exp $
*/

function todayayearago( $when, $wpblog, $spacer ='<br /' )
{
    global $tableposts,$row,$blogfilename,$querystring_start,$querystring_equal,$time_difference, $siteurl, $wpdb;

    if( $spacer == '' )
        $spacer = '<br />';

    if( $when == 'month' )
    {
        if( date('m') == '01' )
        {
            $year = (date('Y', time()+($time_difference * 3600)) - 1);
        }
        else
        {
            $year = (date('Y', time()+($time_difference * 3600)));
        }
        $month = date('m', time()+($time_difference * 3600)) - 1;
    }
    else
    {
        $year = (date('Y', time()+($time_difference * 3600))) - 1;
        $month = date('m', time()+($time_difference * 3600));
    }

    $day = date("d", (time()+($time_difference * 3600)));

    $reqhistory = "SELECT post_date, ID, post_title, SUBSTRING(post_content, 1, 100) as post_content 
                   FROM   $tableposts 
                   WHERE  YEAR(post_date)       = $year 
                   AND    MONTH(post_date)      = $month 
                   AND    DAYOFMONTH(post_date) = $day
		   AND    post_status = 'publish'";
    $reqhistory = $wpdb->get_results($reqhistory);
    if( $reqhistory )
    {
        foreach( $reqhistory as $row )
        {
            $todayayearago[ $row->ID ] = array( "title" => strip_tags( stripslashes($row->post_title) ),
                                                      "content" => strip_tags( stripslashes( $row->post_content ) ) );
        }
    }
    return $todayayearago;
}

function smarty_function_todayayearago($params, &$smarty)
{
    global $wpblog, $tableposts,$row,$blogfilename,$querystring_start,$querystring_equal,$time_difference, $siteurl, $wpdb;

    extract( $params );

    if( @include_once( "Cache/Function.php" ) )
    {
        $cache = new Cache_Function( 'file', array('cache_dir' => ABSPATH . "/wp-content/smarty-cache", 'filename_prefix' => 'todayayearago_cache_' ), 3600 ); 
	$todayayearago = $cache->call( "todayayearago", $when, $wpblog, $spacer );
    }
    else
    {
	$todayayearago = todayayearago( $when, $wpblog, $spacer );
    }


    $smarty->assign( "todayayearago", $todayayearago );
}
?>
