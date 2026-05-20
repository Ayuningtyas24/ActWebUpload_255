<?php
$uploadDir = "uploads/";
$file      = basename($_GET['file'] ?? '');
$filePath  = $uploadDir . $file;

if ($file && file_exists($filePath)) {
    unlink($filePath);
    // Kalau dipanggil dari fetch() JS tidak perlu redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_GET['ajax'])) {
        echo "ok";
    } else {
        header("Location: index.html?msg=File $file berhasil dihapus.&ok=1");
    }
} else {
    header("Location: index.html?msg=File tidak ditemukan.");
}
exit;
?>