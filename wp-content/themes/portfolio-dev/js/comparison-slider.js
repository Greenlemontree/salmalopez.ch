/**
 * Comparison Slider for LOG vs Color Graded images
 */
(function() {
	'use strict';

	function initComparisonSlider() {
		const sliders = document.querySelectorAll('[data-component="comparison-slider"]');

		sliders.forEach(slider => {
			const beforeImage = slider.querySelector('.comparison-image-before');
			const handle = slider.querySelector('.comparison-slider-handle');

			if (!beforeImage || !handle) return;

			let isDragging = false;

			function updateSlider(clientX) {
				const rect = slider.getBoundingClientRect();
				let x = clientX - rect.left;

				// Constrain to slider bounds
				x = Math.max(0, Math.min(x, rect.width));

				const percentage = (x / rect.width) * 100;

				// Update before image clip-path
				beforeImage.style.clipPath = `inset(0 ${100 - percentage}% 0 0)`;

				// Update handle position
				handle.style.left = `${percentage}%`;
			}

			// Mouse events
			slider.addEventListener('mousedown', (e) => {
				isDragging = true;
				updateSlider(e.clientX);
			});

			document.addEventListener('mousemove', (e) => {
				if (!isDragging) return;
				updateSlider(e.clientX);
			});

			document.addEventListener('mouseup', () => {
				isDragging = false;
			});

			// Touch events for mobile
			slider.addEventListener('touchstart', (e) => {
				isDragging = true;
				updateSlider(e.touches[0].clientX);
			});

			document.addEventListener('touchmove', (e) => {
				if (!isDragging) return;
				updateSlider(e.touches[0].clientX);
			});

			document.addEventListener('touchend', () => {
				isDragging = false;
			});

			// Click anywhere on slider to move handle
			slider.addEventListener('click', (e) => {
				updateSlider(e.clientX);
			});
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initComparisonSlider);
	} else {
		initComparisonSlider();
	}
})();
