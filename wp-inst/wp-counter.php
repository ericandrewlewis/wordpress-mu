<?php

/* $Id: wp-counter.php,v 1.5 2005/03/11 14:46:30 donncha Exp $ */

require_once('wp-blog-header.php');

function doCheckRef( $url )
{
    $check = true;
    // Do we need to check the referer? If it's from a known site we can save some cycles.
    $checklist = array( "direct", "http://www.technorati.com", "http://www.google", "http://www.yahoo", "http://www.linux.ie", "http://blogs.linux.ie", "http://blo.gs" );
    reset( $checklist );
    while( list( $key, $val ) = each( $checklist ) ) 
    { 
        $p = strpos( strtolower( $url ), $val );
        if( $p !== false )
        {
            $check = false;
        }
    }
    return $check;
}

function getreferer()
{
    global $p, $m, $cat, $site, $wpdb, $wpblog, $referer, $loc;

    $ref = $referer;

    // delete tomorrow's referers today
    $tomorrow  = date( "j", mktime (0,0,0,date("m")  ,date("d")+1,date("Y")) );
    $sec = date( "s" );
    $hour = date( "G" );
    if( $sec == 30 && $hour < 2 )
    {
        $sql = "delete from referer_visitLog WHERE dayofmonth = '$tomorrow'"; // delete referers from a (month + 1 day) ago.
        $wpdb->query($sql);
    }

    $ua = getenv( 'HTTP_USER_AGENT' );
    $currentURL = str_replace( "http://".$_SERVER[ 'SERVER_NAME' ], '', $loc );
    $fullCurrentURL = $loc;
    if( $ref == '' )
    {
        $ref = "DIRECT";
    }

    $found = false;

    if( $currentURL[ strlen( $currentURL ) -1 ] == '/' )
    {
        $found = true;
    }
    else
    {
        $count_files = array( "wp-admin" );
        reset( $count_files );
        while( list( $key, $val ) = each( $count_files ) ) 
        { 
            $pos = strpos( $currentURL, $val );
            if( $pos == true )
            {
                $found = true;
            }
        }
        if( $found == true )
        {
            // Don't bother going further - no need to record request!
            return;
        }
    }

    if ($ref || $ref = strip_tags($ref) )
    {

        $realReferer = true;
        $ignorePages = Array( 'lastupdated.php', 'b2rdf.php', 'b2rss2.php', 'b2bookmarklet.php', 'b2referers.php', 'b2commentspopup.php' );
        foreach ($ignorePages as $ignoresite){
            if (stristr($currentURL, $ignoresite)){
                $realReferer = false;
            }
        }

        $ignore = Array(
                'http://www.myelin.co.nz/ecosystem/bot.php',
                'http://radio.xmlstoragesystem.com/rcsPublic/',
                'http://blogdex.media.mit.edu//',
                'http://subhonker6.userland.com/rcsPublic/',
                'mastadonte.com',
                'http://blo.gs/ping.php'
                );
        foreach ($ignore as $ignoresite){
            if (stristr($ref, $ignoresite)){
                $realReferer = false;
            }
        }

        $doubleCheckReferers = 0;

        $checkRef = doCheckRef( $ref );

        if( $realReferer && $checkRef && $ref != 'DIRECT' && $doubleCheckReferers)
        {
            //this is so that the page up until the call to
            //logReferer will get shown before it tries to check
            //back against the refering URL.
            flush();

            $goodReferer = 0;
            $fp = @fopen ($ref, "r");
            if ($fp){
                socket_set_timeout($fp, 5);
		$c = 0;
                while (!feof ($fp) || $c > 5) {
                    $page .= trim(fgets($fp, 4096));
		    $c++;
                }
		fclose( $fp );
                if (strstr($page,$fullCurrentURL)){
                    $goodReferer = 1;
                }
            }

            if(!$goodReferer){
                $realReferer = false;
            }
        }

	if( $realReferer == true )
	{
	    $query = "SELECT ID FROM referer_blacklist WHERE URL like '%$ref%'";
	    $result = $wpdb->get_var( $query );
	    if( $result )
	    {
		$ref = "DIRECT";
	    }
	}

        $useragents = array( "http://www.syndic8.com", "http://dir.com/pompos.html", "NaverBot-1.0", "http://help.yahoo.com/help/us/ysearch/slurp", "http://www.google.com/bot.html", "http://www.blogdigger.com/", "http://search.msn.com/msnbot.htm", "Feedster, LLC.", "http://www.breakingblogs.com/timbo_bot.html", "fastbuzz.com", "http://www.pubsub.com/", "http://www.bloglines.com", "http://www.drupal.org/", "Ask Jeeves/Teoma", "ia_archiver", "http://minutillo.com/steve/feedonfeeds/", "larbin_2", "lmspider", "kinjabot", "lickBot 2.0", "Downes/Referrers", "daypopbot", "www.globalspec.com" );
        reset( $useragents );
        while( list( $key, $val ) = each( $useragents ) ) 
        { 
            if( strpos( $ua, $val ) !== false )
            {
                $realReferer = false;
            }
        }

        if( $realReferer )
        {
            if( $ref == 'DIRECT' )
            {
                $anchor = $ref;
            }
            else
            {
                $anchor = preg_replace("/http:\/\//i", "", $ref);
                $anchor = preg_replace("/^www\./i", "", $anchor);
                $anchor = preg_replace("/\/.*/i", "", $anchor);
            }
            $today = date( "d" );

            $sql = "update referer_visitLog
                set    visitTimes = visitTimes + 1 
                where  blogID = '$wpblog' 
                AND    dayofmonth = '$today'
                AND    referingURL = '$ref'
                AND    visitURL   = '$currentURL'";
            $result = $wpdb->query( $sql );
            if( $result == false )
            {
                $sql ="insert delayed into referer_visitLog (blogID,referingURL,visitURL,refpost, visitTimes, dayofmonth)
                       values ('$wpblog','$ref','$currentURL','$p','1', '$today')";
                $result = $wpdb->query( $sql );
            }
        }
    }
}

if( $_GET[ 'page' ] )
{
    if( $referer == '' )
        if( $HTTP_GET_VARS[ 'referer' ] != '' )
        {
            $referer = $HTTP_GET_VARS[ 'referer' ];
        }
    getreferer();
    header( "Content-type: IMAGE/PNG" );
    readfile( get_settings( 'siteurl' ) . "/wp-images/wpminilogo.png" );
}
else
{
    ?>
    <!--
    if (document["referrer"] != null) 
    {
        ref = document.referrer;
    }
    else
    {
        ref = 'DIRECT';
    }
    counterDate = new Date();
    seconds = counterDate.getSeconds();
    document.write("<a  href='<?php print get_settings( 'siteurl' ) ?>/'><img width=1 height=1 src='<?php print get_settings( 'siteurl' ) ?>/wp-counter.php?page=img&loc="+document.location+"&referer="+escape(ref)+"&t="+seconds+"' border='0' title='Stats'></a>");
    // -->
    <?php
}
?>
