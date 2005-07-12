<?php
require_once('admin.php');

$title = __('WPMU Admin');
$parent_file = 'wpmu-admin.php';
require_once('admin-header.php');
if( $wpblog != 'main' || $user_level < 10) {
    die( __('<p>You do not have permission to access this page.</p>') );
}
if (isset($_GET['updated'])) {
    ?><div class="updated"><p><strong><?php _e('Options saved.') ?></strong></p></div><?php
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

    do_action( "wpmuadmindefaultpage", "" );
    break;
}

?>
</div>
<?php include('admin-footer.php'); ?>
