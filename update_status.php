<?php
session_start();
require 'config.php';
$userId   = $_SESSION['id_admin'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id  = $_POST['id_transaksi'];
    $status = $_POST['status'];

    $update = "UPDATE tb_transaksi SET  id_admin='$userId', status='$status' WHERE id_transaksi='$id'";

    if (mysqli_query($conn, $update)) {
        // kembali ke dashboard dan kirim notifikasi
        header("Location: dashboard.php?success=1");
        exit;
    } else {
        header("Location: dashboard.php?error=1");
        exit;
    }
}
?>
