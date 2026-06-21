<?php
// logger.php - Sistem Jejak Rekam Digital Avelza Group
function catat_log($conn, $aksi) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $conn->prepare("INSERT INTO log_aktivitas (aksi, ip_address) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ss", $aksi, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
?>
