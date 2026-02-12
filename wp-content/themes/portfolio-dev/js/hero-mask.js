/**
 * Hero Mask Morph Controller
 *
 * Creates a morphing SVG polygon mask that transforms from an irregular star
 * shape to a full-screen rectangle when dragged.
 *
 * Features:
 * - 12 control points in 4 groups of 3 (linked points)
 * - Drag any point to morph only its group
 * - Responsive: shape adapts to viewport aspect ratio and width
 * - Smooth interpolation between "wide" and "tall" shape variants
 */

(function () {
	'use strict';

	// SVG viewBox dimensions
	const SVG_WIDTH = 1000;
	const SVG_HEIGHT = 600;

	// ==========================================================================
	// POINT GROUPS - 4 groups of 3 linked points
	// ==========================================================================
	// When you drag one point, only its group morphs together
	// Groups are spread across the shape for interesting reveal patterns
	// ==========================================================================
	const POINT_GROUPS = [
		[0, 1, 11],   // Group A: top-left corner region
		[2, 3, 4],    // Group B: top-right corner region
		[5, 6, 7],    // Group C: bottom-right corner region
		[8, 9, 10],   // Group D: bottom-left corner region
	];

	// Map each point index to its group index for quick lookup
	const POINT_TO_GROUP = {};
	POINT_GROUPS.forEach((group, groupIndex) => {
		group.forEach(pointIndex => {
			POINT_TO_GROUP[pointIndex] = groupIndex;
		});
	});

	// ==========================================================================
	// RESPONSIVE SHAPE DEFINITIONS
	// ==========================================================================
	// Two base shapes that get interpolated based on aspect ratio:
	// - WIDE: for landscape/desktop viewports
	// - TALL: for portrait/mobile viewports (more compact, centered)
	// Made crazier/more irregular for visual interest
	// ==========================================================================

	// Wide viewport shape (desktop landscape) - wide but short
	const WIDE_START = [
		{ x: 150, y: 180 },   // 0: top-left
		{ x: 380, y: 220 },   // 1: top-center-left - dipped
		{ x: 620, y: 170 },   // 2: top-center-right
		{ x: 870, y: 200 },   // 3: top-right
		{ x: 800, y: 270 },   // 4: right-upper - pulled inward
		{ x: 900, y: 360 },   // 5: right-lower
		{ x: 850, y: 420 },   // 6: bottom-right
		{ x: 620, y: 380 },   // 7: bottom-center-right - pulled up
		{ x: 380, y: 430 },   // 8: bottom-center-left
		{ x: 130, y: 400 },   // 9: bottom-left
		{ x: 200, y: 320 },   // 10: left-lower - pulled inward
		{ x: 100, y: 240 },   // 11: left-upper
	];

	// END points are calculated dynamically in generateEndPoints()
	// to fill the visible viewport area at any aspect ratio

	// Tall viewport shape (mobile portrait) - compact but irregular
	const TALL_START = [
		{ x: 380, y: 100 },   // 0: top-left - irregular
		{ x: 480, y: 160 },   // 1: top-center-left - dipped
		{ x: 520, y: 90 },    // 2: top-center-right - up
		{ x: 620, y: 140 },   // 3: top-right - irregular
		{ x: 580, y: 220 },   // 4: right-upper - pulled in
		{ x: 640, y: 400 },   // 5: right-lower - pushed out
		{ x: 600, y: 510 },   // 6: bottom-right - down
		{ x: 520, y: 430 },   // 7: bottom-center-right - up
		{ x: 480, y: 520 },   // 8: bottom-center-left - down
		{ x: 380, y: 470 },   // 9: bottom-left - irregular
		{ x: 360, y: 380 },   // 10: left-lower - in
		{ x: 340, y: 200 },   // 11: left-upper - pushed left
	];

	// Responsive configuration
	const RESPONSIVE = {
		// Aspect ratio thresholds
		wideRatio: 1.5,      // Above this = fully "wide" shape
		tallRatio: 0.8,      // Below this = fully "tall" shape
		// Width-based scaling
		referenceWidth: 1200,
		minScale: 0.7,
		maxScale: 1.0,
	};

	// Configuration
	const CONFIG = {
		pointRadius: 5,
		touchRadius: 18,
		pointFill: '#EAFF00',
		pointFillHover: '#F5FF66',
		pointFillDrag: '#BFCC00',
		pointStroke: 'none',
		pointStrokeWidth: 0,
		dragSensitivity: 150,
		easeProgress: true,
	};

	// State
	let isDragging = false;
	let activePointIndex = null;
	let activeGroupIndex = null;
	let dragStartPos = null;
	let dragStartProgress = 0;
	// Individual progress for each group (4 groups)
	let groupProgress = [0, 0, 0, 0];
	let points = [];
	let currentStartPoints = [];
	let currentEndPoints = [];
	let polygon = null;
	let controlPointsGroup = null;
	let svg = null;
	let svgPoint = null;
	// Track last known width to ignore height-only changes (mobile URL bar)
	let lastKnownWidth = 0;

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

		svgPoint = svg.createSVGPoint();

		// Store initial width for resize comparison
		lastKnownWidth = window.innerWidth;

		// Calculate responsive shapes
		updateResponsiveShapes();

		// Initialize points
		updatePointsFromProgress();

		// Create control points
		createControlPoints();

		// Set up events
		setupEventListeners();

		// Update polygon
		updatePolygon();

		console.log('Hero mask morph: initialized with 4 groups of 3 linked points');
	}

	/**
	 * Linear interpolation
	 */
	function lerp(start, end, t) {
		return start + (end - start) * t;
	}

	/**
	 * Ease out cubic
	 */
	function easeOutCubic(t) {
		return 1 - Math.pow(1 - t, 3);
	}

	/**
	 * Clamp value between min and max
	 */
	function clamp(value, min, max) {
		return Math.max(min, Math.min(max, value));
	}

	/**
	 * Calculate aspect ratio factor (0 = tall, 1 = wide)
	 */
	function getAspectRatioFactor() {
		const aspectRatio = window.innerWidth / window.innerHeight;
		// Map aspect ratio to 0-1 range
		const factor = (aspectRatio - RESPONSIVE.tallRatio) /
			(RESPONSIVE.wideRatio - RESPONSIVE.tallRatio);
		return clamp(factor, 0, 1);
	}

	/**
	 * Calculate width scale factor
	 */
	function getWidthScaleFactor() {
		const scale = window.innerWidth / RESPONSIVE.referenceWidth;
		return clamp(scale, RESPONSIVE.minScale, RESPONSIVE.maxScale);
	}

	/**
	 * Interpolate between two point arrays
	 */
	function interpolatePoints(pointsA, pointsB, t) {
		return pointsA.map((a, i) => ({
			x: lerp(a.x, pointsB[i].x, t),
			y: lerp(a.y, pointsB[i].y, t),
		}));
	}

	/**
	 * Scale points from center
	 */
	function scalePointsFromCenter(pts, scale) {
		const centerX = SVG_WIDTH / 2;
		const centerY = SVG_HEIGHT / 2;
		return pts.map(p => ({
			x: centerX + (p.x - centerX) * scale,
			y: centerY + (p.y - centerY) * scale,
		}));
	}

	/**
	 * Calculate the visible rectangle in SVG coordinates
	 * The SVG uses preserveAspectRatio="xMidYMid slice" which means
	 * the SVG is scaled to cover the viewport, cropping the excess
	 */
	function getVisibleRect() {
		const viewportAspect = window.innerWidth / window.innerHeight;
		const svgAspect = SVG_WIDTH / SVG_HEIGHT;

		let visibleWidth, visibleHeight, offsetX, offsetY;

		if (viewportAspect > svgAspect) {
			// Viewport is wider than SVG - height is cropped
			visibleWidth = SVG_WIDTH;
			visibleHeight = SVG_WIDTH / viewportAspect;
			offsetX = 0;
			offsetY = (SVG_HEIGHT - visibleHeight) / 2;
		} else {
			// Viewport is taller than SVG - width is cropped
			visibleHeight = SVG_HEIGHT;
			visibleWidth = SVG_HEIGHT * viewportAspect;
			offsetX = (SVG_WIDTH - visibleWidth) / 2;
			offsetY = 0;
		}

		return {
			left: offsetX,
			top: offsetY,
			right: offsetX + visibleWidth,
			bottom: offsetY + visibleHeight,
			width: visibleWidth,
			height: visibleHeight,
		};
	}

	/**
	 * Generate end rectangle points with padding from edges
	 * Keeps all points visible even when fully expanded
	 * Extra top padding accounts for the fixed header/navigation
	 */
	function generateEndPoints(rect) {
		// Padding from edges (in SVG units) - keeps points visible
		const EDGE_PADDING = 30;
		// Extra padding at top to account for fixed header
		const TOP_PADDING = 80;

		const left = rect.left + EDGE_PADDING;
		const top = rect.top + TOP_PADDING;
		const right = rect.right - EDGE_PADDING;
		const bottom = rect.bottom - EDGE_PADDING;
		const width = right - left;
		const height = bottom - top;
		const thirdW = width / 3;
		const thirdH = height / 3;

		return [
			{ x: left, y: top },                        // 0: top-left
			{ x: left + thirdW, y: top },               // 1: top 1/3
			{ x: left + thirdW * 2, y: top },           // 2: top 2/3
			{ x: right, y: top },                       // 3: top-right
			{ x: right, y: top + thirdH },              // 4: right 1/3
			{ x: right, y: top + thirdH * 2 },          // 5: right 2/3
			{ x: right, y: bottom },                    // 6: bottom-right
			{ x: left + thirdW * 2, y: bottom },        // 7: bottom 2/3
			{ x: left + thirdW, y: bottom },            // 8: bottom 1/3
			{ x: left, y: bottom },                     // 9: bottom-left
			{ x: left, y: top + thirdH * 2 },           // 10: left 2/3
			{ x: left, y: top + thirdH },               // 11: left 1/3
		];
	}

	/**
	 * Update responsive shapes based on current viewport
	 */
	function updateResponsiveShapes() {
		const aspectFactor = getAspectRatioFactor();
		const scaleFactor = getWidthScaleFactor();

		// Get the visible rectangle in SVG coordinates
		const visibleRect = getVisibleRect();

		// Generate dynamic end points based on visible area
		const dynamicEnd = generateEndPoints(visibleRect);

		// Interpolate between wide and tall START shapes
		const baseStart = interpolatePoints(TALL_START, WIDE_START, aspectFactor);

		// For END, we always use the dynamic end points (fills visible area)
		// No need to interpolate - the dynamic calculation handles all viewports
		const baseEnd = dynamicEnd;

		// Apply width scaling to START shape only (END should fill the screen)
		currentStartPoints = scalePointsFromCenter(baseStart, scaleFactor);
		currentEndPoints = baseEnd; // Don't scale - should reach edges
	}

	/**
	 * Update points based on per-group morph progress
	 * Each group has its own progress value, so points morph independently
	 */
	function updatePointsFromProgress() {
		points = currentStartPoints.map((startPoint, i) => {
			const groupIndex = POINT_TO_GROUP[i];
			const progress = groupProgress[groupIndex];
			const t = CONFIG.easeProgress ? easeOutCubic(progress) : progress;
			const endPoint = currentEndPoints[i];
			return {
				x: lerp(startPoint.x, endPoint.x, t),
				y: lerp(startPoint.y, endPoint.y, t),
			};
		});
	}

	/**
	 * Create control point elements
	 */
	function createControlPoints() {
		controlPointsGroup.innerHTML = '';

		points.forEach((point, index) => {
			const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
			group.setAttribute('data-index', index);
			group.classList.add('control-point', 'hero-control-point');
			group.style.cursor = 'grab';

			// Touch target
			const touchTarget = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			touchTarget.setAttribute('cx', point.x);
			touchTarget.setAttribute('cy', point.y);
			touchTarget.setAttribute('r', CONFIG.touchRadius);
			touchTarget.setAttribute('fill', 'transparent');
			touchTarget.style.touchAction = 'none';
			group.appendChild(touchTarget);

			// Visible circle
			const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			circle.setAttribute('cx', point.x);
			circle.setAttribute('cy', point.y);
			circle.setAttribute('r', CONFIG.pointRadius);
			circle.setAttribute('fill', CONFIG.pointFill);
			circle.setAttribute('stroke', CONFIG.pointStroke);
			circle.setAttribute('stroke-width', CONFIG.pointStrokeWidth);
			circle.classList.add('hero-point-visible');
			group.appendChild(circle);

			controlPointsGroup.appendChild(group);
		});
	}

	/**
	 * Update all control point positions
	 */
	function updateAllControlPoints() {
		points.forEach((point, index) => {
			const group = controlPointsGroup.children[index];
			if (group) {
				const circles = group.querySelectorAll('circle');
				circles.forEach(circle => {
					circle.setAttribute('cx', point.x);
					circle.setAttribute('cy', point.y);
				});
			}
		});
	}

	/**
	 * Update polygon
	 */
	function updatePolygon() {
		const pointsStr = points.map(p => `${p.x.toFixed(1)},${p.y.toFixed(1)}`).join(' ');
		polygon.setAttribute('points', pointsStr);
	}

	/**
	 * Screen to SVG coordinates
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
	 * Calculate drag delta for morph progress
	 */
	function calculateDragDelta(currentPos, startPos, pointIndex) {
		const startPoint = currentStartPoints[pointIndex];
		const endPoint = currentEndPoints[pointIndex];

		const dirX = endPoint.x - startPoint.x;
		const dirY = endPoint.y - startPoint.y;
		const dirLength = Math.sqrt(dirX * dirX + dirY * dirY);

		if (dirLength === 0) return 0;

		const normDirX = dirX / dirLength;
		const normDirY = dirY / dirLength;

		const dragX = currentPos.x - startPos.x;
		const dragY = currentPos.y - startPos.y;

		return dragX * normDirX + dragY * normDirY;
	}

	/**
	 * Handle resize
	 * Only recalculate when width changes to avoid mobile URL bar causing jitter
	 */
	function onResize() {
		if (isDragging) return;

		const currentWidth = window.innerWidth;

		// Ignore height-only changes (mobile browser URL bar hide/show)
		// Only recalculate when width actually changes (orientation change, actual resize)
		if (currentWidth === lastKnownWidth) {
			return;
		}

		lastKnownWidth = currentWidth;

		updateResponsiveShapes();
		updatePointsFromProgress();
		updateAllControlPoints();
		updatePolygon();
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
			const group = e.target.closest('.hero-control-point');
			if (group) e.preventDefault();
		}, { passive: false });

		controlPointsGroup.addEventListener('touchmove', (e) => {
			if (isDragging) e.preventDefault();
		}, { passive: false });

		controlPointsGroup.addEventListener('pointerenter', onPointerEnter, true);
		controlPointsGroup.addEventListener('pointerleave', onPointerLeave, true);

		window.addEventListener('resize', onResize);
	}

	/**
	 * Pointer down
	 */
	function onPointerDown(e) {
		const group = e.target.closest('.hero-control-point');
		if (!group) return;

		e.preventDefault();
		e.stopPropagation();

		group.setPointerCapture(e.pointerId);

		isDragging = true;
		activePointIndex = parseInt(group.getAttribute('data-index'), 10);
		activeGroupIndex = POINT_TO_GROUP[activePointIndex];
		dragStartPos = screenToSVG(e.clientX, e.clientY);
		dragStartProgress = groupProgress[activeGroupIndex];

		// Highlight all points in the active group
		POINT_GROUPS[activeGroupIndex].forEach(pointIdx => {
			const pointGroup = controlPointsGroup.children[pointIdx];
			if (pointGroup) {
				const visibleCircle = pointGroup.querySelector('.hero-point-visible');
				if (visibleCircle) {
					visibleCircle.setAttribute('fill', CONFIG.pointFillDrag);
				}
			}
		});

		group.style.cursor = 'grabbing';
		document.body.style.cursor = 'grabbing';
	}

	/**
	 * Pointer move
	 */
	function onPointerMove(e) {
		if (!isDragging || activePointIndex === null || activeGroupIndex === null) return;

		e.preventDefault();

		const currentPos = screenToSVG(e.clientX, e.clientY);
		const dragDelta = calculateDragDelta(currentPos, dragStartPos, activePointIndex);
		const progressDelta = dragDelta / CONFIG.dragSensitivity;

		// Only update the active group's progress
		groupProgress[activeGroupIndex] = clamp(dragStartProgress + progressDelta, 0, 1);

		updatePointsFromProgress();

		requestAnimationFrame(() => {
			updateAllControlPoints();
			updatePolygon();
		});
	}

	/**
	 * Pointer up
	 */
	function onPointerUp(e) {
		if (!isDragging) return;

		isDragging = false;

		// Reset colors for all points in the active group
		if (activeGroupIndex !== null) {
			POINT_GROUPS[activeGroupIndex].forEach(pointIdx => {
				const pointGroup = controlPointsGroup.children[pointIdx];
				if (pointGroup) {
					const visibleCircle = pointGroup.querySelector('.hero-point-visible');
					if (visibleCircle) {
						visibleCircle.setAttribute('fill', CONFIG.pointFill);
					}
				}
			});
		}

		if (activePointIndex !== null) {
			const group = controlPointsGroup.children[activePointIndex];
			if (group) {
				group.releasePointerCapture(e.pointerId);
				group.style.cursor = 'grab';
			}
		}

		activePointIndex = null;
		activeGroupIndex = null;
		dragStartPos = null;
		document.body.style.cursor = '';
	}

	/**
	 * Hover enter
	 */
	function onPointerEnter(e) {
		if (isDragging) return;
		const group = e.target.closest('.hero-control-point');
		if (group) {
			const visibleCircle = group.querySelector('.hero-point-visible');
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
		const group = e.target.closest('.hero-control-point');
		if (group) {
			const visibleCircle = group.querySelector('.hero-point-visible');
			if (visibleCircle) {
				visibleCircle.setAttribute('fill', CONFIG.pointFill);
			}
		}
	}

	/**
	 * Get/set progress for all groups or specific group
	 */
	function getProgress(groupIndex = null) {
		if (groupIndex !== null && groupIndex >= 0 && groupIndex < POINT_GROUPS.length) {
			return groupProgress[groupIndex];
		}
		// Return average progress of all groups
		return groupProgress.reduce((sum, p) => sum + p, 0) / groupProgress.length;
	}

	function setProgress(value, groupIndex = null) {
		const clampedValue = clamp(value, 0, 1);
		if (groupIndex !== null && groupIndex >= 0 && groupIndex < POINT_GROUPS.length) {
			// Set specific group
			groupProgress[groupIndex] = clampedValue;
		} else {
			// Set all groups
			groupProgress = groupProgress.map(() => clampedValue);
		}
		updatePointsFromProgress();
		updateAllControlPoints();
		updatePolygon();
	}

	// Expose API
	window.HeroMask = {
		getProgress,
		setProgress,
		getGroupProgress: () => [...groupProgress],
		setGroupProgress: (progressArray) => {
			if (Array.isArray(progressArray) && progressArray.length === POINT_GROUPS.length) {
				groupProgress = progressArray.map(p => clamp(p, 0, 1));
				updatePointsFromProgress();
				updateAllControlPoints();
				updatePolygon();
			}
		},
		getPoints: () => points.map(p => ({ ...p })),
		getPointsString: () => points.map(p => `${Math.round(p.x)},${Math.round(p.y)}`).join(' '),
	};

	// Initialize
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
