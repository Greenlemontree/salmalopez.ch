/**
 * Interactive Sketchbook
 * - Mouse-following "open" button on cover (desktop)
 * - Static "open" CTA on cover (mobile)
 * - Left/right nav zones with mouse-following arrows (desktop)
 * - Tap left/right to navigate (mobile)
 * - Auto-advance every 5 seconds
 * - Reset on scroll/interaction outside
 */
(function() {
	'use strict';

	function initSketchbook() {
		const sketchbook = document.querySelector('[data-component="sketchbook"]');
		if (!sketchbook) return;

		const cover = sketchbook.querySelector('.sketchbook-cover');
		const openButton = sketchbook.querySelector('.sketchbook-open-button');
		const viewer = sketchbook.querySelector('.sketchbook-viewer');
		const pages = sketchbook.querySelectorAll('.sketchbook-page');
		const currentPageEl = sketchbook.querySelector('.current-page');

		if (!cover || !openButton || !viewer || pages.length === 0) return;

		let currentPage = 0;
		let isOpen = false;
		let autoAdvanceTimer = null;
		const AUTO_ADVANCE_DELAY = 5000;

		// Create nav zones and mouse-following arrows
		const leftZone = document.createElement('div');
		leftZone.className = 'sketchbook-nav-zone sketchbook-zone-prev';
		const rightZone = document.createElement('div');
		rightZone.className = 'sketchbook-nav-zone sketchbook-zone-next';

		const leftArrow = document.createElement('div');
		leftArrow.className = 'sketchbook-nav-arrow';
		leftArrow.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#171412" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>';

		const rightArrow = document.createElement('div');
		rightArrow.className = 'sketchbook-nav-arrow';
		rightArrow.innerHTML = '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#171412" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>';

		leftZone.appendChild(leftArrow);
		rightZone.appendChild(rightArrow);
		viewer.appendChild(leftZone);
		viewer.appendChild(rightZone);

		// Mouse-following on cover (desktop)
		cover.addEventListener('mousemove', (e) => {
			if (isOpen) return;
			const rect = cover.getBoundingClientRect();
			openButton.style.left = `${e.clientX - rect.left}px`;
			openButton.style.top = `${e.clientY - rect.top}px`;
		});

		// Mouse-following arrows on nav zones (desktop)
		leftZone.addEventListener('mousemove', (e) => {
			const rect = leftZone.getBoundingClientRect();
			leftArrow.style.left = `${e.clientX - rect.left}px`;
			leftArrow.style.top = `${e.clientY - rect.top}px`;
		});

		rightZone.addEventListener('mousemove', (e) => {
			const rect = rightZone.getBoundingClientRect();
			rightArrow.style.left = `${e.clientX - rect.left}px`;
			rightArrow.style.top = `${e.clientY - rect.top}px`;
		});

		// Open sketchbook
		cover.addEventListener('click', () => {
			openSketchbook();
		});

		// Nav zone clicks
		leftZone.addEventListener('click', (e) => {
			e.stopPropagation();
			prevPage();
		});

		rightZone.addEventListener('click', (e) => {
			e.stopPropagation();
			nextPage();
		});

		function openSketchbook() {
			isOpen = true;
			sketchbook.classList.add('is-open');
			currentPage = 0;
			updatePage();
			startAutoAdvance();
		}

		function closeSketchbook() {
			isOpen = false;
			sketchbook.classList.remove('is-open');
			stopAutoAdvance();
			currentPage = 0;
			pages.forEach(page => {
				page.classList.remove('active');
			});
			pages[0].classList.add('active');
		}

		// Navigation
		function goToPage(index) {
			if (index < 0 || index >= pages.length || index === currentPage) return;
			pages[currentPage].classList.remove('active');
			pages[index].classList.add('active');
			currentPage = index;
			updatePage();
			restartAutoAdvance();
		}

		function nextPage() {
			const next = (currentPage + 1) % pages.length;
			goToPage(next);
		}

		function prevPage() {
			const prev = (currentPage - 1 + pages.length) % pages.length;
			goToPage(prev);
		}

		function updatePage() {
			if (currentPageEl) {
				currentPageEl.textContent = currentPage + 1;
			}
		}

		// Auto-advance
		function startAutoAdvance() {
			stopAutoAdvance();
			autoAdvanceTimer = setInterval(() => {
				nextPage();
			}, AUTO_ADVANCE_DELAY);
		}

		function stopAutoAdvance() {
			if (autoAdvanceTimer) {
				clearInterval(autoAdvanceTimer);
				autoAdvanceTimer = null;
			}
		}

		function restartAutoAdvance() {
			stopAutoAdvance();
			startAutoAdvance();
		}

		// Keyboard navigation
		document.addEventListener('keydown', (e) => {
			if (!isOpen) return;

			if (e.key === 'ArrowLeft') {
				e.preventDefault();
				prevPage();
			} else if (e.key === 'ArrowRight') {
				e.preventDefault();
				nextPage();
			} else if (e.key === 'Escape') {
				e.preventDefault();
				closeSketchbook();
			}
		});

		// Reset on scroll (when user scrolls away)
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (!entry.isIntersecting && isOpen) {
					closeSketchbook();
				}
			});
		}, {
			threshold: 0.1
		});

		observer.observe(sketchbook);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initSketchbook);
	} else {
		initSketchbook();
	}
})();
