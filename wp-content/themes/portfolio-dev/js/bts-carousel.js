/**
 * Behind The Scenes Carousel
 */
(function() {
	'use strict';

	function initBTSCarousel() {
		const carousel = document.querySelector('.project-bts-carousel');
		if (!carousel) return;

		const track = carousel.querySelector('.bts-carousel-track');
		const prevBtn = carousel.querySelector('.bts-carousel-prev');
		const nextBtn = carousel.querySelector('.bts-carousel-next');
		const items = carousel.querySelectorAll('.bts-carousel-item');

		if (!track || !prevBtn || !nextBtn || items.length === 0) return;

		let currentIndex = 0;
		const itemsPerPage = 3;
		const maxIndex = Math.max(0, items.length - itemsPerPage);

		function updateCarousel() {
			const itemWidth = items[0].offsetWidth;
			const gap = 16; // 1rem in pixels
			const offset = currentIndex * (itemWidth + gap);
			track.style.transform = `translateX(-${offset}px)`;

			// Update button states
			prevBtn.disabled = currentIndex === 0;
			nextBtn.disabled = currentIndex >= maxIndex;

			prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
			nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
		}

		prevBtn.addEventListener('click', () => {
			if (currentIndex > 0) {
				currentIndex--;
				updateCarousel();
			}
		});

		nextBtn.addEventListener('click', () => {
			if (currentIndex < maxIndex) {
				currentIndex++;
				updateCarousel();
			}
		});

		// Handle window resize
		let resizeTimeout;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(() => {
				// Reset to first item on resize
				currentIndex = 0;
				updateCarousel();
			}, 250);
		});

		// Initial update
		updateCarousel();
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBTSCarousel);
	} else {
		initBTSCarousel();
	}
})();
