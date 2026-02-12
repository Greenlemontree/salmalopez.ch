<?php
/**
 * Template for displaying single project
 *
 * @package portfolio-dev
 */

get_header();

$details = portfolio_salma_get_project_details( get_the_ID() );
$media = portfolio_salma_get_project_media( get_the_ID() );
$tags = get_the_terms( get_the_ID(), 'project_tag' );

// Check project type by tag
$is_video_project = false;
$is_photography_project = false;
if ( $tags && ! is_wp_error( $tags ) ) {
	foreach ( $tags as $tag ) {
		if ( $tag->slug === 'video' ) {
			$is_video_project = true;
			break;
		}
		if ( $tag->slug === 'photography' ) {
			$is_photography_project = true;
			break;
		}
	}
}

// Get video-specific data
$color_graded_url = portfolio_salma_get_color_graded_image( get_the_ID() );
$log_url = portfolio_salma_get_log_image( get_the_ID() );
$bts_images = portfolio_salma_get_bts_images( get_the_ID() );
$extracts = portfolio_salma_get_extracts( get_the_ID() );
$scouting_images = portfolio_salma_get_scouting_images( get_the_ID() );

// Get photography-specific data
$material_image = portfolio_salma_get_material_image( get_the_ID() );
$lab_sketches = portfolio_salma_get_lab_sketches( get_the_ID() );

$project_class = $is_video_project ? 'is-video-project' : ( $is_photography_project ? 'is-photography-project' : '' );
?>

