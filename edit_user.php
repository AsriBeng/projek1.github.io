<?php
session_start();
require 'config.php';

// CEK PARAMETER ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID user tidak valid.");
}

$id_user = (int) $_GET['id'];
$error   = '';
$success = '';

// AMBIL DATA USER
$sql  = "SELECT * FROM tb_pelanggan WHERE id_user = $id_user LIMIT 1";
$res  = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);

if (!$user) {
    die("Data user tidak ditemukan.");
}

// PROSES SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? ''); // boleh kosong (tidak ganti)
    $alamat   = trim($_POST['alamat'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');

    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } else {
        $namaEsc     = mysqli_real_escape_string($conn, $nama);
        $userEsc     = mysqli_real_escape_string($conn, $username);
        $alamatEsc   = mysqli_real_escape_string($conn, $alamat);
        $nohpEsc     = mysqli_real_escape_string($conn, $no_hp);

        $set = "nama='$namaEsc',
                username='$userEsc',
                alamat='$alamatEsc',
                no_hp='$nohpEsc'";

        // jika password diisi, update juga
        if ($password !== '') {
            $passEsc = mysqli_real_escape_string($conn, $password); // masih plaintext, sesuaikan login
            $set .= ", password='$passEsc'";
        }

        $sqlUpdate = "UPDATE tb_pelanggan SET $set WHERE id_user = $id_user";

        if (mysqli_query($conn, $sqlUpdate)) {
            $success = "Data user berhasil disimpan! Anda akan dialihkan...";
            // update variabel $user supaya form menampilkan data terbaru
            $sql  = "SELECT * FROM tb_pelanggan WHERE id_user = $id_user LIMIT 1";
            $res  = mysqli_query($conn, $sql);
            $user = mysqli_fetch_assoc($res);
        } else {
            $error = "Gagal mengupdate data: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Omah Kopi</title>

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
        .topbar {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 20px;
        }
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
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="staf_user.php"><i class="fas fa-user-shield"></i> Staf / Admin</a></li>
        </ul>
    </nav>

    <!-- KONTEN -->
    <div class="main-content">
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn-toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h2>Edit User</h2>
            </div>
            <div class="user-info">
                Login sebagai:
                <strong><?= $_SESSION['nama'] ?? 'Admin'; ?></strong> |
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Form Edit User</div>
            <div class="card-body">

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
                    <script>
                        setTimeout(function () {
                            window.location.href = "users.php?msg=updated";
                        }, 1500);
                    </script>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control"
                               value="<?= htmlspecialchars($user['nama']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                               value="<?= htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password (kosongkan jika tidak ingin diubah)</label>
                        <input type="text" name="password" class="form-control" value="">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($user['alamat']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-control"
                               value="<?= htmlspecialchars($user['no_hp']); ?>">
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    <a href="users.php" class="btn btn-secondary">Kembali</a>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery-1.11.3.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
    document.querySelector('.btn-toggle-sidebar').addEventListener('click', function () {
        document.querySelector('.sidebar').classList.toggle('show');
    });
</script>
</body>
</html>
