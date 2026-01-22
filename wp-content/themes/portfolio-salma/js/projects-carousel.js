/**
 * Projects Horizontal Carousel
 *
 * GSAP-powered horizontal scrolling carousel for the projects archive.
 * Features:
 * - Horizontal drag scrolling
 * - Yellow circle overlay that shrinks on hover
 * - Text rotation animation on hover
 * - Tag filtering
 */

(function () {
	'use strict';

	function init() {
		const carousel = document.getElementById('projects-carousel');
		const items = document.querySelectorAll('.project-carousel-item');
		const filterBtns = document.querySelectorAll('.carousel-filter-btn');

		if (!carousel || items.length === 0) return;

		// Initialize GSAP Draggable for horizontal scroll
		initDraggable();

		// Initialize hover animations
		initHoverAnimations();

		// Initialize filter buttons
		initFilters();

		/**
		 * Initialize GSAP Draggable for horizontal scrolling
		 */
		function initDraggable() {
			// Calculate total width
			let totalWidth = 0;
			items.forEach(item => {
				totalWidth += item.offsetWidth + parseInt(getComputedStyle(item).marginRight || 0);
			});

			const wrapperWidth = carousel.parentElement.offsetWidth;
			const maxScroll = Math.max(0, totalWidth - wrapperWidth);

			// Create draggable
			if (typeof gsap !== 'undefined' && gsap.registerPlugin) {
				gsap.registerPlugin(Draggable);

				Draggable.create(carousel, {
					type: 'x',
					bounds: {
						minX: -maxScroll,
						maxX: 0
					},
					inertia: true,
					edgeResistance: 0.65,
					throwResistance: 2000,
					cursor: 'grab',
					activeCursor: 'grabbing',
					onDrag: updateActiveItem,
					onThrowUpdate: updateActiveItem
				});
			}

			// Also allow mouse wheel horizontal scroll
			carousel.parentElement.addEventListener('wheel', (e) => {
				if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
					// Already horizontal scroll, let it happen
					return;
				}
				e.preventDefault();
				const currentX = gsap.getProperty(carousel, 'x') || 0;
				const newX = Math.max(-maxScroll, Math.min(0, currentX - e.deltaY));
				gsap.to(carousel, {
					x: newX,
					duration: 0.5,
					ease: 'power2.out',
					onUpdate: updateActiveItem
				});
			}, { passive: false });
		}

		/**
		 * Update which item is "active" (center of viewport)
		 */
		function updateActiveItem() {
			const wrapperRect = carousel.parentElement.getBoundingClientRect();
			const centerX = wrapperRect.left + wrapperRect.width / 2;

			let closestItem = null;
			let closestDistance = Infinity;

			items.forEach(item => {
				if (item.classList.contains('is-hidden')) return;

				const itemRect = item.getBoundingClientRect();
				const itemCenterX = itemRect.left + itemRect.width / 2;
				const distance = Math.abs(centerX - itemCenterX);

				if (distance < closestDistance) {
					closestDistance = distance;
					closestItem = item;
				}
			});

			// Update active class
			items.forEach(item => item.classList.remove('is-center'));
			if (closestItem) {
				closestItem.classList.add('is-center');
			}
		}

		/**
		 * Initialize hover animations for yellow circles
		 */
		function initHoverAnimations() {
			items.forEach(item => {
				const circle = item.querySelector('.project-circle-overlay');
				const title = item.querySelector('.project-circle-title');

				if (!circle || !title) return;

				// Hover in - shrink circle, rotate text
				item.addEventListener('mouseenter', () => {
					gsap.to(circle, {
						scale: 0.85,
						duration: 0.4,
						ease: 'power2.out'
					});
					gsap.to(title, {
						rotation: -15,
						duration: 0.4,
						ease: 'power2.out'
					});
				});

				// Hover out - restore
				item.addEventListener('mouseleave', () => {
					gsap.to(circle, {
						scale: 1,
						duration: 0.4,
						ease: 'power2.out'
					});
					gsap.to(title, {
						rotation: 0,
						duration: 0.4,
						ease: 'power2.out'
					});
				});
			});
		}

		/**
		 * Initialize filter buttons
		 */
		function initFilters() {
			filterBtns.forEach(btn => {
				btn.addEventListener('click', () => {
					const filter = btn.dataset.filter;

					// Update active button
					filterBtns.forEach(b => b.classList.remove('is-active'));
					btn.classList.add('is-active');

					// Filter items
					items.forEach(item => {
						const tags = item.dataset.tags ? item.dataset.tags.split(',') : [];

						if (filter === 'all' || tags.includes(filter)) {
							item.classList.remove('is-hidden');
							gsap.to(item, {
								opacity: 1,
								scale: 1,
								duration: 0.4,
								ease: 'power2.out'
							});
						} else {
							gsap.to(item, {
								opacity: 0,
								scale: 0.8,
								duration: 0.3,
								ease: 'power2.in',
								onComplete: () => item.classList.add('is-hidden')
							});
						}
					});

					// Recalculate draggable bounds after filter
					setTimeout(() => {
						initDraggable();
						updateActiveItem();
					}, 400);
				});
			});
		}

		// Initial active item update
		setTimeout(updateActiveItem, 100);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
