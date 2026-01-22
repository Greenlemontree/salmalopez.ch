<?php
/**
 * The template for displaying the Projects Archive
 *
 * Horizontal scrolling carousel with GSAP
 *
 * @package portfolio-salma
 */

get_header();
?>

<main id="primary" class="site-main projects-archive">

	<!-- Projects Horizontal Carousel -->
	<section class="projects-carousel-section">
		<div class="projects-carousel-wrapper">
			<div class="projects-carousel" id="projects-carousel">
				<?php
				$projects = new WP_Query( array(
					'post_type'      => 'project',
					'posts_per_page' => -1,
					'orderby'        => 'date',
					'order'          => 'DESC',
				) );

				if ( $projects->have_posts() ) :
					while ( $projects->have_posts() ) :
						$projects->the_post();
						$thumbnail_url   = get_the_post_thumbnail_url( get_the_ID(), 'large' );
						$project_details = portfolio_salma_get_project_details( get_the_ID() );

						// Get project tags
						$project_tags = get_the_terms( get_the_ID(), 'project_tag' );
						$tag_names = array();
						$tag_slugs = array();
						if ( ! empty( $project_tags ) && ! is_wp_error( $project_tags ) ) {
							foreach ( $project_tags as $tag ) {
								$tag_names[] = $tag->name;
								$tag_slugs[] = $tag->slug;
							}
						}
						$category_display = ! empty( $tag_names ) ? $tag_names[0] : $project_details['category'];
						?>
						<article class="project-carousel-item"
							data-tags="<?php echo esc_attr( implode( ',', $tag_slugs ) ); ?>"
							data-year="<?php echo esc_attr( $project_details['year'] ); ?>">
							<a href="<?php the_permalink(); ?>" class="project-carousel-link">
								<!-- Project thumbnail -->
								<div class="project-carousel-thumbnail">
									<?php if ( $thumbnail_url ) : ?>
										<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php the_title_attribute(); ?>" />
									<?php endif; ?>

									<!-- Yellow circle overlay with title -->
									<div class="project-circle-overlay">
										<span class="project-circle-title"><?php the_title(); ?></span>
									</div>
								</div>

								<!-- Project meta (shown for center item) -->
								<div class="project-carousel-meta">
									<span class="project-meta-year"><?php echo esc_html( $project_details['year'] ); ?></span>
									<?php if ( $category_display ) : ?>
										<span class="project-meta-separator">-</span>
										<span class="project-meta-category"><?php echo esc_html( $category_display ); ?></span>
									<?php endif; ?>
								</div>
							</a>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</div>

		<!-- Filter buttons -->
		<div class="projects-carousel-filters">
			<?php
			$all_tags = get_terms( array(
				'taxonomy'   => 'project_tag',
				'hide_empty' => true,
			) );
			?>
			<button type="button" class="carousel-filter-btn is-active" data-filter="all">All</button>
			<?php if ( ! empty( $all_tags ) && ! is_wp_error( $all_tags ) ) : ?>
				<?php foreach ( $all_tags as $tag ) : ?>
					<button type="button" class="carousel-filter-btn" data-filter="<?php echo esc_attr( $tag->slug ); ?>">
						<?php echo esc_html( $tag->name ); ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>

</main><!-- #main -->

<?php
get_footer();
