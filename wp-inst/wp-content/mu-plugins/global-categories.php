<?php

function global_categories_filter( $update, $cat_ID, $category_nicename, $cat_name, $rval ) {
    global $wpdb;

    $details = $wpdb->get_row( "SELECT * FROM $wpdb->sitecategories WHERE category_nicename = '$category_nicename'" );
    if( $details == false ) {
	$res = $wpdb->query( "INSERT INTO $wpdb->sitecategories ( cat_ID, cat_name, category_nicename ) VALUES ( NULL, '$cat_name', '$category_nicename' )" );
	$newcat_ID = $wpdb->insert_id;
	$wpdb->query( "UPDATE $wpdb->categories SET cat_ID = '$newcat_ID' WHERE cat_ID = '$cat_ID'" );
	$wpdb->query( "UPDATE $wpdb->post2cat   SET category_id = '$newcat_ID' WHERE category_id = '$cat_ID'" );
	$cat_ID = $newcat_ID;
    } elseif( $details->cat_ID != $cat_ID ) {
	$wpdb->query( "UPDATE $wpdb->categories SET cat_ID = '$details->cat_ID' WHERE cat_ID = '$cat_ID'" );
	$wpdb->query( "UPDATE $wpdb->post2cat   SET category_id = '$details->cat_ID' WHERE category_id = '$cat_ID'" );
	$cat_ID = $details->cat_ID;
    }

    if( $update == false )
	$rval = $cat_ID;

    return array( $update, $cat_ID, $category_nicename, $cat_name, $rval );
}
add_filter('new_category', 'global_categories_filter', 10, 5 );
?>
