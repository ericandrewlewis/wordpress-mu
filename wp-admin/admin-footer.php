<?php
/**
 * WordPress Administration Template Footer
 *
 * @package WordPress
 * @subpackage Administration
 */
?>

<div class="clear" /></div></div><!-- wpbody-content -->
</div><!-- wpbody -->
<div class="clear" /></div></div><!-- wpcontent -->
</div><!-- wpwrap -->

<div id="footer">
<p><?php
do_action( 'in_admin_footer' );
$upgrade = '';
$footer_text = __('Thank you for creating with <a href="http://mu.wordpress.org/">WordPress MU</a>');
if( is_site_admin() ) {
	$upgrade = apply_filters( 'update_footer', '' );
	$footer_text .= ' ' . $wpmu_version;
}
$footer_text .= ' | ' . __('<a href="http://mu.wordpress.org/docs/">Documentation</a>');
echo apply_filters( 'admin_footer_text', $footer_text ) . ' ' . $upgrade;
?></p>
</div>
<?php do_action('admin_footer', ''); ?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>
