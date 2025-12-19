<?php
session_start();
require 'config.php'; // $conn


// ================== CARD 1: RESERVASI PENDING (HARI INI) ==================
$sqlReservasiPending = "
    SELECT COUNT(*) AS total_pending
    FROM tb_transaksi
    WHERE status = 'pending'
";
$resReservasiPending = mysqli_query($conn, $sqlReservasiPending);
$rowReservasiPending = mysqli_fetch_assoc($resReservasiPending);
$totalPending = (int)($rowReservasiPending['total_pending'] ?? 0);

// ================== CARD 2: RESERVASI BELUM SELESAI (KONFIRMASI) ==================
$sqlReservasiKonfirmasi = "
    SELECT COUNT(*) AS total_konfirmasi
    FROM tb_transaksi
    WHERE status = 'konfirmasi'
";
$resReservasiKonfirmasi = mysqli_query($conn, $sqlReservasiKonfirmasi);
$rowReservasiKonfirmasi = mysqli_fetch_assoc($resReservasiKonfirmasi);
$totalKonfirmasi = (int)($rowReservasiKonfirmasi['total_konfirmasi'] ?? 0);

// ================== CARD 4: TOTAL MENU ==================
$sqlTotalMenu = "SELECT COUNT(*) AS total_menu FROM tb_menu";
$resTotalMenu = mysqli_query($conn, $sqlTotalMenu);
$rowTotalMenu = mysqli_fetch_assoc($resTotalMenu);
$totalMenu = (int)($rowTotalMenu['total_menu'] ?? 0);

// ================== CARD 5: TOTAL PELANGGAN / USERS ==================
$sqlTotalUsers = "SELECT COUNT(*) AS total_users FROM tb_pelanggan";
$resTotalUsers = mysqli_query($conn, $sqlTotalUsers);
$rowTotalUsers = mysqli_fetch_assoc($resTotalUsers);
$totalUsers = (int)($rowTotalUsers['total_users'] ?? 0);

// ================== GRAFIK 30 HARI: PENDAPATAN ==================
$sqlPendapatan30 = "
    SELECT DATE(tgl_transaksi) AS tgl, IFNULL(SUM(total_transaksi),0) AS pendapatan
    FROM tb_transaksi
    WHERE DATE(tgl_transaksi) BETWEEN (CURDATE() - INTERVAL 29 DAY) AND CURDATE()
    GROUP BY DATE(tgl_transaksi)
    ORDER BY DATE(tgl_transaksi) ASC
";
$resPendapatan30 = mysqli_query($conn, $sqlPendapatan30);

$mapPendapatan = [];
if ($resPendapatan30) {
    while ($g = mysqli_fetch_assoc($resPendapatan30)) {
        $mapPendapatan[$g['tgl']] = (int)$g['pendapatan'];
    }
}

// ================== GRAFIK 30 HARI: TOTAL PESANAN ==================
$sqlPesanan30 = "
    SELECT DATE(tgl_transaksi) AS tgl, COUNT(*) AS jumlah_pesanan
    FROM tb_transaksi
    WHERE DATE(tgl_transaksi) BETWEEN (CURDATE() - INTERVAL 29 DAY) AND CURDATE()
    GROUP BY DATE(tgl_transaksi)
    ORDER BY DATE(tgl_transaksi) ASC
";
$resPesanan30 = mysqli_query($conn, $sqlPesanan30);

$mapPesanan = [];
if ($resPesanan30) {
    while ($g = mysqli_fetch_assoc($resPesanan30)) {
        $mapPesanan[$g['tgl']] = (int)$g['jumlah_pesanan'];
    }
}

// ================== LABEL 30 HARI ==================
$labels = [];
$dataPendapatan = [];
$dataPesanan = [];

for ($i = 29; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d M', strtotime($tgl));
    $dataPendapatan[] = $mapPendapatan[$tgl] ?? 0;
    $dataPesanan[] = $mapPesanan[$tgl] ?? 0;
}

// ================== TABEL: RESERVASI BELUM SELESAI (pending + konfirmasi) HARI INI ==================
$sqlBelumSelesai = "
    SELECT r.*, p.nama, m.meja
    FROM tb_transaksi r
    LEFT JOIN tb_pelanggan p ON r.id_user = p.id_user
    LEFT JOIN tb_meja m ON r.id_meja = m.id_meja
    WHERE r.status IN ('pending','konfirmasi')
    ORDER BY r.tgl_transaksi DESC
";

