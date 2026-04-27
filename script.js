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
        return { x: clientX - rect.left, y: clientY - rect.top };
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
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing, { passive: false });

    window.clearSig = function () {
        if (ctx) {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        }
    };

    window.saveSig = function () {
        if (!hasDrawn) { alert("Please draw your signature first."); return; }
        const dataURL = canvas.toDataURL('image/png');
        const sigImg = document.getElementById('saved-signature');
        sigImg.src = dataURL;
        sigImg.style.display = 'block';
        document.getElementById('signature-board-wrapper').style.display = 'none';
        document.getElementById('confirmed-sig-container').style.display = 'block';
    };

    window.deleteSig = function () {
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
    inputs.forEach(input => input.addEventListener('input', calculateScore));
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
   4. IMAGE PREVIEW (legacy helper)
   ========================================= */
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('upload-placeholder');
    if (input.files && input.files[0] && preview && placeholder) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/* =========================================
   5. BUTTON LOADING STATE
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
   6. SELECT2 DROPDOWNS
   ========================================= */
if (typeof jQuery !== 'undefined') {
    $(document).ready(function () {
        if ($('.search-select').length > 0) {
            $('.search-select').select2({ width: '100%' });
            $('.search-select').removeAttr('required');

            $('.search-select').on('select2:open', function () {
                setTimeout(function () {
                    const searchField = document.querySelector('.select2-search__field');
                    if (searchField) searchField.placeholder = 'Search...';
                }, 50);
            });
        }
    });
}

/* =========================================
   7. UNIVERSAL WIZARD LOGIC (Student & Staff)
   ========================================= */
let currentStep = 1;
const totalSteps = 4;

function updateSidebar(step) {
    document.querySelectorAll('.step-item').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s === step) el.classList.add('active');
        else if (s < step) el.classList.add('done');

        const dot = el.querySelector('.step-dot');
        if (dot) dot.textContent = s < step ? '✓' : s;
    });
}

