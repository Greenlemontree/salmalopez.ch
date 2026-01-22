<?php
/**
 * The template for displaying single Project posts
 *
 * @package portfolio-salma
 */

get_header();

$project_details = portfolio_salma_get_project_details( get_the_ID() );
$project_media   = portfolio_salma_get_project_media( get_the_ID() );
$thumbnail_url   = get_the_post_thumbnail_url( get_the_ID(), 'full' );

// Get project tags
$project_tags = get_the_terms( get_the_ID(), 'project_tag' );
$tag_names = array();
if ( ! empty( $project_tags ) && ! is_wp_error( $project_tags ) ) {
	foreach ( $project_tags as $tag ) {
		$tag_names[] = $tag->name;
	}
}
$category_display = ! empty( $tag_names ) ? $tag_names[0] : $project_details['category'];

// Get the first video from media for the hero
$hero_video = null;
$hero_image = $thumbnail_url;
foreach ( $project_media as $media ) {
	if ( $media['type'] === 'video' ) {
		$hero_video = $media['url'];
		break;
	}
}

// Get gallery images only
$gallery_images = array();
foreach ( $project_media as $media ) {
	if ( $media['type'] === 'image' ) {
		$gallery_images[] = $media['url'];
	}
}
?>

<main id="primary" class="site-main single-project">

	<!-- Hero Section -->
	<section class="project-hero">
		<!-- Corner dots -->
		<span class="project-dot project-dot-tl"></span>
		<span class="project-dot project-dot-tr"></span>
		<span class="project-dot project-dot-bl"></span>
		<span class="project-dot project-dot-br"></span>

		<div class="project-hero-content">
			<!-- Left: Video/Image with play button -->
			<div class="project-hero-media">
				<?php if ( $hero_video || $hero_image ) : ?>
					<div class="project-hero-thumbnail" data-video="<?php echo esc_url( $hero_video ); ?>">
						<?php if ( $hero_image ) : ?>
							<img src="<?php echo esc_url( $hero_image ); ?>" alt="<?php the_title_attribute(); ?>" />
						<?php endif; ?>

						<?php if ( $hero_video ) : ?>
							<!-- Play button -->
							<button type="button" class="project-play-btn" aria-label="<?php esc_attr_e( 'Play video', 'portfolio-salma' ); ?>">
								<span class="play-btn-oval"></span>
								<span class="play-btn-text">(play video)</span>
							</button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right: Title and Description -->
			<div class="project-hero-info">
				<h1 class="project-title"><?php the_title(); ?></h1>
				<div class="project-description">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $project_details['video_graded'] && $project_details['video_log'] ) : ?>
	<!-- Color Grading Comparison Section -->
	<section class="project-color-grading">
		<div class="color-grading-container">
			<div class="color-grading-slider" id="color-grading-slider">
				<!-- Color graded video (left/before) -->
				<div class="color-grading-side color-grading-graded">
					<video src="<?php echo esc_url( $project_details['video_graded'] ); ?>" loop muted playsinline></video>
					<span class="color-grading-label">Color graded</span>
				</div>

				<!-- LOG video (right/after) -->
				<div class="color-grading-side color-grading-log">
					<video src="<?php echo esc_url( $project_details['video_log'] ); ?>" loop muted playsinline></video>
					<span class="color-grading-label">LOG</span>
				</div>

				<!-- Slider handle -->
				<div class="color-grading-handle" id="color-grading-handle">
					<span class="handle-dot"></span>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $gallery_images ) ) : ?>
	<!-- Image Gallery Section -->
	<section class="project-gallery">
		<div class="project-gallery-grid">
			<?php foreach ( $gallery_images as $image_url ) : ?>
				<div class="project-gallery-item">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" />
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- Back Navigation -->
	<nav class="project-navigation">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="back-to-projects">
			<span>&larr;</span>
			<span>Back to projects</span>
		</a>
	</nav>

</main><!-- #main -->

<!-- Video Lightbox -->
<div id="video-lightbox" class="video-lightbox" aria-hidden="true">
	<div class="lightbox-overlay"></div>
	<div class="lightbox-content">
		<button type="button" class="lightbox-close" aria-label="<?php esc_attr_e( 'Close', 'portfolio-salma' ); ?>">
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
				<line x1="18" y1="6" x2="6" y2="18"></line>
				<line x1="6" y1="6" x2="18" y2="18"></line>
			</svg>
		</button>
		<video id="lightbox-video" controls playsinline></video>
	</div>
</div>

<?php
get_footer();
