<?php

function wp_login($username, $password, $already_md5 = false) {
	global $wpdb, $error;

	if ( !$username )
		return false;

	if ( !$password ) {
		$error = __('<strong>Error</strong>: The password field is empty.');
		return false;
	}

	$login = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $wpdb->users, $wpdb->usermeta WHERE " . $wpdb->users . ".ID =  " . $wpdb->usermeta . ".user_id AND meta_key = '" . $wpdb->prefix. "user_level' AND user_login = '$username'");

	if (!$login) {
	    $admins = get_admin_users_for_domain();
	    reset( $admins );
	    while( list( $key, $val ) = each( $admins ) ) 
	    { 
		if( $val[ 'user_login' ] == $username ) {
		    unset( $login );
		    $login->user_login = $username;
		    $login->user_pass  = $val[ 'user_pass' ];
		}
	    }
	}
	if (!$login) {
		$error = __('<strong>Error</strong>: Wrong username.');
		return false;
	} else {
		// If the password is already_md5, it has been double hashed.
		// Otherwise, it is plain text.
		if ( ($already_md5 && $login->user_login == $username && md5($login->user_pass) == $password) || ($login->user_login == $username && $login->user_pass == md5($password)) ) {
			return true;
		} else {
			$error = __('<strong>Error</strong>: Incorrect password.');
			$pwd = '';
			return false;
		}
	}
}

function get_userdata( $user_id ) {
	global $wpdb, $cache_userdata;
	$user_id = (int) $user_id;
	if ( $user_id == 0 )
		return false;

	if ( isset( $cache_userdata[$user_id] ) ) 
		return $cache_userdata[$user_id];

	if ( !$user = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = '$user_id'") )
		return $cache_userdata[$user_id] = false;

	$metavalues = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = '$user_id'");

	foreach ( $metavalues as $meta ) {
		@ $value = unserialize($meta->meta_value);
		if ($value === FALSE)
			$value = $meta->meta_value;
		$user->{$meta->meta_key} = $value;

		// We need to set user_level from meta, not row
		if ( $wpdb->prefix . 'user_level' == $meta->meta_key )
			$user->user_level = $meta->meta_value;
	}
	if( is_site_admin( $user_id ) == true ) {
	    $user->user_level = 10;
	    $cap_key = $wpdb->prefix . 'capabilities';
	    $user->{$cap_key} = array( 'administrator' => '1' );
	}

	$cache_userdata[$user_id] = $user;
	$cache_userdata[$cache_userdata[$user_id]->user_login] =& $cache_userdata[$user_id];

	return $cache_userdata[$user_id];
}

?>
