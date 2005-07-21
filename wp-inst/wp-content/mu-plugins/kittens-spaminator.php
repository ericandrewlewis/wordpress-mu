<?php
/*
Plugin Name: Kitten's Spaminator
Version: 1.0rc7
Plugin URI: http://blog.mookitty.co.uk/wordpress/spaminator/
Description: Spam prevention and blocking using tarpitting and strike counting. Comments are assigned strikes for spam content, and a comment that meets the criteria for spam is blocked from posting.<br>If you're using WP 1.3 or higher, you have an admin menu under "Options" if you wish to change the defaults. For 1.2 users, you must edit the plugin file directly.<br><strong> Copyright 2004, Released under the GPL.</strong>
Author: Kitten
Author URI: http://blog.mookitty.co.uk

INSTALLATION INSTRUCTIONS
=========================
1) If you're viewing this on the web, select all and paste it into a new text file named "kittens-spaminator.php" 

2) Copy the "kittens-spaminator.php" file to your wp-contents/plugins directory.

3) On your plugins admin page, activate the plugin.

4) Enjoy your freedom from spam!

   4a) Configure options if you don't like the defaults. Use the admin page for WP 1.3+, edit
       edit this file for 1.2.
=========================

Change Log:
0.1a   - initial release
0.2a   - add more stuff
0.3a   - add whitelisting, fix checker function
0.3b   - fix debug code error
0.4a   - changed even more stuff
0.5a   - add crapflood check, change default values
0.6a   - add stike if no email,etc 
0.6b   - fix php error
0.7a   - add check for empty email (Donncha)
0.8a   - add encoded char check, from functions.php
0.9a   - add post ID check, from techgnome
0.9b   - clean up email stuff & version
0.10a  - add user regex for especially annoying spam
0.10b  - fix user regex bug
0.10c  - fix another user regex bug
0.10d  - clean up code, add TODO
0.11a  - add URL check, fix strpos errors, interface for $strike_cnt
0.11b  - add user regex for email
1.0rc  - Release candidate, add GPL license
1.0rc2 - Add admin page skel, 1.2 fixes, name whitelisting, "how" in email, clear TODOs
         fix excessive links bug, change default for no referrer, double stripslashes on the 
		 comment (to make the comment_url filter work to detect strange urls), fix html
		 entities
1.0rc3 - Add check for real url in URL field, expand character entities check, check
		 all urls for dashes (most spam urls have dashes). Change user_regex_c to hopefully
		 catch the garbage spammer. Add record keeping on passed comments.
1.0rc4 - Now used now 'preprocess' comment filter hook, only works with v1.5 nightlies from
		 Jan 5th or later. New email address for returned mail.
1.0rc5 - New shiny admin interface + Kitten's regex service.
1.0rc6 - Fix install bug.
1.0rc7 - Sanitize regex service text, trim form fields.
*/

/**************----------------  CLASS DEFINITIONS  ----------------**************/
/// Admin page
if ( ! class_exists( 'spaminator_admin_page' ) ) :
class spaminator_admin_page
{
	function spaminator_admin_page( $post = '' )
	{
		$this->installed = get_settings( 'spaminator_status' );
		// Get and load old options
		$this->opts = get_settings( 'spaminator_settings' );
		if ( count( $this->opts ) == 6 ) {
			foreach ( $this->opts as $key => $val ) $this->$key = $val;
		}
		// Override with new options
		if ( 'Save Options' == $post['save_spaminator_options'] ) {
			$this->save_options = trim( $post['save_spaminator_options'] );
			$this->strikes      = trim( $post['spaminator_strikes'] );
			$this->user_regex_c = trim( $post['spaminator_comment_regex'] );
			$this->user_regex_e = trim( $post['spaminator_email_regex'] );
			$this->nap_time     = trim( $post['spaminator_naptime'] );
			$this->send_mail    = trim( $post['spaminator_sendmail'] );
			$this->crap_flood   = trim( $post['spaminator_crapflood'] );
		}

		if ( 'Remove Options' == $post['remove_spaminator_options'] ) {
			$this->remove_options();
		}

		// Need to set things up
		$this->install_options = $post['install_spaminator_options'];
		$this->process_options();

		// Get regex list
		if ( 'Get Regexs' == $post['get_regexs'] ) {
			$this->get_regexs();
		}
	}

