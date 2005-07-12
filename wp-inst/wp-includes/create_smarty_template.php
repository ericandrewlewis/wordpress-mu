#!/usr/bin/php -q
<?php

/* $Id: create_smarty_template.php,v 1.7 2005/03/12 20:17:52 donncha Exp $ */


if (is_dir( "." )) 
{
	if ($dh = opendir( "." )) 
	{
		while (($file = readdir($dh)) !== false ) 
		{
			if( strpos( $file, "template-functions" ) !== false )
			{
				$files[] = $file;
			}
		}
		closedir($dh);
	}
}
$files[] = 'links.php';
$files[] = 'functions.php';
$files[] = 'wp-l10n.php';
$files[] = 'functions-formatting.php';
$files[] = 'functions-post.php';
$files[] = 'functions-user.php';
$files[] = 'feed-functions.php';
$files[] = 'comment-functions.php';


$buffer2 = '';
reset( $files );
while( list( $k, $file ) = each( $files ) )
{
	if( is_file( $file ) )
	{
		$fp = fopen( $file, "r" );
		while (!feof ($fp)) 
		{
			$buffer = fgets($fp, 4096);
			if( strpos( $buffer, "unction " ) == '1' )
			{
				if( strpos( $buffer, '{' ) === false )
				{
					// multi-line function call
					$buffer2 .= $buffer;
					while(!feof( $fp ) && strpos( $buffer, '{' ) === false )
					{
						$buffer = fgets($fp, 4096);
						$buffer2 .= $buffer;
					}
					$line[] = $buffer2;
					$buffer2 = '';
				}
				else
				{
					$line[] = $buffer;
					$buffer2 = '';
				}
			}

		}
		fclose ($fp);
	}
}
print '<'.'?'.'php'."\n".'
if( isset( $wpsmarty ) == false || is_object( $wpsmarty ) == false )
{       
        if( defined( ABSPATH ) == false )
            define( "ABSPATH", "../" );

	require_once( ABSPATH . "Smarty.class.php" );
	$wpsmarty = new Smarty;
}

';

reset( $line );
while( list( $key, $val ) = each( $line ) )
{
	$function = substr( $val, 9, strpos( $val, "(" ) - 9 );
	$bracket1 = strpos( $val, "(" ) + 1;
	$origargs = substr( $val, $bracket1, strpos( $val, ') {' ) - $bracket1 );
	$argslist = split( ",", $origargs );
	$args = '';
	$defineargs = '';
	reset( $argslist );
	while( list( $key, $val ) = each( $argslist ) )
	{
		if( strpos( $val, "=" ) )
		{
			$defineargs .= "    ".trim( $val ).";\n";
			$args .= trim( substr( $val, 0, strpos( $val, "=" ) ) ).", ";
		}
		else
		{
			$args .= $val.", ";
		}
	}
	$args = substr( $args, 0, -2 );
	print "/* $function( $origargs ) */\n";
	if( $function[0] == '&' )
	{
	    print "function &smarty_".substr( $function, 1 )."( \$params, &\$smarty )\n";
	}
	else
	{
	    print "function smarty_".$function."( \$params, &\$smarty )\n";
	}
	print "{\n";
	print "$defineargs\n";
	print "    extract( \$params );\n";
	if( $function[0] == '&' )
	{
	    print "    return ".substr( $function, 1 )."( $args );\n";
	}
	else
	{
	    print "    return $function( $args );\n";
	}
	print "}\n";
	if( $function[0] == '&' )
	{
	    print '$wpsmarty->register_function( "'.substr( $function, 1 ).'", "smarty_'.substr( $function, 1 ).'" );'."\n\n";
	}
	else
	{
	    print '$wpsmarty->register_function( "'.$function.'", "smarty_'.$function.'" );'."\n\n";
	}
}
print '
$wpsmarty->template_dir = ABSPATH."/wp-content/blogs/".$wpblog."/templates";
$wpsmarty->compile_dir  = ABSPATH."/wp-content/blogs/".$wpblog."/templates_c";
$wpsmarty->cache_dir    = ABSPATH."/wp-content/blogs/".$wpblog."/smartycache";
$wpsmarty->plugins_dir  = ABSPATH."/wp-content/smarty-plugins";
$wpsmarty->cache_lifetime = -1;
$wpsmarty->caching = true;
$wpsmarty->security = 1;
$wpsmarty->secure_dir = array( ABSPATH."/wp-content/blogs/".$wpblog."/templates", "wp-content/smarty-templates" );
if( isset( $_GET[ "clear" ] ) )
    $wpsmarty->clear_all_cache();
';
print "?".">";
?>
