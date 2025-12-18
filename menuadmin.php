<?php
session_start();
require 'config.php'; // pastikan ada $conn
$adminId= $_SESSION['id_admin'];
// PROSES UPDATE STATUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id_transaksi = (int) $_POST['id_transaksi'];
    $status       = mysqli_real_escape_string($conn, $_POST['status']);

    // pastikan hanya 3 status ini
    $allowed = ['pending', 'konfirmasi', 'selesai'];
    if (in_array($status, $allowed)) {
        $sql_update = "UPDATE tb_transaksi SET id_admin='$adminId', status = '$status' WHERE id_transaksi = $id_transaksi";
        mysqli_query($conn, $sql_update);
    }

    // reload supaya tidak resubmit form saat refresh
    header("Location: menuadmin.php");
    exit;
}

// AMBIL DATA TRANSAKSI
$sql = "SELECT t.*, 
               p.nama       AS nama_user,
               m.meja  AS nama_meja
        FROM tb_transaksi t
        LEFT JOIN tb_pelanggan p ON t.id_user = p.id_user
        LEFT JOIN tb_meja m      ON t.id_meja = m.id_meja
        ORDER BY t.tgl_transaksi DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Omah Kopi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap (sesuaikan jika pakai file lokal) -->
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
            background-color: #231c17; /* warna yang diminta */
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
        .status-select {
            width: 120px;
        }
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .wrapper-admin {
                flex-direction: column; /* sidebar di atas, konten di bawah */
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;      /* hilangkan offset 220px */
                padding: 15px;       /* sedikit lebih rapat di mobile */
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .topbar h2 {
                font-size: 18px;
            }

            .topbar .user-info {
                font-size: 13px;
            }

            .sidebar .brand {
                font-size: 18px;
                margin-bottom: 10px;
            }

            .sidebar .menu a {
                padding: 8px 15px;
                font-size: 14px;
            }

            /* tabel: biar bisa scroll horizontal kalau kepanjangan */
            .card-body .table-responsive {
                font-size: 13px;
            }
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
            <li><a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="menuadmin.php" class="active">
                <i class="fas fa-calendar-check"></i> Reservasi
            </a></li>
            <li><a href="menues.php">
                <i class="fas fa-coffee"></i> Menu
            </a></li>
            <li><a href="laporan.php">
                <i class="fas fa-file-alt"></i> Laporan
            </a></li>
            <li><a href="users.php">
                <i class="fas fa-users"></i> Users
            </a></li>
            <li><a href="staf_user.php"><i class="fas fa-user-shield"></i> Staf / Admin</a></li>
        </ul>
    </nav>

    <!-- KONTEN -->
    <div class="main-content">
        <div class="topbar">
            <h2>Data Reservasi</h2>
            <div class="user-info">
                Login: 
                <strong>
                    <?php 
                        echo isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin';
                    ?>
                </strong>
                | <a href="logout.php">Logout</a>
            </div>
        </div>

        <!-- CARD DATA RESERVASI / TRANSAKSI -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><strong>Data Transaksi / Reservasi</strong></span>

                <a href="meja.php" class="btn btn-sm btn-info">
                    <i class="fas fa-chair"></i> Daftar Meja
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>User</th>
                                <th>Meja</th>
                                <th>Tanggal</th>
                                <th>Total Menu</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi (Konfirmasi / Detail)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $id_transaksi = (int)$row['id_transaksi'];

                                // hitung total menu (jumlah item)
                                $sql_totmenu = "SELECT SUM(qty) AS total_menu 
                                                FROM tb_detail_transaksi 
                                                WHERE id_transaksi = $id_transaksi";
                                $res_totmenu = mysqli_query($conn, $sql_totmenu);
                                $tot_menu = 0;
                                if ($res_totmenu && $tm = mysqli_fetch_assoc($res_totmenu)) {
                                    $tot_menu = (int)$tm['total_menu'];
                                }

                                // total harga dari kolom di tb_transaksi (asumsi 'total_harga')
                                $total_harga = isset($row['total_transaksi']) ? $row['total_transaksi'] : 0;
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nama_user'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($row['nama_meja'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($row['tgl_transaksi'] ?? ''); ?></td>
                                    <td><?= $tot_menu; ?></td>
                                    <td>Rp <?= number_format($total_harga, 0, ',', '.'); ?></td>
                                    <td><?= htmlspecialchars($row['status']); ?></td>
                                    <td>
                                        <!-- FORM UPDATE STATUS -->
                                        <form method="post" class="d-inline form-update-status">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id_transaksi" value="<?= $id_transaksi; ?>">

                                            <select name="status" class="form-control form-control-sm status-select" style="display:none;">
                                                <option value="pending"   <?= ($row['status']=='pending')   ? 'selected' : ''; ?>>Pending</option>
                                                <option value="konfirmasi"<?= ($row['status']=='konfirmasi')? 'selected' : ''; ?>>Konfirmasi</option>
                                                <option value="selesai"   <?= ($row['status']=='selesai')   ? 'selected' : ''; ?>>Selesai</option>
                                            </select>

                                            <!-- PENTING: type="button" -->
                                            <button type="button" class="btn btn-sm btn-warning btn-update-status">
                                                Update Status
                                            </button>
                                        </form>

                                        <!-- TOMBOL DETAIL -->
                                        <button type="button" 
                                                class="btn btn-sm btn-info"
                                                data-toggle="modal" 
                                                data-target="#detailTransaksiModal<?= $id_transaksi; ?>">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data transaksi.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php
        // LOOP KEDUA: BUAT MODAL DETAIL UNTUK SETIAP TRANSAKSI
        // (bisa juga digabung di atas, ini dipisah biar rapi)
        mysqli_data_seek($result, 0); // reset pointer hasil query
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $id_transaksi = (int)$row['id_transaksi'];

                // ambil detail pesanan
                $sql_detail = "SELECT d.*, m.nama_menu, m.harga_menu 
                               FROM tb_detail_transaksi d
                               JOIN tb_menu m ON d.id_menu = m.id_menu
                               WHERE d.id_transaksi = $id_transaksi";
                $res_detail = mysqli_query($conn, $sql_detail);
                ?>
                <!-- MODAL DETAIL TRANSAKSI -->
                <div class="modal fade" id="detailTransaksiModal<?= $id_transaksi; ?>" tabindex="-1" role="dialog" aria-labelledby="detailTransaksiLabel<?= $id_transaksi; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="detailTransaksiLabel<?= $id_transaksi; ?>">
                                    Detail Transaksi #<?= $id_transaksi; ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Info singkat transaksi -->
                                <p><strong>User:</strong> <?= htmlspecialchars($row['nama_user'] ?? '-'); ?></p>
                                <p><strong>Meja:</strong> <?= htmlspecialchars($row['nama_meja'] ?? '-'); ?></p>
                                <p><strong>Tanggal:</strong> <?= htmlspecialchars($row['tgl_transaksi'] ?? ''); ?></p>
                                <p><strong>Status:</strong> <?= htmlspecialchars($row['status'] ?? ''); ?></p>

                                <hr>
                                <h6>Detail Pesanan</h6>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Menu</th>
                                                <th>Harga</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no_d = 1;
                                            $grand_total = 0;
                                            if ($res_detail && mysqli_num_rows($res_detail) > 0) {
                                                while ($d = mysqli_fetch_assoc($res_detail)) {
                                                    $subtotal = $d['harga_menu'] * $d['qty'];
                                                    $grand_total += $subtotal;
                                                    ?>
                                                    <tr>
                                                        <td><?= $no_d++; ?></td>
                                                        <td><?= htmlspecialchars($d['nama_menu']); ?></td>
                                                        <td>Rp <?= number_format($d['harga_menu'], 0, ',', '.'); ?></td>
                                                        <td><?= (int)$d['qty']; ?></td>
                                                        <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">Tidak ada detail pesanan.</td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-right">Total</th>
                                                <th>Rp <?= number_format($grand_total, 0, ',', '.'); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>

<script>
$(document).on('click', '.btn-update-status', function(e) {
    e.preventDefault(); // cegah submit otomatis

    var btn    = $(this);
    var form   = btn.closest('form');
    var select = form.find('.status-select');

    // Kalau select masih tersembunyi => tampilkan dan ubah tombol jadi "Save"
    if (select.is(':hidden')) {
        select.show();
        btn.text('Save');
        // kasih flag bahwa next click itu untuk save
        btn.data('mode', 'save');
        return;
    }

    // Kalau sudah mode save => submit form
    if (btn.data('mode') === 'save') {
        form.submit();
    }
});
</script>


</body>
</html>
