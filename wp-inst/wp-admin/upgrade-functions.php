<?php

require_once(ABSPATH . '/wp-admin/admin-functions.php');
require_once(ABSPATH . '/wp-admin/upgrade-schema.php');
// Functions to be called in install and upgrade scripts

define( "RESET_CAPS", true );

function upgrade_all() {
	populate_options();
	upgrade_160();
}


function upgrade_160() {
	global $wpdb, $table_prefix;
	$users = $wpdb->get_results("SELECT * FROM $wpdb->users");
	foreach ( $users as $user ) :
		if ( !empty( $user->user_firstname ) )
			update_usermeta( $user->ID, 'first_name', $wpdb->escape($user->user_firstname) );
		if ( !empty( $user->user_lastname ) )
			update_usermeta( $user->ID, 'last_name', $wpdb->escape($user->user_lastname) );
		if ( !empty( $user->user_nickname ) )
			update_usermeta( $user->ID, 'nickname', $wpdb->escape($user->user_nickname) );
		if ( !empty( $user->user_level ) )
			update_usermeta( $user->ID, $table_prefix . 'user_level', $user->user_level );
		if ( !empty( $user->user_icq ) )
			update_usermeta( $user->ID, 'icq', $wpdb->escape($user->user_icq) );
		if ( !empty( $user->user_aim ) )
			update_usermeta( $user->ID, 'aim', $wpdb->escape($user->user_aim) );
		if ( !empty( $user->user_msn ) )
			update_usermeta( $user->ID, 'msn', $wpdb->escape($user->user_msn) );
		if ( !empty( $user->user_yim ) )
			update_usermeta( $user->ID, 'yim', $wpdb->escape($user->user_icq) );
		if ( !empty( $user->user_description ) )
			update_usermeta( $user->ID, 'description', $wpdb->escape($user->user_description) );

		if ( isset( $user->user_idmode ) ):
			$idmode = $user->user_idmode;
			if ($idmode == 'nickname') $id = $user->user_nickname;
			if ($idmode == 'login') $id = $user->user_login;
			if ($idmode == 'firstname') $id = $user->user_firstname;
			if ($idmode == 'lastname') $id = $user->user_lastname;
			if ($idmode == 'namefl') $id = $user->user_firstname.' '.$user->user_lastname;
			if ($idmode == 'namelf') $id = $user->user_lastname.' '.$user->user_firstname;
			if (!$idmode) $id = $user->user_nickname;
			$id = $wpdb->escape( $id );
			$wpdb->query("UPDATE $wpdb->users SET display_name = '$id' WHERE ID = '$user->ID'");
		endif;
		
		// FIXME: RESET_CAPS is temporary code to reset roles and caps if flag is set.
		$caps = get_usermeta( $user->ID, $table_prefix . 'capabilities');
		if ( empty($caps) || defined('RESET_CAPS') ) {
			$level = get_usermeta($user->ID, $table_prefix . 'user_level');
			if( empty( $level ) == false ) {
				$role = translate_level_to_role($level);
				update_usermeta( $user->ID, $table_prefix . 'capabilities', array($role => true) );
			}
		}
			
	endforeach;
	$old_user_fields = array( 'user_firstname', 'user_lastname', 'user_icq', 'user_aim', 'user_msn', 'user_yim', 'user_idmode', 'user_ip', 'user_domain', 'user_browser', 'user_description', 'user_nickname', 'user_level' );
	$wpdb->hide_errors();
	foreach ( $old_user_fields as $old )
		$wpdb->query("ALTER TABLE $wpdb->users DROP $old");
	$wpdb->show_errors();
	
	if ( 0 == $wpdb->get_var("SELECT SUM(category_count) FROM $wpdb->categories") ) { // Create counts
		$categories = $wpdb->get_col("SELECT cat_ID FROM $wpdb->categories");
		foreach ( $categories as $cat_id ) {
			$count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->post2cat WHERE category_id = '$cat_id'");
			$wpdb->query("UPDATE $wpdb->categories SET category_count = '$count' WHERE cat_ID = '$cat_id'");
		}
	}
}

// The functions we use to actually do stuff

// General
function maybe_create_table($table_name, $create_ddl) {
    global $wpdb;
    foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
        if ($table == $table_name) {
            return true;
        }
    }
    //didn't find it try to create it.
    $q = $wpdb->query($create_ddl);
    // we cannot directly tell that whether this succeeded!
    foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
        if ($table == $table_name) {
            return true;
        }
    }
    return false;
}

