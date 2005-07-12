<?php
/*
 Plugin Name: WPMU Administration Console
 Plugin URI: http://derek.ditch.name/
 Description: Use this plugin to install new users, remove existing, and configure WPMU. <strong>Warning!</strong> This plugin is not ready for general use. It may lead to serious problems on your site if enabled!
 Author: Derek Ditch
 Author URI: http://derek.ditch.name
 Version: 0.1
 */
 
 /* 	THINGS TO DO:
 	-  Add option to backup database and/or templates into tarball or similar
 	-  Add smarty code to insert aggregate of hosted blogs on main blog page
 
 		BUGS
 	-  NONE so far!  Please send me your bug reports!  <derek AT ditch DOT name>
 */

if( ! function_exists('wpmu_admin_run') ){
 	function wpmu_admin_run() {
 		add_options_page( __('WPMU Administration'), __('WPMU'), 10, ABSPATH.'wp-content/plugins/wpmu-plugin.php');
 	}
 }
 
 if ( is_plugin_page() ){
 
	function setPasswd($login){
 		global $table_prefix;
 		 		
		// Connect to the db
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		mysql_select_db(DB_NAME);
		
 		$random_password = substr(md5(uniqid(microtime())), 0, 6);
 		mysql_query("UPDATE {$table_prefix}users SET user_pass=MD5('$random_password') WHERE ID='1'");
 		
 		return $random_password;
 	}
 	
 	function emailNewUser($password){
 		global $email, $login, $base;
 		
 		$from = 'From: WPMU Admin <wordpress@'.$_SERVER['HTTP_HOST'].'>';
		$message_headers = "$from";
		
		mail($email, '
		New WordPress Blog', "Your new WordPress blog has been successfully set up at:
		
		http://{$_SERVER['HTTP_HOST']}{$base}{$login}/
		
		You can log in to your account with the following information:
		
		Username: $login
		Password: $password
		
		We hope you enjoy your new weblog. Thanks!
		
		--The WordPress Team
		http://wordpress.org/
		", $message_headers);
 	}
 	
 	function emailNewPass($password, $email, $login){
 		
 		$from = 'From: WPMU Admin <wordpress@'.$_SERVER['HTTP_HOST'].'>';
		$message_headers = "$from";
		
		mail($email, 'New WordPress Password', "
		Your WordPress has been reset.  If you do not think this should be happening, 
		please contact your site admin for further info.:
		
		You can log in to your account with the following information:
		
		Username: $login
		Password: $password
		
		Happy blogging. Thanks!
		
		--The WordPress Team
		http://wordpress.org/
		", $message_headers);
		
 	}
 	 	
 	function &getUserInfo($blog){
 		
 		global $wpmuBaseTablePrefix, $wpdb;
 				
		return $wpdb->get_row("SELECT CONCAT(user_lastname, ', ', user_firstname) as name, user_email, user_url FROM {$wpmuBaseTablePrefix}{$blog}_users WHERE ID='1' "); 		
 	}
 
	 function getSchema(){
	 	global $login;
	 	
		 // Load the schema
		$queries = file(ABSPATH.'wp-admin/upgrade-schema.php');
		$queries = str_replace('$wpdb->', $login . '_', $queries);
		$queries = implode('', $queries);
		preg_match('/\"(.*?)\"/s', $queries, $matches);
		
		$queries = $matches[1];

		// Taken from upgrade-functions.php	
		// Seperate individual queries into an array
		if( !is_array($queries) ) {
			$queries = explode( ';', $queries );
			if('' == $queries[count($queries) - 1]) array_pop($queries);
		}
		
		$cqueries = array(); // Creation Queries
		$iqueries = array(); // Insertion Queries
		$for_update = array();
		// Create a tablename index for an array ($cqueries) of queries

		foreach($queries as $qry) {
			if(preg_match("|CREATE TABLE ([^ ]*)|", $qry, $matches)) {
				$cqueries[strtolower(str_replace('PREFIX', '', $matches[1]))] = $qry;
				$for_update[$matches[1]] = 'Created table '.$matches[1];
			}
			else if(preg_match("|CREATE DATABASE ([^ ]*)|", $qry, $matches)) {
				array_unshift($cqueries, $qry);
			}
			else if(preg_match("|INSERT INTO ([^ ]*)|", $qry, $matches)) {
				$iqueries[] = $qry;
			}
			else if(preg_match("|UPDATE ([^ ]*)|", $qry, $matches)) {
				$iqueries[] = $qry;
			}
			else {
				// Unrecognized query type
			}
		}
		
		return array($cqueries, $iqueries, $for_update);
	 }
	
	// Expects array of queries ready to be executed.
	function installdb($queries){
	
		// Connect to the db
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		mysql_select_db(DB_NAME);

		// Process each query.
		foreach($queries as $query){
			mysql_query($query);
		}
		
		mysql_close($link);
	}
	
	function deletedb($login){
		
		global $table_prefix;
		
		$delete_tables = array();
		
		// Connect to the db
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		
		$tables = mysql_list_tables(DB_NAME);
		for ($i = 0; $i < mysql_num_rows($tables); $i++) {
			if ((substr($tablename = mysql_tablename($tables,$i),0,strlen($table_prefix))==$table_prefix)) {
				$delete_tables[] = $tablename;
			}
		}
		
		mysql_free_result($tables);
		
		foreach($delete_tables as $tablename){
			mysql_query("DROP TABLE $tablename");
		}
	}
	
	function dboptions(){
		global $login, $base, $firstname, $lastname, $email, $table_prefix;
		 $name = '';
		 $value = '';
		 $description = '';
		 
		 $qry = "INSERT INTO {$table_prefix}options (option_name, option_value, option_description) VALUES ('{&$name}', '{&$value}', '{&$description}')";				 
		 
		 // Load the schema		
		$queries = file(ABSPATH.'wp-admin/upgrade-schema.php');
		$queries = str_replace('$wpdb->', $login . '_', $queries);
		$queries = implode('', $queries);
		
		// Connect to the db
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		mysql_select_db(DB_NAME);
		
		$result = mysql_query("SELECT * FROM derek_users");
		
		// Catch all add_option usage
		preg_match_all('/add_option\((.*?)\)\;/', $queries, $matches);
		foreach($matches[1] as $match){
		
			list($name, $value, $description) = explode(',', $match);
			$name = trim($name, " '");
			$value = trim($value, " '");
			$description = trim($description, " '");
			$qry = "INSERT INTO PREFIXoptions (option_name, option_value, option_description) VALUES ('$name', '$value', '$description')";
			$qry = str_replace('PREFIX', addslashes($login) . '_', $qry);
			
			// Shouldn't be any result set since these are all INSERT queries.
			mysql_query($qry);
		} // foreach
		 
		// Need to update fileupload_realpath, fileupload_url, siteurl
		// These values are not parsed correctly from schema because they utilize variables
		$guessurl = 'http://' . $_SERVER['HTTP_HOST'] . $base . $login . '/';
		mysql_query("UPDATE ". addslashes($login) ."_options SET option_value='" . ABSPATH . "wp-content/blogs/$login/images/' WHERE option_name='fileupload_realpath'");
		mysql_query("UPDATE ". addslashes($login) ."_options SET option_value='{$guessurl}wp-content/blogs/$login/images/' WHERE option_name='fileupload_url'");
		mysql_query("UPDATE ". addslashes($login) ."_options SET option_value='$guessurl', option_description='WordPress web address' WHERE option_name='siteurl'");
		mysql_query("UPDATE ". addslashes($login) ."_options SET option_value='a:0:{}' WHERE option_name='active_plugins'");
		
		// Need to add user information (login, pass, real name, e-mail, homepage) to db.
		mysql_query("INSERT INTO {$login}_users (ID, user_login, user_nickname, user_email, user_level, user_idmode, user_firstname, user_lastname, user_url) VALUES ( '1', '$login', 'Administrator', '$email', '10', 'nickname', '$firstname', '$lastname', '$guessurl')");
		
		// Set up a few options not to load by default
		$fatoptions = array( 'moderation_keys', 'recently_edited' );
		foreach ($fatoptions as $fatoption) :
			mysql_query("UPDATE {$login}_options SET `autoload` = 'no' WHERE option_name = '$fatoption'");
		endforeach;
		
		// Need to add some default information
		$now = date('Y-m-d H:i:s');
		$now_gmt = gmdate('Y-m-d H:i:s');
		mysql_query("INSERT INTO ". addslashes($login) . "_categories (cat_ID, cat_name) VALUES ('0', 'Uncategorized')");
		mysql_query("INSERT INTO ". addslashes($login) . "_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_modified, post_modified_gmt) VALUES ('1', '$now', '$now_gmt', 'Welcome to WordPress. This is your first post. Edit or delete it, then start blogging!', 'Hello world!', '0', '$now', '$now_gmt')");
		mysql_query("INSERT INTO ". addslashes($login) . "_post2cat (`rel_id`, `post_id`, `category_id`) VALUES (1, 1, 1)");
		mysql_query("INSERT INTO ". addslashes($login) . "_comments (comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content) VALUES ('1', 'Mr WordPress', 'mr@wordpress.org', 'http://wordpress.org', '127.0.0.1', '$now', '$now_gmt', 'Hi, this is a comment.<br />To delete a comment, just log in, and view the posts\' comments, there you will have the option to edit or delete them.')");
		mysql_query("INSERT INTO ". addslashes($login) . "_linkcategories (cat_id, cat_name) VALUES (1, 'Blogroll')");
		
		// Links
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://photomatt.net/', 'Matt', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://blog.carthik.net/index.php', 'Carthik', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://zengun.org/weblog/', 'Michel', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://zed1.com/journalized/', 'Mike', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://dougal.gunters.org/', 'Dougal', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://blogs.linux.ie/xeer/', 'Donncha', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://derek.ditch.name', 'Derek', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://www.alexking.org/', 'Alex', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://mu.wordpress.org', 'WPMU', 1);");
		mysql_query("INSERT INTO ". addslashes($login) . "_links (link_url, link_name, link_category) VALUES ('http://wordpress.org', 'Wordpress', 1);");
		
		
		mysql_close($link);
	}
	
	// Taken from  Aidan Lister <aidan@php.net> http://aidan.dotgeek.org/lib/?file=function.copyr.php
	function copyr($source, $dest){
	
		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}
	 
		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}
	 
		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
	 
			// Deep copy directories
			if ($dest !== "$source/$entry") {
				copyr("$source/$entry", "$dest/$entry");
			}
		}
	 
		// Clean up
		$dir->close();
		return true;
	}
	
	// Taken from  Aidan Lister <aidan@php.net> http://aidan.dotgeek.org/lib/?file=function.rmdirr.php
	function rmdirr($dirname)
	{
		// Sanity check
		if (!file_exists($dirname)) {
			return false;
		}
	 
		// Simple delete for a file
		if (is_file($dirname)) {
			return unlink($dirname);
		}
	 
		// Loop through the folder
		$dir = dir($dirname);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
	 
			// Recurse
			rmdirr("$dirname/$entry");
		}
	 
		// Clean up
		$dir->close();
		return rmdir($dirname);
	}

	function setup($login){
		global $base;
		list($cqueries, $iqueries, $updates) = getSchema();
	
		foreach($cqueries as $key => $qry){
			$cqueries[$key] = str_replace('PREFIX', $install_prefix, $qry);
		}
		
		// Create the blog dir
		mkdir( ABSPATH . 'wp-content/blogs/' . $login, 0777);
		mkdir( ABSPATH . 'wp-content/blogs/' . $login . '/images', 0777);
		mkdir( ABSPATH . 'wp-content/blogs/' . $login . '/templates', 0777);
		mkdir( ABSPATH . 'wp-content/blogs/' . $login . '/templates_c', 0777);
		mkdir( ABSPATH . 'wp-content/blogs/' . $login . '/smartycache', 0777);
		
		copyr(ABSPATH . 'wp-content/sitetemplates/humancondition/templates' , ABSPATH . 'wp-content/blogs/' . $login . '/templates');

		// Modify template files with correct paths
		$cssfile = file_get_contents(ABSPATH . 'wp-content/blogs/' . $login . '/templates/wp-layout.css');
		$cssfile = str_replace('BASE', $base, $cssfile);
		
		$fh = fopen(ABSPATH . 'wp-content/blogs/' . $login . '/templates/wp-layout.css', w);
		fwrite($fh, $cssfile);
		fclose($fh);
						
		// Get the DB pristine
		installdb($cqueries);
		dboptions();
		$password = setPasswd($login);
		
		// Notify him/her that his/her blog is ready
		emailNewUser($password);
	}	


	/* ############  PAGE ENTRY STARTS HERE ##############*/
	
	// Set all vars from POST or GET
	$wpvarstoreset = array('action', 'login', 'firstname', 'lastname', 'email');
	for($i=0; $i<count($wpvarstoreset); $i += 1){
		$wpvar = $wpvarstoreset[$i];
		if(! isset($$wpvar)){
			if( empty( $_POST["$wpvar"])){
				if(empty($_GET["$wpvar"])){
					$$wpvar = '';
				} else {
					$$wpvar = $_GET["$wpvar"];
				} // GET
			} else {
				$$wpvar = $_POST["$wpvar"];
			} // POST
		}
	} // for
	
	if( 'main' != $wpblog ){
		// Give error message.  This blog isn't authorized to modify the whole site.
		print('<div class="wrap"><p align="center"><font color="red">Error: </font> This site is not authorized to access this page.  Please contact your site administrator if you need assistance.</p></div>');
		include('admin-footer.php');
		exit;
	}
	
	/*
	switch($action){
		
		case 'options':
			// Update the disk space allowed
			wpmu_adminOptionSet('wpmu_space_allocated',$option['wpmu_space_allocated']);
					
			do_action('wpmu_options_admin_set',array('option'=>&$option));
			
		break;
		
		case 'add':
			// Perform some simple input validation.
			if( $login == ''){
				die(__('<div class="wrap"><p align="center"><font color=red>Error: </font> Please enter a login.</p></div>'));
			} elseif ( $email == ''  || ! preg_match('/^.+@.+\..{2,4}$/', $email) ){
				die(__('<div class="wrap"><p align="center"><font color=red>Error: </font> Please enter a valid e-mail address.</p></div>'));
			}
			
			$current_users = wpmu_getBlogs();
			foreach($current_users as $user){
				if($login == $user)
					die(__('<div class="wrap"><p align="center"><font color=red>Error: </font> Username already exists.  Please select a new unique value</p></div>'));
			}
			
			setup($login);
			
			break;
			
		case 'delete':
			if( $login == ''){
				die(__('<div class="wrap"><p align="center"><font color=red>Error: </font> Something didn\'t work correctly.  Please use the delete button.</p></div>'));
			}
			
			// Pure irreversible destruction.  Do not try this at home, we are professionals
			deletedb($login);
			rmdirr(ABSPATH . '/wp-content/blogs/' . $login);
			break;
			
		case 'resetpw':
			if( $login == ''){
				die(__('<div class="wrap"><p align="center"><font color=red>Error: </font> Something didn\'t work correctly.  Please use the reset password button.</p></div>'));
			}
			
			// Get user info
			list($name, $email, $URI) = getUserInfo($login);
			
			emailNewPass(setPasswd($login), $email, $login);
			break;
			
		default:
			break;
	}
	*/
	
	$dirs = wpmu_getBlogs();
	$spaceAllowed = wpmu_adminOption_get("wpmu_space_allocated", (1024 * 1024));
	
	?>
	<!-- commented out html because plugin has not be sufficiently tested -->
	<div class="wrap">
	<p>The WPMU admin plugin is temporarily disabled.</p>
	</div>
	<!--
	<div class="wrap">
		<h2><?php _e('Blogs') ?></h2>
		<table cellpadding="3" cellspacing="3" width="100%">
			<tr>
				<th><?php _e('Name') ?></th>
				<th><?php _e('E-mail') ?></th>
				<th><?php _e('Website') ?></th>
				<th><?php _e('Login') ?></th>
				<th><?php _e('Password') ?></th>
				<th>&nbsp;</th>
			</tr>
			<tr>
			</tr>
			<?php foreach($dirs as $blog): 
				$thisBlog =& getUserInfo($blog);
			?>
			<tr class="alternate">
				<td><?php echo $thisBlog->name; ?></td>
				<td><?php echo $thisBlog->user_email; ?></td>
				<td><a href="<?php echo $thisBlog->user_url; ?>"><?php echo $thisBlog->user_url; ?></a></td>
				<td style="text-align:center;"><?php echo $blog; ?></td>
				<td><?php if('main' !== $blog): ?><a href="<?php echo get_settings('siteurl'); ?>/wp-admin/admin.php?page=wpmu-plugin.php&action=resetpw&login=<?php echo $blog; ?>" class="delete" onclick="return confirm('Are you sure you wish to reset this password? This cannot be undone.')">Reset</a><?php endif; ?></td>
				<td><?php if('main' !== $blog): ?><a href="<?php echo get_settings('siteurl'); ?>/wp-admin/admin.php?page=wpmu-plugin.php&action=delete&login=<?php echo $blog; ?>" class="delete" onclick="return confirm('Are you sure you wish to delete this site? This cannot be undone and all data will be lost.')">Delete</a><?php endif; ?></td>
			<tr>
			<?php endforeach; ?>

		</table>
	</div>
	
	<div class="wrap">
		<h2>
		<?php 
			_e('Options');
			$options = array(
					0=>array(
						'caption'=>'Disk Space (bytes)',
						'name'=>'wpmu_space_allocated',
						'value'=>$spaceAllowed,
						'type'=>'text'
						),
/*					1=>array(
						'caption'=>'Plugins Enabled',
						'name'=>'all_user_plugins',
						'value'=>'none',
						'type'=>'text'
						)
*/				);
			do_action('wpmu_options_admin_get', array('options'=>&$options));
		?>
		</h2>
		<form name="form1" method="post" action="<?php echo get_settings('siteurl'); ?>/wp-admin/admin.php?page=wpmu-plugin.php">
			<table class="editform" width="100%" cellspacing="2" cellpadding="5">
			<tr>
					<input name="action" type="hidden" id="action" value="options" />
			</tr>
			<?php
				foreach ($options as $thisOption) {
					switch ($thisOption['type']) {
						case 'text':
							?>
								<tr>
									<th scope="row" width="33%"><?php _e($thisOption['caption']) ?></th>
									<td width="66%"><input name="option[<?php echo $thisOption['name']; ?>]" type="<?php echo $thisOption['type']; ?>" id="option[<?php echo $thisOption['name']; ?>]" value="<?php echo $thisOption['value']; ?>"/></td>
								</tr>					
							<?php
						break;
						
						case 'checkbox':
							if (is_array($thisOption['value'])) {
							?>
								<tr>
									<td align="right"><b><?php _e('Plugins') ?></b></td>
									<td></td>
								</tr>
								<tr>
								<td></td>
								<td>
									<table class="editform" width="100%">
									<tr>
									<td width="33%"><b><?php _e('Plugin Name');?></b></td><td width="66%"><b><?php _e('Active');?></b></td>
									</tr>
										<?php
										foreach ($thisOption['value'] as $filename => $thisItem) {
										?>
										<tr>
										<td><?php echo $thisItem['Name'] ?></td>
										<td align="center"><input name="option[<?php echo $thisOption['name']; ?>][<?php echo $filename; ?>]" type="checkbox" id="option[<?php echo $thisOption['name']; ?>][<?php echo $thisOption['name']; ?>]" value="1" <?php checked('1', $thisItem['enabled']) ?>  /></td>
										</tr>
										<?php
										}
									?>
									</table>
								</td>
								</tr>					
							<?php
							} else {
									?>
									<tr>
										<th scope="row" width="33%"><?php _e($thisOption['caption']) ?></th>
										<td width="66%"><input name="option[<?php echo $thisOption['name']; ?>]" type="<?php echo $thisOption['type']; ?>" id="option[<?php echo $thisOption['name']; ?>]" value="1"/></td>
									</tr>					
									<?php
							}
						break;
					}
				}
			?>
			</table>
			<p class="submit">
    			<input name="options" type="submit" id="options" value="<?php _e('Update') ?> &raquo;" />
 			</p>
 		</form>
	</div>
	-->
	
	<?php /*
	<!--
	
	<div class="wrap">
		<h2><?php _e('Add New Blog') ?></h2>
		<form name="form1" method="post" action="<?php echo get_settings('siteurl'); ?>/wp-admin/admin.php?page=wpmu-plugin.php">
			<table class="editform" width="100%" cellspacing="2" cellpadding="5">
				<tr>
					<th scope="row" width="33%"><?php _e('Nickname') ?>
					<input name="action" type="hidden" id="action" value="add" /></th>
					<td width="66%"><input name="login" type="text" id="user_login" /></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('First Name') ?> </th>
					<td><input name="firstname" type="text" id="firstname" /></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Last Name') ?> </th>
					<td><input name="lastname" type="text" id="lastname" /></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('E-mail') ?></th>
					<td><input name="email" type="text" id="email" /></td>
				</tr>
			</table>
			<p class="submit">
    			<input name="add" type="submit" id="add" value="<?php _e('Add') ?> &raquo;" />
 			</p>
 		</form>
	</div>
	-->
	*/
	?>
	
	
	<?php
  
  // if (is_plugin_page())
 } else {
 	if('main' == $wpblog){
 		add_action('admin_menu', 'wpmu_admin_run');
 	}
 	
 } // else
 
?>
