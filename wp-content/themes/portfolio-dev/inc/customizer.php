<?php
/**
 * portfolio-salma Theme Customizer
 *
 * @package portfolio-salma
 */

/**
 * Custom control for sketchbook pages gallery
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
	class Portfolio_Salma_Sketchbook_Pages_Control extends WP_Customize_Control {
		public $type = 'sketchbook_pages';

		public function render_content() {
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
			</label>

			<div class="sketchbook-pages-preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0;">
				<?php
				$page_ids = $this->value();
				if ( ! empty( $page_ids ) ) {
					$ids_array = explode( ',', $page_ids );
					foreach ( $ids_array as $id ) {
						$img_url = wp_get_attachment_image_url( $id, 'thumbnail' );
						if ( $img_url ) {
							echo '<div class="page-preview-item" data-id="' . esc_attr( $id ) . '" style="position: relative; width: 80px; height: 80px;">';
							echo '<img src="' . esc_url( $img_url ) . '" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">';
							echo '<button type="button" class="remove-page" style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; line-height: 1;">&times;</button>';
							echo '</div>';
						}
					}
				}
				?>
			</div>

			<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr( $this->value() ); ?>" class="sketchbook-pages-input">
			<button type="button" class="button add-sketchbook-pages"><?php esc_html_e( 'Add Pages', 'portfolio-salma' ); ?></button>

			<script>
			(function($) {
				$(document).ready(function() {
					var frame;
					var controlId = '<?php echo esc_js( $this->id ); ?>';
					var $control = $('#customize-control-' + controlId);
					var $input = $control.find('.sketchbook-pages-input');
					var $preview = $control.find('.sketchbook-pages-preview');

					// Add pages
					$control.on('click', '.add-sketchbook-pages', function(e) {
						e.preventDefault();

						if (frame) {
							frame.open();
							return;
						}

						frame = wp.media({
							title: 'Select Sketchbook Pages',
							button: { text: 'Add Pages' },
							library: { type: 'image' },
							multiple: true
						});

						frame.on('select', function() {
							var selection = frame.state().get('selection');
							var currentIds = $input.val();
							var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

							selection.each(function(attachment) {
								attachment = attachment.toJSON();
								if (idsArray.indexOf(attachment.id.toString()) === -1) {
									idsArray.push(attachment.id);

									var thumbUrl = attachment.sizes && attachment.sizes.thumbnail
										? attachment.sizes.thumbnail.url
										: attachment.url;

									var html = '<div class="page-preview-item" data-id="' + attachment.id + '" style="position: relative; width: 80px; height: 80px;">';
									html += '<img src="' + thumbUrl + '" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">';
									html += '<button type="button" class="remove-page" style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; line-height: 1;">&times;</button>';
									html += '</div>';

									$preview.append(html);
								}
							});

							$input.val(idsArray.join(',')).trigger('change');
						});

						frame.open();
					});

					// Remove page
					$preview.on('click', '.remove-page', function() {
						var $item = $(this).closest('.page-preview-item');
						var removeId = $item.data('id').toString();
						var currentIds = $input.val();
						var idsArray = currentIds ? currentIds.split(',').filter(Boolean) : [];

						idsArray = idsArray.filter(function(id) {
							return id !== removeId;
						});

						$input.val(idsArray.join(',')).trigger('change');
						$item.remove();
					});
				});
			})(jQuery);
			</script>
			<?php
		}
	}
}

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

	/*
	 * About Section Settings
	 */
	$wp_customize->add_section(
		'portfolio_salma_about_section',
		array(
			'title'       => __( 'About Section', 'portfolio-salma' ),
			'description' => __( 'Customize the About / Hello section.', 'portfolio-salma' ),
			'priority'    => 31,
		)
	);

	// About Image
	$wp_customize->add_setting(
		'portfolio_salma_about_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'portfolio_salma_about_image',
			array(
				'label'       => __( 'About Photo', 'portfolio-salma' ),
				'description' => __( 'Upload a photo of yourself for the About section.', 'portfolio-salma' ),
				'section'     => 'portfolio_salma_about_section',
				'settings'    => 'portfolio_salma_about_image',
			)
		)
	);

	// About Heading
	$wp_customize->add_setting(
		'portfolio_salma_about_heading',
		array(
			'default'           => 'Hello!',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'portfolio_salma_about_heading',
		array(
			'type'        => 'text',
			'label'       => __( 'Heading', 'portfolio-salma' ),
			'description' => __( 'The main heading for the About section.', 'portfolio-salma' ),
			'section'     => 'portfolio_salma_about_section',
		)
	);

	// About Text
	$wp_customize->add_setting(
		'portfolio_salma_about_text',
		array(
			'default'           => "I'm Salma, a 21-year-old student pursuing a degree in Interactive Media Design. Currently, I am in my fourth year at CFP-Arts Geneva.\n\nI'm eager to expand my knowledge by diving deeper into the industry and working on personal projects. While most of my current work stems from the school curriculum, I aim to demonstrate what I've learned over the past two years through projects and collaborations with teachers who are experts in their fields.\n\nIn addition to my academic work, you'll also find some of my personal projects here, including digital illustrations and videography work.",
			'sanitize_callback' => 'wp_kses_post',
		)
	);

	$wp_customize->add_control(
		'portfolio_salma_about_text',
		array(
			'type'        => 'textarea',
			'label'       => __( 'About Text', 'portfolio-salma' ),
			'description' => __( 'Your bio text. Use double line breaks to create paragraphs.', 'portfolio-salma' ),
			'section'     => 'portfolio_salma_about_section',
		)
	);

	/*
	 * Sketchbook Section Settings
	 */
	$wp_customize->add_section(
		'portfolio_salma_sketchbook_section',
		array(
			'title'       => __( 'Sketchbook Section', 'portfolio-salma' ),
			'description' => __( 'Add your sketchbook cover and pages. This appears before the About section.', 'portfolio-salma' ),
			'priority'    => 32,
		)
	);

	// Enable Sketchbook Section
	$wp_customize->add_setting(
		'portfolio_salma_sketchbook_enable',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);

	$wp_customize->add_control(
		'portfolio_salma_sketchbook_enable',
		array(
			'type'        => 'checkbox',
			'label'       => __( 'Enable Sketchbook Section', 'portfolio-salma' ),
			'description' => __( 'Check to display the sketchbook on your homepage.', 'portfolio-salma' ),
			'section'     => 'portfolio_salma_sketchbook_section',
		)
	);

	// Sketchbook Cover Image
	$wp_customize->add_setting(
		'portfolio_salma_sketchbook_cover',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'portfolio_salma_sketchbook_cover',
			array(
				'label'       => __( 'Sketchbook Cover', 'portfolio-salma' ),
				'description' => __( 'Upload the cover image of your sketchbook.', 'portfolio-salma' ),
				'section'     => 'portfolio_salma_sketchbook_section',
				'mime_type'   => 'image',
			)
		)
	);

	// Sketchbook Pages (stored as comma-separated attachment IDs)
	$wp_customize->add_setting(
		'portfolio_salma_sketchbook_pages',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new Portfolio_Salma_Sketchbook_Pages_Control(
			$wp_customize,
			'portfolio_salma_sketchbook_pages',
			array(
				'label'       => __( 'Sketchbook Pages', 'portfolio-salma' ),
				'description' => __( 'Upload multiple sketchbook pages. These will appear when the sketchbook is opened.', 'portfolio-salma' ),
				'section'     => 'portfolio_salma_sketchbook_section',
			)
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
