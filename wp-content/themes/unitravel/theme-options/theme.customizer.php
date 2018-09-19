<?php
/**
 * Theme customizer
 *
 * @package WordPress
 * @subpackage UNITRAVEL
 * @since UNITRAVEL 1.0
 */

//--------------------------------------------------------------
//-- Register Customizer Controls
//--------------------------------------------------------------

define('CUSTOMIZE_PRIORITY', 200);		// Start priority for the new controls

if (!function_exists('unitravel_customizer_register_controls')) {
	add_action( 'customize_register', 'unitravel_customizer_register_controls', 11 );
	function unitravel_customizer_register_controls( $wp_customize ) {

		// Setup standard WP Controls
		// ---------------------------------
		
		// Remove unused sections
		$wp_customize->remove_section( 'colors');
		$wp_customize->remove_section( 'static_front_page');

		// Reorder standard WP sections
		$sec = $wp_customize->get_panel( 'nav_menus' );
		if (is_object($sec)) $sec->priority = 30;
		$sec = $wp_customize->get_panel( 'widgets' );
		if (is_object($sec)) $sec->priority = 40;
		$sec = $wp_customize->get_section( 'title_tagline' );
		if (is_object($sec)) $sec->priority = 50;
		$sec = $wp_customize->get_section( 'background_image' );
		if (is_object($sec)) $sec->priority = 60;
		$sec = $wp_customize->get_section( 'header_image' );
		if (is_object($sec)) $sec->priority = 80;
		$sec = $wp_customize->get_section( 'custom_css' );
		if (is_object($sec)) {
			$sec->title = '* ' . $sec->title;
			$sec->priority = 2000;
		}
		
		// Modify standard WP controls
		$sec = $wp_customize->get_control( 'blogname' );
		if (is_object($sec)) $sec->description      = esc_html__('Use "[[" and "]]" to modify style and color of parts of the text, "||" to break current line', 'unitravel');
		$sec = $wp_customize->get_setting( 'blogname' );
		if (is_object($sec)) $sec->transport = 'postMessage';

		$sec = $wp_customize->get_setting( 'blogdescription' );
		if (is_object($sec)) $sec->transport = 'postMessage';
		
		$sec = $wp_customize->get_section( 'background_image' );
		if (is_object($sec)) {
			$sec->title = esc_html__('Background', 'unitravel');
			$sec->description = esc_html__('Used only if "Content - Body style" equal to "boxed"', 'unitravel');
		}
		
		// Move standard option 'Background Color' to the section 'Background Image'
		$wp_customize->add_setting( 'background_color', array(
			'default'        => get_theme_support( 'custom-background', 'default-color' ),
			'theme_supports' => 'custom-background',
			'transport'		 => 'postMessage',
			'sanitize_callback'    => 'sanitize_hex_color_no_hash',
			'sanitize_js_callback' => 'maybe_hash_hex_color',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'background_color', array(
			'label'   => esc_html__( 'Background color', 'unitravel' ),
			'section' => 'background_image',
		) ) );
		

		// Add Theme specific controls
		// ---------------------------------
		
		$panels = array('');
		$p = 0;
		$sections = array('');
		$s = 0;
		$i = 0;

		// Reload Theme Options before create controls
		if (is_admin()) {
			unitravel_storage_set('options_reloaded', true);
			unitravel_load_theme_options();
		}
		$options = unitravel_storage_get('options');
		
		foreach ($options as $id=>$opt) {
			
			$i++;
			
			if (!empty($opt['hidden'])) continue;
			
			if ($opt['type'] == 'panel') {

				$sec = $wp_customize->get_panel( $id );
				if ( is_object($sec) && !empty($sec->title) ) {
					$sec->title      = $opt['title'];
					$sec->description= $opt['desc'];
					if ( !empty($opt['priority']) )	$sec->priority = $opt['priority'];
				} else {
					$wp_customize->add_panel( esc_attr($id) , array(
						'title'      => $opt['title'],
						'description'=> $opt['desc'],
						'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i
					) );
				}
				array_push($panels, $id);
				$p++;

			} else if ($opt['type'] == 'panel_end') {

				array_pop($panels);
				$p--;

			} else if ($opt['type'] == 'section') {

				$sec = $wp_customize->get_section( $id );
				if ( is_object($sec) && !empty($sec->title) ) {
					$sec->title      = $opt['title'];
					$sec->description= $opt['desc'];
					if ( !empty($opt['priority']) )	$sec->priority = $opt['priority'];
				} else {
					$wp_customize->add_section( esc_attr($id) , array(
						'title'      => $opt['title'],
						'description'=> $opt['desc'],
						'panel'  => esc_attr($panels[$p]),
						'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i
					) );
				}
				array_push($sections, $id);
				$s++;

			} else if ($opt['type'] == 'section_end') {

				array_pop($sections);
				$s--;

			} else if ($opt['type'] == 'select') {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
					'type'     => 'select',
					'choices'  => $opt['options']
				) );

			} else if ($opt['type'] == 'radio') {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
					'type'     => 'radio',
					'choices'  => $opt['options']
				) );

			} else if ($opt['type'] == 'switch') {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( new UNITRAVEL_Customize_Switch_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority' => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
					'choices'  => $opt['options']
				) ) );

			} else if ($opt['type'] == 'checkbox') {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
					'type'     => 'checkbox'
				) );

			} else if ($opt['type'] == 'color') {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'sanitize_hex_color',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
				) ) );

			} else if ($opt['type'] == 'image') {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority' => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
				) ) );

			} else if (in_array($opt['type'], array('media', 'audio', 'video'))) {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority' => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
				) ) );

			} else if ($opt['type'] == 'button') {
			
				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );

				$wp_customize->add_control( new UNITRAVEL_Customize_Button_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'input_attrs' => array(
						'caption' => $opt['caption'],
						'action' => $opt['action']
					),
					'section'  => esc_attr($sections[$s]),
					'priority' => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
				) ) );

			} else if ($opt['type'] == 'info') {
			
				$wp_customize->add_setting( $id, array(
					'default'           => '',
					'sanitize_callback' => 'unitravel_sanitize_value',
					'transport'         => 'postMessage'
				) );

				$wp_customize->add_control( new UNITRAVEL_Customize_Info_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority' => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
				) ) );

			} else if ($opt['type'] == 'hidden') {
			
				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_html',
					'transport'         => 'postMessage'
				) );

				$wp_customize->add_control( new UNITRAVEL_Customize_Hidden_Control( $wp_customize, $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority' => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
				) ) );

			} else {	// if (in_array($opt['type'], array('text', 'textarea'))) {

				$wp_customize->add_setting( $id, array(
					'default'           => unitravel_get_theme_option($id),
					'sanitize_callback' => 'unitravel_sanitize_html',
					'transport'         => !isset($opt['refresh']) || $opt['refresh'] ? 'refresh' : 'postMessage'
				) );
			
				$wp_customize->add_control( $id, array(
					'label'    => $opt['title'],
					'description' => $opt['desc'],
					'section'  => esc_attr($sections[$s]),
					'priority'	 => !empty($opt['priority']) ? $opt['priority'] : CUSTOMIZE_PRIORITY+$i,
					'type'     => $opt['type']	//'text' | 'textarea'
				) );
			}

		}
	}
}