	function process_options()
	{
		// Install
		if ( 'Install now' == $this->install_options && 'installed' !=  $this->installed ) {
			add_option( 'spaminator_status', 'installed', 'Spaminator install flag' );
			$settings = array( 'strikes' => 5,
							   'user_regex_e' => '/^byob.*[0-9]{1,4}/i',
							   'user_regex_c' => '/bea?stiality|rape|incest/i',
							   'nap_time' => 60,
							   'send_mail' => TRUE,
							   'crap_flood' => 60 );
			add_option( 'spaminator_settings', $settings, 'Spaminator options' );
			$this->installed = get_settings( 'spaminator_status' );
			$this->opts = get_settings( 'spaminator_settings' );
			if ( count( $this->opts ) == 6 ) {
				foreach ( $this->opts as $key => $val ) $this->$key = $val;
			} else {
				die( "There was a problem installing the default settings. Please try again." );
			}
		}

		// Save new options
		if ( 'Save Options' == $this->save_options ) {
			$settings = array( 'strikes' => $this->strikes,
							   'user_regex_c' => $this->user_regex_c,
							   'user_regex_e' => $this->user_regex_e,
							   'nap_time' => $this->nap_time,
							   'send_mail' => $this->send_mail,
							   'crap_flood' => $this->crap_flood );
			update_option( 'spaminator_settings', $settings );
			$this->updated = '<div class="updated"><p>The Spaminator&#146;s settings were updated successfully.</p></div>' . "\n";
		}
	}

	function get_regexs()
	{
		$f = @fopen( "http://mookitty.co.uk/regexs.txt", 'r' );
		$data = @fread( $f, 2048 ); // even if corrupted, only 2K max
		@fclose( $f );

		if ( strlen( $data ) < 1 ) {
			$this->regexs = 'Sorry, no data available.<br />See <a href="http://mookitty.co.uk/regexs.txt">this page</a> for the latest.';
		} else {
			$this->regexs = htmlentities( $data );
		}
	}

	function remove_options()
	{
		delete_option( 'spaminator_status' );
		delete_option( 'spaminator_settings' );
		$this->installed = '';
		$this->updated = '<div class="updated"><p style="color: red;"><strong>The Spaminator&#146;s settings were removed.</strong> You are now using the built in defaults.</p></div>' . "\n";
	}
	
	function display_admin_page()
	{
		if ( 'installed' == $this->installed ) {
			return $this->show_form();
		} else {
			return $this->show_install_form();
		}
	}

	function show_install_form()
	{
		$text  = $this->updated;
		$text .= '<div class="wrap"><h2>Install The Spaminator&#146;s Config</h2>' . "\n";
		$text .= '<form method="post" action="">' . "\n";
		$text .= '<h3>Do you want to be able to configure The Spaminator from this admin page?</h3>' . "\n";
		$text .= "<p>This will install The Spaminator's options in your database.</p>" . "\n";
		$text .= '<input type="submit" name="install_spaminator_options" value="Install now" />' . "\n";
		$text .= '</form>'. "\n";
		$text .= '</div>' . "\n";
		return $text;
	}

