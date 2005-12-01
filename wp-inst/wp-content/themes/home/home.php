<?php get_header(); ?>

	<div id="content" class="widecolumn">
				
	<h2><?php echo $current_site->site_name ?></h2>
	<p>This is a <a href="http://mu.wordpress.org/">WordPress Mu</a> powered site.</p>
	<p>You can: <ul><li> <a href="wp-login.php">Login</a> </li><li> <a href="wp-newblog.php">Create a new blog</a></li><li> Edit this file at <code>wp-content/themes/home/home.php</code> with your favourite text editor and customize this screen.</li></ul></p>
	<?php if( constant( "VHOST" ) == 'no' ) :?>
		<h3>Virtual Hosts</h3>
		<p>As you are not using virtual hosts, it is not possible to post anything to this primary blog because the URLs of your posts could potentially conflict with weblogs living at this site. Please consider using virtual hosts as <a href="http://mu.wordpress.org/forums/topic/99">described here</a> instead so that your website will have http://blogname.<?php echo $_SERVER[ 'HTTP_HOST' ] ?>/ format addresses instead of http://<?php echo $_SERVER[ 'HTTP_HOST' ] ?>/blogname/ format.</p>
	<?php endif; ?>
	
	</div>

<?php get_footer(); ?>
