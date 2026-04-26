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
        if (step === 3) {
            const coVal = getSelectVal('f_company');
            if (!coVal) { alert('Please select a company.'); return false; }
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

        setText('rev_company', getSelectText('f_company'));
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
            revPhoto.src           = 'images/default.png';
            revPhoto.style.display = 'block';
            revPlaceholder.style.display = 'none';
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
   ─────────────────────────────────────────
   FIX: Cropped image is stored as Base64 in
   a hidden input (#croppedImageData) instead
   of using DataTransfer (unreliable on MAMP/
   Windows). PHP reads the hidden input and
   decodes it directly — no file upload needed.
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

        // Drag-and-drop styling
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

            const croppedCanvas = cropper.getCroppedCanvas({
                width: 800, height: 800,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            // Store Base64 in hidden input — bypasses DataTransfer bug on Windows/MAMP
            const base64data = croppedCanvas.toDataURL('image/jpeg', 0.85);

            let hiddenInput = document.getElementById('croppedImageData');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'cropped_image_data';
                hiddenInput.id   = 'croppedImageData';
                const form = document.querySelector('form');
                if (form) form.appendChild(hiddenInput);
            }
            hiddenInput.value = base64data;

            // Update preview and hide placeholder
            const preview     = document.getElementById('photoPreview');
            const placeholder = document.getElementById('photoPlaceholder');
            if (preview) {
                preview.src           = base64data;
                preview.style.display = 'block';
            }
            if (placeholder) placeholder.style.display = 'none';

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
};

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('f_uid')) window.syncRoleCards();
});