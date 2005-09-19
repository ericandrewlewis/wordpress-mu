<?php
add_action('wp_head', "header_js" );

function header_js() {
	?>
<script type="text/javascript">

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}
</script>
	<?php
}

?>
