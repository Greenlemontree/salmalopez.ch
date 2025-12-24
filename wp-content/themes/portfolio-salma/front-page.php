<?php
/**
 * The front page template
 * Split-view portfolio with scratch effect
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

Scratch Overlay
<div class="scratch-container" id="scratchContainer">
    <canvas class="scratch-canvas" id="scratchCanvas"></canvas>
    <div class="cursor-indicator" id="cursorIndicator">
        <span class="cursor-text">scratch</span>
    </div>
</div>

<main class="portfolio-wrapper">
    
    <!-- Left Column - Project Details (Yellow) -->
    <div class="portfolio-left" id="projectDetails">
        <?php
        // Get the first project to display by default
        $first_project = new WP_Query(array(
            'post_type' => 'project',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        if ($first_project->have_posts()) :
            while ($first_project->have_posts()) : $first_project->the_post();
                $project_id = get_the_ID();
                $layout = get_post_meta($project_id, '_project_layout', true) ?: 'layout-1';
                $gallery = get_post_meta($project_id, '_project_gallery', true);
                $video_url = get_post_meta($project_id, '_project_video_url', true);
                
                // Get gallery images
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
                        <?php if (has_excerpt()) : ?>
                            <div class="project-description">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="project-featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($gallery_images)) : ?>
                            <div class="project-gallery">
                                <?php foreach ($gallery_images as $img) : ?>
                                    <div class="gallery-image">
                                        <img src="<?php echo esc_url($img['url']); ?>" alt="">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="project-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                    
                <?php elseif ($layout === 'layout-2') : ?>
                    <div class="layout-2-content">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="project-featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="project-info-block">
                            <?php if (has_excerpt()) : ?>
                                <div class="project-description">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="project-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($gallery_images)) : ?>
                            <div class="project-gallery">
                                <?php foreach ($gallery_images as $img) : ?>
                                    <div class="gallery-image">
                                        <img src="<?php echo esc_url($img['url']); ?>" alt="">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php elseif ($layout === 'layout-video') : ?>
                    <div class="layout-video-content">
                        <?php if (has_excerpt()) : ?>
                            <div class="project-description">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="project-featured-image">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($video_url) : ?>
                            <div class="project-actions">
                                <a href="<?php echo esc_url($video_url); ?>" class="play-video-btn" target="_blank">PLAY VIDEO</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($gallery_images)) : ?>
                            <div class="project-gallery">
                                <?php foreach ($gallery_images as $img) : ?>
                                    <div class="gallery-image">
                                        <img src="<?php echo esc_url($img['url']); ?>" alt="">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="project-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
        ?>
            <p class="no-projects">No projects yet.</p>
        <?php endif; ?>
    </div>

    <!-- Right Column - Project List (White) -->
    <div class="portfolio-right">
        
        <!-- Logo -->
        <div class="site-logo">
            <!-- Add your logo image here -->
        </div>

        <!-- Navigation -->
        <nav class="main-nav">
            <a href="#contact">CONTACT</a>
            <a href="#about">ABOUT</a>
            <a href="#work">WORK</a>
        </nav>

        <!-- Intro Text -->
        <div class="intro-text">
            <p>Hello my name is salma, also known as greenlemontree<br>
            I'm an Interactive Media Designer and illustrator based<br>
            in Geneva and this is my portfolio</p>
        </div>

        <!-- Projects List -->
        <div class="projects-list" id="work">
            <?php
            $projects = new WP_Query(array(
                'post_type' => 'project',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC'
            ));

            $first = true;
            if ($projects->have_posts()) :
                while ($projects->have_posts()) : $projects->the_post();
                    $category = get_post_meta(get_the_ID(), '_project_category', true);
                    $active_class = $first ? 'active' : '';
            ?>
                <div class="project-row <?php echo $active_class; ?>" data-project-id="<?php the_ID(); ?>">
                    <span class="project-title"><?php the_title(); ?></span>
                    <span class="project-category"><?php echo esc_html($category); ?></span>
                </div>
            <?php
                    $first = false;
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

    </div>

</main>

<?php wp_footer(); ?>
</body>
</html>