function drop_index($table, $index) {
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->query("ALTER TABLE `$table` DROP INDEX `$index`");
	// Now we need to take out all the extra ones we may have created
	for ($i = 0; $i < 25; $i++) {
		$wpdb->query("ALTER TABLE `$table` DROP INDEX `{$index}_$i`");
	}
	$wpdb->show_errors();
	return true;
}

function add_clean_index($table, $index) {
	global $wpdb;
	drop_index($table, $index);
	$wpdb->query("ALTER TABLE `$table` ADD INDEX ( `$index` )");
	return true;
}

/**
 ** maybe_add_column()
 ** Add column to db table if it doesn't exist.
 ** Returns:  true if already exists or on successful completion
 **           false on error
 */
function maybe_add_column($table_name, $column_name, $create_ddl) {
    global $wpdb, $debug;
    foreach ($wpdb->get_col("DESC $table_name", 0) as $column ) {
        if ($debug) echo("checking $column == $column_name<br />");
        if ($column == $column_name) {
            return true;
        }
    }
    //didn't find it try to create it.
    $q = $wpdb->query($create_ddl);
    // we cannot directly tell that whether this succeeded!
    foreach ($wpdb->get_col("DESC $table_name", 0) as $column ) {
        if ($column == $column_name) {
            return true;
        }
    }
    return false;
}


// get_alloptions as it was for 1.2.
function get_alloptions_110() {
	global $wpdb;
	if ($options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options")) {
		foreach ($options as $option) {
			// "When trying to design a foolproof system, 
			//  never underestimate the ingenuity of the fools :)" -- Dougal
			if ('siteurl' == $option->option_name) $option->option_value = preg_replace('|/+$|', '', $option->option_value);
			if ('home' == $option->option_name) $option->option_value = preg_replace('|/+$|', '', $option->option_value);
			if ('category_base' == $option->option_name) $option->option_value = preg_replace('|/+$|', '', $option->option_value);
			$all_options->{$option->option_name} = stripslashes($option->option_value);
		}
	}
	return $all_options;
}

// Version of get_option that is private to install/upgrade.
function __get_option($setting) {
	global $wpdb;

	$option = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = '$setting'");

	@ $kellogs = unserialize($option);
	if ($kellogs !== FALSE)
		return $kellogs;
	else
		return $option;
}

function deslash($content) {
    // Note: \\\ inside a regex denotes a single backslash.

    // Replace one or more backslashes followed by a single quote with
    // a single quote.
    $content = preg_replace("/\\\+'/", "'", $content);

    // Replace one or more backslashes followed by a double quote with
    // a double quote.
    $content = preg_replace('/\\\+"/', '"', $content);

    // Replace one or more backslashes with one backslash.
    $content = preg_replace("/\\\+/", "\\", $content);

    return $content;
}

