// Sistem Audio Premium Avelza Labs (Web Audio API Synthesizer)
function playClickSound() {
    try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gainNode = ctx.createGain();

        // Desain Suara: Nada futuristik "Ting/Bloop" yang elegan
        osc.type = 'sine'; 
        osc.frequency.setValueAtTime(1200, ctx.currentTime); // Nada awal tinggi
        osc.frequency.exponentialRampToValueAtTime(300, ctx.currentTime + 0.15); // Turun drastis

        gainNode.gain.setValueAtTime(0.3, ctx.currentTime); // Volume awal
        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15); // Memudar

        osc.connect(gainNode);
        gainNode.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 0.15);
    } catch(e) {
        console.log("Audio diblokir oleh browser (butuh interaksi user pertama kali)");
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Menerapkan suara ke semua tombol dan link kembali
    const buttons = document.querySelectorAll('.btn, .btn-aksi, .back-link');
    
    buttons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Jika tombol adalah Link (<a href=...>)
            if (this.tagName === 'A' && this.href) {
                // Trik: Jika kita membiarkan pindah halaman langsung, suara tidak akan sempat berbunyi
                e.preventDefault(); // Tahan halaman sejenak
                playClickSound(); // Mainkan Suara
                
                // Lanjutkan pindah halaman setelah 150 milidetik (cukup untuk suara selesai)
                setTimeout(() => {
                    window.location.href = this.href;
                }, 150);
            } else {
                // Jika tombol form biasa (Submit), mainkan suara langsung
                playClickSound();
            }
        });
    });
});
