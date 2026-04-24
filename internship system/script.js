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
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
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
        btn.innerHTML = 'Processing Record...';
        btn.style.opacity = '0.7';
        btn.style.pointerEvents = 'none';
    }
}

/* =========================================
   6. SELECT2 DROPDOWNS (Searchable Selects)
   ========================================= */
if (typeof jQuery !== 'undefined') {
    $(document).ready(function() {
        if ($('.search-select').length > 0) {
            $('.search-select').select2({ width: '100%' });
            $('.search-select').on('select2:open', function () {
                setTimeout(function() {
                    const searchField = document.querySelector('.select2-search__field');
                    if (searchField) searchField.placeholder = 'Search...';
                }, 50);
            });
        }
    });
}

/* =========================================
   7. ADD STUDENT WIZARD LOGIC
   ========================================= */
// Declare variables globally
let currentStep = 1;
const totalSteps = 4;

// Declare functions globally so inline HTML onclick handlers can access them
function updateSidebar(step) {
    document.querySelectorAll('.step-item').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s === step) el.classList.add('active');
        else if (s < step) el.classList.add('done');

        const dot = el.querySelector('.step-dot');
        if (s < step) dot.textContent = '✓';
        else dot.textContent = s;
    });

    const pct = Math.round((step / totalSteps) * 100);
    const progressBar = document.getElementById('progressBar');
    const progressPct = document.getElementById('progressPct');
    
    if(progressBar) progressBar.style.width = pct + '%';
    if(progressPct) progressPct.textContent = pct + '%';
}

function showPanel(step) {
    document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
    const targetPanel = document.getElementById('panel' + step);
    if(targetPanel) {
        targetPanel.classList.add('active');
    }
    updateSidebar(step);
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    if (step === 1) {
        const sid   = document.getElementById('f_sid');
        const sname = document.getElementById('f_sname');
        
        if (!sid || !sname) return true; // Fail safe if elements don't exist
        
        if (!sid.value.trim()) { 
            alert('Please enter a Student ID.'); 
            sid.focus(); 
            return false; 
        }
        if (!sname.value.trim()) { 
            alert('Please enter the student\'s full name.'); 
            sname.focus(); 
            return false; 
        }
        // ONLY allow 3 to 20 numbers
        if (!/^[0-9]{3,20}$/.test(sid.value.trim())) { 
            alert('Student ID must contain only numbers (3–20 digits).'); 
            return false; 
        }
    }
    if (step === 2) {
        const prog = document.getElementById('f_prog');
        const lect = document.getElementById('f_lect');
        if (prog && !prog.value) { alert('Please select an academic programme.'); return false; }
        if (lect && !lect.value) { alert('Please select an assessor.'); return false; }
    }
    if (step === 3) {
        const co = document.getElementById('f_company');
        if (co && !co.value) { alert('Please select a company.'); return false; }
    }
    return true;
}

function nextStep(from) {
    if (!validateStep(from)) return;
    if (from === 3) populateReview();
    showPanel(from + 1);
}

function prevStep(from) { 
    showPanel(from - 1); 
}

function goToStep(target) {
    if (target < currentStep) showPanel(target);
}

function populateReview() {
    const sid = document.getElementById('f_sid')?.value.trim() || '—';
    const sname = document.getElementById('f_sname')?.value.trim() || '—';
    const progVal = document.getElementById('f_prog')?.value.trim() || '—'; // Added for Assessor Username

    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };

    setText('rev_name', sname);
    setText('rev_sid', sid);
    setText('rev_user', progVal);
    
    if (progSel) setText('rev_prog', progSel.options[progSel.selectedIndex]?.text || '—');
    if (lectSel) setText('rev_lect', lectSel.options[lectSel.selectedIndex]?.text || '—');
    if (coSel) setText('rev_company', coSel.options[coSel.selectedIndex]?.text || '—');

    const preview = document.getElementById('photoPreview');
    const revPhoto = document.getElementById('reviewPhoto');
    const revPlaceholder = document.getElementById('reviewAvatarPlaceholder');
    
    if (preview && revPhoto && revPlaceholder) {
        if (preview.style.display !== 'none' && preview.src && preview.src !== window.location.href + '#') {
            revPhoto.src = preview.src;
            revPhoto.style.display = 'block';
            revPlaceholder.style.display = 'none';
            setText('rev_photo', photoInput?.files[0]?.name || 'Uploaded');
        } else {
            revPhoto.style.display = 'none';
            revPlaceholder.style.display = 'flex';
            setText('rev_photo', 'Default (none uploaded)');
        }
    }
}

