<?php
session_start();
require 'config.php';
require 'logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_arisan']) || $_SESSION['admin_arisan'] !== true) {
    echo json_encode(["status" => "error", "message" => "Akses Ditolak. Khusus Admin Arisan."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Action bisa 'simpan' atau 'hapus'
    $action = isset($_POST['action']) ? $_POST['action'] : 'simpan';
    
    if ($action === 'hapus') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM pemenang_arisan WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                catat_log($conn, "Admin menghapus riwayat pemenang arisan ID $id");
                echo json_encode(["status" => "success", "message" => "Riwayat pemenang dihapus."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal menghapus riwayat."]);
            }
        }
        exit;
    }

    $peserta_id = isset($_POST['peserta_id']) ? (int)$_POST['peserta_id'] : 0;
    $bulan_menang = isset($_POST['bulan_menang']) ? trim($_POST['bulan_menang']) : '';

    if ($peserta_id > 0 && !empty($bulan_menang)) {
        // Cek apakah sudah pernah menang sebelumnya
        $cek = $conn->prepare("SELECT id FROM pemenang_arisan WHERE peserta_id = ?");
        $cek->bind_param("i", $peserta_id);
        $cek->execute();
        $res = $cek->get_result();

        if ($res->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Peserta ini sudah pernah menang sebelumnya!"]);
            exit;
        }

        // Simpan pemenang
        $stmt = $conn->prepare("INSERT INTO pemenang_arisan (peserta_id, bulan_menang) VALUES (?, ?)");
        $stmt->bind_param("is", $peserta_id, $bulan_menang);

        if ($stmt->execute()) {
            catat_log($conn, "Admin mengesahkan pemenang arisan: ID Peserta $peserta_id untuk bulan $bulan_menang");
            echo json_encode(["status" => "success", "message" => "Pemenang berhasil disimpan!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
    }
}
?>
