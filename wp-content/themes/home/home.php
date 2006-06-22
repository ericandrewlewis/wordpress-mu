<?php get_header(); ?>

	<div id="content" class="widecolumn">
				
	<h2>WordPress &micro;</h2>
	<p>This is a <a href="http://mu.wordpress.org/">WordPress Mu</a> powered site.</p>
	<p>You can: <ul><li> <a href="wp-login.php">Login</a> </li><li> <a href="wp-signup.php">Create a new blog</a></li><li> Edit this file at <code>wp-content/themes/home/home.php</code> with your favourite text editor and customize this screen.</li></ul></p>
<h3>News Blog</h3>

<ul>
<?php 
query_posts('showposts=7');
if (have_posts()) : ?><?php while (have_posts()) : the_post(); ?>
<li><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title();?> </a></li>
<?php endwhile; ?><?php endif; ?>
</ul>

</div>

<?php get_footer(); ?>