	function show_form()
	{
		$text  = $this->updated;
		$text .= '<div class="wrap"><h2>Configure The Spaminator</h2>' . "\n";
		$text .= '<form method="post" action="" name="spaminator_options">' . "\n";
		$text .= '<table>' . "\n";
		$text .= '<tr><td>Strikes:</td>' . "\n";
		$text .= '<td><input type="text" name="spaminator_strikes" value="'.$this->strikes.'" /></td>' . "\n";
		$text .= '<td>The number of "hits" needed to kill a comment as spam.</td></tr>' . "\n";
		
		$text .= '<tr><td>Send email?</td>' . "\n";
		$text .= '<td><select name="spaminator_sendmail">' . "\n";
		if ( $this->send_mail ) {
			$text .= '<option value="1" selected="selected">Yes&nbsp;&nbsp;</option>' . "\n";
			$text .= '<option value="0">No</option>' . "\n";
		} else {
			$text .= '<option value="1">Yes&nbsp;&nbsp;</option>' . "\n";
			$text .= '<option value="0" selected="selected">No</option>' . "\n";
		}
		$text .= '</select></td>' . "\n";
		$text .= '<td>Send email confirmation of each comment killed?</td></tr>' . "\n";
		

		$text .= '<tr><td>Nap Time:</td>' . "\n";
		$text .= '<td><input type="text" name="spaminator_naptime" value="'.$this->nap_time.'" /></td>' . "\n";
		$text .= '<td>How long to tarpit the spammer, in seconds.</td></tr>' . "\n";
		
		$text .= '<tr><td>Crap Flood:</td>' . "\n";
		$text .= '<td><input type="text" name="spaminator_crapflood" value="'.$this->crap_flood.'" /></td>' . "\n";
		$text .= '<td>Minimum amount of time allowed between comments from same IP address.</td></tr>' . "\n";
		
		
		$text .= '<tr><td>Email regex:</td>' . "\n";
		$text .= '<td><input type="text" name="spaminator_email_regex" value="'.$this->user_regex_e.'" /></td>' . "\n";
		$text .= '<td>Special pattern in the email address of the commenter to kill comments.</td></tr>' . "\n";
		
		
		$text .= '<tr><td>Comment regex:</td>' . "\n";
		$text .= '<td><input type="text" name="spaminator_comment_regex" value="'.$this->user_regex_c.'" /></td>' . "\n";
		$text .= '<td>Special pattern in the comment body to kill comments.</td></tr>' . "\n";
		
		$text .= '</table>' . "\n";
		$text .= '<input type="submit" name="save_spaminator_options" value="Save Options" />' . "\n";
		$text .= '</form>'. "\n";
		$text .= '</div>' . "\n";

		$text .= '<div class="wrap"><h2>Kitten\'s Regex Service</h2>' . "\n";
		if ( !empty( $this->regexs ) ) {
			$text .= "<pre>$this->regexs</pre>";
		} else {
			$text .= '<p>If you\'d like to see <a href="http://blog.mookitty.co.uk/wordpress/regex-service/">Kitten\'s custom regexs</a> that she\'s currently using to keep spam away, click the button below:</p>' . "\n";
			$text .= '<p><strong style="color: red">Notice:</strong> This is highly dependant on your server configuration, and may not work. You\'ve been warned.</p>' . "\n";
			$text .= '<form method="post" action="" name="kittens_regexs">' . "\n";
			$text .= '<input type="submit" name="get_regexs" value="Get Regexs" />' . "\n";
			$text .= '</form>' . "\n";
		}
		$text .= '</div>' . "\n";

		$text .= '<div class="wrap"><h2>Remove The Spaminator&#146;s Options</h2>' . "\n";
		$text .= '<p><strong style="color: red">Help!</strong> I\'ve totally screwed up my settings and want to use the built in defaults.</p>' . "\n";
		$text .= '<form method="post" action="" name="remove_spaminator_options">' . "\n";
		$text .= '<p>Really remove the user options?</p>' . "\n";
		$text .= '<input type="submit" name="remove_spaminator_options" value="Remove Options" />' . "\n";
		$text .= '</form>'. "\n";
		$text .= '</div>' . "\n";

		return $text;
	}
}
endif;

/// Spam redirector class
if ( ! class_exists( 'spam_killer' ) ) :
class spam_killer
{	
	// Class vars
	var $how;
	var $post;
	var $strikes;
	var $strike_cnt;
	var $word_list;
	var $nap_time;
	var $send_mail;
	var $crap_flood;

