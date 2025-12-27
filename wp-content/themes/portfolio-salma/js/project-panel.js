/**
 * Project Slide-in Panel
 *
 * Handles opening/closing of the project detail panel
 * when clicking on project dots.
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
		const panelTitle = document.getElementById('panel-title');
		const panelExcerpt = document.getElementById('panel-excerpt');
		const panelContent = document.getElementById('panel-content');

		if (!panel) {
			console.warn('Project panel not found');
			return;
		}

		// Track which dot is currently showing preview (for mobile)
		let activePreviewDot = null;

		// Detect touch device
		const isTouchDevice =
			'ontouchstart' in window || navigator.maxTouchPoints > 0;

		/**
		 * Open the panel with project data
		 */
		function openPanel(projectData) {
			// Populate panel content
			if (panelImage && projectData.thumbnail) {
				panelImage.src = projectData.thumbnail;
				panelImage.alt = projectData.title;
				panelImage.parentElement.style.display = 'block';
			} else if (panelImage) {
				panelImage.parentElement.style.display = 'none';
			}

			if (panelTitle) {
				panelTitle.textContent = projectData.title || '';
			}

			if (panelExcerpt) {
				panelExcerpt.textContent = projectData.excerpt || '';
				panelExcerpt.style.display = projectData.excerpt ? 'block' : 'none';
			}

			if (panelContent) {
				panelContent.textContent = projectData.content || '';
				panelContent.style.display = projectData.content ? 'block' : 'none';
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
					const projectData = {
						id: dotItem.dataset.projectId,
						title: dotItem.dataset.projectTitle,
						excerpt: dotItem.dataset.projectExcerpt,
						thumbnail: dotItem.dataset.projectThumbnail,
						content: dotItem.dataset.projectContent,
						link: dotItem.dataset.projectLink,
					};
					openPanel(projectData);
				} else {
					// First tap or tap on different dot - show preview
					clearActivePreview();
					dotItem.classList.add('preview-active');
					activePreviewDot = dotItem;
				}
			} else {
				// Desktop: click directly opens panel
				const projectData = {
					id: dotItem.dataset.projectId,
					title: dotItem.dataset.projectTitle,
					excerpt: dotItem.dataset.projectExcerpt,
					thumbnail: dotItem.dataset.projectThumbnail,
					content: dotItem.dataset.projectContent,
					link: dotItem.dataset.projectLink,
				};
				openPanel(projectData);
			}
		}

		/**
		 * Handle keyboard events
		 */
		function handleKeydown(e) {
			if (e.key === 'Escape' && panel.classList.contains('is-open')) {
				closePanel();
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

			const projectData = {
				id: dotItem.dataset.projectId,
				title: dotItem.dataset.projectTitle,
				excerpt: dotItem.dataset.projectExcerpt,
				thumbnail: dotItem.dataset.projectThumbnail,
				content: dotItem.dataset.projectContent,
				link: dotItem.dataset.projectLink,
			};

			openPanel(projectData);
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

		if (closeBtn) closeBtn.addEventListener('click', closePanel);
		if (overlay) overlay.addEventListener('click', closePanel);
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