function showPanel(step) {
    document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
    const targetPanel = document.getElementById('panel' + step);
    if (targetPanel) targetPanel.classList.add('active');
    updateSidebar(step);
    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getSelectVal(id) {
    if (typeof jQuery !== 'undefined' && $('#' + id).length) {
        return $('#' + id).val() || '';
    }
    const el = document.getElementById(id);
    return el ? el.value : '';
}

function getSelectText(id) {
    const el = document.getElementById(id);
    if (!el) return '—';
    const idx = el.selectedIndex;
    return (idx >= 0 && el.options[idx]) ? el.options[idx].text : '—';
}

function validateStep(step) {
    const isStaffWizard = document.getElementById('f_uid') !== null;

    if (isStaffWizard) {
        if (step === 2) {
            const uid   = (document.getElementById('f_uid')   || {}).value || '';
            const fname = (document.getElementById('f_fname') || {}).value || '';
            if (!uid.trim())   { alert('Please enter a Staff ID.');   document.getElementById('f_uid').focus();   return false; }
            if (!fname.trim()) { alert('Please enter the full name.'); document.getElementById('f_fname').focus(); return false; }
        }
        if (step === 3) {
            const uname = (document.getElementById('f_uname') || {}).value || '';
            if (!uname.trim()) { alert('Please enter a username.'); document.getElementById('f_uname').focus(); return false; }
        }
        return true;

    } else {
        if (step === 1) {
            const sid   = document.getElementById('f_sid');
            const sname = document.getElementById('f_sname');
            if (!sid || !sname) return true;
            if (!sid.value.trim())   { alert('Please enter a Student ID.');   sid.focus();   return false; }
            if (!sname.value.trim()) { alert('Please enter the full name.');  sname.focus(); return false; }
            if (!/^[0-9]{3,20}$/.test(sid.value.trim())) {
                alert('Student ID must contain only numbers (3–20 digits).');
                sid.focus();
                return false;
            }
        }
        if (step === 2) {
            const progVal = getSelectVal('f_prog');
            const lectVal = getSelectVal('f_lect');
            if (!progVal) { alert('Please select an academic programme.'); return false; }
            if (!lectVal) { alert('Please select an assigned lecturer.');  return false; }
        }
        return true;
    }
}

function populateReview() {
    const isStaffWizard = document.getElementById('f_uid') !== null;
    const setText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

    if (isStaffWizard) {
        if (typeof window.syncReview === 'function') window.syncReview();
    } else {
        const sid   = (document.getElementById('f_sid')   || {}).value?.trim() || '—';
        const sname = (document.getElementById('f_sname') || {}).value?.trim() || '—';

        setText('rev_name',    sname);
        setText('rev_id',      sid);
        setText('rev_sid',     sid);
        setText('rev_user',    sid);

        setText('rev_prog',    getSelectText('f_prog'));
        setText('rev_lect',    getSelectText('f_lect'));

        const superVal = getSelectVal('f_super');
        setText('rev_super', superVal ? getSelectText('f_super') : 'Not assigned');

        const companyVal  = getSelectVal('f_company');
        const companyText = (!companyVal || companyVal === '__PENDING__') ? 'Pending Placement' : getSelectText('f_company');
        setText('rev_company', companyText);
    }

    const preview        = document.getElementById('photoPreview');
    const revPhoto       = document.getElementById('reviewPhoto');
    const revPlaceholder = document.getElementById('reviewAvatarPlaceholder');

    if (preview && revPhoto && revPlaceholder) {
        const hasPhoto = preview.style.display !== 'none'
                      && preview.src
                      && !preview.src.endsWith('#')
                      && preview.src !== window.location.href + '#';
        if (hasPhoto) {
            revPhoto.src           = preview.src;
            revPhoto.style.display = 'block';
            revPlaceholder.style.display = 'none';
        } else {
            revPhoto.style.display       = 'none';
            revPlaceholder.style.display = 'flex';
        }
    }
}

function nextStep(from) {
    if (!validateStep(from)) return;
    if (from === totalSteps - 1) populateReview();
    showPanel(from + 1);
}

function prevStep(from) { showPanel(from - 1); }
function goToStep(target) { if (target < currentStep) showPanel(target); }

function handleSubmit() {
    const btn = document.getElementById('submitBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Creating record…'; }
    return true;
}

/* =========================================
   8. IMAGE UPLOAD & CROPPER LOGIC
   ========================================= */
let cropper = null;

window.handlePhotoFile = function (file) {
    const errEl      = document.getElementById('photoError');
    const photoInput = document.getElementById('photoInput');
    if (!photoInput) return;

    if (errEl) errEl.style.display = 'none';
    if (!file) return;

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        if (errEl) { errEl.textContent = 'Only JPG, PNG or WEBP files are accepted.'; errEl.style.display = 'block'; }
        photoInput.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        const modal     = document.getElementById('cropModal');
        const imgToCrop = document.getElementById('imageToCrop');
        if (modal && imgToCrop) {
            modal.style.display = 'flex';
            imgToCrop.src = e.target.result;
            imgToCrop.onload = function () {
                if (cropper) { cropper.destroy(); cropper = null; }
                cropper = new Cropper(imgToCrop, {
                    aspectRatio: 1,
                    viewMode: 0,
                    dragMode: 'crop',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
        }
    };
    reader.readAsDataURL(file);
};

window.closeCropModal = function () {
    const modal = document.getElementById('cropModal');
    if (modal) modal.style.display = 'none';
    const input = document.getElementById('photoInput');
    if (input) input.value = '';
    if (cropper) { cropper.destroy(); cropper = null; }
};

document.addEventListener("DOMContentLoaded", function () {
    const photoInput     = document.getElementById('photoInput');
    const photoZone      = document.getElementById('photoZone');
    const btnCropConfirm = document.getElementById('btnCropConfirm');

    if (photoInput && photoZone) {
        photoInput.addEventListener('change', function (e) {
            if (e.target.files.length > 0) window.handlePhotoFile(e.target.files[0]);
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
            if (e.dataTransfer.files.length) {
                window.handlePhotoFile(e.dataTransfer.files[0]);
            }
        });
    }

    if (btnCropConfirm) {
        btnCropConfirm.addEventListener('click', function () {
            if (!cropper) return;
            
            // Generate circular-ready high-res crop
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 });
            const base64data = canvas.toDataURL('image/jpeg', 0.9);

            // Store in hidden input for PHP
            let hiddenInput = document.getElementById('croppedImageData');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden'; 
                hiddenInput.name = 'cropped_image_data';
                hiddenInput.id = 'croppedImageData';
                
                // FIX: Look for ANY form instead of specifically "wizardForm"
                const form = document.querySelector('form');
                if (form) form.appendChild(hiddenInput);
            }
            hiddenInput.value = base64data;

            // UI Update: Show Image, Hide "?" Placeholder
            const preview     = document.getElementById('photoPreview');
            const placeholder = document.getElementById('photoPlaceholder');
            const btnRemove   = document.getElementById('btnRemovePhoto');

            if (preview) {
                preview.src = base64data;
                preview.classList.remove('hidden-element');
            }
            if (placeholder) {
                placeholder.classList.add('hidden-element');
            }
            if (btnRemove) {
                btnRemove.classList.remove('hidden-element');
            }
            
            // Ensure PHP knows NOT to remove the photo
            const removeFlag = document.getElementById('removePhotoFlag');
            if (removeFlag) removeFlag.value = '0';

            window.closeCropModal();
        });
    }
});

