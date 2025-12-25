/**
 * Hero Mask Interactive Controller
 *
 * Creates draggable control points for the SVG polygon mask.
 * The mask creates a "hole" in the hero image, revealing text underneath.
 *
 * Control points are always visible and draggable.
 * Uses Pointer Events API for unified mouse/touch support.
 * All coordinates are in absolute SVG units (viewBox: 0 0 1000 600).
 */

(function () {
	'use strict';

	// SVG viewBox dimensions
	const SVG_WIDTH = 1000;
	const SVG_HEIGHT = 600;

	// Configuration
	const CONFIG = {
		pointRadius: 14,
		pointFill: '#FFD700',
		pointFillHover: '#FFEC00',
		pointFillDrag: '#FF6B00',
		pointStroke: '#000',
		pointStrokeWidth: 2,
		boundsPadding: 20,
	};

	// State
	let isDragging = false;
	let activePointIndex = null;
	let points = [];
	let polygon = null;
	let controlPointsGroup = null;
	let svg = null;
	let svgPoint = null;

	/**
	 * Initialize
	 */
	function init() {
		svg = document.querySelector('.hero-svg');
		polygon = document.getElementById('hero-polygon');
		controlPointsGroup = document.getElementById('hero-control-points');

		if (!svg || !polygon || !controlPointsGroup) {
			console.warn('Hero mask: Required elements not found');
			return;
		}

		// Create reusable SVG point for coordinate transforms
		svgPoint = svg.createSVGPoint();

		// Parse polygon points
		parsePolygonPoints();

		// Create control point circles
		createControlPoints();

		// Set up event listeners
		setupEventListeners();

		console.log('Hero mask: initialized with', points.length, 'control points');
	}

	/**
	 * Parse polygon points attribute
	 */
	function parsePolygonPoints() {
		const pointsAttr = polygon.getAttribute('points');
		points = pointsAttr.trim().split(/\s+/).map((pair) => {
			const [x, y] = pair.split(',').map(Number);
			return { x, y };
		});
	}

	/**
	 * Create SVG circle elements for control points
	 */
	function createControlPoints() {
		controlPointsGroup.innerHTML = '';

		points.forEach((point, index) => {
			const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			circle.setAttribute('cx', point.x);
			circle.setAttribute('cy', point.y);
			circle.setAttribute('r', CONFIG.pointRadius);
			circle.setAttribute('fill', CONFIG.pointFill);
			circle.setAttribute('stroke', CONFIG.pointStroke);
			circle.setAttribute('stroke-width', CONFIG.pointStrokeWidth);
			circle.setAttribute('data-index', index);
			circle.classList.add('control-point');
			circle.style.cursor = 'grab';
			circle.style.touchAction = 'none';

			controlPointsGroup.appendChild(circle);
		});
	}

	/**
	 * Update single control point position
	 */
	function updateControlPoint(index) {
		const circle = controlPointsGroup.children[index];
		if (circle) {
			circle.setAttribute('cx', points[index].x);
			circle.setAttribute('cy', points[index].y);
		}
	}

	/**
	 * Update polygon from points array
	 */
	function updatePolygon() {
		const pointsStr = points.map((p) => `${p.x},${p.y}`).join(' ');
		polygon.setAttribute('points', pointsStr);
	}

	/**
	 * Convert screen coordinates to SVG coordinates
	 */
	function screenToSVG(clientX, clientY) {
		svgPoint.x = clientX;
		svgPoint.y = clientY;

		const ctm = svg.getScreenCTM();
		if (!ctm) return { x: 0, y: 0 };

		const transformed = svgPoint.matrixTransform(ctm.inverse());
		return { x: transformed.x, y: transformed.y };
	}

	/**
	 * Clamp point to SVG bounds
	 */
	function clampToBounds(x, y) {
		return {
			x: Math.max(CONFIG.boundsPadding, Math.min(SVG_WIDTH - CONFIG.boundsPadding, x)),
			y: Math.max(CONFIG.boundsPadding, Math.min(SVG_HEIGHT - CONFIG.boundsPadding, y)),
		};
	}

	/**
	 * Set up event listeners
	 */
	function setupEventListeners() {
		// Pointer down on control points
		controlPointsGroup.addEventListener('pointerdown', onPointerDown);

		// Global move/up on document
		document.addEventListener('pointermove', onPointerMove);
		document.addEventListener('pointerup', onPointerUp);
		document.addEventListener('pointercancel', onPointerUp);

		// Prevent touch scrolling when dragging
		svg.addEventListener('touchstart', (e) => {
			if (e.target.classList.contains('control-point')) {
				e.preventDefault();
			}
		}, { passive: false });

		// Hover effects
		controlPointsGroup.addEventListener('pointerenter', onPointerEnter, true);
		controlPointsGroup.addEventListener('pointerleave', onPointerLeave, true);
	}

	/**
	 * Pointer down handler
	 */
	function onPointerDown(e) {
		const circle = e.target.closest('.control-point');
		if (!circle) return;

		e.preventDefault();
		e.stopPropagation();

		circle.setPointerCapture(e.pointerId);

		isDragging = true;
		activePointIndex = parseInt(circle.getAttribute('data-index'), 10);

		circle.setAttribute('fill', CONFIG.pointFillDrag);
		circle.style.cursor = 'grabbing';
		document.body.style.cursor = 'grabbing';
	}

	/**
	 * Pointer move handler
	 */
	function onPointerMove(e) {
		if (!isDragging || activePointIndex === null) return;

		e.preventDefault();

		const svgCoords = screenToSVG(e.clientX, e.clientY);
		const clamped = clampToBounds(svgCoords.x, svgCoords.y);

		points[activePointIndex] = clamped;

		requestAnimationFrame(() => {
			updateControlPoint(activePointIndex);
			updatePolygon();
		});
	}

	/**
	 * Pointer up handler
	 */
	function onPointerUp(e) {
		if (!isDragging) return;

		isDragging = false;

		if (activePointIndex !== null) {
			const circle = controlPointsGroup.children[activePointIndex];
			if (circle) {
				circle.releasePointerCapture(e.pointerId);
				circle.setAttribute('fill', CONFIG.pointFill);
				circle.style.cursor = 'grab';
			}
		}

		activePointIndex = null;
		document.body.style.cursor = '';
	}

	/**
	 * Hover enter
	 */
	function onPointerEnter(e) {
		if (isDragging) return;
		if (e.target.classList.contains('control-point')) {
			e.target.setAttribute('fill', CONFIG.pointFillHover);
		}
	}

	/**
	 * Hover leave
	 */
	function onPointerLeave(e) {
		if (isDragging) return;
		if (e.target.classList.contains('control-point')) {
			e.target.setAttribute('fill', CONFIG.pointFill);
		}
	}

	/**
	 * Get current points
	 */
	function getPoints() {
		return points.map((p) => ({ ...p }));
	}

	/**
	 * Get points as string
	 */
	function getPointsString() {
		return points.map((p) => `${Math.round(p.x)},${Math.round(p.y)}`).join(' ');
	}

	// Expose API
	window.HeroMask = {
		getPoints,
		getPointsString,
	};

	// Initialize when ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