// Create custom controls for customizer
if (!function_exists('unitravel_customizer_custom_controls')) {
	add_action( 'customize_register', 'unitravel_customizer_custom_controls' );
	function unitravel_customizer_custom_controls( $wp_customize ) {
	
		class UNITRAVEL_Customize_Info_Control extends WP_Customize_Control {
			public $type = 'info';

			public function render_content() {
				?><label><?php
				if (!empty($this->label)) {
					?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
				}
				if (!empty($this->description)) {
					?><span class="customize-control-description desctiption"><?php echo esc_html( $this->description ); ?></span><?php
				}
				?></label><?php
			}
		}
	
		class UNITRAVEL_Customize_Switch_Control extends WP_Customize_Control {
			public $type = 'switch';

			public function render_content() {
				?><label><?php
				if (!empty($this->label)) {
					?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
				}
				if (!empty($this->description)) {
					?><span class="customize-control-description desctiption"><?php echo esc_html( $this->description ); ?></span><?php
				}
				if (is_array($this->choices) && count($this->choices)>0) {
					foreach ($this->choices as $k=>$v) {
						?><label><input type="radio" name="_customize-radio-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value="<?php echo esc_attr($k); ?>">
						<?php echo esc_html($v); ?></label><?php
					}
				}
				?></label><?php
			}
		}
	
		class UNITRAVEL_Customize_Button_Control extends WP_Customize_Control {
			public $type = 'button';

			public function render_content() {
				?><label><?php
				if (!empty($this->label)) {
					?><span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span><?php
				}
				if (!empty($this->description)) {
					?><span class="customize-control-description desctiption"><?php echo esc_html( $this->description ); ?></span><?php
				}
				?>
				<input type="button" 
						name="_customize-button-<?php echo esc_attr($this->id); ?>" 
						value="<?php echo esc_attr($this->input_attrs['caption']); ?>"
						data-action="<?php echo esc_attr($this->input_attrs['action']); ?>">
				</label>
				<?php
			}
		}

		class UNITRAVEL_Customize_Hidden_Control extends WP_Customize_Control {
			public $type = 'info';

			public function render_content() {
				?><input type="hidden" name="_customize-hidden-<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> value=""><?php
			}
		}
	
	}
}


// Sanitize plain value
if (!function_exists('unitravel_sanitize_value')) {
	function unitravel_sanitize_value($value) {
		return empty($value) ? $value : trim(strip_tags($value));
	}
}


// Sanitize html value
if (!function_exists('unitravel_sanitize_html')) {
	function unitravel_sanitize_html($value) {
		return empty($value) ? $value : wp_kses_post($value);
	}
}


//--------------------------------------------------------------
// Save custom settings in CSS file
//--------------------------------------------------------------

// Save CSS with custom colors and fonts after save custom options
if (!function_exists('unitravel_customizer_action_save_after')) {
	add_action('customize_save_after', 'unitravel_customizer_action_save_after');
	function unitravel_customizer_action_save_after($api=false) {

		// Get saved settings
		$settings = $api->settings();

		// Store new schemes colors
		$schemes = unitravel_unserialize($settings['scheme_storage']->value());
		if (is_array($schemes) && count($schemes) > 0) 
			unitravel_storage_set('schemes', $schemes);

		// Store new fonts parameters
		$fonts = unitravel_get_theme_fonts();
		foreach ($fonts as $tag=>$v) {
			foreach ($v as $css_prop=>$css_value) {
				if (in_array($css_prop, array('title', 'description'))) continue;
				$fonts[$tag][$css_prop] = $settings["{$tag}_{$css_prop}"]->value();
			}
		}
		unitravel_storage_set('theme_fonts', $fonts);

		// Regenerate CSS with new colors
		unitravel_customizer_save_css();
	}
}

