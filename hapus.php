<?php
session_start();
require 'config.php';
require 'logger.php';

// Cek keamanan akses admin
if (!isset($_SESSION['admin_arisan']) || $_SESSION['admin_arisan'] !== true) {
    header("Location: login.php?modul=arisan");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fitur Soft Delete: Jangan hapus secara fisik dari mesin, tapi sembunyikan
    $stmt = $conn->prepare("UPDATE peserta SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        catat_log($conn, "Admin menyembunyikan (Soft-Delete) peserta ID: $id");
        header("Location: peserta.php?pesan=hapus_sukses");
    } else {
        echo "Gagal mengamankan (menyembunyikan) data: " . $conn->error;
    }
} else {
    header("Location: peserta.php");
}
exit();
?>
