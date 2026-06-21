<?php
session_start();
require 'config.php';
require 'logger.php';

$modul = isset($_GET['modul']) ? $_GET['modul'] : 'all';

if ($modul === 'arisan') {
    unset($_SESSION['admin_arisan']);
    catat_log($conn, "Admin Logout dari modul Arisan");
    header("Location: peserta.php");
} elseif ($modul === 'ronda') {
    unset($_SESSION['admin_ronda']);
    catat_log($conn, "Admin Logout dari modul Siskamling");
    header("Location: ronda.php");
} elseif ($modul === 'pertemuan') {
    unset($_SESSION['admin_pertemuan']);
    catat_log($conn, "Admin Logout dari modul Pertemuan");
    header("Location: pertemuan.php");
} else {
    // Logout Semua (Master)
    session_destroy();
    header("Location: index.php");
}
exit;
?>
