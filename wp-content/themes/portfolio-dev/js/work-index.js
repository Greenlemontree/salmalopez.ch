/**
 * Work Section - Dot Grid Index
 *
 * Grid of dots where project thumbnails appear in the CENTER of each 4-dot cell.
 * Hover reveals the thumbnail, click locks the selection.
 */

(function () {
	'use strict';

	// ==========================================================================
	// CONFIGURATION
	// ==========================================================================

	// Grid: dots form corners, projects appear in cells between dots
	// For N columns of cells, we need N+1 columns of dots
	const GRID_CONFIG = {
		desktop: { cellCols: 5, cellRows: 4 }, // 5x4 = 20 cells, 6x5 dots
		tablet: { cellCols: 4, cellRows: 3 },  // 4x3 = 12 cells, 5x4 dots
		mobile: { cellCols: 3, cellRows: 3 },  // 3x3 = 9 cells, 4x4 dots
		small: { cellCols: 2, cellRows: 3 },   // 2x3 = 6 cells, 3x4 dots
	};

	function getGridConfig() {
		const width = window.innerWidth;
		if (width <= 480) return GRID_CONFIG.small;
		if (width <= 768) return GRID_CONFIG.mobile;
		if (width <= 1200) return GRID_CONFIG.tablet;
		return GRID_CONFIG.desktop;
	}

	// ==========================================================================
	// STATE
	// ==========================================================================

	let projects = [];
	let gridConfig = getGridConfig();
	let activeProject = 0;
	let lockedProject = null;
	let hoveredProject = null;

	// DOM
	let gridEl, titleEl, categoryEl, excerptEl, previewImg, viewLink, thumbsEl;
	let lightbox, lightboxImg, lightboxThumbs;
	let cells = []; // Array of cell elements (each cell = project slot)

	// ==========================================================================
	// INITIALIZATION
	// ==========================================================================

	function init() {
		const dataScript = document.getElementById('work-projects-data');
		if (!dataScript) return;

		try {
			projects = JSON.parse(dataScript.textContent);
		} catch (e) {
			console.warn('Failed to parse project data:', e);
			return;
		}

		// Cache DOM
		gridEl = document.querySelector('.work-grid');
		titleEl = document.querySelector('.work-title');
		categoryEl = document.querySelector('.work-category');
		excerptEl = document.querySelector('.work-excerpt');
		previewImg = document.querySelector('.work-preview__image');
		viewLink = document.querySelector('.work-view-link');
		thumbsEl = document.querySelector('.work-thumbs');

		lightbox = document.querySelector('.work-lightbox');
		lightboxImg = document.querySelector('.work-lightbox__image');
		lightboxThumbs = document.querySelector('.work-lightbox__thumbs');

		if (!gridEl || !titleEl) return;

		// Build the grid
		buildGrid();

		// Show first project by default
		if (projects.length > 0) {
			activeProject = 0;
			displayProject(activeProject);
			updateCellStates();
		} else {
			titleEl.textContent = 'No Projects Yet';
			if (excerptEl) excerptEl.textContent = 'Projects will appear here once added.';
		}

		// Bind events
		bindLightboxEvents();
		bindResizeHandler();

		document.documentElement.classList.add('js');
	}

	// ==========================================================================
	// GRID BUILDING
	// ==========================================================================

	function buildGrid() {
		gridConfig = getGridConfig();
		const { cellCols, cellRows } = gridConfig;

		// Dots: need cellCols+1 x cellRows+1
		const dotCols = cellCols + 1;
		const dotRows = cellRows + 1;

		// Clear grid
		gridEl.innerHTML = '';
		cells = [];

		// Set CSS grid for dots
		// We'll use a grid where dots and cells interleave
		// Actually, easier approach: position dots absolutely, cells as grid items

		// Create wrapper for proper sizing
		gridEl.style.display = 'grid';
		gridEl.style.gridTemplateColumns = `repeat(${cellCols}, 1fr)`;
		gridEl.style.gridTemplateRows = `repeat(${cellRows}, 1fr)`;
		gridEl.style.gap = '0';
		gridEl.style.position = 'relative';

		// Create cells (project slots)
		const totalCells = cellCols * cellRows;
		for (let i = 0; i < totalCells; i++) {
			const cell = document.createElement('div');
			cell.className = 'work-cell';
			cell.dataset.cellIndex = i;

			// If we have a project for this cell
			if (i < projects.length) {
				cell.classList.add('work-cell--has-project');
				cell.dataset.projectIndex = i;

				// Thumbnail image (hidden by default, shows on hover)
				const thumb = document.createElement('img');
				thumb.className = 'work-cell__thumb';
				thumb.src = projects[i].image || '';
				thumb.alt = projects[i].title || '';
				cell.appendChild(thumb);

				// Bind events
				bindCellEvents(cell, i);
			}

			gridEl.appendChild(cell);
			cells.push(cell);
		}

		// Create dot overlay
		const dotOverlay = document.createElement('div');
		dotOverlay.className = 'work-dots-overlay';
		dotOverlay.style.position = 'absolute';
		dotOverlay.style.inset = '0';
		dotOverlay.style.display = 'grid';
		dotOverlay.style.gridTemplateColumns = `repeat(${dotCols}, 1fr)`;
		dotOverlay.style.gridTemplateRows = `repeat(${dotRows}, 1fr)`;
		dotOverlay.style.pointerEvents = 'none';

		// Create dots at intersections
		const totalDots = dotCols * dotRows;
		for (let i = 0; i < totalDots; i++) {
			const dotWrapper = document.createElement('div');
			dotWrapper.className = 'work-dot-wrapper';
			dotWrapper.style.display = 'flex';
			dotWrapper.style.alignItems = 'center';
			dotWrapper.style.justifyContent = 'center';

			const dot = document.createElement('div');
			dot.className = 'work-dot';
			dotWrapper.appendChild(dot);

			dotOverlay.appendChild(dotWrapper);
		}

		gridEl.appendChild(dotOverlay);
	}

	// ==========================================================================
	// EVENTS
	// ==========================================================================

	function bindCellEvents(cell, projectIdx) {
		// Hover - preview project
		cell.addEventListener('mouseenter', () => {
			hoveredProject = projectIdx;
			displayProject(projectIdx);
			updateCellStates();
		});

		// Leave - revert to locked/default
		cell.addEventListener('mouseleave', () => {
			hoveredProject = null;
			const show = lockedProject !== null ? lockedProject : activeProject;
			displayProject(show);
			updateCellStates();
		});

		// Click - lock
		cell.addEventListener('click', () => {
			if (lockedProject === projectIdx) {
				lockedProject = null;
			} else {
				lockedProject = projectIdx;
				activeProject = projectIdx;
			}
			hoveredProject = null;
			displayProject(lockedProject !== null ? lockedProject : activeProject);
			updateCellStates();
		});
	}

	function bindLightboxEvents() {
		if (!lightbox) return;

		const overlay = lightbox.querySelector('.work-lightbox__overlay');
		const closeBtn = lightbox.querySelector('.work-lightbox__close');
		const mediaContainer = lightbox.querySelector('.work-lightbox__media');

		if (previewImg) {
			previewImg.addEventListener('click', (e) => {
				e.preventDefault();
				openLightbox();
			});
		}

		if (overlay) overlay.addEventListener('click', closeLightbox);
		if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
				closeLightbox();
			}
		});

		// 3D tilt in lightbox
		if (mediaContainer && lightboxImg) {
			mediaContainer.addEventListener('mousemove', (e) => {
				if (!lightbox.classList.contains('is-open')) return;
				const rect = mediaContainer.getBoundingClientRect();
				const x = (e.clientX - rect.left) / rect.width;
				const y = (e.clientY - rect.top) / rect.height;
				const rotateY = (x - 0.5) * 10;
				const rotateX = (0.5 - y) * 10;
				lightboxImg.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
			});

			mediaContainer.addEventListener('mouseleave', () => {
				lightboxImg.style.transform = 'rotateX(0) rotateY(0)';
			});
		}
	}

	function bindResizeHandler() {
		let timeout;
		window.addEventListener('resize', () => {
			clearTimeout(timeout);
			timeout = setTimeout(() => {
				const newConfig = getGridConfig();
				if (newConfig.cellCols !== gridConfig.cellCols || newConfig.cellRows !== gridConfig.cellRows) {
					buildGrid();
					updateCellStates();
				}
			}, 250);
		});
	}

	// ==========================================================================
	// DISPLAY
	// ==========================================================================

	function displayProject(idx) {
		const project = projects[idx];
		if (!project) return;

		if (titleEl) titleEl.textContent = project.title;
		if (categoryEl) categoryEl.textContent = project.category || '';
		if (excerptEl) excerptEl.textContent = project.excerpt || '';
		if (previewImg) {
			previewImg.src = project.image || '';
			previewImg.alt = project.imageAlt || project.title;
		}
		if (viewLink) viewLink.href = project.permalink || '#';

		buildThumbnails(project);
	}

	function buildThumbnails(project) {
		if (!thumbsEl) return;
		thumbsEl.innerHTML = '';

		if (project.image) {
			const thumb = createThumb(project.image, project.imageAlt || project.title, true);
			thumbsEl.appendChild(thumb);
		}

		if (project.gallery && project.gallery.length > 0) {
			project.gallery.forEach((img) => {
				const thumb = createThumb(img.thumb || img.large, img.alt || '', false);
				thumb.dataset.large = img.large;
				thumbsEl.appendChild(thumb);
			});
		}
	}

	function createThumb(src, alt, isActive) {
		const img = document.createElement('img');
		img.src = src;
		img.alt = alt;
		img.className = 'work-thumb' + (isActive ? ' is-active' : '');

		img.addEventListener('click', () => {
			if (previewImg) {
				previewImg.src = img.dataset.large || src;
				previewImg.alt = alt;
			}
			thumbsEl.querySelectorAll('.work-thumb').forEach((t) => t.classList.remove('is-active'));
			img.classList.add('is-active');
		});

		return img;
	}

	function updateCellStates() {
		const displayed = hoveredProject !== null ? hoveredProject : (lockedProject !== null ? lockedProject : activeProject);

		cells.forEach((cell, idx) => {
			cell.classList.remove('work-cell--active', 'work-cell--locked', 'work-cell--hover');

			if (idx < projects.length) {
				if (hoveredProject === idx) {
					cell.classList.add('work-cell--hover');
				}
				if (lockedProject === idx) {
					cell.classList.add('work-cell--locked');
				}
				if (displayed === idx && lockedProject !== idx && hoveredProject !== idx) {
					cell.classList.add('work-cell--active');
				}
			}
		});
	}

	// ==========================================================================
	// LIGHTBOX
	// ==========================================================================

	function openLightbox() {
		if (!lightbox) return;
		const project = projects[activeProject];
		if (!project) return;

		if (lightboxImg) {
			lightboxImg.src = project.image || '';
			lightboxImg.alt = project.imageAlt || project.title;
		}

		if (lightboxThumbs) {
			lightboxThumbs.innerHTML = '';

			if (project.image) {
				const thumb = document.createElement('img');
				thumb.src = project.image;
				thumb.alt = project.imageAlt || project.title;
				thumb.className = 'work-lightbox__thumb is-active';
				thumb.addEventListener('click', () => selectLightboxImage(thumb, project.image, project.imageAlt));
				lightboxThumbs.appendChild(thumb);
			}

			if (project.gallery && project.gallery.length > 0) {
				project.gallery.forEach((img) => {
					const thumb = document.createElement('img');
					thumb.src = img.thumb || img.large;
					thumb.alt = img.alt || '';
					thumb.className = 'work-lightbox__thumb';
					thumb.addEventListener('click', () => selectLightboxImage(thumb, img.large, img.alt));
					lightboxThumbs.appendChild(thumb);
				});
			}
		}

		lightbox.classList.add('is-open');
		lightbox.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
	}

	function closeLightbox() {
		if (!lightbox) return;
		lightbox.classList.remove('is-open');
		lightbox.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
		if (lightboxImg) lightboxImg.style.transform = 'rotateX(0) rotateY(0)';
	}

	function selectLightboxImage(thumb, src, alt) {
		if (lightboxImg) {
			lightboxImg.src = src;
			lightboxImg.alt = alt || '';
		}
		if (lightboxThumbs) {
			lightboxThumbs.querySelectorAll('.work-lightbox__thumb').forEach((t) => t.classList.remove('is-active'));
			thumb.classList.add('is-active');
		}
	}

	// ==========================================================================
	// INIT
	// ==========================================================================

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
