<?php

/*
Plugin Name: Recent Comments List
Plugin URI: http://freepressblog.org/plugins/recentComments.html

Description: This plugin will add a list of the most frequent comments posted to your blog. They are gathered in descending order (newest at the top), but then group them together by post title, so that comments from the same post are listed together. The list items will be links to the comments, and will contain the name of the commenter. Derived from the "Top/Recent Commenters" plugin by Scott Reilly (http://www.coffee2code.com/wp-plugins/)
Author: Jared Bangs
Author URI: http://freepressblog.org/
Version: 1.7
*/ 

function get_recent_comments($no_comments = 5, $comment_lenth = 5) {

	global $wpdb, $tablecomments, $tableposts, $id;
	if (!isset($tablecomments)) $tablecomments = $wpdb->comments;
	if (!isset($tableposts)) $tableposts = $wpdb->posts;

    $request = "SELECT ID, comment_ID, comment_content, comment_author, post_title FROM $tablecomments LEFT JOIN $tableposts ON $tableposts.ID=$tablecomments.comment_post_ID ";
	$request .= "WHERE post_status = 'publish' ";
	if(!$show_pass_post) $request .= "AND post_password ='' ";
	$request .= "AND comment_approved = '1' ORDER BY comment_ID DESC LIMIT $no_comments";
	$comments = $wpdb->get_results($request);
    $output = '';

    foreach ($comments as $comment) {

        $comment_author = stripslashes($comment->comment_author);

		if ($comment_author == "")
			$comment_author = "anonymous"; 

		$comment_content = strip_tags($comment->comment_content);
		$comment_content = stripslashes($comment_content);
		$words=split(" ",$comment_content); 
		$comment_excerpt = join(" ",array_slice($words,0,$comment_lenth));

        $permalink = get_permalink($comment->ID)."#comment-".$comment->comment_ID;
        $postlink = get_permalink($comment->ID);

		// Assemble link
		$post_title = '<a href="' . $postlink . '">' . stripslashes($comment->post_title) . '</a>';
		$comment_ID = stripslashes($comment->comment_ID);
		$postTitles[$post_title][$comment_ID] = '<li><span class="commentAuthor">' . $comment_author . ':</span> <a href="' . $permalink;
		$postTitles[$post_title][$comment_ID] .= '" title="View the entire comment by ' . $comment_author.'">' . $comment_excerpt.'...</a></li>';
    }

	$output = '<div id="recentcomments"><span id="recentCommentsTitle">Recent Comments:</span><ul id="recentcommentsList">' . "\n";

	foreach ($postTitles as $title => $commentPreview) {

		$output .= '<li><span class="recentCommentsPostTitle">' . $title . ':</span>' . "\n";
			$output .= '<ul>' . "\n";

	    	foreach ($commentPreview as $comID => $commentPreviewHTML) {
				$output .= $commentPreviewHTML . "\n";
			}

			$output .= '</ul>' . "\n";
		$output .= '</li>';
	}

	$output .= "\n" . '</ul>' . "\n" . '</div>' . "\n";

	return $output;
}

?>