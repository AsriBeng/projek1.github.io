<?php
session_start();
require 'config.php';

// ================== AMBIL RANGE TANGGAL ==================
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// validasi sederhana format date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   $dari = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = date('Y-m-d');

// WHERE range inklusif (00:00:00 - 23:59:59)
$where = "t.tgl_transaksi >= '{$dari} 00:00:00' AND t.tgl_transaksi <= '{$sampai} 23:59:59'";

$keterangan = "Laporan Transaksi<br>Dari Tanggal " . date('d/m/Y', strtotime($dari)) .
              " sampai " . date('d/m/Y', strtotime($sampai));

// ================== QUERY DATA ==================
$sqlTrans = "
    SELECT 
        t.id_transaksi,
        t.tgl_transaksi,
        t.total_transaksi,
        t.status,
        p.nama
    FROM tb_transaksi t
    LEFT JOIN tb_pelanggan p ON t.id_user = p.id_user
    WHERE $where
    ORDER BY t.tgl_transaksi ASC
";

$result = mysqli_query($conn, $sqlTrans);

// ================== HITUNG TOTAL ==================
// Jika mau total semua transaksi: set $hitungSemua = true
$hitungSemua = false;

// Kalau hanya yang lunas/sudah bayar, isi sesuai status di sistemmu
$statusDihitung = ["Sudah Bayar", "Lunas"]; // <-- sesuaikan

$grandTotal = 0;
$rows = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;

        if ($hitungSemua) {
            $grandTotal += (int)$r['total_transaksi'];
        } else {
            if (in_array($r['status'], $statusDihitung, true)) {
                $grandTotal += (int)$r['total_transaksi'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - Omah Kopi</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
        body { font-family: "Poppins", sans-serif; font-size: 12px; }
        .kop { text-align: center; margin-bottom: 10px; }
        .kop h3 { margin: 0; }
        .table th, .table td { padding: 6px; vertical-align: middle; }
        .muted { color: #666; font-size: 11px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">

<div class="container-fluid">

    <div class="no-print mt-2 mb-2 text-right">
        <button onclick="window.print()" class="btn btn-sm btn-secondary">Print / Simpan PDF</button>
    </div>

    <div class="kop">
        <h3>Omah Kopi</h3>
        <div><?php echo $keterangan; ?></div>
        <div class="muted">Dicetak: <?php echo date('d-m-Y H:i'); ?></div>
        <hr>
    </div>

    <div class="d-flex justify-content-between mb-2">
        <div class="muted">
            <?php if (!$hitungSemua): ?>
                * Total dihitung dari status: <?php echo htmlspecialchars(implode(', ', $statusDihitung)); ?>
            <?php else: ?>
                * Total dihitung dari semua transaksi.
            <?php endif; ?>
        </div>
        <div>
            <strong>Total Pendapatan: Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?></strong>
        </div>
    </div>

    <table class="table table-bordered table-sm">
        <thead class="thead-light">
            <tr>
                <th style="width:40px;">No</th>
                <th style="width:160px;">ID Pesanan</th>
                <th style="width:150px;">Tanggal</th>
                <th>Pelanggan</th>
                <th style="width:120px;">Total</th>
                <th style="width:120px;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)): ?>
            <?php $no = 1; foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['id_transaksi']); ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($row['tgl_transaksi'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></td>
                    <td>Rp <?php echo number_format((int)$row['total_transaksi'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada data.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