/* =========================================
   9. UNIVERSAL PROGRESS BAR LOGIC
   ========================================= */
function calculateRealProgress() {
    const isStaffWizard = document.getElementById('f_uid') !== null;

    let filled = 0;
    let total  = 0;

    if (isStaffWizard) {
        const roleChecked = document.querySelector('input[name="role"]:checked');
        total++;
        if (roleChecked) filled++;

        const uid = document.getElementById('f_uid');
        total++;
        if (uid && uid.value.trim() !== '') filled++;

        const fname = document.getElementById('f_fname');
        total++;
        if (fname && fname.value.trim() !== '') filled++;

        const uname = document.getElementById('f_uname');
        total++;
        if (uname && uname.value.trim() !== '') filled++;

    } else {
        const fields = [
            { id: 'f_sid',     type: 'input'  },
            { id: 'f_sname',   type: 'input'  },
            { id: 'f_prog',    type: 'select' },
            { id: 'f_lect',    type: 'select' },
            { id: 'f_super',   type: 'select' },
            { id: 'f_company', type: 'select' },
        ];

        fields.forEach(f => {
            if (!document.getElementById(f.id)) return;
            total++;
            const val = f.type === 'select' ? getSelectVal(f.id) : (document.getElementById(f.id).value || '');
            if (val.trim() !== '') filled++;
        });
    }

    const pct = total > 0 ? Math.round((filled / total) * 100) : 0;

    const progressBar = document.getElementById('progressBar');
    const progressPct = document.getElementById('progressPct');
    if (progressBar) progressBar.style.width = pct + '%';
    if (progressPct) progressPct.textContent  = pct + '%';
}

document.addEventListener("DOMContentLoaded", function () {
    const wizardForm = document.getElementById('wizardForm');
    if (wizardForm) {
        wizardForm.addEventListener('input',  calculateRealProgress);
        wizardForm.addEventListener('change', calculateRealProgress);

        if (typeof jQuery !== 'undefined') {
            $('.search-select').on('select2:select select2:unselect', function () {
                calculateRealProgress();
            });
        }

        calculateRealProgress();
    }
});

/* =========================================
   10. ADD STAFF (supervisor/lecturer)
   ========================================= */
window.syncRoleCards = function () {
    const roleInput = document.querySelector('input[name="role"]:checked');
    if (!roleInput) return;
    const selected = roleInput.value;

    const cardLecturer   = document.getElementById('card_lecturer');
    const cardSupervisor = document.getElementById('card_supervisor');
    const revRoleIcon    = document.getElementById('revRoleIcon');

    if (cardLecturer)   cardLecturer.classList.toggle('selected',  selected === 'Lecturer');
    if (cardSupervisor) cardSupervisor.classList.toggle('selected', selected === 'Supervisor');
    if (revRoleIcon)    revRoleIcon.textContent = selected === 'Lecturer' ? '🎓' : '🏢';

    window.syncReview();
};