$resBelumSelesai = mysqli_query($conn, $sqlBelumSelesai);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Omah Kopi</title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">

    <style>
        body { margin:0; font-family:"Poppins", sans-serif; background-color:#f5f5f5; }
        .wrapper-admin { display:flex; min-height:100vh; }
        .sidebar {
            width:220px; background-color:#231c17; color:#fff; padding:20px 0;
            position:fixed; top:0; bottom:0; left:0;
        }
        .sidebar .brand { font-size:20px; font-weight:700; text-align:center; margin-bottom:30px; }
        .sidebar .brand span { color:#f2a154; }
        .sidebar .menu { list-style:none; padding-left:0; margin:0; }
        .sidebar .menu li { margin-bottom:5px; }
        .sidebar .menu a { display:block; padding:10px 20px; color:#f1f1f1; text-decoration:none; font-size:15px; }
        .sidebar .menu a i { margin-right:8px; }
        .sidebar .menu a:hover, .sidebar .menu a.active { background-color:#3a2f26; }

        .main-content { margin-left:220px; padding:20px; width:100%; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .topbar h2 { margin:0; }
        .topbar .user-info { font-size:14px; }
        .card { border-radius:10px; }
        .card-icon { font-size:30px; opacity:0.6; }
    </style>
</head>
<body>

<div class="wrapper-admin">
    <nav class="sidebar">
        <div class="brand">Omah <span>Kopi</span></div>
        <ul class="menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="menuadmin.php"><i class="fas fa-calendar-check"></i> Reservasi</a></li>
            <li><a href="menues.php"><i class="fas fa-coffee"></i> Menu</a></li>
            <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="staf_user.php"><i class="fas fa-user-shield"></i> Staf / Admin</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="topbar">
            <h2>Dashboard</h2>
            <div class="user-info">
                Login sebagai: <strong>Admin</strong> | <a href="logout.php">Logout</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Reservasi berhasil diupdate.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Gagal update reservasi.</div>
        <?php endif; ?>

        <!-- ✅ 2 GRAFIK PALING ATAS -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-2">Grafik Pendapatan 30 Hari</h5>
                        <p class="text-muted mb-3">Total pendapatan per hari</p>
                        <canvas id="chartPendapatan30" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-2">Grafik Total Pesanan 30 Hari</h5>
                        <p class="text-muted mb-3">Jumlah pesanan per hari</p>
                        <canvas id="chartPesanan30" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ CARD INFORMASI (URUT SESUAI PERMINTAAN) -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Reservasi Pending</h6>
                            <p class="mb-0 text-muted">Status pending</p>
                            <h4 class="mt-2 mb-0"><?php echo $totalPending; ?></h4>
                        </div>
                        <div class="card-icon text-warning"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Reservasi Belum Selesai</h6>
                            <p class="mb-0 text-muted">Status konfirmasi</p>
                            <h4 class="mt-2 mb-0"><?php echo $totalKonfirmasi; ?></h4>
                        </div>
                        <div class="card-icon text-primary"><i class="fas fa-clipboard-check"></i></div>
                    </div>
                </div>
            </div>


            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Menu</h6>
                            <p class="mb-0 text-muted">Tersedia</p>
                            <h4 class="mt-2 mb-0"><?php echo $totalMenu; ?></h4>
                        </div>
                        <div class="card-icon text-info"><i class="fas fa-mug-hot"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Pelanggan</h6>
                            <p class="mb-0 text-muted">Users</p>
                            <h4 class="mt-2 mb-0"><?php echo $totalUsers; ?></h4>
                        </div>
                        <div class="card-icon text-dark"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ TABEL: RESERVASI BELUM SELESAI (pending + konfirmasi) -->
        <div class="card">
            <div class="card-header">
                Reservasi Status Belum Selesai
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>ID Reservasi</th>
                                <th>Pelanggan</th>
                                <th>Meja</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th style="width:220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($resBelumSelesai && mysqli_num_rows($resBelumSelesai) > 0):
                            $no = 1;
                            while ($r = mysqli_fetch_assoc($resBelumSelesai)):

                                $namaPelanggan = $r['nama'] ?? '-';
                                $namaMeja = !empty($r['meja']) ? $r['meja'] : '-';

                                $totalReservasi = 0;
                                if (isset($r['total_transaksi'])) $totalReservasi = (int)$r['total_transaksi'];
                                elseif (isset($r['total_menu'])) $totalReservasi = (int)$r['total_menu'];
                                elseif (isset($r['total'])) $totalReservasi = (int)$r['total'];
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($r['id_transaksi']); ?></td>
                                <td><?php echo htmlspecialchars($namaPelanggan); ?></td>
                                <td><?php echo htmlspecialchars($namaMeja); ?></td>
                                <td>Rp <?php echo number_format($totalReservasi, 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($r['status']); ?></td>
                                <td>
                                    <div id="action-btn-<?php echo $r['id_transaksi']; ?>">
                                        <button class="btn btn-sm btn-primary"
                                                onclick="editStatus('<?php echo $r['id_transaksi']; ?>')">
                                            Update Status
                                        </button>
                                    </div>

                                    <div id="edit-row-<?php echo $r['id_transaksi']; ?>" style="display:none;">
                                        <form method="post" action="update_status.php" class="form-inline d-flex">
                                            <input type="hidden" name="id_transaksi" value="<?php echo $r['id_transaksi']; ?>">
                                            <select name="status" class="form-control mr-2">
                                                <option value="pending" <?= ($r['status']=='pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="konfirmasi" <?= ($r['status']=='konfirmasi') ? 'selected' : ''; ?>>Konfirmasi</option>
                                                <option value="selesai" <?= ($r['status']=='selesai') ? 'selected' : ''; ?>>Selesai</option>
                                            </select>

                                            <button type="submit" class="btn btn-success btn-sm mr-2">Save</button>
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="cancelEdit('<?php echo $r['id_transaksi']; ?>')">
                                                Batal
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada reservasi belum selesai.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function editStatus(id) {
    document.getElementById('action-btn-' + id).style.display = 'none';
    document.getElementById('edit-row-' + id).style.display = 'block';
}
function cancelEdit(id) {
    document.getElementById('action-btn-' + id).style.display = 'block';
    document.getElementById('edit-row-' + id).style.display = 'none';
}

// Data dari PHP
const labels = <?php echo json_encode($labels); ?>;
const dataPendapatan = <?php echo json_encode($dataPendapatan); ?>;
const dataPesanan = <?php echo json_encode($dataPesanan); ?>;

// Chart Pendapatan
new Chart(document.getElementById('chartPendapatan30').getContext('2d'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: dataPendapatan,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: { y: { beginAtZero: true } }
    }
});

// Chart Pesanan
new Chart(document.getElementById('chartPesanan30').getContext('2d'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Total Pesanan',
            data: dataPesanan,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

</body>
</html>
