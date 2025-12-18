<?php
session_start();
require 'config.php'; // $conn

// ================== TOTAL TRANSAKSI HARI INI ==================
$sqlHariIni = "
    SELECT IFNULL(SUM(total_transaksi),0) AS total_hari_ini
    FROM tb_transaksi
    WHERE DATE(tgl_transaksi) = CURDATE()
";
$resHariIni = mysqli_query($conn, $sqlHariIni);
$rowHariIni = mysqli_fetch_assoc($resHariIni);
$totalHariIni = (int)$rowHariIni['total_hari_ini'];

// ================== TOTAL TRANSAKSI BULAN INI ==================
$sqlBulanIni = "
    SELECT IFNULL(SUM(total_transaksi),0) AS total_bulan_ini
    FROM tb_transaksi
    WHERE YEAR(tgl_transaksi) = YEAR(CURDATE())
      AND MONTH(tgl_transaksi) = MONTH(CURDATE())
";
$resBulanIni = mysqli_query($conn, $sqlBulanIni);
$rowBulanIni = mysqli_fetch_assoc($resBulanIni);
$totalBulanIni = (int)$rowBulanIni['total_bulan_ini'];

// ================== DATA RESERVASI PENDING ==================
// CATATAN: sesuaikan nama tabel & kolom jika beda.
// asumsi: tb_reservasi(id_reservasi, id_user, id_meja, tgl_transaksi, total, status)
//         tb_pelanggan(id_user, nama)
//         tb_meja(id_meja, meja / no_meja)
$sqlPending = "
    SELECT r.*, p.nama, m.meja
    FROM tb_transaksi r
    LEFT JOIN tb_pelanggan p ON r.id_user = p.id_user
    LEFT JOIN tb_meja m ON r.id_meja = m.id_meja
    WHERE r.status != 'selesai'
      AND DATE(r.tgl_transaksi) = CURDATE()
    ORDER BY r.tgl_transaksi ASC
";

