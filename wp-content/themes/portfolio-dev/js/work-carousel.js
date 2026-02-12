/**
 * Work Page - GSAP Horizontal Scroll Carousel
 *
 * Features:
 * - Horizontal scroll that only affects carousel section
 * - Snap to each project
 * - Center project scaling (grows as it approaches center)
 * - Filter by project_tag taxonomy
 * - Mobile touch swipe support
 * - Year + Category meta display for center project
 */

(function () {
	'use strict';

	// ==========================================================================
	// CONFIGURATION
	// ==========================================================================

	const CONFIG = {
		// Scale settings - bigger difference for more dramatic effect
		centerScale: 1.0,
		sideScale: 0.55,
		scaleEase: 'power2.out',

		// Snap settings
		snapDuration: { min: 0.2, max: 0.5 },
		snapEase: 'power2.inOut',

		// Touch
		touchThreshold: 50,

		// Selectors
		selectors: {
			section: '.work-carousel-section',
			wrapper: '.carousel-wrapper',
			track: '.carousel-track',
			item: '.carousel-item',
			meta: '.carousel-meta',
			metaYear: '.carousel-meta-year',
			metaCategory: '.carousel-meta-category',
			metaSeparator: '.carousel-meta-separator',
			filterBtn: '.filter-btn',
		},
	};

	// ==========================================================================
	// STATE
	// ==========================================================================

	let state = {
		items: [],
		visibleItems: [],
		currentIndex: 0,
		isAnimating: false,
		scrollTrigger: null,
		touchStartX: 0,
		touchStartY: 0,
		isTouchDevice: false,
		itemWidth: 0,
		gap: 0,
	};

	// DOM Cache
	let DOM = {};

	// ==========================================================================
	// INITIALIZATION
	// ==========================================================================

	function init() {
		// Check for GSAP
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
			console.warn('Work Carousel: GSAP or ScrollTrigger not loaded');
			return;
		}

		gsap.registerPlugin(ScrollTrigger);

		// Cache DOM elements
		DOM.section = document.querySelector(CONFIG.selectors.section);
		DOM.wrapper = document.querySelector(CONFIG.selectors.wrapper);
		DOM.track = document.querySelector(CONFIG.selectors.track);
		DOM.meta = document.querySelector(CONFIG.selectors.meta);
		DOM.metaYear = document.querySelector(CONFIG.selectors.metaYear);
		DOM.metaCategory = document.querySelector(CONFIG.selectors.metaCategory);
		DOM.metaSeparator = document.querySelector(CONFIG.selectors.metaSeparator);
		DOM.filterBtns = document.querySelectorAll(CONFIG.selectors.filterBtn);

		if (!DOM.section || !DOM.track) {
			console.warn('Work Carousel: Required elements not found');
			return;
		}

		// Get all items
		state.items = Array.from(
			document.querySelectorAll(CONFIG.selectors.item)
		);
		state.visibleItems = [...state.items];

		if (state.items.length === 0) {
			console.warn('Work Carousel: No items found');
			return;
		}

		// Detect touch device
		state.isTouchDevice =
			'ontouchstart' in window || navigator.maxTouchPoints > 0;

		// Calculate dimensions
		calculateDimensions();

		// Setup carousel
		setupCarousel();

		// Setup filters
		setupFilters();

		// Setup touch events for mobile
		if (state.isTouchDevice) {
			setupTouchEvents();
		}

		// Setup keyboard navigation
		setupKeyboardNavigation();

		// Setup resize handler
		setupResizeHandler();

		// Initial state
		updateCarouselState(0);

		// Show meta after initial setup
		setTimeout(() => {
			if (DOM.meta) DOM.meta.classList.add('is-visible');
		}, 300);
	}

	// ==========================================================================
	// DIMENSIONS
	// ==========================================================================

	function calculateDimensions() {
		// Get item width from CSS (percentage of viewport)
		const firstItem = state.visibleItems[0];
		if (firstItem) {
			const computedStyle = getComputedStyle(firstItem);
			state.itemWidth = firstItem.offsetWidth;

			// Get gap from track
			const trackStyle = getComputedStyle(DOM.track);
			state.gap = parseFloat(trackStyle.gap) || window.innerWidth * 0.05;
		}
	}

	// ==========================================================================
	// CAROUSEL SETUP
	// ==========================================================================

	function setupCarousel() {
		// Set initial scales
		state.visibleItems.forEach((item, index) => {
			const scale = index === 0 ? CONFIG.centerScale : CONFIG.sideScale;
			gsap.set(item, { scale: scale });
		});

		// Create ScrollTrigger
		createScrollTrigger();
	}

	function createScrollTrigger() {
		// Kill existing ScrollTrigger if any
		if (state.scrollTrigger) {
			state.scrollTrigger.kill();
		}

		const totalItems = state.visibleItems.length;
		if (totalItems <= 1) {
			// Single item, no scroll needed
			updateCarouselState(0);
			return;
		}

		// Calculate total scroll distance
		const scrollDistance = (totalItems - 1) * (state.itemWidth + state.gap);

		// Create timeline with ScrollTrigger
		const tl = gsap.timeline({
			scrollTrigger: {
				trigger: DOM.section,
				start: 'top top',
				end: () => `+=${scrollDistance}`,
				pin: true,
				scrub: 1,
				snap: {
					snapTo: 1 / (totalItems - 1),
					duration: CONFIG.snapDuration,
					ease: CONFIG.snapEase,
					inertia: false,
				},
				onUpdate: (self) => {
					handleScrollUpdate(self.progress, totalItems);
				},
				invalidateOnRefresh: true,
			},
		});

		// Animate track position
		tl.to(DOM.track, {
			x: -scrollDistance,
			ease: 'none',
		});

		// Store reference
		state.scrollTrigger = ScrollTrigger.getAll().find(
			(st) => st.trigger === DOM.section
		);
	}

	function handleScrollUpdate(progress, totalItems) {
		// Calculate which item is in center
		const exactIndex = progress * (totalItems - 1);
		const newIndex = Math.round(exactIndex);

		// Update current index and state
		if (newIndex !== state.currentIndex) {
			state.currentIndex = newIndex;
			updateCarouselState(newIndex);
		}

		// Update scales based on proximity to center
		updateItemScales(exactIndex);
	}

	// ==========================================================================
	// CAROUSEL STATE
	// ==========================================================================

	function updateCarouselState(index) {
		// Update active classes
		state.visibleItems.forEach((item, i) => {
			item.classList.toggle('is-active', i === index);
		});

		// Update meta display
		const activeItem = state.visibleItems[index];
		if (activeItem) {
			const year = activeItem.dataset.year || '';
			const category = activeItem.dataset.category || '';

			if (DOM.metaYear) DOM.metaYear.textContent = year;
			if (DOM.metaCategory) DOM.metaCategory.textContent = category;
			if (DOM.metaSeparator) {
				DOM.metaSeparator.style.display =
					year && category ? 'inline' : 'none';
			}
		}
	}

	function updateItemScales(exactIndex) {
		state.visibleItems.forEach((item, index) => {
			// Calculate distance from center position
			const distance = Math.abs(index - exactIndex);

			// Calculate scale based on distance (closer = bigger)
			// At distance 0 = centerScale, at distance >= 1 = sideScale
			const normalizedDistance = Math.min(distance, 1);
			const scale =
				CONFIG.centerScale -
				normalizedDistance * (CONFIG.centerScale - CONFIG.sideScale);

			gsap.to(item, {
				scale: scale,
				duration: 0.2,
				ease: CONFIG.scaleEase,
				overwrite: 'auto',
			});
		});
	}

	// ==========================================================================
	// FILTERING
	// ==========================================================================

	function setupFilters() {
		DOM.filterBtns.forEach((btn) => {
			btn.addEventListener('click', () => handleFilterClick(btn));
		});
	}

	function handleFilterClick(btn) {
		const filter = btn.dataset.filter;

		// Update active button
		DOM.filterBtns.forEach((b) => b.classList.remove('is-active'));
		btn.classList.add('is-active');

		// Filter items
		filterItems(filter);
	}

	function filterItems(filter) {
		// Hide/show items based on filter
		state.items.forEach((item) => {
			const tags = (item.dataset.tags || '').split(',').map((t) => t.trim());
			const shouldShow = filter === 'all' || tags.includes(filter);

			if (shouldShow) {
				item.classList.remove('is-hidden');
			} else {
				item.classList.add('is-hidden');
			}
		});

		// Update visible items array
		state.visibleItems = state.items.filter(
			(item) => !item.classList.contains('is-hidden')
		);

		// Reset to first item
		state.currentIndex = 0;

		// Rebuild carousel
		rebuildCarousel();
	}

	function rebuildCarousel() {
		// Kill existing ScrollTrigger
		ScrollTrigger.getAll().forEach((st) => {
			if (st.trigger === DOM.section) {
				st.kill();
			}
		});

		// Reset track position
		gsap.set(DOM.track, { x: 0 });

		// Recalculate dimensions
		calculateDimensions();

		// Reset scales
		state.visibleItems.forEach((item, index) => {
			const scale = index === 0 ? CONFIG.centerScale : CONFIG.sideScale;
			gsap.set(item, { scale: scale });
		});

		// Recreate ScrollTrigger after a brief delay
		setTimeout(() => {
			createScrollTrigger();
			updateCarouselState(0);

			// Scroll to top of carousel section
			const sectionTop =
				DOM.section.getBoundingClientRect().top + window.pageYOffset;
			window.scrollTo({
				top: sectionTop,
				behavior: 'smooth',
			});
		}, 100);
	}

	// ==========================================================================
	// TOUCH SUPPORT
	// ==========================================================================

	function setupTouchEvents() {
		DOM.wrapper.addEventListener('touchstart', handleTouchStart, {
			passive: true,
		});
		DOM.wrapper.addEventListener('touchmove', handleTouchMove, {
			passive: false,
		});
		DOM.wrapper.addEventListener('touchend', handleTouchEnd, {
			passive: true,
		});
	}

	function handleTouchStart(e) {
		state.touchStartX = e.touches[0].clientX;
		state.touchStartY = e.touches[0].clientY;
	}

	function handleTouchMove(e) {
		if (!state.touchStartX) return;

		const touchX = e.touches[0].clientX;
		const touchY = e.touches[0].clientY;
		const diffX = state.touchStartX - touchX;
		const diffY = state.touchStartY - touchY;

		// If horizontal swipe is dominant, prevent vertical scroll
		if (Math.abs(diffX) > Math.abs(diffY) * 1.5) {
			e.preventDefault();
		}
	}

	function handleTouchEnd(e) {
		if (!state.touchStartX) return;

		const touchEndX = e.changedTouches[0].clientX;
		const diffX = state.touchStartX - touchEndX;

		// Only navigate if swipe exceeds threshold
		if (Math.abs(diffX) > CONFIG.touchThreshold) {
			const totalItems = state.visibleItems.length;

			if (diffX > 0 && state.currentIndex < totalItems - 1) {
				// Swipe left - go to next
				navigateToItem(state.currentIndex + 1);
			} else if (diffX < 0 && state.currentIndex > 0) {
				// Swipe right - go to previous
				navigateToItem(state.currentIndex - 1);
			}
		}

		state.touchStartX = 0;
		state.touchStartY = 0;
	}

	function navigateToItem(index) {
		const totalItems = state.visibleItems.length;

		// Clamp index
		index = Math.max(0, Math.min(index, totalItems - 1));

		if (index === state.currentIndex || state.isAnimating) return;
		if (totalItems <= 1) return;

		state.isAnimating = true;

		// Calculate scroll position
		const scrollDistance = (totalItems - 1) * (state.itemWidth + state.gap);
		const progress = index / (totalItems - 1);
		const sectionTop =
			DOM.section.getBoundingClientRect().top + window.pageYOffset;
		const targetScroll = sectionTop + progress * scrollDistance;

		// Scroll to position
		window.scrollTo({
			top: targetScroll,
			behavior: 'smooth',
		});

		// Reset animation flag
		setTimeout(() => {
			state.isAnimating = false;
		}, 500);
	}

	// ==========================================================================
	// KEYBOARD NAVIGATION
	// ==========================================================================

	function setupKeyboardNavigation() {
		document.addEventListener('keydown', (e) => {
			// Only if carousel section is in view
			const rect = DOM.section.getBoundingClientRect();
			const isInView = rect.top < window.innerHeight && rect.bottom > 0;

			if (!isInView) return;

			if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
				e.preventDefault();
				navigateToItem(state.currentIndex + 1);
			} else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
				e.preventDefault();
				navigateToItem(state.currentIndex - 1);
			}
		});
	}

	// ==========================================================================
	// RESIZE HANDLER
	// ==========================================================================

	function setupResizeHandler() {
		let resizeTimeout;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(() => {
				calculateDimensions();
				rebuildCarousel();
			}, 250);
		});
	}

	// ==========================================================================
	// INIT ON DOM READY
	// ==========================================================================

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	// Refresh ScrollTrigger after all images load
	window.addEventListener('load', () => {
		calculateDimensions();
		ScrollTrigger.refresh();
	});
})();