function handleSubmit() {
    const btn = document.getElementById('submitBtn');
    if(btn) {
        btn.disabled = true;
        btn.textContent = 'Creating record…';
    }
    return true;
}

// ---------------------------------------------------------
// IMAGE UPLOAD & RESIZE LOGIC
// ---------------------------------------------------------
window.handlePhotoFile = function(file) {
    const errEl = document.getElementById('photoError');
    const photoInput = document.getElementById('photoInput');
    if(!errEl || !photoInput) return;
    
    errEl.style.display = 'none';
    if (!file) return;
    
    if (!['image/jpeg','image/png'].includes(file.type)) {
        errEl.textContent = 'Only JPG or PNG files are accepted.';
        errEl.style.display = 'block';
        photoInput.value = '';
        return;
    }

    // Read the file so we can modify it
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            // 1. Calculate new dimensions (Max 800px on either side)
            const MAX_SIZE = 800;
            let width = img.width;
            let height = img.height;

            if (width > height && width > MAX_SIZE) {
                height *= MAX_SIZE / width;
                width = MAX_SIZE;
            } else if (height > MAX_SIZE) {
                width *= MAX_SIZE / height;
                height = MAX_SIZE;
            }

            // 2. Draw the resized image onto an invisible canvas
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            // 3. Compress it into a JPEG at 80% quality
            canvas.toBlob(blob => {
                
                // 4. Create a brand new, lightweight File object
                const compressedFile = new File([blob], file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });

                // 5. Secretly swap the heavy file in the input for our light one!
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(compressedFile);
                photoInput.files = dataTransfer.files;

                // 6. Update the UI Preview
                const preview = document.getElementById('photoPreview');
                const placeholder = document.getElementById('photoPlaceholder');
                if (preview && placeholder) {
                    preview.src = URL.createObjectURL(compressedFile);
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                
            }, 'image/jpeg', 0.8); // 0.8 = 80% compression quality
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Listen for clicks and drags on the photo uploader
document.addEventListener("DOMContentLoaded", function() {
    const photoInput = document.getElementById('photoInput');
    const photoZone  = document.getElementById('photoZone');

    if (photoInput && photoZone) {
        photoInput.addEventListener('change', function(e) { 
            if(e.target.files.length > 0) window.handlePhotoFile(e.target.files[0]); 
        });

        photoZone.addEventListener('dragover', e => { 
            e.preventDefault(); 
            photoZone.classList.add('dragover'); 
        });
        
        photoZone.addEventListener('dragleave', () => {
            photoZone.classList.remove('dragover');
        });
        
        photoZone.addEventListener('drop', e => {
            e.preventDefault();
            photoZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length) {
                photoInput.files = files;
                window.handlePhotoFile(files[0]);
            }
        });
    }
});


// ---------------------------------------------------------
// IMAGE UPLOAD & RESIZE LOGIC (WITH CROPPER)
// ---------------------------------------------------------
let cropper = null;