// Save CSS with custom colors and fonts after switch theme
if (!function_exists('unitravel_customizer_action_switch_theme')) {
	add_action('after_switch_theme', 'unitravel_customizer_action_switch_theme');
	function unitravel_customizer_action_switch_theme() {
		// Remove condition if you want regenerate css after switch to this theme
		if (false) unitravel_customizer_save_css();
	}
}

// Save CSS with custom colors and fonts into custom.css
if (!function_exists('unitravel_customizer_save_css')) {
	add_action('trx_addons_action_save_options', 'unitravel_customizer_save_css');
	function unitravel_customizer_save_css() {
		$msg = 	'/* ' . esc_html__("ATTENTION! This file was generated automatically! Don't change it!!!", 'unitravel') 
				. "\n----------------------------------------------------------------------- */\n";

		// Save CSS with custom colors and fonts into custom.css
		$css = unitravel_customizer_get_css();
		$file = unitravel_get_file_dir('css/__colors.css');
		if (file_exists($file)) unitravel_fpc($file, $msg . $css );

		// Merge stylesheets
		$list = apply_filters( 'unitravel_filter_merge_styles', array() );
		$css = '';
		foreach ($list as $f) {
			$css .= unitravel_fgc(unitravel_get_file_dir($f));
		}
		if ( $css != '') {
			unitravel_fpc( unitravel_get_file_dir('css/__styles.css'), $msg . apply_filters( 'unitravel_filter_prepare_css', $css, true ) );
		}

		// Merge scripts
		$list = apply_filters( 'unitravel_filter_merge_scripts', array(
																	'js/skip-link-focus.js',
																	'js/bideo.js',
																	'js/_utils.js',
																	'js/_init.js'
																	)
							);
		$js = '';
		foreach ($list as $f) {
			$js .= unitravel_fgc(unitravel_get_file_dir($f));
		}
		if ( $js != '') {
			unitravel_fpc( unitravel_get_file_dir('js/__scripts.js'), $msg . apply_filters( 'unitravel_filter_prepare_js', $js, true ) );
		}
	}
}


//--------------------------------------------------------------
// Border radius settings
//--------------------------------------------------------------

// Return current theme-specific border radius for form's fields and buttons
if ( !function_exists( 'unitravel_get_border_radius' ) ) {
	function unitravel_get_border_radius() {
		$rad = str_replace(' ', '', unitravel_get_theme_option('border_radius'));
		if (empty($rad)) $rad = 0;
		return unitravel_prepare_css_value($rad); 
	}
}


//--------------------------------------------------------------
// Color schemes manipulations
//--------------------------------------------------------------

// Load saved values into color schemes
if (!function_exists('unitravel_load_schemes')) {
	add_action('unitravel_action_load_options', 'unitravel_load_schemes');
	function unitravel_load_schemes() {
		$schemes = unitravel_storage_get('schemes');
		$storage = unitravel_unserialize(unitravel_get_theme_option('scheme_storage'));
		if (is_array($storage) && count($storage) > 0)  {
			foreach ($storage as $k=>$v) {
				if (isset($schemes[$k])) {
					$schemes[$k] = $v;
				}
			}
			unitravel_storage_set('schemes', $schemes);
		}
	}
}

// Return specified color from current (or specified) color scheme
if ( !function_exists( 'unitravel_get_scheme_color' ) ) {
	function unitravel_get_scheme_color($color_name, $scheme = '') {
		if (empty($scheme)) $scheme = unitravel_get_theme_option( 'color_scheme' );
		if (empty($scheme) || unitravel_storage_empty('schemes', $scheme)) $scheme = 'default';
		$colors = unitravel_storage_get_array('schemes', $scheme, 'colors');
		return $colors[$color_name];
	}
}

// Return colors from current color scheme
if ( !function_exists( 'unitravel_get_scheme_colors' ) ) {
	function unitravel_get_scheme_colors($scheme = '') {
		if (empty($scheme)) $scheme = unitravel_get_theme_option( 'color_scheme' );
		if (empty($scheme) || unitravel_storage_empty('schemes', $scheme)) $scheme = 'default';
		return unitravel_storage_get_array('schemes', $scheme, 'colors');
	}
}


// Return schemes list
if ( !function_exists( 'unitravel_get_list_schemes' ) ) {
	function unitravel_get_list_schemes($prepend_inherit=false) {
		$list = array();
		$schemes = unitravel_storage_get('schemes');
		if (is_array($schemes) && count($schemes) > 0) {
			foreach ($schemes as $slug => $scheme) {
				$list[$slug] = $scheme['title'];
			}
		}
		return $prepend_inherit ? unitravel_array_merge(array('inherit' => esc_html__("Inherit", 'unitravel')), $list) : $list;
	}
}


//--------------------------------------------------------------
// Theme fonts
//--------------------------------------------------------------

