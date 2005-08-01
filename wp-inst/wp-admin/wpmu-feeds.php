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
print '<div class="wrap">';
switch( $_GET[ 'action' ] ) {
	default: 
	break;
}

$customizefeed1 = get_site_option( 'customizefeed1' );
$customizefeed2 = get_site_option( 'customizefeed2' );
$dashboardfeed1 = get_site_option( 'dashboardfeed1' );
$dashboardfeed2 = get_site_option( 'dashboardfeed2' );
$dashboardfeed1name = get_site_option( 'dashboardfeed1name' );
$dashboardfeed2name = get_site_option( 'dashboardfeed2name' );

?>
<h2>Dashboard Feeds</h2>
<p>The dashboard displays two feeds. You can allow your users to customize those feeds to set them to a feed of your own.</p>
<form action='wpmu-edit.php?action=updatefeeds' method='POST'>
<table>
<tr><td valign='top'>Feed 1</td><td><ul><li> User customizable: <input type='radio' name='customizefeed1' value='1'<?php echo $customizefeed1 == 1 ? ' checked' : ''?>> Yes <input type='radio' name='customizefeed1' value='0'<?php echo $customizefeed1 == 0 ? ' checked' : ''?>> No</li>
			<li> Title: <input type='text' name='dashboardfeed1name' size='40' value='<?php echo $dashboardfeed1name ?>'></li>
			<li> Default Feed URL: <input type='text' name='dashboardfeed1' size='40' value='<?php echo $dashboardfeed1 ?>'></li></ul></td></tr>
<tr><td valign='top'>Feed 2</td><td><ul><li> User customizable: <input type='radio' name='customizefeed2' value='1'<?php echo $customizefeed2 == 1 ? ' checked' : ''?>> Yes <input type='radio' name='customizefeed2' value='0'<?php echo $customizefeed2 == 0 ? ' checked' : ''?>> No</li>
			<li> Title: <input type='text' name='dashboardfeed2name' size='40' value='<?php echo $dashboardfeed2name ?>'></li>
			<li> Default Feed URL: <input type='text' name='dashboardfeed2' size='40' value='<?php echo $dashboardfeed2 ?>'></li></ul></td></tr>

</table>
<input type='submit' value='Update Feeds'>
</form>

</div>
<?php include('admin-footer.php'); ?>