window.syncReview = function () {
    const uidInput   = document.getElementById('f_uid');
    const fnameInput = document.getElementById('f_fname');
    const unameInput = document.getElementById('f_uname');
    const roleInput  = document.querySelector('input[name="role"]:checked');

    if (!uidInput) return;

    const uid   = uidInput.value.trim()   || '—';
    const fname = fnameInput ? fnameInput.value.trim() || '—' : '—';
    const uname = unameInput ? unameInput.value.trim() || '—' : '—';
    const role  = roleInput ? roleInput.value : '—';

    const map = {
        'rev_fname': fname, 'rev_uid': uid,
        'rv_uid': uid, 'rv_fname': fname,
        'rv_uname': uname, 'rv_role': role
    };
    for (const [id, value] of Object.entries(map)) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    // Handle Photo vs Letter Avatar on Review Step
    const photoPreview = document.getElementById('photoPreview');
    const reviewPhoto = document.getElementById('reviewPhoto');
    const reviewPlaceholder = document.getElementById('reviewAvatarPlaceholder');
    
    const hasPhoto = photoPreview && photoPreview.src && !photoPreview.src.endsWith('#') && photoPreview.style.display !== 'none';
    
    if (hasPhoto) {
        if (reviewPhoto) { 
            reviewPhoto.src = photoPreview.src; 
            reviewPhoto.style.display = 'block'; 
        }
        if (reviewPlaceholder) reviewPlaceholder.style.display = 'none';
    } else {
        if (reviewPhoto) reviewPhoto.style.display = 'none';
        if (reviewPlaceholder) reviewPlaceholder.style.display = 'flex';
    }
};

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('f_uid')) {
        window.syncRoleCards();
        
        // Live Update Letter Avatar for Staff
        const fnameInput = document.getElementById('f_fname');
        const letterEl = document.getElementById('letterAvatar');
        const reviewPlaceholder = document.getElementById('reviewAvatarPlaceholder');

        function updateStaffLetter() {
            const val = (fnameInput ? fnameInput.value : '').trim();
            const letter = val.length > 0 ? val.charAt(0).toUpperCase() : '?';
            if (letterEl) letterEl.textContent = letter;
            if (reviewPlaceholder) reviewPlaceholder.textContent = letter;
        }

        if (fnameInput) {
            fnameInput.addEventListener('input', updateStaffLetter);
            updateStaffLetter(); // Initialize on load
        }
    }
});

/* =========================================
   11. LECTURER EVALUATION PORTAL
   ========================================= */
window.stepVal = function(btn, dir) {
    const input = btn.parentElement.querySelector('input');
    const max = parseFloat(input.getAttribute('max'));
    let val = parseFloat(input.value) || 0;
    
    val = Math.round((val + dir) * 10) / 10;
    
    input.value = Math.min(max, Math.max(0, val));
    input.dispatchEvent(new Event('input'));
};

document.addEventListener("DOMContentLoaded", function () {
    const scoreInputs = document.querySelectorAll('.score-input');
    
    // Support both new UI (live-total) and legacy UI (total-display)
    const totalDisplay = document.getElementById('live-total') || document.getElementById('total-display');
    const gradeBadge = document.getElementById('grade-badge');
    const progText = document.getElementById('prog-text');
    const progBar = document.getElementById('prog-bar');

    if (scoreInputs.length > 0 && totalDisplay) {
        function updateEvaluation() {
            let total = 0, filled = 0;
            scoreInputs.forEach(i => {
                if (i.value !== '') { 
                    filled++; 
                    total += parseFloat(i.value) || 0; 
                }
            });

            totalDisplay.textContent = total.toFixed(1);
            
            if (progText) progText.textContent = filled + ' / 8 Fields';
            
            // Cap the progress bar at 100% so it doesn't break out of its container
            if (progBar) progBar.style.width = Math.min(total, 100) + '%'; 

            let g = '—', bg = '#f1f5f9', c = '#64748b';
            if (total >= 80) { g = 'A (Distinction)'; bg = '#dcfce7'; c = '#166534'; }
            else if (total >= 70) { g = 'B (Credit)'; bg = '#dbeafe'; c = '#1e40af'; }
            else if (total >= 60) { g = 'C (Pass)'; bg = '#fef9c3'; c = '#854d0e'; }
            else if (total >= 50) { g = 'D (Marginal)'; bg = '#ffedd5'; c = '#c2410c'; }
            else if (total > 0) { g = 'F (Fail)'; bg = '#fef2f2'; c = '#b91c1c'; }

            if (gradeBadge) {
                gradeBadge.textContent = g;
                gradeBadge.style.backgroundColor = bg;
                gradeBadge.style.color = c;
            }
        }

        scoreInputs.forEach(i => i.addEventListener('input', updateEvaluation));
        updateEvaluation(); 
    }
});