// Load saved values into fonts list
if (!function_exists('unitravel_load_fonts')) {
	add_action('unitravel_action_load_options', 'unitravel_load_fonts');
	function unitravel_load_fonts() {
		// Fonts to load when theme starts
		$fonts = array();
		for ($i=1; $i<=unitravel_get_theme_setting('max_load_fonts'); $i++) {
			if (($name = unitravel_get_theme_option("load_fonts-{$i}-name")) != '') {
				$fonts[] = array(
					'name'	 => $name,
					'family' => unitravel_get_theme_option("load_fonts-{$i}-family"),
					'styles' => unitravel_get_theme_option("load_fonts-{$i}-styles")
				);
			}
		}
		unitravel_storage_set('load_fonts', $fonts);
		unitravel_storage_set('load_fonts_subset', unitravel_get_theme_option("load_fonts_subset"));
		
		// Font parameters of the main theme's elements
		$fonts = unitravel_get_theme_fonts();
		foreach ($fonts as $tag=>$v) {
			foreach ($v as $css_prop=>$css_value) {
				if (in_array($css_prop, array('title', 'description'))) continue;
				$fonts[$tag][$css_prop] = unitravel_get_theme_option("{$tag}_{$css_prop}");
			}
		}
		unitravel_storage_set('theme_fonts', $fonts);
	}
}

// Return slug of the loaded font
if (!function_exists('unitravel_get_load_fonts_slug')) {
	function unitravel_get_load_fonts_slug($name) {
		return str_replace(' ', '-', $name);
	}
}

// Return load fonts parameter's default value
if (!function_exists('unitravel_get_load_fonts_option')) {
	function unitravel_get_load_fonts_option($option_name) {
		$rez = '';
		$parts = explode('-', $option_name);
		$load_fonts = unitravel_storage_get('load_fonts');
		if ($parts[0] == 'load_fonts' && count($load_fonts) > $parts[1]-1 && isset($load_fonts[$parts[1]-1][$parts[2]])) {
			$rez = $load_fonts[$parts[1]-1][$parts[2]];
		}
		return $rez;
	}
}

// Return load fonts subset's default value
if (!function_exists('unitravel_get_load_fonts_subset')) {
	function unitravel_get_load_fonts_subset($option_name) {
		return unitravel_storage_get('load_fonts_subset');
	}
}

// Return load fonts list
if (!function_exists('unitravel_get_list_load_fonts')) {
	function unitravel_get_list_load_fonts($prepend_inherit=false) {
		$list = array();
		$load_fonts = unitravel_storage_get('load_fonts');
		if (is_array($load_fonts) && count($load_fonts) > 0) {
			foreach ($load_fonts as $font) {
				$list[sprintf('%s%s', 
								strpos($font['name'], ' ')!==false ? sprintf('"%s"', $font['name']) : $font['name'],
								!empty($font['family']) ? ', '.trim($font['family']): '')] = $font['name'];
			}
		}
		return $prepend_inherit ? unitravel_array_merge(array('inherit' => esc_html__("Inherit", 'unitravel')), $list) : $list;
	}
}

// Return font settings of the theme specific elements
if ( !function_exists( 'unitravel_get_theme_fonts' ) ) {
	function unitravel_get_theme_fonts() {
		return unitravel_storage_get('theme_fonts');
	}
}

// Return theme fonts parameter's default value
if (!function_exists('unitravel_get_theme_fonts_option')) {
	function unitravel_get_theme_fonts_option($option_name) {
		$rez = '';
		$parts = explode('_', $option_name);
		$theme_fonts = unitravel_storage_get('theme_fonts');
		if (!empty($theme_fonts[$parts[0]][$parts[1]])) {
			$rez = $theme_fonts[$parts[0]][$parts[1]];
		}
		// For the font-families update options list also
		if ($parts[1] == 'font-family') {
			unitravel_storage_set_array2('options', $option_name, 'options', unitravel_get_list_load_fonts(true));
		}
		return $rez;
	}
}


//--------------------------------------------------------------
// Customizer JS and CSS
//--------------------------------------------------------------

// Binds JS listener to make Customizer color_scheme control.
// Passes color scheme data as colorScheme global.
if ( !function_exists( 'unitravel_customizer_control_js' ) ) {
	add_action( 'customize_controls_enqueue_scripts', 'unitravel_customizer_control_js' );
	function unitravel_customizer_control_js() {
		wp_enqueue_style( 'unitravel-customizer', unitravel_get_file_url('theme-options/theme.customizer.css') );
		wp_enqueue_script( 'unitravel-customizer-color-scheme-control', unitravel_get_file_url('theme-options/theme.customizer.color-scheme.js'), array( 'customize-controls', 'iris', 'underscore', 'wp-util' ), null, true );
		wp_localize_script( 'unitravel-customizer-color-scheme-control', 'unitravel_color_schemes', unitravel_storage_get('schemes') );
		wp_localize_script( 'unitravel-customizer-color-scheme-control', 'unitravel_theme_fonts', unitravel_storage_get('theme_fonts') );
		wp_localize_script( 'unitravel-customizer-color-scheme-control', 'unitravel_customizer_vars', array(
			'max_load_fonts' => unitravel_get_theme_setting('max_load_fonts'),
			'msg_refresh' => esc_html__('Refresh', 'unitravel'),
			'msg_reset' => esc_html__('Reset', 'unitravel'),
			'msg_reset_confirm' => esc_html__('Are you sure you want to reset all Theme Options?', 'unitravel'),
			) );
		wp_localize_script( 'unitravel-customizer-color-scheme-control', 'unitravel_dependencies', unitravel_get_theme_dependencies() );
	}
}

