<?php

require_once('admin.php');
$title = "Referers";
$parent_file = 'edit.php';
include( '../wp-config.php' );
require_once("admin-header.php");

$b2varstoreset = array('action','standalone', "day", "order", "num" );
for ($i=0; $i<count($b2varstoreset); $i += 1) {
	$b2var = $b2varstoreset[$i];
	if (!isset($$b2var)) {
		if (empty($HTTP_POST_VARS["$b2var"])) {
			if (empty($HTTP_GET_VARS["$b2var"])) {
				$$b2var = '';
			} else {
				$$b2var = $HTTP_GET_VARS["$b2var"];
			}
		} else {
			$$b2var = $HTTP_POST_VARS["$b2var"];
		}
	}
}

function makeHiddenVals()
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

        global $$field;
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
            $query = "DELETE FROM " . $table_prefix . "referer_visitLog
                      WHERE       visitID = '".$val."'
                      AND          blogID = '".$wpblog."'";
            $result = $wpdb->query($query);
        }
    }
    $action = "listday";
}
elseif( $action == 'deletedirect' )
{
    $query = "DELETE FROM " . $table_prefix . "referer_visitLog
        WHERE        dayofmonth='".$day."'
        AND          referingURL = 'DIRECT'
        AND          blogID = '".$wpblog."'";
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
                      FROM   " . $table_prefix . "referer_visitLog
                      WHERE  visitID = '".$val."'
                      AND    blogID = '".$wpblog."'";
            $result=$wpdb->get_var( $query );
            if( $result )
            {
                $query = "INSERT INTO " . $table_prefix . "referer_blacklist VALUES( NULL, '".$wpblog."', '".$result."', NOW() )";
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
                $query = "DELETE FROM " . $table_prefix . "referer_blacklist WHERE ID='".$val."' AND blogID = '".$wpblog."'";
                $result = $wpdb->query($query);
        }
    }
    $action = "blacklist";
}

switch( $action )
{
    case "blacklist":
        $query = "SELECT * FROM " . $table_prefix . "referer_blacklist
                  WHERE  blogID = '".$wpblog."'";
        $result = $wpdb->get_results($query, ARRAY_A );
        if( $result )
        {   
            print "<div class='wrap'><h2>Referer Blacklist</h2>";
            print "<form action='referers.php' method='post'>";
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

        $query = "select visitTimes,referingURL,date_format( visitTime, '%k:%i' ) as visitTime2, visitURL, visitID from " . $table_prefix . "referer_visitLog where dayofmonth='".$day."'";
        if( $wpblog != 'root' )
            $query .= " and blogID='".$wpblog."'";

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
            $nav .= "<a href='referers.php?action=month'>Month View</a> | ";
            $nav .= "<a href='referers.php".makeURL( "num", 0 )."'>Top</a>";
            if( $ignoreDIRECT == 'yes' )
            {
                $nav .= " | <a href='referers.php".makeURL( "ignoreDIRECT", 'no' )."'>Display DIRECT requests</a>";
            }
            else
            {
                $nav .= " | <a href='referers.php".makeURL( "ignoreDIRECT", 'yes' )."'>Hide DIRECT requests</a>";
            }
            if( $internal == 'yes' )
            {   
                $nav .= " | <a href='referers.php".makeURL( "internal", 'no' )."'>Display internal requests</a>";
            }
            else
            {   
                $nav .= " | <a href='referers.php".makeURL( "internal", 'yes' )."'>Hide internal requests</a>";
            }

            if( $num >= 10 )
            {
                if( $num > $more )
                {
                    $nav .= " | <a href='referers.php".makeURL( "num", ( $num - $more ) )."'>Previous $more</a>";
                }
            }
            else
            {
                $nav .= " | Previous";
            }
            if( $rows >= $more )
            {
                $nav .= " | <a href='referers.php".makeURL( "num", ($num + $more) )."'>Next $more</a>";
            }
            else
            {
                $nav .= " | Next";
            }
            $nav .= " | <a href='referers.php".makeURL( "more", ($more + 10) )."'>More Hits</a>";
            $nav .= " | <a href='referers.php".makeURL( "more", ($more - 10) )."'>Less Hits</a>";
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
            print "<form action='referers.php' name='deletedirect'>";
            print "<input type='hidden' name='action' value='deletedirect'>";
            print makeHiddenVals();
            print "</form>";
            print "<form action='referers.php' name='referers'>";
            print makeHiddenVals();
            //print "<input type='hidden' name='action' value='delete'>";
            print "<table border=0 cellspacing=0 cellpadding=2>";
            print "<caption> Referers For $day $month</caption>";
            print "<tr><th>#</th><th>Refering URL</th>
                   <th><a href='referers.php".makeURL( "order", "hits" )."' title='order by hits'>Hits</a></th>
                   <th><a href='referers.php".makeURL( "order", "url" )."' title='order by entry page'>Entry Page</a></th>
                   <th><a href='referers.php".makeURL( "order", "time" )."' title='order by time'>Last</a></th>
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
        $query = "select sum( visitTimes ) as c, dayofmonth from " . $table_prefix . "referer_visitLog ";
        if( $wpblog != 'root' )
            $query .= "where blogID='".$wpblog."' ";
        $query .= "group by " . $table_prefix . "referer_visitLog.dayofmonth";
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
                print "'><td><a href='referers.php?action=listday&day=".$row1[ 'dayofmonth' ]."'>".$row1[ 'dayofmonth']."</a></td><td>".$row1[ 'c']."</td></tr>";
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
            print "<br><a href='referers.php?action=blacklist'>View Blacklist</a>";
            print "</div>";
        }
        else
        {
            print "There are no referers for your site! Wait until Google indexes you!";
        }
}
print "</div>";
include("admin-footer.php");
?>
