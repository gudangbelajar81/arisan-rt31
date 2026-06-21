<?php
session_start();
require 'config.php';
require 'logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_arisan']) || $_SESSION['admin_arisan'] !== true) {
    echo json_encode(["status" => "error", "message" => "Akses ditolak. Anda bukan admin arisan."]);
    exit;
}

$peserta_id = isset($_POST['peserta_id']) ? (int)$_POST['peserta_id'] : 0;
$bulan = isset($_POST['bulan']) ? trim($_POST['bulan']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($peserta_id > 0 && !empty($bulan)) {
    if (strpos($action, 'check_') === 0) {
        $warna = str_replace('check_', '', $action);
        // Validasi warna
        if (!in_array($warna, ['hijau', 'merah', 'biru'])) {
            $warna = 'hijau';
        }
        
        $stmt = $conn->prepare("INSERT INTO pembayaran_arisan (peserta_id, bulan, status_warna) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status_warna = ?");
        $stmt->bind_param("isss", $peserta_id, $bulan, $warna, $warna);
        if ($stmt->execute()) {
            catat_log($conn, "Admin mengonfirmasi pembayaran arisan ID $peserta_id untuk bulan $bulan (Status: $warna)");
            echo json_encode(["status" => "success", "action" => "checked", "warna" => $warna]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan database"]);
        }
    } elseif ($action === 'uncheck') {
        $stmt = $conn->prepare("DELETE FROM pembayaran_arisan WHERE peserta_id = ? AND bulan = ?");
        $stmt->bind_param("is", $peserta_id, $bulan);
        if ($stmt->execute()) {
            catat_log($conn, "Admin membatalkan pembayaran arisan ID $peserta_id untuk bulan $bulan");
            echo json_encode(["status" => "success", "action" => "unchecked"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menghapus dari database"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Aksi tidak valid"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap (ID atau Bulan kosong)"]);
}
?>
