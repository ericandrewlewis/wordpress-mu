<?php // Do not delete these lines

/* $Id: wp-comments.php,v 1.3 2004/11/22 11:14:01 donncha Exp $ */

global $wpsmarty;
if ('wp-comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
die ('Please do not load this page directly. Thanks!');
$commentstext = '';
$withcomments = true;
if (($withcomments && $wpsmarty->template_exists( "blog-comments.html" ) ) or (is_single())) 
{
    unset( $t );
    if (!empty($post->post_password)) { // if there's a password
        if ($_COOKIE['wp-postpass_'.$cookiehash] != $post->post_password) {  // and it doesn't match the cookie
            ?><p><?php _e("Enter your password to view comments."); ?><p><?php
                return;
        }
    }
    $query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID = '$post->ID' AND comment_approved = '1' ORDER BY comment_date";
    $comments = $wpdb->get_results( $query );
    if( is_array( $comments ) )
    {
        foreach( $comments as $key => $comment )
        {
            $t[ $key ] = $comment;
            $t[ $key ]->comment_content = stripslashes( $t[ $key ]->comment_content );
        }
    }
    else
    {
        $t = $comments;
    }
    $wpsmarty->assign( "comments", $t);
    $wpsmarty->assign( "comment_author_url", $comment_author_url );
    $wpsmarty->assign( "comment_author", $comment_author );
    $wpsmarty->assign( "comment_author_email", $comment_author_email );
    $wpsmarty->assign( "req", $req );
    $wpsmarty->assign( "redirect_to", htmlspecialchars($_SERVER["REQUEST_URI"]) );
    $wpsmarty->assign( "post", $post );
    $wpsmarty->caching = false;
    if( is_single() == false )
    {
	if( $wpsmarty->template_exists( "blog-comments.html" ) )
	    $commentstext .= $wpsmarty->fetch( "blog-comments.html" );
    }
    else
    {
	$commentstext .= $wpsmarty->fetch( "comments.html" );
    }
    $wpsmarty->caching = true;
}
?>
