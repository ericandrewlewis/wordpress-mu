<?php
require_once('admin.php');

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if( is_site_admin() == false ) {
    die( __('<p>You do not have permission to access this page.</p>') );
}
if (isset($_GET['updated'])) {
	?><div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}
?>
<div class="wrap">
<?php

do_action( "wpmuadminresult", "" );

switch( $_GET[ 'action' ] ) {
    default:
    // print some global stats.
    $stats = get_sitestats();
    print "<h2>Site Stats</h2>
    There are currently ".$stats[ 'blogs' ]." <a href='wpmu-blogs.php'>blogs</a> running on this server and ".$stats[ 'users' ]." <a href='wpmu-users.php'>users</a>.</p><br /><br />
    ";

    print "<table>";
    #$blogs = get_blog_list();
    #print "<br>blogs: <br>";
    #print_r( $blogs );

    $most_active = get_most_active_blogs( 10, false );
    if( is_array( $most_active ) ) {
	print "<caption>Most Active Blogs</caption>";
	print "<tr><th scope='col'>ID</th><th scope='col'>Address</th><th scope='col'>Posts</th></tr>";
	while( list( $key, $details ) = each( $most_active ) ) { 
	    $class = ('alternate' == $class) ? '' : 'alternate';
	    $url = "http://" . $details[ 'domain' ] . $details[ 'path' ];
	    print "<tr class='$class'><td>" . $details[ 'blog_id' ] . "</td><td><a href='$url'>$url</a></td><td>" . $details[ 'postcount' ] . "</td></tr>";
	}
    }
    print "</table>";

    do_action( "wpmuadmindefaultpage", "" );
    break;
}

?>
</div>
<?php include('admin-footer.php'); ?>
