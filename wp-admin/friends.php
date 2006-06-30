<?php
require_once('admin.php'); 
$title = __('Friends'); 
$parent_file = 'index.php';

$links = get_bookmarks();
if( is_array( $links ) ) {
	include(ABSPATH . WPINC . '/simplepie.php');
	while( list( $key, $link ) = each( $links ) ) { 
		if( $link->link_rss ) {
			$url = $link->link_rss;
		} else {
			$url = $link->link_url;
		}

		// Create a new instance of the SimplePie object
		$feed = new SimplePie();
		$feed->cache_location = ABSPATH . 'wp-content/cache';

		// Set these Configuration Options
		$feed->bypass_image_hotlink();
		$feed->strip_ads(true);

		$feed->feed_url($url);
		// Initialize the whole SimplePie object.  Read the feed, process it, parse it, cache it, and 
		// all that other good stuff.  The feed's information will not be available to SimplePie before 
		// this is called.
		$feed->init();
		// Check to see if there are more than zero errors (i.e. if there are any errors at all)

		if ($feed->data) {
			$count = 0;
			foreach($feed->get_items() as $item) {
				if( $count > 3 )
					break;
				$count++;
				$items[ $item->get_date('U') ] = array( "link_id" => $link->link_id, "blog_url" => $feed->get_feed_link(), "blog_title" => $feed->get_feed_title(), "title" => $item->get_title(), "description" => $item->get_description(), "link" => $item->get_permalink(), "pubdate" => $item->get_date( "D, j M Y H:i:s O") );
			}
		}
		unset( $feed );
	}
}  

if( $_POST[ 'friend' ] ) {
	$friend = wp_specialchars( $_POST[ 'friend' ] );
	$link = get_default_link_to_edit();
	$link->link_url = $friend;
	wp_enqueue_script( array('xfn', 'dbx-admin-key?pagenow=link.php') );
	if ( current_user_can( 'manage_categories' ) )
		wp_enqueue_script( 'ajaxcat' );
}
require_once('admin-header.php');
if( $friend ) {
	include('edit-link-form.php');
} else {
?>

<div class="wrap">

<h2><?php _e('Friends'); ?></h2>

<p>Your friends are bookmarked using the <a href="link-manager.php">Link Manager</a>. This is the latest news from their websites.</p>
<?php
?>
<form name="addlink" id="addlink" method="post" action="friends.php">
Add another friend: <input type='text' name='friend' value='http://'>
<input type='submit'>
</form>
<br />
<?php
if( is_array( $items ) ) {
	reset( $items );
	krsort( $items );
	$blog_title = '';
	$count = 0;
	foreach ($items as $item ) {
		if( $blog_title != $item[ 'blog_title' ] ) {
			++ $count;
			if ($count % 2)
				$style = 'alternate';
			else
				$style = '';
			if( $count != 1 )
				print "</div><br />"; // not the first time!
			print "<div class='{$style}' style='padding: 5px; border: 1px solid #000'>";
			print "<div style='background: #94c6fa; color: #c3def1; border: 1px solid #000; padding: 2px;'><h3><a href='{$item[ 'blog_url' ]}/'>".wp_specialchars( $item[ 'blog_title' ] )."</a>&nbsp&nbsp;<sub><a href='link.php?link_id={$item[ 'link_id' ]}&action=edit'>(edit)</a></sub></h3></div>";
			$blog_title = $item[ 'blog_title' ];
		}
		?>
		<div style='border-bottom: 1px dotted #333; padding: 5px;'>
		<h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($item['pubdate'], time() ) ) ); ?></h4>
		<p><?php echo $item['description']; ?></p>
		</div>
		<?php
	}
} else {
	?>
	<p><?php _e("Below is the latest news from the official WordPress development blog, click on a title to read the full entry. If you need help with WordPress please see our <a href='http://codex.wordpress.org/'>great documentation</a> or if that doesn't help visit the <a href='http://wordpress.org/support/'>support forums</a>."); ?></p>
	<?php
	$rss = @fetch_rss('http://wordpress.org/development/feed/');
	if ( isset($rss->items) && 0 != count($rss->items) ) {
		?>
		<h3><?php _e('WordPress Development Blog'); ?></h3>
		<?php
		$rss->items = array_slice($rss->items, 0, 3);
		foreach ($rss->items as $item ) {
			?>
			<h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($item['pubdate'], time() ) ) ); ?></h4>
			<p><?php echo $item['description']; ?></p>
			<?php
		}
	}
	$rss = @fetch_rss('http://planet.wordpress.org/feed/');
	if ( isset($rss->items) && 0 != count($rss->items) ) {
		?>
		<div id="planetnews">
		<h3><?php _e('Other WordPress News'); ?> <a href="http://planet.wordpress.org/"><?php _e('more'); ?> &raquo;</a></h3>
		<ul>
		<?php
		$rss->items = array_slice($rss->items, 0, 20);
		foreach ($rss->items as $item ) {
			?>
			<li><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a></li>
			<?php
		}
		?>
		</ul>
		</div>
		<?php
	}
}
?>
<div style="clear: both">&nbsp;
<br clear="all" />
</div>
</div>

<?php
} // else $friend
require('./admin-footer.php');
?>