function dbDelta($queries, $execute = true) {
	global $wpdb;
	
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
			$cqueries[strtolower($matches[1])] = $qry;
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

	// Check to see which tables and fields exist
	if($tables = $wpdb->get_col('SHOW TABLES;')) {
		// For every table in the database
		foreach($tables as $table) {
			// If a table query exists for the database table...
			if( array_key_exists(strtolower($table), $cqueries) ) {
				// Clear the field and index arrays
				unset($cfields);
				unset($indices);
				// Get all of the field names in the query from between the parens
				preg_match("|\((.*)\)|ms", $cqueries[strtolower($table)], $match2);
				$qryline = trim($match2[1]);

				// Separate field lines into an array
				$flds = explode("\n", $qryline);

				//echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";
				
				// For every field line specified in the query
				foreach($flds as $fld) {
					// Extract the field name
					preg_match("|^([^ ]*)|", trim($fld), $fvals);
					$fieldname = $fvals[1];
					
					// Verify the found field name
					$validfield = true;
					switch(strtolower($fieldname))
					{
					case '':
					case 'primary':
					case 'index':
					case 'fulltext':
					case 'unique':
					case 'key':
						$validfield = false;
						$indices[] = trim(trim($fld), ", \n");
						break;
					}
					$fld = trim($fld);
					
					// If it's a valid field, add it to the field array
					if($validfield) {
						$cfields[strtolower($fieldname)] = trim($fld, ", \n");
					}
				}
				
				// Fetch the table column structure from the database
				$tablefields = $wpdb->get_results("DESCRIBE {$table};");
								
				// For every field in the table
				foreach($tablefields as $tablefield) {				
					// If the table field exists in the field array...
					if(array_key_exists(strtolower($tablefield->Field), $cfields)) {
						// Get the field type from the query
						preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
						$fieldtype = $matches[1];

						// Is actual field type different from the field type in query?
						if($tablefield->Type != $fieldtype) {
							// Add a query to change the column type
							$cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
							$for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
						}
						
						// Get the default value from the array
							//echo "{$cfields[strtolower($tablefield->Field)]}<br>";
						if(preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
							$default_value = $matches[1];
							if($tablefield->Default != $default_value)
							{
								// Add a query to change the column's default value
								$cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
								$for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
							}
						}

						// Remove the field from the array (so it's not added)
						unset($cfields[strtolower($tablefield->Field)]);
					}
					else {
						// This field exists in the table, but not in the creation queries?
					}
				}

				// For every remaining field specified for the table
				foreach($cfields as $fieldname => $fielddef) {
					// Push a query line into $cqueries that adds the field to that table
					$cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
					$for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
				}
				
				// Index stuff goes here
				// Fetch the table index structure from the database
				$tableindices = $wpdb->get_results("SHOW INDEX FROM {$table};");
				
				if($tableindices) {
					// Clear the index array
					unset($index_ary);

					// For every index in the table
					foreach($tableindices as $tableindex) {
						// Add the index to the index data array
						$keyname = $tableindex->Key_name;
						$index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
						$index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
					}

					// For each actual index in the index array
					foreach($index_ary as $index_name => $index_data) {
						// Build a create string to compare to the query
						$index_string = '';
						if($index_name == 'PRIMARY') {
							$index_string .= 'PRIMARY ';
						}
						else if($index_data['unique']) {
							$index_string .= 'UNIQUE ';
						}
						$index_string .= 'KEY ';
						if($index_name != 'PRIMARY') {
							$index_string .= $index_name;
						}
						$index_columns = '';
						// For each column in the index
						foreach($index_data['columns'] as $column_data) {					
							if($index_columns != '') $index_columns .= ',';
							// Add the field to the column list string
							$index_columns .= $column_data['fieldname'];
							if($column_data['subpart'] != '') {
								$index_columns .= '('.$column_data['subpart'].')';
							}
						}
						// Add the column list to the index create string 
						$index_string .= ' ('.$index_columns.')';

						if(!(($aindex = array_search($index_string, $indices)) === false)) {
							unset($indices[$aindex]);
							//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br/>Found index:".$index_string."</pre>\n";
						}
						//else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br/><b>Did not find index:</b>".$index_string."<br/>".print_r($indices, true)."</pre>\n";
					}
				}

				// For every remaining index specified for the table
				foreach($indices as $index) {
					// Push a query line into $cqueries that adds the index to that table
					$cqueries[] = "ALTER TABLE {$table} ADD $index";
					$for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
				}

				// Remove the original table creation query from processing
				unset($cqueries[strtolower($table)]);
				unset($for_update[strtolower($table)]);
			} else {
				// This table exists in the database, but not in the creation queries?
			}
		}
	}

	$allqueries = array_merge($cqueries, $iqueries);
	if($execute) {
		foreach($allqueries as $query) {
			//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">".print_r($query, true)."</pre>\n";
			$wpdb->query($query);
		}
	}

	return $for_update;
}

function make_db_current() {
	global $wp_queries;

	$alterations = dbDelta($wp_queries);
	echo "<ol>\n";
	foreach($alterations as $alteration) echo "<li>$alteration</li>\n";
	echo "</ol>\n";
}

function make_db_current_silent() {
	global $wp_queries;

	$alterations = dbDelta($wp_queries);
}

function make_site_theme_from_oldschool($theme_name, $template) {
	$home_path = get_home_path();
	$site_dir = ABSPATH . "wp-content/themes/$template";

	if (! file_exists("$home_path/index.php"))
		return false;

	// Copy files from the old locations to the site theme.
	// TODO: This does not copy arbitarary include dependencies.  Only the
	// standard WP files are copied.
	$files = array('index.php' => 'index.php', 'wp-layout.css' => 'style.css', 'wp-comments.php' => 'comments.php', 'wp-comments-popup.php' => 'comments-popup.php');

	foreach ($files as $oldfile => $newfile) {
		if ($oldfile == 'index.php')
			$oldpath = $home_path;
		else
			$oldpath = ABSPATH;

		if ($oldfile == 'index.php') { // Check to make sure it's not a new index
			$index = implode('', file("$oldpath/$oldfile"));
			if ( strstr( $index, 'WP_USE_THEMES' ) ) {
				if (! @copy(ABSPATH . 'wp-content/themes/default/index.php', "$site_dir/$newfile"))
					return false;
				continue; // Don't copy anything
				}
		}

		if (! @copy("$oldpath/$oldfile", "$site_dir/$newfile"))
			return false;

		chmod("$site_dir/$newfile", 0777);

		// Update the blog header include in each file.
		$lines = explode("\n", implode('', file("$site_dir/$newfile")));
		if ($lines) {
			$f = fopen("$site_dir/$newfile", 'w');

			foreach ($lines as $line) {
				if (preg_match('/require.*wp-blog-header/', $line))
					$line = '//' . $line;

				// Update stylesheet references.
				$line = str_replace("<?php echo __get_option('siteurl'); ?>/wp-layout.css", "<?php bloginfo('stylesheet_url'); ?>", $line);

				// Update comments template inclusion.
				$line = str_replace("<?php include(ABSPATH . 'wp-comments.php'); ?>", "<?php comments_template(); ?>", $line);

				fwrite($f, "{$line}\n");
			}
			fclose($f);
		}
	}

	// Add a theme header.
	$header = "/*\nTheme Name: $theme_name\nTheme URI: " . __get_option('siteurl') . "\nDescription: A theme automatically created by the upgrade.\nVersion: 1.0\nAuthor: Moi\n*/\n";

	$stylelines = file_get_contents("$site_dir/style.css");
	if ($stylelines) {
		$f = fopen("$site_dir/style.css", 'w');

		fwrite($f, $header);
		fwrite($f, $stylelines);
		fclose($f);
	}

	return true;
}