	function spam_killer( $post )
	{
		global $wpdb, $wpmuBaseTablePrefix;
		$options = array(
		//==================================================================
		//	Adjust the number of strikes needed to reject a comment
				strikes => 5,		// Number
		//==================================================================
		//	Change this to fight a particular spammer
			user_regex_c => '/bea?stiality|rape|incest/i',	// Text string
			user_regex_e => '/^byob.*[0-9]{1,4}/i',		// Text string
		//==================================================================
		//	Adjust the nap time to vary the TarPit delay
				nap_time   => 10,		// Time in seconds
		//==================================================================
		//	Change to TRUE to enable sending email when a spammer is caught
				send_mail  => true,	// TRUE or FALSE
		//==================================================================
		//	Adjust the minimum time between posts (crapflooding)
				crap_flood => 60,		// Time in seconds
		//==================================================================
		);
		
		$installed = get_settings( 'spaminator_status' );

		if ( 'installed' == $installed ) { // this is set via the admin page
			$saved_options = get_settings( 'spaminator_settings' );
			if ( is_array( $saved_options ) ) $options = $saved_options; // override
		}

		foreach ( $options as $key => $opt ) $this->$key = $opt;
		
		$this->post = $post;
		$this->strike_cnt = 0;
		$this->word_list = get_settings('moderation_keys');
		if( $wpdb->options != $wpmuBaseTablePrefix . '_1_options' )
		{
		    $query = "SELECT option_value from " . $wpmuBaseTablePrefix . $wpdb->blogid . "_options WHERE option_name='moderation_keys'";
		    $this->word_list .= "\n" . $wpdb->get_var( $query );
		}
	}

	function count_strikes( $str = 0, $how )
	{
		$this->how[] = $how; // list all checks so far
		$this->strike_cnt += $str;
		
		// stats
		$this->post['comment_content'] .= "<!-- X-spaminator-strike: $how, $str -->";

		if ( $this->strike_cnt >= $this->strikes ) {

			// Maybe send mail - we got spammer tail
			if ( $this->send_mail ) {
				$why   = implode( ", ", $this->how );
				$body  = "The Spaminator has killed a comment.\r\n\r\n";
				$body .= "The details:\r\n";
				$body .= "Strikes : $this->strike_cnt/$this->strikes\r\n";
				$body .= "How     : $why\r\n";
				$body .= "IP Addr : ".$_SERVER['REMOTE_ADDR']."\r\n";
				$body .= "Referer : ".$_SERVER['HTTP_REFERER']."\r\n";
				$body .= "Client  : ".$_SERVER['HTTP_USER_AGENT']."\r\n";
				$body .= "Request : ".$_SERVER['REQUEST_METHOD']." ". $_SERVER['REQUEST_URI']."\r\n";
				$body .= "Post ID : ".$this->post['comment_post_ID']."\r\n";
				$body .= "Email   : ".$this->post['comment_author_email']."\r\n";
				$body .= "Author  : ".$this->post['comment_author']."\r\n";
				$body .= "URL     : ".$this->post['comment_author_url']."\r\n";
				$body .= "Body:\r\n";
				$body .= $this->post['comment_content']."\r\n\r\n";
				$body .= "--\r\nThis email has been sent because the Spaminator plugin is set to send emails when a suspected spam has been blocked.\r\nTo not receive these emails change the ".'$this->send_mail'." variable to FALSE.\r\n\r\nThanks for using this plugin, hope it helps!\r\nhttp://mookitty.co.uk/devblog/";
				$headers = "From: The Spaminator <wp.spaminator@gmail.com>";
				$to = "[" . get_settings('blogname') . "] Spaminator: Spammer caught!";
				
				@mail( get_settings('admin_email'), $to, $body, $headers );
			}
			
			// Let's waste spambot time:
			sleep ( $this->nap_time );
			
			// Make sure the bot thinks that spam was posted:
			header( "HTTP/1.0 200 OK" );

			// Tell humans something else:
			echo "<p>Sorry, you've been prevented from commenting on this blog.</p>\n";
			echo "<p>Either your comment content was found to contain spam, or<br />\n";
			echo "your IP address (or a subnet of your IP address) has spammed this blog before.</p>\n";
			echo "<p>If you think you got this page in error, your entered name might be too short.</p>\n";
			echo "<p>You can also complain to <a href=\"wp.spaminator@gmail.com\">wp.spaminator@gmail.com</a>. View source to see why you got blocked.\n";
			echo "<p>Strike count: $this->strike_cnt</p>\n";
			echo "\n<!-- ", implode( ", ", $this->how ), " -->";
			exit;
		}
	}

