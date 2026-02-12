/**
 * Film Extracts - Mouse-following "see video" button
 * Video cover mouse-following play button
 * ScrollTrigger pinning for video project sections
 */
(function() {
	'use strict';

	function initFilmExtracts() {
		var container = document.querySelector('[data-component="film-extracts"]');
		if (!container) return;

		var items = container.querySelectorAll('.extract-item');
		if (!items.length) return;

		var videoSection = document.querySelector('[data-component="video-cover"]');

		items.forEach(function(item) {
			var button = item.querySelector('.extract-hover-button');
			if (!button) return;

			item.addEventListener('mousemove', function(e) {
				var rect = item.getBoundingClientRect();
				var x = e.clientX - rect.left;
				var y = e.clientY - rect.top;

				button.style.left = x + 'px';
				button.style.top = y + 'px';
			});

			item.addEventListener('click', function() {
				if (!videoSection) return;
				videoSection.scrollIntoView({ behavior: 'smooth' });
			});
		});
	}

	function initVideoCover() {
		var container = document.querySelector('[data-component="video-cover"]');
		if (!container) return;

		var wrapper = container.querySelector('.video-cover-wrapper');
		var video = container.querySelector('video');
		var playBtn = container.querySelector('.video-play-button');
		if (!video || !playBtn || !wrapper) return;

		// Create cinema overlay
		var overlay = document.createElement('div');
		overlay.className = 'cinema-overlay';
		var cinemaInner = document.createElement('div');
		cinemaInner.className = 'cinema-inner';
		var closeBtn = document.createElement('button');
		closeBtn.className = 'cinema-close';
		closeBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
		overlay.appendChild(cinemaInner);
		overlay.appendChild(closeBtn);
		document.body.appendChild(overlay);

		wrapper.addEventListener('mousemove', function(e) {
			var rect = wrapper.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var y = e.clientY - rect.top;

			playBtn.style.left = x + 'px';
			playBtn.style.top = y + 'px';
		});

		function openCinema() {
			if (container.classList.contains('is-playing')) return;
			// Move video into cinema overlay
			cinemaInner.appendChild(video);
			video.controls = true;
			overlay.classList.add('is-active');
			document.body.style.overflow = 'hidden';
			container.classList.add('is-playing');
			// Wait for DOM to settle after moving the element, then play
			requestAnimationFrame(function() {
				video.play().catch(function() {});
			});
		}

		function closeCinema() {
			video.pause();
			video.controls = false;
			overlay.classList.remove('is-active');
			document.body.style.overflow = '';
			// Move video back to original wrapper
			wrapper.insertBefore(video, playBtn);
			container.classList.remove('is-playing');
		}

		wrapper.addEventListener('click', openCinema);
		closeBtn.addEventListener('click', closeCinema);

		overlay.addEventListener('click', function(e) {
			if (e.target === overlay) closeCinema();
		});

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && overlay.classList.contains('is-active')) {
				closeCinema();
			}
		});

		video.addEventListener('ended', function() {
			closeCinema();
		});
	}

	function initSectionReveals() {
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

		gsap.registerPlugin(ScrollTrigger);

		// Featured image
		var featuredImage = document.querySelector('.project-featured-image');
		if (featuredImage) {
			gsap.set(featuredImage, { opacity: 0, y: 40 });
			gsap.to(featuredImage, {
				opacity: 1, y: 0, duration: 1, ease: 'power2.out',
				scrollTrigger: { trigger: featuredImage, start: 'top 80%' }
			});
		}

		// Extract items (staggered)
		var extractItems = document.querySelectorAll('.extract-item');
		if (extractItems.length) {
			gsap.set(extractItems, { opacity: 0, y: 40 });
			gsap.to(extractItems, {
				opacity: 1, y: 0, duration: 0.8, ease: 'power2.out',
				stagger: 0.15,
				scrollTrigger: { trigger: extractItems[0].parentElement, start: 'top 80%' }
			});
		}

		// Title section elements (staggered reveal)
		var titleSection = document.querySelector('.project-title-section');
		if (titleSection) {
			var titleElements = titleSection.querySelectorAll('.project-meta, .project-title, .project-tags, .project-description, .project-tools, .project-links-inline');
			if (titleElements.length) {
				gsap.set(titleElements, { opacity: 0, y: 30 });
				gsap.to(titleElements, {
					opacity: 1, y: 0, duration: 0.7, ease: 'power2.out',
					stagger: 0.12,
					scrollTrigger: { trigger: titleSection, start: 'top 70%' }
				});
			}
		}

		// Comparison slider
		var compSlider = document.querySelector('.project-comparison-slider');
		if (compSlider) {
			gsap.set(compSlider, { opacity: 0, y: 40 });
			gsap.to(compSlider, {
				opacity: 1, y: 0, duration: 1, ease: 'power2.out',
				scrollTrigger: { trigger: compSlider, start: 'top 80%' }
			});
		}

		// Scouting items (staggered)
		var scoutingItems = document.querySelectorAll('.scouting-item');
		if (scoutingItems.length) {
			gsap.set(scoutingItems, { opacity: 0, y: 30 });
			gsap.to(scoutingItems, {
				opacity: 1, y: 0, duration: 0.7, ease: 'power2.out',
				stagger: 0.15,
				scrollTrigger: { trigger: scoutingItems[0].parentElement, start: 'top 80%' }
			});
		}

		// Final video
		var finalVideo = document.querySelector('.project-final-video');
		if (finalVideo) {
			gsap.set(finalVideo, { opacity: 0, y: 40 });
			gsap.to(finalVideo, {
				opacity: 1, y: 0, duration: 1, ease: 'power2.out',
				scrollTrigger: { trigger: finalVideo, start: 'top 80%' }
			});
		}

		// Gallery items (staggered)
		var galleryItems = document.querySelectorAll('.gallery-item');
		if (galleryItems.length) {
			gsap.set(galleryItems, { opacity: 0, y: 30 });
			gsap.to(galleryItems, {
				opacity: 1, y: 0, duration: 0.7, ease: 'power2.out',
				stagger: 0.12,
				scrollTrigger: { trigger: galleryItems[0].parentElement, start: 'top 80%' }
			});
		}

		// Photography: cover frame
		var coverFrame = document.querySelector('.photo-cover-frame');
		if (coverFrame) {
			gsap.set(coverFrame, { opacity: 0, y: 40 });
			gsap.to(coverFrame, {
				opacity: 1, y: 0, duration: 1, ease: 'power2.out',
				scrollTrigger: { trigger: coverFrame, start: 'top 80%' }
			});
		}

		// Photography: material image + description
		var titleBody = document.querySelector('.photo-title-body');
		if (titleBody) {
			var bodyChildren = titleBody.children;
			if (bodyChildren.length) {
				gsap.set(bodyChildren, { opacity: 0, y: 30 });
				gsap.to(bodyChildren, {
					opacity: 1, y: 0, duration: 0.8, ease: 'power2.out',
					stagger: 0.2,
					scrollTrigger: { trigger: titleBody, start: 'top 80%' }
				});
			}
		}

		// Photography: lab sketch items (staggered)
		var labItems = document.querySelectorAll('.lab-sketch-item');
		if (labItems.length) {
			gsap.set(labItems, { opacity: 0, y: 30 });
			gsap.to(labItems, {
				opacity: 1, y: 0, duration: 0.7, ease: 'power2.out',
				stagger: 0.15,
				scrollTrigger: { trigger: labItems[0].parentElement, start: 'top 80%' }
			});
		}

		// Photography: mosaic items (staggered)
		var mosaicItems = document.querySelectorAll('.mosaic-item');
		if (mosaicItems.length) {
			gsap.set(mosaicItems, { opacity: 0, y: 30 });
			gsap.to(mosaicItems, {
				opacity: 1, y: 0, duration: 0.7, ease: 'power2.out',
				stagger: 0.08,
				scrollTrigger: { trigger: mosaicItems[0].parentElement, start: 'top 80%' }
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			initFilmExtracts();
			initVideoCover();
			initSectionReveals();
		});
	} else {
		initFilmExtracts();
		initVideoCover();
		initSectionReveals();
	}
})();
