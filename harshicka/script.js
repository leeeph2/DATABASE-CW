/* =========================================
   1. SIGNATURE PAD (Final Report)
   ========================================= */
const canvas = document.getElementById('signature-pad');
const ctx = canvas ? canvas.getContext('2d') : null;
let isDrawing = false;
let hasDrawn = false; 

if (canvas) {
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        let clientX = e.clientX;
        let clientY = e.clientY;
        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }
        
    }

    function startDrawing(e) {
        e.preventDefault();
        isDrawing = true;
        hasDrawn = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        e.preventDefault();
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    }

    function stopDrawing(e) {
        if (e) e.preventDefault();
        isDrawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    canvas.addEventListener('touchstart', startDrawing, {passive: false});
    canvas.addEventListener('touchmove', draw, {passive: false});
    canvas.addEventListener('touchend', stopDrawing, {passive: false});
    
    window.clearSig = function() {
        if(ctx) {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        }
    };

    window.saveSig = function() {
        if (!hasDrawn) {
            alert("Please draw your signature first.");
            return;
        }
        const dataURL = canvas.toDataURL('image/png');
        document.getElementById('saved-signature').src = dataURL;
        document.getElementById('signature-board-wrapper').style.display = 'none';
        document.getElementById('confirmed-sig-container').style.display = 'block';
    };

    window.deleteSig = function() {
        document.getElementById('saved-signature').src = "";
        clearSig();
        document.getElementById('confirmed-sig-container').style.display = 'none';
        document.getElementById('signature-board-wrapper').style.display = 'block';
    };
}

/* =========================================
   2. EVALUATION CALCULATOR (Weightage System)
   ========================================= */
const inputs = document.querySelectorAll('.rubric-input');
const displayTotal = document.getElementById('live-total');

if (inputs.length > 0 && displayTotal) {
    function calculateScore() {
        let total = 0;
        inputs.forEach(input => {
            let val = parseFloat(input.value);
            if (isNaN(val)) val = 0;
            
            if (val > 100) { val = 100; input.value = 100; }
            if (val < 0) { val = 0; input.value = 0; }
            
            let weight = parseFloat(input.getAttribute('data-weight')) || 0;
            total += (val * weight);
        });
        displayTotal.innerHTML = total.toFixed(1) + '<span style="font-size: 2rem;">%</span>';
    }

    inputs.forEach(input => {
        input.addEventListener('input', calculateScore);
    });
}

/* =========================================
   3. SAFE URL CLEANUP
   ========================================= */
if (window.history.replaceState) {
    const url = new URL(window.location);
    if (url.searchParams.has('msg') || url.searchParams.has('error')) {
        url.searchParams.delete('msg');
        url.searchParams.delete('error');
        window.history.replaceState(null, null, url.toString());
    }
}

/* =========================================
   4. IMAGE PREVIEW (Add Student Page)
   ========================================= */
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('upload-placeholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

/* =========================================
   5. BUTTON LOADING STATE (Prevent Double Submit)
   ========================================= */
function showLoadingState() {
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.innerHTML = '⚙️ Processing Record...';
        btn.style.opacity = '0.7';
        btn.style.pointerEvents = 'none';
    }
}

/* =========================================
   6. SELECT2 DROPDOWNS (Searchable Selects)
   ========================================= */
$(document).ready(function() {
    if ($('.search-select').length > 0) {
        // Initialize cleanly without conflicting placeholders
        $('.search-select').select2({
            width: '100%' 
        });

        // Safely inject "Search..." into the text box without breaking clicks
        $('.search-select').on('select2:open', function () {
            setTimeout(function() {
                const searchField = document.querySelector('.select2-search__field');
                if (searchField) {
                    searchField.placeholder = 'Search...';
                }
            }, 50); // 50ms delay prevents the "click twice" glitch
        });
    }
});


