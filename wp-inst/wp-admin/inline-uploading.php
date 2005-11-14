<?php

require_once('admin.php');

if (!current_user_can('edit_posts'))
	die(__('You do not have permission to edit posts.'));

$wpvarstoreset = array('action', 'post', 'all', 'last', 'link', 'sort', 'start', 'imgtitle', 'descr', 'object', 'flickrtag');

for ($i=0; $i<count($wpvarstoreset); $i += 1) {
	$wpvar = $wpvarstoreset[$i];
	if (!isset($$wpvar)) {
		if (empty($_POST["$wpvar"])) {
			if (empty($_GET["$wpvar"])) {
				$$wpvar = '';
			} else {
			$$wpvar = $_GET["$wpvar"];
			}
		} else {
			$$wpvar = $_POST["$wpvar"];
		}
	}
}

$post = (int) $post;
$images_width = 1;

function get_udims($width, $height) {
	if ( $height <= 96 && $width <= 128 )
		return array($width, $height);
	elseif ( $width / $height > 4 / 3 )
		return array(128, (int) ($height / $width * 128));
	else
		return array((int) ($width / $height * 96), 96);
}

switch($action) {
case 'delete':

wp_delete_object($object);

header("Location: ".basename(__FILE__)."?post=$post&all=$all&action=view&start=$start");
die;

case 'save':

$overrides = array('action'=>'save');

$file = wp_handle_upload($_FILES['image'], $overrides);

if( isset( $file[ 'error' ] ) == false )
	$file[ 'error' ] = apply_filters( "check_uploaded_file", $file[ 'error' ] );

if ( isset($file['error']) )
	die($file['error'] . '<a href="' . basename(__FILE__) . '?action=upload&post="' . $post . '">Back to Image Uploading</a>');

$url = $file['url'];
$file = $file['file'];
$filename = basename($file);

// Construct the object array
$object = array(
	'post_title' => $imgtitle ? $imgtitle : $filename,
	'post_content' => $descr,
	'post_status' => 'object',
	'post_parent' => $post,
	'post_type' => $_FILES['image']['type'],
	'guid' => $url
	);

// Save the data
$id = wp_attach_object($object, $post);

// Generate the object's postmeta.
$imagesize = getimagesize($file);
$imagedata['width'] = $imagesize['0'];
$imagedata['height'] = $imagesize['1'];
list($uwidth, $uheight) = get_udims($imagedata['width'], $imagedata['height']);
$imagedata['hwstring_small'] = "height='$uheight' width='$uwidth'";
$imagedata['file'] = $file;
$imagedata['thumb'] = "thumb-$filename";

add_post_meta($id, 'imagedata', $imagedata);

if ( $imagedata['width'] * $imagedata['height'] < 3 * 1024 * 1024 ) {
	if ( $imagedata['width'] > 128 && $imagedata['width'] >= $imagedata['height'] * 4 / 3 )
		$error = wp_create_thumbnail($file['file'], 128);
	elseif ( $imagedata['height'] > 96 )
		$error = wp_create_thumbnail($file, 96);
}

header("Location: ".basename(__FILE__)."?post=$post&all=$all&action=view&last=true");
die;

case 'upload':

$current_1 = ' class="current"';
$back = $next = false;
break;

case 'view':

// How many images do we show? How many do we query?
$num = 5;
$double = $num * 2;

if ( $post && empty($all) ) {
	$and_post = "AND post_parent = '$post'";
	$current_2 = ' class="current"';
} else {
	$current_3 = ' class="current"';
}

if ( $last )
	$start = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts WHERE post_status = 'object' AND left(post_type, 5) = 'image' $and_post") - $num;
else
	$start = (int) $start;

if ( $start < 0 )
	$start = 0;

if ( '' == $sort )
	$sort = "ID";

$images = $wpdb->get_results("SELECT ID, post_date, post_title, guid FROM $wpdb->posts WHERE post_status = 'object' AND left(post_type, 5) = 'image' $and_post ORDER BY $sort LIMIT $start, $double", ARRAY_A);

if ( count($images) > $num ) {
	$next = $start + count($images) - $num;
} else {
	$next = false;
}

if ( $start > 0 ) {
	$back = $start - $num;
	if ( $back < 1 )
		$back = '0';
} else {
	$back = false;
}

$i = 0;
$uwidth_sum = 0;
$images_html = '';
$images_style = '';
$images_script = '';
if ( count($images) > 0 ) {
	$images = array_slice( $images, 0, $num );
	$__delete = __('DELETE');
	$__subpost_on = __('SUBPOST <strong>ON</strong>');
	$__subpost_off = __('SUBPOST <strong>OFF</strong>');
	$__thumbnail_on = __('THUMBNAIL <strong>ON</strong>');
	$__thumbnail_off = __('THUMBNAIL <strong>OFF</strong>');
	$__no_thumbnail = __('<del>THUMBNAIL</del>');
	$__close = __('CLOSE');
	$__confirmdelete = __('Delete this photo from the server?');
	$__nothumb = __('There is no thumbnail associated with this photo.');
	$images_script .= "subposton = '$__subpost_on';\nsubpostoff = '$__subpost_off';\n";
	$images_script .= "thumbnailon = '$__thumbnail_on';\nthumbnailoff = '$__thumbnail_off';\n";
	foreach ( $images as $key => $image ) {
		$meta = get_post_meta($image['ID'], 'imagedata', true);
		if (!is_array($meta)) {
			wp_delete_object($image['ID']);
			continue;
		}
		$image = array_merge($image, $meta);
		if ( ($image['width'] > 128 || $image['height'] > 96) && !empty($image['thumb']) && file_exists(dirname($image['file']).'/'.$image['thumb']) ) {
			$src = str_replace(basename($image['guid']), '', $image['guid']) . $image['thumb'];
			$images_script .= "src".$i."a = '$src';\nsrc".$i."b = '".$image['guid']."';\n";
			$thumb = 'true';
			$thumbtext = $__thumbnail_on;
		} else {
			$src = $image['guid'];
			$thumb = 'false';
			$thumbtext = $__no_thumbnail;
		}
		list($image['uwidth'], $image['uheight']) = get_udims($image['width'], $image['height']);
		$height_width = 'height="'.$image['uheight'].'" width="'.$image['uwidth'].'"';
		$uwidth_sum += 128;
		$xpadding = (128 - $image['uwidth']) / 2;
		$ypadding = (96 - $image['uheight']) / 2;
		$object = $image['ID'];
		$images_style .= "#target$i img { padding: {$ypadding}px {$xpadding}px; }\n";
		$href = get_subpost_link($object);
		$images_script .= "href".$i."a = '$href';\nhref".$i."b = '{$image['guid']}';\n";
		$images_html .= "
<div id='target$i' class='imagewrap left'>
	<div id='popup$i' class='popup'>
		<a id=\"L$i\" onclick=\"toggleLink($i);return false;\" href=\"javascript:void();\">$__subpost_on</a>
		<a id=\"I$i\" onclick=\"if($thumb)toggleImage($i);else alert('$__nothumb');return false;\" href=\"javascript:void();\">$thumbtext</a>
		<a onclick=\"return confirm('$__confirmdelete')\" href=\"".basename(__FILE__)."?action=delete&amp;object=$object&amp;all=$all&amp;start=$start&amp;post=$post\">$__delete</a>
		<a onclick=\"popup.style.display='none';return false;\" href=\"javascript:void()\">$__close</a>
	</div>
	<a id=\"link$i\" class=\"imagelink\" href=\"$href\" onclick=\"imagePopup($i);return false;\" title=\"{$image['post_title']}\">		
		<img id='image$i' src='$src' alt='{$image['post_title']}' $height_width />
	</a>
</div>
";
		$i++;
	}
}

$images_width = $uwidth_sum + ( count($images) * 5 ) + 30;

break;

default:
die('This script was not meant to be called directly.');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="imagetoolbar" content="no" />
<script type="text/javascript">
/* Define any variables we'll need, such as alternate URLs. */
<?php echo $images_script; ?>

function validateImageName() {
/* This is more for convenience than security. Server-side validation is very thorough.*/
obj = document.getElementById('upload');
r = /.jpg$|.gif$|.png$/i;
if ( obj.value.match(r) )
return true;
alert('Please select a JPG, PNG or GIF file.');
return false;
}
function cancelUpload() {
o = document.getElementById('uploadForm');
o.method = 'GET';
o.action.value = 'view';
o.submit();
}
function imagePopup(i) {
if ( popup )
popup.style.display = 'none';
target = document.getElementById('target'+i);
popup = document.getElementById('popup'+i);
//popup.style.top = (target.offsetTop + 3) + 'px';
popup.style.left = (target.offsetLeft) + 'px';
popup.style.display = 'block';
}
function init() {
popup = false;
}
function toggleLink(n) {
	o=document.getElementById('link'+n);
	oi=document.getElementById('L'+n);
	if ( oi.innerHTML == subposton ) {
		o.href = eval('href'+n+'b');
		oi.innerHTML = subpostoff;
	} else {
		o.href = eval('href'+n+'a');
		oi.innerHTML = subposton;
	}
}
function toggleImage(n) {
	o = document.getElementById('image'+n);
	oi = document.getElementById('I'+n);
	if ( oi.innerHTML == thumbnailon ) {
		o.src = eval('src'+n+'b');
		oi.innerHTML = thumbnailoff;
	} else {
		o.src = eval('src'+n+'a');
		oi.innerHTML = thumbnailon;
	}
}
</script>
<style type="text/css">
body {
font: 13px "Lucida Grande", "Lucida Sans Unicode", Tahoma, Verdana;
border: none;
margin: 0px;
height: 150px;
background: rgb(223, 232, 241);
}
form {
margin: 6px 2px 0px 6px;
}
#wrap {
clear: both;
margin: 0px;
padding: 0px;
height: 133px;
width: 100%;
overflow: auto;
}
#images {
clear: both;
margin: 0px;
padding: 5px 15px;
height: 96px;
white-space: nowrap;
width: <?php echo $images_width; ?>px;
}
#images img {
background-color: rgb(209, 226, 239);
}
<?php echo $images_style; ?>
.imagewrap {
margin-right: 5px;
height: 96px;
overflow: hidden;
}
.imagewrap * {
margin: 0px;
padding: 0px;
border: 0px;
}
.imagewrap a, .imagewrap a img, .imagewrap a:hover img, .imagewrap a:visited img, .imagewrap a:active img {
text-decoration: none;
float: left;
/*display: block;*/
text-align: center;
}
#menu {
margin: 0px;
list-style: none;
background: rgb(109, 166, 209);
padding: 4px 0px 0px 8px;
text-align: left;
border-bottom: 3px solid rgb(68, 138, 189);
}
#menu li {
display: inline;
margin: 0px;
}
#menu a, #menu a:visited, #menu a:active {
padding: 1px 3px 3px;
text-decoration: none;
color: #234;
background: transparent;
}
#menu a:hover {
background: rgb(203, 214, 228);
color: #000;
}
#menu .current a, #menu .current a:hover, #menu .current a:visited, #menu .current a:active {
background: rgb(223, 232, 241);
padding-bottom: 3px;
color: #000;
border-right: 2px solid rgb(20, 86, 138);
}
.tip {
color: rgb(68, 138, 189);
padding: 1px 3px;
}
.inactive {
color: #579;
padding: 1px 3px;
}
.left {
float: left;
}
.right {
float: right;
}
.center {
text-align: center;
}
#menu li.spacer {
margin-left: 40px;
}
label {
float: left;
width: 18%;
}
#title, #descr {
width: 80%;
margin-top: 2px;
}
#descr {
height: 35px;
v-align: top;
}
#buttons {
width: 98%;
margin-top: 2px;
text-align: right;
}
.popup {
margin: 4px 4px;
padding: 3px;
position: absolute;
width: 114px;
height: 82px;
display: none;
background-color: rgb(223, 232, 241);
opacity: .90;
filter:alpha(opacity=90);
text-align: center;
}
.popup a, .popup a:visited, .popup a:active {
background-color: transparent;
display: block;
width: 100%;
text-decoration: none;
color: #246;
}
.popup a:hover {
background-color: #fff;
color: #000;
}
</style>
</head>
<body onload="init()">
<ul id="menu">
<li<?php echo $current_1; ?>><a href="<?php echo basename(__FILE__); ?>?action=upload&amp;post=<?php echo $post; ?>&amp;all=<?php echo $all; ?>"><?php _e('Upload File'); ?></a></li>
<li<?php echo $current_2; ?>><a href="<?php echo basename(__FILE__); ?>?action=view&amp;post=<?php echo $post; ?>"><?php _e('Browse Attached'); ?></a></li>
<li<?php echo $current_3; ?>><a href="<?php echo basename(__FILE__); ?>?action=view&amp;post=<?php echo $post; ?>&amp;all=true"><?php _e('Browse All'); ?></a></li>
<li> </li>
<?php if ( false !== $back ) : ?>
<li class="spacer"><a href="<?php echo basename(__FILE__); ?>?action=<?php echo $action; ?>&amp;post=<?php echo $post; ?>&amp;all=<?php echo $all; ?>&amp;start=0" title="<?php _e('First'); ?>">|&lt;</a></li>
<li><a href="<?php echo basename(__FILE__); ?>?action=<?php echo $action; ?>&amp;post=<?php echo $post; ?>&amp;all=<?php echo $all; ?>&amp;start=<?php echo $back; ?>" title="<?php _e('Back'); ?>">&lt;&lt;</a></li>
<?php else : ?>
<li class="inactive spacer">|&lt;</li>
<li class="inactive">&lt;&lt;</li>
<?php endif; ?>
<?php if ( false !== $next ) : ?>
<li><a href="<?php echo basename(__FILE__); ?>?action=<?php echo $action; ?>&amp;post=<?php echo $post; ?>&amp;all=<?php echo $all; ?>&amp;start=<?php echo $next; ?>" title="<?php _e('Next'); ?>">&gt;&gt;</a></li>
<li><a href="<?php echo basename(__FILE__); ?>?action=<?php echo $action; ?>&amp;post=<?php echo $post; ?>&amp;all=<?php echo $all; ?>&amp;last=true" title="<?php _e('Last'); ?>">&gt;|</a></li>
<?php else : ?>
<li class="inactive">&gt;&gt;</li>
<li class="inactive">&gt;|</li>
<?php endif; ?>
</ul>
<?php if ( $action == 'view' ) : ?>
<span class="left tip"><?php _e('Drag and drop photos to post'); ?></span>
<span class="right tip"><?php _e('Click photos for more options'); ?></span>
<div id="wrap">
<div id="images">
<?php echo $images_html; ?>
</div>
</div>
<?php elseif ( $action == 'upload' ) : ?>
	<?php if( apply_filters( "pre_upload_error", false ) == true ) {
		?><div class="center tip">Sorry, you have used your space allocation. Please delete some files to upload more files.</div><?php
	} else { ?>
<div class="tip"></div>
<form enctype="multipart/form-data" id="uploadForm" method="POST" action="<?php echo basename(__FILE__); ?>" onsubmit="return validateImageName()">
<label for="upload"><?php _e('Image:'); ?></label><input type="file" id="upload" name="image" onchange="validateImageName()" />
<label for="title"><?php _e('Title:'); ?></label><input type="text" id="title" name="imgtitle" />
<label for="descr"><?php _e('Description:'); ?></label><input type="textarea" name="descr" id="descr" value="" />
<input type="hidden" name="action" value="save" />
<input type="hidden" name="post" value="<?php echo $post; ?>" />
<input type="hidden" name="all" value="<?php echo $all; ?>" />
<div id="buttons">
<input type="submit" value="<?php _e('Upload'); ?>" />
<input type="button" value="<?php _e('Cancel'); ?>" onclick="cancelUpload()" />
</div>
</form>
<?php } ?>
<?php endif; ?>
</body>
</html>


