/* =============================================
   SCRATCH REVEAL CLASS
   ============================================= */
class ScratchReveal {
    constructor(container, options = {}) {
        this.container = container;
        this.canvas = container.querySelector('canvas');
        this.ctx = this.canvas.getContext('2d');
        
        // Options with defaults
        this.brushSize = options.brushSize || 120;
        this.overlayColor = options.overlayColor || '#39ff14';
        this.imagePath = options.imagePath || '';
        
        this.isDrawing = false;
        this.lastPoint = null;
        
        this.init();
    }

    init() {
        // Set canvas size to match container
        const rect = this.container.getBoundingClientRect();
        this.canvas.width = rect.width;
        this.canvas.height = rect.height;
        
        // Fill canvas with overlay color
        this.ctx.fillStyle = this.overlayColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Bind events
        this.bindEvents();
    }

    // Check if a point is on a scratched (transparent) area
    isPointScratched(x, y) {
        // Check bounds
        if (x < 0 || y < 0 || x >= this.canvas.width || y >= this.canvas.height) {
            return true; // Outside canvas = already transparent
        }

        const pixel = this.ctx.getImageData(x, y, 1, 1).data;
        return pixel[3] < 10; // Alpha close to 0 = transparent = scratched
    }

    bindEvents() {
        // Mouse events - canvas only receives events when pointer-events is enabled
        this.canvas.addEventListener('mousedown', (e) => {
            this.startDrawing(e);
        });

        // Use document-level events for move/up to handle dragging
        document.addEventListener('mousemove', (e) => this.draw(e));
        document.addEventListener('mouseup', () => this.stopDrawing());
        
        // Touch events - canvas only receives events when pointer-events is enabled
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            this.startDrawing(touch);
        }, { passive: false });
        
        this.canvas.addEventListener('touchmove', (e) => {
            if (this.isDrawing) {
                e.preventDefault();
                const touch = e.touches[0];
                this.draw(touch);
            }
        }, { passive: false });
        
        this.canvas.addEventListener('touchend', () => this.stopDrawing());
        this.canvas.addEventListener('touchcancel', () => this.stopDrawing());
    }

    // Separate method for touch position
    getTouchPosition(touch) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    // Pass click through to element below
    passThroughClick(e) {
        // Temporarily hide canvas
        this.canvas.style.pointerEvents = 'none';
        
        // Find element under cursor
        const elementBelow = document.elementFromPoint(e.clientX, e.clientY);
        
        // Re-enable canvas
        this.canvas.style.pointerEvents = 'auto';
        
        // Trigger click on element below
        if (elementBelow && elementBelow !== this.canvas) {
            elementBelow.click();
        }
    }

    getPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    startDrawing(e) {
        this.isDrawing = true;
        this.lastPoint = this.getPosition(e);
        this.scratch(this.lastPoint);
        
        // Lock scroll while scratching
        document.body.style.overflow = 'hidden';
    }

    draw(e) {
        if (!this.isDrawing) return;
        
        const currentPoint = this.getPosition(e);
        this.scratch(currentPoint);
        this.lastPoint = currentPoint;
    }

    stopDrawing() {
        if (this.isDrawing) {
            this.isDrawing = false;
            this.lastPoint = null;
            
            // Unlock scroll
            document.body.style.overflow = '';
        }
    }

    scratch(point) {
        // Set composite operation to "erase" the overlay
        this.ctx.globalCompositeOperation = 'destination-out';
        
        // Draw a circle at the current point
        this.ctx.beginPath();
        this.ctx.arc(point.x, point.y, this.brushSize / 2, 0, Math.PI * 2);
        this.ctx.fill();
        
        // If we have a last point, draw a line between them for smooth strokes
        if (this.lastPoint) {
            this.ctx.lineWidth = this.brushSize;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.beginPath();
            this.ctx.moveTo(this.lastPoint.x, this.lastPoint.y);
            this.ctx.lineTo(point.x, point.y);
            this.ctx.stroke();
        }
        
        // Reset composite operation
        this.ctx.globalCompositeOperation = 'source-over';
    }

    // Erase using a PNG image as mask (opaque pixels in PNG = transparent on canvas)
    drawFromImage(imageSrc, options = {}) {
        const img = new Image();
        img.onload = () => {
            const w = this.canvas.width;
            const h = this.canvas.height;

            // Calculate size and position (centered, scaled to fit)
            const scale = options.scale || 0.5;
            const imgW = img.width * scale;
            const imgH = img.height * scale;
            const offsetX = options.x !== undefined ? options.x * w : (w - imgW) / 2;
            const offsetY = options.y !== undefined ? options.y * h : (h - imgH) / 2;

            // Use destination-out to erase where the image is drawn
            // The image's alpha channel determines what gets erased
            this.ctx.globalCompositeOperation = 'destination-out';
            this.ctx.drawImage(img, offsetX, offsetY, imgW, imgH);
            this.ctx.globalCompositeOperation = 'source-over';
        };
        img.src = imageSrc;
    }
}

/* =============================================
   INITIALIZE SCRATCH
   ============================================= */
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('scratchContainer');
    
    // Only run on pages with scratch container
    if (!container) return;
    
    const cursorIndicator = document.getElementById('cursorIndicator');
    
    // Detect touch device
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    
    // Hide cursor indicator on touch devices
    if (isTouchDevice && cursorIndicator) {
        cursorIndicator.style.display = 'none';
    }

    // Get theme path from WordPress (set via wp_localize_script)
    const themePath = typeof scratchData !== 'undefined' ? scratchData.themePath : '';

    const scratch = new ScratchReveal(container, {
        brushSize: 120,
        overlayColor: '#39ff14', // Fluorescent green
    });

    // Draw initial scratch pattern from PNG image first
    if (themePath) {
        scratch.drawFromImage(themePath + '/images/lemon.png', {
            scale: 0.6,
        });
    }

    // Simple cursor tracking
    if (cursorIndicator) {
        scratch.canvas.addEventListener('mouseenter', () => {
            cursorIndicator.style.opacity = '1';
        });

        scratch.canvas.addEventListener('mousemove', (e) => {
            cursorIndicator.style.left = e.clientX + 'px';
            cursorIndicator.style.top = e.clientY + 'px';
        });

        scratch.canvas.addEventListener('mouseleave', () => {
            cursorIndicator.style.opacity = '0';
        });

        scratch.canvas.addEventListener('mousedown', () => {
            container.classList.add('is-scratching');
        });

        document.addEventListener('mouseup', () => {
            container.classList.remove('is-scratching');
        });
    }

    // Handle window resize
    window.addEventListener('resize', () => {
        scratch.init();
        if (themePath) {
            scratch.drawFromImage(themePath + '/images/lemon.png', { scale: 0.6 });
        }
    });
});
