<?php
session_start();
include 'config.php';

$error = '';
$success = '';

// biar value form tetap keisi kalau ada error
$namamenu = $_POST['namamenu'] ?? '';
$kategori = $_POST['kategori'] ?? '';
$harga    = $_POST['harga'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namamenu = trim($namamenu);
    $kategori = $kategori;
    $harga    = $harga;

    // pakai path absolut ke folder gambar
    $uploadDirFs = __DIR__ . '/assets/img/products/'; // untuk server (filesystem)

    // validasi input
    if ($namamenu === '' || $kategori === '' || $harga === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Gambar produk wajib diupload.';
    } else {
        $fileName = $_FILES['file']['name'];
        $fileTmp  = $_FILES['file']['tmp_name'];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExt)) {
            $error = 'Format gambar hanya boleh JPG/JPEG/PNG.';
        } else {
            // pastikan folder ada (kalau belum, coba buat)
            if (!is_dir($uploadDirFs)) {
                @mkdir($uploadDirFs, 0777, true);
            }

            // bikin nama file unik
            $newFileName = time() . '_' . uniqid() . '.' . $ext;
            $destPath = $uploadDirFs . $newFileName;

            if (!move_uploaded_file($fileTmp, $destPath)) {
                $error = 'Gagal menyimpan file gambar.';
            } else {
                // simpan ke database
                $nama_esc  = mysqli_real_escape_string($conn, $namamenu);
                $foto_esc  = mysqli_real_escape_string($conn, $newFileName);
                $kat_int   = (int) $kategori;
                $harga_int = (int) $harga;

                $sql = "INSERT INTO tb_menu (nama_menu, foto_menu, kategori_menu, keterangan_menu, harga_menu)
                        VALUES ('$nama_esc', '$foto_esc', '$kat_int', '', '$harga_int')";

                if (mysqli_query($conn, $sql)) {
                    // notifikasi via query string
                    header("Location: menues.php?msg=added");
                    exit;
                } else {
                    $error = 'Gagal menyimpan ke database: ' . mysqli_error($conn);
                    @unlink($destPath); // hapus file kalau DB gagal
                }
            }
        }
    }
}

// ambil data kategori buat dropdown
$kategoriOptions = [];
$qKat = mysqli_query($conn, "SELECT id_kategori, kategori FROM tb_kategori_menu ORDER BY id_kategori ASC");
if ($qKat) {
    while ($row = mysqli_fetch_assoc($qKat)) {
        $kategoriOptions[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Menu - Omah Kopi</title>

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
                transform: translateX(-220px); /* default tertutup */
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
                <h2>Tambah Menu</h2>
            </div>
            <div class="user-info">
                Login sebagai:
                <strong><?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; ?></strong>
                | <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                Form Tambah Menu
            </div>
            <div class="card-body">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="namamenu" class="form-label">Nama Menu</label>
                        <input type="text" class="form-control" id="namamenu" name="namamenu"
                               value="<?= htmlspecialchars($namamenu); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Gambar Produk (JPG/JPEG/PNG)</label>
                        <input type="file" class="form-control" id="file" name="file"
                               accept=".jpg,.jpeg,.png" required>
                    </div>

                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select class="form-select form-control" name="kategori" id="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriOptions as $kat): ?>
                                <option value="<?= $kat['id_kategori']; ?>"
                                    <?= ($kategori == $kat['id_kategori']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($kat['kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga (Rp) tanpa titik</label>
                        <input type="number" class="form-control" id="harga" name="harga"
                               value="<?= htmlspecialchars($harga); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="menues.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
    // toggle sidebar (mobile)
    $(document).on('click', '.btn-toggle-sidebar', function() {
        $('.sidebar').toggleClass('show');
    });
</script>

</body>
</html>
