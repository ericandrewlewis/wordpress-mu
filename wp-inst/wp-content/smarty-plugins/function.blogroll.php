<?php

/* $Id: function.blogroll.php,v 1.2 2004/11/30 00:50:56 donncha Exp $ */

// Modified version of PHP blogroll by Phil Ringnalda @ http://philringnalda.com/phpblogroll/
// Modified by Donncha O Caoimh, donncha@linux.ie

$GLOBALS['blogroll_open_tags' ] = array(
        'WEBLOGUPDATES' => '<WEBLOGUPDATES>',
        'WEBLOG' => '<WEBLOG>');

$GLOBALS['blogroll_close_tags' ] = array(
        'WEBLOGUPDATES' => '</WEBLOGUPDATES>');

// declare the character set - UTF-8 is the default
$GLOBALS['blogroll_type' ] = 'ISO-8859-1';


function smarty_function_blogroll($params, &$smarty)
{
    global $blogroll_open_tags, $blogroll_close_tags, $blogroll_type;
    global $b2sitePath, $site, $blogteststring;
    global $wpblog;

    $origCache = $smarty->cache_dir;
    $smarty->cache_dir = ABSPATH . "/wp-content/smarty-cache";

    extract( $params );
    if( $blogID == '' )
    {
        $smarty->display( ABSPATH . "/wp-content/smarty-templates/noblogroll.tpl" );
    }
    else
    {
        $blogroll_xml_source = 'http://blo.gs/'.$blogID.'/favorites.xml';
        //$blogroll_xml_source = '/tmp/favorites.xml';
        $blogroll_xml_test = $blogteststring;

        $smarty->caching = 2;
        $smarty->cache_lifetime = 7200; // cache is updated at most once every hour.
        if( $smarty->template_exists( "blogroll.tpl" ) == false )
        {
            copy( ABSPATH . "/wp-content/smarty-templates/blogroll.tpl", ABSPATH . "/wp-content/blogs/".$wpblog."/templates/blogroll.tpl" );
            $origDir = $smarty->template_dir;
            $smarty->template_dir = ABSPATH . "/templates";
        }

        if( $smarty->is_cached( "blogroll.tpl", $wpblog ) == false )
        {
            $smarty->clear_cache( 'blogroll.tpl', $wpblog );
            $blogroll_remote_fp = @fopen($blogroll_xml_source,"r");
            $blogroll_remote_data = '';
            if( $blogroll_remote_fp )
            {
                while (!feof ($blogroll_remote_fp)) 
                {
                    $blogroll_remote_data .= fgets($blogroll_remote_fp, 4096);
                }
                fclose($blogroll_remote_fp);
                if (stristr($blogroll_remote_data, $blogroll_xml_test))
                {
                    // create our parser
                    $blogroll_xml_parser = xml_parser_create($blogroll_type);

                    // set some parser options 
                    xml_parser_set_option($blogroll_xml_parser, XML_OPTION_CASE_FOLDING, true);
                    xml_parser_set_option($blogroll_xml_parser, XML_OPTION_TARGET_ENCODING, $blogroll_type);

                    // this tells PHP what functions to call when it finds an element
                    // these funcitons also handle the element's attributes
                    xml_set_element_handler($blogroll_xml_parser, 'blogrollStartElement','blogrollEndElement');

                    if (!xml_parse($blogroll_xml_parser, $blogroll_remote_data)) {
                        die(sprintf( "XML error: %s at line %d\n\n",
                                    xml_error_string(xml_get_error_code($blogroll_xml_parser)),
                                    xml_get_current_line_number($blogroll_xml_parser)));
                    }

                    xml_parser_free($blogroll_xml_parser);
                }

            }
            else
            {
                $smarty->assign( "gendate", date("n/d g:ia") );
                $links[] = array( 'url' => '', 'name' => 'Not Available' );
                $smarty->assign( "links", $links );
            }
        }
        $smarty->display( "blogroll.tpl", $wpblog );
    }
    $smarty->cache_dir = $origCache;
    if( $origDir != '' )
        $smarty->template_dir = $origDir;
}

function blogrollStartElement($parser, $name, $attrs=''){
    global $blogroll_open_tags, $blogroll_temp, $blogroll_current_tag, $blogroll_weblog_index;
    $blogroll_current_tag = $name;
    if ($format = $blogroll_open_tags[$name]){
        switch($name){
            case 'WEBLOGUPDATES':
                //starting to parse
                $blogroll_weblog_index = -1;
                break;
            case 'WEBLOG':
                //indivdual blog
                $blogroll_weblog_index++;
                $blogroll_temp[$blogroll_weblog_index]['name'] = htmlentities(addslashes((strlen($attrs['NAME']) > 19) ? substr($attrs['NAME'], 0, 17) . "..." : $attrs['NAME']));
                $blogroll_temp[$blogroll_weblog_index]['url'] = $attrs['URL'];
                break;    
            default:
                break;
        }
    }
}

function blogrollEndElement($parser, $name, $attrs=''){
    global $blogroll_close_tags, $blogroll_temp, $blogroll_current_tag;
    if ($format = $blogroll_close_tags[$name])
    {
        switch($name){
            case 'WEBLOGUPDATES':
                blogrollWriteLinks();
                break;
            default:
                break;
        }
    }
}


function blogrollWriteLinks()
{
    global $blogroll_temp, $wpsmarty;
    $wpsmarty->assign( "gendate", date("n/d g:ia") );
    reset( $blogroll_temp );
    while( list( $key, $val ) = each( $blogroll_temp ) ) 
    { 
        $links[ $key ] = array( 'url' => $val[ 'url' ], 'name' => stripslashes( $val[ 'name' ] ) );
    } 
    $wpsmarty->assign( "links", $links );
}

?>
