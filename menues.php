<?php
session_start();
require 'config.php';

// PROSES HAPUS MENU (jika ada ?hapus=)
$errorDelete = '';
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    // ambil data menu untuk tahu nama file gambarnya
    $sqlFoto = "SELECT foto_menu FROM tb_menu WHERE id_menu = $id";
    $resFoto = mysqli_query($conn, $sqlFoto);

    if ($resFoto && mysqli_num_rows($resFoto) > 0) {
        $rowFoto  = mysqli_fetch_assoc($resFoto);
        $fileName = trim($rowFoto['foto_menu']);
        $filePath = __DIR__ . '/assets/img/products/' . $fileName;

        // hapus dari database
        $delete = mysqli_query($conn, "DELETE FROM tb_menu WHERE id_menu = $id ORDER BY kategori_menu");

        if ($delete) {
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            header("Location: menues.php?msg=deleted");
            exit;
        } else {
            $errorDelete = "Gagal menghapus data: " . mysqli_error($conn);
        }
    } else {
        $errorDelete = "Data menu tidak ditemukan.";
    }
}

// QUERY: AMBIL DATA MENU + NAMA KATEGORI
$sql = "SELECT m.id_menu,
               m.nama_menu,
               m.foto_menu,
               m.harga_menu,
               m.keterangan_menu,
               k.kategori
        FROM tb_menu m
        JOIN tb_kategori_menu k ON m.kategori_menu = k.id_kategori
        ORDER BY k.kategori, m.nama_menu ASC";
$result = mysqli_query($conn, $sql);
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
            transition: transform 0.3s ease;
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

        /* tombol toggle sidebar (mobile) */
        .btn-toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            margin-right: 10px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .wrapper-admin {
                flex-direction: column;
            }
            .sidebar {
                position: fixed;
                z-index: 999;
                height: 100%;
                transform: translateX(-220px); /* default: tertutup */
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .topbar {
                justify-content: space-between;
            }
            .btn-toggle-sidebar {
                display: block;
            }
            .topbar h2 {
                font-size: 18px;
            }
            .topbar .user-info {
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
            <li><a href="menuadmin.php">
                <i class="fas fa-calendar-check"></i> Reservasi
            </a></li>
            <li><a href="menues.php" class="active">
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
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Data Menu</h2>
            </div>
            <div class="user-info">
                Login sebagai:
                <strong><?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; ?></strong>
                | <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
    
                <span>Daftar Menu</span>

                <div>
                    <a href="kategori.php" class="btn btn-sm btn-info me-2">
                        <i class="fas fa-tags"></i> Kategori Menu
                    </a>

                    <a href="tambah_menu.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah Menu
                    </a>
                </div>

            </div>

            <div class="card-body">

                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success">Menu berhasil dihapus.</div>
                <?php endif; ?>

                <?php if (!empty($errorDelete)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorDelete); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-striped table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
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
                                    <td>
                                        <?php if (!empty($row['foto_menu'])): ?>
                                            <img src="assets/img/products/<?= htmlspecialchars($row['foto_menu']); ?>"
                                                 alt="<?= htmlspecialchars($row['nama_menu']); ?>"
                                                 width="80">
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada foto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['nama_menu']); ?></td>
                                    <td><?= htmlspecialchars($row['kategori']); ?></td>
                                    <td>Rp <?= number_format($row['harga_menu'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="edit_menu.php?id=<?= $row['id_menu']; ?>"
                                           class="btn btn-sm btn-warning mb-1">
                                            Edit
                                        </a>
                                        <a href="menues.php?hapus=<?= $row['id_menu']; ?>"
                                           class="btn btn-sm btn-danger mb-1"
                                           onclick="return confirm('Yakin hapus menu ini?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data menu.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
    // toggle sidebar untuk mobile
    $(document).on('click', '.btn-toggle-sidebar', function() {
        $('.sidebar').toggleClass('show');
    });
</script>

</body>
</html>
