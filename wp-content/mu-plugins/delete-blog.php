<?php

class delete_blog {

	function delete_blog() {
		$this->reallydeleteblog = false;
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_footer', array(&$this, 'admin_footer'));
	}

	function admin_footer() {
		global $wpdb;

		if( $this->reallydeleteblog == true ) {
			wpmu_delete_blog( $wpdb->blogid ); 
		}
	}

	function admin_menu() {
		add_submenu_page('options-general.php', __('Delete Blog'), __('Delete Blog'), 'manage_options', 'delete-blog', array(&$this, 'plugin_content'));
	}

	function plugin_content() {
		global $wpdb, $current_blog, $current_site;
		$this->delete_blog_hash = get_settings('delete_blog_hash');
		echo '<div class="wrap"><h2>' . __('Delete Blog') . '</h2>';
		if( $_POST[ 'action' ] == "deleteblog" && $_POST[ 'confirmdelete' ] == '1' ) {
			$hash = substr( md5( $_SERVER[ 'REQUEST_URI' ] . time() ), 0, 6 );
			update_option( "delete_blog_hash", $hash );
			$url_delete = get_option( "siteurl" ) . "/wp-admin/options-general.php?page=delete-blog&h=" . $hash;
			$msg = __("Dear User,
You recently clicked the 'Delete Blog' link on your blog and filled in a 
form on that page.
If you really want to delete your blog, click the link below. You will not
be asked to confirm again so only click this link if you are 100% certain:
URL_DELETE

If you delete your blog, please consider opening a new blog here
some time in the future! (But remember your current blog and username 
are gone forever.)

Thanks for using the site,
Webmaster
SITE_NAME
");
			$msg = str_replace( "URL_DELETE", $url_delete, $msg );
			$msg = str_replace( "SITE_NAME", $current_site->site_name, $msg );
			wp_mail( get_option( "admin_email" ), "[ " . get_option( "blogname" ) . " ] ".__("Delete My Blog"), $msg );
			?>
			<p><?php _e('Thank you. Please check your email for a link to confirm your action. Your blog will not be deleted until this link is clicked.') ?></p>
			<?php
		} elseif( isset( $_GET[ 'h' ] ) && $_GET[ 'h' ] != '' && get_option('delete_blog_hash') != false ) {
			if( get_option('delete_blog_hash') == $_GET[ 'h' ] ) {
				$this->reallydeleteblog = true;
				echo "<p>" . sprintf(__('Thank you for using %s, your blog has been deleted. Happy trails to you until we meet again.'), $current_site->site_name) . "</p>";
			} else {
				$this->reallydeleteblog = false;
				echo "<p>" . __("I'm sorry, the link you clicked is stale. Please select another option.") . "</p>";
			}
		} else {
?>
			<p><?php printf(__('If you do not want to use your %s blog any more, you can delete it using the form below. When you click <strong>Delete My Blog</strong> you will be sent an email with a link in it. Click on this link to delete your blog.'), $current_site->site_name); ?></p>
			<p><?php _e('Remember, once deleted your blog cannot be restored.') ?></p>
			<form method='post' name='deletedirect'>
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
			<input type='hidden' name='action' value='deleteblog' />
			<p><input id='confirmdelete' type='checkbox' name='confirmdelete' value='1' /> <label for='confirmdelete'><strong><?php printf( __("I'm sure I want to permanently disable my blog, and I am aware I can never get it back or use %s again."), $current_blog->domain); ?></strong></label></p>
			<p class="submit"><input type='submit' value='<?php _e('Delete My Blog Permanently &raquo;') ?>' /></p>
			</form>
<?php
		}
		echo "</div>";
	}
}

$delete_blog_obj = new delete_blog();

?>