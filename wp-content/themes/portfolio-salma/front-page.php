<?php
/**
 * New One-Page Portfolio Template
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

<!-- Navigation -->
<nav class="main-navigation">
    <div class="nav-links">
        <a href="#about">ABOUT</a>
        <a href="#work">WORK</a>
        <a href="mailto:your@email.com">EMAIL</a>
        <a href="https://instagram.com/yourhandle" target="_blank">INSTAGRAM</a>
    </div>
</nav>

<!-- Hero Section with Masked Image -->
<section id="hero" class="hero-section">
    <div class="hero-image-container">
        <?php
        // Get featured image or default
        $hero_image = get_theme_mod('hero_image', get_template_directory_uri() . '/images/hero.jpg');
        ?>
        <img src="<?php echo esc_url($hero_image); ?>" alt="Hero" class="hero-bg-image">

        <!-- SVG Mask Shape (draggable points) -->
        <svg class="hero-mask" viewBox="0 0 1920 1080" preserveAspectRatio="xMidYMid slice">
            <defs>
                <clipPath id="heroClipPath">
                    <polygon id="heroPolygon" points="300,150 600,200 900,180 1100,300 950,650 400,600" />
                </clipPath>
            </defs>
            <image href="<?php echo esc_url($hero_image); ?>" width="1920" height="1080" clip-path="url(#heroClipPath)" />
        </svg>
    </div>

    <!-- Text Behind Mask -->
    <div class="hero-text">
        <h1>UI UX<br>ILLUSTRATION ANIMATION<br>VIDEOGRAPHY CREATIVE<br>DIRECTION</h1>
    </div>
</section>

<!-- About Section -->
<section id="about" class="about-section">
    <div class="about-container">
        <!-- Left: Photo + Text -->
        <div class="about-left">
            <div class="about-image">
                <?php
                $about_image = get_theme_mod('about_image', get_template_directory_uri() . '/images/about.jpg');
                ?>
                <img src="<?php echo esc_url($about_image); ?>" alt="About Salma">
            </div>
            <div class="about-text">
                <h2>Hello!</h2>
                <?php
                $about_content = get_theme_mod('about_text', 'Your about text here...');
                echo wpautop($about_content);
                ?>
            </div>
        </div>

        <!-- Right: Selected Works Grid with Mask -->
        <div class="about-right">
            <h3>Selected Works</h3>

            <!-- SVG Mask over grid -->
            <div class="works-grid-container">
                <!-- Mask overlay -->
                <svg class="works-mask" viewBox="0 0 800 800" preserveAspectRatio="none">
                    <defs>
                        <mask id="worksMask">
                            <rect width="800" height="800" fill="white"/>
                            <polygon id="worksMaskPolygon" points="100,100 400,80 700,150 650,600 150,550" fill="black" />
                        </mask>
                    </defs>
                    <rect width="800" height="800" fill="rgba(0,0,0,0.8)" mask="url(#worksMask)" />
                </svg>

                <!-- Works Grid -->
                <div class="works-grid">
                    <?php
                    $selected_works = new WP_Query(array(
                        'post_type' => 'selected_work',
                        'posts_per_page' => 6,
                        'orderby' => 'menu_order',
                        'order' => 'ASC'
                    ));

                    if ($selected_works->have_posts()) :
                        while ($selected_works->have_posts()) : $selected_works->the_post();
                            $linked_project_id = get_post_meta(get_the_ID(), '_linked_project_id', true);
                            $external_url = get_post_meta(get_the_ID(), '_external_url', true);

                            // Determine the link
                            if ($linked_project_id) {
                                $link = get_permalink($linked_project_id);
                            } elseif ($external_url) {
                                $link = $external_url;
                            } else {
                                $link = '#';
                            }
                    ?>
                        <a href="<?php echo esc_url($link); ?>" class="work-item" <?php if ($external_url) echo 'target="_blank"'; ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium'); ?>
                            <?php else : ?>
                                <div class="work-placeholder"></div>
                            <?php endif; ?>
                            <span class="work-title"><?php the_title(); ?></span>
                        </a>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <p class="no-works">No selected works yet. Add some in WordPress admin.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Work Section (placeholder for future) -->
<section id="work" class="work-section">
    <!-- Will add more sections here -->
</section>

<?php wp_footer(); ?>

<!-- Draggable Points Script -->
<script>
// Make SVG polygon points draggable
document.addEventListener('DOMContentLoaded', () => {
    const heroPolygon = document.getElementById('heroPolygon');
    const worksMaskPolygon = document.getElementById('worksMaskPolygon');

    if (heroPolygon) {
        makeDraggable(heroPolygon);
    }
    if (worksMaskPolygon) {
        makeDraggable(worksMaskPolygon);
    }

    function makeDraggable(polygon) {
        const svg = polygon.closest('svg');
        const points = parsePoints(polygon.getAttribute('points'));
        let selectedPoint = null;

        // Create draggable handles
        points.forEach((point, index) => {
            const handle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            handle.setAttribute('cx', point.x);
            handle.setAttribute('cy', point.y);
            handle.setAttribute('r', '10');
            handle.setAttribute('fill', 'yellow');
            handle.setAttribute('stroke', 'black');
            handle.setAttribute('stroke-width', '2');
            handle.setAttribute('cursor', 'move');
            handle.dataset.index = index;

            handle.addEventListener('mousedown', (e) => startDrag(e, index));
            svg.appendChild(handle);
        });

        function startDrag(e, index) {
            selectedPoint = index;
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
        }

        function drag(e) {
            if (selectedPoint === null) return;

            const pt = svg.createSVGPoint();
            pt.x = e.clientX;
            pt.y = e.clientY;
            const svgPt = pt.matrixTransform(svg.getScreenCTM().inverse());

            points[selectedPoint] = { x: svgPt.x, y: svgPt.y };
            updatePolygon();
        }

        function stopDrag() {
            selectedPoint = null;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }

        function updatePolygon() {
            const pointsStr = points.map(p => `${p.x},${p.y}`).join(' ');
            polygon.setAttribute('points', pointsStr);

            // Update handles
            const handles = svg.querySelectorAll('circle');
            handles.forEach((handle, i) => {
                if (points[i]) {
                    handle.setAttribute('cx', points[i].x);
                    handle.setAttribute('cy', points[i].y);
                }
            });
        }

        function parsePoints(pointsStr) {
            return pointsStr.trim().split(/\s+/).map(pair => {
                const [x, y] = pair.split(',').map(Number);
                return { x, y };
            });
        }
    }
});
</script>

</body>
</html>
