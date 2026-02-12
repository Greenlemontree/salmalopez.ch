/**
 * Project Slide-in Panel
 *
 * Handles opening/closing of the project detail panel
 * with support for image and video carousel.
 *
 * Mobile: First tap shows preview, second tap opens panel.
 * Desktop: Hover shows preview, click opens panel.
 */

(function () {
	'use strict';

	function init() {
		const panel = document.getElementById('project-panel');
		const overlay = document.querySelector('.project-panel-overlay');
		const closeBtn = document.querySelector('.project-panel-close');
		const panelImage = document.getElementById('panel-image');
		const panelVideo = document.getElementById('panel-video');
		const panelTitle = document.getElementById('panel-title');
		const panelYear = document.getElementById('panel-year');
		const panelCategory = document.getElementById('panel-category');
		const panelExcerpt = document.getElementById('panel-excerpt');
		const panelTools = document.getElementById('panel-tools');
		const panelCurrentImage = document.getElementById('panel-current-image');
		const panelTotalImages = document.getElementById('panel-total-images');
		const prevBtn = document.querySelector('.panel-nav-prev');
		const nextBtn = document.querySelector('.panel-nav-next');
		const navContainer = document.querySelector('.project-panel-nav');
		const videoPlayBtn = document.getElementById('panel-video-play');
		const panelLinks = document.getElementById('panel-links');

		if (!panel) {
			console.warn('Project panel not found');
			return;
		}

		// Track which dot is currently showing preview (for mobile)
		let activePreviewDot = null;

		// Detect touch device
		const isTouchDevice =
			'ontouchstart' in window || navigator.maxTouchPoints > 0;

		// Media carousel state
		let currentMedia = []; // Array of {type: 'image'|'video', url: '...'}
		let currentMediaIndex = 0;

		/**
		 * Display the current media item (image or video)
		 */
		function displayCurrentMedia() {
			if (currentMedia.length === 0) return;

			const item = currentMedia[currentMediaIndex];

			// Pause any playing video first
			if (panelVideo) {
				panelVideo.pause();
				panelVideo.currentTime = 0;
				panelVideo.removeAttribute('controls');
			}

			// Hide play button by default
			if (videoPlayBtn) {
				videoPlayBtn.style.display = 'none';
				videoPlayBtn.classList.remove('is-hidden');
			}

			if (item.type === 'video') {
				// Show video, hide image
				if (panelImage) panelImage.style.display = 'none';
				if (panelVideo) {
					panelVideo.src = item.url;
					panelVideo.style.display = 'block';
				}
				// Show custom play button for video
				if (videoPlayBtn) {
					videoPlayBtn.style.display = 'block';
				}
			} else {
				// Show image, hide video
				if (panelVideo) panelVideo.style.display = 'none';
				if (panelImage) {
					panelImage.src = item.url;
					panelImage.style.display = 'block';
				}
			}

			// Update counter
			if (panelCurrentImage) {
				panelCurrentImage.textContent = currentMediaIndex + 1;
			}

			// Update navigation button states
			if (prevBtn) {
				prevBtn.disabled = currentMediaIndex === 0;
			}
			if (nextBtn) {
				nextBtn.disabled = currentMediaIndex === currentMedia.length - 1;
			}
		}

		/**
		 * Handle custom play button click
		 */
		function handleVideoPlay() {
			if (panelVideo) {
				panelVideo.setAttribute('controls', '');
				panelVideo.play();
				if (videoPlayBtn) {
					videoPlayBtn.classList.add('is-hidden');
				}
			}
		}

		/**
		 * Go to previous media item
		 */
		function prevMedia() {
			if (currentMediaIndex > 0) {
				currentMediaIndex--;
				displayCurrentMedia();
			}
		}

		/**
		 * Go to next media item
		 */
		function nextMedia() {
			if (currentMediaIndex < currentMedia.length - 1) {
				currentMediaIndex++;
				displayCurrentMedia();
			}
		}

		/**
		 * Open the panel with project data
		 */
		function openPanel(projectData) {
			// Reset carousel
			currentMedia = [];
			currentMediaIndex = 0;

			// Update header - Title
			if (panelTitle) {
				panelTitle.textContent = projectData.title || '';
			}

			// Update header - Year
			if (panelYear) {
				panelYear.textContent = projectData.year || '';
				panelYear.style.display = projectData.year ? 'inline' : 'none';
			}

			// Update header - Category
			if (panelCategory) {
				panelCategory.textContent = projectData.category || '';
				panelCategory.style.display = projectData.category ? 'inline' : 'none';
			}

			// Parse media data
			if (projectData.media) {
				try {
					currentMedia = JSON.parse(projectData.media);
				} catch (e) {
					console.warn('Failed to parse media data:', e);
				}
			}

			// Fallback to thumbnail if no media
			if (currentMedia.length === 0 && projectData.thumbnail) {
				currentMedia.push({
					type: 'image',
					url: projectData.thumbnail
				});
			}

			// Update total count
			if (panelTotalImages) {
				panelTotalImages.textContent = currentMedia.length;
			}

			// Display first media item
			if (currentMedia.length > 0) {
				displayCurrentMedia();
				if (panelImage) panelImage.alt = projectData.title || '';
			} else {
				// No media at all
				if (panelImage) {
					panelImage.style.display = 'none';
				}
				if (panelVideo) {
					panelVideo.style.display = 'none';
				}
			}

			// Update excerpt/description - use content if excerpt is truncated or empty
			if (panelExcerpt) {
				// Use full content if available, otherwise fall back to excerpt
				const description = projectData.content || projectData.excerpt || '';
				panelExcerpt.textContent = description;
			}

			// Update tools - create tags
			if (panelTools) {
				panelTools.innerHTML = '';
				if (projectData.tools) {
					const toolsArray = projectData.tools.split(',').map(t => t.trim()).filter(Boolean);
					toolsArray.forEach(tool => {
						const tag = document.createElement('span');
						tag.className = 'tool-tag';
						tag.textContent = tool;
						panelTools.appendChild(tag);
					});
				}
			}

			// Show/hide navigation based on media count
			if (navContainer) {
				navContainer.style.display = currentMedia.length > 1 ? 'flex' : 'none';
			}

			// Update external links (website/youtube)
			if (panelLinks) {
				panelLinks.innerHTML = '';

				if (projectData.website) {
					const websiteLink = document.createElement('a');
					websiteLink.href = projectData.website;
					websiteLink.target = '_blank';
					websiteLink.rel = 'noopener noreferrer';
					websiteLink.className = 'project-link website-link';
					websiteLink.textContent = 'View Website';
					panelLinks.appendChild(websiteLink);
				}

				if (projectData.youtube) {
					const youtubeLink = document.createElement('a');
					youtubeLink.href = projectData.youtube;
					youtubeLink.target = '_blank';
					youtubeLink.rel = 'noopener noreferrer';
					youtubeLink.className = 'project-link youtube-link';
					youtubeLink.textContent = 'Watch on YouTube';
					panelLinks.appendChild(youtubeLink);
				}

				// Show/hide links container based on whether there are links
				panelLinks.style.display = (projectData.website || projectData.youtube) ? 'flex' : 'none';
			}

			// Open panel
			panel.classList.add('is-open');
			panel.setAttribute('aria-hidden', 'false');
			document.body.classList.add('panel-open');

			// Clear active preview
			clearActivePreview();

			// Focus management for accessibility
			if (closeBtn) closeBtn.focus();
		}

		/**
		 * Close the panel
		 */
		function closePanel() {
			// Pause video when closing
			if (panelVideo) {
				panelVideo.pause();
				panelVideo.currentTime = 0;
			}

			panel.classList.remove('is-open');
			panel.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('panel-open');
		}

		/**
		 * Clear active preview on mobile
		 */
		function clearActivePreview() {
			if (activePreviewDot) {
				activePreviewDot.classList.remove('preview-active');
				activePreviewDot = null;
			}
		}

		/**
		 * Extract project data from a dot item element
		 */
		function getProjectDataFromDot(dotItem) {
			return {
				id: dotItem.dataset.projectId,
				title: dotItem.dataset.projectTitle,
				excerpt: dotItem.dataset.projectExcerpt,
				thumbnail: dotItem.dataset.projectThumbnail,
				media: dotItem.dataset.projectMedia,
				year: dotItem.dataset.projectYear,
				category: dotItem.dataset.projectCategory,
				tools: dotItem.dataset.projectTools,
				content: dotItem.dataset.projectContent,
				link: dotItem.dataset.projectLink,
				website: dotItem.dataset.projectWebsite,
				youtube: dotItem.dataset.projectYoutube,
			};
		}

		/**
		 * Handle click on project dot
		 */
		function handleProjectClick(e) {
			e.preventDefault();
			e.stopPropagation();

			const dotItem = e.currentTarget;

			// On touch devices: first tap shows preview, second tap opens panel
			if (isTouchDevice) {
				if (activePreviewDot === dotItem) {
					// Second tap on same dot - open panel
					openPanel(getProjectDataFromDot(dotItem));
				} else {
					// First tap or tap on different dot - show preview
					clearActivePreview();
					dotItem.classList.add('preview-active');
					activePreviewDot = dotItem;
				}
			} else {
				// Desktop: click directly opens panel
				openPanel(getProjectDataFromDot(dotItem));
			}
		}

		/**
		 * Handle keyboard events
		 */
		function handleKeydown(e) {
			if (!panel.classList.contains('is-open')) return;

			if (e.key === 'Escape') {
				closePanel();
			} else if (e.key === 'ArrowLeft') {
				prevMedia();
			} else if (e.key === 'ArrowRight') {
				nextMedia();
			}
		}

		/**
		 * Handle click on preview image/card (mobile only)
		 * Opens the panel directly when tapping on the preview
		 */
		function handlePreviewClick(e) {
			if (!isTouchDevice) return;

			e.preventDefault();
			e.stopPropagation();

			// Find the parent dot item to get project data
			const dotItem = e.target.closest('.project-dot-item');
			if (!dotItem) return;

			openPanel(getProjectDataFromDot(dotItem));
		}

		/**
		 * Handle clicks outside dots to close preview on mobile
		 */
		function handleDocumentClick(e) {
			if (isTouchDevice && activePreviewDot) {
				// If click is outside any dot item, close preview
				if (!e.target.closest('.project-dot-item')) {
					clearActivePreview();
				}
			}
		}

		// Event listeners for project dots
		const dotItems = document.querySelectorAll('.project-dot-item');

		dotItems.forEach((item) => {
			item.addEventListener('click', handleProjectClick);

			// On mobile, make preview clickable to open panel
			const preview = item.querySelector('.project-dot-preview');
			if (preview && isTouchDevice) {
				preview.addEventListener('click', handlePreviewClick);
			}
		});

		// Panel controls
		if (closeBtn) closeBtn.addEventListener('click', closePanel);
		if (overlay) overlay.addEventListener('click', closePanel);
		if (prevBtn) prevBtn.addEventListener('click', prevMedia);
		if (nextBtn) nextBtn.addEventListener('click', nextMedia);
		if (videoPlayBtn) videoPlayBtn.addEventListener('click', handleVideoPlay);

		document.addEventListener('keydown', handleKeydown);
		document.addEventListener('click', handleDocumentClick);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
