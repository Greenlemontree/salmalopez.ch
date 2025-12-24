<?php
/**
 * Template for displaying single project
 */
get_header();

$year = get_post_meta(get_the_ID(), '_project_year', true);
$category = get_post_meta(get_the_ID(), '_project_category', true);
?>

<main class="single-project">
    
    <nav class="project-nav">
        <a href="<?php echo home_url(); ?>" class="back-link">← Back to projects</a>
    </nav>

    <article class="project-full">
        
        <header class="project-header">
            <h1 class="project-title"><?php the_title(); ?></h1>
            <div class="project-meta">
                <?php if ($year) : ?>
                    <span class="project-year"><?php echo esc_html($year); ?></span>
                <?php endif; ?>
                <?php if ($category) : ?>
                    <span class="project-category"><?php echo esc_html($category); ?></span>
                <?php endif; ?>
            </div>
        </header>

        <?php if (has_post_thumbnail()) : ?>
            <div class="project-featured-image">
                <?php the_post_thumbnail('full'); ?>
            </div>
        <?php endif; ?>

        <div class="project-content">
            <?php the_content(); ?>
        </div>

        <!-- Project navigation -->
        <nav class="project-pagination">
            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ?>
            <?php if ($prev_post) : ?>
                <a href="<?php echo get_permalink($prev_post); ?>" class="prev-project">
                    ← <?php echo get_the_title($prev_post); ?>
                </a>
            <?php endif; ?>
            <?php if ($next_post) : ?>
                <a href="<?php echo get_permalink($next_post); ?>" class="next-project">
                    <?php echo get_the_title($next_post); ?> →
                </a>
            <?php endif; ?>
        </nav>

    </article>

</main>

<?php get_footer(); ?>
