<?php session_start(); ?>
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
        <div class="header" id="secret-master-trigger">
            <h1>🤝 Arisan Warga RT 31</h1>
            <p>Guyub, Rukun, dan Sejahtera Bersama</p>
        </div>
        
        <div class="menu-box">
            <?php
            require 'config.php';
            $is_master = isset($_SESSION['admin_master']) && $_SESSION['admin_master'] === true;
            ?>
            <a href="daftar.php" class="btn btn-primary btn-cta-pulse"><span style="position: relative; z-index: 2;">📝 Daftar Arisan Sekarang</span></a>
            <a href="arisan.php" class="menu-btn" style="border-left: 8px solid #cca300; background: linear-gradient(145deg, #fff, #fdf8e6); padding: 25px 20px; border-radius: 15px; box-shadow: 0 10px 20px rgba(204, 163, 0, 0.15);">
                <h3 style="font-size: 1.5rem; margin-bottom: 8px;">💰 Ruang Kendali Arisan</h3>
                <p style="font-size: 1rem; color: #555;">Manajemen Buku Kas & Mesin Kocokan Roulette dalam satu wadah eksklusif.</p>
            </a>
            
            <a href="ronda.php" class="btn" style="background: rgba(255,255,255,0.7); border: 2px solid #1e293b; color: #1e293b; display: block; text-align: center; backdrop-filter: blur(5px); margin-bottom: 15px;">🛡️ Jadwal Siskamling (Ronda)</a>
            <a href="pertemuan.php" class="btn" style="background: rgba(255,255,255,0.7); border: 2px solid #2563eb; color: #2563eb; display: block; text-align: center; backdrop-filter: blur(5px);">📅 Jadwal Pertemuan Rutin</a>
            
            <?php if ($is_master): ?>
            <a href="pengaturan.php" class="btn" style="background: #0f172a; border: 2px solid #0f172a; color: white; display: block; text-align: center; margin-top: 15px;">⚙️ Pengaturan Keamanan (Master)</a>
            <?php endif; ?>
        <div style="text-align: center; margin-top: 15px;">
            <button id="btn-install" style="display: none; background: transparent; border: 1px solid rgba(0,0,0,0.15); color: #64748b; padding: 6px 18px; border-radius: 50px; font-weight: 400; font-size: 0.8rem; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s ease;">📱 Instal di HP</button>
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

    // Secret Login (Triple Tap)
    let clickCount = 0;
    let lastClick = 0;
    document.getElementById('secret-master-trigger').addEventListener('click', function() {
        let now = new Date().getTime();
        if (now - lastClick > 1000) clickCount = 0;
        clickCount++;
        lastClick = now;
        
        if (clickCount >= 3) {
            clickCount = 0;
            let pin = prompt("🔒 [RESTRICTED AREA] Masukkan PIN Keamanan Master:");
            if (pin) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = 'login.php?modul=master';
                
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'pin';
                input.value = pin;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    });
</script>
</body>
</html>