function make_site_theme_from_default($theme_name, $template) {
	$site_dir = ABSPATH . "wp-content/themes/$template";
	$default_dir = ABSPATH . 'wp-content/themes/default';

	// Copy files from the default theme to the site theme.
	//$files = array('index.php', 'comments.php', 'comments-popup.php', 'footer.php', 'header.php', 'sidebar.php', 'style.css');

	$theme_dir = @ dir("$default_dir");
	if ($theme_dir) {
		while(($theme_file = $theme_dir->read()) !== false) {
			if (is_dir("$default_dir/$theme_file"))
				continue;
			if (! @copy("$default_dir/$theme_file", "$site_dir/$theme_file"))
				return;
			chmod("$site_dir/$theme_file", 0777);
		}
	}

	// Rewrite the theme header.
	$stylelines = explode("\n", implode('', file("$site_dir/style.css")));
	if ($stylelines) {
		$f = fopen("$site_dir/style.css", 'w');

		foreach ($stylelines as $line) {
			if (strstr($line, "Theme Name:")) $line = "Theme Name: $theme_name";
			elseif (strstr($line, "Theme URI:")) $line = "Theme URI: " . __get_option('siteurl');
			elseif (strstr($line, "Description:")) $line = "Description: Your theme";
			elseif (strstr($line, "Version:")) $line = "Version: 1";
			elseif (strstr($line, "Author:")) $line = "Author: You";
			fwrite($f, "{$line}\n");
		}
		fclose($f);
	}

	// Copy the images.
	umask(0);
	if (! mkdir("$site_dir/images", 0777)) {
		return false;
	}

	$images_dir = @ dir("$default_dir/images");
	if ($images_dir) {
		while(($image = $images_dir->read()) !== false) {
			if (is_dir("$default_dir/images/$image"))
				continue;
			if (! @copy("$default_dir/images/$image", "$site_dir/images/$image"))
				return;
			chmod("$site_dir/images/$image", 0777);
		}
	}
}

// Create a site theme from the default theme.
function make_site_theme() {
	return true;
	// Name the theme after the blog.
	$theme_name = __get_option('blogname');
	$template = sanitize_title($theme_name);
	$site_dir = ABSPATH . "wp-content/themes/$template";

	// If the theme already exists, nothing to do.
	if ( is_dir($site_dir)) {
		return false;
	}

	// We must be able to write to the themes dir.
	if (! is_writable(ABSPATH . "wp-content/themes")) {
		return false;
	}

	umask(0);
	if (! mkdir($site_dir, 0777)) {
		return false;
	}

	if (file_exists(ABSPATH . 'wp-layout.css')) {
		if (! make_site_theme_from_oldschool($theme_name, $template)) {
			// TODO:  rm -rf the site theme directory.
			return false;
		}
	} else {
		if (! make_site_theme_from_default($theme_name, $template))
			// TODO:  rm -rf the site theme directory.
			return false;
	}

	// Make the new site theme active.
	$current_template = __get_option('template');
	if ($current_template == 'default') {
		update_option('template', $template);
		update_option('stylesheet', $template);
	}
	return $template;
}

function translate_level_to_role($level) {
	switch ($level) {
	case 10:
	case 9:
	case 8:
		return 'administrator';
	case 7:
	case 6:
	case 5:
		return 'editor';
	case 4:
	case 3:
	case 2:
		return 'author';
	case 1:
		return 'contributor';
	case 0:
		return 'subscriber';
	}
}

?>
