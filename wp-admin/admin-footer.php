
<div id="footer">
<?php
$footer_text = '<p>' . __('Thank you for creating with <a href="http://mu.wordpress.org/">WordPress MU</a>') . ' | ' . __('<a href="http://mu.wordpress.org/docs/">Documentation</a>') . '</p>';
echo apply_filters( 'admin_footer_text', $footer_text );
?>
</div>
<?php do_action('admin_footer', ''); ?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
