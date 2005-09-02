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
print '<div class="wrap">';
switch( $_GET[ 'action' ] ) {
	case "upgrade":
		if( isset( $_GET[ 'n' ] ) == false ) {
			$n = 0;
		} else {
			$n = $_GET[ 'n' ];
		}
		$blogs = $wpdb->get_results( "SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = '$wpdb->siteid' ORDER BY registered DESC LIMIT $n, 10", ARRAY_A );
		if( is_array( $blogs ) ) {
			foreach( $blogs as $details ) {
				$siteurl = $wpdb->get_var( "SELECT option_value from {$wpmuBaseTablePrefix}{$details[ 'blog_id' ]}_options WHERE option_name = 'siteurl'" );
				print "$siteurl<br>";
				$fp = fopen( $siteurl . "wp-admin/upgrade.php?step=1", "r" );
				if( $fp ) {
					while( feof( $fp ) == false ) {
						fgets($fp, 4096);
					}
					fclose( $fp );
				}
			}
			?>
			<p>If your browser doesn't start loading the next page automatically click this link: <a href="?action=upgrade&n=<?php echo ($n + 10) ?>">Next Blogs</a> </p>
			<script language='javascript'>
			<!--

			function nextpage() {
				location.href="wpmu-upgrade-site.php?action=upgrade&n=<?php echo ($n + 10) ?>";
			}
			setTimeout( "nextpage()", 250 );

			//-->
			</script>
			<?php
		} else {
			print "All Done!";
		}
	break;
	default:
	?>
		<p>You can upgrade all the blogs on your site through this page. It works by calling the upgrade script of each blog automatically. Hit the link below to upgrade.</p>
		<p><a href="wpmu-upgrade-site.php?action=upgrade">Upgrade Site</a></p>
	<?php
	break;

}
?>
</div>
<?php include('admin-footer.php'); ?>
