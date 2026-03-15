const canvas = document.getElementById('signature-pad');
const ctx = canvas.getContext('2d');
const boardWrapper = document.getElementById('signature-board-wrapper');
const sigImg = document.getElementById('saved-signature');
let drawing = false;

// Initialize on load
window.addEventListener('load', () => {
    resizeCanvas();
    ctx.strokeStyle = "#1e293b";
    ctx.lineWidth = 2;
    ctx.lineCap = "round";
});

function resizeCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
}

function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

canvas.addEventListener('mousedown', (e) => {
    drawing = true;
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
});

canvas.addEventListener('mousemove', (e) => {
    if (!drawing) return;
    const pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
});

window.addEventListener('mouseup', () => { drawing = false; });

function clearSig() { ctx.clearRect(0, 0, canvas.width, canvas.height); }

function saveSig() {
    const dataURL = canvas.toDataURL();
    boardWrapper.style.display = 'none';
    sigImg.src = dataURL;
    sigImg.style.display = 'block';
}