// Binds JS handlers to make the Customizer preview reload changes asynchronously.
if ( !function_exists( 'unitravel_customizer_preview_js' ) ) {
	add_action( 'customize_preview_init', 'unitravel_customizer_preview_js' );
	function unitravel_customizer_preview_js() {
		wp_enqueue_script( 'unitravel-customize-preview', unitravel_get_file_url('theme-options/theme.customizer.preview.js'), array( 'customize-preview' ), null, true );
	}
}

// Output an Underscore template for generating CSS for the color scheme.
// The template generates the css dynamically for instant display in the Customizer preview.
if ( !function_exists( 'unitravel_customizer_css_template' ) ) {
	add_action( 'customize_controls_print_footer_scripts', 'unitravel_customizer_css_template' );
	function unitravel_customizer_css_template() {
		$colors = array(
			
			// Whole block border and background
			'bg_color'				=> '{{ data.bg_color }}',
			'bd_color'				=> '{{ data.bd_color }}',
			
			// Text and links colors
			'text'					=> '{{ data.text }}',
			'text_light'			=> '{{ data.text_light }}',
			'text_dark'				=> '{{ data.text_dark }}',
			'text_link'				=> '{{ data.text_link }}',
			'text_hover'			=> '{{ data.text_hover }}',
		
			// Alternative blocks (submenu, buttons, tabs, etc.)
			'alter_bg_color'		=> '{{ data.alter_bg_color }}',
			'alter_bg_hover'		=> '{{ data.alter_bg_hover }}',
			'alter_bd_color'		=> '{{ data.alter_bd_color }}',
			'alter_bd_hover'		=> '{{ data.alter_bd_hover }}',
			'alter_text'			=> '{{ data.alter_text }}',
			'alter_light'			=> '{{ data.alter_light }}',
			'alter_dark'			=> '{{ data.alter_dark }}',
			'alter_link'			=> '{{ data.alter_link }}',
			'alter_hover'			=> '{{ data.alter_hover }}',
		
			// Input fields (form's fields and textarea)
			'input_bg_color'		=> '{{ data.input_bg_color }}',
			'input_bg_hover'		=> '{{ data.input_bg_hover }}',
			'input_bd_color'		=> '{{ data.input_bd_color }}',
			'input_bd_hover'		=> '{{ data.input_bd_hover }}',
			'input_text'			=> '{{ data.input_text }}',
			'input_light'			=> '{{ data.input_light }}',
			'input_dark'			=> '{{ data.input_dark }}',

			// Inverse blocks (with background equal to the links color or one of accented colors)
			'inverse_text'			=> '{{ data.inverse_text }}',
			'inverse_light'			=> '{{ data.inverse_light }}',
			'inverse_dark'			=> '{{ data.inverse_dark }}',
			'inverse_link'			=> '{{ data.inverse_link }}',
			'inverse_hover'			=> '{{ data.inverse_hover }}',

			// Additional accented colors (if used in the current theme)
			// For example:
			// 'accent2'			=> '{{ data.accent2 }}',
			// 'accent2_hover'		=> '{{ data.accent2_hover }}',

		);

		$tmpl_holder = 'script';

		$schemes = array_keys(unitravel_get_list_schemes());
		if (count($schemes) > 0) {
			foreach ($schemes as $scheme) {
				echo '<' . esc_html($tmpl_holder) . ' type="text/html" id="tmpl-unitravel-color-scheme-'.esc_attr($scheme).'">'
						. unitravel_customizer_get_css( $colors, false, false, $scheme )
					. '</' . esc_html($tmpl_holder) . '>';
			}
		}


		// Fonts
		$fonts = unitravel_get_theme_fonts();
		if (is_array($fonts) && count($fonts) > 0) {
			foreach ($fonts as $tag => $font) {
				$fonts[$tag]['font-family']		= '{{ data["'.$tag.'"]["font-family"] }}';
				$fonts[$tag]['font-size']		= '{{ data["'.$tag.'"]["font-size"] }}';
				$fonts[$tag]['line-height']		= '{{ data["'.$tag.'"]["line-height"] }}';
				$fonts[$tag]['font-weight']		= '{{ data["'.$tag.'"]["font-weight"] }}';
				$fonts[$tag]['font-style']		= '{{ data["'.$tag.'"]["font-style"] }}';
				$fonts[$tag]['text-decoration']	= '{{ data["'.$tag.'"]["text-decoration"] }}';
				$fonts[$tag]['text-transform']	= '{{ data["'.$tag.'"]["text-transform"] }}';
				$fonts[$tag]['letter-spacing']	= '{{ data["'.$tag.'"]["letter-spacing"] }}';
				$fonts[$tag]['margin-top']		= '{{ data["'.$tag.'"]["margin-top"] }}';
				$fonts[$tag]['margin-bottom']	= '{{ data["'.$tag.'"]["margin-bottom"] }}';
			}
			echo '<'.trim($tmpl_holder).' type="text/html" id="tmpl-unitravel-fonts">'
					. trim(unitravel_customizer_get_css( false, $fonts, false, false ))
				. '</'.trim($tmpl_holder).'>';
		}

	}
}


