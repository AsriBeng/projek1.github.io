<?php
session_start();
require 'config.php';

// ========== PROSES HAPUS ADMIN ==========
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $cek = mysqli_query($conn, "SELECT id_admin FROM tb_admin WHERE id_admin = $id");
    if ($cek && mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "DELETE FROM tb_admin WHERE id_admin = $id");
        header("Location: staf_user.php?msg=deleted");
        exit;
    } else {
        header("Location: staf_user.php?msg=notfound");
        exit;
    }
}

// ambil data admin / staf
$sql = "SELECT * FROM tb_admin ORDER BY id_admin";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Staf / Admin - Omah Kopi</title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body { margin: 0; font-family: "Poppins", sans-serif; background-color: #f5f5f5; }
        .wrapper-admin { display: flex; min-height: 100vh; }
        .sidebar {
            width: 220px; background-color: #231c17; color: #fff;
            padding: 20px 0; position: fixed; top: 0; bottom: 0; left: 0;
            transition: transform 0.3s ease;
        }
        .sidebar .brand { font-size: 20px; font-weight: 700; text-align: center; margin-bottom: 30px; }
        .sidebar .brand span { color: #f2a154; }
        .sidebar .menu { list-style: none; padding-left: 0; margin: 0; }
        .sidebar .menu li { margin-bottom: 5px; }
        .sidebar .menu a {
            display: block; padding: 10px 20px; color: #f1f1f1;
            text-decoration: none; font-size: 15px;
        }
        .sidebar .menu a i { margin-right: 8px; }
        .sidebar .menu a:hover, .sidebar .menu a.active { background-color: #3a2f26; }

        .main-content { margin-left: 220px; padding: 20px; width: 100%; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .topbar h2 { margin: 0; }
        .topbar .user-info { font-size: 14px; }
        .card { border-radius: 10px; }

        .btn-toggle-sidebar {
            display: none; background: none; border: none;
            font-size: 22px; margin-right: 10px;
        }

        @media (max-width: 768px) {
            .wrapper-admin { flex-direction: column; }
            .sidebar {
                position: fixed; z-index: 999; height: 100%;
                transform: translateX(-220px);
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 15px; }
            .btn-toggle-sidebar { display: block; }
            .topbar h2 { font-size: 18px; }
            .topbar .user-info { font-size: 13px; }
        }
    </style>
</head>
<body>

<div class="wrapper-admin">
    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="brand">Omah <span>Kopi</span></div>
        <ul class="menu">
            <li><a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="menuadmin.php"><i class="fas fa-calendar-check"></i> Reservasi</a></li>
            <li><a href="menues.php"><i class="fas fa-coffee"></i> Menu</a></li>
            <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="staf_user.php" class="active"><i class="fas fa-user-shield"></i> Staf / Admin</a></li>
        </ul>
    </nav>

    <!-- KONTEN -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h2>Data Staf / Admin</h2>
            </div>
            <div class="user-info">
                Login sebagai:
                <strong><?= $_SESSION['nama'] ?? 'Admin'; ?></strong> |
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Daftar Staf / Admin</span>
                <a href="tambah_staf.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Tambah Staf/Admin
                </a>
            </div>
            <div class="card-body">

                <!-- NOTIF -->
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success">Staf/Admin berhasil dihapus.</div>
                <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'notfound'): ?>
                    <div class="alert alert-warning">Data Staf/Admin tidak ditemukan.</div>
                <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success">Staf/Admin baru berhasil ditambahkan.</div>
                <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                    <div class="alert alert-success">Data Staf/Admin berhasil diperbarui.</div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td><?= htmlspecialchars($row['password']); ?></td>
                                    <td>
                                        <a href="edit_staf.php?id=<?= $row['id_admin']; ?>"
                                           class="btn btn-sm btn-warning mb-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="staf_user.php?hapus=<?= $row['id_admin']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus staf/admin ini?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else { ?>
                            <tr><td colspan="5" class="text-center">Belum ada data staf/admin.</td></tr>
                        <?php } ?>
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
    $(document).on('click', '.btn-toggle-sidebar', function () {
        $('.sidebar').toggleClass('show');
    });
</script>
</body>
</html>