	function process_comment()
	{
		global $wpdb, $table_prefix;
		if ( empty($wpdb->comments) ) $wpdb->comments =  $table_prefix . 'comments';
		if ( empty($wpdb->posts) ) $wpdb->posts =  $table_prefix . 'posts';
		
		/// Set up vars to use:
		
		$type    = $this->post['comment_type'];
		$url     = parse_url( $this->post['comment_author_url'] );
		$postID  = $this->post['comment_post_ID'];
		$author  = $this->post['comment_author'];
		if ( empty( $this->post['comment_author_email'] ) ) {
			if ( 'trackback' == $type ) {
				$this->post['comment_author_email'] = 'trackback@' . $url['host'];
			} elseif ( 'pingback' == $type ) {
				$this->post['comment_author_email'] = 'pingback@' . $url['host'];
			}
		}
		$email = $this->post['comment_author_email'];
		// Filter the text so that links show up, etc.
		$comment = apply_filters( 'comment_text', stripslashes(stripslashes($this->post['comment_content'])) );
//		$forward = $this->post['redirect_to'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		$referer = $_SERVER['HTTP_REFERER'];
		$iplist  = preg_grep( "/^([0-9]{1,3}\.?){2}/", explode("\n", $this->word_list) );
		$wdlist  = explode( "\n", $this->word_list );
		$wdlist  = array_diff( $wdlist, $iplist );
		// Get email for whitelist
		if ( !empty( $email ) ) {
			$whlist  = $wpdb->get_row("SELECT comment_approved, comment_author FROM $wpdb->comments WHERE comment_author_email = '$email' AND comment_approved = '1' AND NOW()+0 > UNIX_TIMESTAMP( DATE_ADD( comment_date, INTERVAL 1  DAY ) ) GROUP BY comment_author;");
		} else {
			$whlist = '0';
		}

		// This returns a unix timestamp
		$cfchck  = $wpdb->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM $wpdb->comments WHERE comment_author_IP = '$remote' ORDER BY comment_date_gmt DESC LIMIT 1;");

		/// Check for spam:

		// User regex check
		if (  preg_match( $this->user_regex_c, $comment ) > 0 ) {
			$this->count_strikes( 10, 'user regex - comment' );
		}
		if (  preg_match( $this->user_regex_e, $email ) > 0 ) {
			$this->count_strikes( 10, 'user regex - email ');
		}
		
		// If non-existing post, spam for sure
		if ( !$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE ID = '$postID'") ) {
			$this->count_strikes( 10, 'non-existant post' );
		}

		// If previously approved, they get a headstart
		if ( '1' == $whlist->comment_approved  ) $this->count_strikes( -3, 'whitelist' );

		// Check referer, see if a bot is directly inserting comment
		if ( FALSE === strpos( $referer, get_settings('siteurl') ) ) {
			$this->count_strikes( 3, 'bad referer - spambot?' );
		}

		// Check IP, see if it's a known spammer
		$this->checker( $iplist, $remote, 5, 'IP check' );

		// Check for crapflood
		if ( $cfchck + $this->crap_flood > time() ) {
             $this->count_strikes( 3, 'crap flooding' );
		}

		// Count links 
		if ( (count(explode('http', $comment)) - 1 ) > ( (int) get_settings('comment_max_links') ) ) {
			$this->count_strikes( 3, 'excessive links' );
		}

		// Check email
		$this->checker( $wdlist, $email, 1, 'email check' );

		// Check author
		// If the email is whitelisted, the name gets a free pass
		// the most recent version of the name is used
		if ( $author != $whlist->comment_author ) {
			$this->checker( $wdlist, $author, 1, 'author check' );
		}

		// Check URL
		// 3 points here, in case of "no body links" spam
		$this->checker( $wdlist, $url['host'], 3, 'author url' );

		// Check comment
		$this->checker( $wdlist, $comment, 1, 'comment body' );

		// From functions.php:
		// Useless numeric encoding is a pretty good spam indicator:
		// Extract entities:
		if (preg_match_all('/&#?(\d+);/', $url['host'] . $author . $email, $chars)) {
			foreach ($chars[1] as $char) {
			// If it's an encoded char in the normal ASCII set, reject
				if ($char < 128) $this->count_strikes( 1, 'html entity' );
			}
		}

		// Check for dashes in urls
		// Urls that are auto formatted will get counted twice - feature, not bug!
		preg_match_all( '#(http:)?//([^\s"\'<>]*)#i', $comment, $match );
		array_push( $match[2], $url['host'] );
		array_push( $match[2], $email );
		$cnt_dash = 0;
		foreach ( $match[2] as $u ) {
			$cnt_dash += substr_count( $u, '-' );
		}
		if ( $cnt_dash > 0 ) {
			$this->count_strikes( $cnt_dash, 'url dashes' );
		}

		// Check if provided url is legit
		if ( strlen( $url['host'] ) > 0 && ! checkdnsrr( $url['host'], 'A' ) ) {
			$this->count_strikes( 4, "unknown url, $url" );
		}

		return $this->post;

	} // end of function process_comment

