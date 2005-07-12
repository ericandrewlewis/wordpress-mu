<?php

/* $Id: function.referer.php,v 1.4 2005/03/11 17:52:56 donncha Exp $ */


// Based on code by Nathan Young @ http://ncyoung.com/entry/57
// Modified by Donncha O Caoimh, donncha@linux.ie


function getreferers( $wpblog, $siteDomain )
{
    global $wpdb;

    /*if( $p || $cat || $m )
    {
        $sql = "select baseDomain,referingURL, visitTimes from referer_visitLog WHERE visitURL = '".$currentURL."' ORDER BY visitTime ASC LIMIT 0 , 10";
    }
    else
    {*/
        $sql = "select referingURL, visitTimes, visitTime from referer_visitLog WHERE blogID='".$wpblog."' and  visitTime >= NOW() - INTERVAL 24 HOUR ORDER BY visitTime DESC";
    //}
    $result = $wpdb->get_results($sql);
    if( $result )
    {
        foreach( $result as $details )
        {
            if( $details->referingURL == 'DIRECT' )
            {
		$details->baseDomain = $details->referingURL;
            }
            else
            {
                $t = preg_replace("/http:\/\//i", "", $details->referingURL );
                $t = preg_replace("/^www\./i", "", $t );
                $t = preg_replace("/\/.*/i", "", $t );
		$details->baseDomain = $t;
            }
            if( $details->baseDomain != 'DIRECT' )
            {
                if( substr( $details->baseDomain, 0, 6 ) == 'google' )
                {
                    $refererlinks[ 'google' ][ 'visitTimes' ] += $details->visitTimes;
                    $refererlinks[ 'google' ][ 'referingURL' ] = "http://www.google.com/";
                    $refererlinks[ 'google' ][ 'baseDomain' ] = "google.com";
                }
                elseif( strpos( $details->baseDomain, "ebsearch.com" ) )
                {
                    $refererlinks[ 'websearch' ][ 'visitTimes' ] += $details->visitTimes;
                    $refererlinks[ 'websearch' ][ 'referingURL' ] = "http://www.websearch.com/";
                    $refererlinks[ 'websearch' ][ 'baseDomain' ] = "websearch.com";
                }
                elseif( strpos( $details->baseDomain, "ahoo.com" ) )
                {
                    $refererlinks[ 'yahoo' ][ 'visitTimes' ] += $details->visitTimes;
                    $refererlinks[ 'yahoo' ][ 'referingURL' ] = "http://www.yahoo.com/";
                    $refererlinks[ 'yahoo' ][ 'baseDomain' ] = "yahoo.com";
                }
                elseif( strpos( $details->baseDomain, "sxml.infospace.com" ) )
                {
                    $refererlinks[ 'yahoo' ][ 'visitTimes' ] += $details->visitTimes;
                    $refererlinks[ 'yahoo' ][ 'referingURL' ] = "http://msxml.infospace.com/";
                    $refererlinks[ 'yahoo' ][ 'baseDomain' ] = "infospace.com";
                }
                elseif( strpos( $details->baseDomain, $siteDomain ) === false )
                {
                    $refererlinks[ $details->referingURL ][ "referingURL" ] = $details->referingURL;
                    $refererlinks[ $details->referingURL ][ "visitTimes" ]  += $details->visitTimes;
                    $refererlinks[ $details->referingURL ][ "baseDomain" ]  = $details->baseDomain;
                }
            }
        }
        if(is_array( $refererlinks ) == false )
            $refererlinks = array();
        reset( $refererlinks );
        while( list( $key, $val ) = each( $refererlinks ) ) 
        { 
            if( $val[ 'visitTimes' ] < 3 )
                unset( $refererlinks[ $key ] );
        }
    }
    else
    {
        $refererlinks = '';
    }
    return $refererlinks;
}

function smarty_function_referer($params, &$smarty)
{
    global $post, $wpdb, $wpblog;


    $ref = getenv('HTTP_REFERER');
    $currentURL = $_SERVER['REQUEST_URI'];
    $fullCurrentURL = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

    $tomorrow  = date( "d", mktime (0,0,0,date("m")  ,date("d")+1,date("Y")) );
    if( $site == 'root' )
    {
        $sql = "delete from referer_visitLog WHERE dayofmonth = '$tomorrow'"; // delete referers from a (month - 1 day) ago.
        $result = $wpdb->query( $query );
    }

    $siteurl = get_settings( "siteurl" );
    $siteDomain = str_replace( "http://", "", $siteurl );
    if( strpos( $siteDomain, "/" ) )
        $siteDomain = substr( $siteDomain, 0, strpos( $siteDomain, "/" ) );

    // find referers for current page

    if( @include_once( "Cache/Function.php" ) )
    {
        $cache = new Cache_Function( 'file', array('cache_dir' => ABSPATH . "/wp-content/smarty-cache", 'filename_prefix' => 'referers_cache_' ), 600 ); 
	$refererlinks = $cache->call( "getreferers", $wpblog, $siteDomain );
    }
    else
    {
	$refererlinks = getreferers( $wpblog, $siteDomain );
    }


    if( is_array( $refererlinks ) )
        reset( $refererlinks );

    $smarty->assign( "refererlinks", $refererlinks );
}

?>
