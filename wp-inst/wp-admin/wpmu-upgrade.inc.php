<?php

$row = $wpdb->get_row( "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = 'first_post'" );
if( $row == false )
	$wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, '$wpdb->siteid', 'first_post', 'Welcome to <a href=\"SITE_URL\">SITE_NAME</a>. This is your first post. Edit or delete it, then start blogging!'" );

$row = $wpdb->get_row( "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = 'welcome_email'" );
if( $row == false )
	$wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, '$wpdb->siteid', 'welcome_email', 
'Dear User,

Your new SITE_NAME blog has been successfully set up at:
BLOG_URL

You can log in to the administrator account with the following information:
Username: USERNAME
Password: PASSWORD
Login Here: BLOG_URLwp-login.php

We hope you enjoy your new weblog.
Thanks!

--The WordPress Team
SITE_NAME'" );

$row = $wpdb->get_row( "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = 'site_name'" );
if( $row == false )
	$wpdb->query( "INSERT INTO ".$wpdb->sitemeta." (meta_id, site_id, meta_key, meta_value) VALUES (NULL, '$wpdb->siteid', 'site_name', '" . ucfirst( $current_site->domain ) . "')";

unset( $row );

?>
