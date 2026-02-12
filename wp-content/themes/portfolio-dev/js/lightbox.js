/**
 * Lightbox for Gallery Images
 * - Mouse-following navigation arrows (same system as sketchbook)
 * - Left/right invisible zones for prev/next
 * - Mobile: tap left/right to navigate
 */
(function() {
	'use strict';

	let lightbox;
	let lightboxImg;
	let currentImages = [];
	let currentIndex = 0;

	function createLightbox() {
		lightbox = document.createElement('div');
		lightbox.className = 'lightbox';

		var content = document.createElement('div');
		content.className = 'lightbox-content';

		lightboxImg = document.createElement('img');
		lightboxImg.src = '';
		lightboxImg.alt = '';
		content.appendChild(lightboxImg);

		// Close button
		var closeBtn = document.createElement('button');
		closeBtn.className = 'lightbox-close';
		closeBtn.setAttribute('aria-label', 'Close');
		closeBtn.innerHTML = '&times;';

		// Navigation zones with mouse-following arrows
		var prevZone = document.createElement('div');
		prevZone.className = 'lightbox-zone lightbox-zone-prev';
		var prevArrow = document.createElement('div');
		prevArrow.className = 'lightbox-arrow';
		prevArrow.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#F5F5F0" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>';
		prevZone.appendChild(prevArrow);

		var nextZone = document.createElement('div');
		nextZone.className = 'lightbox-zone lightbox-zone-next';
		var nextArrow = document.createElement('div');
		nextArrow.className = 'lightbox-arrow';
		nextArrow.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#F5F5F0" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>';
		nextZone.appendChild(nextArrow);

		// Zones on lightbox root (cover screen sides, not just image)
		lightbox.appendChild(content);
		lightbox.appendChild(closeBtn);
		lightbox.appendChild(prevZone);
		lightbox.appendChild(nextZone);
		document.body.appendChild(lightbox);

		// Mouse-following arrows on zones
		prevZone.addEventListener('mousemove', function(e) {
			var rect = prevZone.getBoundingClientRect();
			prevArrow.style.left = (e.clientX - rect.left) + 'px';
			prevArrow.style.top = (e.clientY - rect.top) + 'px';
		});

		nextZone.addEventListener('mousemove', function(e) {
			var rect = nextZone.getBoundingClientRect();
			nextArrow.style.left = (e.clientX - rect.left) + 'px';
			nextArrow.style.top = (e.clientY - rect.top) + 'px';
		});

		// Navigation clicks
		prevZone.addEventListener('click', function(e) {
			e.stopPropagation();
			showPrevImage();
		});

		nextZone.addEventListener('click', function(e) {
			e.stopPropagation();
			showNextImage();
		});

		// Close on button or clicking the dark backdrop (not the image/zones)
		closeBtn.addEventListener('click', closeLightbox);
		lightbox.addEventListener('click', function(e) {
			if (e.target === lightbox) {
				closeLightbox();
			}
		});

		// Keyboard navigation
		document.addEventListener('keydown', function(e) {
			if (!lightbox.classList.contains('active')) return;

			if (e.key === 'Escape') closeLightbox();
			if (e.key === 'ArrowLeft') showPrevImage();
			if (e.key === 'ArrowRight') showNextImage();
		});
	}

	function openLightbox(index) {
		if (!lightbox) createLightbox();

		currentIndex = index;
		updateLightboxImage();
		lightbox.classList.add('active');
		document.body.style.overflow = 'hidden';
	}

	function closeLightbox() {
		lightbox.classList.remove('active');
		document.body.style.overflow = '';
	}

	function updateLightboxImage() {
		if (currentImages[currentIndex]) {
			lightboxImg.src = currentImages[currentIndex];
		}

		// Show/hide navigation zones based on position
		var prevZone = lightbox.querySelector('.lightbox-zone-prev');
		var nextZone = lightbox.querySelector('.lightbox-zone-next');

		prevZone.style.display = currentIndex > 0 ? 'block' : 'none';
		nextZone.style.display = currentIndex < currentImages.length - 1 ? 'block' : 'none';
	}

	function showPrevImage() {
		if (currentIndex > 0) {
			currentIndex--;
			updateLightboxImage();
		}
	}

	function showNextImage() {
		if (currentIndex < currentImages.length - 1) {
			currentIndex++;
			updateLightboxImage();
		}
	}

	function initLightbox() {
		// Find all gallery images with lightbox trigger class
		var triggers = document.querySelectorAll('.gallery-lightbox-trigger');
		if (triggers.length === 0) return;

		// Collect all image URLs
		currentImages = Array.from(triggers).map(function(img) { return img.src; });

		// Add click handlers
		triggers.forEach(function(trigger, index) {
			trigger.style.cursor = 'pointer';
			trigger.addEventListener('click', function() {
				openLightbox(index);
			});
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLightbox);
	} else {
		initLightbox();
	}
})();