/* =========================================
   12. ADD STUDENT PORTAL LOGIC
   ========================================= */
document.addEventListener('DOMContentLoaded', function () {
    const studentNameInput = document.getElementById('f_sname');
    const letterEl = document.getElementById('letterAvatar');
    const reviewPlaceholder = document.getElementById('reviewAvatarPlaceholder');

    function updateStudentLetter() {
        const val = (studentNameInput ? studentNameInput.value : '').trim();
        const letter = val.length > 0 ? val.charAt(0).toUpperCase() : '?';
        if (letterEl) letterEl.textContent = letter;
        if (reviewPlaceholder) reviewPlaceholder.textContent = letter;
    }

    if (studentNameInput) {
        studentNameInput.addEventListener('input', updateStudentLetter);
        updateStudentLetter();
    }

    const wizardForm = document.getElementById('wizardForm');
    if (wizardForm) {
        wizardForm.addEventListener('submit', function () {
            const companySelect = document.getElementById('f_company');
            if (companySelect && companySelect.value === '__PENDING__') {
                companySelect.value = '';
            }
        });
    }
});


/* =========================================
   13. PROFILE PHOTO REMOVAL LOGIC
   ========================================= */
window.removeProfilePhoto = function() {
    const removeFlag = document.getElementById('removePhotoFlag');
    if (removeFlag) removeFlag.value = '1';
    
    const preview = document.getElementById('photoPreview');
    if (preview) {
        preview.src = '#';
        preview.classList.add('hidden-element');
    }
    
    const placeholder = document.getElementById('photoPlaceholder');
    if (placeholder) placeholder.classList.remove('hidden-element');
    
    const photoInput = document.getElementById('photoInput');
    if (photoInput) photoInput.value = '';
    
    const croppedInput = document.getElementById('croppedImageData');
    if (croppedInput) croppedInput.value = '';
    
    const btnRemove = document.getElementById('btnRemovePhoto');
    if (btnRemove) btnRemove.classList.add('hidden-element');
};

document.addEventListener("DOMContentLoaded", function() {
    const btnCropConfirm = document.getElementById('btnCropConfirm');
    if (btnCropConfirm) {
        btnCropConfirm.addEventListener('click', function() {
            const removeFlag = document.getElementById('removePhotoFlag');
            if (removeFlag) removeFlag.value = '0';
            
            const btnRemove = document.getElementById('btnRemovePhoto');
            if (btnRemove) btnRemove.classList.remove('hidden-element');
        });
    }
});


/* =========================================
   14. SETTINGS PAGE — PASSWORD TOGGLE & STRENGTH
   ========================================= */
document.addEventListener('DOMContentLoaded', function () {
    const EYE_OPEN = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    const EYE_SHUT = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

    // Initialise all eye buttons
    document.querySelectorAll('.s-eye-btn').forEach(btn => btn.innerHTML = EYE_OPEN);

    // Toggle password visibility
    window.togglePassword = function (fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (!input) return;
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.innerHTML = isHidden ? EYE_SHUT : EYE_OPEN;
    };

    // Password strength meter
    window.checkStrength = function (val) {
        const meter = document.getElementById('strengthMeter');
        const label = document.getElementById('strengthLabel');
        if (!meter || !label) return;

        meter.classList.toggle('visible', val.length > 0);
        if (val.length === 0) return;

        let score = 0;
        if (val.length >= 6)  score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = ['weak', 'fair', 'good', 'strong'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#ef4444', '#f59e0b', '#3b82f6', '#22c55e'];

        meter.className = `s-strength visible strength-${levels[score - 1] || 'weak'}`;
        label.textContent = labels[score - 1] || 'Too short';
        label.style.color  = colors[score - 1] || '#94a3b8';
    };
});
