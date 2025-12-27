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
		initNavColorInversion();
		initGalleryAnimations();
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
	 * About Section Animations - Two Column with Circle Reveal
	 * - Yellow overlay with circular hole that grows from center
	 * - Like a spotlight opening up to reveal content
	 */
	function initAboutAnimations() {
		const aboutSection = document.querySelector('.about-section');
		const revealOverlay = document.querySelector('.about-reveal-overlay');
		const aboutHeading = document.querySelector('.about-heading');
		const aboutImage = document.querySelector('.about-image');
		const aboutTextParagraphs = document.querySelectorAll('.about-text p');

		if (!aboutSection || !revealOverlay) return;

		// Set initial hidden states for content FIRST
		if (aboutHeading) {
			gsap.set(aboutHeading, { opacity: 0, y: 30 });
		}
		if (aboutImage) {
			gsap.set(aboutImage, { opacity: 0, scale: 0.95 });
		}
		if (aboutTextParagraphs.length > 0) {
			gsap.set(aboutTextParagraphs, { opacity: 0, y: 20 });
		}

		// Set up the overlay with box-shadow trick:
		// A circular element with massive box-shadow creates yellow "frame" around transparent center
		gsap.set(revealOverlay, {
			position: 'absolute',
			background: 'transparent',
			boxShadow: '0 0 0 100vmax #EAFF00',
			borderRadius: '50%',
			width: 0,
			height: 0,
			top: '50%',
			left: '50%',
			xPercent: -50,
			yPercent: -50,
		});

		// Circle reveal animation timeline
		const revealTl = gsap.timeline({
			scrollTrigger: {
				trigger: aboutSection,
				start: 'top 50%', // Triggers when section is more visible
				toggleActions: 'play none none reverse',
			},
		});

		// Circle hole grows from center - revealing content (slower)
		revealTl.to(revealOverlay, {
			width: '300vmax',
			height: '300vmax',
			duration: 1.8,
			ease: 'power2.out',
		});

		// Content fades in as hole opens (adjusted timing for slower reveal)
		if (aboutImage) {
			revealTl.to(
				aboutImage,
				{
					opacity: 1,
					scale: 1,
					duration: 1,
					ease: 'power2.out',
				},
				'-=1.4'
			);
		}

		if (aboutHeading) {
			revealTl.to(
				aboutHeading,
				{
					opacity: 1,
					y: 0,
					duration: 0.8,
					ease: 'power2.out',
				},
				'-=1.1'
			);
		}

		if (aboutTextParagraphs.length > 0) {
			revealTl.to(
				aboutTextParagraphs,
				{
					opacity: 1,
					y: 0,
					duration: 0.7,
					ease: 'power2.out',
					stagger: 0.15,
				},
				'-=0.8'
			);
		}
	}

	/**
	 * Color Inversion Effect
	 * - Nav stays gray (light) through hero and about sections
	 * - When entering projects section: nav goes dark + projects & about shift to light
	 * - Chrome dino game style color inversion
	 */
	function initNavColorInversion() {
		const siteHeader = document.querySelector('.site-header');
		const aboutSection = document.querySelector('.about-section');
		const projectsSection = document.querySelector('.projects-section');

		if (!siteHeader || !projectsSection) return;

		// Color shift when entering projects section
		// Nav goes dark, projects AND about go from dark to light
		ScrollTrigger.create({
			trigger: projectsSection,
			start: 'top 50%',
			onEnter: () => {
				projectsSection.classList.add('color-shifted');
				if (aboutSection) aboutSection.classList.add('color-shifted');
				siteHeader.classList.add('nav-inverted');
			},
			onLeaveBack: () => {
				projectsSection.classList.remove('color-shifted');
				if (aboutSection) aboutSection.classList.remove('color-shifted');
				siteHeader.classList.remove('nav-inverted');
			},
		});
	}

	/**
	 * Projects Dot Grid Animations
	 * - Dots and numbers stagger in on scroll
	 */
	function initGalleryAnimations() {
		const projectsSection = document.querySelector('.projects-section');
		const dotItems = document.querySelectorAll('.project-dot-item');

		if (!projectsSection) return;

		// Dot items stagger in
		if (dotItems.length > 0) {
			gsap.set(dotItems, {
				opacity: 0,
				y: 15,
			});

			gsap.to(dotItems, {
				opacity: 1,
				y: 0,
				duration: 0.5,
				ease: 'power2.out',
				stagger: {
					amount: 0.6,
					from: 'start',
				},
				scrollTrigger: {
					trigger: '.projects-dot-grid',
					start: 'top 85%',
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
