<?php
/**
 * The template for homepage posts with "Classic" style
 *
 * @package WordPress
 * @subpackage UNITRAVEL
 * @since UNITRAVEL 1.0
 */

unitravel_storage_set('blog_archive', true);

// Load scripts for 'Masonry' layout
if (substr(unitravel_get_theme_option('blog_style'), 0, 7) == 'masonry') {
	wp_enqueue_script( 'classie', unitravel_get_file_url('js/theme.gallery/classie.min.js'), array(), null, true );
	wp_enqueue_script( 'imagesloaded');
	wp_enqueue_script( 'masonry');
	wp_enqueue_script( 'unitravel-gallery-script', unitravel_get_file_url('js/theme.gallery/theme.gallery.js'), array(), null, true );
}

get_header(); 

if (have_posts()) {

	echo get_query_var('blog_archive_start');

	$unitravel_classes = 'posts_container '
						. (substr(unitravel_get_theme_option('blog_style'), 0, 7) == 'classic' ? 'columns_wrap' : 'masonry_wrap');
	$unitravel_stickies = is_home() ? get_option( 'sticky_posts' ) : false;
	$unitravel_sticky_out = is_array($unitravel_stickies) && count($unitravel_stickies) > 0 && get_query_var( 'paged' ) < 1;
	if ($unitravel_sticky_out) {
		?><div class="sticky_wrap columns_wrap"><?php	
	}
	if (!$unitravel_sticky_out) {
		if (unitravel_get_theme_option('first_post_large') && !is_paged() && !in_array(unitravel_get_theme_option('body_style'), array('fullwide', 'fullscreen'))) {
			the_post();
			get_template_part( 'content', 'excerpt' );
		}
		
		?><div class="<?php echo esc_attr($unitravel_classes); ?>"><?php
	}
	while ( have_posts() ) { the_post(); 
		if ($unitravel_sticky_out && !is_sticky()) {
			$unitravel_sticky_out = false;
			?></div><div class="<?php echo esc_attr($unitravel_classes); ?>"><?php
		}
		get_template_part( 'content', $unitravel_sticky_out && is_sticky() ? 'sticky' : 'classic' );
	}
	
	?></div><?php

	unitravel_show_pagination();

	echo get_query_var('blog_archive_end');

} else {

	if ( is_search() )
		get_template_part( 'content', 'none-search' );
	else
		get_template_part( 'content', 'none-archive' );

}

get_footer();
?>