<?php
session_start();
require 'config.php'; // $conn

// Default: awal bulan ini s.d hari ini (mirip contoh)
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// Validasi sederhana (biar aman)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   $dari = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = date('Y-m-d');

// WHERE range (inklusif dari 00:00:00 sampai 23:59:59)
$where = "t.tgl_transaksi >= '{$dari} 00:00:00' AND t.tgl_transaksi <= '{$sampai} 23:59:59'";

// Query data transaksi + pelanggan
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
$resultTrans = mysqli_query($conn, $sqlTrans);

// Total Pendapatan (biasanya hanya yang sudah bayar/lunas)
// Ubah status sesuai sistem kamu:
$statusDihitung = ["Sudah Bayar", "Lunas"]; // <-- sesuaikan bila perlu

$grandTotal = 0;
if ($resultTrans && mysqli_num_rows($resultTrans) > 0) {
    while ($r = mysqli_fetch_assoc($resultTrans)) {
        if (in_array($r['status'], $statusDihitung, true)) {
            $grandTotal += (int)$r['total_transaksi'];
        }
    }
    mysqli_data_seek($resultTrans, 0); // reset pointer untuk tampil tabel
}

// untuk link cetak
$queryString = http_build_query([
    'dari' => $dari,
    'sampai' => $sampai
]);

$keterangan = "Dari Tanggal " . date('d/m/Y', strtotime($dari)) . " sampai " . date('d/m/Y', strtotime($sampai));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Omah Kopi</title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background-color: #f5f5f5;
        }
        .wrapper-admin {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 220px;
            background-color: #231c17;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
        }
        .sidebar .brand {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar .brand span { color: #f2a154; }
        .sidebar .menu { list-style: none; padding-left: 0; margin: 0; }
        .sidebar .menu li { margin-bottom: 5px; }
        .sidebar .menu a {
            display: block;
            padding: 10px 20px;
            color: #f1f1f1;
            text-decoration: none;
            font-size: 15px;
        }
        .sidebar .menu a i { margin-right: 8px; }
        .sidebar .menu a:hover,
        .sidebar .menu a.active { background-color: #3a2f26; }

        .main-content {
            margin-left: 220px;
            padding: 20px;
            width: 100%;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .topbar h2 { margin: 0; }
        .card { border-radius: 10px; }
    </style>
</head>
<body>

<div class="wrapper-admin">
    <nav class="sidebar">
        <div class="brand">Omah <span>Kopi</span></div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="menuadmin.php"><i class="fas fa-calendar-check"></i> Reservasi</a></li>
            <li><a href="menues.php"><i class="fas fa-coffee"></i> Menu</a></li>
            <li><a href="laporan.php" class="active"><i class="fas fa-file-alt"></i> Laporan</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="staf_user.php"><i class="fas fa-user-shield"></i> Staf / Admin</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="topbar">
            <h2>Laporan Transaksi</h2>
            <div class="user-info">
                Login sebagai: <strong>Admin</strong> | <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Laporan Transaksi</div>
            <div class="card-body">

                <!-- FILTER RANGE TANGGAL -->
                <form method="get" class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label class="font-weight-bold">Dari Tanggal</label>
                        <input type="date" name="dari" class="form-control"
                               value="<?= htmlspecialchars($dari); ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="font-weight-bold">Sampai Tanggal</label>
                        <input type="date" name="sampai" class="form-control"
                               value="<?= htmlspecialchars($sampai); ?>">
                    </div>

                    <div class="form-group col-md-6 text-right">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Tampilkan
                        </button>
                        <a href="laporan_cetak.php?<?= $queryString; ?>" target="_blank" class="btn btn-success">
                            <i class="fas fa-print"></i> Cetak
                        </a>
                    </div>
                </form>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="m-0"><?= $keterangan; ?></h5>
                    <h5 class="m-0">
                        <span class="font-weight-bold">Total Pendapatan:</span>
                        <span class="text-success font-weight-bold">
                            Rp <?= number_format($grandTotal, 0, ',', '.'); ?>
                        </span>
                    </h5>
                </div>

                <!-- TABEL -->
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>ID Pesanan</th>
                                <th>Tanggal Transaksi</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($resultTrans && mysqli_num_rows($resultTrans) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($resultTrans)): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['id_transaksi']); ?></td>
                                    <td><?= date('d-m-Y H:i', strtotime($row['tgl_transaksi'])); ?></td>
                                    <td><?= htmlspecialchars($row['nama'] ?? '-'); ?></td>
                                    <td>Rp <?= number_format((int)$row['total_transaksi'], 0, ',', '.'); ?></td>
                                    <td><?= htmlspecialchars($row['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data transaksi untuk rentang tanggal ini.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <small class="text-muted">
                    * Total Pendapatan dihitung dari transaksi berstatus: <?= implode(', ', $statusDihitung); ?>
                </small>

            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
