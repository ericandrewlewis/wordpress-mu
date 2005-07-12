<?php

/* $Id: insert.getreferer.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $ */


// Based on code by Nathan Young @ http://ncyoung.com/entry/57
// Modified by Donncha O Caoimh, donncha@linux.ie


// TODO - see if I really need "visitID" in the table.


function err( $err )
{
    error_log( "$err\n", 3, "/tmp/err.txt" );
}

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
        /*
        {
            mail( "donncha@tradesignals.com", "check1: $val $url", "" );
            return true;
        }
        else
            */
        {
            $check = false;
        }
    }
    /*
    if( $check )
    {
        err( "check3: *$url*" );
    }
    else
    {
        err( "ok3: *$url*" );
    }
    */
    return $check;
}

function smarty_insert_getreferer()
{
    global $siteurl, $p, $m, $cat, $site, $wpdb, $wpblog;


    // delete tomorrow's referers today
    $tomorrow  = date( "j", mktime (0,0,0,date("m")  ,date("d")+1,date("Y")) );
    $sec = date( "s" );
    $hour = date( "G" );
    if( $sec == 30 && $hour < 2 )
    {
        $sql = "delete from referer_visitLog WHERE dayofmonth = '$tomorrow'"; // delete referers from a (month + 1 day) ago.
        $wpdb->query($sql);
    }

    $ref = getenv('HTTP_REFERER');
    $ua = getenv( 'HTTP_USER_AGENT' );
    $currentURL = $_SERVER['REQUEST_URI'];
    $fullCurrentURL = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
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
                'http://blo.gs/ping.php',
                $siteurl
                );
        foreach ($ignore as $ignoresite){
            if (stristr($ref, $ignoresite)){
                $realReferer = false;
            }
        }

        $doubleCheckReferers = 1;

        $checkRef = doCheckRef( $ref );

        if( $realReferer && $checkRef && $ref != 'DIRECT' && $doubleCheckReferers)
        {
            //this is so that the page up until the call to
            //logReferer will get shown before it tries to check
            //back against the refering URL.
            flush();
            //err( "checking $ref" );

            $goodReferer = 0;
            $fp = @fopen ($ref, "r");
            if ($fp){
                socket_set_timeout($fp, 5);
                while (!feof ($fp)) {
                    $page .= trim(fgets($fp));
                }
                if (strstr($page,$fullCurrentURL)){
                    $goodReferer = 1;
                }
            }

            if(!$goodReferer){
                $realReferer = false;
            }
            if( $realReferer == true )
            {
                $query = "SELECT ID FROM referer_blacklist WHERE URL like '%$ref%'";
                //error_log( "$query\n", 3, "/tmp/queries.txt" );
                #mail( "donncha@tradesignals.com", "query", $query );
                $result = $wpdb->get_row( $query );
                if( $result )
                {
                    $ref = "DIRECT";
                }
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
                AND    baseDomain = '$anchor'
                AND    visitURL   = '$currentURL'";
            $result = $wpdb->query( $sql );
            if( $result == false )
            {
                $sql ="insert delayed into referer_visitLog (blogID,referingURL,baseDomain,visitURL,refpost, visitTimes, dayofmonth)
                       values ('$wpblog','$ref','$anchor','$currentURL','$p','1', '$today')";

                $result = $wpdb->query( $sql );
            }
        }
    }
}

?>
