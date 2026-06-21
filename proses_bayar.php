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
    if ($action === 'check') {
        $stmt = $conn->prepare("INSERT IGNORE INTO pembayaran_arisan (peserta_id, bulan) VALUES (?, ?)");
        $stmt->bind_param("is", $peserta_id, $bulan);
        if ($stmt->execute()) {
            catat_log($conn, "Admin mengonfirmasi pembayaran arisan ID $peserta_id untuk bulan $bulan");
            echo json_encode(["status" => "success", "action" => "checked"]);
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
