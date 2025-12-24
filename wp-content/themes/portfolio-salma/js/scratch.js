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
        const pixel = this.ctx.getImageData(x, y, 1, 1).data;
        return pixel[3] === 0; // Alpha is 0 = transparent = scratched
    }

    bindEvents() {
        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => {
            const pos = this.getPosition(e);
            
            // If clicking on already scratched area, pass click through
            if (this.isPointScratched(pos.x, pos.y)) {
                this.passThroughClick(e);
                return;
            }
            
            // Otherwise start scratching
            this.startDrawing(e);
        });
        
        // Use document-level events for move/up to handle dragging
        document.addEventListener('mousemove', (e) => this.draw(e));
        document.addEventListener('mouseup', () => this.stopDrawing());
        
        // Touch events
        this.canvas.addEventListener('touchstart', (e) => {
            const touch = e.touches[0];
            const pos = this.getTouchPosition(touch);
            
            if (this.isPointScratched(pos.x, pos.y)) {
                // Let touch pass through to content below
                return;
            }
            
            e.preventDefault();
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

    // Erase using a PNG image as mask (dark pixels get erased)
    drawFromImage(imageSrc, options = {}) {
        const img = new Image();
        img.onload = () => {
            const w = this.canvas.width;
            const h = this.canvas.height;
            
            // Create temporary canvas to read image pixels
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');
            
            // Calculate size and position (centered, scaled to fit)
            const scale = options.scale || 0.5;
            const imgW = img.width * scale;
            const imgH = img.height * scale;
            const offsetX = options.x !== undefined ? options.x * w : (w - imgW) / 2;
            const offsetY = options.y !== undefined ? options.y * h : (h - imgH) / 2;
            
            tempCanvas.width = imgW;
            tempCanvas.height = imgH;
            tempCtx.drawImage(img, 0, 0, imgW, imgH);
            
            // Get image data
            const imageData = tempCtx.getImageData(0, 0, imgW, imgH);
            const pixels = imageData.data;
            
            // Erase where the image has dark pixels
            this.ctx.globalCompositeOperation = 'destination-out';
            
            for (let y = 0; y < imgH; y++) {
                for (let x = 0; x < imgW; x++) {
                    const i = (y * imgW + x) * 4;
                    const r = pixels[i];
                    const g = pixels[i + 1];
                    const b = pixels[i + 2];
                    const a = pixels[i + 3];
                    
                    // Check if pixel is dark (the black lines)
                    const brightness = (r + g + b) / 3;
                    if (brightness < 128 && a > 128) {
                        this.ctx.fillRect(offsetX + x, offsetY + y, 1, 1);
                    }
                }
            }
            
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

    // Custom cursor tracking
    if (cursorIndicator) {
        container.addEventListener('mousemove', (e) => {
            cursorIndicator.style.left = e.clientX + 'px';
            cursorIndicator.style.top = e.clientY + 'px';
            
            // Check if cursor is over scratched or unscratched area
            const rect = scratch.canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const isScratched = scratch.isPointScratched(x, y);
            
            if (isScratched) {
                cursorIndicator.style.opacity = '0';
                container.style.cursor = 'auto';
            } else {
                cursorIndicator.style.opacity = '1';
                container.style.cursor = 'none';
            }
        });

        container.addEventListener('mousedown', () => {
            container.classList.add('is-scratching');
        });

        container.addEventListener('mouseup', () => {
            container.classList.remove('is-scratching');
        });

        container.addEventListener('mouseleave', () => {
            container.classList.remove('is-scratching');
            cursorIndicator.style.opacity = '0';
        });
    }

    // Draw initial scratch pattern from PNG image
    if (themePath) {
        scratch.drawFromImage(themePath + '/images/lemon.png', {
            scale: 0.6,
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
