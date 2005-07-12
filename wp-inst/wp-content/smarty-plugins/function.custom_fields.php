<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.custom_fields.php
 * Type:     function
 * Name:     custom_fields
 * Purpose:  Passes the custom field value to the named function
 * -------------------------------------------------------------
 */
function smarty_function_custom_fields($params, &$smarty)
{
    global $wpblog, $siteurl;

    extract( $params );
    $fields = get_post_custom();
    if( is_array( $fields ) )
    {
        while( list( $func, $custom_params ) = each( $fields ) ) 
        {
            $func = str_replace('../', '', $func);
            $file = $smarty->plugins_dir . "/custom_fields." . $func . ".php";
            if( is_file( $file ) )
            {
                include_once( $file );
                $func( $smarty, $params, $custom_params );
            }
        }
    }
}
?>
