// Idle Detection & Screensaver System (Avelza Premium)
let idleTime = 0;
let screensaverMode = 0; // 0 = inactive, 1 = Mode A (Fireflies), 2 = Mode B (Auto Scroll)
let idleInterval;
let phaseInterval;
let fireflyInterval;
let scrollInterval;
let currentRowIndex = 0;

// Konstanta waktu (Disetel 10 detik agar Anda cepat melihat hasilnya tanpa menunggu lama)
const IDLE_LIMIT = 10; 
const PHASE_DURATION = 15000; // Tiap mode tayang 15 detik secara bergantian

document.addEventListener('DOMContentLoaded', () => {
    // Reset timer saat ada interaksi sekecil apapun
    window.onload = resetTimer;
    window.onmousemove = resetTimer;
    window.onmousedown = resetTimer; 
    window.ontouchstart = resetTimer;
    window.onclick = resetTimer;     
    window.onkeypress = resetTimer;   
    window.addEventListener('scroll', resetTimer, true); 

    // Timer penghitung waktu diam (berdetak setiap 1 detik)
    idleInterval = setInterval(timerIncrement, 1000);
});

function timerIncrement() {
    idleTime = idleTime + 1;
    if (idleTime > IDLE_LIMIT && screensaverMode === 0) {
        startScreensaver();
    }
}

function resetTimer() {
    idleTime = 0;
    if (screensaverMode !== 0) {
        stopScreensaver();
    }
}

function startScreensaver() {
    const isPesertaPage = document.querySelector('.card-peserta') !== null;
    document.body.classList.add('idle-active');
    
    if (isPesertaPage) {
        // Bergantian antara Mode A dan Mode B khusus di halaman buku catatan
        screensaverMode = 1;
        triggerModeA();
        
        phaseInterval = setInterval(() => {
            if (screensaverMode === 1) {
                stopModeA();
                screensaverMode = 2;
                triggerModeB();
            } else {
                stopModeB();
                screensaverMode = 1;
                triggerModeA();
            }
        }, PHASE_DURATION);
    } else {
        // Halaman lain hanya mode A (karena tidak ada tabel)
        screensaverMode = 1;
        triggerModeA();
    }
}

function stopScreensaver() {
    screensaverMode = 0;
    document.body.classList.remove('idle-active');
    clearInterval(phaseInterval);
    stopModeA();
    stopModeB();
}

// ==========================================
// MODE A: Hujan Kunang-Kunang Emas
// ==========================================
function triggerModeA() {
    document.body.classList.add('mode-a-active');
    const container = document.createElement('div');
    container.id = 'firefly-container';
    document.body.appendChild(container);
    
    // Ciptakan partikel kunang-kunang secara acak
    fireflyInterval = setInterval(() => {
        const firefly = document.createElement('div');
        firefly.className = 'firefly';
        firefly.style.left = Math.random() * 100 + 'vw';
        firefly.style.animationDuration = (Math.random() * 3 + 2) + 's';
        container.appendChild(firefly);
        
        // Hapus kunang-kunang yang sudah lewat
        setTimeout(() => {
            if(firefly.parentNode) firefly.remove();
        }, 5000);
    }, 150); // Kecepatan partikel muncul
}

function stopModeA() {
    document.body.classList.remove('mode-a-active');
    clearInterval(fireflyInterval);
    const container = document.getElementById('firefly-container');
    if (container) container.remove();
}

// ==========================================
// MODE B: Auto-Pilot Papan Reklame
// ==========================================
function triggerModeB() {
    document.body.classList.add('mode-b-active');
    const rows = document.querySelectorAll('.card-peserta tbody tr');
    if (rows.length === 0) return;
    
    currentRowIndex = 0;
    const tableContainer = document.querySelector('.table-responsive');
    
    scrollInterval = setInterval(() => {
        // Hapus sorotan dari semua baris
        rows.forEach(r => r.classList.remove('spotlight-row'));
        
        // Tambahkan sorotan emas ke baris target
        const targetRow = rows[currentRowIndex];
        if(targetRow) {
            targetRow.classList.add('spotlight-row');
            
            // Gulung (scroll) otomatis ke arah nama yang disorot
            if(tableContainer) {
                const scrollPos = targetRow.offsetTop - (tableContainer.offsetHeight / 2) + (targetRow.offsetHeight / 2);
                tableContainer.scrollTo({
                    top: Math.max(0, scrollPos),
                    behavior: 'smooth'
                });
            }
        }
        
        currentRowIndex++;
        if (currentRowIndex >= rows.length) {
            currentRowIndex = 0; // Ulangi dari atas
        }
    }, 1200); // Kecepatan sorotan melompat ke bawah
}

function stopModeB() {
    document.body.classList.remove('mode-b-active');
    clearInterval(scrollInterval);
    const rows = document.querySelectorAll('.card-peserta tbody tr');
    rows.forEach(r => r.classList.remove('spotlight-row'));
}
