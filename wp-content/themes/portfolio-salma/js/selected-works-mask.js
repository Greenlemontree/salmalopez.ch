/**
 * Selected Works Mask Morph Controller
 *
 * Creates a morphing SVG polygon mask that transforms from an irregular
 * shape to a full rectangle when dragged.
 *
 * Features:
 * - 12 control points that all move together as one unit
 * - Drag any point to morph the entire shape
 * - Smooth eased interpolation between start and end shapes
 */

(function () {
	'use strict';

	// SVG viewBox dimensions
	const SVG_WIDTH = 600;
	const SVG_HEIGHT = 800;

	// ==========================================================================
	// START SHAPE - Irregular, interesting polygon
	// ==========================================================================
	// More randomized/organic shape for visual interest
	// 12 points creating an asymmetric star-like form
	// ==========================================================================
	const START_POINTS = [
		{ x: 300, y: 35 },    // 0: top center - slightly off
		{ x: 420, y: 95 },    // 1: top right inner
		{ x: 545, y: 140 },   // 2: right upper - pushed out
		{ x: 480, y: 280 },   // 3: right upper inner - pulled in
		{ x: 565, y: 420 },   // 4: right lower - pushed out
		{ x: 440, y: 520 },   // 5: right lower inner
		{ x: 300, y: 760 },   // 6: bottom center
		{ x: 160, y: 520 },   // 7: left lower inner
		{ x: 35, y: 450 },    // 8: left lower - pushed out
		{ x: 120, y: 300 },   // 9: left upper inner - pulled in
		{ x: 55, y: 160 },    // 10: left upper - pushed out
		{ x: 180, y: 95 },    // 11: top left inner
	];

	// ==========================================================================
	// END SHAPE - Rectangle with padding from edges
	// ==========================================================================
	const EDGE_PADDING = 25;

	const END_POINTS = [
		{ x: 300, y: EDGE_PADDING },                          // 0: top center
		{ x: 450, y: EDGE_PADDING },                          // 1: top right area
		{ x: SVG_WIDTH - EDGE_PADDING, y: EDGE_PADDING },     // 2: top right corner
		{ x: SVG_WIDTH - EDGE_PADDING, y: 300 },              // 3: right upper
		{ x: SVG_WIDTH - EDGE_PADDING, y: 500 },              // 4: right lower
		{ x: SVG_WIDTH - EDGE_PADDING, y: SVG_HEIGHT - EDGE_PADDING }, // 5: bottom right
		{ x: 300, y: SVG_HEIGHT - EDGE_PADDING },             // 6: bottom center
		{ x: EDGE_PADDING, y: SVG_HEIGHT - EDGE_PADDING },    // 7: bottom left
		{ x: EDGE_PADDING, y: 500 },                          // 8: left lower
		{ x: EDGE_PADDING, y: 300 },                          // 9: left upper
		{ x: EDGE_PADDING, y: EDGE_PADDING },                 // 10: top left corner
		{ x: 150, y: EDGE_PADDING },                          // 11: top left area
	];

	// Configuration
	const CONFIG = {
		pointRadius: 8,
		touchRadius: 24,
		pointFill: '#EAFF00',
		pointFillHover: '#F5FF66',
		pointFillDrag: '#BFCC00',
		pointStroke: 'none',
		pointStrokeWidth: 0,
		dragSensitivity: 120,
		easeProgress: true,
	};

	// Responsive scaling config
	const RESPONSIVE = {
		referenceWidth: 1200,
		minScale: 0.75,
		maxScale: 1.0,
	};

	// State
	let isDragging = false;
	let activePointIndex = null;
	let dragStartPos = null;
	let dragStartProgress = 0;
	let morphProgress = 0; // 0 = start shape, 1 = end shape (rectangle)
	let points = [];
	let currentStartPoints = [];
	let currentEndPoints = [];
	let polygon = null;
	let controlPointsGroup = null;
	let svg = null;
	let svgPoint = null;

	/**
	 * Initialize
	 */
	function init() {
		svg = document.querySelector('.selected-works-svg');
		polygon = document.getElementById('selected-works-polygon');
		controlPointsGroup = document.getElementById('selected-works-control-points');

		if (!svg || !polygon || !controlPointsGroup) {
			console.warn('Selected Works mask: Required elements not found');
			return;
		}

		svgPoint = svg.createSVGPoint();

		// Calculate responsive shapes
		calculateResponsiveShapes();

		// Initialize points from progress
		updatePointsFromProgress();

		// Create control points
		createControlPoints();

		// Set up events
		setupEventListeners();

		// Update polygon
		updatePolygon();

		console.log('Selected Works mask morph: initialized with', points.length, 'control points');
	}

	/**
	 * Linear interpolation
	 */
	function lerp(start, end, t) {
		return start + (end - start) * t;
	}

	/**
	 * Clamp value between min and max
	 */
	function clamp(value, min, max) {
		return Math.max(min, Math.min(max, value));
	}

	/**
	 * Ease out cubic for smooth deceleration
	 */
	function easeOutCubic(t) {
		return 1 - Math.pow(1 - t, 3);
	}

	/**
	 * Calculate scale factor based on viewport width
	 */
	function calculateScale() {
		const viewportWidth = window.innerWidth;
		let scale = viewportWidth / RESPONSIVE.referenceWidth;
		return clamp(scale, RESPONSIVE.minScale, RESPONSIVE.maxScale);
	}

	/**
	 * Scale points from center
	 */
	function scalePointsFromCenter(pointsArray, scale) {
		const centerX = SVG_WIDTH / 2;
		const centerY = SVG_HEIGHT / 2;
		return pointsArray.map(p => ({
			x: centerX + (p.x - centerX) * scale,
			y: centerY + (p.y - centerY) * scale,
		}));
	}

	/**
	 * Calculate responsive shapes based on viewport
	 */
	function calculateResponsiveShapes() {
		const scaleFactor = calculateScale();
		// Scale the start shape, keep end shape at full size
		currentStartPoints = scalePointsFromCenter(START_POINTS, scaleFactor);
		currentEndPoints = END_POINTS.map(p => ({ ...p })); // Don't scale end - should fill container
	}

	/**
	 * Update points based on morph progress
	 */
	function updatePointsFromProgress() {
		const t = CONFIG.easeProgress ? easeOutCubic(morphProgress) : morphProgress;
		points = currentStartPoints.map((startPoint, i) => {
			const endPoint = currentEndPoints[i];
			return {
				x: lerp(startPoint.x, endPoint.x, t),
				y: lerp(startPoint.y, endPoint.y, t),
			};
		});
	}

	/**
	 * Handle window resize
	 */
	function onResize() {
		if (isDragging) return;

		calculateResponsiveShapes();
		updatePointsFromProgress();
		updatePolygon();
		updateAllControlPoints();
	}

	/**
	 * Update all control point positions
	 */
	function updateAllControlPoints() {
		points.forEach((point, index) => {
			const group = controlPointsGroup.children[index];
			if (group) {
				const circles = group.querySelectorAll('circle');
				circles.forEach((circle) => {
					circle.setAttribute('cx', point.x);
					circle.setAttribute('cy', point.y);
				});
			}
		});
	}

	/**
	 * Create SVG circle elements for control points
	 */
	function createControlPoints() {
		controlPointsGroup.innerHTML = '';

		points.forEach((point, index) => {
			const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
			group.setAttribute('data-index', index);
			group.classList.add('control-point', 'sw-control-point');
			group.style.cursor = 'grab';

			// Invisible touch target
			const touchTarget = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			touchTarget.setAttribute('cx', point.x);
			touchTarget.setAttribute('cy', point.y);
			touchTarget.setAttribute('r', CONFIG.touchRadius);
			touchTarget.setAttribute('fill', 'transparent');
			touchTarget.style.touchAction = 'none';
			group.appendChild(touchTarget);

			// Visible control point
			const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			circle.setAttribute('cx', point.x);
			circle.setAttribute('cy', point.y);
			circle.setAttribute('r', CONFIG.pointRadius);
			circle.setAttribute('fill', CONFIG.pointFill);
			circle.setAttribute('stroke', CONFIG.pointStroke);
			circle.setAttribute('stroke-width', CONFIG.pointStrokeWidth);
			circle.classList.add('sw-point-visible');
			group.appendChild(circle);

			controlPointsGroup.appendChild(group);
		});
	}

	/**
	 * Update polygon from points array
	 */
	function updatePolygon() {
		const pointsStr = points.map((p) => `${p.x},${p.y}`).join(' ');
		polygon.setAttribute('points', pointsStr);
		// Also update the background polygon to stay in sync
		const bgPolygon = document.getElementById('selected-works-bg-polygon');
		if (bgPolygon) {
			bgPolygon.setAttribute('points', pointsStr);
		}
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
	 * Calculate drag delta based on point position
	 * Dragging outward = positive (expand), inward = negative (contract)
	 */
	function calculateDragDelta(currentPos, startPos, pointIndex) {
		const point = currentStartPoints[pointIndex];
		const centerX = SVG_WIDTH / 2;
		const centerY = SVG_HEIGHT / 2;

		// Direction from center to this point
		const dirX = point.x - centerX;
		const dirY = point.y - centerY;
		const length = Math.sqrt(dirX * dirX + dirY * dirY);

		if (length === 0) return 0;

		// Normalize direction
		const normX = dirX / length;
		const normY = dirY / length;

		// Movement vector
		const moveX = currentPos.x - startPos.x;
		const moveY = currentPos.y - startPos.y;

		// Project movement onto the outward direction
		return moveX * normX + moveY * normY;
	}

	/**
	 * Set up event listeners
	 */
	function setupEventListeners() {
		controlPointsGroup.addEventListener('pointerdown', onPointerDown);
		document.addEventListener('pointermove', onPointerMove);
		document.addEventListener('pointerup', onPointerUp);
		document.addEventListener('pointercancel', onPointerUp);

		controlPointsGroup.addEventListener('touchstart', (e) => {
			const group = e.target.closest('.sw-control-point');
			if (group) {
				e.preventDefault();
			}
		}, { passive: false });

		controlPointsGroup.addEventListener('touchmove', (e) => {
			if (isDragging) {
				e.preventDefault();
			}
		}, { passive: false });

		controlPointsGroup.addEventListener('pointerenter', onPointerEnter, true);
		controlPointsGroup.addEventListener('pointerleave', onPointerLeave, true);

		window.addEventListener('resize', onResize);
	}

	/**
	 * Pointer down handler
	 */
	function onPointerDown(e) {
		const group = e.target.closest('.sw-control-point');
		if (!group) return;

		e.preventDefault();
		e.stopPropagation();

		group.setPointerCapture(e.pointerId);

		isDragging = true;
		activePointIndex = parseInt(group.getAttribute('data-index'), 10);
		dragStartPos = screenToSVG(e.clientX, e.clientY);
		dragStartProgress = morphProgress;

		// Highlight all points
		Array.from(controlPointsGroup.children).forEach(pointGroup => {
			const visibleCircle = pointGroup.querySelector('.sw-point-visible');
			if (visibleCircle) {
				visibleCircle.setAttribute('fill', CONFIG.pointFillDrag);
			}
		});

		group.style.cursor = 'grabbing';
		document.body.style.cursor = 'grabbing';
	}

	/**
	 * Pointer move handler
	 */
	function onPointerMove(e) {
		if (!isDragging || activePointIndex === null) return;

		e.preventDefault();

		const currentPos = screenToSVG(e.clientX, e.clientY);
		const dragDelta = calculateDragDelta(currentPos, dragStartPos, activePointIndex);
		const progressDelta = dragDelta / CONFIG.dragSensitivity;

		// Update progress for all points together
		morphProgress = clamp(dragStartProgress + progressDelta, 0, 1);

		updatePointsFromProgress();

		requestAnimationFrame(() => {
			updateAllControlPoints();
			updatePolygon();
		});
	}

	/**
	 * Pointer up handler
	 */
	function onPointerUp(e) {
		if (!isDragging) return;

		isDragging = false;

		// Reset colors for all points
		Array.from(controlPointsGroup.children).forEach(pointGroup => {
			const visibleCircle = pointGroup.querySelector('.sw-point-visible');
			if (visibleCircle) {
				visibleCircle.setAttribute('fill', CONFIG.pointFill);
			}
		});

		if (activePointIndex !== null) {
			const group = controlPointsGroup.children[activePointIndex];
			if (group) {
				group.releasePointerCapture(e.pointerId);
				group.style.cursor = 'grab';
			}
		}

		activePointIndex = null;
		dragStartPos = null;
		document.body.style.cursor = '';
	}

	/**
	 * Hover enter
	 */
	function onPointerEnter(e) {
		if (isDragging) return;
		const group = e.target.closest('.sw-control-point');
		if (group) {
			const visibleCircle = group.querySelector('.sw-point-visible');
			if (visibleCircle) {
				visibleCircle.setAttribute('fill', CONFIG.pointFillHover);
			}
		}
	}

	/**
	 * Hover leave
	 */
	function onPointerLeave(e) {
		if (isDragging) return;
		const group = e.target.closest('.sw-control-point');
		if (group) {
			const visibleCircle = group.querySelector('.sw-point-visible');
			if (visibleCircle) {
				visibleCircle.setAttribute('fill', CONFIG.pointFill);
			}
		}
	}

	/**
	 * Get/set progress
	 */
	function getProgress() {
		return morphProgress;
	}

	function setProgress(value) {
		morphProgress = clamp(value, 0, 1);
		updatePointsFromProgress();
		updateAllControlPoints();
		updatePolygon();
	}

	// Expose API
	window.SelectedWorksMask = {
		getProgress,
		setProgress,
		getPoints: () => points.map(p => ({ ...p })),
		getPointsString: () => points.map(p => `${Math.round(p.x)},${Math.round(p.y)}`).join(' '),
	};

	// Initialize when ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