// Add scheme name in each selector in the CSS (priority 100 - after complete css)
if (!function_exists('unitravel_customizer_add_scheme_in_css')) {
	add_action( 'unitravel_filter_get_css', 'unitravel_customizer_add_scheme_in_css', 100, 4 );
	function unitravel_customizer_add_scheme_in_css($css, $colors, $fonts, $scheme) {
		if ($colors && !empty($css['colors'])) {
			$rez = '';
			$in_comment = $in_rule = false;
			$allow = true;
			$scheme_class = sprintf('.scheme_%s ', $scheme);
			$self_class = '.scheme_self';
			$self_class_len = strlen($self_class);
			$css_str = str_replace(array('{{', '}}'), array('[[',']]'), $css['colors']);
			for ($i=0; $i<strlen($css_str); $i++) {
				$ch = $css_str[$i];
				if ($in_comment) {
					$rez .= $ch;
					if ($ch=='/' && $css_str[$i-1]=='*') {
						$in_comment = false;
						$allow = !$in_rule;
					}
				} else if ($in_rule) {
					$rez .= $ch;
					if ($ch=='}') {
						$in_rule = false;
						$allow = !$in_comment;
					}
				} else {
					if ($ch=='/' && $css_str[$i+1]=='*') {
						$rez .= $ch;
						$in_comment = true;
					} else if ($ch=='{') {
						$rez .= $ch;
						$in_rule = true;
					} else if ($ch==',') {
						$rez .= $ch;
						$allow = true;
					} else if (strpos(" \t\r\n", $ch)===false) {
						if ($allow) {
							$pos_comma = strpos($css_str, ',', $i+1);
							$pos_bracket = strpos($css_str, '{', $i+1);
							$pos = $pos_comma === false
										? $pos_bracket
										: ($pos_bracket === false
												? $pos_comma
												: min($pos_comma, $pos_bracket)
											);
							$selector = $pos > 0 ? substr($css_str, $i, $pos-$i) : '';
							if (strpos($selector, $self_class) !== false) {
								$rez .= str_replace($self_class, trim($scheme_class), $selector);
								$i += strlen($selector) - 1;
							} else {
								$rez .= $scheme_class . trim($ch);
							}
							$allow = false;
						} else
							$rez .= $ch;
					} else {
						$rez .= $ch;
					}
				}
			}
			$rez = str_replace(array('[[',']]'), array('{{', '}}'), $rez);
			$css['colors'] = $rez;
		}
		return $css;
	}
}
	



// -----------------------------------------------------------------
// -- Page Options section
// -----------------------------------------------------------------

if ( !function_exists('unitravel_init_meta_box') ) {
	add_action( 'after_setup_theme', 'unitravel_init_meta_box' );
	function unitravel_init_meta_box() {
		if ( is_admin() ) {
			add_action("admin_enqueue_scripts", 'unitravel_add_meta_box_scripts');
			add_action('save_post',			'unitravel_save_meta_box');
			add_action('add_meta_boxes',	'unitravel_add_meta_box');
		}
	}
}
	
// Load required styles and scripts for admin mode
if ( !function_exists( 'unitravel_add_meta_box_scripts' ) ) {
	add_action("admin_enqueue_scripts", 'unitravel_add_meta_box_scripts');
	function unitravel_add_meta_box_scripts() {
		// If current screen is 'Edit Page' - load fontello
		$screen = function_exists('get_current_screen') ? get_current_screen() : false;
		if (is_object($screen) && unitravel_allow_meta_box(!empty($screen->post_type) ? $screen->post_type : $screen->id)) {
			wp_enqueue_style( 'unitravel-fontello',  unitravel_get_file_url('css/fontello/fontello-embedded.css') );
			wp_enqueue_script('jquery-ui-tabs', false, array('jquery', 'jquery-ui'), null, true);
			wp_enqueue_script( 'unitravel-meta-box', unitravel_get_file_url('theme-options/theme.meta-box.js'), array('jquery'), null, true );
			wp_localize_script( 'unitravel-meta-box', 'unitravel_dependencies', unitravel_get_theme_dependencies() );
		}
	}
}


// Check if meta box is allow
if (!function_exists('unitravel_allow_meta_box')) {
	function unitravel_allow_meta_box($post_type) {
		return apply_filters('unitravel_filter_allow_meta_box', in_array($post_type, array('page', 'post')), $post_type);
	}
}

// Add meta box
if (!function_exists('unitravel_add_meta_box')) {
	//Handler of the add_action('add_meta_boxes', 'unitravel_add_meta_box');
	function unitravel_add_meta_box() {
		global $post_type;
		if (unitravel_allow_meta_box($post_type)) {
			add_meta_box(sprintf('unitravel_meta_box_%s', $post_type), esc_html__('Theme Options', 'unitravel'), 'unitravel_show_meta_box', $post_type, 'advanced', 'default');
		}
	}
}

