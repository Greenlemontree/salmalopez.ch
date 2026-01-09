<?php
/**
 * portfolio-salma functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package portfolio-salma
 */

if ( ! defined( '_S_VERSION' ) ) {
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function portfolio_salma_setup() {
	load_theme_textdomain( 'portfolio-salma', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary Left (ABOUT, WORK)', 'portfolio-salma' ),
			'menu-2' => esc_html__( 'Primary Right (EMAIL, INSTAGRAM)', 'portfolio-salma' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-background',
		apply_filters(
			'portfolio_salma_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'portfolio_salma_setup' );

/**
 * Set the content width in pixels.
 */
function portfolio_salma_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'portfolio_salma_content_width', 640 );
}
add_action( 'after_setup_theme', 'portfolio_salma_content_width', 0 );

/**
 * Register widget area.
 */
function portfolio_salma_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'portfolio-salma' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'portfolio-salma' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'portfolio_salma_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function portfolio_salma_scripts() {
	wp_enqueue_style( 'portfolio-salma-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'portfolio-salma-style', 'rtl', 'replace' );
	wp_enqueue_script( 'portfolio-salma-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	// Front page scripts (GSAP, masks, animations)
	if ( is_front_page() ) {
		// GSAP Core
		wp_enqueue_script(
			'gsap',
			'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js',
			array(),
			'3.12.5',
			true
		);

		// GSAP ScrollTrigger
		wp_enqueue_script(
			'gsap-scrolltrigger',
			'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js',
			array( 'gsap' ),
			'3.12.5',
			true
		);

		// Hero mask interaction script
		wp_enqueue_script(
			'portfolio-salma-hero-mask',
			get_template_directory_uri() . '/js/hero-mask.js',
			array(),
			_S_VERSION,
			true
		);

		// Scroll animations script
		wp_enqueue_script(
			'portfolio-salma-animations',
			get_template_directory_uri() . '/js/animations.js',
			array( 'gsap', 'gsap-scrolltrigger' ),
			_S_VERSION,
			true
		);

		// Hero image slideshow
		wp_enqueue_script(
			'portfolio-salma-hero-slideshow',
			get_template_directory_uri() . '/js/hero-slideshow.js',
			array( 'gsap' ),
			_S_VERSION,
			true
		);

		// Project panel script
		wp_enqueue_script(
			'portfolio-salma-project-panel',
			get_template_directory_uri() . '/js/project-panel.js',
			array(),
			_S_VERSION,
			true
		);

		// Project filter script
		wp_enqueue_script(
			'portfolio-salma-project-filter',
			get_template_directory_uri() . '/js/project-filter.js',
			array(),
			_S_VERSION,
			true
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'portfolio_salma_scripts' );

require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/cpt-project.php';

if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Project Gallery and Video Meta Box
 */
require get_template_directory() . '/inc/project-meta.php';
