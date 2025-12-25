/* =============================================
   SIMPLE SCRATCH OVERLAY - Clean approach
   ============================================= */

class SimpleScratch {
    constructor(options = {}) {
        this.container = document.getElementById('scratchContainer');
        if (!this.container) return;

        this.canvas = this.container.querySelector('canvas');
        this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
        this.cursorIndicator = document.getElementById('cursorIndicator');

        this.brushSize = options.brushSize || 120;
        this.overlayColor = options.overlayColor || '#39ff14';
        this.maskImage = options.maskImage || null;

        this.isDrawing = false;
        this.lastPoint = null;

        this.init();
    }

    init() {
        // Set canvas size
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;

        // Fill with green overlay
        this.ctx.fillStyle = this.overlayColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

        // Apply mask if provided
        if (this.maskImage) {
            this.applyMask(this.maskImage);
        }

        this.setupEvents();
    }

    applyMask(imagePath) {
        const img = new Image();
        img.onload = () => {
            const scale = 0.6;
            const imgW = img.width * scale;
            const imgH = img.height * scale;
            const x = (this.canvas.width - imgW) / 2;
            const y = (this.canvas.height - imgH) / 2;

            this.ctx.globalCompositeOperation = 'destination-out';
            this.ctx.drawImage(img, x, y, imgW, imgH);
            this.ctx.globalCompositeOperation = 'source-over';
        };
        img.src = imagePath;
    }

    setupEvents() {
        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.startScratching(e));
        this.canvas.addEventListener('mousemove', (e) => this.scratch(e));
        this.canvas.addEventListener('mouseup', () => this.stopScratching());
        this.canvas.addEventListener('mouseleave', () => this.stopScratching());

        // Touch events
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            this.startScratching(e.touches[0]);
        });
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            this.scratch(e.touches[0]);
        });
        this.canvas.addEventListener('touchend', () => this.stopScratching());

        // Cursor tracking
        if (this.cursorIndicator) {
            this.canvas.addEventListener('mouseenter', () => {
                this.cursorIndicator.style.opacity = '1';
            });

            this.canvas.addEventListener('mousemove', (e) => {
                this.cursorIndicator.style.left = e.clientX + 'px';
                this.cursorIndicator.style.top = e.clientY + 'px';
            });

            this.canvas.addEventListener('mouseleave', () => {
                this.cursorIndicator.style.opacity = '0';
            });
        }

        // Window resize
        window.addEventListener('resize', () => {
            this.init();
        });
    }

    getPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    startScratching(e) {
        this.isDrawing = true;
        this.lastPoint = this.getPosition(e);
        this.eraseAt(this.lastPoint);

        if (this.container) {
            this.container.classList.add('is-scratching');
        }
    }

    scratch(e) {
        if (!this.isDrawing) return;

        const currentPoint = this.getPosition(e);
        this.eraseAt(currentPoint);

        // Draw line from last point for smooth scratching
        if (this.lastPoint) {
            this.ctx.globalCompositeOperation = 'destination-out';
            this.ctx.lineWidth = this.brushSize;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.beginPath();
            this.ctx.moveTo(this.lastPoint.x, this.lastPoint.y);
            this.ctx.lineTo(currentPoint.x, currentPoint.y);
            this.ctx.stroke();
            this.ctx.globalCompositeOperation = 'source-over';
        }

        this.lastPoint = currentPoint;
    }

    stopScratching() {
        this.isDrawing = false;
        this.lastPoint = null;

        if (this.container) {
            this.container.classList.remove('is-scratching');
        }
    }

    eraseAt(point) {
        this.ctx.globalCompositeOperation = 'destination-out';
        this.ctx.beginPath();
        this.ctx.arc(point.x, point.y, this.brushSize / 2, 0, Math.PI * 2);
        this.ctx.fill();
        this.ctx.globalCompositeOperation = 'source-over';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const themePath = typeof scratchData !== 'undefined' ? scratchData.themePath : '';

    new SimpleScratch({
        brushSize: 120,
        overlayColor: '#39ff14',
        maskImage: themePath ? themePath + '/images/lemon.png' : null
    });
});
