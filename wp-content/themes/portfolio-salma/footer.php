<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package portfolio-salma
 */

?>

	<footer id="colophon" class="site-footer">
		<!-- Corner dots -->
		<span class="footer-dot footer-dot-tl"></span>
		<span class="footer-dot footer-dot-tr"></span>
		<span class="footer-dot footer-dot-bl"></span>
		<span class="footer-dot footer-dot-br"></span>

		<div class="footer-content">
			<!-- Links column -->
			<div class="footer-links">
				<a href="mailto:salma.lopezgil@gmail.com" class="footer-link">
					<span>(salma.lopezgil@gmail.com)</span>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<line x1="7" y1="17" x2="17" y2="7"></line>
						<polyline points="7 7 17 7 17 17"></polyline>
					</svg>
				</a>
				<a href="https://www.instagram.com/greenlemontree__/" target="_blank" rel="noopener noreferrer" class="footer-link">
					<span>(instagram)</span>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<line x1="7" y1="17" x2="17" y2="7"></line>
						<polyline points="7 7 17 7 17 17"></polyline>
					</svg>
				</a>
				<a href="https://www.youtube.com/@salmalopez" target="_blank" rel="noopener noreferrer" class="footer-link">
					<span>(youtube)</span>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<line x1="7" y1="17" x2="17" y2="7"></line>
						<polyline points="7 7 17 7 17 17"></polyline>
					</svg>
				</a>
			</div>

			<!-- Copyright -->
			<div class="footer-copyright">
				<span>&copy; <?php echo date('Y'); ?></span>
			</div>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