window.handlePhotoFile = function(file) {
    const errEl = document.getElementById('photoError');
    const photoInput = document.getElementById('photoInput');
    if(!errEl || !photoInput) return;
    
    errEl.style.display = 'none';
    if (!file) return;
    
    if (!['image/jpeg','image/png', 'image/webp'].includes(file.type)) {
        errEl.textContent = 'Only JPG or PNG files are accepted.';
        errEl.style.display = 'block';
        photoInput.value = '';
        return;
    }

    // Read the file and open the cropping modal
    const reader = new FileReader();
    reader.onload = e => {
        const modal = document.getElementById('cropModal');
        const imgToCrop = document.getElementById('imageToCrop');
        
        if (modal && imgToCrop) {
            imgToCrop.src = e.target.result;
            modal.style.display = 'flex'; // Show modal

            // Destroy old cropper if it exists
            if (cropper) { cropper.destroy(); }
            
            // Initialize new cropper
            cropper = new Cropper(imgToCrop, {
                aspectRatio: 1, // Forces a perfect square/circle crop!
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                restore: false,
                guides: false,
                center: false,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }
    };
    reader.readAsDataURL(file);
};

window.closeCropModal = function() {
    document.getElementById('cropModal').style.display = 'none';
    document.getElementById('photoInput').value = ''; // Reset input so they can try again
    if (cropper) cropper.destroy();
};

// Listen for clicks and drags on the photo uploader
document.addEventListener("DOMContentLoaded", function() {
    const photoInput = document.getElementById('photoInput');
    const photoZone  = document.getElementById('photoZone');
    const btnCropConfirm = document.getElementById('btnCropConfirm');

    if (photoInput && photoZone) {
        photoInput.addEventListener('change', function(e) { 
            if(e.target.files.length > 0) window.handlePhotoFile(e.target.files[0]); 
        });

        photoZone.addEventListener('dragover', e => { 
            e.preventDefault(); 
            photoZone.classList.add('dragover'); 
        });
        
        photoZone.addEventListener('dragleave', () => {
            photoZone.classList.remove('dragover');
        });
        
        photoZone.addEventListener('drop', e => {
            e.preventDefault();
            photoZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length) {
                photoInput.files = files;
                window.handlePhotoFile(files[0]);
            }
        });
    }

    // Handle the "Confirm & Save" button inside the crop popup
    if (btnCropConfirm) {
        btnCropConfirm.addEventListener('click', function() {
            if (!cropper) return;

            // Extract the cropped area and resize it to 800x800 maximum
            const canvas = cropper.getCroppedCanvas({
                width: 800,
                height: 800,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Convert to JPEG blob at 85% quality
            canvas.toBlob(blob => {
                const fileInput = document.getElementById('photoInput');
                let originalName = fileInput.files[0] ? fileInput.files[0].name : 'profile.jpg';

                // Create the lightweight file
                const compressedFile = new File([blob], originalName, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });

                // Secretly put the cropped file into the HTML form
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(compressedFile);
                fileInput.files = dataTransfer.files;

                // Update the circular UI Preview
                const preview = document.getElementById('photoPreview');
                const placeholder = document.getElementById('photoPlaceholder');
                if (preview && placeholder) {
                    preview.src = URL.createObjectURL(compressedFile);
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }

                window.closeCropModal();
            }, 'image/jpeg', 0.85);
        });
    }
});



/* =========================================
   8. ADD ASSESSOR LOGIC
   ========================================= */
window.validateAndSubmit = function() {
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⚙ &nbsp;Processing… please wait';
        btn.style.opacity = '0.75';
    }
    return true;
};

window.togglePassword = function() {
    const field = document.getElementById('f_lect');
    if (field) {
        field.type = field.type === 'password' ? 'text' : 'password';
    }
};


/* =========================================
   REVISED PROGRESS LOGIC
   ========================================= */

// 1. Function to calculate progress based on field completion
function calculateRealProgress() {
    // List all the required field IDs for the form
    const requiredFields = [
        'f_sid',      // Student/Staff ID
        'f_sname',    // Full Name
        'f_prog',     // Programme (for students) or username (for assessors)
        'f_lect',     // Assessor (for students) or password (for assessors)
        'f_company'   // Company (for students)
    ];

    // Filter the list to only include elements that actually exist on the current page
    const existingFields = requiredFields.filter(id => document.getElementById(id));
    
    // Count how many of those existing fields have a value
    const filledFields = existingFields.filter(id => {
        const el = document.getElementById(id);
        return el && el.value.trim() !== "";
    }).length;

    // Calculate percentage
    const total = existingFields.length;
    const pct = total > 0 ? Math.round((filledFields / total) * 100) : 0;

    // Update UI
    const progressBar = document.getElementById('progressBar');
    const progressPct = document.getElementById('progressPct');
    
    if (progressBar) progressBar.style.width = pct + '%';
    if (progressPct) progressPct.textContent = pct + '%';
}

// 2. Update the sidebar step indicators (Keep this for the numbers/ticks)
function updateSidebar(step) {
    document.querySelectorAll('.step-item').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s === step) el.classList.add('active');
        else if (s < step) el.classList.add('done');

        const dot = el.querySelector('.step-dot');
        if (dot) {
            if (s < step) dot.textContent = '✓';
            else dot.textContent = s;
        }
    });
    
}

// 3. Attach listeners to the form inputs when the DOM is ready
document.addEventListener("DOMContentLoaded", function() {
    const wizardForm = document.getElementById('wizardForm');
    if (wizardForm) {
        // Listen for any typing or selection change in the form
        wizardForm.addEventListener('input', calculateRealProgress);
        wizardForm.addEventListener('change', calculateRealProgress);
        
        // Run once on load just in case the browser auto-filled anything
        calculateRealProgress();
    }
});