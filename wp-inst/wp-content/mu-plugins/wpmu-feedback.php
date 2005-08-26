<?php
// disabled by default!
return;
if( defined( "WP_INSTALLING" ) )
	return true;

add_action('admin_footer', 'feedbackform' );
add_action('admin_head', 'feedbackform_javascript' );

function wpmufeedback() {
	$msg = "Feedback from ".$_GET[ 'user_login' ]."\n\nHost: ".$_GET[ 'host' ]."\nBrowser: ".$_GET[ 'browser' ]."\nPage: " . $_GET[ 'req' ] . "\nFeedback: " . $_GET[ 'feedback' ] . "\n";
	// mail this somewhere!
}

function feedbackform() {
	?>
	<div id='feedbackform' style='display: none; position: absolute; top: 70px; right: 10px; height:200px; width: 400px; background: #fff; padding: 5px; border: 1px solid #333;'>
	<div style='padding-left: 5px;text-align: right;'><a style='text-decoration: none' href="javascript: hide_feedback_form()">X</a>&nbsp;</div>
	<h2>Feedback</h2>
	<form id='wpmufeedbackform' action='wpmu-feedback.php' method='post'>
	<input type='hidden' name='user_login' value='<?php echo $current_user->data->user_login ?>'>
	<input type='hidden' name='host' value='<?php echo $_SERVER["HTTP_HOST"] ?>'>
	<input type='hidden' name='browser' value='<?php echo $_SERVER["HTTP_USER_AGENT"] ?>'>
	<input type='hidden' name='page' value='<?php echo $_SERVER["REQUEST_URI"] ?>'>
	<p>Please describe your problem in as much detail as possible. User feedback is a very important part of developing any software system and we want to hear your ideas, annoyances and bug reports!</p>
	<p>Bugs can be hard to describe but here are some guidelines: <ul><li><a href="http://www.chiark.greenend.org.uk/~sgtatham/bugs.html">How to Report Bugs Effectively</a></li><li><a href="http://www.mozilla.org/quality/bug-writing-guidelines.html">Mozilla Bug Writing Guidelines</a></li></ul></p>
	<table>
	<tr><th align='left' valign='top'>Problem:</td><td valign='top'><textarea name='feedbackproblem' rows='5' cols='40'></textarea></td></tr>
	<tr>
	<td align='right'><input value='Submit' type='button' onclick='javascript: return newfeedback()'></td>
	<td align='right' id='feedbackstatus' valign='top'></td>
	</tr>
	</table>
	</form>
</div>
	<?php
}

function feedbackform_javascript() {
	?>
<script type="text/javascript" src="tw-sack.js"></script>
<script type="text/javascript">

function create_feedback_link() {
	feedbacklink = document.createElement('a');
	feedbacklink.name = 'feedbacklink';
	feedbacklink.id = 'feedbacklink';
	feedbacklink.innerHTML = 'Feedback';
	feedbacklink.href = 'javascript: toggle_feedback_form()';
	var userinfo = document.getElementById( 'user_info' );
	userinfo.innerHTML += '[';
	userinfo.appendChild(feedbacklink);
	userinfo.innerHTML += ']';
}

// addLoadEvent from admin-header
addLoadEvent(create_feedback_link);

var ajaxFeedback = new sack();

function show_feedback_form() {
	var feedbackform = document.getElementById( 'feedbackform' );
	feedbackform.style.display='';
}
function hide_feedback_form() {
	var feedbackform = document.getElementById( 'feedbackform' );
	feedbackform.style.display='none';
}
function toggle_feedback_form() {
	var feedbackform = document.getElementById( 'feedbackform' );
	if( feedbackform.style.display == 'table' ) {
		feedbackform.style.display='none';
	} else {
		feedbackform.style.display='table';
	}
}
function feedbackLoading() {
	var p = document.getElementById( 'feedbackstatus' );
	p.innerHTML = 'Sending Feedback...';
}

function feedbackLoaded() {
	var p = document.getElementById( 'feedbackstatus' );
	p.innerHTML = 'Feedback Sent. Thank you for your help!';
}


function newfeedback() {
	var form = document.getElementById( 'wpmufeedbackform' );
	var feedback = 'user_login=' + form.user_login.value + '&host=' + form.host.value + '&browser=' + form.browser.value + '&req=' + form.page.value + '&feedback=' + form.feedbackproblem.value;
	ajaxFeedback.requestFile = 'wpmu-feedback.php';
	ajaxFeedback.method = 'GET';
	ajaxFeedback.onLoading = feedbackLoading;
	ajaxFeedback.onLoaded = feedbackLoaded;
	//ajaxFeedback.onInteractive = newCatInteractive;
	//ajaxFeedback.onCompletion = feedbackCompletion;
	ajaxFeedback.runAJAX(feedback);
	return false;
}

</script>
	<?php
}
?>
