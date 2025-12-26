<?php
/**
 * Custom Post Type: Project
 *
 * Registers the 'project' CPT and 'project_visibility' taxonomy
 * for portfolio items and Selected Works functionality.
 *
 * @package portfolio-salma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Project Custom Post Type
 */
function portfolio_salma_register_project_cpt() {
	$labels = array(
		'name'                  => _x( 'Projects', 'Post type general name', 'portfolio-salma' ),
		'singular_name'         => _x( 'Project', 'Post type singular name', 'portfolio-salma' ),
		'menu_name'             => _x( 'Projects', 'Admin Menu text', 'portfolio-salma' ),
		'name_admin_bar'        => _x( 'Project', 'Add New on Toolbar', 'portfolio-salma' ),
		'add_new'               => __( 'Add New', 'portfolio-salma' ),
		'add_new_item'          => __( 'Add New Project', 'portfolio-salma' ),
		'new_item'              => __( 'New Project', 'portfolio-salma' ),
		'edit_item'             => __( 'Edit Project', 'portfolio-salma' ),
		'view_item'             => __( 'View Project', 'portfolio-salma' ),
		'all_items'             => __( 'All Projects', 'portfolio-salma' ),
		'search_items'          => __( 'Search Projects', 'portfolio-salma' ),
		'parent_item_colon'     => __( 'Parent Projects:', 'portfolio-salma' ),
		'not_found'             => __( 'No projects found.', 'portfolio-salma' ),
		'not_found_in_trash'    => __( 'No projects found in Trash.', 'portfolio-salma' ),
		'featured_image'        => _x( 'Project Cover Image', 'Overrides the "Featured Image" phrase', 'portfolio-salma' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'portfolio-salma' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'portfolio-salma' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'portfolio-salma' ),
		'archives'              => _x( 'Project archives', 'The post type archive label', 'portfolio-salma' ),
		'insert_into_item'      => _x( 'Insert into project', 'Overrides the "Insert into post" phrase', 'portfolio-salma' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this project', 'Overrides the "Uploaded to this post" phrase', 'portfolio-salma' ),
		'filter_items_list'     => _x( 'Filter projects list', 'Screen reader text', 'portfolio-salma' ),
		'items_list_navigation' => _x( 'Projects list navigation', 'Screen reader text', 'portfolio-salma' ),
		'items_list'            => _x( 'Projects list', 'Screen reader text', 'portfolio-salma' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'project' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-portfolio',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'       => true, // Enable Gutenberg editor
	);

	register_post_type( 'project', $args );
}
add_action( 'init', 'portfolio_salma_register_project_cpt' );

/**
 * Register Project Visibility Taxonomy
 *
 * Used to mark projects as "Selected Works" for homepage display.
 * Can be extended with additional terms like "featured", "archived", etc.
 */
function portfolio_salma_register_project_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Visibility', 'taxonomy general name', 'portfolio-salma' ),
		'singular_name'              => _x( 'Visibility', 'taxonomy singular name', 'portfolio-salma' ),
		'search_items'               => __( 'Search Visibility', 'portfolio-salma' ),
		'all_items'                  => __( 'All Visibility Options', 'portfolio-salma' ),
		'edit_item'                  => __( 'Edit Visibility', 'portfolio-salma' ),
		'update_item'                => __( 'Update Visibility', 'portfolio-salma' ),
		'add_new_item'               => __( 'Add New Visibility', 'portfolio-salma' ),
		'new_item_name'              => __( 'New Visibility Name', 'portfolio-salma' ),
		'menu_name'                  => __( 'Visibility', 'portfolio-salma' ),
		'popular_items'              => __( 'Popular Visibility', 'portfolio-salma' ),
		'separate_items_with_commas' => __( 'Separate with commas', 'portfolio-salma' ),
		'add_or_remove_items'        => __( 'Add or remove visibility', 'portfolio-salma' ),
		'choose_from_most_used'      => __( 'Choose from most used', 'portfolio-salma' ),
		'not_found'                  => __( 'No visibility options found.', 'portfolio-salma' ),
	);

	$args = array(
		'hierarchical'      => true, // Like categories (checkboxes in admin)
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'project-visibility' ),
		'show_in_rest'      => true,
	);

	register_taxonomy( 'project_visibility', array( 'project' ), $args );
}
add_action( 'init', 'portfolio_salma_register_project_taxonomy' );

/**
 * Create default "Selected" term on theme activation
 */
function portfolio_salma_create_default_terms() {
	// Only run if the term doesn't exist
	if ( ! term_exists( 'selected', 'project_visibility' ) ) {
		wp_insert_term(
			'Selected',
			'project_visibility',
			array(
				'slug'        => 'selected',
				'description' => 'Projects marked as Selected Works appear on the homepage.',
			)
		);
	}
}
add_action( 'after_switch_theme', 'portfolio_salma_create_default_terms' );
add_action( 'init', 'portfolio_salma_create_default_terms', 20 ); // Also run on init for existing installs

/**
 * Add custom column for menu_order in admin
 */
function portfolio_salma_project_columns( $columns ) {
	$new_columns = array();
	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;
		if ( 'title' === $key ) {
			$new_columns['menu_order'] = __( 'Order', 'portfolio-salma' );
		}
	}
	return $new_columns;
}
add_filter( 'manage_project_posts_columns', 'portfolio_salma_project_columns' );

/**
 * Populate the Order column
 */
function portfolio_salma_project_column_content( $column, $post_id ) {
	if ( 'menu_order' === $column ) {
		$order = get_post_field( 'menu_order', $post_id );
		echo esc_html( $order );
	}
}
add_action( 'manage_project_posts_custom_column', 'portfolio_salma_project_column_content', 10, 2 );

/**
 * Make Order column sortable
 */
function portfolio_salma_project_sortable_columns( $columns ) {
	$columns['menu_order'] = 'menu_order';
	return $columns;
}
add_filter( 'manage_edit-project_sortable_columns', 'portfolio_salma_project_sortable_columns' );

/**
 * Helper function: Get Selected Works projects
 *
 * @param int $count Number of projects to retrieve (default 6).
 * @return WP_Query
 */
function portfolio_salma_get_selected_works( $count = 6 ) {
	$args = array(
		'post_type'      => 'project',
		'posts_per_page' => $count,
		'post_status'    => 'publish',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'project_visibility',
				'field'    => 'slug',
				'terms'    => 'selected',
			),
		),
	);

	return new WP_Query( $args );
}

/**
 * Flush rewrite rules on theme activation
 */
function portfolio_salma_rewrite_flush() {
	portfolio_salma_register_project_cpt();
	portfolio_salma_register_project_taxonomy();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'portfolio_salma_rewrite_flush' );
