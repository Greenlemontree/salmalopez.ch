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
			'menu-1' => esc_html__( 'Primary', 'portfolio-salma' ),
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
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'portfolio_salma_scripts' );

require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/customizer.php';

if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

// =============================================
// REGISTER PROJECTS CUSTOM POST TYPE
// =============================================
function portfolio_register_projects() {
	register_post_type('project', array(
		'labels' => array(
			'name'               => 'Projects',
			'singular_name'      => 'Project',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Project',
			'edit_item'          => 'Edit Project',
			'view_item'          => 'View Project',
			'all_items'          => 'All Projects',
			'search_items'       => 'Search Projects',
			'not_found'          => 'No projects found',
		),
		'public'             => true,
		'has_archive'        => true,
		'menu_icon'          => 'dashicons-portfolio',
		'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
		'rewrite'            => array('slug' => 'projects'),
		'show_in_rest'       => true,
	));
}
add_action('init', 'portfolio_register_projects');

// =============================================
// PROJECT META BOX (Year, Category, Layout, Gallery, Video)
// =============================================
function portfolio_add_project_meta_boxes() {
	add_meta_box(
		'project_details',
		'Project Details',
		'portfolio_project_meta_box_html',
		'project',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'portfolio_add_project_meta_boxes');

function portfolio_project_meta_box_html($post) {
	$year = get_post_meta($post->ID, '_project_year', true);
	$category = get_post_meta($post->ID, '_project_category', true);
	$layout = get_post_meta($post->ID, '_project_layout', true);
	$gallery = get_post_meta($post->ID, '_project_gallery', true);
	$video_url = get_post_meta($post->ID, '_project_video_url', true);
	
	wp_nonce_field('portfolio_save_project_meta', 'portfolio_project_nonce');
	?>
	<p>
		<label><strong>Year:</strong></label><br>
		<input type="text" name="project_year" value="<?php echo esc_attr($year); ?>" placeholder="2024" style="width: 200px;">
	</p>
	<p>
		<label><strong>Category:</strong></label><br>
		<input type="text" name="project_category" value="<?php echo esc_attr($category); ?>" placeholder="video | web" style="width: 300px;">
	</p>
	<p>
		<label><strong>Layout:</strong></label><br>
		<select name="project_layout" style="width: 300px;">
			<option value="layout-1" <?php selected($layout, 'layout-1'); ?>>Layout 1 (Default)</option>
			<option value="layout-2" <?php selected($layout, 'layout-2'); ?>>Layout 2</option>
			<option value="layout-video" <?php selected($layout, 'layout-video'); ?>>Video</option>
		</select>
	</p>
	<p>
		<label><strong>Video URL:</strong> (only for Video layout)</label><br>
		<input type="url" name="project_video_url" value="<?php echo esc_url($video_url); ?>" placeholder="https://youtube.com/..." style="width: 100%;">
	</p>
	<hr style="margin: 20px 0;">
	<p>
		<label><strong>Project Gallery:</strong></label><br>
		<span class="description">Select multiple images for this project</span>
	</p>
	<div id="project-gallery-container">
		<ul id="project-gallery-list" style="display: flex; flex-wrap: wrap; gap: 10px; list-style: none; padding: 0; margin: 10px 0;">
			<?php
			if ($gallery) {
				$gallery_ids = explode(',', $gallery);
				foreach ($gallery_ids as $img_id) {
					$img_url = wp_get_attachment_image_url($img_id, 'thumbnail');
					if ($img_url) {
						echo '<li data-id="' . esc_attr($img_id) . '" style="position: relative;">';
						echo '<img src="' . esc_url($img_url) . '" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;">';
						echo '<button type="button" class="remove-gallery-image" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;">&times;</button>';
						echo '</li>';
					}
				}
			}
			?>
		</ul>
		<input type="hidden" name="project_gallery" id="project-gallery-ids" value="<?php echo esc_attr($gallery); ?>">
		<button type="button" id="add-gallery-images" class="button">Add Images</button>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		var galleryFrame;
		
		$('#add-gallery-images').on('click', function(e) {
			e.preventDefault();
			
			if (galleryFrame) {
				galleryFrame.open();
				return;
			}
			
			galleryFrame = wp.media({
				title: 'Select Gallery Images',
				button: { text: 'Add to Gallery' },
				multiple: true
			});
			
			galleryFrame.on('select', function() {
				var attachments = galleryFrame.state().get('selection').toJSON();
				var ids = $('#project-gallery-ids').val() ? $('#project-gallery-ids').val().split(',') : [];
				
				attachments.forEach(function(attachment) {
					if (ids.indexOf(attachment.id.toString()) === -1) {
						ids.push(attachment.id);
						var thumb = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
						$('#project-gallery-list').append(
							'<li data-id="' + attachment.id + '" style="position: relative;">' +
							'<img src="' + thumb + '" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;">' +
							'<button type="button" class="remove-gallery-image" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;">&times;</button>' +
							'</li>'
						);
					}
				});
				
				$('#project-gallery-ids').val(ids.join(','));
			});
			
			galleryFrame.open();
		});
		
		$(document).on('click', '.remove-gallery-image', function() {
			var li = $(this).parent();
			var id = li.data('id').toString();
			var ids = $('#project-gallery-ids').val().split(',').filter(function(i) { return i !== id; });
			$('#project-gallery-ids').val(ids.join(','));
			li.remove();
		});
	});
	</script>
	<?php
}

