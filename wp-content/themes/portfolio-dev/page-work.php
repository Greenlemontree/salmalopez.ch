<?php
/**
 * Template Name: Work Page
 * Template for displaying projects in horizontal scroll carousel
 *
 * @package portfolio-dev
 */

get_header();
?>

<main id="primary" class="site-main work-page">

    <!-- Horizontal Scroll Carousel Section -->
    <section class="work-carousel-section">

        <!-- Meta Display (Year + Category) - appears above center project -->
        <div class="carousel-meta">
            <span class="carousel-meta-year"></span>
            <span class="carousel-meta-separator">-</span>
            <span class="carousel-meta-category"></span>
        </div>

        <!-- Scroll Container (pinned during horizontal scroll) -->
        <div class="carousel-wrapper">
            <div class="carousel-track">
                <?php
                $projects = new WP_Query([
                    'post_type' => 'project',
                    'posts_per_page' => -1,
                    'orderby' => 'menu_order date',
                    'order' => 'ASC',
                ]);

                if ($projects->have_posts()) :
                    $project_index = 0;
                    while ($projects->have_posts()) : $projects->the_post();
                        $details = portfolio_salma_get_project_details(get_the_ID());
                        $tags = get_the_terms(get_the_ID(), 'project_tag');
                        $tag_slugs = $tags && !is_wp_error($tags) ? wp_list_pluck($tags, 'slug') : [];
                        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
                ?>
                        <article class="carousel-item"
                            data-project-id="<?php the_ID(); ?>"
                            data-index="<?php echo $project_index; ?>"
                            data-year="<?php echo esc_attr($details['year']); ?>"
                            data-category="<?php echo esc_attr($details['category']); ?>"
                            data-tags="<?php echo esc_attr(implode(',', $tag_slugs)); ?>"
                            data-permalink="<?php the_permalink(); ?>">

                            <!-- Project thumbnail with link -->
                            <a href="<?php the_permalink(); ?>" class="carousel-item-link">
                                <?php if ($thumbnail_url) : ?>
                                    <img src="<?php echo esc_url($thumbnail_url); ?>"
                                         alt="<?php echo esc_attr(get_the_title()); ?>"
                                         class="carousel-item-image">
                                <?php endif; ?>

                                <!-- Yellow dot with curved title inside -->
                                <div class="carousel-item-dot">
                                    <svg class="dot-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                        <!-- Yellow circle background -->
                                        <circle cx="60" cy="60" r="55" fill="#EAFF00"/>
                                        <!-- Full circle text path starting from left -->
                                        <defs>
                                            <path id="textPath-<?php echo $project_index; ?>"
                                                  d="M 15,60 A 45,45 0 1,0 15,59.99"
                                                  fill="none"/>
                                        </defs>
                                        <text class="dot-title-text">
                                            <textPath href="#textPath-<?php echo $project_index; ?>">
                                                <?php echo esc_html(get_the_title()); ?>
                                            </textPath>
                                        </text>
                                    </svg>
                                </div>
                            </a>
                        </article>
                <?php
                        $project_index++;
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>

        <!-- Filter Buttons (fixed at bottom) -->
        <div class="carousel-filters">
            <button type="button" class="filter-btn is-active" data-filter="all">All</button>
            <?php
            $project_tags = get_terms([
                'taxonomy' => 'project_tag',
                'hide_empty' => true,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);
            if ($project_tags && !is_wp_error($project_tags)) :
                foreach ($project_tags as $tag) :
            ?>
                <button type="button" class="filter-btn" data-filter="<?php echo esc_attr($tag->slug); ?>">
                    <?php echo esc_html($tag->name); ?>
                </button>
            <?php
                endforeach;
            endif;
            ?>
        </div>

    </section>

</main>

<?php get_footer(); ?>
