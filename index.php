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
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <a href="peserta.php" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: linear-gradient(145deg, #2ea84b, #217346); color: white; padding: 20px 10px; border-radius: 12px; text-decoration: none; font-weight: bold; box-shadow: 0 5px 0 #1b5e20, 0 8px 15px rgba(33, 115, 70, 0.4); transition: transform 0.1s, box-shadow 0.1s;">
                    <span style="font-size: 2rem; margin-bottom: 5px;">👥</span>
                    <span style="font-size: 1.1rem; line-height: 1.2;">Buku Kas<br>Arisan</span>
                </a>
                
                <a href="kocokan.php" style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: linear-gradient(145deg, #e6b800, #cca300); color: #111; padding: 20px 10px; border-radius: 12px; text-decoration: none; font-weight: bold; box-shadow: 0 5px 0 #997a00, 0 8px 15px rgba(204, 163, 0, 0.4); transition: transform 0.1s, box-shadow 0.1s;">
                    <span style="font-size: 2rem; margin-bottom: 5px;">🎰</span>
                    <span style="font-size: 1.1rem; line-height: 1.2;">Mesin<br>Kocokan</span>
                </a>
            </div>
            
            <a href="ronda.php" class="btn" style="background: rgba(255,255,255,0.7); border: 2px solid #1e293b; color: #1e293b; display: block; text-align: center; backdrop-filter: blur(5px); margin-bottom: 15px;">🛡️ Jadwal Siskamling (Ronda)</a>
            <a href="pertemuan.php" class="btn" style="background: rgba(255,255,255,0.7); border: 2px solid #2563eb; color: #2563eb; display: block; text-align: center; backdrop-filter: blur(5px);">📅 Jadwal Pertemuan Rutin</a>
        <div style="text-align: center; margin-top: 25px;">
            <button id="btn-install" style="display: none; background: transparent; border: 2px solid #64748b; color: #64748b; padding: 12px 30px; border-radius: 50px; font-weight: 800; font-size: 1.4rem; letter-spacing: 1px; cursor: pointer; transition: all 0.3s ease; width: 100%; max-width: 350px; margin: 0 auto;">📱 Instal di HP</button>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <p style="color: #64748b; font-weight: 600; font-size: 0.8rem; letter-spacing: 1px; margin-top: 20px;">Sistem ini dibangun oleh Musyafa</p>
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
