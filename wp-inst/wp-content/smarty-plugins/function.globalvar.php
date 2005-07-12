<?php

/* $Id: function.globalvar.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $ */


/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     globalvar
 * Purpose:  Make a Smarty variable global in PHP land.
 * -------------------------------------------------------------
 */
function smarty_function_globalvar($params, &$smarty)
{
    extract($params);

    if (empty($var)) {
        $smarty->trigger_error("globalvar: missing 'var' parameter");
        return;
    }

    if (!in_array('value', array_keys($params))) {
        $smarty->trigger_error("globalvar: missing 'value' parameter");
        return;
    }
    
    global $$var;
    $$var = $value;
}

/* vim: set expandtab: */

?>
