<?php
/**
 * portfolio-salma Theme Customizer
 *
 * @package portfolio-salma
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function portfolio_salma_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'portfolio_salma_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'portfolio_salma_customize_partial_blogdescription',
			)
		);
	}

	/*
	 * Hero Section Settings
	 */
	$wp_customize->add_section(
		'portfolio_salma_hero_section',
		array(
			'title'       => __( 'Hero Section', 'portfolio-salma' ),
			'description' => __( 'Customize the homepage hero area.', 'portfolio-salma' ),
			'priority'    => 30,
		)
	);

	// Hero Image
	$wp_customize->add_setting(
		'portfolio_salma_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'portfolio_salma_hero_image',
			array(
				'label'       => __( 'Hero Background Image', 'portfolio-salma' ),
				'description' => __( 'Upload an image for the hero background. Recommended: 1920x1080 or larger.', 'portfolio-salma' ),
				'section'     => 'portfolio_salma_hero_section',
				'settings'    => 'portfolio_salma_hero_image',
			)
		)
	);

	// Skills Text
	$wp_customize->add_setting(
		'portfolio_salma_skills_text',
		array(
			'default'           => "ANIMATION\nWEB DESIGN\nUI/UX\nVIDEOGRAPHY\nILLUSTRATION",
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'portfolio_salma_skills_text',
		array(
			'type'        => 'textarea',
			'label'       => __( 'Skills / Keywords', 'portfolio-salma' ),
			'description' => __( 'Enter your skills or keywords, one per line. These appear as large text behind the hero mask.', 'portfolio-salma' ),
			'section'     => 'portfolio_salma_hero_section',
		)
	);

	// Hero Overlay Color
	$wp_customize->add_setting(
		'portfolio_salma_hero_overlay',
		array(
			'default'           => 'rgba(0, 0, 0, 0.3)',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'portfolio_salma_hero_overlay',
		array(
			'type'        => 'text',
			'label'       => __( 'Hero Overlay Color', 'portfolio-salma' ),
			'description' => __( 'CSS color value for overlay (e.g., rgba(0,0,0,0.3))', 'portfolio-salma' ),
			'section'     => 'portfolio_salma_hero_section',
		)
	);
}
add_action( 'customize_register', 'portfolio_salma_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function portfolio_salma_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function portfolio_salma_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function portfolio_salma_customize_preview_js() {
	wp_enqueue_script( 'portfolio-salma-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'portfolio_salma_customize_preview_js' );
