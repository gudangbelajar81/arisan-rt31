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
    <link rel="stylesheet" href="style.css?v=2.0">
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
            <a href="daftar.php" class="btn-glass-primary">
                <span style="font-size: 1.8rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">📝</span>
                <span>Daftar Arisan Sekarang</span>
            </a>
            
            <div class="glass-grid">
                <a href="peserta.php" class="btn-glass">
                    <span class="icon">👥</span>
                    <span class="text">Buku Kas<br>Arisan</span>
                </a>
                
                <a href="kocokan.php" class="btn-glass">
                    <span class="icon">🎰</span>
                    <span class="text">Mesin<br>Kocokan</span>
                </a>
                
                <a href="ronda.php" class="btn-glass">
                    <span class="icon">🛡️</span>
                    <span class="text">Jadwal<br>Siskamling</span>
                </a>
                
                <a href="pertemuan.php" class="btn-glass">
                    <span class="icon">📅</span>
                    <span class="text">Jadwal<br>Pertemuan</span>
                </a>
            </div>
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
