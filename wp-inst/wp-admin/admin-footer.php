
<div id="footer"><p><a href="http://wordpress.org/" id="wordpress-logo"><img src="images/wordpress-logo.png" alt="WordPress" /></a></p>
<p>
<a href="http://codex.wordpress.org/"><?php _e('Documentation'); ?></a> <br />
<?php printf(__('%s seconds'), number_format(timer_stop(), 2)); ?>
</p>

</div>
<?php check_for_pings(); ?>
<?php do_action('admin_footer', ''); ?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
