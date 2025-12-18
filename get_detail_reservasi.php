<?php
session_start();
require 'config.php';

if (!isset($_GET['id_transaksi'])) {
    echo json_encode(['error' => 'ID tidak ditemukan']);
    exit;
}

$id_transaksi = (int) $_GET['id_transaksi'];

// Opsional: amankan supaya hanya transaksi milik user login
$id_user = isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 0;

/* --- Ambil data header reservasi (transaksi + meja) --- */
$sql_header = "
    SELECT t.id_transaksi, t.tgl_transaksi, t.total_transaksi, t.status,
           m.meja
    FROM tb_transaksi t
    JOIN tb_meja m ON t.id_meja = m.id_meja
    WHERE t.id_transaksi = $id_transaksi
      AND t.id_user = $id_user
    LIMIT 1
";
$header = mysqli_query($conn, $sql_header);

if (!$header || mysqli_num_rows($header) == 0) {
    echo json_encode(['error' => 'Data reservasi tidak ditemukan']);
    exit;
}

$h = mysqli_fetch_assoc($header);

/* --- Ambil detail menu --- */
$sql_detail = "
    SELECT d.qty, mn.nama_menu, mn.harga_menu
    FROM tb_detail_transaksi d
    JOIN tb_menu mn ON d.id_menu = mn.id_menu
    WHERE d.id_transaksi = $id_transaksi
";
$detail = mysqli_query($conn, $sql_detail);

/* --- Bangun HTML untuk dikirim ke modal --- */

// INFO HEADER: 3 <p> persis seperti struktur di modal
ob_start();
?>
<p class="mb-1"><strong>Meja:</strong> <?= htmlspecialchars($h['meja']); ?></p>
<p class="mb-1"><strong>Tanggal:</strong> <?= htmlspecialchars($h['tgl_transaksi']); ?></p>
<p class="mb-1">
    <strong>Total:</strong>
    Rp <?= number_format($h['total_transaksi'], 0, ',', '.'); ?>
</p>
<?php
$info_html = ob_get_clean();

// TABEL DETAIL: hanya 3 kolom (Menu, Harga, Qty)
ob_start();
if ($detail && mysqli_num_rows($detail) > 0) {
    while ($row = mysqli_fetch_assoc($detail)) {
        ?>
        <tr>
            <td><?= htmlspecialchars($row['nama_menu']); ?></td>
            <td>Rp <?= number_format($row['harga_menu'], 0, ',', '.'); ?></td>
            <td><?= (int) $row['qty']; ?></td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
        <td colspan="3" class="text-center">Tidak ada menu pada reservasi ini.</td>
    </tr>
    <?php
}
$rows_html = ob_get_clean();

echo json_encode([
    'info' => $info_html,
    'rows' => $rows_html
]);
