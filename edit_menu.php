<?php
session_start();
require 'config.php';

// CEK PARAMETER ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID menu tidak valid.");
}

$id_menu = (int) $_GET['id'];
$error   = '';
$success = '';

// AMBIL DATA MENU YANG AKAN DIEDIT
$sqlMenu   = "SELECT * FROM tb_menu WHERE id_menu = $id_menu LIMIT 1";
$resMenu   = mysqli_query($conn, $sqlMenu);
$menu      = mysqli_fetch_assoc($resMenu);

if (!$menu) {
    die("Data menu tidak ditemukan.");
}

// AMBIL DATA KATEGORI UNTUK DROPDOWN
$sqlKat   = "SELECT * FROM tb_kategori_menu ORDER BY kategori ASC";
$resKat   = mysqli_query($conn, $sqlKat);

// PROSES SUBMIT FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_menu       = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $kategori_menu   = (int) $_POST['kategori_menu'];
    $keterangan_menu = mysqli_real_escape_string($conn, $_POST['keterangan_menu']);
    $harga_menu      = (int) $_POST['harga_menu'];

    // nama foto lama
    $oldFoto = $menu['foto_menu'];
    $newFoto = $oldFoto;

    // CEK JIKA ADA FILE BARU DIUPLOAD
    if (!empty($_FILES['foto_menu']['name'])) {
        $uploadDir  = __DIR__ . '/assets/img/products/';
        $fileName   = basename($_FILES['foto_menu']['name']);
        $fileTmp    = $_FILES['foto_menu']['tmp_name'];
        $fileSize   = $_FILES['foto_menu']['size'];

        $ext        = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExt)) {
            $error = "Format foto harus JPG / JPEG / PNG.";
        } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB
            $error = "Ukuran foto maksimal 2MB.";
        } else {
            // buat nama file unik
            $newName = 'menu_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dest    = $uploadDir . $newName;

            if (move_uploaded_file($fileTmp, $dest)) {
                $newFoto = $newName;

                // hapus foto lama jika ada
                if (!empty($oldFoto)) {
                    $oldPath = $uploadDir . $oldFoto;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            } else {
                $error = "Gagal mengupload foto baru.";
            }
        }
    }

    // JIKA TIDAK ADA ERROR, UPDATE DATABASE
    if (empty($error)) {
        $sqlUpdate = "UPDATE tb_menu SET 
                        nama_menu       = '$nama_menu',
                        kategori_menu   = $kategori_menu,
                        keterangan_menu = '$keterangan_menu',
                        harga_menu      = $harga_menu,
                        foto_menu       = '$newFoto'
                      WHERE id_menu = $id_menu";

        if (mysqli_query($conn, $sqlUpdate)) {
            $success = "Perubahan menu berhasil disimpan! Anda akan dialihkan...";
        } else {
            $error = "Gagal mengupdate data: " . mysqli_error($conn);
        }

    }

    // reload data menu terbaru (kalau gagal pun biar form isiannya mengikuti input tadi)
    $sqlMenu   = "SELECT * FROM tb_menu WHERE id_menu = $id_menu LIMIT 1";
    $resMenu   = mysqli_query($conn, $sqlMenu);
    $menu      = mysqli_fetch_assoc($resMenu);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu - Omah Kopi</title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
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

        .btn-toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 22px;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .wrapper-admin {
                flex-direction: column;
            }
            .sidebar {
                position: fixed;
                z-index: 999;
                height: 100%;
                transform: translateX(-220px);
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
                <h2>Edit Menu</h2>
            </div>
            <div class="user-info">
                Login sebagai:
                <strong><?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; ?></strong>
                | <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                Form Edit Menu
            </div>
            <div class="card-body">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>

                    <!-- Auto redirect setelah 1.5 detik -->
                    <script>
                        setTimeout(function() {
                            window.location.href = "menues.php?msg=updated";
                        }, 1500);
                    </script>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Menu</label>
                        <input type="text" name="nama_menu" class="form-control" 
                               value="<?= htmlspecialchars($menu['nama_menu']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori_menu" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($kat = mysqli_fetch_assoc($resKat)): ?>
                                <option value="<?= $kat['id_kategori']; ?>"
                                    <?= ($kat['id_kategori'] == $menu['kategori_menu']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kat['kategori']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan_menu" class="form-control" rows="3"><?= htmlspecialchars($menu['keterangan_menu']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" name="harga_menu" class="form-control"
                               value="<?= htmlspecialchars($menu['harga_menu']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Foto Menu</label><br>
                        <?php if (!empty($menu['foto_menu'])): ?>
                            <img src="assets/img/products/<?= htmlspecialchars($menu['foto_menu']); ?>" 
                                 alt="<?= htmlspecialchars($menu['nama_menu']); ?>" width="120" class="mb-2"><br>
                        <?php endif; ?>
                        <input type="file" name="foto_menu" class="form-control-file">
                        <small class="form-text text-muted">
                            Kosongkan jika tidak ingin mengganti foto. Maks 2MB, JPG/PNG.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    <a href="menues.php" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
    $(document).on('click', '.btn-toggle-sidebar', function() {
        $('.sidebar').toggleClass('show');
    });
</script>

</body>
</html>