function portfolio_save_project_meta($post_id) {
	if (!isset($_POST['portfolio_project_nonce'])) { return; }
	if (!wp_verify_nonce($_POST['portfolio_project_nonce'], 'portfolio_save_project_meta')) { return; }
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
	
	if (isset($_POST['project_year'])) {
		update_post_meta($post_id, '_project_year', sanitize_text_field($_POST['project_year']));
	}
	if (isset($_POST['project_category'])) {
		update_post_meta($post_id, '_project_category', sanitize_text_field($_POST['project_category']));
	}
	if (isset($_POST['project_layout'])) {
		update_post_meta($post_id, '_project_layout', sanitize_text_field($_POST['project_layout']));
	}
	if (isset($_POST['project_gallery'])) {
		update_post_meta($post_id, '_project_gallery', sanitize_text_field($_POST['project_gallery']));
	}
	if (isset($_POST['project_video_url'])) {
		update_post_meta($post_id, '_project_video_url', esc_url_raw($_POST['project_video_url']));
	}
}
add_action('save_post_project', 'portfolio_save_project_meta');

// =============================================
// ENQUEUE PORTFOLIO STYLES AND SCRIPTS
// =============================================
function portfolio_enqueue_assets() {
	wp_enqueue_style('portfolio-css', get_template_directory_uri() . '/css/portfolio.css', array(), '1.2');

	if (is_front_page()) {
		wp_enqueue_style('scratch-css', get_template_directory_uri() . '/css/scratch.css', array(), '1.2');
		wp_enqueue_script('scratch-js', get_template_directory_uri() . '/js/scratch.js', array(), '1.2', true);
		wp_localize_script('scratch-js', 'scratchData', array('themePath' => get_template_directory_uri()));

		wp_enqueue_script('portfolio-js', get_template_directory_uri() . '/js/portfolio.js', array(), '1.2', true);
		wp_localize_script('portfolio-js', 'portfolioAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
	}
}
add_action('wp_enqueue_scripts', 'portfolio_enqueue_assets');

// =============================================
// AJAX HANDLER FOR PROJECT DETAILS
// =============================================
function get_project_details_ajax() {
	$project_id = intval($_POST['project_id']);
	
	$project = get_post($project_id);
	if (!$project) {
		echo '<p>Project not found</p>';
		wp_die();
	}

	$layout = get_post_meta($project_id, '_project_layout', true) ?: 'layout-1';
	$gallery = get_post_meta($project_id, '_project_gallery', true);
	$video_url = get_post_meta($project_id, '_project_video_url', true);
	$thumbnail = get_the_post_thumbnail($project_id, 'large');
	$excerpt = get_the_excerpt($project_id);
	$content = $project->post_content;
	
	$gallery_images = array();
	if ($gallery) {
		$gallery_ids = explode(',', $gallery);
		foreach ($gallery_ids as $img_id) {
			$img_url = wp_get_attachment_image_url($img_id, 'large');
			if ($img_url) {
				$gallery_images[] = array('url' => $img_url);
			}
		}
	}
	?>
	<div class="project-detail-content <?php echo esc_attr($layout); ?>">
		
		<?php if ($layout === 'layout-1') : ?>
			<div class="layout-1-content">
				<?php if ($excerpt) : ?>
					<div class="project-description"><?php echo wpautop(esc_html($excerpt)); ?></div>
				<?php endif; ?>
				<?php if ($thumbnail) : ?>
					<div class="project-featured-image"><?php echo $thumbnail; ?></div>
				<?php endif; ?>
				<?php if (!empty($gallery_images)) : ?>
					<div class="project-gallery">
						<?php foreach ($gallery_images as $img) : ?>
							<div class="gallery-image"><img src="<?php echo esc_url($img['url']); ?>" alt=""></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ($content) : ?>
					<div class="project-content"><?php echo wpautop(wp_kses_post($content)); ?></div>
				<?php endif; ?>
			</div>
			
		<?php elseif ($layout === 'layout-2') : ?>
			<div class="layout-2-content">
				<?php if ($thumbnail) : ?>
					<div class="project-featured-image"><?php echo $thumbnail; ?></div>
				<?php endif; ?>
				<div class="project-info-block">
					<?php if ($excerpt) : ?>
						<div class="project-description"><?php echo wpautop(esc_html($excerpt)); ?></div>
					<?php endif; ?>
					<?php if ($content) : ?>
						<div class="project-content"><?php echo wpautop(wp_kses_post($content)); ?></div>
					<?php endif; ?>
				</div>
				<?php if (!empty($gallery_images)) : ?>
					<div class="project-gallery">
						<?php foreach ($gallery_images as $img) : ?>
							<div class="gallery-image"><img src="<?php echo esc_url($img['url']); ?>" alt=""></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			
		<?php elseif ($layout === 'layout-video') : ?>
			<div class="layout-video-content">
				<?php if ($excerpt) : ?>
					<div class="project-description"><?php echo wpautop(esc_html($excerpt)); ?></div>
				<?php endif; ?>
				<?php if ($thumbnail) : ?>
					<div class="project-featured-image"><?php echo $thumbnail; ?></div>
				<?php endif; ?>
				<?php if ($video_url) : ?>
					<div class="project-actions">
						<a href="<?php echo esc_url($video_url); ?>" class="play-video-btn" target="_blank">PLAY VIDEO</a>
					</div>
				<?php endif; ?>
				<?php if (!empty($gallery_images)) : ?>
					<div class="project-gallery">
						<?php foreach ($gallery_images as $img) : ?>
							<div class="gallery-image"><img src="<?php echo esc_url($img['url']); ?>" alt=""></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ($content) : ?>
					<div class="project-content"><?php echo wpautop(wp_kses_post($content)); ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		
	</div>
	<?php
	wp_die();
}
add_action('wp_ajax_get_project_details', 'get_project_details_ajax');
add_action('wp_ajax_nopriv_get_project_details', 'get_project_details_ajax');

// =============================================
// FLUSH REWRITE RULES ON THEME ACTIVATION
// =============================================
function portfolio_rewrite_flush() {
	portfolio_register_projects();
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'portfolio_rewrite_flush');