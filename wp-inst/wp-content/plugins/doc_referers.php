<?php
/*
Plugin Name: Referers
Plugin URI: http://mu.wordpress.org/
Description: Display referers to your site
Version: 0.1
Author: Donncha O Caoimh
Author URI: http://blogs.linux.ie/xeer/
*/

/*  Copyright 2005  Donncha O Caoimh  (email : donncha@linux.ie)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class DOC_Referers {

    var $table_version = 0.1;

    function DOC_Referers() {
	add_action('admin_menu', array(&$this, 'admin_menu'));
	add_action('admin_footer', array(&$this, 'admin_footer'));
	add_action('wp_head', array(&$this, 'wp_head'));
	add_action('template_redirect', array(&$this, 'template_redirect'));
	$this->settings = get_settings('doc_referers');
	$this->wpdb_tables();

	if($this->settings['table_version'] != $this->table_version)
	{
	    $this->make_tables();
	    $this->added_tables = true;
	}
    }

    function admin_footer()
    {
	update_option('doc_referers', $this->settings);
    }

    function admin_menu()
    {
	$pfile = basename(dirname(__FILE__)) . '/' . basename(__FILE__);
	add_submenu_page('edit.php', 'Referers', 'Referers', 0, $pfile, array(&$this, 'plugin_content'));
    }

    function wpdb_tables() {
	global $wpdb, $table_prefix;

	$wpdb->doc_referers  = "{$table_prefix}referer_visitLog";
	$wpdb->doc_blacklist = "{$table_prefix}referer_blacklist";
    }
    function make_tables() {
	global $wpdb, $table_prefix;
	if(!include_once(ABSPATH . 'wp-admin/upgrade-functions.php')) {
	    die(_e('There is was error adding the required tables to the database.  Please refer to the documentation regarding this issue.', 'DOC_Referers'));
	}
	$qry = "CREATE TABLE " . $wpdb->doc_blacklist . " (
		ID int(11) NOT NULL auto_increment,
		blogID varchar(32) NOT NULL default '',
		URL varchar(250) NOT NULL default '',
		t timestamp(14) NOT NULL,
		PRIMARY KEY  (ID),
		KEY blogID (blogID,URL),
		KEY URL (URL)
		);
		CREATE TABLE " . $wpdb->doc_referers . " (
		blogID char( 32 ) default NULL ,
		visitID int( 11 ) NOT NULL AUTO_INCREMENT ,
		visitTime timestamp( 14 ) NOT NULL ,
		visitURL char( 250 ) default NULL ,
		referingURL char( 250 ) default NULL ,
		baseDomain char( 250 ) default NULL ,
		refpost int( 11 ) NOT NULL default '0',
		visitTimes int( 10 ) NOT NULL default '0',
		dayofmonth smallint( 2 ) NOT NULL default '0',
		PRIMARY KEY ( visitID ) ,
		KEY blogID ( blogID ) ,
		KEY refpost ( refpost ) ,
		KEY dayofmonth ( dayofmonth )
		);
	";
	dbDelta($qry);

	$this->settings['table_version'] = $this->table_version;
	update_option('doc_referers', $this->settings);
    }


    function makeHiddenVals( $day, $order, $num, $more, $ignoreDIRECT, $visitID, $internal )
    {
	$fields = array( "day", "order", "num", "more", "ignoreDIRECT", "visitID", "internal" );
	reset( $fields );
	while( list( $key, $field ) = each( $fields ) ) 
	{ 
	    if( $field == 'action' )
	    {
		$sep = '?';
	    }
	    else
	    {
		$sep = '&';
	    }

	    if( $$field != '' )
		$vals .= "<input type='hidden' name='".$field."' value='".$$field."'>\n";
	}
	return $vals;
    }

    function makeURL( $var, $val )
    {
	$fields = array( "action", "day", "order", "num", "more", "ignoreDIRECT", "visitID", "internal" );
	reset( $fields );
	while( list( $key, $field ) = each( $fields ) ) 
	{ 
	    if( $field == 'action' )
	    {
		$sep = '?';
	    }
	    else
	    {
		$sep = '&';
	    }
	    if( $field != $var )
	    {
		global $$field;
		if( $$field != '' )
		    $url .= $sep.$field."=".$$field;
	    }
	    else
	    {
		$url .= $sep.$var."=".$val;
	    }
	}
	return $url;
    }

    function plugin_content()
    {
	global $wpdb;

	$action = $_GET[ 'action' ];
	$day    = $_GET[ 'day' ];
	$del    = $_GET[ 'del' ];
	$num    = $_GET[ 'num' ];
	$more   = $_GET[ 'more' ];
	$ignoreDIRECT = $_GET[ 'ignoreDIRECT' ];
	$internal = $_GET[ 'internal' ];
	
	if( $action == '' )
	{
	    $action = 'listday';
	    $day = date( 'j' );
	}

	print '<div class="wrap">';

	if( $action == 'Delete' )
	{
	    if( is_array( $del ) )
	    {
		reset( $del );
		while( list( $key, $val ) = each( $del ) ) 
		{ 
		    $query = "DELETE FROM " . $wpdb->doc_referers . "
			      WHERE       visitID = '".$val."'";
		    $result = $wpdb->query($query);
		}
	    }
	    $action = "listday";
	}
	elseif( $action == 'deletedirect' )
	{
	    $query = "DELETE FROM " . $wpdb->doc_referers . "
		WHERE        dayofmonth='".$day."'
		AND          referingURL = 'DIRECT'";
	    $result = $wpdb->query($query);
	    printf ("Records deleted: %d\n", $wpdb->rows_affected);
	    $action = "listday";
	}
	elseif( $action == 'Add To Blacklist' )
	{
	    if( is_array( $del ) )
	    {
		reset( $del );
		while( list( $key, $val ) = each( $del ) ) 
		{ 
		    $query = "SELECT referingURL
			FROM   " . $wpdb->doc_referers . "
			WHERE  visitID = '".$val."'";
		    $result=$wpdb->get_var( $query );
		    if( $result )
		    {
			$query = "INSERT INTO " . $wpdb->doc_blacklist . " VALUES( NULL, 0, '".$result."', NOW() )";
			$result = $wpdb->query($query);
		    }
		}
	    }
	    $action = "listday";
	}
	elseif( $action == 'deleteblacklist' )
	{
	    if( is_array( $del ) )
	    {
		reset( $del );
		while( list( $key, $val ) = each( $del ) ) 
		{ 
		    $query = "DELETE FROM " . $wpdb->doc_blacklist . " WHERE ID='".$val."'";
		    $result = $wpdb->query($query);
		}
	    }
	    $action = "blacklist";
	}

	switch( $action )
	{
	    case "blacklist":
		$query = "SELECT * FROM " . $wpdb->doc_blacklist;
	    $result = $wpdb->get_results($query, ARRAY_A );
	    if( $result )
	    {   
		print "<div class='wrap'><h2>Referer Blacklist</h2>";
		print "<form method='get'>";
		print '<input type="hidden" name="page" value="' . $_GET['page'] .'"/>';
		print "<input type='hidden' name='action' value='deleteblacklist'>";
		print "<input type='submit' value='Delete'>";
		print "<table>";
		$c = 1;
		while( list( $key, $row1 ) = each( $result ) ) 
		{
		    if( substr( $row1[ 'URL' ], 0, 16 ) == 'http://www.google' )
		    {
			$displayurl = "Google: ". substr( $row1[ 'URL' ], strpos( $row1[ 'URL' ], "search" )+6 );
		    }
		    elseif( strstr( $row1[ 'URL' ], 'search.yahoo' ) )
		    {
			$displayurl = "Yahoo: ". substr( $row1[ 'URL' ], strpos( $row1[ 'URL' ], "p=" )+2 );
		    }
		    elseif( strpos( $row1[ 'URL' ], 'www.blueyonder.co.uk' ) )
		    {
			$displayurl = "Blueyonder: ". substr( $row1[ 'URL' ], strpos( $row1[ 'URL' ], "q=" )+2 );
		    }
		    else
		    {
			$displayurl = $row1[ 'URL' ];
		    }
		    print "<tr><td>$c</td><td><a href='".$row1[ 'URL' ]."'>".$displayurl."</a></td><td><input type='checkbox' name='del[]' value='".$row1['ID']."'></td></tr>\n";
		    $c++;
		}
		print "</table>";
		print "</form>";
		print "</div>";
	    }
	    else
	    {
		print "No URLs in blacklist yet!";
	    }
	    break;
	    case "listday":

		$query = "select visitTimes,referingURL,date_format( visitTime, '%k:%i' ) as visitTime2, visitURL, visitID from " . $wpdb->doc_referers . " where dayofmonth='".$day."'";

	    if( $internal == 'yes' )
		$query .= " and referingURL NOT LIKE '".get_settings( "siteurl" )."%'";

	    if( $ignoreDIRECT == 'yes' )
		$query .= " and referingURL != 'DIRECT'";

	    if( $order == '' || $order == 'time' )
	    {
		$query .= " order by visitTime desc";
	    }
	    elseif( $order == 'hits' )
	    {
		$query .= " order by visitTimes desc";
	    }
	    elseif( $order == 'url' )
	    {
		$query .= " order by visitURL desc";
	    }
	    if( $num == '' )
	    {
		$num = 0;
	    }
	    if( $more == '' || $more == '0' )
		$more = '30';

	    $query .= " limit $num,$more";

	    $result = $wpdb->get_results($query, ARRAY_A );
	    $rows = $wpdb->num_rows;
	    if( $result )
	    {
		// javascript from http://www.experts-exchange.com/Web/Web_Languages/JavaScript/Q_10105441.html and
		// http://members.aol.com/grassblad/html/chkAllBut.html
		print "<script langage='javascript'>
		    <!--
		    function selectAll(cbList,bSelect) {
			for (var i=0; i<cbList.length; i++)
			    cbList[i].selected = cbList[i].checked = bSelect
		    }

		function reverseAll(cbList) {
		    for (var i=0; i<cbList.length; i++) {
			cbList[i].checked = !(cbList[i].checked)
			    cbList[i].selected = !(cbList[i].selected)
		    }
		}
		//-->
		</script>";
		$c = $num+1;
		$nav = "<br /><div align='center'>";
		$nav .= "<a href='edit.php?page=" . $_GET[ 'page' ] . "&action=month'>Month View</a> | ";
		$nav .= "<a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "num", 0 )."'>Top</a>";
		if( $ignoreDIRECT == 'yes' )
		{
		    $nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "ignoreDIRECT", 'no' )."'>Display DIRECT requests</a>";
		}
		else
		{
		    $nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "ignoreDIRECT", 'yes' )."'>Hide DIRECT requests</a>";
		}
		if( $internal == 'yes' )
		{   
		    $nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "internal", 'no' )."'>Display internal requests</a>";
		}
		else
		{   
		    $nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "internal", 'yes' )."'>Hide internal requests</a>";
		}

		if( $num >= 10 )
		{
		    if( $num > $more )
		    {
			$nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "num", ( $num - $more ) )."'>Previous $more</a>";
		    }
		}
		else
		{
		    $nav .= " | Previous";
		}
		if( $rows >= $more )
		{
		    $nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "num", ($num + $more) )."'>Next $more</a>";
		}
		else
		{
		    $nav .= " | Next";
		}
		$nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "more", ($more + 10) )."'>More Hits</a>";
		$nav .= " | <a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "more", ($more - 10) )."'>Less Hits</a>";
		$nav .= "<br />";
		print "<div class='wrap'><h2>Referers</h2>";
		print $nav;
		$today = date( 'd' );
		if( $day > $today )
		{
		    $month = date( 'F', mktime (0,0,0,date("m")-1,date("d"),  date("Y")) );
		}
		else
		{
		    $month = date( 'F' );
		}
		print "<form method='GET' name='deletedirect'>";
		print '<input type="hidden" name="page" value="' . $_GET['page'] .'"/>';
		print "<input type='hidden' name='action' value='deletedirect'>";
		print $this->makeHiddenVals( $day, $order, $num, $more, $ignoreDIRECT, $visitID, $internal );
		print "</form>";
		print "<form method='GET'  name='referers'>";
		print '<input type="hidden" name="page" value="' . $_GET['page'] .'"/>';
		print $this->makeHiddenVals( $day, $order, $num, $more, $ignoreDIRECT, $visitID, $internal );
		//print "<input type='hidden' name='action' value='delete'>";
		print "<table border=0 cellspacing=0 cellpadding=2>";
		print "<caption> Referers For $day $month</caption>";
		print "<tr><th>#</th><th>Refering URL</th>
		    <th><a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "order", "hits" )."' title='order by hits'>Hits</a></th>
		    <th><a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "order", "url" )."' title='order by entry page'>Entry Page</a></th>
		    <th><a href='edit.php?page=" . $_GET[ 'page' ] . "".$this->makeURL( "order", "time" )."' title='order by time'>Last</a></th>
		    <th>Selected</th>
		    </tr>
		    <tr><td colspan='6' align='right'><INPUT TYPE=button VALUE='Select All' ONCLICK='selectAll(this.form,true)'>
		    <INPUT class='edit' TYPE=button VALUE='Clear All' ONCLICK='selectAll(this.form,false)'>
		    <INPUT class='edit' TYPE=button VALUE='Reverse' ONCLICK='reverseAll(this.form)'>
		    &nbsp;|&nbsp;<input class='edit' type='submit' name='action' value='Delete' onclick='javascript:document.referers.submit()'>
		    &nbsp;|&nbsp;<input class='edit' type='submit' name='action' value='Add To Blacklist'>
		    &nbsp;|&nbsp;<input class='edit' type='button' name='action' value='Delete Direct Referers' onclick='javascript:document.deletedirect.submit()'></td></tr>\n";
		while( list( $key, $row1 ) = each( $result ) ) 
		{
		    if( $col == 'f5f5f5' )
		    {
			$col = 'ffffff';
		    }
		    else
		    {
			$col = 'f5f5f5';
		    }
		    if( $row1[ 'referingURL' ] != 'DIRECT' )
		    {
			if( substr( $row1[ 'referingURL' ], 0, 17 ) == 'http://www.google' )
			{
			    $args = parse_url( $row1[ 'referingURL' ] );
			    parse_str( $args[ 'query' ] );
			    $url = "<a href='".$row1[ 'referingURL' ]."' title='".$row1[ 'referingURL' ]."'>Google: ".stripslashes( htmlspecialchars( $q ) )."</a>";
			}
			elseif( strstr( $row1[ 'referingURL' ], 'search.yahoo' ) )
			{
			    $args = parse_url( $row1[ 'referingURL' ] );
			    parse_str( $args[ 'query' ] );
			    $url = "<a href='".$row1[ 'referingURL' ]."' title='".$row1[ 'referingURL' ]."'>Yahoo: ".stripslashes( htmlspecialchars( $p ) )."</a>";
			}
			elseif( strpos( $row1[ 'referingURL' ], 'www.blueyonder.co.uk' ) )
			{
			    $args = parse_url( $row1[ 'referingURL' ] );
			    parse_str( $args[ 'query' ] );
			    $url = "<a href='".$row1[ 'referingURL' ]."' title='".$row1[ 'referingURL' ]."'>Blueyonder: ".stripslashes( htmlspecialchars( $q ) )."</a>";
			}
			else
			{
			    $url = "<a href='".$row1[ 'referingURL' ]."' title='".$row1[ 'referingURL' ]."'>".substr( $row1[ 'referingURL' ], 0, 40 )."</a>";
			}
		    }
		    else
		    {
			$url = 'DIRECT';
		    }
		    $visitID = $row1[ 'visitID' ];
		    print "<tr bgcolor='#$col'>
			<td>".$c."</td>
			<td>".$url."</td>
			<td>".substr($row1[ 'visitTimes' ],0, 40 )."</td>
			<td><a href='".$row1[ 'visitURL' ]."'>".substr($row1[ 'visitURL' ],0, 40 )."</a></td>
			<td>".$row1[ 'visitTime2' ]."</td>
			<td align='right'><input type=checkbox name='del[]' value='".$visitID."'></td>
			</tr>";
		    $c++;

		}
		print "</table>";
		print $nav;
		print "</form>";
		print "</div>";
	    }
	    break;
	    default:
	    $query = "select sum( visitTimes ) as c, dayofmonth from " . $wpdb->doc_referers . " ";
	    $query .= "group by " . $wpdb->doc_referers . ".dayofmonth";
	    $result = $wpdb->get_results($query, ARRAY_A );
	    if( $result )
	    {
		$c = 0;
		$col = 'ccc';
		print "<div class='wrap'><h2>Referers</h2>";
		print "<table><td valign='top'>";
		print "<table border=1 cellspacing=0 cellpadding=2>";
		print "<tr><th>Day</th><th>Hits</th>";
		while( list( $key, $row1 ) = each( $result ) ) 
		{
		    if( $col == 'f5f5f5' )
		    {
			$col = 'ffffff';
		    }
		    else
		    {
			$col = 'f5f5f5';
		    }
		    print "<tr bgcolor='#";
		    if( $row1[ 'dayofmonth' ] == date( 'j' ) )
		    {
			print "ffdddd";
		    }
		    else
		    {
			print $col;
		    }
		    print "'><td><a href='edit.php?page=" . $_GET[ 'page' ] . "&action=listday&day=".$row1[ 'dayofmonth' ]."'>".$row1[ 'dayofmonth']."</a></td><td>".$row1[ 'c']."</td></tr>";
		    $c++;
		    if( $c == '15' )
		    {
			print "</table>";
			print "</td><td valign='top'>";
			print "<table border=1 cellspacing=0 cellpadding=2>";
			print "<tr><th>Day</th><th>Hits</th>";
		    }

		}
		print "</table>";
		print "</td></table>";
		print "<br><a href='edit.php?page=" . $_GET[ 'page' ] . "&action=blacklist'>View Blacklist</a>";
		print "</div>";
	    }
	    else
	    {
		print "There are no referers for your site! Wait until Google indexes you!";
	    }
	}
	print "</div>";

    }

    function template_redirect() {
	global $wpdb;

	// delete tomorrow's referers today
	$tomorrow  = date( "j", mktime (0,0,0,date("m")  ,date("d")+1,date("Y")) );
	$sec = date( "s" );
	$hour = date( "G" );
	if( $sec == 30 && $hour < 2 )
	{
	    $sql = "delete from " . $wpdb->doc_referers . " WHERE dayofmonth = '$tomorrow'"; // delete referers from a (month + 1 day) ago.
	    $wpdb->query($sql);
	}

	$ref = $_SERVER["HTTP_REFERER"];
	$currentURL = $_SERVER[ 'REQUEST_URI' ];
	$fullCurrentURL = "http://" . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
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

	$ref = $wpdb->escape($ref);
	if( $ref ) {
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

	    $checkRef = true;
	    // Do we need to check the referer? If it's from a known site we can save some cycles.
	    $checkReflist = array( "direct", "http://www.technorati.com", "http://www.google", "http://www.yahoo", "http://www.linux.ie", "http://blogs.linux.ie", "http://blo.gs" );
	    reset( $checkReflist );
	    while( list( $key, $val ) = each( $checkReflist ) ) 
	    { 
		$p = strpos( strtolower( $url ), $val );
		if( $p !== false )
		{
		    $checkRef = false;
		}
	    }

	    $doubleCheckReferers = 0; // must make this an option
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

	    if( $realReferer == true && $ref != 'DIRECT' )
	    {
		$query = "SELECT ID FROM " . $wpdb->doc_blacklist . " WHERE URL like '%$ref%'";
		$result = $wpdb->get_var( $query );
		if( $result )
		{
		    $ref = "DIRECT";
		}
	    }

	    $ua = getenv( 'HTTP_USER_AGENT' );
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

		$sql = "UPDATE " . $wpdb->doc_referers . "
		        SET    visitTimes = visitTimes + 1 
		        WHERE  dayofmonth = '$today'
			AND    referingURL = '$ref'
			AND    visitURL   = '$currentURL'";
		$result = $wpdb->query( $sql );
		if( $result == false )
		{
		    $sql ="insert delayed into " . $wpdb->doc_referers . " (referingURL,visitURL,refpost, visitTimes, dayofmonth)
			   values ('$ref','$currentURL','$p','1', '$today')";
		    $result = $wpdb->query( $sql );
		}
	    }
	}
    }
}

$doc_referer = new DOC_Referers();

?>
