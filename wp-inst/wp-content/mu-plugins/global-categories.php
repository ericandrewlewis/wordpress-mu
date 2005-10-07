<?php

function global_categories( $cat_ID ) {
    global $wpdb;

    $details = $wpdb->get_row( "SELECT * FROM $wpdb->categories WHERE cat_ID = '$cat_ID'" );
    if( $details == false ) { // this should *not* happen ever!
	    return $cat_ID;
    }
    $global_cat = $wpdb->get_row( "SELECT * FROM $wpdb->sitecategories WHERE cat_name = '{$details->cat_name}'" );
    if( $global_cat == false ) {
	$res = $wpdb->query( "INSERT INTO $wpdb->sitecategories ( cat_ID, cat_name, category_nicename ) VALUES ( NULL, '{$details->cat_name}', '{$details->category_nicename}' )" );
	$newcat_ID = $wpdb->insert_id;
	$wpdb->query( "UPDATE $wpdb->categories SET cat_ID = '$newcat_ID' WHERE cat_ID = '$cat_ID'" );
	$wpdb->query( "UPDATE $wpdb->post2cat   SET category_id = '$newcat_ID' WHERE category_id = '$cat_ID'" );
	$cat_ID = $newcat_ID;
	if( get_option( "default_category" ) == $cat_ID )
		update_option( "default_category", $newcat_ID );
    } elseif( $global_cat->cat_ID != $cat_ID ) {
	$wpdb->query( "UPDATE $wpdb->categories SET cat_ID = '{$global_cat->cat_ID}' WHERE cat_ID = '$cat_ID'" );
	$wpdb->query( "UPDATE $wpdb->post2cat   SET category_id = '{$global_cat->cat_ID}' WHERE category_id = '$cat_ID'" );
	if( get_option( "default_category" ) == $cat_ID )
		update_option( "default_category", $global_cat->cat_ID );
	$cat_ID = $global_cat->cat_ID;
    }

    return $cat_ID;
}
add_action( 'edit_category', 'global_categories' );
add_action( 'add_category', 'global_categories' );
?>