	function checker( $haystack, $needle, $strike_val, $how )
	{
		if ( is_array( $haystack ) && count( $haystack ) > 0 && strlen( $needle ) > 3 ) {
			foreach ( $haystack as $item ) {
				$item = trim( $item );
				// skip empties & shorts in mod keys, faster than filtering list
				if ( strlen( $item ) < 3 ) continue;

				$word_cnt = substr_count( $needle, $item );
				if ( $word_cnt > 0 ) {
					$this->count_strikes( $strike_val * $word_cnt, $how . ' - ' . $item );
				}
			}
		}
		// Add a strike if email, etc is empty
		if ( empty( $needle ) ) $this->count_strikes( 1, "empty field - $how" );
		if ( ! empty( $needle ) && strlen( $needle ) <=3 ) $this->count_strikes( 1, 'short field' );

		// Record keeping
		$this->post['comment_content'] .= "<!-- X-spaminator-passed: $how -->";

	} // end of function checker
	
} // end class spam killer
endif;
/**************--------------  END CLASS DEFINITIONS  --------------**************/

/**************----------------  STANDALONE SECTION  ---------------**************/
/// Control block if comment posted.
if ( ! function_exists( 'spaminate_comment' ) ) {
	function spaminate_comment( $comment ) {
		$incoming_spam = new spam_killer( $comment );
		$foo = $incoming_spam->process_comment();
		return $foo;
	}
}

// Adds the menu item
if ( ! function_exists( 'add_spaminator_menu' ) ) {
	function add_spaminator_menu()
    {
		add_options_page(__('Spaminator Config'), __('Spaminator'), 9, 'kittens-spaminator.php');
	}
} elseif ( 'kittens-spaminator.php' == $_GET['page'] ) {
	$spamiator_iface = new spaminator_admin_page( $_POST );
	echo $spamiator_iface->display_admin_page();
}

add_action('preprocess_comment', 'spaminate_comment');
add_action('admin_menu', 'add_spaminator_menu');
/**************--------------  END STANDALONE SECTION  -------------**************/

?>
