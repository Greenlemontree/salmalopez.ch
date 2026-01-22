/**
 * Single Project Page Scripts
 *
 * Features:
 * - Video lightbox
 * - Color grading comparison slider
 */

(function () {
	'use strict';

	function init() {
		initVideoLightbox();
		initColorGradingSlider();
	}

	/**
	 * Video Lightbox
	 */
	function initVideoLightbox() {
		const playBtn = document.querySelector('.project-play-btn');
		const thumbnail = document.querySelector('.project-hero-thumbnail');
		const lightbox = document.getElementById('video-lightbox');
		const lightboxVideo = document.getElementById('lightbox-video');
		const lightboxClose = document.querySelector('.lightbox-close');
		const lightboxOverlay = document.querySelector('.lightbox-overlay');

		if (!playBtn || !lightbox || !lightboxVideo) return;

		const videoUrl = thumbnail ? thumbnail.dataset.video : null;

		// Open lightbox
		function openLightbox() {
			if (!videoUrl) return;

			lightboxVideo.src = videoUrl;
			lightbox.classList.add('is-open');
			lightbox.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';

			// Auto-play video
			setTimeout(() => {
				lightboxVideo.play();
			}, 300);
		}

		// Close lightbox
		function closeLightbox() {
			lightboxVideo.pause();
			lightboxVideo.currentTime = 0;
			lightbox.classList.remove('is-open');
			lightbox.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}

		playBtn.addEventListener('click', openLightbox);
		lightboxClose.addEventListener('click', closeLightbox);
		lightboxOverlay.addEventListener('click', closeLightbox);

		// Close on Escape key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
				closeLightbox();
			}
		});
	}

	/**
	 * Color Grading Comparison Slider
	 */
	function initColorGradingSlider() {
		const slider = document.getElementById('color-grading-slider');
		const handle = document.getElementById('color-grading-handle');

		if (!slider || !handle) return;

		const gradedSide = slider.querySelector('.color-grading-graded');
		const logSide = slider.querySelector('.color-grading-log');
		const gradedVideo = gradedSide ? gradedSide.querySelector('video') : null;
		const logVideo = logSide ? logSide.querySelector('video') : null;

		let isDragging = false;
		let sliderRect = slider.getBoundingClientRect();

		// Start videos when in view
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					if (gradedVideo) gradedVideo.play();
					if (logVideo) logVideo.play();
				} else {
					if (gradedVideo) gradedVideo.pause();
					if (logVideo) logVideo.pause();
				}
			});
		}, { threshold: 0.3 });

		observer.observe(slider);

		// Update slider position
		function updateSliderPosition(clientX) {
			sliderRect = slider.getBoundingClientRect();
			let percentage = ((clientX - sliderRect.left) / sliderRect.width) * 100;
			percentage = Math.max(0, Math.min(100, percentage));

			// Update handle position
			handle.style.left = percentage + '%';

			// Update clip paths
			if (gradedSide) {
				gradedSide.style.clipPath = `inset(0 ${100 - percentage}% 0 0)`;
			}
		}

		// Mouse events
		handle.addEventListener('mousedown', (e) => {
			isDragging = true;
			e.preventDefault();
		});

		document.addEventListener('mousemove', (e) => {
			if (isDragging) {
				updateSliderPosition(e.clientX);
			}
		});

		document.addEventListener('mouseup', () => {
			isDragging = false;
		});

		// Touch events
		handle.addEventListener('touchstart', (e) => {
			isDragging = true;
			e.preventDefault();
		});

		document.addEventListener('touchmove', (e) => {
			if (isDragging && e.touches[0]) {
				updateSliderPosition(e.touches[0].clientX);
			}
		});

		document.addEventListener('touchend', () => {
			isDragging = false;
		});

		// Click on slider to move handle
		slider.addEventListener('click', (e) => {
			if (e.target !== handle && !handle.contains(e.target)) {
				updateSliderPosition(e.clientX);
			}
		});

		// Initialize at 50%
		setTimeout(() => {
			sliderRect = slider.getBoundingClientRect();
			updateSliderPosition(sliderRect.left + sliderRect.width / 2);
		}, 100);

		// Sync video playback
		if (gradedVideo && logVideo) {
			gradedVideo.addEventListener('timeupdate', () => {
				if (Math.abs(gradedVideo.currentTime - logVideo.currentTime) > 0.1) {
					logVideo.currentTime = gradedVideo.currentTime;
				}
			});
		}
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
