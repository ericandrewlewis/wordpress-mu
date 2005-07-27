<?php
// ** MySQL settings ** //
define('DB_NAME', 'wordpress');     // The name of the database
define('DB_USER', 'username');     // Your MySQL username
define('DB_PASSWORD', 'password'); // ...and password
define('DB_HOST', 'localhost');     // 99% chance you won't need to change this value
define('VHOST', 'VHOSTSETTING'); 


// Change the prefix if you want to have multiple blogs in a single database.
$table_prefix  = 'wp_';   // example: 'wp_' or 'b2' or 'mylogin_'

// Change this to localize WordPress.  A corresponding MO file for the
// chosen language must be installed to wp-includes/languages.
// For example, install de.mo to wp-includes/languages and set WPLANG to 'de'
// to enable German language support.
define ('WPLANG', '');

define( "WP_USE_MULTIPLE_DB", false );
$db_list = array( 
	"write" => array( 
			array(	"db_name"	=> "WRITE_DB_NAME1",
				"db_user"	=> "WRITE_DB_USER1",
				"db_password"	=> "WRITE_DB_PASS1",
				"db_host"	=> "WRITE_DB_HOST1"
			)
		),
	"read" => array(
			array(	"db_name"	=> "READ_DB_NAME1",
				"db_user"	=> "READ_DB_USER1",
				"db_password"	=> "READ_DB_PASS1",
				"db_host"	=> "READ_DB_HOST1"
			),
			array(	"db_name"	=> "READ_DB_NAME2",
				"db_user"	=> "READ_DB_USER2",
				"db_password"	=> "READ_DB_PASS2",
				"db_host"	=> "READ_DB_HOST2"
			),
			array(	"db_name"	=> "READ_DB_NAME3",
				"db_user"	=> "READ_DB_USER3",
				"db_password"	=> "READ_DB_PASS3",
				"db_host"	=> "READ_DB_HOST3"
			),
			array(	"db_name"	=> "READ_DB_NAME4",
				"db_user"	=> "READ_DB_USER4",
				"db_password"	=> "READ_DB_PASS4",
				"db_host"	=> "READ_DB_HOST4"
			),
			array(	"db_name"	=> "READ_DB_NAME5",
				"db_user"	=> "READ_DB_USER5",
				"db_password"	=> "READ_DB_PASS5",
				"db_host"	=> "READ_DB_HOST5"
			),
			array(	"db_name"	=> "READ_DB_NAME6",
				"db_user"	=> "READ_DB_USER6",
				"db_password"	=> "READ_DB_PASS6",
				"db_host"	=> "READ_DB_HOST6"
			)
		)
	);

/* Stop editing */

define('ABSPATH', dirname(__FILE__).'/');
require_once(ABSPATH.'wpmu-settings.php');
?>
