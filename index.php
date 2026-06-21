<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Arisan RT 31</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#cca300">
    <link rel="apple-touch-icon" href="logo_m.png">
    <link rel="stylesheet" href="style.css">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤝 Arisan Warga RT 31</h1>
            <p>Guyub, Rukun, dan Sejahtera Bersama</p>
        </div>
        
        <div class="menu-box">
            <a href="daftar.php" class="btn btn-primary btn-cta-pulse"><span style="position: relative; z-index: 2;">📝 Daftar Arisan Sekarang</span></a>
            <a href="peserta.php" class="btn btn-secondary">👥 Lihat Siapa Saja yang Sudah Daftar</a>
        <div style="text-align: center; margin-top: 15px;">
            <button id="btn-install" class="btn" style="display: none; background: #1f2937; border: 1px solid #cca300; color: #cca300; padding: 10px 20px; border-radius: 20px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">📲 Instal Aplikasi di HP</button>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <p style="color: #94a3b8; font-weight: 400; font-size: 0.8rem; letter-spacing: 1px; margin-top: 20px; opacity: 0.7;">Sistem ini dibangun oleh Musyafa</p>
        </div>
    </div>
<script src="sound.js"></script>
<script src="screensaver.js"></script>
<script>
    let deferredPrompt;
    const btnInstall = document.getElementById('btn-install');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        btnInstall.style.display = 'inline-block';
    });

    btnInstall.addEventListener('click', () => {
        btnInstall.style.display = 'none';
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            deferredPrompt = null;
        });
    });
</script>
</body>
</html>