// Callback function to show fields in meta box
if (!function_exists('unitravel_show_meta_box')) {
	function unitravel_show_meta_box() {
		global $post, $post_type;
		if (unitravel_allow_meta_box($post_type)) {
			// Load saved options 
			$meta = get_post_meta($post->ID, 'unitravel_options', true);
			$tabs_titles = $tabs_content = array();
			global $UNITRAVEL_STORAGE;
			// Refresh linked data if this field is controller for the another (linked) field
			// Do this before show fields to refresh data in the $UNITRAVEL_STORAGE
			foreach ($UNITRAVEL_STORAGE['options'] as $k=>$v) {
				if (!isset($v['override']) || strpos($v['override']['mode'], $post_type)===false) continue;
				if (!empty($v['linked'])) {
					$v['val'] = isset($meta[$k]) ? $meta[$k] : 'inherit';
					if (!empty($v['val']) && !unitravel_is_inherit($v['val']))
						unitravel_refresh_linked_data($v['val'], $v['linked']);
				}
			}
			// Show fields
			foreach ($UNITRAVEL_STORAGE['options'] as $k=>$v) {
				if (!isset($v['override']) || strpos($v['override']['mode'], $post_type)===false) continue;
				if (empty($v['override']['section']))
					$v['override']['section'] = esc_html__('General', 'unitravel');
				if (!isset($tabs_titles[$v['override']['section']])) {
					$tabs_titles[$v['override']['section']] = $v['override']['section'];
					$tabs_content[$v['override']['section']] = '';
				}
				$v['val'] = isset($meta[$k]) ? $meta[$k] : 'inherit';
				$tabs_content[$v['override']['section']] .= unitravel_show_meta_box_field($k, $v);
			}
			if (count($tabs_titles) > 0) {
				?>
				<div class="unitravel_meta_box">
					<input type="hidden" name="meta_box_post_nonce" value="<?php echo esc_attr(wp_create_nonce(admin_url())); ?>" />
					<input type="hidden" name="meta_box_post_type" value="<?php echo esc_attr($post_type); ?>" />
					<div id="unitravel_meta_box_tabs">
						<ul><?php
							$cnt = 0;
							foreach ($tabs_titles as $k=>$v) {
								$cnt++;
								?><li><a href="#unitravel_meta_box_<?php echo esc_attr($cnt); ?>"><?php echo esc_html($v); ?></a></li><?php
							}
						?></ul>
						<?php
							$cnt = 0;
							foreach ($tabs_content as $k=>$v) {
								$cnt++;
								?>
								<div id="unitravel_meta_box_<?php echo esc_attr($cnt); ?>" class="unitravel_meta_box_section">
									<?php unitravel_show_layout($v); ?>
								</div>
								<?php
							}
						?>
					</div>
				</div>
				<?php		
			}
		}
	}
}

// Display single option's field
if ( !function_exists('unitravel_show_meta_box_field') ) {
	function unitravel_show_meta_box_field($name, $field) {
		if ($field['type'] == 'hidden') return '';
		$inherit_state = unitravel_is_inherit($field['val']);
		$output = '<div class="unitravel_meta_box_item unitravel_meta_box_item_'.esc_attr($field['type']).' unitravel_meta_box_inherit_'.($inherit_state ? 'on' : 'off' ).'">'
						. '<h4 class="unitravel_meta_box_item_title">'
							. esc_html($field['title'])
							. '<span class="unitravel_meta_box_inherit_lock" id="unitravel_meta_box_inherit_'.esc_attr($name).'"></span>'
						. '</h4>'
						. '<div class="unitravel_meta_box_item_data">'
							. '<div class="unitravel_meta_box_item_field" data-param="'.esc_attr($name).'"'.(!empty($field['linked']) ? ' data-linked="'.esc_attr($field['linked']).'"' : '').'>';
		if ($field['type']=='checkbox') {
			$output .= '<label class="unitravel_meta_box_item_label">'
						. '<input type="checkbox" name="unitravel_meta_box_field_'.esc_attr($name).'" value="1"'.($field['val']==1 ? ' checked="checked"' : '').' />'
						. esc_html($field['title'])
					. '</label>';
		} else if ($field['type']=='switch' || $field['type']=='radio') {
			foreach ($field['options'] as $k=>$v) {
				$output .= '<label class="unitravel_meta_box_item_label">'
							. '<input type="radio" name="unitravel_meta_box_field_'.esc_attr($name).'" value="'.esc_attr($k).'"'.($field['val']==$k ? ' checked="checked"' : '').' />'
							. esc_html($v)
						. '</label>';
			}
		} else if ($field['type']=='text') {
			$output .= '<input type="text" name="unitravel_meta_box_field_'.esc_attr($name).'" value="'.esc_attr(unitravel_is_inherit($field['val']) ? '' : $field['val']).'" />';
		} else if ($field['type']=='textarea') {
			$output .= '<textarea name="unitravel_meta_box_field_'.esc_attr($name).'">'.esc_html(unitravel_is_inherit($field['val']) ? '' : $field['val']).'</textarea>';
		} else if ($field['type']=='select') {
			$output .= '<select size="1" name="unitravel_meta_box_field_'.esc_attr($name).'">';
			foreach ($field['options'] as $k=>$v) {
				$output .= '<option value="'.esc_attr($k).'"'.($field['val']==$k ? ' selected="selected"' : '').'>'.esc_html($v).'</option>';
			}
			$output .= '</select>';
		} else if (in_array($field['type'], array('image', 'media', 'video', 'audio'))) {
			$output .= '<input type="text" id="unitravel_meta_box_field_'.esc_attr($name).'" name="unitravel_meta_box_field_'.esc_attr($name).'" value="'.esc_attr(unitravel_is_inherit($field['val']) ? '' : $field['val']).'" />'
					. unitravel_show_custom_field('unitravel_meta_box_field_'.esc_attr($name).'_button', array(
														'type'				=> 'mediamanager',
														'data_type'			=> $field['type'],
														'linked_field_id'	=> 'unitravel_meta_box_field_'.esc_attr($name)),
														null)
					. '<div class="unitravel_meta_box_field_preview">'
						. (unitravel_is_inherit($field['val']) ? '' : ($field['val'] && $field['type']=='image' ? '<img src="' . esc_url($field['val']) . '" alt="">' : basename($field['val'])))
					. '</div>';
		}
		$output .= '<div class="unitravel_meta_box_inherit_cover'.(!$inherit_state ? ' unitravel_hidden' : '').'">'
							. '<span class="unitravel_meta_box_inherit_label">' . esc_html__('Inherit', 'unitravel') . '</span>'
							. '<input type="hidden" name="unitravel_meta_box_inherit_'.esc_attr($name).'" value="'.esc_attr($inherit_state ? 'inherit' : '').'" />'
						. '</div>'
					. '</div>'
					. '<div class="unitravel_meta_box_item_description">'
						. (!empty($field['override']['desc']) ? $field['override']['desc'] : $field['desc'])	// param 'desc' already processed with wp_kses()!
					. '</div>'
				. '</div>'
			. '</div>';
		return $output;
	}
}

