
<div id="footer"><p><a href="http://wordpress.org/"><img src="../wp-includes/images/wp-small.png" alt="WordPress" /></a><br />
<a href="http://codex.wordpress.org/"><?php _e('Documentation'); ?></a> <br />
<?php printf(__('%s seconds'), number_format(timer_stop(), 2)); ?>
</p>

</div>
<?php check_for_pings(); ?>
<?php do_action('admin_footer', ''); ?>

</body>
</html>
