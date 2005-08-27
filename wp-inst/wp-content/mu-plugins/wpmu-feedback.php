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
	global $current_user;
	?>
	<div id='feedbackform' style='display: none; position: absolute; top: 6em; right: 2em; width: 400px; background: #fff; padding: 5px; border: 1px solid #666;'>
	<div style='padding-left: 5px;text-align: right;'><a style='text-decoration: none' href="javascript: hide_feedback_form()">X</a>&nbsp;</div>
	<h2>Bugs and Hugs</h2>
	<form id='wpmufeedbackform' action='wpmu-feedback.php' method='post' style="padding: 0 1em; ">
	<input type='hidden' name='page' value='<?php echo urlencode( $_SERVER["REQUEST_URI"] ) ?>' />
	<p>Your thoughts and opinions are the most single most important thing to the future of WordPress.com. Please share what you love, what you hate, or what you think is a little quirky:</p>
	<p><textarea name='feedbackproblem' rows='6' cols='40' style="width: 95%"></textarea>
	</p>
	<p class="submit"><input value='Send Feedback &raquo;' type='button' id="feedbacksubmit" onclick='javascript: return newfeedback()' /></p>
	</form>
	<p id="feedbackstatus" style="padding: .5em 1em"></p>
</div>
	<?php
}

function feedbackform_javascript() {
	?>
<script type="text/javascript" src="tw-sack.js"></script>
<script type="text/javascript">
function addEvent(obj, evType, fn) {
if (obj.addEventListener) {
	obj.addEventListener(evType, fn, false); 
	return true;
	} else if (obj.attachEvent) {
	var r = obj.attachEvent('on'+evType, fn);
	return r;
	} else {
	return false;
	}
}
function setup_feedback() {
	feedbacklink = document.createElement('a');
	feedbacklink.id = 'feedbacklink';
	feedbacklink.innerHTML = 'Feedback';
	var userinfo = document.getElementById( 'footer' );
	userinfo.appendChild(feedbacklink);
	addEvent(feedbacklink, 'click', function(e){toggle_feedback_form()});
	var feedbackForm = document.getElementById('feedbackform');
	feedbackForm.state = 'up';
	sub = 	document.getElementById( 'feedbacksubmit' );
	addEvent(sub, 'click', function(e){sub.style.display = 'none';});
}

// addLoadEvent from admin-header
addLoadEvent( setup_feedback );

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
	var form = document.getElementById( 'wpmufeedbackform' );
	form.style.display = 'none';
	p.innerHTML = 'Feedback Sent. Thanks for your help! Please let us know again if there is ever anything on your mind.';
	Fat.fade_element( p.id );
}


function newfeedback() {
	var form = document.getElementById( 'wpmufeedbackform' );
	var feedback = 'req=' + form.page.value + '&feedback=' + form.feedbackproblem.value;
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
<style type="text/css">
#feedbacklink {
	position: absolute;
	top: 2.8em;
	right: 2em;
	display: block;
	padding: .3em .8em;
	background: #6da6d1;
	color: #fff;
	cursor: pointer;
}
</style>
	<?php
}
?>
