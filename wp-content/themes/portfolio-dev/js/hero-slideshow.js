/**
 * Hero Image Slideshow
 *
 * Automatically rotates through hero background images with instant swap.
 * Images are loaded from the data-hero-images attribute on .hero-image-layer.
 * Uses instant transition to avoid revealing text through the mask.
 */

(function () {
	'use strict';

	// Configuration
	const CONFIG = {
		// Time between image changes (in milliseconds)
		interval: 5000,
	};

	// State
	let images = [];
	let currentIndex = 0;
	let heroImage = null;
	let intervalId = null;

	/**
	 * Initialize the slideshow
	 */
	function init() {
		const heroLayer = document.querySelector('.hero-image-layer');
		heroImage = document.getElementById('hero-image');

		if (!heroLayer || !heroImage) {
			return;
		}

		// Parse images from data attribute
		const imagesData = heroLayer.getAttribute('data-hero-images');
		if (!imagesData) {
			return;
		}

		try {
			images = JSON.parse(imagesData);
		} catch (e) {
			console.warn('Hero slideshow: Could not parse images data');
			return;
		}

		// Only start slideshow if we have multiple images
		if (images.length <= 1) {
			console.log('Hero slideshow: Only one image, slideshow disabled');
			return;
		}

		// Preload all images for smooth transitions
		preloadImages();

		// Start the slideshow
		startSlideshow();

		console.log('Hero slideshow: initialized with', images.length, 'images');
	}

	/**
	 * Preload all images
	 */
	function preloadImages() {
		images.forEach((src) => {
			const img = new Image();
			img.src = src;
		});
	}

	/**
	 * Start the automatic slideshow
	 */
	function startSlideshow() {
		intervalId = setInterval(nextImage, CONFIG.interval);
	}

	/**
	 * Transition to the next image
	 */
	function nextImage() {
		currentIndex = (currentIndex + 1) % images.length;
		swapImage(images[currentIndex]);
	}

	/**
	 * Instant swap to new image (no fade to avoid revealing text)
	 */
	function swapImage(newSrc) {
		heroImage.setAttribute('href', newSrc);
	}

	/**
	 * Pause slideshow (e.g., when tab not visible)
	 */
	function pause() {
		if (intervalId) {
			clearInterval(intervalId);
			intervalId = null;
		}
	}

	/**
	 * Resume slideshow
	 */
	function resume() {
		if (!intervalId && images.length > 1) {
			startSlideshow();
		}
	}

	// Handle page visibility changes
	document.addEventListener('visibilitychange', () => {
		if (document.hidden) {
			pause();
		} else {
			resume();
		}
	});

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Expose API for external control
	window.HeroSlideshow = {
		pause,
		resume,
		next: nextImage,
	};
})();
