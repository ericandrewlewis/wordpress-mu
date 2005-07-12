<?php

/* $Id: wp-config-sample.php,v 1.2 2005/01/13 23:33:38 donncha Exp $ */

// ** MySQL settings ** //
define('DB_NAME', 'wordpress');     // The name of the database
define('DB_USER', 'username');     // Your MySQL username
define('DB_PASSWORD', 'password'); // ...and password
define('DB_HOST', 'localhost');     // 99% chance you won't need to change this value
define('VHOST', 'VHOSTSETTING'); 


$table_prefix  = 'wp_';   // example: 'wp_' or 'b2' or 'mylogin_'

/* Stop editing */

define('ABSPATH', dirname(__FILE__).'/');
require_once(ABSPATH.'wpmu-settings.php');
?>