// Save data from meta box
if (!function_exists('unitravel_save_meta_box')) {
	//Handler of the add_action('save_post', 'unitravel_save_meta_box');
	function unitravel_save_meta_box($post_id) {

		// verify nonce
		if ( !wp_verify_nonce( unitravel_get_value_gp('meta_box_post_nonce'), admin_url() ) )
			return $post_id;

		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		$post_type = isset($_POST['meta_box_post_type']) ? $_POST['meta_box_post_type'] : $_POST['post_type'];

		// check permissions
		$capability = 'page';
		$post_types = get_post_types( array( 'name' => $post_type), 'objects' );
		if (!empty($post_types) && is_array($post_types)) {
			foreach ($post_types  as $type) {
				$capability = $type->capability_type;
				break;
			}
		}
		if (!current_user_can('edit_'.($capability), $post_id)) {
			return $post_id;
		}

		// Save meta
		$meta = array();
		$options = unitravel_storage_get('options');
		foreach ($options as $k=>$v) {
			// Skip not overriden options
			if (!isset($v['override']) || strpos($v['override']['mode'], $post_type)===false) continue;
			// Skip inherited options
			if (!empty($_POST['unitravel_meta_box_inherit_' . $k])) continue;
			// Get option value from POST
			$meta[$k] = isset($_POST['unitravel_meta_box_field_' . $k])
							? $_POST['unitravel_meta_box_field_' . $k]
							: ($v['type']=='checkbox' ? 0 : '');
		}
		update_post_meta($post_id, 'unitravel_options', $meta);
		
		// Save separate meta options to search template pages
		if ($post_type=='page' && !empty($_POST['page_template']) && $_POST['page_template']=='blog.php') {
			update_post_meta($post_id, 'unitravel_options_post_type', isset($meta['post_type']) ? $meta['post_type'] : 'post');
			update_post_meta($post_id, 'unitravel_options_parent_cat', isset($meta['parent_cat']) ? $meta['parent_cat'] : 0);
		}
	}
}

// Refresh data in the linked field
// according the main field value
if (!function_exists('unitravel_refresh_linked_data')) {
	function unitravel_refresh_linked_data($value, $linked_name) {
		if ($linked_name == 'parent_cat') {
			$tax = unitravel_get_post_type_taxonomy($value);
			$terms = !empty($tax) ? unitravel_get_list_terms(false, $tax) : array();
			$terms = unitravel_array_merge(array(0 => esc_html__('- Select category -', 'unitravel')), $terms);
			unitravel_storage_set_array2('options', $linked_name, 'options', $terms);
		}
	}
}

// AJAX: Refresh data in the linked fields
if (!function_exists('unitravel_callback_get_linked_data')) {
	add_action('wp_ajax_unitravel_get_linked_data', 		'unitravel_callback_get_linked_data');
	add_action('wp_ajax_nopriv_unitravel_get_linked_data','unitravel_callback_get_linked_data');
	function unitravel_callback_get_linked_data() {
		if ( !wp_verify_nonce( unitravel_get_value_gp('nonce'), admin_url('admin-ajax.php') ) )
			die();
		$chg_name = $_REQUEST['chg_name'];
		$chg_value = $_REQUEST['chg_value'];
		$response = array('error' => '');
		if ($chg_name == 'post_type') {
			$tax = unitravel_get_post_type_taxonomy($chg_value);
			$terms = !empty($tax) ? unitravel_get_list_terms(false, $tax) : array();
			$response['list'] = unitravel_array_merge(array(0 => esc_html__('- Select category -', 'unitravel')), $terms);
		}
		echo json_encode($response);
		die();
	}
}



//--------------------------------------------------------------
//-- Load Options list and styles
//--------------------------------------------------------------
require_once UNITRAVEL_THEME_DIR . 'theme-options/theme.options.php';
require_once UNITRAVEL_THEME_DIR . 'theme-options/theme.styles.php';
?>