$resPending = mysqli_query($conn, $sqlPending);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Omah Kopi</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
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
        /* SIDEBAR */
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
        .sidebar .brand span {
            color: #f2a154;
        }
        .sidebar .menu {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .sidebar .menu li {
            margin-bottom: 5px;
        }
        .sidebar .menu a {
            display: block;
            padding: 10px 20px;
            color: #f1f1f1;
            text-decoration: none;
            font-size: 15px;
        }
        .sidebar .menu a i {
            margin-right: 8px;
        }
        .sidebar .menu a:hover,
        .sidebar .menu a.active {
            background-color: #3a2f26;
        }

        /* KONTEN UTAMA */
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
        .topbar h2 {
            margin: 0;
        }
        .topbar .user-info {
            font-size: 14px;
        }
        .card {
            border-radius: 10px;
        }
        .card-icon {
            font-size: 30px;
            opacity: 0.6;
        }
    </style>
</head>
<body>

<div class="wrapper-admin">
    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="brand">
            Omah <span>Kopi</span>
        </div>
        <ul class="menu">
            <li>
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="menuadmin.php">
                    <i class="fas fa-calendar-check"></i> Reservasi
                </a>
            </li>
            <li>
                <a href="menues.php">
                    <i class="fas fa-coffee"></i> Menu
                </a>
            </li>
            <li>
                <a href="laporan.php">
                    <i class="fas fa-file-alt"></i> Laporan
                </a>
            </li>
            <li>
                <a href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            <li>
                <a href="staf_user.php">
                    <i class="fas fa-user-shield"></i> Staf / Admin
                </a>
            </li>
        </ul>
    </nav>

    <!-- KONTEN -->
    <div class="main-content">
        <div class="topbar">
            <h2>Dashboard</h2>
            <div class="user-info">
                Login sebagai: <strong>Admin</strong>
                | <a href="logout.php">Logout</a>
            </div>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Reservasi berhasil diupdate.</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Gagal update reservasi.</div>
        <?php endif; ?>


        <!-- KARTU RINGKASAN -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Transaksi Hari Ini</h5>
                            <p class="mb-0 text-muted"><?php echo date('d M Y'); ?></p>
                            <h4 class="mt-2 mb-0">
                                Rp <?php echo number_format($totalHariIni, 0, ',', '.'); ?>
                            </h4>
                        </div>
                        <div class="card-icon text-success">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Transaksi Bulan Ini</h5>
                            <p class="mb-0 text-muted">
                                <?php echo date('F Y'); ?>
                            </p>
                            <h4 class="mt-2 mb-0">
                                Rp <?php echo number_format($totalBulanIni, 0, ',', '.'); ?>
                            </h4>
                        </div>
                        <div class="card-icon text-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL RESERVASI PENDING -->
        <div class="card">
            <div class="card-header">
                Reservasi Pending Hari Ini (<?php echo date('d M Y'); ?>)
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($resPending && mysqli_num_rows($resPending) > 0):
                            $no = 1;
                            while ($r = mysqli_fetch_assoc($resPending)):
                                // sesuaikan nama kolom: tgl_transaksi, total_menu/total_transaksi dsb.
                                $tglReservasi = isset($r['tgl_transaksi']) 
                                                ? date('d-m-Y H:i', strtotime($r['tgl_transaksi']))
                                                : '-';

                                $namaPelanggan = $r['nama'] ?? '-';

                                // Nama/no meja: pilih mana yang ada
                                $namaMeja = '-';
                                if (!empty($r['meja'])) {
                                    $namaMeja = $r['meja'];
                                }

                                // Total reservasi (ganti sesuai kolommu, misal total_menu / total_transaksi / total)
                                $totalReservasi = 0;
                                if (isset($r['total_transaksi'])) {
                                    $totalReservasi = (int)$r['total_transaksi'];
                                } elseif (isset($r['total_menu'])) {
                                    $totalReservasi = (int)$r['total_menu'];
                                } elseif (isset($r['total'])) {
                                    $totalReservasi = (int)$r['total'];
                                }
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($r['id_transaksi']); ?></td>
                                <td><?php echo htmlspecialchars($namaPelanggan); ?></td>
                                <td><?php echo htmlspecialchars($namaMeja); ?></td>
                                <td>Rp <?php echo number_format($totalReservasi, 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($r['status']); ?></td>
                                <td id="action-btn-<?php echo $r['id_transaksi']; ?>">
                                    <button class="btn btn-sm btn-primary"
                                            onclick="editStatus('<?php echo $r['id_transaksi']; ?>')">
                                        Update Status
                                    </button>
                                </td>
                                <td id="edit-row-<?php echo $r['id_transaksi']; ?>" style="display:none;">
                                    <form method="post" action="update_status.php" class="form-inline d-flex">

                                        <input type="hidden" name="id_transaksi" value="<?php echo $r['id_transaksi']; ?>">
                                        <select name="status" class="form-control mr-2">
                                            <option value="pending" <?= ($r['status']=='pending')   ? 'selected' : ''; ?>>Pending</option>
                                            <option value="konfirmasi" <?= ($r['status']=='konfirmasi')? 'selected' : ''; ?>>Konfirmasi</option>
                                            <option value="selesai" <?= ($r['status']=='selesai')   ? 'selected' : ''; ?>>Selesai</option>
                                        </select>

                                        <button type="submit" class="btn btn-success btn-sm mr-2">Save</button>

                                        <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="cancelEdit('<?php echo $r['id_transaksi']; ?>')">
                                            Batal
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada reservasi pending.</td>
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
<script>
function editStatus(id) {
    // sembunyikan tombol update
    document.getElementById('action-btn-' + id).style.display = 'none';


    // tampilkan form row
    document.getElementById('edit-row-' + id).style.display = 'table-row';
}

function cancelEdit(id) {
    // tampilkan tombol update
    document.getElementById('action-btn-' + id).style.display = 'table-cell';


    // sembunyikan form
    document.getElementById('edit-row-' + id).style.display = 'none';
}
</script>


</body>
</html>
