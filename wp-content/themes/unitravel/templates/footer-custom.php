<?php
/**
 * The template to display default site footer
 *
 * @package WordPress
 * @subpackage UNITRAVEL
 * @since UNITRAVEL 1.0.10
 */

$unitravel_footer_scheme =  unitravel_is_inherit(unitravel_get_theme_option('footer_scheme')) ? unitravel_get_theme_option('color_scheme') : unitravel_get_theme_option('footer_scheme');
$unitravel_footer_id = str_replace('footer-custom-', '', unitravel_get_theme_option("footer_style"));
?>
<footer class="footer_wrap footer_custom footer_custom_<?php echo esc_attr($unitravel_footer_id); ?> scheme_<?php echo esc_attr($unitravel_footer_scheme); ?>">
	<?php
    // Custom footer's layout
    do_action('unitravel_action_show_layout', $unitravel_footer_id);
	?>
</footer><!-- /.footer_wrap -->
