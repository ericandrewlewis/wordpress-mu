<?php
require_once('admin.php');

do_action( "wpmuadminedit", "" );

switch( $_GET[ 'action' ] ) {
    case "updateblog":
    $options_table_name = $wpmuBaseTablePrefix . $_POST[ 'id' ] ."_options";

    // themes
    if( is_array( $_POST[ 'theme' ] ) ) {
	$allowed_themes = $_POST[ 'theme' ];
	$_POST[ 'option' ][ 'allowed_themes' ] = $_POST[ 'theme' ];
    }
    if( is_array( $_POST[ 'option' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
	    if ( is_array($val) || is_object($val) )
		$val = serialize($val);

	    $query = "SELECT option_id, option_value
	              FROM   ".$options_table_name."
		      WHERE  option_name  = '".$key."'";
	    $opts = $wpdb->get_row( $query, ARRAY_A );
	    $optvalue = $opts[ 'option_value' ];
	    $option_id = $opts[ 'option_id' ];
	    if( $opts == false ) {
		$query = "INSERT INTO ".$options_table_name." ( `option_id` , `blog_id` , `option_name` , `option_can_override` , `option_type` , `option_value` , `option_width` , `option_height` , `option_description` , `option_admin_level` , `autoload` )
		          VALUES ( NULL, '0', '".$key."', 'Y', '1', '".$val."', '20', '8', '', '1', 'yes')";
	        $wpdb->query( $query );
	    } elseif( $optvalue != $val ) {
		$query = "UPDATE ".$options_table_name."
    	                  SET    option_value = '".$val."'
		          WHERE  option_name  = '".$key."'";
	        $wpdb->query( $query );
	    }
	}
    }

    // update blogs table
    if( $_POST[ 'blog' ][ 'domain' ] != $current_site->domain ) {
	$query = "UPDATE ".$wpdb->blogs."
                  SET    domain       = '".$_POST[ 'blog' ][ 'domain' ]."',
	                 path         = '".$_POST[ 'blog' ][ 'path' ]."',
	                 registered   = '".$_POST[ 'blog' ][ 'registered' ]."',
		         last_updated = '".$_POST[ 'blog' ][ 'last_updated' ]."',
		         is_public    = '".$_POST[ 'blog' ][ 'is_public' ]."'
	          WHERE  blog_id = '".$_POST[ 'id' ]."'";
        $wpdb->query( $query );
    }
    header( "Location: wpmu-blogs.php?action=editblog&id=".$_POST[ 'id' ]."&updated=true" );
    break;
    case "deleteblog":
	$query = "UPDATE ".$wpdb->blogs."
	          SET    is_public = 'archived'
	          WHERE  blog_id = '".$_GET[ 'id' ]."'";
        $wpdb->query( $query );
    break;
    case "updateuser":
    unset( $_POST[ 'option' ][ 'ID' ] );
    if( is_array( $_POST[ 'option' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'option' ] ) ) { 
    	$query = "UPDATE ".$wpdb->users."
    	          SET    ".$key." = '".$val."'
    	          WHERE  ID  = '".$_POST[ 'id' ]."'";
    	$wpdb->query( $query );
        }
    }
    if( is_array( $_POST[ 'meta' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'meta' ] ) ) { 
    	$query = "UPDATE ".$wpdb->usermeta."
    	          SET    meta_key = '".$_POST[ 'metaname' ][ $key ]."',
    		         meta_value = '".$val."'
    	          WHERE  umeta_id  = '".$key."'";
    	$wpdb->query( $query );
        }
    }
    if( is_array( $_POST[ 'metadelete' ] ) ) {
        while( list( $key, $val ) = each( $_POST[ 'metadelete' ] ) ) { 
	    $query = "DELETE FROM ".$wpdb->usermeta."
	              WHERE  umeta_id  = '".$key."'";
	    $wpdb->query( $query );
        }
    }
    header( "Location: wpmu-users.php?action=edit&id=".$_POST[ 'id' ]."&updated=true" );
    break;
    case "updatethemes":
    if( is_array( $_POST[ 'theme' ] ) ) {
	$themes = array_flip( array_keys( get_themes() ) );
	reset( $themes );
	while( list( $key, $val ) = each( $themes ) ) 
	{
	    if( $_POST[ 'theme' ][ addslashes( $key ) ] == 'enabled' )
		$allowed_themes[ $key ] = true;
	}
	update_site_settings( 'allowed_themes', $allowed_themes );
    }
    header( "Location: wpmu-blogs.php?updated=true" );
    break;
    default:
    header( "Location: wpmu-admin.php" );
    break;
}
?>
