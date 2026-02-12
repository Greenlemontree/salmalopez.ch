/**
 * Project Tag Filter
 *
 * Handles filtering projects by tags.
 * Click a tag to show only projects with that tag.
 * Click "All" to show all projects.
 */

(function () {
	'use strict';

	function init() {
		const filterButtons = document.querySelectorAll('.filter-tag');
		const projectDots = document.querySelectorAll('.project-dot-item');

		if (filterButtons.length === 0 || projectDots.length === 0) {
			return;
		}

		/**
		 * Filter projects by tag
		 */
		function filterByTag(selectedTag) {
			projectDots.forEach((dot) => {
				const projectTags = dot.dataset.projectTags || '';
				const tagsArray = projectTags.split(',').filter(Boolean);

				if (selectedTag === 'all' || tagsArray.includes(selectedTag)) {
					dot.classList.remove('is-hidden');
				} else {
					dot.classList.add('is-hidden');
				}
			});

			// Renumber visible projects
			renumberProjects();
		}

		/**
		 * Renumber visible projects sequentially
		 */
		function renumberProjects() {
			const visibleDots = document.querySelectorAll('.project-dot-item:not(.is-hidden)');
			let number = 1;

			visibleDots.forEach((dot) => {
				const numberSpan = dot.querySelector('.project-dot-number');
				if (numberSpan) {
					numberSpan.textContent = String(number).padStart(2, '0');
					number++;
				}
			});
		}

		/**
		 * Handle filter button click
		 */
		function handleFilterClick(e) {
			const button = e.currentTarget;
			const tag = button.dataset.tag;

			// Update active state on buttons
			filterButtons.forEach((btn) => btn.classList.remove('is-active'));
			button.classList.add('is-active');

			// Filter projects
			filterByTag(tag);
		}

		// Add click listeners to filter buttons
		filterButtons.forEach((button) => {
			button.addEventListener('click', handleFilterClick);
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
