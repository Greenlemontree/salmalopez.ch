/**
 * GSAP Scroll Animations
 *
 * Handles scroll-triggered animations for the portfolio.
 * Uses GSAP and ScrollTrigger for smooth, performant effects.
 */

(function () {
	'use strict';

	// Wait for GSAP and ScrollTrigger to be available
	function init() {
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
			console.warn('GSAP or ScrollTrigger not loaded');
			return;
		}

		// Register ScrollTrigger plugin
		gsap.registerPlugin(ScrollTrigger);

		// Initialize all animations
		initHeroAnimations();
		initAboutAnimations();
		initSelectedWorksAnimations();
	}

	/**
	 * Hero Section Animations
	 * - Skills text slides in with stagger on page load
	 * - Parallax on scroll (set up immediately but starts from y=0)
	 */
	function initHeroAnimations() {
		// Animate ALL hero skills including repeated ones
		const allHeroSkills = document.querySelectorAll('.hero-skill');
		const heroSection = document.querySelector('.hero-section');

		if (allHeroSkills.length === 0 || !heroSection) return;

		// Set initial state for ALL skills (main + repeated)
		gsap.set(allHeroSkills, {
			opacity: 0,
			y: 80,
			rotationX: -20,
		});

		// Animate ALL skills together with stagger
		gsap.to(allHeroSkills, {
			opacity: 1,
			y: 0,
			rotationX: 0,
			duration: 1,
			ease: 'power3.out',
			stagger: {
				amount: 0.8,
				from: 'center',
			},
			delay: 0.3,
		});

		// No parallax - keeps text centered
	}

	/**
	 * About Section Animations
	 * - Heading slides in from left
	 * - Image fades and scales in
	 * - Text paragraphs stagger in from bottom
	 */
	function initAboutAnimations() {
		const aboutHeading = document.querySelector('.about-heading');
		const aboutImage = document.querySelector('.about-image');
		const aboutTextParagraphs = document.querySelectorAll('.about-text p');

		// About heading animation
		if (aboutHeading) {
			gsap.set(aboutHeading, {
				opacity: 0,
				x: -100,
			});

			gsap.to(aboutHeading, {
				opacity: 1,
				x: 0,
				duration: 1,
				ease: 'power3.out',
				scrollTrigger: {
					trigger: aboutHeading,
					start: 'top 85%',
					end: 'top 50%',
					toggleActions: 'play none none reverse',
				},
			});
		}

		// About image animation
		if (aboutImage) {
			gsap.set(aboutImage, {
				opacity: 0,
				scale: 0.9,
				y: 40,
			});

			gsap.to(aboutImage, {
				opacity: 1,
				scale: 1,
				y: 0,
				duration: 1,
				ease: 'power2.out',
				scrollTrigger: {
					trigger: aboutImage,
					start: 'top 85%',
					end: 'top 50%',
					toggleActions: 'play none none reverse',
				},
			});
		}

		// About text paragraphs stagger animation
		if (aboutTextParagraphs.length > 0) {
			gsap.set(aboutTextParagraphs, {
				opacity: 0,
				y: 30,
			});

			gsap.to(aboutTextParagraphs, {
				opacity: 1,
				y: 0,
				duration: 0.8,
				ease: 'power2.out',
				stagger: 0.15,
				scrollTrigger: {
					trigger: '.about-text',
					start: 'top 85%',
					end: 'top 50%',
					toggleActions: 'play none none reverse',
				},
			});
		}
	}

	/**
	 * Selected Works Section Animations
	 * - Label fades in
	 * - Grid items stagger in (no wrapper animation to avoid grey border flash)
	 */
	function initSelectedWorksAnimations() {
		const selectedWorksLabel = document.querySelector('.selected-works-label');
		const selectedWorkItems = document.querySelectorAll('.selected-work-item');

		// Label animation
		if (selectedWorksLabel) {
			gsap.set(selectedWorksLabel, {
				opacity: 0,
				y: 20,
			});

			gsap.to(selectedWorksLabel, {
				opacity: 1,
				y: 0,
				duration: 0.8,
				ease: 'power2.out',
				scrollTrigger: {
					trigger: selectedWorksLabel,
					start: 'top 90%',
					toggleActions: 'play none none reverse',
				},
			});
		}

		// Grid items stagger (visible through the mask hole)
		// No wrapper animation to avoid grey border flash
		if (selectedWorkItems.length > 0) {
			gsap.set(selectedWorkItems, {
				opacity: 0,
				y: 20,
			});

			gsap.to(selectedWorkItems, {
				opacity: 1,
				y: 0,
				duration: 0.5,
				ease: 'power2.out',
				stagger: {
					amount: 0.3,
					from: 'start',
				},
				scrollTrigger: {
					trigger: '.selected-works-grid',
					start: 'top 80%',
					toggleActions: 'play none none reverse',
				},
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