<main id="primary" class="site-main single-project <?php echo $project_class; ?>">

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if ( $is_video_project ) : ?>
			<!-- VIDEO PROJECT LAYOUT -->

			<!-- 1. Film Extracts Section -->
			<?php if ( ! empty( $extracts ) ) : ?>
				<section class="project-extracts" data-component="film-extracts">
					<div class="extracts-grid extracts-grid-<?php echo count( $extracts ); ?>">
						<?php foreach ( $extracts as $extract_url ) : ?>
							<div class="extract-item">
								<video
									src="<?php echo esc_url( $extract_url ); ?>"
									autoplay
									loop
									muted
									playsinline
									disablepictureinpicture>
								</video>
								<div class="extract-hover-button">see video</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<!-- 2. Title & Description Section -->
			<section class="project-title-section">
				<div class="project-title-content">
					<div class="project-meta">
						<?php if ( $details['year'] ) : ?>
							<span class="project-year"><?php echo esc_html( $details['year'] ); ?></span>
						<?php endif; ?>
						<?php if ( $details['year'] && $details['category'] ) : ?>
							<span class="project-meta-separator">-</span>
						<?php endif; ?>
						<?php if ( $details['category'] ) : ?>
							<span class="project-category"><?php echo esc_html( $details['category'] ); ?></span>
						<?php endif; ?>
					</div>

					<h1 class="project-title"><?php the_title(); ?></h1>

					<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
						<div class="project-tags">
							<?php foreach ( $tags as $tag ) : ?>
								<span class="project-tag"><?php echo esc_html( $tag->name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="project-description">
						<?php the_content(); ?>
					</div>

					<?php if ( $details['tools'] ) : ?>
						<div class="project-tools">
							<div class="tools-list">
								<?php
								$tools_array = array_map( 'trim', explode( ',', $details['tools'] ) );
								foreach ( $tools_array as $tool ) :
									?>
									<span class="tool-tag"><?php echo esc_html( $tool ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $details['website'] || $details['youtube'] ) : ?>
						<div class="project-links-inline">
							<?php if ( $details['website'] ) : ?>
								<a href="<?php echo esc_url( $details['website'] ); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
									View Website
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="7" y1="17" x2="17" y2="7"></line>
										<polyline points="7 7 17 7 17 17"></polyline>
									</svg>
								</a>
							<?php endif; ?>
							<?php if ( $details['youtube'] ) : ?>
								<a href="<?php echo esc_url( $details['youtube'] ); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
									Watch on YouTube
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="7" y1="17" x2="17" y2="7"></line>
										<polyline points="7 7 17 7 17 17"></polyline>
									</svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<!-- 3. LOG/Color Graded Comparison Slider (unchanged) -->
			<?php if ( $color_graded_url && $log_url ) : ?>
				<div class="project-comparison-slider">
					<div class="comparison-container" data-component="comparison-slider">
						<img src="<?php echo esc_url( $color_graded_url ); ?>" alt="Color Graded" class="comparison-image-after">
						<img src="<?php echo esc_url( $log_url ); ?>" alt="LOG" class="comparison-image-before">
						<div class="comparison-slider-handle">
							<div class="comparison-slider-line"></div>
							<div class="comparison-slider-button">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<polyline points="15 18 9 12 15 6"></polyline>
								</svg>
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<polyline points="9 18 15 12 9 6"></polyline>
								</svg>
							</div>
						</div>
					</div>
					<div class="comparison-labels">
						<span class="comparison-label-left">Color Graded</span>
						<span class="comparison-label-right">LOG</span>
					</div>
				</div>
			<?php endif; ?>

			<!-- 4. Location Scouting Section -->
			<?php if ( ! empty( $scouting_images ) ) : ?>
				<section class="project-scouting">
					<div class="scouting-layout">
						<?php foreach ( $scouting_images as $index => $image_url ) : ?>
							<div class="scouting-item scouting-item-<?php echo $index; ?>">
								<div class="scouting-pin">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="#EAFF00" stroke="none">
										<path d="M12 0C7.58 0 4 3.58 4 8c0 5.25 8 13 8 13s8-7.75 8-13c0-4.42-3.58-8-8-8zm0 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/>
									</svg>
								</div>
								<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy">
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<!-- 5. Final Video with Cover -->
			<?php
			$video_url = '';
			foreach ( $media as $item ) {
				if ( $item['type'] === 'video' ) {
					$video_url = $item['url'];
					break;
				}
			}
			if ( $video_url ) : ?>
				<section class="project-final-video" data-component="video-cover">
					<div class="video-cover-wrapper">
						<video
							src="<?php echo esc_url( $video_url ); ?>"
							<?php if ( has_post_thumbnail() ) : ?>
								poster="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ); ?>"
							<?php endif; ?>
							playsinline
							preload="metadata">
						</video>
						<div class="video-play-button">play</div>
					</div>
				</section>
			<?php endif; ?>

		<?php elseif ( $is_photography_project ) : ?>
			<!-- PHOTOGRAPHY PROJECT LAYOUT -->

			<!-- 1. Cover Frame -->
			<?php if ( has_post_thumbnail() ) : ?>
				<section class="photo-cover-frame">
					<?php the_post_thumbnail( 'full' ); ?>
				</section>
			<?php endif; ?>

			<!-- 2. Title & Description + Material Photo -->
			<section class="project-title-section">
				<div class="project-title-content">
					<div class="project-meta">
						<?php if ( $details['year'] ) : ?>
							<span class="project-year"><?php echo esc_html( $details['year'] ); ?></span>
						<?php endif; ?>
						<?php if ( $details['year'] && $details['category'] ) : ?>
							<span class="project-meta-separator">-</span>
						<?php endif; ?>
						<?php if ( $details['category'] ) : ?>
							<span class="project-category"><?php echo esc_html( $details['category'] ); ?></span>
						<?php endif; ?>
					</div>

					<h1 class="project-title"><?php the_title(); ?></h1>

					<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
						<div class="project-tags">
							<?php foreach ( $tags as $tag ) : ?>
								<span class="project-tag"><?php echo esc_html( $tag->name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="photo-title-body">
						<div class="project-description">
							<?php the_content(); ?>
						</div>

						<?php if ( $material_image ) : ?>
							<div class="material-image">
								<img src="<?php echo esc_url( $material_image ); ?>" alt="<?php esc_attr_e( 'Material & Equipment', 'portfolio-salma' ); ?>" loading="lazy">
							</div>
						<?php endif; ?>
					</div>

					<?php if ( $details['tools'] ) : ?>
						<div class="project-tools">
							<div class="tools-list">
								<?php
								$tools_array = array_map( 'trim', explode( ',', $details['tools'] ) );
								foreach ( $tools_array as $tool ) :
									?>
									<span class="tool-tag"><?php echo esc_html( $tool ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $details['website'] || $details['youtube'] ) : ?>
						<div class="project-links-inline">
							<?php if ( $details['website'] ) : ?>
								<a href="<?php echo esc_url( $details['website'] ); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
									View Website
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="7" y1="17" x2="17" y2="7"></line>
										<polyline points="7 7 17 7 17 17"></polyline>
									</svg>
								</a>
							<?php endif; ?>
							<?php if ( $details['youtube'] ) : ?>
								<a href="<?php echo esc_url( $details['youtube'] ); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
									Watch on YouTube
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="7" y1="17" x2="17" y2="7"></line>
										<polyline points="7 7 17 7 17 17"></polyline>
									</svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<!-- 3. Lab Sketches -->
			<?php if ( ! empty( $lab_sketches ) ) : ?>
				<section class="photo-lab-sketches">
					<div class="lab-sketches-layout">
						<?php foreach ( $lab_sketches as $index => $sketch ) : ?>
							<div class="lab-sketch-item">
								<div class="lab-sketch-pin">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="#EAFF00" stroke="none">
										<path d="M12 0C7.58 0 4 3.58 4 8c0 5.25 8 13 8 13s8-7.75 8-13c0-4.42-3.58-8-8-8zm0 11c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/>
									</svg>
									<?php if ( ! empty( $sketch['name'] ) ) : ?>
										<span class="lab-sketch-name"><?php echo esc_html( $sketch['name'] ); ?></span>
									<?php endif; ?>
								</div>
								<img src="<?php echo esc_url( $sketch['url'] ); ?>" alt="<?php echo esc_attr( $sketch['name'] ); ?>" loading="lazy">
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<!-- 4. Photo Mosaic -->
			<?php if ( ! empty( $media ) ) : ?>
				<section class="photo-mosaic">
					<div class="mosaic-grid">
						<?php foreach ( $media as $item ) : ?>
							<?php if ( $item['type'] === 'image' ) : ?>
								<div class="mosaic-item">
									<img src="<?php echo esc_url( $item['url'] ); ?>" alt="" loading="lazy" class="gallery-lightbox-trigger">
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

		<?php else : ?>
			<!-- DEFAULT PROJECT LAYOUT -->

			<!-- 1. Featured Image (full-width) -->
			<?php if ( has_post_thumbnail() ) : ?>
				<section class="project-featured-image">
					<?php the_post_thumbnail( 'full' ); ?>
				</section>
			<?php endif; ?>

			<!-- 2. Title & Description Section -->
			<section class="project-title-section">
				<div class="project-title-content">
					<div class="project-meta">
						<?php if ( $details['year'] ) : ?>
							<span class="project-year"><?php echo esc_html( $details['year'] ); ?></span>
						<?php endif; ?>
						<?php if ( $details['year'] && $details['category'] ) : ?>
							<span class="project-meta-separator">-</span>
						<?php endif; ?>
						<?php if ( $details['category'] ) : ?>
							<span class="project-category"><?php echo esc_html( $details['category'] ); ?></span>
						<?php endif; ?>
					</div>

					<h1 class="project-title"><?php the_title(); ?></h1>

					<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
						<div class="project-tags">
							<?php foreach ( $tags as $tag ) : ?>
								<span class="project-tag"><?php echo esc_html( $tag->name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="project-description">
						<?php the_content(); ?>
					</div>

					<?php if ( $details['tools'] ) : ?>
						<div class="project-tools">
							<div class="tools-list">
								<?php
								$tools_array = array_map( 'trim', explode( ',', $details['tools'] ) );
								foreach ( $tools_array as $tool ) :
									?>
									<span class="tool-tag"><?php echo esc_html( $tool ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $details['website'] || $details['youtube'] ) : ?>
						<div class="project-links-inline">
							<?php if ( $details['website'] ) : ?>
								<a href="<?php echo esc_url( $details['website'] ); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
									View Website
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="7" y1="17" x2="17" y2="7"></line>
										<polyline points="7 7 17 7 17 17"></polyline>
									</svg>
								</a>
							<?php endif; ?>
							<?php if ( $details['youtube'] ) : ?>
								<a href="<?php echo esc_url( $details['youtube'] ); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
									Watch on YouTube
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="7" y1="17" x2="17" y2="7"></line>
										<polyline points="7 7 17 7 17 17"></polyline>
									</svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>

			<!-- 3. Media Gallery -->
			<?php if ( ! empty( $media ) ) : ?>
				<section class="project-gallery">
					<div class="gallery-grid">
						<?php foreach ( $media as $item ) : ?>
							<div class="gallery-item" data-type="<?php echo esc_attr( $item['type'] ); ?>">
								<?php if ( $item['type'] === 'video' ) : ?>
									<video src="<?php echo esc_url( $item['url'] ); ?>" controls playsinline></video>
								<?php else : ?>
									<img src="<?php echo esc_url( $item['url'] ); ?>" alt="" loading="lazy" class="gallery-lightbox-trigger">
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

		<?php endif; ?>

		<!-- Navigation -->
		<nav class="project-navigation">
			<?php
			$prev_post = get_previous_post();
			$next_post = get_next_post();
			?>
			<?php if ( $prev_post ) : ?>
				<a href="<?php echo get_permalink( $prev_post ); ?>" class="nav-prev">
					<span class="nav-label">Previous</span>
					<span class="nav-title"><?php echo get_the_title( $prev_post ); ?></span>
				</a>
			<?php else : ?>
				<span class="nav-prev nav-disabled"></span>
			<?php endif; ?>

			<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" class="nav-back">
				All Projects
			</a>

			<?php if ( $next_post ) : ?>
				<a href="<?php echo get_permalink( $next_post ); ?>" class="nav-next">
					<span class="nav-label">Next</span>
					<span class="nav-title"><?php echo get_the_title( $next_post ); ?></span>
				</a>
			<?php else : ?>
				<span class="nav-next nav-disabled"></span>
			<?php endif; ?>
		</nav>

	</article>

</main>

<?php get_footer(); ?>
